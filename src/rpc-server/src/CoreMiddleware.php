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

namespace Hyperf\RpcServer;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\RpcServer\Router\DispatcherFactory;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {@inheritdoc}
 */
class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    /**
     * {@inheritdoc}
     */
    protected function createDispatcher(string $serverName): Dispatcher
    {
        $factory = $this->container->get(DispatcherFactory::class);
        return $factory->getDispatcher($serverName);
    }

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
            $parameters = $this->parseParameters($controller, $action, $request->getParsedBody());
            $response = $controllerInstance->{$action}(...$parameters);
        }
        return $response;
    }
}
