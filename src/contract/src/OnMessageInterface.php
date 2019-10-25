<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Contract;

use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server;

interface OnMessageInterface
{
    public function onMessage(Server $server, Frame $frame): void;
}
