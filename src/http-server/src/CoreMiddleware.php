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

namespace Hyperf\HttpServer;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Core middleware of Hyperf, main responsibility is use to handle route info
 * and then delegate to the specified handler (which is Controller) to handle the request,
 * generate a response object and delegate to next middleware (Because this middleware is the
 * core middleware, then the next middleware also means it's the previous middlewares object) .
 */
class CoreMiddleware implements MiddlewareInterface
{
    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container, string $serverName)
    {
        $this->container = $container;
        $factory = $container->get(DispatcherFactory::class);
        $this->dispatcher = $factory->getDispatcher($serverName);
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
                $response = $this->handleNotFound($request);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = $this->handleMethodNotAllowed($routes, $request);
                break;
            case Dispatcher::FOUND:
                $response = $this->handleFound($routes, $request);
                break;
        }
        if (! $response instanceof ResponseInterface) {
            $response = $this->transferToResponse($response, $request);
        }
        return $response->withAddedHeader('Server', 'Hyperf');
    }

    /**
     * Handle the response when found.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleFound(array $routes, ServerRequestInterface $request)
    {
        if ($routes[1] instanceof Closure) {
            $response = call($routes[1]);
        } else {
            [$controller, $action] = $this->prepareHandler($routes[1]);
            $controllerInstance = $this->container->get($controller);
            if (! method_exists($controller, $action)) {
                // Route found, but the handler does not exist.
                return $this->response()->withStatus(500)->withBody(new SwooleStream('Method of class does not exist.'));
            }
            $parameters = $this->parseParameters($controller, $action, $routes[2]);
            $response = $controllerInstance->{$action}(...$parameters);
        }
        return $response;
    }

    /**
     * Handle the response when cannot found any routes.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        return $this->response()->withStatus(404);
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $routes, ServerRequestInterface $request)
    {
        return $this->response()->withStatus(405)->withAddedHeader('Allow', implode(', ', $routes[1]));
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
     * Transfer the non-standard response content to a standard response object.
     *
     * @param array|string|Jsonable|Arrayable $response
     */
    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream($response));
        }

        if (is_array($response) || $response instanceof Arrayable) {
            if ($response instanceof Arrayable) {
                $response = $response->toArray();
            }
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream(json_encode($response, JSON_UNESCAPED_UNICODE)));
        }

        if ($response instanceof Jsonable) {
            return $this->response()
                ->withAddedHeader('content-type', 'application/json')
                ->withBody(new SwooleStream((string) $response));
        }

        return $this->response()->withAddedHeader('content-type', 'text/plain')->withBody(new SwooleStream((string) $response));
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
        $definitions = MethodDefinitionCollector::getOrParse($controller, $action);
        foreach ($definitions ?? [] as $definition) {
            if (! is_array($definition)) {
                throw new \RuntimeException('Invalid method definition.');
            }
            if (! isset($definition['type']) || ! isset($definition['name'])) {
                $injections[] = null;
                continue;
            }
            $injections[] = value(function () use ($definition, $arguments) {
                switch ($definition['type']) {
                    case 'int':
                    case 'float':
                    case 'bool':
                    case 'string':
                    case 'array':
                        return $this->getValue($definition, $arguments);
                    case 'object':
                        if (! $this->container->has($definition['ref']) && ! $definition['allowsNull']) {
                            throw new \RuntimeException(sprintf('Argument %s invalid, object %s not found.', $definition['name'], $definition['ref']));
                        }
                        return $this->container->get($definition['ref']);
                        break;
                    default:
                        throw new \RuntimeException('Invalid method definition detected.');
                }
            });
        }

        return $injections;
    }

    protected function getValue($definition, $arguments)
    {
        if (isset($arguments[$definition['name']])) {
            $value = $arguments[$definition['name']];
            switch ($definition['type']) {
                case 'int':
                    return (int) $value;
                case 'string':
                    return (string) $value;
                case 'float':
                    return (float) $value;
                case 'array':
                    return (array) $value;
                case 'bool':
                    return (bool) $value;
                default:
                    return $value;
            }
        }

        if (isset($definition['defaultValue'])) {
            return $definition['defaultValue'];
        }

        if (isset($definition['allowsNull'])) {
            return null;
        }

        throw new \RuntimeException('Invalid method definition detected.');
    }
}
