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
use Hyperf\Framework\Event\BootApplication;
use SplPriorityQueue;

class ExceptionHandlerListener implements ListenerInterface
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

    public function process(object $event)
    {
        $queue = new SplPriorityQueue();
        $handlers = $this->config->get(self::HANDLER_KEY, []);
        foreach ($handlers as $server => $items) {
            $count = count($items);
            $len = strlen((string) $count);
            foreach ($items as $handler => $priority) {
                if (! is_numeric($priority)) {
                    $handler = $priority;
                    --$count;
                    $priority = sprintf('0.%d', str_pad((string) $count, $len, '0', STR_PAD_LEFT));
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
