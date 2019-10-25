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
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Metric\Contract\GaugeInterface;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Swoole\Server;
use Swoole\Timer;

/**
 * @Listener
 */
class OnWorkerStart implements ListenerInterface
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
        $this->factory = $container->get(MetricFactoryInterface::class);
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
    public function process(object $event)
    {
        $workerId = $event->workerId;

        if ($workerId === null) {
            return;
        }

        /*
         * If no standalone process is started, we have to do handle metrics on worker.
         */
        if (! $this->config->get('metric.use_standalone_process', true)) {
            go(function () {
                $this->factory->handle();
            });
        }

        /*
         * Allow user to hook up their own metrics logic
         */
        if ($this->shouldFireMetricFactoryReadyEvent($workerId)) {
            $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
            $eventDispatcher->dispatch(new MetricFactoryReady($this->factory));
        }

        if (! $this->config->get('metric.enable_default_metric', false)) {
            return;
        }

        // The following metrics MUST be collected in worker.
        $metrics = $this->factoryMetrics(
            $workerId,
            'worker_request_count',
            'worker_dispatch_count'
        );

        $server = $this->container->get(Server::class);

        Timer::tick(5000, function () use ($metrics, $server) {
            $serverStats = $server->stats();
            $metrics['worker_request_count']->set($serverStats['worker_request_count']);
            $metrics['worker_dispatch_count']->set($serverStats['worker_dispatch_count']);
        });
    }

    /**
     * Create an array of gauges.
     * @param int $workerId
     * @param array<int, string> $names
     * @return GaugeInterface[]
     */
    private function factoryMetrics(int $workerId, string ...$names): array
    {
        $out = [];
        foreach ($names as $name) {
            $out[$name] = $this
                ->factory
                ->makeGauge($name, ['worker_id'])
                ->with((string) $workerId);
        }
        return $out;
    }

    private function shouldFireMetricFactoryReadyEvent(int $workerId): bool
    {
        return (! $this->config->get('metric.use_standalone_process'))
            && $workerId == 0;
    }
}
