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

namespace Hyperf\ExceptionHandler;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Dispatcher\AbstractDispatcher;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ExceptionHandlerDispatcher extends AbstractDispatcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    private $handlers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initAnnotationExceptionHandler(AnnotationCollector::list());
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(...$params)
    {
        return parent::dispatch(...$params);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(...$params)
    {
        /**
         * @var Throwable
         * @var string[] $handlers
         */
        [$throwable, $handlers] = $params;
        $response = Context::get(ResponseInterface::class);

        foreach ($this->handlers as $handler) {
            $handlers[] = $handler;
        }
        
        foreach ($handlers as $handler) {
            if (! $this->container->has($handler)) {
                throw new \InvalidArgumentException(sprintf('Invalid exception handler %s.', $handler));
            }
            $handlerInstance = $this->container->get($handler);
            if (! $handlerInstance instanceof ExceptionHandler || ! $handlerInstance->isValid($throwable)) {
                continue;
            }
            $response = $handlerInstance->handle($throwable, $response);
            if ($handlerInstance->isPropagationStopped()) {
                break;
            }
        }
        return $response;
    }

    private function initAnnotationExceptionHandler(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][\Hyperf\ExceptionHandler\Annotation\ExceptionHandler::class])) {
                $this->handlers[] = $className;
            }
        }
    }
}
