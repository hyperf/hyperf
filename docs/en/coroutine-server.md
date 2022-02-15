# 协程风格服务

Hyperf 默认使用的是 [Swoole 异步风格](https://wiki.swoole.com/#/server/init)，此类型为多进程模型，自定义进程为单独进程运行。

> 此类型在使用 SWOOLE_BASE 且不使用自定义进程时，会以单进程模型来跑，具体可查看 Swoole 官方文档。

Hyperf 还提供了协程风格服务，此类型为单进程模型，所有的自定义进程会全部以协程模式来跑，不会创建单独的进程。

此两种风格，可以按需选择，**但不推荐将已经在正常使用的服务，进行无脑切换**。

## 配置

修改 `autoload/server.php` 配置文件，设置 `type` 为 `Hyperf\Server\CoroutineServer::class` 即可启动协程风格。

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

1. 因为协程风格和异步风格，在对应的回调上存在差异，所以需要按需使用

例如 `onReceive` 回调，异步风格是 `Swoole\Server`，协程风格是 `Swoole\Coroutine\Server\Connection`。

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

2. 中间件所在协程只有在 `onClose` 时，才会结束

因为 `Hyperf` 的数据库实例，是在协程销毁时，返还给连接池，所以如果在 `WebSocket` 的中间件中使用 `Database` 就会导致连接池内的连接无法正常归还。
