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

/**
 * @\Hyperf\Command\Annotation\Command()
 */
class RestartServer extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('restart');
    }

    protected function configure()
    {
        $this->setDescription('Restart hyperf servers.')
            ->addOption('clear', 'c', InputOption::VALUE_OPTIONAL, 'clear runtime container', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkEnvironment($output);

        $io = new SymfonyStyle($input, $output);
        $pidFile = BASE_PATH . '/runtime/hyperf.pid';
        $pid = file_exists($pidFile) ? intval(file_get_contents($pidFile)) : false;
        if (!$pid) {
            $io->note('swoole server pid is invalid.');
            return false;
        }

        if (!Process::kill($pid, SIG_DFL)) {
            $io->note('swoole server process does not exist.');
            return false;
        }

        if (!Process::kill($pid, SIGTERM)) {
            $io->error('swoole server stop error.');
            return false;
        }

        while (Process::kill($pid, SIG_DFL)) {
            sleep(1);
        }

        if ($input->getOption('clear') !== false) {
            exec('rm -rf ' . BASE_PATH . '/runtime/container');
        }

        $serverFactory = $this->container->get(ServerFactory::class)
            ->setEventDispatcher($this->container->get(EventDispatcherInterface::class))
            ->setLogger($this->container->get(StdoutLoggerInterface::class));

        $serverConfig = $this->container->get(ConfigInterface::class)->get('server', []);
        if (!$serverConfig) {
            throw new InvalidArgumentException('At least one server should be defined.');
        }

        $serverConfig['settings']['daemonize'] = 1;

        $serverFactory->configure($serverConfig);

        Runtime::enableCoroutine(true, swoole_hook_flags());

        $serverFactory->start();
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
}
