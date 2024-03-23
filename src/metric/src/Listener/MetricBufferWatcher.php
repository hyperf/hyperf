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
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Metric\Contract\MetricCollectorInterface;
use Psr\Container\ContainerInterface;

/**
 * Collect and handle metrics before worker start.
 * Only used for swoole process mode.
 */
class MetricBufferWatcher implements ListenerInterface
{
    private ConfigInterface $config;

    private MetricCollectorInterface $collector;

    private Timer $timer;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->collector = $container->get(MetricCollectorInterface::class);
        $this->timer = new Timer();
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
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        if ($event->workerId === null) {
            return;
        }

        /*
         * Only start buffer watcher in standalone process mode
         */
        if (! $this->config->get('metric.use_standalone_process', true)) {
            return;
        }

        $timerInterval = $this->config->get('metric.buffer_interval', 5);
        $timerId = $this->timer->tick($timerInterval, function () {
            $this->collector->flush();
        });
        // Clean up timer on worker exit;
        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
