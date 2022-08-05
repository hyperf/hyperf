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
namespace Hyperf\Contract;

use Swoole\Coroutine\Server\Connection;
use Swoole\Server as SwooleServer;
use Swow\Socket;

interface OnReceiveInterface
{
    public function onReceive(\Swoole\Coroutine\Server\Connection|\Socket|SwooleServer $server, int $fd, int $reactorId, string $data): void;
}
