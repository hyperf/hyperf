# Nacos

一個 `Nacos` 的 `PHP` 協程客戶端，與 `Hyperf` 的配置中心、微服務治理完美結合。

## 安裝

```shell
composer require hyperf/nacos
```

### 釋出配置檔案

```shell
php bin/hyperf.php vendor:publish hyperf/nacos
```

### 目錄結構

```
./src
├── Api
│   ├── AbstractNacos.php
│   ├── NacosConfig.php
│   ├── NacosInstance.php
│   ├── NacosOperator.php
│   └── NacosService.php
├── Client.php
├── Config
│   ├── FetchConfigProcess.php
│   ├── OnPipeMessageListener.php
│   └── PipeMessage.php
├── ConfigProvider.php
├── Contract
│   └── LoggerInterface.php
├── Exception
│   ├── InvalidArgumentException.php
│   ├── NacosThrowable.php
│   └── RuntimeException.php
├── Instance.php
├── Listener
│   ├── MainWorkerStartListener.php
│   └── OnShutdownListener.php
├── Model
│   ├── AbstractModel.php
│   ├── ConfigModel.php
│   ├── InstanceModel.php
│   └── ServiceModel.php
├── Process
│   └── InstanceBeatProcess.php
└── Service.php
```

## 服務與例項

`MainWorkerStartListener.php` 將在系統啟動完成時自動完成 `例項註冊`，`服務註冊` 

如果需要在服務下線時自動登出服務，請增加如下配置，以監聽 `Shutdown` 事件

```php
// config/autoload/server.php

return [
    // ...other
    'callbacks' => [
        // ...other
        Event::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

### 獲取當前例項

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Instance;

$container = ApplicationContext::getContainer();
$instance = $container->get(Instance::class);
```

### 獲取當前服務

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Service;

$container = ApplicationContext::getContainer();
$service = $container->get(Service::class);
```

### 獲取一個服務的最優節點

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Api\NacosInstance;
use Hyperf\Nacos\Model\ServiceModel;

$container = ApplicationContext::getContainer();
$instance = $container->get(NacosInstance::class);

$service = new ServiceModel([
    'service_name' => 'hyperf',
    'group_name' => 'api',
    'namespace_id' => '5ce9d1c1-6732-4ccc-ae1f-5139af86a845'
]);

$optimal = $instance->getOptimal($service);

```

## 配置中心

`MainWorkerStartListener.php` 系統啟動時將拉取遠端配置, 併合入`hyperf` 的 `Config`

`FetchConfigProcess.php` 自定義程序將監聽配置, 若有更新將傳送 `PipeMessage` 到各服務`worker` 程序, 併合入當前程序的 `Config`

如果服務如下配置
```php
// config/autoload/nacos.php

return [
    // ...other
    'config_reload_interval' => 3,
    // 遠端配置合併節點, 預設 config 根節點
    'config_append_node' => 'nacos_config',
    'listener_config' => [
        // 配置項 dataId, group, tenant, type, content
        [
            'data_id' => 'hyperf-service-config',
            'group' => 'DEFAULT_GROUP',
        ],
        [
            'data_id' => 'hyperf-service-config-yml',
            'group' => 'DEFAULT_GROUP',
            'type' => 'yml',
        ],
    ],
];
```

系統將自動監聽`listener_config` 中的配置，並將其合併入`hyperf Config` 物件的指定(`config_append_node`) 節點，可以用`config('nacos_config.***')` 獲取，若沒有配置 `config_append_node` 項，將會併入 `Config` 物件根節點。

> 所有配置的 `鍵(key)` 在實際發起 API 請求時會自動從下劃線風格轉換為駝峰風格。
