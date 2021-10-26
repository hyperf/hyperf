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
namespace Hyperf\Validation\Middleware;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Utils\Context;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\UnauthorizedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $implements = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        Context::set(ServerRequestInterface::class, $request);

        if ($this->shouldHandle($dispatched)) {
            try {
                [$requestHandler, $method] = $this->prepareHandler($dispatched->handler->callback);
                if ($method) {
                    $reflectionMethod = ReflectionManager::reflectMethod($requestHandler, $method);
                    $parameters = $reflectionMethod->getParameters();
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() === null) {
                            continue;
                        }
                        $classname = $parameter->getType()->getName();
                        if ($this->isImplementedValidatesWhenResolved($classname)) {
                            /** @var \Hyperf\Validation\Contract\ValidatesWhenResolved $formRequest */
                            $formRequest = $this->container->get($classname);
                            $formRequest->validateResolved();
                        }
                    }
                }
            } catch (UnauthorizedException $exception) {
                return $this->handleUnauthorizedException($exception);
            }
        }

        return $handler->handle($request);
    }

    public function isImplementedValidatesWhenResolved(string $classname): bool
    {
        if (! isset($this->implements[$classname]) && class_exists($classname)) {
            $implements = class_implements($classname);
            $this->implements[$classname] = in_array(ValidatesWhenResolved::class, $implements, true);
        }
        return $this->implements[$classname] ?? false;
    }

    /**
     * @param UnauthorizedException $exception Keep this argument here even this argument is unused in the method,
     *                                         maybe the user need the details of exception when rewrite this method
     */
    protected function handleUnauthorizedException(UnauthorizedException $exception): ResponseInterface
    {
        return Context::override(ResponseInterface::class, function (ResponseInterface $response) {
            return $response->withStatus(403);
        });
    }

    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && ! $dispatched->handler->callback instanceof Closure;
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     * @param array|string $handler
     */
    protected function prepareHandler($handler): array
    {
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new \RuntimeException('Handler not exist.');
    }
}
