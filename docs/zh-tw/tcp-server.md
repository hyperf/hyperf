# TCP/UDP 服務

框架預設提供建立 `TCP/UDP` 服務的能力。只需要進行簡易的配置，便可使用。

## 使用 TCP 服務

### 建立 TcpServer 類

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnReceiveInterface;

class TcpServer implements OnReceiveInterface
{
    public function onReceive($server, int $fd, int $reactorId, string $data): void
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
use Hyperf\Server\Event;

return [
    // 以下刪除了其他不相關的配置項
    'servers' => [
        [
            'name' => 'tcp',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [App\Controller\TcpServer::class, 'onReceive'],
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

## 使用 UDP 服務

> Docker 預設使用 TCP 協議來通訊，如果你需要使用 UDP 協議，你需要透過配置 Docker 網路來實現。  
```shell
docker run -p 9502:9502/udp <image-name>
```

### 建立 UdpServer 類

> 如果沒有 OnPacketInterface 介面檔案，則可以不實現此介面，執行結果與實現介面一致，只要保證配置正確即可。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnPacketInterface;

class UdpServer implements OnPacketInterface
{
    public function onPacket($server, $data, $clientInfo): void
    {
        var_dump($clientInfo);
        $server->sendto($clientInfo['address'], $clientInfo['port'], 'Server：' . $data);
    }
}

```

### 建立對應配置

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 以下刪除了其他不相關的配置項
    'servers' => [
        [
            'name' => 'udp',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9505,
            'sock_type' => SWOOLE_SOCK_UDP,
            'callbacks' => [
                Event::ON_PACKET => [App\Controller\UdpServer::class, 'onPacket'],
            ],
            'settings' => [
                // 按需配置
            ],
        ],
    ],
];

```

## 事件

|       事件        |       備註       |
| :---------------: | :--------------: |
| Event::ON_CONNECT | 監聽連線進入事件 |
| Event::ON_RECEIVE | 監聽資料接收事件 |
|  Event::ON_CLOSE  | 監聽連線關閉事件 |
| Event::ON_PACKET  | UDP 資料接收事件 |

