# 基于多路复用的 RPC 组件

本组件基于 `TCP` 协议，多路复用的设计借鉴于 `AMQP` 组件。

## 安装

```
composer require hyperf/rpc-multiplex
```

## Server 配置

修改 `config/autoload/server.php` 配置文件，以下配置删除了不相干的配置。

`settings` 设置中，分包规则不允许修改，只可以修改 `package_max_length`，此配置需要 `Server` 和 `Client` 保持一致。

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;

return [
    'servers' => [
        [
            'name' => 'rpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [Hyperf\RpcMultiplex\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => 1024 * 1024 * 2,
            ],
            'options' => [
                // 多路复用下，避免跨协程 Socket 跨协程多写报错
                'send_channel_capacity' => 65535,
            ],
        ],
    ],
];

```

创建 `RpcService`

```php
<?php

namespace App\RPC;

use App\JsonRpc\CalculatorServiceInterface;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", server: "rpc", protocol: Constant::PROTOCOL_DEFAULT)]
class CalculatorService implements CalculatorServiceInterface
{
}

```

## 客户端配置

修改 `config/autoload/services.php` 配置文件

```php
<?php

declare(strict_types=1);

return [
    'consumers' => [
        [
            'name' => 'CalculatorService',
            'service' => App\JsonRpc\CalculatorServiceInterface::class,
            'id' => App\JsonRpc\CalculatorServiceInterface::class,
            'protocol' => Hyperf\RpcMultiplex\Constant::PROTOCOL_DEFAULT,
            'load_balancer' => 'random',
            // 这个消费者要从哪个服务中心获取节点信息，如不配置则不会从服务中心获取节点信息
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9502],
            ],
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // 包体最大值，若小于 Server 返回的数据大小，则会抛出异常，故尽量控制包体大小
                    'package_max_length' => 1024 * 1024 * 2,
                ],
                // 重试次数，默认值为 2
                'retry_count' => 2,
                // 重试间隔，毫秒
                'retry_interval' => 100,
                // 多路复用客户端数量
                'client_count' => 4,
                // 心跳间隔 非 numeric 表示不开启心跳
                'heartbeat' => 30,
            ],
        ],
    ],
];

```

### 注册中心

如果需要使用注册中心，则需要手动添加以下监听器

```php
<?php
return [
    Hyperf\RpcMultiplex\Listener\RegisterServiceListener::class,
];
```

## 使用

- 定义接口

比如我们需要设计一个发送短信的 RPC 服务

```php
<?php

declare(strict_types=1);

namespace RPC\Push;

interface PushInterface
{
    public function sendSmsCode(string $mobile, string $code): bool;
}

```

- 服务端实现接口

```php
<?php

declare(strict_types=1);

namespace App\RPC;

use RPC\Push\PushInterface;
use Hyperf\RpcMultiplex\Constant;
use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: PushInterface::class, server: 'rpc', protocol: Constant::PROTOCOL_DEFAULT)]
class PushService implements PushInterface
{
    public function sendSmsCode(string $mobile, string $code): bool
    {
        // 实际处理逻辑
        return true;
    }
}
```

- 客户端调用

```php
<?php

use Hyperf\Context\ApplicationContext;
use RPC\Push\PushInterface;

ApplicationContext::getContainer()->get(PushInterface::class)->sendSmsCode('18600000001', '6666');

```
