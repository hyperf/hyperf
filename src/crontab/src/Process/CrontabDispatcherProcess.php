<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Crontab\Process;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\StrategyInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Swoole\Server;

class CrontabDispatcherProcess extends AbstractProcess
{
    public string $name = 'crontab-dispatcher';

    /**
     * @var Server
     */
    private $server;

    private ConfigInterface $config;

    private Scheduler $scheduler;

    private StrategyInterface $strategy;

    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->config = $container->get(ConfigInterface::class);
        $this->scheduler = $container->get(Scheduler::class);
        $this->strategy = $container->get(StrategyInterface::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function bind($server): void
    {
        $this->server = $server;
        parent::bind($server);
    }

    public function isEnable($server): bool
    {
        return (bool) $this->config->get('crontab.enable', false);
    }

    public function handle(): void
    {
        $this->event?->dispatch(new CrontabDispatcherStarted());
        while (ProcessManager::isRunning()) {
            if ($this->sleep()) {
                break;
            }
            $crontabs = $this->scheduler->schedule();
            while (! $crontabs->isEmpty()) {
                $crontab = $crontabs->dequeue();
                $this->strategy->dispatch($crontab);
            }
        }
    }

    /**
     * @return bool whether the server shutdown
     */
    private function sleep(): bool
    {
        $current = date('s', time());
        $sleep = 60 - $current;
        $this->logger->debug('Crontab dispatcher sleep ' . $sleep . 's.');
        if ($sleep > 0) {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($sleep)) {
                return true;
            }
        }

        return false;
    }
}
