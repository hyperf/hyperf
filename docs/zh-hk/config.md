# 配置

當您使用的是 [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 項目創建的項目時，Hyperf 的所有配置文件均處於根目錄下的 `config` 文件夾內，每個選項都有説明，您可以隨時查看並熟悉有哪些選項可以使用。

# 安裝

```bash
composer require hyperf/config
```

# 配置文件結構

以下結構僅為 Hyperf-Skeleton 所提供的默認配置的情況下的結構，實際情況由於依賴或使用的組件的差異，文件會有差異。
```
config
├── autoload // 此文件夾內的配置文件會被配置組件自己加載，並以文件夾內的文件名作為第一個鍵值
│   ├── amqp.php  // 用於管理 AMQP 組件
│   ├── annotations.php // 用於管理註解
│   ├── apollo.php // 用於管理基於 Apollo 實現的配置中心
│   ├── aspects.php // 用於管理 AOP 切面
│   ├── async_queue.php // 用於管理基於 Redis 實現的簡易隊列服務
│   ├── cache.php // 用於管理緩存組件
│   ├── commands.php // 用於管理自定義命令
│   ├── consul.php // 用於管理 Consul 客户端
│   ├── databases.php // 用於管理數據庫客户端
│   ├── dependencies.php // 用於管理 DI 的依賴關係和類對應關係
│   ├── devtool.php // 用於管理開發者工具
│   ├── exceptions.php // 用於管理異常處理器
│   ├── listeners.php // 用於管理事件監聽者
│   ├── logger.php // 用於管理日誌
│   ├── middlewares.php // 用於管理中間件
│   ├── opentracing.php // 用於管理調用鏈追蹤
│   ├── processes.php // 用於管理自定義進程
│   ├── redis.php // 用於管理 Redis 客户端
│   └── server.php // 用於管理 Server 服務
├── config.php // 用於管理用户或框架的配置，如配置相對獨立亦可放於 autoload 文件夾內
├── container.php // 負責容器的初始化，作為一個配置文件運行並最終返回一個 Psr\Container\ContainerInterface 對象
└── routes.php // 用於管理路由
```

## server.php 配置説明

以下為 Hyperf-Skeleton 中的 `config/autoload/server.php` 所提供的默認 `settings` 

```php
<?php
declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 這裏省略了該文件的其它配置
    'settings' => [
        'enable_coroutine' => true, // 開啓內置協程
        'worker_num' => swoole_cpu_num(), // 設置啓動的 Worker 進程數
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid', // master 進程的 PID
        'open_tcp_nodelay' => true, // TCP 連接發送數據時會關閉 Nagle 合併算法，立即發往客户端連接
        'max_coroutine' => 100000, // 設置當前工作進程最大協程數量
        'open_http2_protocol' => true, // 啓用 HTTP2 協議解析
        'max_request' => 100000, // 設置 worker 進程的最大任務數
        'socket_buffer_size' => 2 * 1024 * 1024, // 配置客户端連接的緩存區長度
    ],
];
```

此配置文件用於管理 Server 服務，其中的 `settings` 選項可以直接使用由 `Swoole Server` 提供的選項，其他選項可參考 [Swoole 官方文檔](https://wiki.swoole.com/#/server/setting) 。

如需要設置守護進程化，可在 `settings` 中增加 `'daemonize' => true`，執行 `php bin/hyperf.php start`後，程序將轉入後台作為守護進程運行

單獨的 Server 配置需要添加在對應 `servers` 的 `settings` 當中，如 `jsonrpc` 協議的 TCP Server 配置啓用 EOF 自動分包和設置 EOF 字符串
```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 這裏省略了該文件的其它配置
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
                'open_eof_split' => true, // 啓用 EOF 自動分包
                'package_eof' => "\r\n", // 設置 EOF 字符串
            ],
        ],
    ],
];

```

## `config.php` 與 `autoload` 文件夾內的配置文件的關係

`config.php` 與 `autoload` 文件夾內的配置文件在服務啓動時都會被掃描並注入到 `Hyperf\Contract\ConfigInterface` 對應的對象中，配置的結構為一個鍵值對的大數組，兩種配置形式不同的在於 `autoload`  內配置文件的文件名會作為第一層 鍵(Key) 存在，而 `config.php` 內的則以您定義的為第一層，我們通過下面的例子來演示一下。   
我們假設存在一個 `config/autoload/client.php` 文件，文件內容如下：
```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```
那麼我們想要得到 `timeout` 的值對應的 鍵(Key) 為 `client.request.timeout`；   

我們假設想要以相同的 鍵(Key) 獲得同樣的結果，但配置是寫在 `config/config.php` 文件內的，那麼文件內容應如下：
```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## 使用 Hyperf Config 組件

該組件是官方提供的默認的配置組件，是面向 `Hyperf\Contract\ConfigInterface` 接口實現的，由 [hyperf/config](https://github.com/hyperf/config) 組件內的 `ConfigProvider` 將 `Hyperf\Config\Config` 對象綁定到接口上。   

### 設置配置

只需在 `config/config.php` 與 `config/autoload/server.php` 與 `autoload` 文件夾內的配置，都能在服務啓動時被掃描並注入到 `Hyperf\Contract\ConfigInterface` 對應的對象中，這個流程是由 `Hyperf\Config\ConfigFactory` 在 Config 對象實例化時完成的。

### 獲取配置

Config 組件提供了三種方式獲取配置，通過 `Hyperf\Config\Config` 對象獲取、通過 `#[Value]` 註解獲取和通過 `config(string $key, $default)` 函數獲取。

#### 通過 Config 對象獲取配置

這種方式要求你已經拿到了 `Config` 對象的實例，默認對象為 `Hyperf\Config\Config`，注入實例的細節可查閲 [依賴注入](zh-hk/di.md) 章節；

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 通過 get(string $key, $default): mixed 方法獲取 $key 所對應的配置，$key 值可以通過 . 連接符定位到下級數組，$default 則是當對應的值不存在時返回的默認值
$config->get($key，$default);
```

#### 通過 `#[Value]` 註解獲取配置

這種方式要求註解的應用對象必須是通過 [hyperf/di](https://github.com/hyperf/di) 組件創建的，注入實例的細節可查閲 [依賴注入](zh-hk/di.md) 章節，示例中我們假設 `IndexController` 就是一個已經定義好的 `Controller` 類，`Controller` 類一定是由 `DI` 容器創建出來的；   
`#[Value]` 內的字符串則對應到 `$config->get($key)` 內的 `$key` 參數，在創建該對象實例時，對應的配置會自動注入到定義的類屬性中。

```php
use Hyperf\Config\Annotation\Value;

class IndexController
{
    #[Value("config.key")]
    private $configValue;

    public function index()
    {
        return $this->configValue;
    }
}
```

#### 通過 config 函數獲取

在任意地方可以通過 `config(string $key, $default)` 函數獲取對應的配置，但這樣的使用方式也就意味着您對 [hyperf/config](https://github.com/hyperf/config) 和 [hyperf/utils](https://github.com/hyperf/utils) 組件是強依賴的。

### 判斷配置是否存在

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 通過 has(): bool 方法判斷對應的 $key 值是否存在於配置中，$key 值可以通過 . 連接符定位到下級數組
$config->has($key);
```

## 環境變量

對於不同的運行環境使用不同的配置是一種常見的需求，比如在測試環境和生產環境的 Redis 配置不一樣，而生產環境的配置又不能提交到源代碼版本管理系統中以免信息泄露。   

在 Hyperf 裏我們提供了環境變量這一解決方案，通過利用 [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) 提供的環境變量解析功能，以及 `env()` 函數來獲取環境變量的值，這一需求解決起來是相當的容易。   

在新安裝好的 Hyperf 應用中，其根目錄會包含一個 `.env.example` 文件。如果是通過 Composer 安裝的 Hyperf，該文件會自動基於 `.env.example` 複製一個新文件並命名為 `.env`。否則，需要你手動更改一下文件名。   

您的 `.env` 文件不應該提交到應用的源代碼版本管理系統中，因為每個使用你的應用的開發人員 / 服務器可能需要有一個不同的環境配置。此外，在入侵者獲得你的源代碼倉庫的訪問權的情況下，這會導致嚴重的安全問題，因為所有敏感的數據都被一覽無餘了。   

> `.env` 文件中的所有變量均可被外部環境變量所覆蓋（比如服務器級或系統級或 Docker 環境變量）。

### 環境變量類型

`.env` 文件中的所有變量都會被解析為字符串類型，因此提供了一些保留值以允許您從 `env()` 函數中獲取更多類型的變量：

| .env 值 | env() 值 |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

如果你需要使用包含空格或包含其他特殊字符的環境變量，可以通過將值括在雙引號中來實現，比如：

```dotenv
APP_NAME="Hyperf Skeleton"
```

### 讀取環境變量

我們在上面也有提到環境變量可以通過 `env()` 函數獲取，在應用開發中，環境變量只應作為配置的一個值，通過環境變量的值來覆蓋配置的值，對於應用層來説應 **只使用配置**，而不是直接使用環境變量。   
我們舉個合理使用的例子：

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## 發佈組件配置

Hyperf 採用組件化設計，在添加一些組件進來骨架項目後，我們通常會需要為新添加的組件創建對應的配置文件，以滿足對組件的使用。Hyperf 為組件提供了一個 `組件配置發佈機制`，通過該機制，您只需通過一個 `vendor:publish` 命令即可將組件預設的配置文件模板發佈到骨架項目中來。
比如我們希望添加一個 `hyperf/foo` 組件 (該組件實際並不存在，僅示例) 以及該組件對應的配置文件，在執行 `composer require hyperf/foo` 安裝之後，您可通過執行 `php bin/hyperf.php vendor:publish hyperf/foo` 來將組件預設的配置文件，發佈到骨架項目的 `config/autoload` 文件夾內，具體要發佈的內容，由組件來定義提供。 

## 配置中心

Hyperf 為您提供了分佈式系統的外部化配置支持，目前支持由攜程開源的 `Apollo`、阿里雲 ACM 應用配置管理、ETCD、Nacos 以及 Zookeeper 作為配置中心的支持。
關於配置中心的使用細節我們由 [配置中心](zh-hk/config-center.md) 章節來闡述。


