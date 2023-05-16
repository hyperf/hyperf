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

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Psr\Container\ContainerInterface;

/**
 * A simple redis queue watcher served as an example.
 * This listener is not auto enabled.Tweak it to fit your
 * own need.
 */
class QueueWatcher implements ListenerInterface
{
    private Timer $timer;

    public function __construct(protected ContainerInterface $container)
    {
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
        $queue = $this->container->get(DriverFactory::class)->get('default');
        $waiting = $event
            ->factory
            ->makeGauge('queue_waiting', ['queue'])
            ->with('default');
        $delayed = $event
            ->factory
            ->makeGauge('queue_delayed', ['queue'])
            ->with('default');
        $failed = $event
            ->factory
            ->makeGauge('queue_failed', ['queue'])
            ->with('default');
        $timeout = $event
            ->factory
            ->makeGauge('queue_timeout', ['queue'])
            ->with('default');

        $config = $this->container->get(ConfigInterface::class);
        $timerInterval = $config->get('metric.default_metric_interval', 5);
        $timerId = $this->timer->tick($timerInterval, function () use ($waiting, $delayed, $failed, $timeout, $queue) {
            $info = $queue->info();
            $waiting->set((float) $info['waiting']);
            $delayed->set((float) $info['delayed']);
            $failed->set((float) $info['failed']);
            $timeout->set((float) $info['timeout']);
        });
        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
