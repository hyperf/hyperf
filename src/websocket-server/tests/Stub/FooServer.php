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

namespace HyperfTest\WebSocketServer\Stub;

use Hyperf\Engine\Http\Server as HttpServer;
use Swow\Http\Server\Connection;

class FooServer
{
    public function getServer(Connection|int $id): HttpServer|int
    {
        return $id;
    }
}
