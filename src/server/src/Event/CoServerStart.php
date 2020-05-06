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
namespace Hyperf\Server\Event;

use Hyperf\Server\ServerConfig;
use Swoole\Coroutine\Server;

class CoServerStart
{
    /**
     * @var object|Server
     */
    public $server;

    /**
     * @var array
     */
    public $serverConfig;

    public function __construct($server, array $serverConfig)
    {
        $this->server = $server;
        $this->serverConfig = $serverConfig;
    }
}
