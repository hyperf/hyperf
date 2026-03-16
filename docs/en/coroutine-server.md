# Coroutine Style Server

Hyperf uses [Swoole asynchronous style](https://wiki.swoole.com/#/server/init) by default, which is a multi-process model and custom processes are running in separate processes.

> This type will run in single-process mode when using SWOOLE_BASE and not using custom processes. You can check the Swoole official documentation for details.

Hyperf also provides a coroutine style service, which is a single-process model, and all custom processes will run in coroutine mode, without creating separate processes.

Both styles can be selected as needed, **but it is not recommended to switch to an existing service without any consideration**.

## Configuration

Modify the `autoload/server.php` configuration file and set `type` to `Hyperf\Server\CoroutineServer::class` to start the coroutine style.

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

1. Because of the coroutine style and asynchronous style, there are differences in the corresponding callbacks, so it needs to be used as needed

For example, `onReceive` callback, the asynchronous style is `Swoole\Server`, and the coroutine style is `Swoole\Coroutine\Server\Connection`.

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