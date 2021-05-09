# 協程風格服務

Hyperf 預設使用的是 [Swoole 非同步風格](https://wiki.swoole.com/#/server/init)，此型別為多程序模型，自定義程序為單獨程序執行。

> 此型別在使用 SWOOLE_BASE 且不使用自定義程序時，會以單程序模型來跑，具體可檢視 Swoole 官方文件。

Hyperf 還提供了協程風格服務，此型別為單程序模型，所有的自定義程序會全部以協程模式來跑，不會建立單獨的程序。

此兩種風格，可以按需選擇，**但不推薦將已經在正常使用的服務，進行無腦切換**。

## 配置

修改 `autoload/server.php` 配置檔案，設定 `type` 為 `Hyperf\Server\CoroutineServer::class` 即可啟動協程風格。

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

1. 因為協程風格和非同步風格，在對應的回撥上存在差異，所以需要按需使用

例如 `onReceive` 回撥，非同步風格是 `Swoole\Server`，協程風格是 `Swoole\Coroutine\Server\Connection`。

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

2. 中介軟體所在協程只有在 `onClose` 時，才會結束

因為 `Hyperf` 的資料庫例項，是在協程銷燬時，返還給連線池，所以如果在 `WebSocket` 的中介軟體中使用 `Database` 就會導致連線池內的連線無法正常歸還。
