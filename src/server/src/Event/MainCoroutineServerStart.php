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

use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Coroutine\Server;

class MainCoroutineServerStart
{
    /**
     * @param HttpServer|mixed|Server $server
     */
    public function __construct(public string $name, public mixed $server, public array $serverConfig)
    {
    }
}
