# 服務註冊

在進行服務拆分之後，服務的數量會變得非常多，而每個服務又可能會有非常多的集羣節點來提供服務，那麼為保障系統的正常運行，必然需要有一箇中心化的組件完成對各個服務的整合，即將分散於各處的服務進行彙總，彙總的信息可以是提供服務的組件名稱、地址、數量等，每個組件擁有一個監聽設備，當本組件內的某個服務的狀態變化時報告至中心化的組件進行狀態的更新。服務的調用方在請求某項服務時首先到中心化組件獲取可提供該項服務的組件信息（IP、端口等），通過默認或自定義的策略選擇該服務的某一提供者進行訪問，實現服務的調用。那麼這個中心化的組件我們一般稱之為 `服務中心`，在 Hyperf 裏，我們實現了以 `Consul` 和 `Nacos` 為服務中心的組件支持，後續將適配更多的服務中心。

# 安裝

## 安裝統一接入層

```bash
composer require hyperf/service-governance
```

## 選擇安裝對應的適配器

服務註冊支持 `Consul` 和 `Nacos`，按需引入對應的適配器組件

- Consul

```shell
composer require hyperf/service-governance-consul
```

- Nacos

```shell
composer require hyperf/service-governance-nacos
```

# 配置文件

組件由 `config/autoload/services.php` 配置文件來驅動，配置文件如下：

```php
return [
    'enable' => [
        // 開啟服務發現
        'discovery' => true,
        // 開啟服務註冊
        'register' => true,
    ],
    // 服務消費者相關配置
    'consumers' => [],
    // 服務提供者相關配置
    'providers' => [],
    // 服務驅動相關配置
    'drivers' => [
        'consul' => [
            'uri' => 'http://127.0.0.1:8500',
            'token' => '',
            'check' => [
                'deregister_critical_service_after' => '90m',
                'interval' => '1s',
            ],
        ],
        'nacos' => [
            // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
            // 'url' => '',
            // The nacos host info
            'host' => '127.0.0.1',
            'port' => 8848,
            // The nacos account info
            'username' => null,
            'password' => null,
            'guzzle' => [
                'config' => null,
            ],
            'group_name' => 'api',
            'namespace_id' => 'namespace_id',
            'heartbeat' => 5,
            'ephemeral' => false, // 是否註冊臨時實例
        ],
    ],
];
```

# 註冊服務

註冊服務可通過 `@RpcService` 註解對一個類進行定義，即為發佈這個服務了，目前 Hyperf 僅適配了 JSON RPC 協議，具體內容也可到 [JSON RPC 服務](zh-hk/json-rpc.md) 章節瞭解詳情。

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // 實現一個加法方法，這裏簡單的認為參數都是 int 類型
    public function calculate(int $a, int $b): int
    {
        // 這裏是服務方法的具體實現
        return $a + $b;
    }
}
```

`@RpcService` 共有 `4` 個參數：   
`name` 屬性為定義該服務的名稱，這裏定義一個全局唯一的名字即可，Hyperf 會根據該屬性生成對應的 ID 註冊到服務中心去；   
`protocol` 屬性為定義該服務暴露的協議，目前僅支持 `jsonrpc` 和 `jsonrpc-http`，分別對應於 TCP 協議和 HTTP 協議下的兩種協議，默認值為 `jsonrpc-http`，這裏的值對應在 `Hyperf\Rpc\ProtocolManager` 裏面註冊的協議的 `key`，這兩個本質上都是 JSON RPC 協議，區別在於數據格式化、數據打包、數據傳輸器等不同。   
`server` 屬性為綁定該服務類發佈所要承載的 `Server`，默認值為 `jsonrpc-http`，該屬性對應 `config/autoload/server.php` 文件內 `servers` 下所對應的 `name`，這裏也就意味着我們需要定義一個對應的 `Server`；   
`publishTo` 屬性為定義該服務所要發佈的服務中心，目前僅支持 `consul`、`nacos` 或為空，為空時代表不發佈該服務到服務中心去，但也就意味着您需要手動處理服務發現的問題，要使用此功能需安裝 [hyperf/service-governance](https://github.com/hyperf/service-governance) 組件及對應的驅動依賴；

> 使用 `@RpcService` 註解需 `use Hyperf\RpcServer\Annotation\RpcService;` 命名空間。

## 自定義服務治理適配器

除了默認支持 `Consul`、`Nacos` 外，用户還可以根據自己的需要，註冊自定義的適配器。

我們可以創建一個 FooService 實現 `Hyperf\ServiceGovernance\DriverInterface`

```php
<?php

declare(strict_types=1);

namespace App\ServiceGovernance;

use Hyperf\ServiceGovernance\DriverInterface;

class FooDriver implements DriverInterface
{
    public function getNodes(string $uri, string $name, array $metadata): array
    {
        return [];
    }

    public function register(string $name, string $host, int $port, array $metadata): void
    {
    }

    public function isRegistered(string $name, string $address, int $port, array $metadata): bool
    {
        return true;
    }
}
```

然後創建一個監聽器，將其註冊到 `DriverManager` 中即可。

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\ServiceGovernance\Listener;

use App\ServiceGovernance\FooDriver;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ServiceGovernance\DriverManager;

#[Listener]
class RegisterDriverListener implements ListenerInterface
{
    protected DriverManager $driverManager;

    public function __construct(DriverManager $manager)
    {
        $this->driverManager = $manager;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event)
    {
        $this->driverManager->register('foo', make(FooDriver::class));
    }
}

```
