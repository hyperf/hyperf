<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\PipeMessage;
use Hyperf\Tracer\HttpClientFactory;
use Hyperf\Tracer\ReporterMessage;
use Psr\Container\ContainerInterface;
use Zipkin\Reporters\Http;

/**
 * Class HttpReporterListener.
 * @Listener
 */
class HttpReporterListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

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
            PipeMessage::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     * @param PipeMessage $event
     */
    public function process(object $event)
    {
        if (! isset($event->data) || ! $event->data instanceof ReporterMessage) {
            return;
        }

        /** @var ReporterMessage $message */
        $message = $event->data;
        $http = new Http(
            $this->container->get(HttpClientFactory::class),
            $message->getOptions(),
            $this->container->get(StdoutLoggerInterface::class)
        );
        $http->report($message->getSpans());
    }
}
