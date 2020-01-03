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

namespace Hyperf\Metric\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Redis\Pool\PoolFactory;
use Psr\Container\ContainerInterface;
use Swoole\Timer;

/**
 * A simple mysql connection watcher served as an example.
 * This listener is not auto enabled.Tweak it to fit your
 * own need.
 */
class RedisPoolWatcher implements ListenerInterface
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
    public function process(object $event)
    {
        $workerId = $event->workerId;
        $pool = $this
            ->container
            ->get(PoolFactory::class)
            ->getPool('default');
        $gauge = $this
            ->container
            ->get(MetricFactoryInterface::class)
            ->makeGauge('redis_connections_in_use', ['pool', 'worker'])
            ->with('default', (string) $workerId);

        $config = $this->container->get(ConfigInterface::class);
        $timerInterval = $config->get('metric.default_metric_interval', 5);
        Timer::tick($timerInterval * 1000, function () use ($gauge, $pool) {
            $gauge->set((float) $pool->getCurrentConnections());
        });
    }
}
