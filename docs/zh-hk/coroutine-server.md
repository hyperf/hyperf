# 協程風格服務

Hyperf 默認使用的是 [Swoole 異步風格](https://wiki.swoole.com/#/server/init)，此類型為多進程模型，自定義進程為單獨進程運行。

> 此類型在使用 SWOOLE_BASE 且不使用自定義進程時，會以單進程模型來跑，具體可查看 Swoole 官方文檔。

Hyperf 還提供了協程風格服務，此類型為單進程模型，所有的自定義進程會全部以協程模式來跑，不會創建單獨的進程。

此兩種風格，可以按需選擇，**但不推薦將已經在正常使用的服務，進行無腦切換**。

## 配置

修改 `autoload/server.php` 配置文件，設置 `type` 為 `Hyperf\Server\CoroutineServer::class` 即可啟動協程風格。

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

1. 因為協程風格和異步風格，在對應的回調上存在差異，所以需要按需使用

例如 `onReceive` 回調，異步風格是 `Swoole\Server`，協程風格是 `Swoole\Coroutine\Server\Connection`。

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

2. 中間件所在協程只有在 `onClose` 時，才會結束

因為 `Hyperf` 的數據庫實例，是在協程銷燬時，返還給連接池，所以如果在 `WebSocket` 的中間件中使用 `Database` 就會導致連接池內的連接無法正常歸還。
