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

namespace Hyperf\ExceptionHandler;

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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
         * @var string[] $secondaryHandlers
         */
        [$throwable, $handlers, $secondaryHandlers] = $params;
        $response = Context::get(ResponseInterface::class);
        [$isHandled, $response] = $this->handleExceptionWithHandlers($throwable, $response, $handlers);
        if (! $isHandled && ! empty($secondaryHandlers)) {
            [$isHandled, $response] = $this->handleExceptionWithHandlers($throwable, $response, $secondaryHandlers);
        }
        return $response;
    }

    private function handleExceptionWithHandlers(Throwable $throwable, ResponseInterface $response, $handlers)
    {
        $isHandled = false;
        foreach ($handlers as $handler) {
            if (! $this->container->has($handler)) {
                throw new \InvalidArgumentException(sprintf('Invalid exception handler %s.', $handler));
            }
            $handlerInstance = $this->container->get($handler);
            if (! $handlerInstance instanceof ExceptionHandler || ! $handlerInstance->isValid($throwable)) {
                continue;
            }
            $response = $handlerInstance->handle($throwable, $response);
            $isHandled = true;
            if ($handlerInstance->isPropagationStopped()) {
                break;
            }
        }
        return [$isHandled, $response];
    }
}
