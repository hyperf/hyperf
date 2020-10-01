# TCP 服務

框架預設提供建立 `TCP/UDP` 服務的能力。只需要進行簡易的配置，便可使用。

## 使用 TCP 服務

> UDP 服務請自行修改配置

### 建立 TcpServer 類

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnReceiveInterface;

class TcpServer implements OnReceiveInterface
{
    public function onReceive($server, int $fd, int $fromId, string $data): void
    {
        $server->send($fd, 'recv:' . $data);
    }
}

```

### 建立對應配置

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

return [
    // 刪除其他不相關的配置項
    'servers' => [
        [
            'name' => 'tcp',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_RECEIVE => [App\Controller\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                // 按需配置
            ],
        ],
    ],
];

```

### 實現客戶端

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## 事件

|          事件           |       備註       |
| :---------------------: | :--------------: |
| SwooleEvent::ON_CONNECT | 監聽連線進入事件 |
| SwooleEvent::ON_RECEIVE | 監聽資料接收事件 |
|  SwooleEvent::ON_CLOSE  | 監聽連線關閉事件 |
