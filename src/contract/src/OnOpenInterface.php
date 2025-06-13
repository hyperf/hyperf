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

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server;
use Swow\Psr7\Server\ServerConnection;

interface OnOpenInterface
{
    /**
     * @param Response|Server|ServerConnection $server
     * @param Request $request
     */
    public function onOpen($server, $request): void;
}
