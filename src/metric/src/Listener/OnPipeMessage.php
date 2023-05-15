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

use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Adapter\RemoteProxy\Counter;
use Hyperf\Metric\Adapter\RemoteProxy\Gauge;
use Hyperf\Metric\Adapter\RemoteProxy\Histogram;
use Hyperf\Metric\Contract\MetricFactoryInterface;
use Hyperf\Process\Event\PipeMessage;

use function Hyperf\Support\make;

/**
 * Receives messages in metric process.
 */
class OnPipeMessage implements ListenerInterface
{
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            PipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        Coroutine::create(function () use ($event) {
            if ($event instanceof PipeMessage) {
                $factory = make(MetricFactoryInterface::class);
                $inner = $event->data;
                switch (true) {
                    case $inner instanceof Counter:
                        $counter = $factory->makeCounter($inner->name, $inner->labelNames);
                        $counter->with(...$inner->labelValues)->add($inner->delta);
                        break;
                    case $inner instanceof Gauge:
                        $gauge = $factory->makeGauge($inner->name, $inner->labelNames);
                        if (isset($inner->value)) {
                            $gauge->with(...$inner->labelValues)->set($inner->value);
                        } else {
                            $gauge->with(...$inner->labelValues)->add($inner->delta);
                        }
                        break;
                    case $inner instanceof Histogram:
                        $histogram = $factory->makeHistogram($inner->name, $inner->labelNames);
                        $histogram->with(...$inner->labelValues)->put($inner->sample);
                        break;
                    default:
                        // Nothing to do
                        break;
                }
            }
        });
    }
}
