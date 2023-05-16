# TCP/UDP 服务

框架默认提供创建 `TCP/UDP` 服务的能力。只需要进行简易的配置，便可使用。

## 使用 TCP 服务

### 创建 TcpServer 类

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

### 创建对应配置

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 以下删除了其他不相关的配置项
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

### 实现客户端

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## 使用 UDP 服务

> Docker 默认使用 TCP 协议来通信，如果你需要使用 UDP 协议，你需要通过配置 Docker 网络来实现。  
```shell
docker run -p 9502:9502/udp <image-name>
```

### 创建 UdpServer 类

> 如果没有 OnPacketInterface 接口文件，则可以不实现此接口，运行结果与实现接口一致，只要保证配置正确即可。

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

### 创建对应配置

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 以下删除了其他不相关的配置项
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

|       事件        |       备注       |
| :---------------: | :--------------: |
| Event::ON_CONNECT | 监听连接进入事件 |
| Event::ON_RECEIVE | 监听数据接收事件 |
|  Event::ON_CLOSE  | 监听连接关闭事件 |
| Event::ON_PACKET  | UDP 数据接收事件 |

