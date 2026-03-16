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

namespace Hyperf\ExceptionHandler;

use Hyperf\Context\ResponseContext;
use Hyperf\Dispatcher\AbstractDispatcher;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Throwable;

class ExceptionHandlerDispatcher extends AbstractDispatcher
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dispatch(...$params)
    {
        /**
         * @param Throwable $throwable
         * @param string[] $handlers
         */
        [$throwable, $handlers] = $params;
        $response = ResponseContext::get();

        foreach ($handlers as $handler) {
            if (! $this->container->has($handler)) {
                throw new InvalidArgumentException(sprintf('Invalid exception handler %s.', $handler));
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
}
