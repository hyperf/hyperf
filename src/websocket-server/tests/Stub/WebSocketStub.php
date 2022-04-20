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
namespace HyperfTest\WebSocketServer\Stub;

use Hyperf\Contract\OnOpenInterface;
use Hyperf\Utils\Coroutine;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketStub implements OnOpenInterface
{
    public static $coroutineId = 0;

    public function onOpen(Response|Server $server, Request $request): void
    {
        static::$coroutineId = Coroutine::id();
    }
}
