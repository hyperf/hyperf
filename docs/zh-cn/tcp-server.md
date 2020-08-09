# TCP 服务

框架默认提供创建 `TCP/UDP` 服务的能力。只需要进行简易的配置，便可使用。

## 使用 TCP 服务

> UDP 服务请自行修改配置

### 创建 TcpServer 类

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

### 创建对应配置

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

return [
    // 删除其他不相关的配置项
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

### 实现客户端

```php
<?php

$client = new \Swoole\Client(SWOOLE_SOCK_TCP);
$client->connect('127.0.0.1', 9504);
$client->send('Hello World.');
$ret = $client->recv(); // recv:Hello World.
```

## 事件

|          事件           |       备注       |
| :---------------------: | :--------------: |
| SwooleEvent::ON_CONNECT | 监听连接进入事件 |
| SwooleEvent::ON_RECEIVE | 监听数据接收事件 |
|  SwooleEvent::ON_CLOSE  | 监听连接关闭事件 |
