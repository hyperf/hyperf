# 版本更新記錄

# v1.1.8 - 2019-11-28

## 新增

- [#965](https://github.com/hyperf/hyperf/pull/965) 新增 Redis Lua 模塊，用於管理 Lua 腳本；
- [#1023](https://github.com/hyperf/hyperf/pull/1023) hyperf/metric 組件的 Prometheus 驅動新增 CUSTOM_MODE 模式；

## 修復

- [#1013](https://github.com/hyperf/hyperf/pull/1013) 修復 JsonRpcPoolTransporter 配置合併失敗的問題；
- [#1006](https://github.com/hyperf/hyperf/pull/1006) 修復 `gen:model` 命令生成的屬性的順序；

## 變更

- [#1021](https://github.com/hyperf/hyperf/pull/1012) WebSocket 客户端新增默認端口支持，根據協議默認為 80 和 443；
- [#1034](https://github.com/hyperf/hyperf/pull/1034) 去掉了 `Hyperf\Amqp\Builder\Builder` 的 `arguments` 參數的 array 類型限制，允許接受其他類型如 AmqpTable；

## 優化

- [#1014](https://github.com/hyperf/hyperf/pull/1014) 優化 `Command::execute` 的返回值類型；
- [#1022](https://github.com/hyperf/hyperf/pull/1022) 提供更清晰友好的連接池報錯信息；
- [#1039](https://github.com/hyperf/hyperf/pull/1039) 在 CoreMiddleware 中自動設置最新的 ServerRequest 對象到 Context；

# v1.1.7 - 2019-11-21

## 新增

- [#860](https://github.com/hyperf/hyperf/pull/860) 新增 [hyperf/retry](https://github.com/hyperf/retry) 組件；
- [#952](https://github.com/hyperf/hyperf/pull/952) 新增 ThinkTemplate 視圖引擎支持；
- [#973](https://github.com/hyperf/hyperf/pull/973) 新增 JSON RPC 在 TCP 協議下的連接池支持，通過 `Hyperf\JsonRpc\JsonRpcPoolTransporter` 來使用連接池版本；
- [#976](https://github.com/hyperf/hyperf/pull/976) 為 `hyperf/amqp` 組件新增  `close_on_destruct` 選項參數，用來控制代碼在執行析構函數時是否主動去關閉連接；

## 變更

- [#944](https://github.com/hyperf/hyperf/pull/944) 將組件內所有使用 `@Listener` 和 `@Process` 註解來註冊的改成通過 `ConfigProvider`來註冊；
- [#977](https://github.com/hyperf/hyperf/pull/977) 調整 `init-proxy.sh` 命令的行為，改成只刪除 `runtime/container` 目錄；

## 修復

- [#955](https://github.com/hyperf/hyperf/pull/955) 修復 `hyperf/db` 組件的 `port` 和 `charset` 參數無效的問題；
- [#956](https://github.com/hyperf/hyperf/pull/956) 修復模型緩存中使用到`RedisHandler::incr` 在集羣模式下會失敗的問題；
- [#966](https://github.com/hyperf/hyperf/pull/966) 修復當在非 Worker 進程環境下使用分頁器會報錯的問題；
- [#968](https://github.com/hyperf/hyperf/pull/968) 修復當 `classes` 和 `annotations` 兩種 Aspect 切入模式同時存在於一個類時，其中一個可能會失效的問題；
- [#980](https://github.com/hyperf/hyperf/pull/980) 修復 Session 組件內 `migrate`, `save` 核 `has` 方法無法使用的問題；
- [#982](https://github.com/hyperf/hyperf/pull/982) 修復 `Hyperf\GrpcClient\GrpcClient::yield` 在獲取 Channel Pool 時沒有通過正確的獲取方式去獲取的問題；
- [#987](https://github.com/hyperf/hyperf/pull/987) 修復通過 `gen:command` 命令生成的命令類缺少調用 `parent::configure()` 方法的問題；

## 優化

- [#991](https://github.com/hyperf/hyperf/pull/991) 優化 `Hyperf\DbConnection\ConnectionResolver::connection`的異常情況處理；

# v1.1.6 - 2019-11-14

## 新增

- [#827](https://github.com/hyperf/hyperf/pull/827) 新增了極簡的高性能的 DB 組件；
- [#905](https://github.com/hyperf/hyperf/pull/905) 視圖組件增加了 `twig` 模板引擎；
- [#911](https://github.com/hyperf/hyperf/pull/911) 定時任務支持多實例情況下，只運行單一實例的定時任務；
- [#913](https://github.com/hyperf/hyperf/pull/913) 增加監聽器 `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`；
- [#921](https://github.com/hyperf/hyperf/pull/921) 新增 `Session` 組件；
- [#931](https://github.com/hyperf/hyperf/pull/931) 阿波羅配置中心增加 `strict_mode`，自動將配置轉化成對應數據類型；
- [#933](https://github.com/hyperf/hyperf/pull/933) 視圖組件增加了 `plates` 模板引擎；
- [#937](https://github.com/hyperf/hyperf/pull/937) Nats 組件添加消費者消費和訂閲事件；
- [#941](https://github.com/hyperf/hyperf/pull/941) 新增 `Zookeeper` 配置中心；

## 變更

- [#934](https://github.com/hyperf/hyperf/pull/934) 修改 `WaitGroup` 繼承 `\Swoole\Coroutine\WaitGroup`；

## 修復

- [#897](https://github.com/hyperf/hyperf/pull/897) 修復 `Nats` 消費者，`pool` 配置無效的 BUG；
- [#901](https://github.com/hyperf/hyperf/pull/901) 修復 `GraphQL` 組件，`Factory` 註解無法正常使用的 BUG；
- [#903](https://github.com/hyperf/hyperf/pull/903) 修復添加 `hyperf/rpc-client` 依賴後，`init-proxy` 腳本無法正常停止的 BUG；
- [#904](https://github.com/hyperf/hyperf/pull/904) 修復監聽器監聽 `Hyperf\Framework\Event\BeforeMainServerStart` 事件時，無法使用 `IO` 操作的 BUG；
- [#906](https://github.com/hyperf/hyperf/pull/906) 修復 `Hyperf\HttpMessage\Server\Request` 端口獲取有誤的 BUG；
- [#907](https://github.com/hyperf/hyperf/pull/907) 修復 `Nats` 組件 `requestSync` 方法，超時時間不準確的 BUG；
- [#909](https://github.com/hyperf/hyperf/pull/909) 修復 `Parallel` 內邏輯拋錯後，無法正常停止的 BUG；
- [#925](https://github.com/hyperf/hyperf/pull/925) 修復因 `Socket` 無法正常建立，導致進程頻繁重啟的 BUG；
- [#932](https://github.com/hyperf/hyperf/pull/932) 修復 `Translator::setLocale` 在協程環境下，數據混淆的 BUG；
- [#940](https://github.com/hyperf/hyperf/pull/940) 修復 `WebSocketClient::push` 方法 `finish` 參數類型錯誤；

## 優化

- [#907](https://github.com/hyperf/hyperf/pull/907) 優化 `Nats` 消費者頻繁重啟；
- [#928](https://github.com/hyperf/hyperf/pull/928) `Hyperf\ModelCache\Cacheable::query` 批量修改數據時，可以刪除對應緩存；
- [#936](https://github.com/hyperf/hyperf/pull/936) 優化調用模型緩存 `increment` 時，可能因併發情況導致的數據有錯；

# v1.1.5 - 2019-11-07

## 新增

- [#812](https://github.com/hyperf/hyperf/pull/812) 新增計劃任務在集羣下僅執行一次的支持；
- [#820](https://github.com/hyperf/hyperf/pull/820) 新增 hyperf/nats 組件；
- [#832](https://github.com/hyperf/hyperf/pull/832) 新增 `Hyperf\Utils\Codec\Json`；
- [#833](https://github.com/hyperf/hyperf/pull/833) 新增 `Hyperf\Utils\Backoff`；
- [#852](https://github.com/hyperf/hyperf/pull/852) 為 `Hyperf\Utils\Parallel` 新增 `clear()` 方法來清理所有已添加的回調；
- [#854](https://github.com/hyperf/hyperf/pull/854) 新增 `Hyperf\GraphQL\GraphQLMiddleware` 用於解析 GraphQL 請求；
- [#859](https://github.com/hyperf/hyperf/pull/859) 新增 Consul 集羣的支持，現在可以從 Consul 集羣中拉取服務提供者的節點信息；
- [#873](https://github.com/hyperf/hyperf/pull/873) 新增 Redis 集羣的客户端支持；

## 修復

- [#831](https://github.com/hyperf/hyperf/pull/831) 修復 Redis 客户端連接在 Redis Server 重啟後不會自動重連的問題；
- [#835](https://github.com/hyperf/hyperf/pull/835) 修復 `Request::inputs` 方法的默認值參數與預期效果不一致的問題；
- [#841](https://github.com/hyperf/hyperf/pull/841) 修復數據庫遷移在多數據庫的情況下連接無效的問題；
- [#844](https://github.com/hyperf/hyperf/pull/844) 修復 Composer 閲讀器不支持根命名空間的用法的問題；
- [#846](https://github.com/hyperf/hyperf/pull/846) 修復 Redis 客户端的 `scan`, `hScan`, `zScan`, `sScan` 無法使用的問題；
- [#850](https://github.com/hyperf/hyperf/pull/850) 修復 Logger group 在 name 一樣時不生效的問題；

## 優化

- [#832](https://github.com/hyperf/hyperf/pull/832) 優化了 Response 對象在轉 JSON 格式時的異常處理邏輯；
- [#840](https://github.com/hyperf/hyperf/pull/840) 使用 `\Swoole\Timer::*` 來替代 `swoole_timer_*` 函數；
- [#859](https://github.com/hyperf/hyperf/pull/859) 優化了 RPC 客户端去 Consul 獲取健康的節點信息的邏輯；

# v1.1.4 - 2019-10-31

## 新增

- [#778](https://github.com/hyperf/hyperf/pull/778) `Hyperf\Testing\Client` 新增 `PUT` 和 `DELETE`方法。
- [#784](https://github.com/hyperf/hyperf/pull/784) 新增服務監控組件。
- [#795](https://github.com/hyperf/hyperf/pull/795) `AbstractProcess` 增加 `restartInterval` 參數，允許子進程異常或正常退出後，延遲重啟。
- [#804](https://github.com/hyperf/hyperf/pull/804) `Command` 增加事件 `BeforeHandle` `AfterHandle` 和 `FailToHandle`。

## 變更

- [#793](https://github.com/hyperf/hyperf/pull/793) `Pool::getConnectionsInChannel` 方法由 `protected` 改為 `public`.
- [#811](https://github.com/hyperf/hyperf/pull/811) 命令 `di:init-proxy` 不再主動清理代理緩存，如果想清理緩存請使用命令 `vendor/bin/init-proxy.sh`。

## 修復

- [#779](https://github.com/hyperf/hyperf/pull/779) 修復 `JPG` 文件驗證不通過的問題。
- [#787](https://github.com/hyperf/hyperf/pull/787) 修復 `db:seed` 參數 `--class` 多餘，導致報錯的問題。
- [#795](https://github.com/hyperf/hyperf/pull/795) 修復自定義進程在異常拋出後，無法正常重啟的 BUG。
- [#796](https://github.com/hyperf/hyperf/pull/796) 修復 `etcd` 配置中心 `enable` 即時設為 `false`，在項目啟動時，依然會拉取配置的 BUG。

## 優化

- [#781](https://github.com/hyperf/hyperf/pull/781) 可以根據國際化組件配置發佈驗證器語言包到規定位置。
- [#796](https://github.com/hyperf/hyperf/pull/796) 優化 `ETCD` 客户端，不會多次創建 `HandlerStack`。 
- [#797](https://github.com/hyperf/hyperf/pull/797) 優化子進程重啟

# v1.1.3 - 2019-10-24

## 新增

- [#745](https://github.com/hyperf/hyperf/pull/745) 為 `gen:model` 命令增加 `with-comments` 選項，以標記是否生成字段註釋；
- [#747](https://github.com/hyperf/hyperf/pull/747) 為 AMQP 消費者增加 `AfterConsume`, `BeforeConsume`, `FailToConsume` 事件； 
- [#762](https://github.com/hyperf/hyperf/pull/762) 為 Parallel 特性增加協程控制功能；

## 變更

- [#767](https://github.com/hyperf/hyperf/pull/767) 重命名 `AbstractProcess` 的 `running` 屬性名為 `listening`；

## 修復

- [#741](https://github.com/hyperf/hyperf/pull/741) 修復執行 `db:seed` 命令缺少文件名報錯的問題；
- [#748](https://github.com/hyperf/hyperf/pull/748) 修復 `SymfonyNormalizer` 不處理 `array` 類型數據的問題；
- [#769](https://github.com/hyperf/hyperf/pull/769) 修復當 JSON RPC 響應的結果的 result 和 error 屬性為 null 時會拋出一個無效請求的問題；

# v1.1.2 - 2019-10-17

## 新增

- [#722](https://github.com/hyperf-cloud/hyperf/pull/722) 為 AMQP Consumer 新增 `concurrent.limit` 配置來對協程消費進行速率限制；

## 變更

- [#678](https://github.com/hyperf-cloud/hyperf/pull/678) 為 `gen:model` 命令增加 `ignore-tables` 參數，同時默認屏蔽 `migrations` 表，即 `migrations` 表對應的模型在執行 `gen:model` 命令時不會生成；

## 修復

- [#694](https://github.com/hyperf-cloud/hyperf/pull/694) 修復 `Hyperf\Validation\Request\FormRequest` 的 `validationData` 方法不包含上傳的文件的問題；
- [#700](https://github.com/hyperf-cloud/hyperf/pull/700) 修復 `Hyperf\HttpServer\Contract\ResponseInterface` 的 `download` 方法不能按預期運行的問題；
- [#701](https://github.com/hyperf-cloud/hyperf/pull/701) 修復自定義進程在出現未捕獲的異常時不會自動重啟的問題；
- [#704](https://github.com/hyperf-cloud/hyperf/pull/704) 修復 `Hyperf\Validation\Middleware\ValidationMiddleware` 在 action 參數沒有定義參數類型時會報錯的問題；
- [#713](https://github.com/hyperf-cloud/hyperf/pull/713) 修復當開啟了註解緩存功能是，`ignoreAnnotations` 不能按預期工作的問題；
- [#717](https://github.com/hyperf-cloud/hyperf/pull/717) 修復 `getValidatorInstance` 方法會重複創建驗證器對象的問題；
- [#724](https://github.com/hyperf-cloud/hyperf/pull/724) 修復 `db:seed` 命令在沒有傳 `database` 參數時會報錯的問題； 
- [#729](https://github.com/hyperf-cloud/hyperf/pull/729) 修正組件配置項 `db:model` 為 `gen:model`；
- [#737](https://github.com/hyperf-cloud/hyperf/pull/737) 修復非 Worker 進程下無法使用 Tracer 組件來追蹤調用鏈的問題；

# v1.1.1 - 2019-10-08

## Fixed

- [#664](https://github.com/hyperf/hyperf/pull/664) 調整通過 `gen:request` 命令生成 FormRequest 時 `authorize` 方法的默認返回值；
- [#665](https://github.com/hyperf/hyperf/pull/665) 修復啟動時永遠會自動生成代理類的問題；
- [#667](https://github.com/hyperf/hyperf/pull/667) 修復當訪問一個不存在的路由時 `Hyperf\Validation\Middleware\ValidationMiddleware` 會拋出異常的問題；
- [#672](https://github.com/hyperf/hyperf/pull/672) 修復當 Action 方法上的參數類型為非對象類型時 `Hyperf\Validation\Middleware\ValidationMiddleware` 會拋出一個未捕獲的異常的問題；
- [#674](https://github.com/hyperf/hyperf/pull/674) 修復使用 `gen:model` 命令從數據庫生成模型時模型表名錯誤的問題；

# v1.1.0 - 2019-10-08

## 新增

- [#401](https://github.com/hyperf/hyperf/pull/401) 新增了 `Hyperf\HttpServer\Router\Dispatched` 對象來儲存解析的路由信息，在用户中間件之前便解析完成以便後續的使用，同時也修復了路由裏帶參時中間件失效的問題；
- [#402](https://github.com/hyperf/hyperf/pull/402) 新增 `@AsyncQueueMessage` 註解，通過定義此註解在方法上，表明這個方法的實際運行邏輯是投遞給 Async-Queue 隊列去消費；
- [#418](https://github.com/hyperf/hyperf/pull/418) 允許發送 WebSocket 消息到任意的 fd，即使當前的 Worker 進程不持有對應的 fd，框架會自動進行進程間通訊來實現發送；
- [#420](https://github.com/hyperf/hyperf/pull/420) 為數據庫模型增加新的事件機制，與 PSR-15 的事件調度器相配合，可以解耦的定義 Listener 來監聽模型事件；
- [#429](https://github.com/hyperf/hyperf/pull/429) [#643](https://github.com/hyperf/hyperf/pull/643) 新增 Validation 表單驗證器組件，這是一個衍生於 [illuminate/validation](https://github.com/illuminate/validation) 的組件，感謝 Laravel 開發組提供如此好用的驗證器組件，；
- [#441](https://github.com/hyperf/hyperf/pull/441) 當 Redis 連接處於低使用頻率的情況下自動關閉空閒連接；
- [#478](https://github.com/hyperf/hyperf/pull/441) 更好的適配 OpenTracing 協議，同時適配 [Jaeger](https://www.jaegertracing.io/)，Jaeger 是一款優秀的開源的端對端分佈式調用鏈追蹤系統；
- [#500](https://github.com/hyperf/hyperf/pull/499) 為 `Hyperf\HttpServer\Contract\ResponseInterface` 增加鏈式方法調用支持，解決調用了代理方法的方法後無法再調用原始方法的問題；
- [#523](https://github.com/hyperf/hyperf/pull/523) 為  `gen:model` 命令新增了 `table-mapping` 選項；
- [#555](https://github.com/hyperf/hyperf/pull/555) 新增了一個全局函數 `swoole_hook_flags` 來獲取由常量 `SWOOLE_HOOK_FLAGS` 所定義的 Runtime Hook 等級，您可以在 `bin/hyperf.php` 通過 `! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);` 的方式來定義該常量，即 Runtime Hook 等級；
- [#596](https://github.com/hyperf/hyperf/pull/596)  為`@Inject` 註解增加了  `required` 參數，當您定義 `@Inject(required=false)` 註解到一個成員屬性上，那麼當該依賴項不存在時也不會拋出 `Hyperf\Di\Exception\NotFoundException` 異常，而是以默認值 `null` 來注入， `required` 參數的默認值為 `true`，當在構造器注入的情況下，您可以通過對構造器的參數定義為 `nullable` 來達到同樣的目的；
- [#597](https://github.com/hyperf/hyperf/pull/597) 為 AsyncQueue 組件的消費者增加 `Concurrent` 來控制消費速率；
- [#599](https://github.com/hyperf/hyperf/pull/599) 為 AsyncQueue 組件的消費者增加根據當前重試次數來設定該消息的重試等待時長的功能，可以為消息設置階梯式的重試等待；
- [#619](https://github.com/hyperf/hyperf/pull/619) 為 Guzzle 客户端增加 HandlerStackFactory 類，以便更便捷地創建一個 HandlerStack；
- [#620](https://github.com/hyperf/hyperf/pull/620) 為 AsyncQueue 組件的消費者增加自動重啟的機制；
- [#629](https://github.com/hyperf/hyperf/pull/629) 允許通過配置文件的形式為 Apollo 客户端定義  `clientIp`, `pullTimeout`, `intervalTimeout` 配置；
- [#647](https://github.com/hyperf/hyperf/pull/647) 根據 server 的配置，自動為 TCP Response 追加 `eof`；
- [#648](https://github.com/hyperf/hyperf/pull/648) 為 AMQP Consumer 增加 `nack` 的返回類型，當消費邏輯返回 `Hyperf\Amqp\Result::NACK` 時抽象消費者會以 `basic_nack` 方法來響應消息；
- [#654](https://github.com/hyperf/hyperf/pull/654) 增加所有 Swoole Event 的默認回調和對應的 Hyperf 事件；

## 變更

- [#437](https://github.com/hyperf/hyperf/pull/437) `Hyperf\Testing\Client` 在遇到異常時不再直接拋出異常而是交給 ExceptionHandler 流程處理；
- [#463](https://github.com/hyperf/hyperf/pull/463) 簡化了 `container.php` 文件及優化了註解緩存機制；

新的 config/container.php 文件內容如下：

```php
<?php

use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Hyperf\Utils\ApplicationContext;

$container = new Container((new DefinitionSourceFactory(true))());

if (! $container instanceof \Psr\Container\ContainerInterface) {
    throw new RuntimeException('The dependency injection container is invalid.');
}
return ApplicationContext::setContainer($container);
```

- [#486](https://github.com/hyperf/hyperf/pull/486) `Hyperf\HttpMessage\Server\Request` 的 `getParsedBody` 方法現在可以直接處理 JSON 格式的數據了；
- [#523](https://github.com/hyperf/hyperf/pull/523) 調整 `gen:model` 命令生成的模型類名默認為單數，如果表名為複數，則默認生成的類名為單數；
- [#614](https://github.com/hyperf/hyperf/pull/614) [#617](https://github.com/hyperf/hyperf/pull/617) 調整了 ConfigProvider 類的結構, 同時將 `config/dependencies.php` 文件移動到了 `config/autoload/dependencies.php` 內，且文件結構去除了 `dependencies` 層，此後也意味着您也可以將 `dependencies` 配置寫到 `config/config.php` 文件內；

Config Provider 內數據結構的變化：
之前：

```php
'scan' => [
    'paths' => [
        __DIR__,
    ],
    'collectors' => [],
],
```

現在：

```php
'annotations' => [
    'scan' => [
        'paths' => [
            __DIR__,
        ],
        'collectors' => [],
    ],
],
```

> 增加了一層 annotations，這樣將與配置文件結構一致，不再特殊

- [#630](https://github.com/hyperf/hyperf/pull/630) 變更了 `Hyperf\HttpServer\CoreMiddleware` 類的實例化方式，使用 `make()` 來替代了 `new`；
- [#631](https://github.com/hyperf/hyperf/pull/631) 變更了 AMQP Consumer 的實例化方式，使用 `make()` 來替代了 `new`；
- [#637](https://github.com/hyperf/hyperf/pull/637) 調整了 `Hyperf\Contract\OnMessageInterface` 和 `Hyperf\Contract\OnOpenInterface` 的第一個參數的類型約束， 使用 `Swoole\WebSocket\Server` 替代 `Swoole\Server`；
- [#638](https://github.com/hyperf/hyperf/pull/638) 重命名了 `db:model` 命令為 `gen:model` 命令，同時增加了一個 Visitor 來優化創建的 `$connection` 成員屬性，如果要創建的模型類的 `$connection` 屬性的值與繼承的父類一致，那麼創建的模型類將不會包含此屬性；

## 移除

- [#401](https://github.com/hyperf/hyperf/pull/401) 移除了 `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\HttpServer\ServerFactory`, `Hyperf\GrpcServer\ServerFactory` 類；
- [#402](https://github.com/hyperf/hyperf/pull/402) 移除了棄用的 `AsyncQueue::delay` 方法；
- [#563](https://github.com/hyperf/hyperf/pull/563) 移除了棄用的 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量，使用 `Hyperf\Server\ServerInterface::SERVER_BASE` 來替代；
- [#602](https://github.com/hyperf/hyperf/pull/602) 移除了 `Hyperf\Utils\Coroutine\Concurrent` 的 `timeout` 參數；
- [#612](https://github.com/hyperf/hyperf/pull/612) 移除了 RingPHP Handler 裏沒有使用到的 `$url` 變量；
- [#616](https://github.com/hyperf/hyperf/pull/616) [#618](https://github.com/hyperf/hyperf/pull/618) 移除了 Guzzle 裏一些無用的代碼；

## 優化

- [#644](https://github.com/hyperf/hyperf/pull/644) 優化了註解掃描的流程，分開 `app` 和 `vendor` 兩部分來掃描註解，大大減少了用户的掃描耗時；
- [#653](https://github.com/hyperf/hyperf/pull/653) 優化了 Swoole shortname 的檢測邏輯，現在的檢測邏輯更加貼合 Swoole 的實際配置場景，也不只是 `swoole.use_shortname = "Off"` 才能通過檢測了；

## 修復

- [#448](https://github.com/hyperf/hyperf/pull/448) 修復了當 HTTP Server 或 WebSocket Server 存在時，TCP Server 有可能無法啟動的問題；
- [#623](https://github.com/hyperf/hyperf/pull/623) 修復了當傳遞一個 `null` 值到代理類的方法參數時，方法仍然會獲取方法默認值的問題；

# v1.0.16 - 2019-09-20

## 新增

- [#565](https://github.com/hyperf/hyperf/pull/565) 增加對 Redis 客户端的 `options` 配置參數支持；
- [#580](https://github.com/hyperf/hyperf/pull/580) 增加協程併發控制特性，通過 `Hyperf\Utils\Coroutine\Concurrent` 可以實現一個代碼塊內限制同時最多運行的協程數量；

## 變更

- [#583](https://github.com/hyperf/hyperf/pull/583) 當 `BaseClient::start` 失敗時會拋出 `Hyperf\GrpcClient\Exception\GrpcClientException` 異常；
- [#585](https://github.com/hyperf/hyperf/pull/585) 當投遞到 TaskWorker 執行的 Task 失敗時，會回傳異常到 Worker 進程中；

## 修復

- [#564](https://github.com/hyperf/hyperf/pull/564) 修復某些情況下 `Coroutine\Http2\Client->send` 返回值不正確的問題；
- [#567](https://github.com/hyperf/hyperf/pull/567) 修復當 JSON RPC 消費者配置 name 不是接口時，無法生成代理類的問題；
- [#571](https://github.com/hyperf/hyperf/pull/571) 修復 ExceptionHandler 的 `stopPropagation` 的協程變量污染的問題；
- [#579](https://github.com/hyperf/hyperf/pull/579) 動態初始化 `snowflake`  的 MetaData，主要修復當在命令模式下使用 Snowflake 時，比如 `di:init-proxy` 命令，會連接到 Redis 服務器至超時；

# v1.0.15 - 2019-09-11

## 修復

- [#534](https://github.com/hyperf/hyperf/pull/534) 修復 Guzzle HTTP 客户端的 `CoroutineHanlder` 沒有處理狀態碼為 `-3` 的情況；
- [#541](https://github.com/hyperf/hyperf/pull/541) 修復 gRPC 客户端的 `$client` 參數設置錯誤的問題；
- [#542](https://github.com/hyperf/hyperf/pull/542) 修復 `Hyperf\Grpc\Parser::parseResponse` 無法支持 gRPC 標準狀態碼的問題；
- [#551](https://github.com/hyperf/hyperf/pull/551) 修復當服務端關閉了 gRPC 連接時，gRPC 客户端會殘留一個死循環的協程；
- [#558](https://github.com/hyperf/hyperf/pull/558) 修復 `UDP Server` 無法正確配置啟動的問題；

## 優化

- [#549](https://github.com/hyperf/hyperf/pull/549) 優化了 `Hyperf\Amqp\Connection\SwooleIO` 的 `read` 和 `write` 方法，減少不必要的重試；
- [#559](https://github.com/hyperf/hyperf/pull/559) 優化 `Hyperf\HttpServer\Response::redirect()` 方法，自動識別鏈接首位是否為斜槓併合理修正參數；
- [#560](https://github.com/hyperf/hyperf/pull/560) 優化 `Hyperf\WebSocketServer\CoreMiddleware`，移除了不必要的代碼；

## 移除

- [#545](https://github.com/hyperf/hyperf/pull/545) 移除了 `Hyperf\Database\Model\SoftDeletes` 內無用的 `restoring` 和 `restored` 靜態方法； 

## 即將移除

- [#558](https://github.com/hyperf/hyperf/pull/558) 標記了 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量為 `棄用` 狀態，該常量將於 `v1.1` 移除，由更合理的 `Hyperf\Server\ServerInterface::SERVER_BASE` 常量替代；

# v1.0.14 - 2019-09-05

## 新增

- [#389](https://github.com/hyperf/hyperf/pull/389) [#419](https://github.com/hyperf/hyperf/pull/419) [#432](https://github.com/hyperf/hyperf/pull/432) [#524](https://github.com/hyperf/hyperf/pull/524) 新增 Snowflake 官方組件, Snowflake 是一個由 Twitter 提出的分佈式全局唯一 ID 生成算法，[hyperf/snowflake](https://github.com/hyperf/snowflake) 組件實現了該算法並設計得易於使用，同時在設計上提供了很好的可擴展性，可以很輕易的將該組件轉換成其它基於 Snowflake 算法的變體算法；
- [#525](https://github.com/hyperf/hyperf/pull/525) 為 `Hyperf\HttpServer\Contract\ResponseInterface` 增加一個 `download()` 方法，提供便捷的下載響應返回；

## 變更

- [#482](https://github.com/hyperf/hyperf/pull/482) 生成模型文件時，當設置了 `refresh-fillable` 選項時重新生成模型的 `fillable` 屬性，同時該命令的默認情況下將不會再覆蓋生成 `fillable` 屬性；
- [#501](https://github.com/hyperf/hyperf/pull/501) 當 `Mapping` 註解的 `path` 屬性為一個空字符串時，那麼該路由則為 `/prefix`；
- [#513](https://github.com/hyperf/hyperf/pull/513) 如果項目設置了 `app_name` 屬性，則進程名稱會自動帶上該名稱；
- [#508](https://github.com/hyperf/hyperf/pull/508) [#526](https://github.com/hyperf/hyperf/pull/526) 當在非協程環境下執行 `Hyperf\Utils\Coroutine::parentId()` 方法時會返回一個 `null` 值；

## 修復

- [#479](https://github.com/hyperf/hyperf/pull/479) 修復了當 Elasticsearch client 的 `host` 屬性設置有誤時，返回類型錯誤的問題；
- [#514](https://github.com/hyperf/hyperf/pull/514) 修復當 Redis 密碼配置為空字符串時鑑權失敗的問題；
- [#527](https://github.com/hyperf/hyperf/pull/527) 修復 Translator 無法重複翻譯的問題；

# v1.0.13 - 2019-08-28

## 新增

- [#449](https://github.com/hyperf/hyperf/pull/428) 新增一個獨立組件 [hyperf/translation](https://github.com/hyperf/translation)， 衍生於 [illuminate/translation](https://github.com/illuminate/translation)；
- [#449](https://github.com/hyperf/hyperf/pull/449) 為 GRPC-Server 增加標準錯誤碼；
- [#450](https://github.com/hyperf/hyperf/pull/450) 為 `Hyperf\Database\Schema\Schema` 類的魔術方法增加對應的靜態方法註釋，為 IDE 提供代碼提醒的支持；

## 變更

- [#451](https://github.com/hyperf/hyperf/pull/451) 在使用 `@AutoController` 註解時不再會自動為魔術方法生成對應的路由；
- [#468](https://github.com/hyperf/hyperf/pull/468) 讓 GRPC-Server 和 HTTP-Server 提供的異常處理器處理所有的異常，而不只是 `ServerException`；

## 修復 

- [#466](https://github.com/hyperf/hyperf/pull/466) 修復分頁時數據不足時返回類型錯誤的問題；
- [#466](https://github.com/hyperf/hyperf/pull/470) 優化了 `vendor:publish` 命令，當要生成的目標文件夾存在時，不再重複生成；

# v1.0.12 - 2019-08-21

## 新增

- [#405](https://github.com/hyperf/hyperf/pull/405) 增加 `Hyperf\Utils\Context::override()` 方法，現在你可以通過 `override` 方法獲取某些協程上下文的值並修改覆蓋它；
- [#415](https://github.com/hyperf/hyperf/pull/415) 對 Logger 的配置文件增加多個 Handler 的配置支持；

## 變更

- [#431](https://github.com/hyperf/hyperf/pull/431) 移除了 `Hyperf\GrpcClient\GrpcClient::openStream()` 的第 3 個參數，這個參數不會影響實際使用；

## 修復

- [#414](https://github.com/hyperf/hyperf/pull/414) 修復 `Hyperf\WebSockerServer\Exception\Handler\WebSocketExceptionHandler` 內的變量名稱錯誤的問題；
- [#424](https://github.com/hyperf/hyperf/pull/424) 修復 Guzzle 在使用 `Hyperf\Guzzle\CoroutineHandler` 時配置 `proxy` 參數時不支持數組傳值的問題；
- [#430](https://github.com/hyperf/hyperf/pull/430) 修復 `Hyperf\HttpServer\Request::file()` 當以一個 Name 上傳多個文件時，返回格式不正確的問題；
- [#431](https://github.com/hyperf/hyperf/pull/431) 修復 GRPC Client 的 Request 對象在發送 Force-Close 請求時缺少參數的問題；

# v1.0.11 - 2019-08-15

## 新增

- [#366](https://github.com/hyperf/hyperf/pull/366) 增加 `Hyperf\Server\Listener\InitProcessTitleListener` 監聽者來設置進程名稱， 同時增加了 `Hyperf\Framework\Event\OnStart` 和 `Hyperf\Framework\Event\OnManagerStart` 事件；

## 修復

- [#361](https://github.com/hyperf/hyperf/pull/361) 修復 `db:model`命令在 MySQL 8 下不能正常運行；
- [#369](https://github.com/hyperf/hyperf/pull/369) 修復實現 `\Serializable` 接口的自定義異常類不能正確的序列化和反序列化問題；
- [#384](https://github.com/hyperf/hyperf/pull/384) 修復用户自定義的 `ExceptionHandler` 在 JSON RPC Server 下無法正常工作的問題，因為框架默認自動處理了對應的異常；
- [#370](https://github.com/hyperf/hyperf/pull/370) 修復了 `Hyperf\GrpcClient\BaseClient` 的 `$client` 屬性在流式傳輸的時候設置了錯誤的類型的值的問題, 同時增加了默認的 `content-type`  為 `application/grpc+proto`，以及允許用户通過自定義 `Request` 對象來重寫 `buildRequest()` 方法；

## 變更

- [#356](https://github.com/hyperf/hyperf/pull/356) [#390](https://github.com/hyperf/hyperf/pull/390) 優化 aysnc-queue 組件當生成 Job 時，如果 Job 實現了 `Hyperf\Contract\CompressInterface`，那麼 Job 對象會被壓縮為一個更小的對象；
- [#358](https://github.com/hyperf/hyperf/pull/358) 只有當 `$enableCache` 為 `true` 時才生成註解緩存文件；
- [#359](https://github.com/hyperf/hyperf/pull/359) [#390](https://github.com/hyperf/hyperf/pull/390) 為 `Collection` 和 `Model` 增加壓縮能力，當類實現 `Hyperf\Contract\CompressInterface` 可通過 `compress` 方法生成一個更小的對象；

# v1.0.10 - 2019-08-09

## 新增

- [#321](https://github.com/hyperf/hyperf/pull/321) 為 HTTP Server 的 Controller/RequestHandler 參數增加自定義對象類型的數組支持，特別適用於 JSON RPC 下，現在你可以通過在方法上定義 `@var Object[]` 來獲得框架自動反序列化對應對象的支持；
- [#324](https://github.com/hyperf/hyperf/pull/324) 增加一個實現於 `Hyperf\Contract\IdGeneratorInterface` 的 ID 生成器 `NodeRequestIdGenerator`；
- [#336](https://github.com/hyperf/hyperf/pull/336) 增加動態代理的 RPC 客户端功能；
- [#346](https://github.com/hyperf/hyperf/pull/346) [#348](https://github.com/hyperf/hyperf/pull/348) 為 `hyperf/cache` 緩存組件增加文件驅動；

## 變更

- [#330](https://github.com/hyperf/hyperf/pull/330) 當掃描的 $paths 為空時，不輸出掃描信息；
- [#328](https://github.com/hyperf/hyperf/pull/328) 根據 Composer 的 PSR-4 定義的規則加載業務項目；
- [#329](https://github.com/hyperf/hyperf/pull/329) 優化 JSON RPC 服務端和客户端的異常消息處理；
- [#340](https://github.com/hyperf/hyperf/pull/340) 為 `make` 函數增加索引數組的傳參方式；
- [#349](https://github.com/hyperf/hyperf/pull/349) 重命名下列類，修正由於拼寫錯誤導致的命名錯誤；

|                     原類名                      |                  修改後的類名                     |
|:----------------------------------------------|:-----------------------------------------------|
| Hyperf\\Database\\Commands\\Ast\\ModelUpdateVistor | Hyperf\\Database\\Commands\\Ast\\ModelUpdateVisitor |
|       Hyperf\\Di\\Aop\\ProxyClassNameVistor       |       Hyperf\\Di\\Aop\\ProxyClassNameVisitor       |
|         Hyperf\\Di\\Aop\\ProxyCallVistor          |         Hyperf\\Di\\Aop\\ProxyCallVisitor          |

## 修復

- [#325](https://github.com/hyperf/hyperf/pull/325) 優化 RPC 服務註冊時會多次調用 Consul Services 的問題；
- [#332](https://github.com/hyperf/hyperf/pull/332) 修復 `Hyperf\Tracer\Middleware\TraceMiddeware` 在新版的 openzipkin/zipkin 下的類型約束錯誤；
- [#333](https://github.com/hyperf/hyperf/pull/333) 修復 `Redis::delete()` 方法在 5.0 版不存在的問題；
- [#334](https://github.com/hyperf/hyperf/pull/334) 修復向阿里雲 ACM 配置中心拉取配置時，部分情況下部分配置無法更新的問題；
- [#337](https://github.com/hyperf/hyperf/pull/337) 修復當 Header 的 key 為非字符串類型時，會返回 500 響應的問題；
- [#338](https://github.com/hyperf/hyperf/pull/338) 修復 `ProviderConfig::load` 在遇到重複 key 時會導致在深度合併時將字符串轉換成數組的問題；

# v1.0.9 - 2019-08-03

## 新增

- [#317](https://github.com/hyperf/hyperf/pull/317) 增加 `composer-json-fixer` 來優化 composer.json 文件的內容；
- [#320](https://github.com/hyperf/hyperf/pull/320) DI 定義 Definition 時，允許 value 為一個匿名函數；

## 修復

- [#300](https://github.com/hyperf/hyperf/pull/300) 讓 AsyncQueue 的消息於子協程內來進行處理，修復 `attempts` 參數與實際重試次數不一致的問題；
- [#305](https://github.com/hyperf/hyperf/pull/305) 修復 `Hyperf\Utils\Arr::set` 方法的 `$key` 參數不支持 `int` 個 `null` 的問題；
- [#312](https://github.com/hyperf/hyperf/pull/312) 修復 `Hyperf\Amqp\BeforeMainServerStartListener` 監聽器的優先級錯誤的問題；
- [#315](https://github.com/hyperf/hyperf/pull/315) 修復 ETCD 配置中心在 Worker 進程重啟後或在自定義進程內無法使用問題；
- [#318](https://github.com/hyperf/hyperf/pull/318) 修復服務會持續註冊到服務中心的問題；

## 變更

- [#323](https://github.com/hyperf/hyperf/pull/323) 強制轉換 `Cacheable` 和 `CachePut` 註解的 `$ttl` 屬性為 `int` 類型；

# v1.0.8 - 2019-07-31

## 新增

- [#276](https://github.com/hyperf/hyperf/pull/276) AMQP 消費者支持配置及綁定多個 `routing_key`；
- [#277](https://github.com/hyperf/hyperf/pull/277) 增加 ETCD 客户端組件及 ETCD 配置中心組件；

## 變更

- [#297](https://github.com/hyperf/hyperf/pull/297) 如果服務註冊失敗，會於 10 秒後重試註冊，且屏蔽了連接不上服務中心(Consul)而拋出的異常；
- [#298](https://github.com/hyperf/hyperf/pull/298) [#301](https://github.com/hyperf/hyperf/pull/301) 適配 `openzipkin/zipkin` v1.3.3+ 版本；

## 修復

- [#271](https://github.com/hyperf/hyperf/pull/271) 修復了 AOP 在 `classes` 只會策略下配置同一個類的多個方法只會實現第一個方法的代理方法的問題；
- [#285](https://github.com/hyperf/hyperf/pull/285) 修復了 AOP 在匿名類下生成節點存在丟失的問題；
- [#286](https://github.com/hyperf/hyperf/pull/286) 自動 `rollback` 沒有 `commit` 或 `rollback` 的 MySQL 連接；
- [#292](https://github.com/hyperf/hyperf/pull/292) 修復了 `Request::header` 方法的 `$default` 參數無效的問題；
- [#293](https://github.com/hyperf/hyperf/pull/293) 修復了 `Arr::get` 方法的 `$key` 參數不支持 `int` and `null` 傳值的問題；

# v1.0.7 - 2019-07-26

## 修復

- [#266](https://github.com/hyperf/hyperf/pull/266) 修復投遞 AMQP 消息時的超時邏輯；
- [#273](https://github.com/hyperf/hyperf/pull/273) 修復當有一個服務註冊到服務中心的時候所有服務會被移除的問題；
- [#274](https://github.com/hyperf/hyperf/pull/274) 修復視圖響應的 Content-Type ；

# v1.0.6 - 2019-07-24

## 新增

- [#203](https://github.com/hyperf/hyperf/pull/203) [#236](https://github.com/hyperf/hyperf/pull/236) [#247](https://github.com/hyperf/hyperf/pull/247) [#252](https://github.com/hyperf/hyperf/pull/252) 增加視圖組件，支持 Blade 引擎和 Smarty 引擎； 
- [#203](https://github.com/hyperf/hyperf/pull/203) 增加 Task 組件，適配 Swoole Task 機制；
- [#245](https://github.com/hyperf/hyperf/pull/245) 增加 TaskWorkerStrategy 和 WorkerStrategy 兩種定時任務調度策略.
- [#251](https://github.com/hyperf/hyperf/pull/251) 增加用協程上下文作為儲存的緩存驅動；
- [#254](https://github.com/hyperf/hyperf/pull/254) 增加 `RequestMapping::$methods` 對數組傳值的支持, 現在可以通過 `@RequestMapping(methods={"GET"})` 和 `@RequestMapping(methods={RequestMapping::GET})` 兩種新的方式定義方法；
- [#255](https://github.com/hyperf/hyperf/pull/255) 控制器返回 `Hyperf\Utils\Contracts\Arrayable` 會自動轉換為 Response 對象, 同時對返回字符串的響應對象增加  `text/plain` Content-Type;
- [#256](https://github.com/hyperf/hyperf/pull/256) 如果 `Hyperf\Contract\IdGeneratorInterface` 存在容器綁定關係, 那麼 `json-rpc` 客户端會根據該類自動生成一個請求 ID 並儲存在 Request attribute 裏，同時完善了 `JSON RPC` 在 TCP 協議下的服務註冊及健康檢查；

## 變更

- [#247](https://github.com/hyperf/hyperf/pull/247) 使用 `WorkerStrategy` 作為默認的計劃任務調度策略；
- [#256](https://github.com/hyperf/hyperf/pull/256) 優化 `JSON RPC` 的錯誤處理，現在當方法不存在時也會返回一個標準的 `JSON RPC` 錯誤對象；

## 修復

- [#235](https://github.com/hyperf/hyperf/pull/235) 為 `grpc-server` 增加了默認的錯誤處理器，防止錯誤拋出.
- [#240](https://github.com/hyperf/hyperf/pull/240) 優化了 OnPipeMessage 事件的觸發，修復會被多個監聽器獲取錯誤數據的問題；
- [#257](https://github.com/hyperf/hyperf/pull/257) 修復了在某些環境下無法獲得內網 IP 的問題；

# v1.0.5 - 2019-07-17

## 新增

- [#185](https://github.com/hyperf/hyperf/pull/185) `響應(Response)` 增加 `xml` 格式支持；
- [#202](https://github.com/hyperf/hyperf/pull/202) 在協程內拋出未捕獲的異常時，默認輸出異常的 trace 信息；
- [#138](https://github.com/hyperf/hyperf/pull/138) [#197](https://github.com/hyperf/hyperf/pull/197) 增加秒級定時任務組件；

# 變更

- [#195](https://github.com/hyperf/hyperf/pull/195) 變更 `retry()` 函數的 `$times` 參數的行為意義, 表示重試的次數而不是執行的次數；
- [#198](https://github.com/hyperf/hyperf/pull/198) 優化 `Hyperf\Di\Container` 的 `has()` 方法, 當傳遞一個不可實例化的示例（如接口）至 `$container->has($interface)` 方法時，會返回 `false`；
- [#199](https://github.com/hyperf/hyperf/pull/199) 當生產 AMQP 消息失敗時，會自動重試一次；
- [#200](https://github.com/hyperf/hyperf/pull/200) 通過 Git 打包項目的部署包時，不再包含 `tests` 文件夾；

## 修復

- [#176](https://github.com/hyperf/hyperf/pull/176) 修復 `LengthAwarePaginator::nextPageUrl()` 方法返回值的類型約束；
- [#188](https://github.com/hyperf/hyperf/pull/188) 修復 Guzzle Client 的代理設置不生效的問題；
- [#211](https://github.com/hyperf/hyperf/pull/211) 修復 RPC Client 存在多個時會被最後一個覆蓋的問題；
- [#212](https://github.com/hyperf/hyperf/pull/212) 修復 Guzzle Client 的 `ssl_key` 和 `cert` 配置項不能正常工作的問題；

# v1.0.4 - 2019-07-08

## 新增

- [#140](https://github.com/hyperf/hyperf/pull/140) 支持 Swoole v4.4.0.
- [#152](https://github.com/hyperf/hyperf/pull/152) 數據庫連接在低使用率時連接池會自動釋放連接
- [#163](https://github.com/hyperf/hyperf/pull/163) constants 組件的`AbstractConstants::__callStatic` 支持自定義參數

## 變更

- [#124](https://github.com/hyperf/hyperf/pull/124) `DriverInterface::push` 增加 `$delay` 參數用於設置延遲時間, 同時 `DriverInterface::delay` 將標記為棄用的，將於 1.1 版本移除 
- [#125](https://github.com/hyperf/hyperf/pull/125) 更改 `config()` 函數的 `$default` 參數的默認值為 `null`.

## 修復

- [#110](https://github.com/hyperf/hyperf/pull/110) [#111](https://github.com/hyperf/hyperf/pull/111) 修復 `Redis::select` 無法正常切換數據庫的問題
- [#131](https://github.com/hyperf/hyperf/pull/131) 修復 `middlewares` 配置在 `Router::addGroup` 下無法正常設置的問題
- [#132](https://github.com/hyperf/hyperf/pull/132) 修復 `request->hasFile` 判斷條件錯誤的問題
- [#135](https://github.com/hyperf/hyperf/pull/135) 修復 `response->redirect` 在調整外鏈時無法正確生成鏈接的問題
- [#139](https://github.com/hyperf/hyperf/pull/139) 修復 ConsulAgent 的 URI 無法自定義設置的問題
- [#148](https://github.com/hyperf/hyperf/pull/148) 修復當 `migrates` 文件夾不存在時無法生成遷移模板的問題
- [#169](https://github.com/hyperf/hyperf/pull/169) 修復處理請求時沒法正確處理數組類型的參數
- [#170](https://github.com/hyperf/hyperf/pull/170) 修復當路由不存在時 WebSocket Server 無法正確捕獲異常的問題

## 移除

- [#131](https://github.com/hyperf/hyperf/pull/131) 移除 `Router` `options` 裏的 `server` 參數

# v1.0.3 - 2019-07-02

## 新增

- [#48](https://github.com/hyperf/hyperf/pull/48) 增加 WebSocket 協程客户端及服務端
- [#51](https://github.com/hyperf/hyperf/pull/51) 增加了 `enableCache` 參數去控制 `DefinitionSource` 是否啟用註解掃描緩存 
- [#61](https://github.com/hyperf/hyperf/pull/61) 通過 `db:model` 命令創建模型時增加屬性類型
- [#65](https://github.com/hyperf/hyperf/pull/65) 模型緩存增加 JSON 格式支持

## 變更

- [#46](https://github.com/hyperf/hyperf/pull/46) 移除了 `hyperf/di`, `hyperf/command` and `hyperf/dispatcher` 組件對 `hyperf/framework` 組件的依賴

## 修復

- [#45](https://github.com/hyperf/hyperf/pull/55) 修復當引用了 `hyperf/websocket-server` 組件時有可能會導致 HTTP Server 啟動失敗的問題
- [#55](https://github.com/hyperf/hyperf/pull/55) 修復方法級別的 `@Middleware` 註解可能會被覆蓋的問題
- [#73](https://github.com/hyperf/hyperf/pull/73) 修復 `db:model` 命令對短屬性處理不正確的問題
- [#88](https://github.com/hyperf/hyperf/pull/88) 修復當控制器存在多層文件夾時生成的路由可能不正確的問題
- [#101](https://github.com/hyperf/hyperf/pull/101) 修復常量不存在 `@Message` 註解時會報錯的問題

# v1.0.2 - 2019-06-25

## 新增

- 接入 Travis CI，目前 Hyperf 共存在 426 個單測，1124 個斷言； [#25](https://github.com/hyperf/hyperf/pull/25)
- 完善了對 `Redis::connect` 方法的參數支持； [#29](https://github.com/hyperf/hyperf/pull/29)

## 修復

- 修復了 HTTP Server 會被 WebSocket Server 影響的問題（WebSocket Server 尚未發佈）；
- 修復了代理類部分註解沒有生成的問題；
- 修復了數據庫連接池在單測環境下會無法獲取連接的問題；
- 修復了 co-phpunit 在某些情況下不能按預期運行的問題；
- 修復了模型事件 `creating`, `updating` ... 運行與預期不一致的問題；
- 修復了 `flushContext` 方法在單測環境下不能按預期運行的問題；
