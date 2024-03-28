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

namespace Hyperf\ExceptionHandler\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Stdlib\SplPriorityQueue;

class ExceptionHandlerListener implements ListenerInterface
{
    public const HANDLER_KEY = 'exceptions.handler';

    public function __construct(private ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $queue = new SplPriorityQueue();
        $handlers = $this->config->get(self::HANDLER_KEY, []);
        foreach ($handlers as $server => $items) {
            foreach ($items as $handler => $priority) {
                if (! is_numeric($priority)) {
                    $handler = $priority;
                    $priority = 0;
                }
                $queue->insert([$server, $handler], $priority);
            }
        }

        $annotations = AnnotationCollector::getClassesByAnnotation(ExceptionHandler::class);
        /**
         * @var string $handler
         * @var ExceptionHandler $annotation
         */
        foreach ($annotations as $handler => $annotation) {
            $queue->insert([$annotation->server, $handler], $annotation->priority);
        }

        $this->config->set(self::HANDLER_KEY, $this->formatExceptionHandlers($queue));
    }

    protected function formatExceptionHandlers(SplPriorityQueue $queue): array
    {
        $result = [];
        foreach ($queue as $item) {
            [$server, $handler] = $item;
            $result[$server][] = $handler;
            $result[$server] = array_values(array_unique($result[$server]));
        }
        return $result;
    }
}
