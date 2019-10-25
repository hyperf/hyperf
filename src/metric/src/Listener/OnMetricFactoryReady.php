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
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;
use Swoole\Server;
use Swoole\Timer;

/**
 * @Listener
 */
class OnMetricFactoryReady implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var MetricFactoryInterface
     */
    protected $factory;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            MetricFactoryReady::class,
        ];
    }

    /**
     * Periodically scan metrics.
     */
    public function process(object $event)
    {
        if (! $this->config->get('metric.enable_default_metric')) {
            return;
        }
        $this->factory = $event->factory;
        $metrics = $this->factoryMetrics(
            'sys_load',
            'memory_usage',
            'memory_peak_usage',
            'event_num',
            'signal_listener_num',
            'aio_task_num',
            'aio_worker_num',
            'c_stack_size',
            'coroutine_num',
            'coroutine_peak_num',
            'coroutine_last_cid',
            'start_time',
            'connection_num',
            'accept_count',
            'close_count',
            'worker_num',
            'idle_worker_num',
            'tasking_num',
            'request_count',
            'timer_num',
            'timer_round'
        );

        $server = $this->container->get(Server::class);

        Timer::tick(5000, function () use ($metrics, $server) {
            $serverStats = $server->stats();
            $coroutineStats = Coroutine::stats();
            $timerStats = Timer::stats();
            foreach ($timerStats as $key => $value) {
                $timerStats['timer_' . $key] = $value;
            }
            $stats = \array_merge($serverStats, $coroutineStats, $timerStats);
            $this->trySet($metrics, $stats);
            $load = sys_getloadavg();
            $metrics['sys_load']->set($load[0] / \swoole_cpu_num());
            $metrics['memory_usage']->set(\memory_get_usage());
            $metrics['memory_peak_usage']->set(\memory_get_peak_usage());
        });
    }

    /**
     * Try to set every stats available to the gauge.
     * Some of the stats might be missing depending
     * on the platform.
     * @param array $metrics
     * @param array $stats
     */
    private function trySet(array $metrics, array $stats): void
    {
        foreach (array_keys($metrics) as $key) {
            if (key_exists($key, $stats)) {
                $metrics[$key]->set($stats[$key]);
            }
        }
    }

    /**
     * Create an array of gauges.
     * @param array<int, string> $names
     * @return GaugeInterface[]
     */
    private function factoryMetrics(string ...$names): array
    {
        $out = [];
        foreach ($names as $name) {
            $out[$name] = $this
                ->factory
                ->makeGauge($name, []);
        }
        return $out;
    }
}
