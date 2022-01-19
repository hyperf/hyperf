# Coroutine style service

Hyperf uses the [Swoole asynchronous style](https://wiki.swoole.com/#/server/init) by default. This type is a multi-process model, and the custom process runs as a separate process.

> When this type uses SWOOLE_BASE and does not use a custom process, it will run in a single-process model. For details, see the official Swoole documentation.

Hyperf also provides coroutine style services. This type is a single process model. All custom processes will run in coroutine mode, and no separate process will be created.

These two styles can be selected as needed, **but it is not recommended to switch services that are already in normal use without thinking**.

## configure

Modify the `autoload/server.php` configuration file and set the `type` to `Hyperf\Server\CoroutineServer::class` to start the coroutine style.

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

##WebSocket

1. Because there are differences in the corresponding callbacks between the coroutine style and the asynchronous style, it needs to be used as needed

For example, `onReceive` callback, asynchronous style is `Swoole\Server`, coroutine style is `Swoole\Coroutine\Server\Connection`.

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

2. The coroutine where the middleware is located will only end when `onClose`

Because the database instance of `Hyperf` is returned to the connection pool when the coroutine is destroyed, if `Database` is used in the middleware of `WebSocket`, the connection in the connection pool will not be returned normally.
