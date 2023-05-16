# 簡介

Hyperf 為您提供了分散式系統的外部化配置支援，預設適配了:

- 由攜程開源的 [ctripcorp/apollo](https://github.com/ctripcorp/apollo)，由 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) 元件提供功能支援。
- 阿里雲提供的免費配置中心服務 [應用配置管理(ACM, Application Config Manager)](https://help.aliyun.com/product/59604.html)，由 [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) 元件提供功能支援。
- ETCD
- Nacos
- Zookeeper

## 為什麼要使用配置中心？

隨著業務的發展，微服務架構的升級，服務的數量、應用的配置日益增多（各種微服務、各種伺服器地址、各種引數），傳統的配置檔案方式和資料庫的方式已經可能無法滿足開發人員對配置管理的要求，同時對於配置的管理可能還會牽涉到 ACL 許可權管理、配置版本管理和回滾、格式驗證、配置灰度釋出、叢集配置隔離等問題，以及：

- 安全性：配置跟隨原始碼儲存在版本管理系統中，容易造成配置洩漏
- 時效性：修改配置，需要每臺伺服器每個應用修改並重啟服務
- 侷限性：無法支援動態調整，例如日誌開關、功能開關等   

因此，我們可以透過一個配置中心以一種科學的管理方式來統一管理相關的配置。

## 安裝

### 配置中心統一接入層

```bash
composer require hyperf/config-center
```

### 使用 Apollo 需安裝

```bash
composer require hyperf/config-apollo
```

### 使用 Aliyun ACM 需安裝

```bash
composer require hyperf/config-aliyun-acm
```

### 使用 Etcd 需安裝

```bash
composer require hyperf/config-etcd
```

### 使用 Nacos 需安裝

```bash
composer require hyperf/config-nacos
```

#### GRPC 雙向流

Nacos 傳統的配置中心，是基於短輪詢進行配置同步的，就會導致輪訓間隔內，服務無法拿到最新的配置。`Nacos V2` 版本增加了 GRPC 雙向流的支援，如果你想讓 Nacos 在發現配置變更後，及時推送給相關服務。

可以按照以下步驟，開啟 GRPC 雙向流功能。

- 首先，我們安裝必要的元件

```shell
composer require "hyperf/http2-client:~3.0.0"
composer require "hyperf/grpc:~3.0.0"
```

- 修改配置項

修改 `config_center.drivers.nacos.client.grpc.enable` 為 `true`，具體如下

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigApollo\PullMode;
use Hyperf\ConfigCenter\Mode;

return [
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    'driver' => env('CONFIG_CENTER_DRIVER', 'nacos'),
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            'default_key' => 'nacos_config',
            'listener_config' => [
                'nacos_config' => [
                    'tenant' => 'tenant', // corresponding with service.namespaceId
                    'data_id' => 'hyperf-service-config',
                    'group' => 'DEFAULT_GROUP',
                ],
            ],
            'client' => [
                // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
                // Only support for nacos v2.
                'grpc' => [
                    'enable' => true,
                    'heartbeat' => 10,
                ],
            ],
        ],
    ],
];

