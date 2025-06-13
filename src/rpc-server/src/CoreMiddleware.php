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

namespace Hyperf\RpcServer;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Protocol;
use Hyperf\RpcServer\Router\DispatcherFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

use function Hyperf\Support\make;

class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    protected Protocol $protocol;

    public function __construct(ContainerInterface $container, Protocol $protocol, string $serverName)
    {
        $this->protocol = $protocol;
        parent::__construct($container, $serverName);
    }

    public function getNormalizer(): NormalizerInterface
    {
        return $this->protocol->getNormalizer();
    }

    protected function createDispatcher(string $serverName): Dispatcher
    {
        $factory = make(DispatcherFactory::class, [
            'pathGenerator' => $this->protocol->getPathGenerator(),
        ]);
        return $factory->getDispatcher($serverName);
    }

    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request): mixed
    {
        if ($dispatched->handler->callback instanceof Closure) {
            $callback = $dispatched->handler->callback;
            $response = $callback();
        } else {
            [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);
            $controllerInstance = $this->container->get($controller);
            if (! method_exists($controller, $action)) {
                // Route found, but the handler does not exist.
                return $this->response()->withStatus(500)->withBody(new SwooleStream('Method of class does not exist.'));
            }
            $parameters = $this->parseMethodParameters($controller, $action, $request->getParsedBody());
            $response = $controllerInstance->{$action}(...$parameters);
        }
        return $response;
    }
}
