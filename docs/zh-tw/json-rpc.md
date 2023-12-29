# JSON RPC 服務

JSON RPC 是一種基於 JSON 格式的輕量級的 RPC 協議標準，易於使用和閱讀。在 Hyperf 裡由 [hyperf/json-rpc](https://github.com/hyperf/json-rpc) 元件來實現，可自定義基於 HTTP 協議來傳輸，或直接基於 TCP 協議來傳輸。

## 安裝

```bash
composer require hyperf/json-rpc
```

該元件只是 JSON RPC 的協議處理的元件，通常來說，您仍需配合 [hyperf/rpc-server](https://github.com/hyperf/rpc-server) 或 [hyperf/rpc-client](https://github.com/hyperf/rpc-client) 來滿足 服務端 和 客戶端的場景，如同時使用則都需要安裝：   

要使用 JSON RPC 服務端：

```bash
composer require hyperf/rpc-server
```

要使用 JSON RPC 客戶端：

```bash
composer require hyperf/rpc-client
```

## 使用

服務有兩種角色，一種是 `服務提供者(ServiceProvider)`，即為其它服務提供服務的服務，另一種是 `服務消費者(ServiceConsumer)`，即依賴其它服務的服務，一個服務既可能是 `服務提供者(ServiceProvider)`，同時又是 `服務消費者(ServiceConsumer)`。而兩者直接可以透過 `服務契約` 來定義和約束介面的呼叫，在 Hyperf 裡，可直接理解為就是一個 `介面類(Interface)`，通常來說這個介面類會同時出現在提供者和消費者下。

### 定義服務提供者

目前僅支援透過註解的形式來定義 `服務提供者(ServiceProvider)`，後續迭代會增加配置的形式。   
我們可以直接透過 `#[RpcService]` 註解對一個類進行定義即可釋出這個服務了：

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * 注意，如希望透過服務中心來管理服務，需在註解內增加 publishTo 屬性
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // 實現一個加法方法，這裡簡單的認為引數都是 int 型別
    public function add(int $a, int $b): int
    {
        // 這裡是服務方法的具體實現
        return $a + $b;
    }
}
```

`#[RpcService]` 共有 `4` 個引數：   
`name` 屬性為定義該服務的名稱，這裡定義一個全域性唯一的名字即可，Hyperf 會根據該屬性生成對應的 ID 註冊到服務中心去；   
`protocol` 屬性為定義該服務暴露的協議，目前僅支援 `jsonrpc-http`, `jsonrpc`, `jsonrpc-tcp-length-check` ，分別對應於 HTTP 協議和 TCP 協議下的兩種協議，預設值為 `jsonrpc-http`，這裡的值對應在 `Hyperf\Rpc\ProtocolManager` 裡面註冊的協議的 `key`，它們本質上都是 JSON RPC 協議，區別在於資料格式化、資料打包、資料傳輸器等不同。   
`server` 屬性為繫結該服務類釋出所要承載的 `Server`，預設值為 `jsonrpc-http`，該屬性對應 `config/autoload/server.php` 檔案內 `servers` 下所對應的 `name`，這裡也就意味著我們需要定義一個對應的 `Server`；   
`publishTo` 屬性為定義該服務所要釋出的服務中心，目前僅支援 `consul`、`nacos` 或為空，為空時代表不釋出該服務到服務中心去，但也就意味著您需要手動處理服務發現的問題，要使用此功能需安裝 [hyperf/service-governance](https://github.com/hyperf/service-governance) 元件及對應的驅動依賴，具體可參考 [服務註冊](zh-tw/service-register.md) 章節；

> 使用 `#[RpcService]` 註解需 `use Hyperf\RpcServer\Annotation\RpcService;` 名稱空間。

#### 定義 JSON RPC Server

HTTP Server (適配 `jsonrpc-http` 協議)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 這裡省略了該檔案的其它配置
    'servers' => [
        [
            'name' => 'jsonrpc-http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [\Hyperf\JsonRpc\HttpServer::class, 'onRequest'],
            ],
        ],
    ],
];
```

TCP Server (適配 `jsonrpc` 協議)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 這裡省略了該檔案的其它配置
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true,
                'package_eof' => "\r\n",
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

TCP Server (適配 `jsonrpc-tcp-length-check` 協議)

當前協議為 `jsonrpc` 的擴充套件協議，使用者可以很方便的修改對應的 `settings` 使用此協議，示例如下。

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 這裡省略了該檔案的其它配置
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

### 釋出到服務中心

目前僅支援釋出服務到 `consul`、`nacos`，後續會增加其它服務中心。   
釋出服務到 `consul` 在 Hyperf 也是非常容易的一件事情，透過 `composer require hyperf/service-governance-consul` 引用元件（如果已安裝則可忽略該步驟），然後再在 `config/autoload/services.php` 配置檔案內配置 `drivers.consul` 配置即可。
釋出服務到 `nacos` 在也是類似，透過 `composer require hyperf/service-governance-nacos` 引用元件（如果已安裝則可忽略該步驟），然後再在 `config/autoload/services.php` 配置檔案內配置 `drivers.nacos` 配置即可，示例如下：

```php
<?php
return [
    'enable' => [
        'discovery' => true,
        'register' => true,
    ],
    'consumers' => [],
    'providers' => [],
    'drivers' => [
        'consul' => [
            'uri' => 'http://127.0.0.1:8500',
            'token' => '',
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
        ],
    ],
];
```

配置完成後，在啟動服務時，Hyperf 會自動地將 `#[RpcService]` 定義了 `publishTo` 屬性為 `consul` 或 `nacos` 的服務註冊到對應的服務中心去。

