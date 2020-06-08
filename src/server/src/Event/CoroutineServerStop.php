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

use Swoole\Coroutine\Server;

class CoroutineServerStop
{
    /**
     * @var object|Server
     */
    public $server;

    public function __construct($server)
    {
        $this->server = $server;
    }
}
