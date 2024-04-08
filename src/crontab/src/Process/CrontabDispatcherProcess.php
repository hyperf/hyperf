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

use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\StrategyInterface;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
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

    private ?PsrLoggerInterface $logger = null;

    private int $minuteTimestamp = 0;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->config = $container->get(ConfigInterface::class);
        $this->scheduler = $container->get(Scheduler::class);
        $this->strategy = $container->get(StrategyInterface::class);
        $this->logger = match (true) {
            $container->has(LoggerInterface::class) => $container->get(LoggerInterface::class),
            $container->has(StdoutLoggerInterface::class) => $container->get(StdoutLoggerInterface::class),
            default => null,
        };
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
     * Get the interval of the current second to the next minute.
     * @deprecated since v3.1, will remove in v3.2
     */
    public function getInterval(int $currentSecond, float $ms): float
    {
        $sleep = 60 - $currentSecond - $ms;
        return round($sleep, 3);
    }

    /**
     * @return bool whether the server shutdown
     */
    private function sleep(): bool
    {
        $now = Carbon::now();
        $nextMinute = $now->clone()->addMinute()->startOfMinute();
        $duration = $nextMinute->diffAsCarbonInterval($now);
        $sleep = $duration->s + round($duration->f, 3);

        if ($sleep > 0) {
            $this->logger?->debug(sprintf('Current microtime: %s, Crontab dispatcher sleep %s s.', $now->format('Y-m-d H:i:s.v'), $sleep));

            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield($sleep)) {
                return true;
            }
        }

        if (Carbon::now()->format('s') !== '00') { // If the current second is not 00, sleep until the next minute.
            $this->logger?->debug('Crontab tasks will be executed at the same minute, but the framework found it, so you don\'t care it. If you received the error message, you can open a issue in `hyperf/hyperf`.');

            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(0.1)) {
                return true;
            }
        }

        return false;
    }
}