> 目前僅支援 `jsonrpc` 和 `jsonrpc-http` 協議釋出到服務中心去，其它協議尚未實現服務註冊

### 定義服務消費者

一個 `服務消費者(ServiceConsumer)` 可以理解為就是一個客戶端類，但在 Hyperf 裡您無需處理連線和請求相關的事情，只需要進行一些鑑定配置即可。

#### 自動建立代理消費者類

您可透過在 `config/autoload/services.php` 配置檔案內進行一些簡單的配置，即可透過動態代理自動建立消費者類。

```php
<?php
return [
    // 此處省略了其它同層級的配置
    'consumers' => [
        [
            // name 需與服務提供者的 name 屬性相同
            'name' => 'CalculatorService',
            // 服務介面名，可選，預設值等於 name 配置的值，如果 name 直接定義為介面類則可忽略此行配置，如 name 為字串則需要配置 service 對應到介面類
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
            // 對應容器物件 ID，可選，預設值等於 service 配置的值，用來定義依賴注入的 key
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
            // 服務提供者的服務協議，可選，預設值為 jsonrpc-http
            // 可選 jsonrpc-http jsonrpc jsonrpc-tcp-length-check
            'protocol' => 'jsonrpc-http',
            // 負載均衡演算法，可選，預設值為 random
            'load_balancer' => 'random',
            // 這個消費者要從哪個服務中心獲取節點資訊，如不配置則不會從服務中心獲取節點資訊
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // 如果沒有指定上面的 registry 配置，即為直接對指定的節點進行消費，透過下面的 nodes 引數來配置服務提供者的節點資訊
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
            // 配置項，會影響到 Packer 和 Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // 根據協議不同，區分配置
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                // 重試次數，預設值為 2，收包超時不進行重試。暫只支援 JsonRpcPoolTransporter
                'retry_count' => 2,
                // 重試間隔，毫秒
                'retry_interval' => 100,
                // 使用多路複用 RPC 時的心跳間隔，null 為不觸發心跳
                'heartbeat' => 30,
                // 當使用 JsonRpcPoolTransporter 時會用到以下配置
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 32,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.0,
                ],
            ],
        ]
    ],
];
```

在應用啟動時會自動建立客戶端類的代理物件，並在容器中使用配置項 `id` 的值（如果未設定，會使用配置項 `service` 值代替）來新增繫結關係，這樣就和手工編寫的客戶端類一樣，透過注入 `CalculatorServiceInterface` 介面來直接使用客戶端。

> 當服務提供者使用介面類名釋出服務名，在服務消費端只需要設定配置項 `name` 值為介面類名，不需要重複設定配置項 `id` 和 `service`。

#### 手動建立消費者類

