<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Server\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\ServerFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Runtime;
use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StartServer extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @var int
     */
    private $interval;

    /**
     * @var bool
     */
    private $clear;

    /**
     * @var bool
     */
    private $daemonize;

    /**
     * @var string
     */
    private $php;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('start');
    }

    protected function configure()
    {
        $this
            ->setDescription('Start hyperf servers.')
            ->addOption('daemonize', 'd', InputOption::VALUE_OPTIONAL, 'swoole server daemonize', false)
            ->addOption('clear', 'c', InputOption::VALUE_OPTIONAL, 'clear runtime container', false)
            ->addOption('watch', 'w', InputOption::VALUE_OPTIONAL, 'watch swoole server', false)
            ->addOption('interval', 't', InputOption::VALUE_OPTIONAL, 'interval time ( 1-15 seconds)', 3)
            ->addOption('php', 'p', InputOption::VALUE_OPTIONAL, 'which php');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->checkEnvironment($output);

        $this->stopServer();

        $this->clear = ($input->getOption('clear') !== false);

        $this->daemonize = ($input->getOption('daemonize') !== false);

        if ($input->getOption('watch') !== false) {

            $this->interval = (int)$input->getOption('interval');
            if ($this->interval < 0 || $this->interval > 15) {
                $this->interval = 3;
            }
            if (!$this->php = $input->getOption('php')) {
                if (!$this->php = exec('which php')) {
                    $this->php = 'php';
                }
            }
            $this->watchServer();
        } else {
            if ($this->clear) {
                $this->clearRuntimeContainer();
            }

            $this->startServer();
        }
    }

    private function checkEnvironment(OutputInterface $output)
    {
        /**
         * swoole.use_shortname = true       => string(1) "1"     => enabled
         * swoole.use_shortname = "true"     => string(1) "1"     => enabled
         * swoole.use_shortname = on         => string(1) "1"     => enabled
         * swoole.use_shortname = On         => string(1) "1"     => enabled
         * swoole.use_shortname = "On"       => string(2) "On"    => enabled
         * swoole.use_shortname = "on"       => string(2) "on"    => enabled
         * swoole.use_shortname = 1          => string(1) "1"     => enabled
         * swoole.use_shortname = "1"        => string(1) "1"     => enabled
         * swoole.use_shortname = 2          => string(1) "1"     => enabled
         * swoole.use_shortname = false      => string(0) ""      => disabled
         * swoole.use_shortname = "false"    => string(5) "false" => disabled
         * swoole.use_shortname = off        => string(0) ""      => disabled
         * swoole.use_shortname = Off        => string(0) ""      => disabled
         * swoole.use_shortname = "off"      => string(3) "off"   => disabled
         * swoole.use_shortname = "Off"      => string(3) "Off"   => disabled
         * swoole.use_shortname = 0          => string(1) "0"     => disabled
         * swoole.use_shortname = "0"        => string(1) "0"     => disabled
         * swoole.use_shortname = 00         => string(2) "00"    => disabled
         * swoole.use_shortname = "00"       => string(2) "00"    => disabled
         * swoole.use_shortname = ""         => string(0) ""      => disabled
         * swoole.use_shortname = " "        => string(1) " "     => disabled.
         */
        $useShortname = ini_get_all('swoole')['swoole.use_shortname']['local_value'];
        $useShortname = strtolower(trim(str_replace('0', '', $useShortname)));
        if (!in_array($useShortname, ['', 'off', 'false'], true)) {
            $output->writeln('<error>ERROR</error> Swoole short name have to disable before start server, please set swoole.use_shortname = off into your php.ini.');
            exit(0);
        }
    }


    private function clearRuntimeContainer()
    {
        exec('rm -rf ' . BASE_PATH . '/runtime/container');
    }

    private function startServer()
    {
        $serverFactory = $this->container->get(ServerFactory::class)
            ->setEventDispatcher($this->container->get(EventDispatcherInterface::class))
            ->setLogger($this->container->get(StdoutLoggerInterface::class));

        $serverConfig = $this->container->get(ConfigInterface::class)->get('server', []);
        if (!$serverConfig) {
            throw new InvalidArgumentException('At least one server should be defined.');
        }

        if ($this->daemonize) {
            $serverConfig['settings']['daemonize'] = 1;
            $this->io->success('swoole server start success.');
        }

        Runtime::enableCoroutine(true, swoole_hook_flags());

        $serverFactory->configure($serverConfig);

        $serverFactory->start();
    }

    private function stopServer()
    {
        $pidFile = BASE_PATH . '/runtime/hyperf.pid';
        $pid = file_exists($pidFile) ? intval(file_get_contents($pidFile)) : false;
        if ($pid && Process::kill($pid, SIG_DFL)) {
            if (!Process::kill($pid, SIGTERM)) {
                $this->io->error('old swoole server stop error.');
                die();
            }

            while (Process::kill($pid, SIG_DFL)) {
                sleep(1);
            }
        }
    }

    private function watchServer()
    {
        $this->io->note('start new swoole server ...');
        $pid = $this->startProcess();

        while ($pid > 0) {

            $this->watch();

            $this->io->note('restart swoole server ...');

            $this->stopProcess($pid);

            $pid = $this->startProcess();

            sleep(1);
        }
    }

    private function startProcess()
    {
        $this->clearRuntimeContainer();

        $process = new Process(function (Process $process) {
            $args = [BASE_PATH . '/bin/hyperf.php', 'start'];
            if ($this->daemonize) {
                $args[] = '-d';
            }
            $process->exec($this->php, $args);
        });
        return $process->start();
    }

    private function stopProcess(int $pid): bool
    {
        $this->io->text('stop old swoole server. pid:' . $pid);

        $timeout = 15;
        $startTime = time();

        while (true) {
            if ($ret = Process::wait(false) && $ret['pid'] == $pid) {
                return true;
            }
            if (!Process::kill($pid, SIG_DFL)) {
                return true;
            }
            if ((time() - $startTime) >= $timeout) {
                $this->io->error('stop old swoole server timeout.');
                return false;
            }
            Process::kill($pid, SIGTERM);
            sleep(1);
        }
        return false;
    }

    private function monitorDirs(bool $recursive = false)
    {
        $dirs[] = BASE_PATH . '/app';
        $dirs[] = BASE_PATH . '/config';

        if ($recursive) {
            foreach ($dirs as $dir) {
                $dirIterator = new \RecursiveDirectoryIterator($dir);
                $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::SELF_FIRST);
                /** @var \SplFileInfo $file */
                foreach ($iterator as $file) {
                    if ($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
                        $dirs[] = $file->getPathname();
                    }
                }
            }
        }

        return $dirs;
    }

    private function monitorFiles()
    {
        $files[] = BASE_PATH . '/.env';
        return $files;
    }

    private function watch()
    {
        if (extension_loaded('inotify')) {
            return $this->inotifyWatch();
        } else {
            return $this->fileWatch();
        }
    }

    private function inotifyWatch()
    {
        $fd = inotify_init();
        stream_set_blocking($fd, 0);

        $dirs = $this->monitorDirs(true);
        foreach ($dirs as $dir) {
            inotify_add_watch($dir, IN_CLOSE_WRITE | IN_CREATE | IN_DELETE);
        }
        $files = $this->monitorFiles();
        foreach ($files as $file) {
            inotify_add_watch($file, IN_CLOSE_WRITE | IN_CREATE | IN_DELETE);
        }

        while (true) {
            sleep($this->interval);
            if (inotify_read($fd)) {
                break;
            }
        }

        fclose($fd);
    }

    private function fileWatch()
    {
        $dirs = $this->monitorDirs();
        $files = $this->monitorFiles();
        $inodeListOld = [];
        $inodeListNew = [];
        $isFrist = true;
        while (true) {
            foreach ($dirs as $dir) {
                $dirIterator = new \RecursiveDirectoryIterator($dir);
                $iterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::LEAVES_ONLY);
                /** @var \SplFileInfo $file */
                foreach ($iterator as $file) {
                    if ($file->isFile() && in_array(strtolower($file->getExtension()), ['env', 'php'])) {
                        $inode = $file->getInode();
                        $sign = $file->getFilename() . $file->getMTime();
                        if ($isFrist) {
                            $inodeListOld[$inode] = $sign;
                        } else {
                            // add new file || file changed
                            if (!isset($inodeListOld[$inode]) || $inodeListOld[$inode] != $sign) {
                                return true;
                            } else {
                                $inodeListNew[$inode] = $sign;
                            }
                        }
                    }
                }
            }

            foreach ($files as $key => $file) {
                if (file_exists($file)) {
                    $file = new \SplFileInfo($file);
                    $inode = $file->getInode();
                    $sign = $file->getFilename() . $file->getMTime();
                    if ($isFrist) {
                        $inodeListOld[$inode] = $sign;
                    } else {
                        // add new file || file changed
                        if (!isset($inodeListOld[$inode]) || $inodeListOld[$inode] != $sign) {
                            return true;
                        } else {
                            $inodeListNew[$inode] = $sign;
                        }
                    }
                }
            }

            if ($isFrist) {
                $isFrist = false;
            } else {
                // file remove
                if (!empty(array_diff($inodeListOld, $inodeListNew))) {
                    return true;
                }
            }

            sleep($this->interval);
        }
    }
}
