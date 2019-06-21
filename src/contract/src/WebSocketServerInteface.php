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

namespace Hyperf\Contract;

use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;

interface WebSocketServerInteface
{
    public function onOpen(Server $server, Request $request): void;

    public function onMessage(Server $server, Frame $frame): void;

    public function onClose(Server $server, int $fd, int $reactorId): void;
}
