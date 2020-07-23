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
namespace Hyperf\Server\Event;

use Swoole\Coroutine\Server;

class CoroutineServerStart
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var object|Server
     */
    public $server;

    /**
     * @var array
     */
    public $serverConfig;

    public function __construct(string $name, $server, array $serverConfig)
    {
        $this->name = $name;
        $this->server = $server;
        $this->serverConfig = $serverConfig;
    }
}
