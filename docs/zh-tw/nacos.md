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

## 服務與例項

`MainWorkerStartListener.php` 將在系統啟動完成時自動完成 `例項註冊`，`服務註冊`

如果需要在服務下線時自動登出服務，請增加如下配置，以監聽 `Shutdown` 事件

- config/autoload/server.php

```php
<?php
use Hyperf\Server\Event;
return [
    // ...other
    'callbacks' => [
        // ...other
        Event::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

### 獲取服務的可用節點列表

```php
use Hyperf\Utils\ApplicationContext;
use Hyperf\Nacos\Client;

$container = ApplicationContext::getContainer();
$client = $container->get(Client::class);

$optimal = $client->getValidNodes('hyperf', [
    'groupName' => 'api',
    'namespaceId' => '5ce9d1c1-6732-4ccc-ae1f-5139af86a845'
]);

```

## 配置中心

`MainWorkerStartListener.php` 系統啟動時將拉取遠端配置, 併合入`hyperf` 的 `Config`

`FetchConfigProcess.php` 自定義程序將監聽配置, 若有更新將傳送 `PipeMessage` 到各服務`worker` 程序, 併合入當前程序的 `Config`

如果服務如下配置

```php
// config/autoload/nacos.php

return [
    'host' => '127.0.0.1',
    'port' => 8848,
    'username' => null,
    'password' => null,
    'config' => [
        // 是否開啟配置中心
        'enable' => true,
        // 合併模式
        'merge_mode' => Constants::CONFIG_MERGE_OVERWRITE,
        // 配置讀取間隔
        'reload_interval' => 3,
        // 預設的配置 KEY 值
        'default_key' => 'nacos_config',
        'listener_config' => [
            // $key => $config
            // 不設定 key 時，則使用 default_key 配置的 key
            'nacos_config' => [
                'tenant' => 'tenant',
                'data_id' => 'json',
                'group' => 'DEFAULT_GROUP',
                'type' => 'json',
            ],
            'nacos_config.data' => [
                'data_id' => 'text',
                'group' => 'DEFAULT_GROUP',
            ],
        ],
    ],
];
```

系統將自動監聽 `listener_config` 中的配置，並將其合併到對應的節點中，例如上述的 `nacos_config` ，可以用`config('nacos_config.***')`
獲取，若沒有配置 `$key` 項，將會併入 `default_key` 節點。
