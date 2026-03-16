# 配置

當您使用的是 [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 專案建立的專案時，Hyperf 的所有配置檔案均處於根目錄下的 `config` 資料夾內，每個選項都有說明，您可以隨時檢視並熟悉有哪些選項可以使用。

# 安裝

```bash
composer require hyperf/config
```

# 配置檔案結構

以下結構僅為 Hyperf-Skeleton 所提供的預設配置的情況下的結構，實際情況由於依賴或使用的元件的差異，檔案會有差異。
```
config
├── autoload // 此資料夾內的配置檔案會被配置元件自己載入，並以資料夾內的檔名作為第一個鍵值
│   ├── amqp.php  // 用於管理 AMQP 元件
│   ├── annotations.php // 用於管理註解
│   ├── apollo.php // 用於管理基於 Apollo 實現的配置中心
│   ├── aspects.php // 用於管理 AOP 切面
│   ├── async_queue.php // 用於管理基於 Redis 實現的簡易佇列服務
│   ├── cache.php // 用於管理快取元件
│   ├── commands.php // 用於管理自定義命令
│   ├── consul.php // 用於管理 Consul 客戶端
│   ├── databases.php // 用於管理資料庫客戶端
│   ├── dependencies.php // 用於管理 DI 的依賴關係和類對應關係
│   ├── devtool.php // 用於管理開發者工具
│   ├── exceptions.php // 用於管理異常處理器
│   ├── listeners.php // 用於管理事件監聽者
│   ├── logger.php // 用於管理日誌
│   ├── middlewares.php // 用於管理中介軟體
│   ├── opentracing.php // 用於管理呼叫鏈追蹤
│   ├── processes.php // 用於管理自定義程序
│   ├── redis.php // 用於管理 Redis 客戶端
│   └── server.php // 用於管理 Server 服務
├── config.php // 用於管理使用者或框架的配置，如配置相對獨立亦可放於 autoload 資料夾內
├── container.php // 負責容器的初始化，作為一個配置檔案執行並最終返回一個 Psr\Container\ContainerInterface 物件
└── routes.php // 用於管理路由
```

## server.php 配置說明

以下為 Hyperf-Skeleton 中的 `config/autoload/server.php` 所提供的預設 `settings` 

```php
<?php
declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // 這裡省略了該檔案的其它配置
    'settings' => [
        'enable_coroutine' => true, // 開啟內建協程
        'worker_num' => swoole_cpu_num(), // 設定啟動的 Worker 程序數
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid', // master 程序的 PID
        'open_tcp_nodelay' => true, // TCP 連線傳送資料時會關閉 Nagle 合併演算法，立即發往客戶端連線
        'max_coroutine' => 100000, // 設定當前工作程序最大協程數量
        'open_http2_protocol' => true, // 啟用 HTTP2 協議解析
        'max_request' => 100000, // 設定 worker 程序的最大任務數
        'socket_buffer_size' => 2 * 1024 * 1024, // 配置客戶端連線的快取區長度
    ],
];
```

此配置檔案用於管理 Server 服務，其中的 `settings` 選項可以直接使用由 `Swoole Server` 提供的選項，其他選項可參考 [Swoole 官方文件](https://wiki.swoole.com/#/server/setting) 。

如需要設定守護程序化，可在 `settings` 中增加 `'daemonize' => true`，執行 `php bin/hyperf.php start`後，程式將轉入後臺作為守護程序執行

單獨的 Server 配置需要新增在對應 `servers` 的 `settings` 當中，如 `jsonrpc` 協議的 TCP Server 配置啟用 EOF 自動分包和設定 EOF 字串
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
                'open_eof_split' => true, // 啟用 EOF 自動分包
                'package_eof' => "\r\n", // 設定 EOF 字串
            ],
        ],
    ],
];

```

## `config.php` 與 `autoload` 資料夾內的配置檔案的關係

`config.php` 與 `autoload` 資料夾內的配置檔案在服務啟動時都會被掃描並注入到 `Hyperf\Contract\ConfigInterface` 對應的物件中，配置的結構為一個鍵值對的大陣列，兩種配置形式不同的在於 `autoload`  內配置檔案的檔名會作為第一層 鍵(Key) 存在，而 `config.php` 內的則以您定義的為第一層，我們透過下面的例子來演示一下。   
我們假設存在一個 `config/autoload/client.php` 檔案，檔案內容如下：
```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```
那麼我們想要得到 `timeout` 的值對應的 鍵(Key) 為 `client.request.timeout`；   

我們假設想要以相同的 鍵(Key) 獲得同樣的結果，但配置是寫在 `config/config.php` 檔案內的，那麼檔案內容應如下：
```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## 使用 Hyperf Config 元件

