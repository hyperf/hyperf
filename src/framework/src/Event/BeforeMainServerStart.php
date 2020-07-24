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
namespace Hyperf\Framework\Event;

use Swoole\Http\Server as SwooleHttpServer;
use Swoole\Server as SwooleServer;

class BeforeMainServerStart
{
    /**
     * @var object|SwooleHttpServer|SwooleServer
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
