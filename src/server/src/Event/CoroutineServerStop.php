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

class CoroutineServerStop
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var object|Server
     */
    public $server;

    public function __construct(string $name, $server)
    {
        $this->name = $name;
        $this->server = $server;
    }
}
