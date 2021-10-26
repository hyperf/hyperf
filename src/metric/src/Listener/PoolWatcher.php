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
namespace Hyperf\Metric\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Coordinator\Constants;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;
use Swoole\Timer;

abstract class PoolWatcher
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    /**
     * Periodically scan metrics.
     */
    abstract public function process(object $event);

    public function watch(Pool $pool, string $poolName, int $workerId)
    {
        $connectionsInUseGauge = $this
            ->container
            ->get(MetricFactoryInterface::class)
            ->makeGauge($this->getPrefix() . '_connections_in_use', ['pool', 'worker'])
            ->with($poolName, (string) $workerId);
        $connectionsInWaitingGauge = $this
            ->container
            ->get(MetricFactoryInterface::class)
            ->makeGauge($this->getPrefix() . '_connections_in_waiting', ['pool', 'worker'])
            ->with($poolName, (string) $workerId);
        $maxConnectionsGauge = $this
            ->container
            ->get(MetricFactoryInterface::class)
            ->makeGauge($this->getPrefix() . '_max_connections', ['pool', 'worker'])
            ->with($poolName, (string) $workerId);

        $config = $this->container->get(ConfigInterface::class);
        $timerInterval = $config->get('metric.default_metric_interval', 5);
        $timerId = Timer::tick($timerInterval * 1000, function () use (
            $connectionsInUseGauge,
            $connectionsInWaitingGauge,
            $maxConnectionsGauge,
            $pool
        ) {
            $maxConnectionsGauge->set((float) $pool->getOption()->getMaxConnections());
            $connectionsInWaitingGauge->set((float) $pool->getConnectionsInChannel());
            $connectionsInUseGauge->set((float) $pool->getCurrentConnections());
        });
        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            Timer::clear($timerId);
        });
    }

    abstract protected function getPrefix();
}
