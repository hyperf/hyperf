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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Process;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @Command
 */
class ServerRestart extends SymfonyCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('server:restart');
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('Restart swoole server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \Swoole\Runtime::enableCoroutine(true);

        $io = new SymfonyStyle($input, $output);

        $this->checkEnvironment($io);

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

        $serverFactory = $this->container->get(ServerFactory::class)
            ->setEventDispatcher($this->container->get(EventDispatcherInterface::class))
            ->setLogger($this->container->get(StdoutLoggerInterface::class));

        $serverConfig = $this->container->get(ConfigInterface::class)->get('server', []);
        if (!$serverConfig) {
            throw new \InvalidArgumentException('At least one server should be defined.');
        }

        $serverConfig['settings']['daemonize'] = 1;

        $io->success('swoole server restart success.');

        $serverFactory->configure($serverConfig);
        $serverFactory->start();
    }

    private function checkEnvironment(SymfonyStyle $io)
    {
        if (ini_get_all('swoole')['swoole.use_shortname']['local_value'] !== 'Off') {
            $io->error('Swoole short name have to disable before start server, please set swoole.use_shortname = \'Off\' into your php.ini.');
            exit(0);
        }
    }
}
