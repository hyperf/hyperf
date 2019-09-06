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

namespace Hyperf\Validation\Middleware;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Validation\Contracts\Validation\ValidatesWhenResolved;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    /**
     * @var
     */
    protected $dispatcher;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $factory = $this->container->get(DispatcherFactory::class);
        $this->dispatcher = $factory->getDispatcher('http');
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $uri = $request->getUri();
        /**
         * @var array
         *            Returns array with one of the following formats:
         *            [self::NOT_FOUND]
         *            [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
         *            [self::FOUND, $handler, ['varName' => 'value', ...]]
         */
        $routes = $this->dispatcher->dispatch($request->getMethod(), $uri->getPath());
        switch ($routes[0]) {
            case Dispatcher::NOT_FOUND:
            case Dispatcher::METHOD_NOT_ALLOWED:
                break;
            case Dispatcher::FOUND:
                $this->handleFound($routes, $request);
                break;
        }

        return $handler->handle($request);
    }

    /**
     * @param array|string $handler
     */
    protected function prepareHandler($handler): array
    {
        if (is_string($handler)) {
            if (strpos($handler, '@') !== false) {
                return explode('@', $handler);
            }

            return explode('::', $handler);
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new \RuntimeException('Handler not exist.');
    }

    /**
     * Handle the response when found.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleFound(array $routes, ServerRequestInterface $request)
    {
        if ($routes[1] instanceof Closure) {
            // Do nothing
        } else {
            [$controller, $action] = $this->prepareHandler($routes[1]);
            if (! method_exists($controller, $action)) {
                // Route found, but the handler does not exist.
                return $this->response()->withStatus(500)->withBody(new SwooleStream('Method of class does not exist.'));
            }
            $params = $this->parseParameters($controller, $action, $routes[2]);
            foreach ($params as $param) {
                if ($param instanceof ValidatesWhenResolved) {
                    $param->validateResolved();
                }
            }
        }
    }

    /**
     * Get response instance from context.
     */
    protected function response(): ResponseInterface
    {
        return Context::get(ResponseInterface::class);
    }

    /**
     * Parse the parameters of method definitions, and then bind the specified arguments or
     * get the value from DI container, combine to a argument array that should be injected
     * and return the array.
     */
    protected function parseParameters(string $controller, string $action, array $arguments): array
    {
        $injections = [];
        $this->container->get(MethodDefinitionCollectorInterface::class);

        $definitions = $this->container->get(MethodDefinitionCollectorInterface::class)->getParameters($controller, $action);
        foreach ($definitions ?? [] as $pos => $definition) {
            $value = $arguments[$pos] ?? $arguments[$definition->getMeta('name')] ?? null;
            if ($value === null) {
                if ($definition->getMeta('defaultValueAvailable')) {
                    $injections[] = $definition->getMeta('defaultValue');
                } elseif ($definition->allowsNull()) {
                    $injections[] = null;
                } elseif ($this->container->has($definition->getName())) {
                    $injections[] = $this->container->get($definition->getName());
                } else {
                    throw new \InvalidArgumentException("Parameter '{$definition->getMeta('name')}' "
                        . "of {$controller}::{$action} should not be null");
                }
            } else {
                $injections[] = $this->container->get(NormalizerInterface::class)->denormalize($value, $definition->getName());
            }
        }

        return $injections;
    }
}
