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

use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

interface OnMessageInterface
{
    /**
     * @param Frame $frame
     */
    public function onMessage(\Swoole\Http\Response|\Swoole\WebSocket\Server $server, $frame): void;
}