如您對消費者類有更多的需求，您可透過手動建立一個消費者類來實現，只需要定義一個類及相關屬性即可。

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * 定義對應服務提供者的服務名稱
     */
    protected string $serviceName = 'CalculatorService';
    
    /**
     * 定義對應服務提供者的服務協議
     */
    protected string $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

然後還需要在配置檔案定義一個配置標記要從何服務中心獲取節點資訊，位於 `config/autoload/services.php` (如不存在可自行建立)

```php
<?php
return [
    // 此處省略了其它同層級的配置
    'consumers' => [
        [
            // 對應消費者類的 $serviceName
            'name' => 'CalculatorService',
            // 這個消費者要從哪個服務中心獲取節點資訊，如不配置則不會從服務中心獲取節點資訊
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // 如果沒有指定上面的 registry 配置，即為直接對指定的節點進行消費，透過下面的 nodes 引數來配置服務提供者的節點資訊
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```


這樣我們便可以透過 `CalculatorService` 類來實現對服務的消費了，為了讓這裡的關係邏輯更加的合理，還應該在 `config/autoload/dependencies.php` 內定義 `CalculatorServiceInterface` 和 `CalculatorServiceConsumer` 的關係，示例如下：

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

這樣便可以透過注入 `CalculatorServiceInterface` 介面來使用客戶端了。

#### 配置複用

通常來說，一個服務消費者會同時消費多個服務提供者，當我們透過服務中心來發現服務提供者時， `config/autoload/services.php` 配置檔案內就可能會重複配置很多次 `registry` 配置，但通常來說，我們的服務中心可能是統一的，也就意味著多個服務消費者配置都是從同樣的服務中心去拉取節點資訊，此時我們可以透過 `PHP 變數` 或 `迴圈` 等 PHP 程式碼來實現配置檔案的生成。

##### 透過 PHP 變數生成配置

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // 下面的 FooService 和 BarService 僅示例多服務，並不是在文件示例中真實存在的
    'consumers' => [
        [
            'name' => 'FooService',
            'registry' => $registry,
        ],
        [
            'name' => 'BarService',
            'registry' => $registry,
        ]
    ],
];
```

##### 透過迴圈生成配置

```php
<?php
return [
    // 此處省略了其它同層級的配置
    'consumers' => value(function () {
        $consumers = [];
        // 這裡示例自動建立代理消費者類的配置形式，顧存在 name 和 service 兩個配置項，這裡的做法不是唯一的，僅說明可以透過 PHP 程式碼來生成配置
        // 下面的 FooServiceInterface 和 BarServiceInterface 僅示例多服務，並不是在文件示例中真實存在的
        $services = [
            'FooService' => App\JsonRpc\FooServiceInterface::class,
            'BarService' => App\JsonRpc\BarServiceInterface::class,
        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'registry' => [
                   'protocol' => 'consul',
                   'address' => 'http://127.0.0.1:8500',
                ]
            ];
        }
        return $consumers;
    }),
];
```

### 返回 PHP 物件

當框架匯入 `symfony/serializer (^5.0)` 和 `symfony/property-access (^5.0)` 後，並在 `dependencies.php` 中配置一下對映關係

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` 就會支援物件的序列化和反序列化。暫時不支援這種 `MathValue[]` 物件陣列。

定義返回物件

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

class MathValue
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
```

改寫介面檔案

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function sum(MathValue $v1, MathValue $v2): MathValue;
}
```

控制器中呼叫

```php
<?php

use Hyperf\Context\ApplicationContext;
use App\JsonRpc\CalculatorServiceInterface;
use App\JsonRpc\MathValue;

$client = ApplicationContext::getContainer()->get(CalculatorServiceInterface::class);

/** @var MathValue $result */
$result = $client->sum(new MathValue(1), new MathValue(2));

var_dump($result->value);
```

### 使用 JsonRpcPoolTransporter

框架提供了基於連線池的 `Transporter`，可以有效避免高併發時，建立過多連線的問題。這裡可以透過替換 `JsonRpcTransporter` 的方式，使用 `JsonRpcPoolTransporter`。

修改 `dependencies.php` 檔案

```php
<?php

declare(strict_types=1);

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;

return [
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
];

```


