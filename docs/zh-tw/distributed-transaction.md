# 分散式事務

[dtm-client](https://github.com/dtm-php/dtm-client) 是由 Hyperf 團隊開發並維護的 DTM 分散式事務客戶端元件，配合 DTM-Server 可以實現分散式事務的管理，穩定可用於生產環境。   
[seata/seata-php](https://github.com/seata/seata-php) 是由 Hyperf 團隊開發並貢獻給 Seata 開源社群的 Seata PHP 客戶端元件，配合 Seata-Server 可以實現分散式事務的管理，目前仍在開發迭代中，尚未能用於生產環境，希望大家能夠共同參與進來加速孵化。

# DTM-Client 介紹

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) 是分散式事務管理器 [DTM](https://github.com/dtm-labs/dtm) 的 PHP 客戶端，已支援 TCC 模式、Saga、XA、二階段訊息模式的分散式事務模式，並分別實現了與 DTM Server 以 HTTP 協議或 gRPC 協議通訊，該客戶端可安全運行於 PHP-FPM 和 Swoole 協程環境中，更是對 [Hyperf](https://github.com/hyperf/hyperf) 做了更加易用的功能支援。

# 關於 DTM

DTM 是一款基於 Go 語言實現的開源分散式事務管理器，提供跨語言，跨儲存引擎組合事務的強大功能。DTM 優雅的解決了冪等、空補償、懸掛等分散式事務難題，也提供了簡單易用、高效能、易水平擴充套件的分散式事務解決方案。

## 亮點

* 極易上手
    - 零配置啟動服務，提供非常簡單的 HTTP 介面，極大降低上手分散式事務的難度
* 跨語言
    - 可適合多語言棧的公司使用。方便 Go、Python、PHP、NodeJs、Ruby、C# 等各類語言使用。
* 使用簡單
    - 開發者不再擔心懸掛、空補償、冪等各類問題，首創子事務屏障技術代為處理
* 易部署、易擴充套件
    - 僅依賴 MySQL/Redis，部署簡單，易叢集化，易水平擴充套件
* 多種分散式事務協議支援
    - TCC、SAGA、XA、二階段訊息，一站式解決多種分散式事務問題

## 對比

在非 Java 語言下，暫未看到除 DTM 之外的成熟的分散式事務管理器，因此這裡將 DTM 和 Java 中最成熟的開源專案 Seata 做對比：

|  特性| DTM |                                              SEATA                                               |備註|
|:-----:|:----:|:------------------------------------------------------------------------------------------------:|:----:|
|[支援語言](https://dtm.pub/other/opensource.html#lang) |<span style="color:green">Go、C#、Java、Python、PHP...</span>|                            <span style="color:orange">Java、Go</span>                             |DTM 可輕鬆接入一門新語言|
|[儲存引擎](https://dtm.pub/other/opensource.html#store) |<span style="color:green"> 支援資料庫、Redis、Mongo 等 </span>|                              <span style="color:orange"> 資料庫 </span>                               ||
|[異常處理](https://dtm.pub/other/opensource.html#exception)| <span style="color:green"> 子事務屏障自動處理 </span>|                              <span style="color:orange"> 手動處理 </span>                              |DTM 解決了冪等、懸掛、空補償|
|[SAGA 事務](https://dtm.pub/other/opensource.html#saga) |<span style="color:green"> 極簡易用 </span> |                             <span style="color:orange"> 複雜狀態機 </span>                              ||
|[二階段訊息](https://dtm.pub/other/opensource.html#msg)|<span style="color:green">✓</span>|                                 <span style="color:red">✗</span>                                 |最簡訊息最終一致性架構|
|[TCC 事務](https://dtm.pub/other/opensource.html#tcc)| <span style="color:green">✓</span>|                                <span style="color:green">✓</span>                                ||
|[XA 事務](https://dtm.pub/other/opensource.html#xa)|<span style="color:green">✓</span>|                                <span style="color:green">✓</span>                                ||
|[AT 事務](https://dtm.pub/other/opensource.html#at)|<span style="color:orange"> 建議使用 XA</span>|                                <span style="color:green">✓</span>                                |AT 與 XA 類似，但有髒回滾|
|[單服務多資料來源](https://dtm.pub/other/opensource.html#multidb)|<span style="color:green">✓</span>|                                 <span style="color:red">✗</span>                                 ||
|[通訊協議](https://dtm.pub/other/opensource.html#protocol)|HTTP、gRPC|                                             Dubbo 等協議                                             |DTM 對雲原生更加友好|
|[star 數量](https://dtm.pub/other/opensource.html#star)|<img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/>| <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> |DTM 從 2021-06-04 釋出 0.1 版本，發展飛快|

從上面對比的特性來看，DTM 在許多方面都具備很大的優勢。如果考慮多語言支援、多儲存引擎支援，那麼 DTM 毫無疑問是您的首選.

# 安裝

透過 Composer 可以非常方便的安裝 dtm-client

```bash
composer require dtm/dtm-client
```

* 使用時別忘了啟動 DTM Server 哦

# 配置

## 配置檔案

如果您是在 Hyperf 框架中使用，在安裝元件後，可透過下面的 `vendor:publish` 命令一件釋出配置檔案於 `./config/autoload/dtm.php`

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

如果您是在非 Hyperf 框架中使用，可複製 `./vendor/dtm/dtm-client/publish/dtm.php` 檔案到對應的配置目錄中。

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    // 客戶端與 DTM Server 通訊的協議，支援 Protocol::HTTP 和 Protocol::GRPC 兩種
    'protocol' => Protocol::HTTP,
    // DTM Server 的地址
    'server' => '127.0.0.1',
    // DTM Server 的埠
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // 子事務屏障配置
    'barrier' => [
        // DB 模式下的子事務屏障配置
        'db' => [
            'type' => DbType::MySQL
        ],
        // Redis 模式下的子事務屏障配置
        'redis' => [
            // 子事務屏障記錄的超時時間
            'expire_seconds' => 7 * 86400,
        ],
        // 非 Hyperf 框架下應用子事務屏障的類
        'apply' => [],
    ],
    // HTTP 協議下 Guzzle 客戶端的通用配置
    'guzzle' => [
        'options' => [],
    ],
];
```

## 配置中介軟體

在使用之前，需要配置 `DtmClient\Middleware\DtmMiddleware` 中介軟體作為 Server 的全域性中介軟體，該中介軟體支援 PSR-15 規範，可適用於各個支援該規範的的框架。   
在 Hyperf 中的中介軟體配置可參考 [Hyperf 文件 - 中介軟體](https://www.hyperf.wiki/2.2/#/zh-cn/middleware/middleware) 一章。

# 使用

dtm-client 的使用非常簡單，我們提供了一個示例專案 [dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) 來幫助大家更好的理解和除錯。   
在使用該元件之前，也強烈建議您先閱讀 [DTM 官方文件](https://dtm.pub/)，以做更詳細的瞭解。

## TCC 模式

TCC 模式是一種非常流行的柔性事務解決方案，由 Try-Confirm-Cancel 三個單詞的首字母縮寫分別組成 TCC 的概念，最早是由 Pat Helland 於 2007 年發表的一篇名為《Life beyond Distributed Transactions:an Apostate’s Opinion》的論文中提出。

### TCC 的 3 個階段

Try 階段：嘗試執行，完成所有業務檢查（一致性）, 預留必須業務資源（準隔離性）  
Confirm 階段：如果所有分支的 Try 都成功了，則走到 Confirm 階段。Confirm 真正執行業務，不作任何業務檢查，只使用 Try 階段預留的業務資源  
Cancel 階段：如果所有分支的 Try 有一個失敗了，則走到 Cancel 階段。Cancel 釋放 Try 階段預留的業務資源。

如果我們要進行一個類似於銀行跨行轉賬的業務，轉出（TransOut）和轉入（TransIn）分別在不同的微服務裡，一個成功完成的 TCC 事務典型的時序圖如下：

<img src="https://dtm.pub/assets/tcc_normal.dea14fb3.jpg" height=600 />

### 程式碼示例

以下展示在 Hyperf 框架中的使用方法，其它框架類似

```php
<?php
namespace App\Controller;

use DtmClient\TCC;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Throwable;

#[Controller(prefix: '/tcc')]
class TccController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';

    #[Inject]
    protected TCC $tcc;

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        try {
            
            $this->tcc->globalTransaction(function (TCC $tcc) {
                // 建立子事務 A 的呼叫資料
                $tcc->callBranch(
                    // 呼叫 Try 方法的引數
                    ['amount' => 30],
                    // Try 方法的 URL
                    $this->serviceUri . '/tcc/transA/try',
                    // Confirm 方法的 URL
                    $this->serviceUri . '/tcc/transA/confirm',
                    // Cancel 方法的 URL
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // 建立子事務 B 的呼叫資料，以此類推
                $tcc->callBranch(
                    ['amount' => 30],
                    $this->serviceUri . '/tcc/transB/try',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
        }
        // 透過 TransContext::getGid() 獲得 全域性事務ID 並返回
        return TransContext::getGid();
    }
}
```

## Saga 模式

Saga 模式是分散式事務領域最有名氣的解決方案之一，也非常流行於各大系統中，最初出現在 1987 年 由 Hector Garcaa-Molrna & Kenneth Salem 發表的論文 [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf) 裡。

Saga 是一種最終一致性事務，也是一種柔性事務，又被叫做 長時間執行的事務（Long-running-transaction），Saga 是由一系列的本地事務構成。每一個本地事務在更新完資料庫之後，會發布一條訊息或者一個事件來觸發 Saga 全域性事務中的下一個本地事務的執行。如果一個本地事務因為某些業務規則無法滿足而失敗，Saga 會執行在這個失敗的事務之前成功提交的所有事務的補償操作。所以 Saga 模式在對比 TCC 模式時，因缺少了資源預留的步驟，往往在實現回滾邏輯時會變得更麻煩。

### Saga 子事務拆分

比如我們要進行一個類似於銀行跨行轉賬的業務，將 A 賬戶中的 30 元轉到 B 賬戶，根據 Saga 事務的原理，我們將整個全域性事務，拆分為以下服務：
- 轉出（TransOut）服務，這裡將會進行操作 A 賬戶扣減 30 元
- 轉出補償（TransOutCompensate）服務，回滾上面的轉出操作，即 A 賬戶增加 30 元
- 轉入（TransIn）服務，這裡將會進行 B  賬戶增加 30 元
- 轉入補償（TransInCompensate）服務，回滾上面的轉入操作，即 B 賬戶減少 30 元

整個事務的邏輯是：

執行轉出成功 => 執行轉入成功 => 全域性事務完成

如果在中間發生錯誤，例如轉入 B 賬戶發生錯誤，則會呼叫已執行分支的補償操作，即：

執行轉出成功 => 執行轉入失敗 => 執行轉入補償成功 => 執行轉出補償成功 => 全域性事務回滾完成

下面是一個成功完成的 SAGA 事務典型的時序圖：

<img src="https://dtm.pub/assets/saga_normal.a2849672.jpg" height=428 />

### 程式碼示例

以下展示在 Hyperf 框架中的使用方法，其它框架類似

```php
namespace App\Controller;

use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/saga')]
class SagaController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';
    
    #[Inject]
    protected Saga $saga;

    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // 初始化 Saga 事務
        $this->saga->init();
        // 增加轉出子事務
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // 增加轉入子事務
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // 提交 Saga 事務
        $this->saga->submit();
        // 透過 TransContext::getGid() 獲得 全域性事務ID 並返回
        return TransContext::getGid();
    }
}
```

## XA 模式
XA 是由 X /Open 組織提出的分散式事務的規範，XA 規範主要定義了(全域性)事務管理器(TM)和(區域性)資源管理器(RM)之間的介面。本地的資料庫如 mysql 在 XA 中扮演的是 RM 角色

XA 一共分為兩階段：

第一階段（prepare）：即所有的參與者 RM 準備執行事務並鎖住需要的資源。參與者 ready 時，向 TM 報告已準備就緒。 第二階段 (commit/rollback)：當事務管理者(TM)確認所有參與者(RM)都 ready 後，向所有參與者傳送 commit 命令。

目前主流的資料庫基本都支援 XA 事務，包括 mysql、oracle、sqlserver、postgre

下面是一個成功完成的 XA 事物典型的時序圖

<img src="https://dtm.pub/assets/xa_normal.5a0ce600.jpg" height=600/>

### 程式碼示例

以下展示在 Hyperf 框架中的使用方法，其它框架類似
```php
<?php

namespace App\Controller;

use App\Grpc\GrpcClient;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/xa')]
class XAController
{

    private GrpcClient $grpcClient;

    protected string $serviceUri = 'http://127.0.0.1:9502';

    public function __construct(
        private XA $xa,
        protected ConfigInterface $config,
    ) {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        $this->grpcClient = new GrpcClient($hostname);
    }


    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // 開啟Xa 全域性事物
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // 呼叫子事物介面
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // XA http模式下獲取子事物返回結構
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // 呼叫子事物介面
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // 透過 TransContext::getGid() 獲得 全域性事務ID 並返回
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // 模擬分散式系統下transIn方法
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 請使用 DBTransactionInterface 處理本地 Mysql 事物
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` + ? where id = 1', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transOut')]
    public function transOut(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 10;
        // 模擬分散式系統下transOut方法
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // 請使用 DBTransactionInterface 處理本地 Mysql 事物
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}

```
上面的程式碼首先註冊了一個全域性 XA 事務，然後添加了兩個子事務 transIn、transOut。子事務全部執行成功之後，提交給 dtm。dtm 收到提交的 xa 全域性事務後，會呼叫所有子事務的 xa commit，完成整個 xa 事務。
