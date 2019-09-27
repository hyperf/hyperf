<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Server\Command;

use Hyperf\Command\Annotation\Command;
use Swoole\Process;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @Command
 */
class ServerWatch extends SymfonyCommand
{
    /**
     * @var int
     */
    private $interval;

    /**
     * @var array|bool
     */
    private $cmdArr;


    public function __construct()
    {
        parent::__construct('server:watch');
    }

    protected function configure()
    {
        $this
            ->setDescription('Watch swoole server.')
            ->addOption('interval', 't', InputOption::VALUE_OPTIONAL, 'interval time ( 1-15 seconds).', 3)
            ->addOption('cmd', 'c', InputOption::VALUE_OPTIONAL, 'Start cmd', 'php bin/hyperf.php server:start');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->checkEnvironment($io);

        $this->interval = (int)$input->getOption('interval');
        if ($this->interval < 0 || $this->interval > 15) {
            $this->interval = 3;
        }

        $cmd = $input->getOption('cmd');
        if (!$cmd) {
            $io->error('Invalid cmd command.');
            return false;
        }

        if (!$this->cmdArr = $this->cmdHandle($cmd)) {
            $io->error('cmd command is empty.');
            return false;
        }

        $pidFile = BASE_PATH . '/runtime/hyperf.pid';
        $pid = file_exists($pidFile) ? intval(file_get_contents($pidFile)) : false;
        if ($pid && Process::kill($pid, SIG_DFL)) {
            if (!Process::kill($pid, SIGTERM)) {
                $io->error('old swoole server stop error.');
                return false;
            }

            while (Process::kill($pid, SIG_DFL)) {
                sleep(1);
            }
        }

        $io->note('start new swoole server ...');
        $pid = $this->startServer();

        while ($pid > 0) {

            $this->watch();

            $io->note('restart swoole server ...');

            $this->stopServer($pid, $io);

            $pid = $this->startServer();

            sleep(1);
        }
    }

    private function checkEnvironment(SymfonyStyle $io)
    {
        if (ini_get_all('swoole')['swoole.use_shortname']['local_value'] !== 'Off') {
            $io->error('Swoole short name have to disable before start server, please set swoole.use_shortname = \'Off\' into your php.ini.');
            exit(0);
        }
    }

    private function cmdHandle(string $cmd)
    {
        $tmpCmdArr = [];
        if ($cmd = trim($cmd)) {
            $tmpArr = explode(' ', $cmd);
            foreach ($tmpArr as $k => &$it) {
                if ($it = trim($it)) {
                    switch ($it) {
                        case 'php':
                            if (!$php = exec('which php')) {
                                return false;
                            }
                            $it = $php;
                            break;
                        case './bin/hyperf.php':
                        case 'bin/hyperf.php':
                            $it = BASE_PATH . '/bin/hyperf.php';
                            break;
                    }
                } else {
                    unset($tmpArr[$k]);
                }
            }
            unset($it);
            if (!empty($tmpArr)) {
                $tmpCmdArr['cmd'] = array_shift($tmpArr);
                $tmpCmdArr['args'] = $tmpArr;
            }
        }
        return empty($tmpCmdArr) ? false : $tmpCmdArr;
    }

    private function startServer()
    {
        $process = new Process(function (Process $process) {
            $process->exec($this->cmdArr['cmd'], $this->cmdArr['args']);
        });
        return $process->start();
    }

    private function stopServer(int $pid, SymfonyStyle $io): bool
    {
        $io->text('stop old swoole server. pid:' . $pid);

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
                $io->error('stop old swoole server timeout.');
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
