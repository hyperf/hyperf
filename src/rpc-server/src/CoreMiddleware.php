<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcServer;

use FastRoute\Dispatcher;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\RpcServer\Router\DispatcherFactory;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Core middleware of Hyperf, main responsibility is use to handle route info
 * and then delegate to the specified handler (which is Controller) to handle the request,
 * generate a response object and delegate to next middleware (Because this middleware is the
 * core middleware, then the next middleware also means it's the previous middlewares object) .
 */
class CoreMiddleware implements \Psr\Http\Server\MiddlewareInterface
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
        /**
         * @var array
         *            Returns array with one of the following formats:
         *            [self::NOT_FOUND]
         *            [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
         *            [self::FOUND, $handler, ['varName' => 'value', ...]]
         */
        $routes = $this->dispatcher->dispatch('GET', $request->getUri()->getPath());
        switch ($routes[0]) {
            case Dispatcher::NOT_FOUND:
                $response = $this->responseNotFound();
                break;
            case Dispatcher::FOUND:
                [$targetClass, $targetMethod] = $this->prepareHandler($routes[1]);
                $targetInstance = $this->container->get($targetClass);
                if (! method_exists($targetClass, $targetMethod)) {
                    $response = $this->responseNotFound();
                    break;
                }
                $parameters = $this->parseParameters($targetClass, $targetMethod, $request->getParsedBody() ?? []);
                $response = $targetInstance->{$targetMethod}(...$parameters);
                if (! $response instanceof ResponseInterface) {
                    $response = $this->transferToResponse($response);
                }
                break;
        }
        return $response;
    }

    /**
     * @param array|string $handler
     */
    protected function prepareHandler($handler): array
    {
        if (is_string($handler)) {
            return explode('@', $handler);
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new \RuntimeException('Handler not exist.');
    }

    /**
     * Transfer the non-standard response content to a standard response object.
     *
     * @param array|string $response
     */
    protected function transferToResponse($response): ResponseInterface
    {
        if (is_string($response)) {
            return $this->response()->withBody(new SwooleStream($response));
        }

        if (is_array($response)) {
            return $this->response()
                ->withBody(new SwooleStream(json_encode($response, JSON_UNESCAPED_UNICODE)));
        }

        return $this->response()->withBody(new SwooleStream((string) $response));
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
                        return (int) $arguments[$definition['name']] ?? null;
                        break;
                    case 'float':
                        return (float) $arguments[$definition['name']] ?? null;
                        break;
                    case 'bool':
                        return (bool) $arguments[$definition['name']] ?? null;
                        break;
                    case 'string':
                        return (string) $arguments[$definition['name']] ?? null;
                        break;
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

    private function responseNotFound(): ResponseInterface
    {
        return $this->response()->withStatus(404, 'Method not found.');
    }
}
