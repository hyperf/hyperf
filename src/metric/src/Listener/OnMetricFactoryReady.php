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
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Hyperf\Metric\MetricSetter;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine;
use Swoole\Server;
use Swoole\Timer;

/**
 * Similar to OnWorkerStart, but this only runs in one process.
 */
class OnMetricFactoryReady implements ListenerInterface
{
    use MetricSetter;

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
            [],
            'sys_load',
            'event_num',
            'signal_listener_num',
            'aio_task_num',
            'aio_worker_num',
            'c_stack_size',
            'coroutine_num',
            'coroutine_peak_num',
            'coroutine_last_cid',
            'connection_num',
            'accept_count',
            'close_count',
            'worker_num',
            'idle_worker_num',
            'tasking_num',
            'request_count',
            'timer_num',
            'timer_round',
            'metric_process_memory_usage',
            'metric_process_memory_peak_usage'
        );

        $server = $this->container->get(Server::class);
        $timerInterval = $this->config->get('metric.default_metric_interval', 5);
        Timer::tick($timerInterval * 1000, function () use ($metrics, $server) {
            $serverStats = $server->stats();
            $coroutineStats = Coroutine::stats();
            $timerStats = Timer::stats();
            $this->trySet('', $metrics, $serverStats);
            $this->trySet('', $metrics, $coroutineStats);
            $this->trySet('timer_', $metrics, $timerStats);
            $load = sys_getloadavg();
            $metrics['sys_load']->set(round($load[0] / swoole_cpu_num(), 2));
            $metrics['metric_process_memory_usage']->set(memory_get_usage());
            $metrics['metric_process_memory_peak_usage']->set(memory_get_peak_usage());
        });
    }
}
