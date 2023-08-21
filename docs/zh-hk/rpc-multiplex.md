# 基於多路複用的 RPC 組件

本組件基於 `TCP` 協議，多路複用的設計借鑑於 `AMQP` 組件。

## 安裝

```
composer require hyperf/rpc-multiplex
```

## Server 配置

修改 `config/autoload/server.php` 配置文件，以下配置刪除了不相干的配置。

`settings` 設置中，分包規則不允許修改，只可以修改 `package_max_length`，此配置需要 `Server` 和 `Client` 保持一致。

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
                // 多路複用下，避免跨協程 Socket 跨協程多寫報錯
                'send_channel_capacity' => 65535,
            ],
        ],
    ],
];

```

創建 `RpcService`

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
            // 這個消費者要從哪個服務中心獲取節點信息，如不配置則不會從服務中心獲取節點信息
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
                    // 包體最大值，若小於 Server 返回的數據大小，則會拋出異常，故儘量控制包體大小
                    'package_max_length' => 1024 * 1024 * 2,
                ],
                // 重試次數，默認值為 2
                'retry_count' => 2,
                // 重試間隔，毫秒
                'retry_interval' => 100,
                // 多路複用客户端數量
                'client_count' => 4,
                // 心跳間隔 非 numeric 表示不開啓心跳
                'heartbeat' => 30,
            ],
        ],
    ],
];

```

### 註冊中心

如果需要使用註冊中心，則需要手動添加以下監聽器

```php
<?php
return [
    Hyperf\RpcMultiplex\Listener\RegisterServiceListener::class,
];
```

## 使用

- 定義接口

比如我們需要設計一個發送短信的 RPC 服務

```php
<?php

declare(strict_types=1);

namespace RPC\Push;

interface PushInterface
{
    public function sendSmsCode(string $mobile, string $code): bool;
}

```

- 服務端實現接口

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
        // 實際處理邏輯
        return true;
    }
}
```

- 客户端調用

```php
<?php

use Hyperf\Context\ApplicationContext;
use RPC\Push\PushInterface;

ApplicationContext::getContainer()->get(PushInterface::class)->sendSmsCode('18600000001', '6666');

```