```

- 接下里啟動服務即可

### 使用 Zookeeper 需安裝

```bash
composer require hyperf/config-zookeeper
```

## 接入配置中心

### 配置檔案

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigCenter\Mode;

return [
    // 是否開啟配置中心
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    // 使用的驅動型別，對應同級別配置 drivers 下的 key
    'driver' => env('CONFIG_CENTER_DRIVER', 'apollo'),
    // 配置中心的執行模式，多程序模型推薦使用 PROCESS 模式，單程序模型推薦使用 COROUTINE 模式
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'apollo' => [
            'driver' => Hyperf\ConfigApollo\ApolloDriver::class,
            // Apollo Server
            'server' => 'http://127.0.0.1:9080',
            // 您的 AppId
            'appid' => 'test',
            // 當前應用所在的叢集
            'cluster' => 'default',
            // 當前應用需要接入的 Namespace，可配置多個
            'namespaces' => [
                'application',
            ],
            // 配置更新間隔（秒）
            'interval' => 5,
            // 嚴格模式，當為 false 時，拉取的配置值均為 string 型別，當為 true 時，拉取的配置值會轉化為原配置值的資料型別
            'strict_mode' => false,
            // 客戶端IP
            'client_ip' => \Hyperf\Utils\Network::ip(),
            // 拉取配置超時時間
            'pullTimeout' => 10,
            // 拉取配置間隔
            'interval_timeout' => 1,
        ],
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            // 配置合併方式，支援覆蓋和合並
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            // 如果對應的對映 key 沒有設定，則使用預設的 key
            'default_key' => 'nacos_config',
            'listener_config' => [
                // dataId, group, tenant, type, content
                // 對映後的配置 KEY => Nacos 中實際的配置
                'nacos_config' => [
                    'tenant' => 'tenant', // corresponding with service.namespaceId
                    'data_id' => 'hyperf-service-config',
                    'group' => 'DEFAULT_GROUP',
                ],
                'nacos_config.data' => [
                    'data_id' => 'hyperf-service-config-yml',
                    'group' => 'DEFAULT_GROUP',
                    'type' => 'yml',
                ],
            ],
            'client' => [
                // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
            ],
        ],
        'aliyun_acm' => [
            'driver' => Hyperf\ConfigAliyunAcm\AliyunAcmDriver::class,
            // 配置更新間隔（秒）
            'interval' => 5,
            // 阿里雲 ACM 斷點地址，取決於您的可用區
            'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
            // 當前應用需要接入的 Namespace
            'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
            // 您的配置對應的 Data ID
            'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
            // 您的配置對應的 Group
            'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
            // 您的阿里雲賬號的 Access Key
            'access_key' => env('ALIYUN_ACM_AK', ''),
            // 您的阿里雲賬號的 Secret Key
            'secret_key' => env('ALIYUN_ACM_SK', ''),
            'ecs_ram_role' => env('ALIYUN_ACM_RAM_ROLE', ''),
        ],
        'etcd' => [
            'driver' => Hyperf\ConfigEtcd\EtcdDriver::class,
            'packer' => Hyperf\Utils\Packer\JsonPacker::class,
            // 需要同步的資料字首
            'namespaces' => [
                '/application',
            ],
            // `Etcd` 與 `Config` 的對映關係。對映中不存在的 `key`，則不會被同步到 `Config` 中
            'mapping' => [
                // etcd key => config key
                '/application/test' => 'test',
            ],
            // 配置更新間隔（秒）
            'interval' => 5,
            'client' => [
                # Etcd Client
                'uri' => 'http://127.0.0.1:2379',
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ],
        'zookeeper' => [
            'driver' => Hyperf\ConfigZookeeper\ZookeeperDriver::class,
            'server' => env('ZOOKEEPER_SERVER', '127.0.0.1:2181'),
            'path' => env('ZOOKEEPER_CONFIG_PATH', '/conf'),
            'interval' => 5,
        ],
    ],
];
```

如配置檔案不存在可執行 `php bin/hyperf.php vendor:publish hyperf/config-center` 命令來生成。

## 配置更新的作用範圍

在預設的功能實現下，是由一個 `ConfigFetcherProcess` 程序根據配置的 `interval` 來向 配置中心 Server 拉取對應 `namespace` 的配置，並透過 IPC 通訊將拉取到的新配置傳遞到各個 Worker 中，並更新到 `Hyperf\Contract\ConfigInterface` 對應的物件內。   
需要注意的是，更新的配置只會更新 `Config` 物件，故僅限應用層或業務層的配置，不涉及框架層的配置改動，因為框架層的配置改動需要重啟服務，如果您有這樣的需求，也可以透過自行實現 `ConfigFetcherProcess` 來達到目的。

## 配置更新事件

配置中心執行期間，但配置發生變化會對應觸發 `Hyperf\ConfigCenter\Event\ConfigChanged` 事件，您可以進行對這些事件進行監聽以滿足您的需求。

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\ConfigCenter\Event\ConfigChanged;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ConfigChanged::class,
        ];
    }

    public function process(object $event)
    {
        var_dump($event);
    }
}
```