該元件是官方提供的預設的配置元件，是面向 `Hyperf\Contract\ConfigInterface` 介面實現的，由 [hyperf/config](https://github.com/hyperf/config) 元件內的 `ConfigProvider` 將 `Hyperf\Config\Config` 物件繫結到介面上。   

### 設定配置

只需在 `config/config.php` 與 `config/autoload/server.php` 與 `autoload` 資料夾內的配置，都能在服務啟動時被掃描並注入到 `Hyperf\Contract\ConfigInterface` 對應的物件中，這個流程是由 `Hyperf\Config\ConfigFactory` 在 Config 物件例項化時完成的。

### 獲取配置

Config 元件提供了三種方式獲取配置，透過 `Hyperf\Config\Config` 物件獲取、透過 `#[Value]` 註解獲取和透過 `config(string $key, $default)` 函式獲取。

#### 透過 Config 物件獲取配置

這種方式要求你已經拿到了 `Config` 物件的例項，預設物件為 `Hyperf\Config\Config`，注入例項的細節可查閱 [依賴注入](zh-tw/di.md) 章節；

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 透過 get(string $key, $default): mixed 方法獲取 $key 所對應的配置，$key 值可以透過 . 連線符定位到下級陣列，$default 則是當對應的值不存在時返回的預設值
$config->get($key，$default);
```

#### 透過 `#[Value]` 註解獲取配置

這種方式要求註解的應用物件必須是透過 [hyperf/di](https://github.com/hyperf/di) 元件建立的，注入例項的細節可查閱 [依賴注入](zh-tw/di.md) 章節，示例中我們假設 `IndexController` 就是一個已經定義好的 `Controller` 類，`Controller` 類一定是由 `DI` 容器創建出來的；   
`#[Value]` 內的字串則對應到 `$config->get($key)` 內的 `$key` 引數，在建立該物件例項時，對應的配置會自動注入到定義的類屬性中。

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

#### 透過 config 函式獲取

在任意地方可以透過 `config(string $key, $default)` 函式獲取對應的配置，但這樣的使用方式也就意味著您對 [hyperf/config](https://github.com/hyperf/config) 和 [hyperf/support](https://github.com/hyperf/support) 元件是強依賴的。

### 判斷配置是否存在

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 透過 has(): bool 方法判斷對應的 $key 值是否存在於配置中，$key 值可以透過 . 連線符定位到下級陣列
$config->has($key);
```

## 環境變數

對於不同的執行環境使用不同的配置是一種常見的需求，比如在測試環境和生產環境的 Redis 配置不一樣，而生產環境的配置又不能提交到原始碼版本管理系統中以免資訊洩露。   

在 Hyperf 裡我們提供了環境變數這一解決方案，透過利用 [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) 提供的環境變數解析功能，以及 `env()` 函式來獲取環境變數的值，這一需求解決起來是相當的容易。   

在新安裝好的 Hyperf 應用中，其根目錄會包含一個 `.env.example` 檔案。如果是透過 Composer 安裝的 Hyperf，該檔案會自動基於 `.env.example` 複製一個新檔案並命名為 `.env`。否則，需要你手動更改一下檔名。   

您的 `.env` 檔案不應該提交到應用的原始碼版本管理系統中，因為每個使用你的應用的開發人員 / 伺服器可能需要有一個不同的環境配置。此外，在入侵者獲得你的原始碼倉庫的訪問權的情況下，這會導致嚴重的安全問題，因為所有敏感的資料都被一覽無餘了。   

> `.env` 檔案中的所有變數均可被外部環境變數所覆蓋（比如伺服器級或系統級或 Docker 環境變數）。

### 環境變數型別

`.env` 檔案中的所有變數都會被解析為字串型別，因此提供了一些保留值以允許您從 `env()` 函式中獲取更多型別的變數：

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

如果你需要使用包含空格或包含其他特殊字元的環境變數，可以透過將值括在雙引號中來實現，比如：

```dotenv
APP_NAME="Hyperf Skeleton"
```

### 讀取環境變數

我們在上面也有提到環境變數可以透過 `env()` 函式獲取，在應用開發中，環境變數只應作為配置的一個值，透過環境變數的值來覆蓋配置的值，對於應用層來說應 **只使用配置**，而不是直接使用環境變數。   
我們舉個合理使用的例子：

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## 釋出元件配置

Hyperf 採用元件化設計，在新增一些元件進來骨架專案後，我們通常會需要為新新增的元件建立對應的配置檔案，以滿足對元件的使用。Hyperf 為元件提供了一個 `元件配置釋出機制`，透過該機制，您只需透過一個 `vendor:publish` 命令即可將元件預設的配置檔案模板釋出到骨架專案中來。
比如我們希望新增一個 `hyperf/foo` 元件 (該元件實際並不存在，僅示例) 以及該元件對應的配置檔案，在執行 `composer require hyperf/foo` 安裝之後，您可透過執行 `php bin/hyperf.php vendor:publish hyperf/foo` 來將元件預設的配置檔案，釋出到骨架專案的 `config/autoload` 資料夾內，具體要釋出的內容，由元件來定義提供。 

## 配置中心

Hyperf 為您提供了分散式系統的外部化配置支援，目前支援由攜程開源的 `Apollo`、阿里雲 ACM 應用配置管理、ETCD、Nacos 以及 Zookeeper 作為配置中心的支援。
關於配置中心的使用細節我們由 [配置中心](zh-tw/config-center.md) 章節來闡述。


