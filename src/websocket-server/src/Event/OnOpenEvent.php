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

namespace Hyperf\WebSocketServer\Event;

use Swoole\Http\Request;
use Swoole\WebSocket\Server;

class OnOpenEvent
{
    /**
     * @var string
     */
    public $class;

    /**
     * @var Server
     */
    public $server;

    /**
     * @var Request
     */
    public $request;
}
