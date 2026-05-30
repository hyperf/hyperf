# Coroutine-style Service

Hyperf uses [Swoole asynchronous style](https://wiki.swoole.com/#/server/init) by default. This type is a multi-process model, and custom processes run as separate processes.

> This type will run as a single-process model when using SWOOLE_BASE and not using custom processes. For details, please refer to the official Swoole documentation.

Hyperf also provides a coroutine-style service. This type is a single-process model. All custom processes will run in coroutine mode, and no separate processes will be created.

These two styles can be chosen as needed, **but it is not recommended to blindly switch services that are already in normal use**.

## Configuration

Modify the `autoload/server.php` configuration file and set `type` to `Hyperf\Server\CoroutineServer::class` to start in coroutine style.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;

return [
    'type' => Hyperf\Server\CoroutineServer::class,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
];
```

## WebSocket

1. Because there are differences in the corresponding callbacks between the coroutine style and the asynchronous style, you need to use them as needed.

For example, for the `onReceive` callback, the asynchronous style uses `Swoole\Server`, while the coroutine style uses `Swoole\Coroutine\Server\Connection`.

```php
<?php

declare(strict_types=1);

namespace Hyperf\Contract;

use Swoole\Coroutine\Server\Connection;
use Swoole\Server as SwooleServer;

interface OnReceiveInterface
{
    /**
     * @param Connection|SwooleServer $server
     */
    public function onReceive($server, int $fd, int $reactorId, string $data): void;
}
```

2. The coroutine where the middleware resides only ends upon `onClose`.

Because the `Hyperf` database instance is returned to the connection pool when the coroutine is destroyed, using `Database` in `WebSocket` middleware will result in connections in the connection pool not being returned normally.
