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
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\CoroutineServerStats;
use Hyperf\Metric\Event\MetricFactoryReady;
use Hyperf\Metric\MetricFactoryPicker;
use Hyperf\Metric\MetricSetter;
use Hyperf\Support\System;
use Psr\Container\ContainerInterface;
use Swoole\Server as SwooleServer;

/**
 * Similar to OnWorkerStart, but this only runs in one process.
 */
class OnMetricFactoryReady implements ListenerInterface
{
    use MetricSetter;

    protected MetricFactoryInterface $factory;

    private ConfigInterface $config;

    private Timer $timer;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->timer = new Timer();
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
    public function process(object $event): void
    {
        if (! $this->config->get('metric.enable_default_metric')) {
            return;
        }

        $this->factory = $event->factory;
        $metrics = $this->factoryMetrics(
            ['worker' => '0'],
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
            'swoole_timer_num',
            'swoole_timer_round',
            'metric_process_memory_usage',
            'metric_process_memory_peak_usage'
        );

        $serverStatsFactory = null;

        if (! MetricFactoryPicker::$isCommand) {
            if ($this->container->has(SwooleServer::class) && $server = $this->container->get(SwooleServer::class)) {
                if ($server instanceof SwooleServer) {
                    $serverStatsFactory = fn (): array => $server->stats();
                }
            }

            if (! $serverStatsFactory) {
                $serverStatsFactory = fn (): array => $this->container->get(CoroutineServerStats::class)->toArray();
            }
        }

        $timerInterval = $this->config->get('metric.default_metric_interval', 5);
        $timerId = $this->timer->tick($timerInterval, function () use ($metrics, $serverStatsFactory) {
            $this->trySet('', $metrics, Co::stats());
            $this->trySet('timer_', $metrics, Timer::stats());

            if ($serverStatsFactory) {
                $this->trySet('', $metrics, $serverStatsFactory());
            }

            if (class_exists('Swoole\Timer')) {
                $this->trySet('swoole_timer_', $metrics, \Swoole\Timer::stats());
            }

            $load = sys_getloadavg();
            $metrics['sys_load']->set(round($load[0] / System::getCpuCoresNum(), 2));
            $metrics['metric_process_memory_usage']->set(memory_get_usage());
            $metrics['metric_process_memory_peak_usage']->set(memory_get_peak_usage());
        });

        // Clean up timer on worker exit;
        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
