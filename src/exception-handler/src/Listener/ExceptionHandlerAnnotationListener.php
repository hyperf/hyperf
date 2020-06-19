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
namespace Hyperf\ExceptionHandler\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler;
use Hyperf\ExceptionHandler\Exception\ServerNotFoundException;
use Hyperf\Framework\Event\BootApplication;
use SplPriorityQueue;

class ExceptionHandlerAnnotationListener implements ListenerInterface
{
    const HANDLER_KEY = 'exceptions.handler';

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @throws ServerNotFoundException
     */
    public function process(object $event)
    {
        $queue = new SplPriorityQueue();
        $handlers = $this->config->get(self::HANDLER_KEY);
        $annotations = AnnotationCollector::getClassesByAnnotation(ExceptionHandler::class);
        foreach ($handlers as $server => $items) {
            foreach ($items as $handler) {
                $queue->insert([$server, $handler], 0);
            }
        }

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
        }
        return $result;
    }
}
