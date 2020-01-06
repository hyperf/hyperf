# 簡介

Hyperf 為您提供了分佈式系統的外部化配置支持，默認適配了:

- 由攜程開源的 [ctripcorp/apollo](https://github.com/ctripcorp/apollo)，由 [hyper/config-apollo](https://github.com/hyperf/config-apollo) 組件提供功能支持。
- 阿里雲提供的免費配置中心服務 [應用配置管理(ACM, Application Config Manager)](https://help.aliyun.com/product/59604.html)，由 [hyper/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) 組件提供功能支持。

## 為什麼要使用配置中心？

隨着業務的發展，微服務架構的升級，服務的數量、應用的配置日益增多（各種微服務、各種服務器地址、各種參數），傳統的配置文件方式和數據庫的方式已經可能無法滿足開發人員對配置管理的要求，同時對於配置的管理可能還會牽涉到 ACL 權限管理、配置版本管理和回滾、格式驗證、配置灰度發佈、集羣配置隔離等問題，以及：

- 安全性：配置跟隨源代碼保存在版本管理系統中，容易造成配置泄漏
- 時效性：修改配置，需要每台服務器每個應用修改並重啟服務
- 侷限性：無法支持動態調整，例如日誌開關、功能開關等   

因此，我們可以通過一個配置中心以一種科學的管理方式來統一管理相關的配置。

## 安裝

### Apollo

```bash
composer require hyperf/config-apollo
```

### Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

## 接入 Apollo 配置中心

如果您沒有對配置組件進行替換使用默認的 [hyperf/config](https://github.com/hyperf/config) 組件的話，接入 Apollo 配置中心則是輕而易舉，只需兩步。
- 通過 Composer 將 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) ，即執行命令 `composer require hyperf/config-apollo`
- 在 `config/autoload` 文件夾內增加一個 `apollo.php` 的配置文件，配置內容如下

```php
<?php
return [
    // 是否開啟配置中心的接入流程，為 true 時會自動啟動一個 ConfigFetcherProcess 進程用於更新配置
    'enable' => true,
    // Apollo Server
    'server' => 'http://127.0.0.1:8080',
    // 您的 AppId
    'appid' => 'test',
    // 當前應用所在的集羣
    'cluster' => 'default',
    // 當前應用需要接入的 Namespace，可配置多個
    'namespaces' => [
        'application',
    ],
    // 配置更新間隔（秒）
    'interval' => 5,
    // 嚴格模式，當為 false 時，拉取的配置值均為 string 類型，當為 true 時，拉取的配置值會轉化為原配置值的數據類型
    'strict_mode' => false,
    // 客户端IP
    'client_ip' => current(swoole_get_local_ip()),
    // 拉取配置超時時間
    'pullTimeout' => 10,
    // 拉取配置間隔
    'interval_timeout' => 60,
];
```

## 接入 Aliyun ACM 配置中心

接入 Aliyun ACM 配置中心與 Apollo 一樣都是輕而易舉的，同樣只需兩步。
- 通過 Composer 將 [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) ，即執行命令 `composer require hyperf/config-aliyun-acm`
- 在 `config/autoload` 文件夾內增加一個 `aliyun_acm.php` 的配置文件，配置內容如下

```php
<?php
return [
    // 是否開啟配置中心的接入流程，為 true 時會自動啟動一個 ConfigFetcherProcess 進程用於更新配置
    'enable' => true,
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
];
```

## 接入 Etcd 配置中心

- 安裝 `Etcd 客户端`

```
composer require hyperf/etcd
```

因為 `Etcd` 分為 `v2` 和 `v3` 版本，所以根據需要選擇安裝

```
# Etcd v3 http client.
composer require start-point/etcd-php
# Etcd v2 http client.
composer require linkorb/etcd-php
```

- 添加 `Etcd 客户端` 配置文件 `etcd.php`

```php
<?php
return [
    'uri' => 'http://192.168.1.200:2379',
    'version' => 'v3beta',
    'options' => [
        'timeout' => 10,
    ],
];
```

- 安裝 `Etcd 配置中心`

```
composer require hyperf/config-etcd
```

- 添加 `Etcd 配置中心` 配置文件 `config_etcd.php`

> mapping 為 `Etcd` 與 `Config` 的映射關係。映射中不存在的 `key`，則不會被同步到 `Config` 中。

```php
<?php
return [
    'enable' => true,
    'namespaces' => [
        '/test',
    ],
    'mapping' => [
        '/test/test' => 'etcd.test.test',
    ],
    'interval' => 5,
];
```

## 配置更新的作用範圍

在默認的功能實現下，是由一個 `ConfigFetcherProcess` 進程根據配置的 `interval` 來向 Apollo 拉取對應 `namespace` 的配置，並通過 IPC 通訊將拉取到的新配置傳遞到各個 Worker 中，並更新到 `Hyperf\Contract\ConfigInterface` 對應的對象內。   
需要注意的是，更新的配置只會更新 `Config` 對象，故僅限應用層或業務層的配置，不涉及框架層的配置改動，因為框架層的配置改動需要重啟服務，如果您有這樣的需求，也可以通過自行實現 `ConfigFetcherProcess` 來達到目的。
