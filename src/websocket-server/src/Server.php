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

namespace Hyperf\WebSocketServer;

use Hyperf\Contract\MiddlewareInitializerInterface;
use Hyperf\Contract\OnRequestInterface;
use Hyperf\Contract\WebSocketServerInteface;
use Hyperf\Dispatcher\HttpDispatcher;
use Psr\Container\ContainerInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Websocket\Frame;

class Server implements MiddlewareInitializerInterface, OnRequestInterface, WebSocketServerInteface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var CoreMiddleware
     */
    protected $coreMiddleware;

    /**
     * @var string
     */
    protected $serverName;

    /**
     * @var HttpDispatcher
     */
    protected $dispatcher;

    public function __construct(ContainerInterface $container, string $serverName)
    {
        $this->container = $container;
        $this->serverName = $serverName;
        $this->dispatcher = $container->get(HttpDispatcher::class);
    }

    public function initCoreMiddleware(string $serverName): void
    {
        $this->serverName = $serverName;
        $this->coreMiddleware = new CoreMiddleware($this->container, $serverName);
    }

    public function onRequest(SwooleRequest $request, SwooleResponse $response): void
    {
    }

    public function onOpen(\Swoole\WebSocket\Server $server, SwooleRequest $request): void
    {
        // TODO: Implement onOpen() method.
    }

    public function onMessage(\Swoole\WebSocket\Server $server, Frame $frame): void
    {
        // TODO: Implement onMessage() method.
    }

    public function onClose(\Swoole\WebSocket\Server $server, int $fd, int $reactorId): void
    {
        // TODO: Implement onClose() method.
    }
}
