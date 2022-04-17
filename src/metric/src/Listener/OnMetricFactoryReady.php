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
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Hyperf\Metric\MetricFactoryPicker;
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

    protected MetricFactoryInterface $factory;

    private ConfigInterface $config;

    public function __construct(protected ContainerInterface $container)
    {
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
    public function process(object $event): void
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
        $serverStats = null;
        if (! MetricFactoryPicker::$isCommand) {
            $server = $this->container->get(Server::class);
            $serverStats = $server->stats();
        }

        $timerInterval = $this->config->get('metric.default_metric_interval', 5);
        $timerId = Timer::tick($timerInterval * 1000, function () use ($metrics, $serverStats) {
            $coroutineStats = Coroutine::stats();
            $timerStats = Timer::stats();
            if ($serverStats) {
                $this->trySet('', $metrics, $serverStats);
            }
            $this->trySet('', $metrics, $coroutineStats);
            $this->trySet('timer_', $metrics, $timerStats);
            $load = sys_getloadavg();
            $metrics['sys_load']->set(round($load[0] / swoole_cpu_num(), 2));
            $metrics['metric_process_memory_usage']->set(memory_get_usage());
            $metrics['metric_process_memory_peak_usage']->set(memory_get_peak_usage());
        });

        // Clean up timer on worker exit;
        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            Timer::clear($timerId);
        });
    }
}
