<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\WebSocketServer;

use Hyperf\Contract\MiddlewareInitializerInterface;

class Server implements MiddlewareInitializerInterface
{
    public function initCoreMiddleware(string $serverName): void
    {
        // TODO: Implement initCoreMiddleware() method.
    }
}
