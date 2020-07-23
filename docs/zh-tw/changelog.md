# 版本更新記錄

# v2.0 - 2020-06-22

## 主要功能

1. 重構 [hyperf/di](https://github.com/hyperf/di) 元件，特別是對 AOP 和註解的優化，在 2.0 版本，該元件使用了一個全新的載入機制來提供 AOP 功能的支援。
    1. 對比 1.x 版本來說最顯著的一個一個功能就是現在你可以通過 AOP 功能切入任何方式例項化的一個類了，比如說，在 1.x 版本，你只能切入由 DI 容器建立的類，你無法切入一個由 `new` 關鍵詞例項化的類，但在 2.0 版本都可以生效了。不過仍有一些例外的情況，您仍無法切入那些在啟動階段用來提供 AOP 功能的類；
    2. 在 1.x 版本，AOP 只能作用於普通的類，無法支援 `Final` 類，但在 2.0 版本您可以這麼做了；
    3. 在 1.x 版本，您無法在當前類的建構函式中使用 `@Inject` 或 `@Value` 註解標記的類成員屬性的值，但在 2.0 版本里，您可以這麼做了；
    4. 在 1.x 版本，只有通過 DI 容器建立的物件才能使 `@Inject` 和 `@Value` 註解的功能生效，通過 `new` 關鍵詞建立的物件無法生效，但在 2.0 版本，都可以生效了；
    5. 在 1.x 版本，在使用註解時，您必須定義註解的名稱空間來指定使用的註解類，但在 2.0 版本下，您可以為任一註解提供一個別名，這樣在使用這個註解時可以直接使用別名而無需引入註解類的名稱空間。比如您可以直接在任意類屬性上標記 `@Inject` 註解而無需編寫 `use Hyperf\Di\Annotation\Inject;`；
    6. 在 1.x 版本，建立的代理類是一個目標類的子類，這樣的實現機制會導致一些魔術常量獲得的值返回的是代理類子類的資訊，而不是目標類的資訊，但在 2.0 版本，代理類會與目標類保持一樣的類名和程式碼結構；
    7. 在 1.x 版本，當代理類快取存在時則不會重新生成快取，就算原始碼發生了變化，這樣的機制有助於掃描耗時的提升，但與此同時，這也會導致開發階段的一些不便利，但在 2.0 版本，代理類快取會根據原始碼的變化而自動變化，這一改變會減少很多在開發階段的心智負擔；
    8. 為 Aspect 類增加了 `priority` 優先順序屬性，現在您可以組織多個 Aspect 之間的順序了；
    9. 在 1.x 版本，您只能通過 `@Aspect` 註解類定義一個 Aspect 類，但在 2.0 版本，您還可以通過配置檔案、ConfigProvider 來定義 Aspect 類；
    10. 在 1.x 版本，您在使用到依賴懶載入功能時，必須註冊一個 `Hyperf\Di\Listener\LazyLoaderBootApplicationListener` 監聽器，但在 2.0 版本，您可以直接使用該功能而無需做任何的註冊動作；
    11. 增加了 `annotations.scan.class_map` 配置項，通過該配置您可以將任意類替換成您自己的類，而使用時無需做任何的改變；
    
## 依賴庫更新

- 將 `ext-swoole` 升級到了 `>=4.5`;
- 將 `psr/event-dispatcher` 升級到了 `^1.0`;
- 將 `monolog/monolog` 升級到了 `^2.0`;
- 將 `phpstan/phpstan` 升級到了 `^0.12.18`;
- 將 `vlucas/phpdotenv` 升級到了 `^4.0`;
- 將 `symfony/finder` 升級到了 `^5.0`;
- 將 `symfony/event-dispatcher` 升級到了 `^5.0`;
- 將 `symfony/console` 升級到了 `^5.0`;
- 將 `symfony/property-access` 升級到了 `^5.0`;
- 將 `symfony/serializer` 升級到了 `^5.0`;
- 將 `elasticsearch/elasticsearch` 升級到了 `^7.0`;

## 類和方法的變更

- 移除了 `Hyperf\Di\Aop\AstCollector`；
- 移除了 `Hyperf\Di\Aop\ProxyClassNameVisitor`；
- 移除了 `Hyperf\Di\Listener\LazyLoaderBootApplicationListener`；
- 移除了 `Hyperf\Dispatcher\AbstractDispatcher` 類的 `dispatch(...$params)` 方法；
- 移除了 hyperf/utils 元件中 ConfigProvider 中的 `Hyperf\Contract\NormalizerInterface => Hyperf\Utils\Serializer\SymfonyNormalizer` 關係；
- 移除了 `Hyperf\Contract\OnOpenInterface`、`Hyperf\Contract\OnCloseInterface`、`Hyperf\Contract\OnMessageInterface`、`Hyperf\Contract\OnReceiveInterface` 介面中的 `$server` 引數的強型別宣告；

## 新增

- [#992](https://github.com/hyperf/hyperf/pull/992) 新增 [hyperf/reactive-x](https://github.com/hyperf/reactive-x) 元件；
- [#1245](https://github.com/hyperf/hyperf/pull/1245) 為 `ExceptionHandler` 新增了註解的定義方式；
- [#1245](https://github.com/hyperf/hyperf/pull/1245) `ExceptionHandler` 新增了 `priority` 優先順序屬性，通過配置檔案或註解方式均可定義優先順序；
- [#1819](https://github.com/hyperf/hyperf/pull/1819) 新增 [hyperf/signal](https://github.com/hyperf/signal) 元件；
- [#1844](https://github.com/hyperf/hyperf/pull/1844) 為 [hyperf/model-cache](https://github.com/hyperf/model-cache) 元件中的 `ttl` 屬性增加了 `\DateInterval` 型別的支援；
- [#1855](https://github.com/hyperf/hyperf/pull/1855) 連線池新增了 `ConstantFrequency` 恆定頻率策略來釋放限制的連線；
- [#1871](https://github.com/hyperf/hyperf/pull/1871) 為 Guzzle 增加 `sink` 選項支援；
- [#1805](https://github.com/hyperf/hyperf/pull/1805) 新增 Coroutine Server 協程服務支援；
  - 變更了 `Hyperf\Contract\ProcessInterface` 中的 `bind(Server $server)` 方法宣告為 `bind($server)`；
  - 變更了 `Hyperf\Contract\ProcessInterface` 中的 `isEnable()` 方法宣告為 `isEnable($server)`；
  - 配置中心、Crontab、服務監控、訊息佇列消費者現在可以通過協程模式來執行，且在使用協程服務模式時，也必須以協程模式來執行；
  - `Hyperf\AsyncQueue\Environment` 的作用域改為當前協程內，而不是整個程序；
  - 協程模式下不再支援 Task 機制；
- [#1877](https://github.com/hyperf/hyperf/pull/1877) 在 PHP 8 下使用 `@Inject` 註解時支援通過成員屬性強型別宣告來替代 `@var` 宣告，如下所示：

```
class Example {
    /**
    * @Inject
    */
    private ExampleService $exampleService;
}
```

- [#1890](https://github.com/hyperf/hyperf/pull/1890) 新增 `Hyperf\HttpServer\ResponseEmitter` 類來響應任意符合 PSR-7 標準的 Response 物件，同時抽象了 `Hyperf\Contract\ResponseEmitterInterface` 契約；
- [#1890](https://github.com/hyperf/hyperf/pull/1890) 為 `Hyperf\HttpMessage\Server\Response` 類新增了 `getTrailers()` 和 `getTrailer(string $key)` 和 `withTrailer(string $key, $value)` 方法；
- [#1920](https://github.com/hyperf/hyperf/pull/1920) 新增方法 `Hyperf\WebSocketServer\Sender::close(int $fd, bool $reset = null)`.

## 修復

- [#1825](https://github.com/hyperf/hyperf/pull/1825) 修復了 `StartServer::execute` 的 `TypeError`；
- [#1854](https://github.com/hyperf/hyperf/pull/1854) 修復了在 filesystem 中使用 `Runtime::enableCoroutine()` 時，`is_resource` 不能工作的問題；
- [#1900](https://github.com/hyperf/hyperf/pull/1900) 修復了 `Model` 中的 `asDecimal` 方法型別有可能錯誤的問題；
- [#1917](https://github.com/hyperf/hyperf/pull/1917) 修復了 `Request::isXmlHttpRequest` 方法無法正常工作的問題；

## 變更

- [#705](https://github.com/hyperf/hyperf/pull/705) 統一了 HTTP 異常的處理方式，現在統一丟擲一個 `Hyperf\HttpMessage\Exception\HttpException` 依賴類來替代在 `Dispatcher` 中直接響應的方式，同時提供了 `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` 異常處理器來處理該類異常；
- [#1846](https://github.com/hyperf/hyperf/pull/1846) 當您 require 了 `symfony/serializer` 庫，不再自動對映 `Hyperf\Contract\NormalizerInterface` 的實現類，您需要手動新增該對映關係，如下：

```php
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

- [#1924](https://github.com/hyperf/hyperf/pull/1924) 重新命名 `Hyperf\GrpcClient\BaseClient` 內 `simpleRequest, getGrpcClient, clientStreamRequest` 方法名為 `_simpleRequest, _getGrpcClient, _clientStreamRequest`；

## 移除

- [#1890](https://github.com/hyperf/hyperf/pull/1890) Removed `Hyperf\Contract\Sendable` interface and all implementations of it.
- [#1905](https://github.com/hyperf/hyperf/pull/1905) Removed config `config/server.php`, you can merge it into `config/config.php`.

## 優化

- [#1793](https://github.com/hyperf/hyperf/pull/1793) Socket.io 服務現在只在 onOpen and onClose 中觸發 connect/disconnect 事件，同時將一些類方法從 private 級別調整到了 protected 級別，以便使用者可以方便的重寫這些方法；
- [#1848](https://github.com/hyperf/hyperf/pull/1848) 當 RPC 客戶端對應的 Contract 發生變更時，自動重寫生成對應的動態代理客戶端類；
- [#1863](https://github.com/hyperf/hyperf/pull/1863) 為 async-queue 元件提供更加安全的停止機制；
- [#1896](https://github.com/hyperf/hyperf/pull/1896) 當在 constants 元件中使用了同樣的 code 時，keys 會被合併起來；

# v1.1.32 - 2020-05-21

## 修復

- [#1734](https://github.com/hyperf/hyperf/pull/1734) 修復模型多型查詢，關聯關係為空時，也會查詢 SQL 的問題；
- [#1739](https://github.com/hyperf/hyperf/pull/1739) 修復 `hyperf/filesystem` 元件 OSS HOOK 位運算錯誤，導致 resource 判斷不準確的問題；
- [#1743](https://github.com/hyperf/hyperf/pull/1743) 修復 `grafana.json` 中錯誤的`refId` 欄位值；
- [#1748](https://github.com/hyperf/hyperf/pull/1748) 修復 `hyperf/amqp` 元件在使用其他連線池時，對應的 `concurrent.limit` 配置不生效的問題；
- [#1750](https://github.com/hyperf/hyperf/pull/1750) 修復連線池元件，在連線關閉失敗時會導致計數有誤的問題；
- [#1754](https://github.com/hyperf/hyperf/pull/1754) 修復 BASE Server 服務，啟動提示沒有考慮 UDP 服務的情況；
- [#1764](https://github.com/hyperf/hyperf/pull/1764) 修復當時間值為 null 時，datatime 驗證器執行失敗的 BUG；
- [#1769](https://github.com/hyperf/hyperf/pull/1769) 修復 `hyperf/socketio-server` 元件中，客戶端初始化斷開連線操作時會報 Notice 的錯誤的問題；

## 新增

- [#1724](https://github.com/hyperf/hyperf/pull/1724) 新增模型方法 `Model::orWhereHasMorph` ,`Model::whereDoesntHaveMorph` and `Model::orWhereDoesntHaveMorph`；
- [#1741](https://github.com/hyperf/hyperf/pull/1741) 新增 `Hyperf\Command\Command::choiceMultiple(): array` 方法，因為 `choice` 方法的返回型別為 `string，所以就算設定了 `$multiple` 引數也無法處理多個選擇的情況；
- [#1742](https://github.com/hyperf/hyperf/pull/1742) 新增模型 自定義型別轉換器 功能；
  - 新增 interface `Castable`, `CastsAttributes` 和 `CastsInboundAttributes`；
  - 新增方法 `Model\Builder::withCasts`；
  - 新增方法 `Model::loadMorph`, `Model::loadMorphCount` 和 `Model::syncAttributes`；
  
# v1.1.31 - 2020-05-14

## 新增

- [#1723](https://github.com/hyperf/hyperf/pull/1723) 異常處理器集成了 filp/whoops 。
- [#1730](https://github.com/hyperf/hyperf/pull/1730) 為命令 `gen:model` 可選項 `--refresh-fillable` 新增簡寫 `-R`。

## 修復

- [#1696](https://github.com/hyperf/hyperf/pull/1696) 修復方法 `Context::copy` 傳入欄位 `keys` 後無法正常使用的BUG。
- [#1708](https://github.com/hyperf/hyperf/pull/1708) [#1718](https://github.com/hyperf/hyperf/pull/1718) 修復 `hyperf/socketio-server` 元件記憶體溢位等BUG。

## 優化

- [#1710](https://github.com/hyperf/hyperf/pull/1710) MAC系統下不再使用 `cli_set_process_title` 方法設定程序名。

# v1.1.30 - 2020-05-07

## 新增

- [#1616](https://github.com/hyperf/hyperf/pull/1616) 新增 ORM 方法 `morphWith` 和 `whereHasMorph`。
- [#1651](https://github.com/hyperf/hyperf/pull/1651) 新增 `socket.io-server` 元件。
- [#1666](https://github.com/hyperf/hyperf/pull/1666) [#1669](https://github.com/hyperf/hyperf/pull/1669) 新增 AMQP RPC 客戶端。

## 修復

- [#1682](https://github.com/hyperf/hyperf/pull/1682) 修復 `RpcPoolTransporter` 的連線池配置不生效的 BUG。
- [#1683](https://github.com/hyperf/hyperf/pull/1683) 修復 `RpcConnection` 連線失敗後，相同協程內無法正常重置連線的 BUG。

## 優化 

- [#1670](https://github.com/hyperf/hyperf/pull/1670) 優化掉 `Cache 元件` 一條無意義的刪除指令。

# v1.1.28 - 2020-04-30

## 新增

- [#1645](https://github.com/hyperf/hyperf/pull/1645) 匿名函式路由支援引數注入。
- [#1647](https://github.com/hyperf/hyperf/pull/1647) 為 `model-cache` 元件新增 `RedisStringHandler`。
- [#1654](https://github.com/hyperf/hyperf/pull/1654) 新增 `RenderException` 統一捕獲 `view` 元件丟擲的異常。

## 修復

- [#1639](https://github.com/hyperf/hyperf/pull/1639) 修復 `rpc-client` 會從 `consul` 中獲取到不健康節點的BUG。
- [#1641](https://github.com/hyperf/hyperf/pull/1641) 修復 `rpc-client` 獲取到的結果為 `null` 時，會丟擲 `RequestException` 的BUG。
- [#1641](https://github.com/hyperf/hyperf/pull/1641) 修復 `rpc-server` 中 `jsonrpc-tcp-length-check` 協議，無法在 `consul` 中新增心跳檢查的BUG。
- [#1650](https://github.com/hyperf/hyperf/pull/1650) 修復指令碼 `describe:routes` 列表展示有誤的BUG。
- [#1655](https://github.com/hyperf/hyperf/pull/1655) 修復 `MysqlProcessor::processColumns` 無法在 `MySQL Server 8.0` 版本中正常工作的BUG。

## 優化 

- [#1636](https://github.com/hyperf/hyperf/pull/1636) 優化 `co-phpunit` 指令碼，當出現 `case` 驗證失敗後，協程也可以正常結束。


# v1.1.27 - 2020-04-23

## 新增

- [#1575](https://github.com/hyperf/hyperf/pull/1575) 為指令碼 `gen:model` 生成的模型，自動新增 `relation` `scope` 和 `attributes` 的變數註釋。
- [#1586](https://github.com/hyperf/hyperf/pull/1586) 新增 `symfony/event-dispatcher` 元件小於 `4.3` 時的 `conflict` 配置。用於解決使用者使用了 `4.3` 以下版本時，導致 `SymfonyDispatcher` 實現衝突的 BUG。
- [#1597](https://github.com/hyperf/hyperf/pull/1597) 為 `AMQP` 消費者，新增最大消費次數 `maxConsumption`。
- [#1603](https://github.com/hyperf/hyperf/pull/1603) 為 `WebSocket` 服務新增基於 `fd` 儲存的 `Context`。

## 修復

- [#1553](https://github.com/hyperf/hyperf/pull/1553) 修復 `jsonrpc` 服務，釋出了相同名字不同協議到 `consul` 後，客戶端無法正常工作的 BUG。
- [#1589](https://github.com/hyperf/hyperf/pull/1589) 修復了檔案鎖在協程下可能會造成死鎖的 BUG。
- [#1607](https://github.com/hyperf/hyperf/pull/1607) 修復了重寫後的 `go` 方法，返回值與 `swoole` 原生方法不符的 BUG。
- [#1624](https://github.com/hyperf/hyperf/pull/1624) 修復當路由 `Handler` 是匿名函式時，指令碼 `describe:routes` 執行失敗的 BUG。

# v1.1.26 - 2020-04-16

## 新增

- [#1578](https://github.com/hyperf/hyperf/pull/1578) `UploadedFile` 支援 `getStream` 方法。

## 修復

- [#1563](https://github.com/hyperf/hyperf/pull/1563) 修復服務關停後，定時器的 `onOneServer` 配置不會被重置。
- [#1565](https://github.com/hyperf/hyperf/pull/1565) 當 `DB` 元件重連 `Mysql` 時，重置事務等級為 0。
- [#1572](https://github.com/hyperf/hyperf/pull/1572) 修復 `Hyperf\GrpcServer\CoreMiddleware` 中，自定義類的父類找不到時報錯的 BUG。
- [#1577](https://github.com/hyperf/hyperf/pull/1577) 修復 `describe:routes` 指令碼 `server` 配置不生效的 BUG。
- [#1579](https://github.com/hyperf/hyperf/pull/1579) 修復 `migrate:refresh` 指令碼 `step` 引數不為 `int` 時會報錯的 BUG。

## 變更

- [#1560](https://github.com/hyperf/hyperf/pull/1560) 修改 `hyperf/cache` 元件檔案快取引擎中 原生的檔案操作為 `Filesystem`。
- [#1568](https://github.com/hyperf/hyperf/pull/1568) 修改 `hyperf/async-queue` 元件 `Redis` 引擎中的 `\Redis` 為 `RedisProxy`。

# v1.1.25 - 2020-04-09

## 修復

- [#1532](https://github.com/hyperf/hyperf/pull/1532) 修復 'Symfony\Component\EventDispatcher\EventDispatcherInterface' 在 --no-dev 條件下安裝會出現找不到介面的問題；


# v1.1.24 - 2020-04-09

## 新增

- [#1501](https://github.com/hyperf/hyperf/pull/1501) 新增 `Symfony` 命令列事件觸發器，使之可以與 `hyperf/event` 元件結合使用；
- [#1502](https://github.com/hyperf/hyperf/pull/1502) 為註解 `Hyperf\AsyncQueue\Annotation\AsyncQueueMessage` 新增 `maxAttempts` 引數，用於控制訊息失敗時重複消費的次數；
- [#1510](https://github.com/hyperf/hyperf/pull/1510) 新增 `Hyperf/Utils/CoordinatorManager`，用於提供更優雅的啟動和停止服務，服務啟動前不響應請求，服務停止前，保證某些迴圈邏輯能夠正常結束；
- [#1517](https://github.com/hyperf/hyperf/pull/1517) 為依賴注入容器的懶載入功能添加了對介面繼承和抽象方法繼承的支援；
- [#1529](https://github.com/hyperf/hyperf/pull/1529) 處理 `response cookies` 中的 `SameSite` 屬性；

## 修復

- [#1494](https://github.com/hyperf/hyperf/pull/1494) 修復單獨使用 `Redis` 元件時，註釋 `@mixin` 會被當成註解的 BUG；
- [#1499](https://github.com/hyperf/hyperf/pull/1499) 修復引入 `hyperf/translation` 元件後，`hyperf/constants` 元件的動態引數不生效的 BUG；
- [#1504](https://github.com/hyperf/hyperf/pull/1504) 修復 `RPC` 代理客戶端無法正常處理返回值為 `nullable` 型別的方法；
- [#1507](https://github.com/hyperf/hyperf/pull/1507) 修復 `hyperf/consul` 元件的 `catalog` 註冊方法呼叫會失敗的 BUG；

# v1.1.23 - 2020-04-02

## 新增

- [#1467](https://github.com/hyperf/hyperf/pull/1467) 為 `filesystem` 元件新增預設配置；
- [#1469](https://github.com/hyperf/hyperf/pull/1469) 為 `Hyperf/Guzzle/HandlerStackFactory` 新增 `getHandler()` 方法，並儘可能的使用 `make()` 建立 `handler`；
- [#1480](https://github.com/hyperf/hyperf/pull/1480) RPC client 現在會自動代理父介面的方法定義；

## 變更

- [#1481](https://github.com/hyperf/hyperf/pull/1481) 非同步佇列建立訊息時，使用 `make` 方法建立；

## 修復

- [#1471](https://github.com/hyperf/hyperf/pull/1471) 修復 `NSQ` 元件，資料量超過 `max-output-buffer-size` 接收資料失敗的 `BUG`；
- [#1472](https://github.com/hyperf/hyperf/pull/1472) 修復 `NSQ` 元件，在消費者中釋出訊息時，會導致消費者無法正常消費的 `BUG`；
- [#1474](https://github.com/hyperf/hyperf/pull/1474) 修復 `NSQ` 元件，`requeue` 訊息時，消費者會意外重啟的 `BUG`；
- [#1477](https://github.com/hyperf/hyperf/pull/1477) 修復使用 `Hyperf\Testing\Client::flushContext` 時，會引發 `Fixed Invalid argument supplied` 異常的 `BUG`；

# v1.1.22 - 2020-03-26

## 新增

- [#1440](https://github.com/hyperf/hyperf/pull/1440) 為 NSQ 的每個連線新增 `enable` 配置項來控制連線下的所有消費者的自啟功能；
- [#1451](https://github.com/hyperf/hyperf/pull/1451) 新增 Filesystem 元件；
- [#1459](https://github.com/hyperf/hyperf/pull/1459) 模型 Collection 新增 macroable 支援；
- [#1463](https://github.com/hyperf/hyperf/pull/1463) 為 Guzzle Handler 增加 `on_stats` 選項的功能支援；

## 變更

- [#1452](https://github.com/hyperf/hyperf/pull/1452) 在注入 Redis 客戶端時，推薦使用 `\Hyperf\Redis\Redis` 來替代 `\Redis`，原因在 [#938](https://github.com/hyperf/hyperf/issues/938)；

## 修復

- [#1445](https://github.com/hyperf/hyperf/pull/1445) 修復命令 `describe:routes` 缺失了帶引數的路由；
- [#1449](https://github.com/hyperf/hyperf/pull/1449) 修復了高基數請求路徑的記憶體溢位的問題；
- [#1454](https://github.com/hyperf/hyperf/pull/1454) 修復 Collection 的 `flatten()` 方法因為 `INF` 引數值為 `float` 型別導致無法使用的問題；
- [#1458](https://github.com/hyperf/hyperf/pull/1458) 修復了 Guzzle 不支援 Elasticsearch 版本大於 7.0 的問題；

# v1.1.21 - 2020-03-19

## 新增

- [#1393](https://github.com/hyperf/hyperf/pull/1393) 為 `Hyperf\HttpMessage\Stream\SwooleStream` 實現更多的方法；
- [#1419](https://github.com/hyperf/hyperf/pull/1419) 允許 ConfigFetcher 通過一個協程啟動而無需額外啟動一個程序；
- [#1424](https://github.com/hyperf/hyperf/pull/1424) 允許使用者通過配置檔案的形式修改 `session_name` 配置；
- [#1435](https://github.com/hyperf/hyperf/pull/1435) 為模型快取增加 `use_default_value` 屬性來自動修正快取資料與資料庫資料之間的差異；
- [#1436](https://github.com/hyperf/hyperf/pull/1436) 為 NSQ 消費者增加 `isEnable()` 方法來控制消費者程序是否啟用自啟功能；

# v1.1.20 - 2020-03-12

## 新增

- [#1402](https://github.com/hyperf/hyperf/pull/1402) 增加 `Hyperf\DbConnection\Annotation\Transactional` 註解來自動開啟一個事務；
- [#1412](https://github.com/hyperf/hyperf/pull/1412) 增加 `Hyperf\View\RenderInterface::getContents()` 方法來直接獲取 View Render 的渲染內容；
- [#1416](https://github.com/hyperf/hyperf/pull/1416) 增加 Swoole 事件常量 `ON_WORKER_ERROR`.

## 修復

- [#1405](https://github.com/hyperf/hyperf/pull/1405) 修復當模型存在 `hidden` 屬性時，模型快取功能快取的欄位資料不正確的問題；
- [#1410](https://github.com/hyperf/hyperf/pull/1410) 修復 Tracer 無法追蹤由 `Hyperf\Redis\RedisFactory` 建立的連線的呼叫鏈；
- [#1415](https://github.com/hyperf/hyperf/pull/1415) 修復阿里 ACM 客戶端在當 `SecurityToken` Header 為空時 sts token 會解密失敗的問題；


# v1.1.19 - 2020-03-05

## 新增

- [#1339](https://github.com/hyperf/hyperf/pull/1339) [#1394](https://github.com/hyperf/hyperf/pull/1394) 新增 `describe:routes` 命令來顯示路由的細節資訊；
- [#1354](https://github.com/hyperf/hyperf/pull/1354) 為  `config-aliyun-acm` 元件新增 ecs ram authorization；
- [#1362](https://github.com/hyperf/hyperf/pull/1362) 為 `Hyperf\Pool\SimplePool\PoolFactory` 增加 `getPoolNames()` 來獲取連線池的名稱；
- [#1371](https://github.com/hyperf/hyperf/pull/1371) 新增 `Hyperf\DB\DB::connection()` 方法來指定要使用的連線；
- [#1384](https://github.com/hyperf/hyperf/pull/1384) 為 `gen:model` 命令新增  `property-case` 選項來設定成員屬性的命名風格；

## 修復

- [#1386](https://github.com/hyperf/hyperf/pull/1386) 修復非同步訊息投遞註解當用在存在可變引數的方法上失效的問題；

# v1.1.18 - 2020-02-27

## 新增

- [#1305](https://github.com/hyperf/hyperf/pull/1305) 為 `hyperf\metric` 元件新增預製的 `Grafana` 面板；
- [#1328](https://github.com/hyperf/hyperf/pull/1328) 新增 `ModelRewriteInheritanceVisitor` 來重寫 model 類繼承的 `gen:model` 命令；
- [#1331](https://github.com/hyperf/hyperf/pull/1331) 新增 `Hyperf\LoadBalancer\LoadBalancerInterface::getNodes()`；
- [#1335](https://github.com/hyperf/hyperf/pull/1335) 為 `command` 新增 `AfterExecute` 事件；
- [#1361](https://github.com/hyperf/hyperf/pull/1361) logger 元件新增 `processors` 配置；

## 修復

- [#1330](https://github.com/hyperf/hyperf/pull/1330) 修復當使用 `(new Parallel())->add($callback, $key)` 並且引數 `$key` 並非 string 型別, 返回結果將會從 0 開始排序 `$key`；
- [#1338](https://github.com/hyperf/hyperf/pull/1338) 修復當從 server 設定自己的設定時, 主 server 的配置不生效的 bug；
- [#1344](https://github.com/hyperf/hyperf/pull/1344) 修復佇列在沒有設定最大訊息數時每次都需要校驗長度的 bug；

## 變更

- [#1324](https://github.com/hyperf/hyperf/pull/1324) [hyperf/async-queue](https://github.com/hyperf/async-queue) 元件不再提供預設啟用 `Hyperf\AsyncQueue\Listener\QueueLengthListener`；

## 優化

- [#1305](https://github.com/hyperf/hyperf/pull/1305) 優化 `hyperf\metric` 中的邊界條件；
- [#1322](https://github.com/hyperf/hyperf/pull/1322) HTTP Server 自動處理 HEAD 請求並且不會在 HEAD 請求時返回 Response body；

## 刪除

- [#1303](https://github.com/hyperf/hyperf/pull/1303) 刪除 `Hyperf\RpcServer\Router\Router` 中無用的 `$httpMethod`；

# v1.1.17 - 2020-01-24

## 新增

- [#1220](https://github.com/hyperf/hyperf/pull/1220) 為 Apollo 元件增加 BootProcessListener 來實現在服務啟動時從 Apollo 拉取配置的功能；
- [#1292](https://github.com/hyperf/hyperf/pull/1292) 為 `Hyperf\Database\Schema\Blueprint::foreign()` 方法的返回型別增加了 `Hyperf\Database\Schema\ForeignKeyDefinition` 型別；
- [#1313](https://github.com/hyperf/hyperf/pull/1313) 為 `hyperf\crontab` 元件增加了 Command 模式支援；
- [#1321](https://github.com/hyperf/hyperf/pull/1321) 增加 [hyperf/nsq](https://github.com/hyperf/nsq) 元件，[NSQ](https://nsq.io) 是一個實時的分散式訊息平臺；

## 修復

- [#1291](https://github.com/hyperf/hyperf/pull/1291) 修復 [hyperf/super-globals](https://github.com/hyperf/super-globals) 元件的 `$_SERVER` 存在小寫鍵值與 PHP-FPM 不統一的問題；
- [#1308](https://github.com/hyperf/hyperf/pull/1308) 修復 [hyperf/validation](https://github.com/hyperf/validation) 元件缺失的一些翻譯內容, 包括 gt, gte, ipv4, ipv6, lt, lte, mimetypes, not_regex, starts_with, uuid；
- [#1310](https://github.com/hyperf/hyperf/pull/1310) 修復服務註冊在當服務同名不同協議的情況下會被覆蓋的問題；
- [#1315](https://github.com/hyperf/hyperf/pull/1315) 修復 `Hyperf\AsyncQueue\Process\ConsumerProcess` 類缺失的 $config 變數；

# v1.1.16 - 2020-01-16

## 新增

- [#1263](https://github.com/hyperf/hyperf/pull/1263) 為 async-queue 元件增加 `QueueLength` 事件；
- [#1276](https://github.com/hyperf/hyperf/pull/1276) 為 Consul 客戶端增加 ACL token 支援；
- [#1277](https://github.com/hyperf/hyperf/pull/1277) 為 [hyperf/metric](https://github.com/hyperf/metric) 元件增加 NoOp 驅動，用來臨時關閉 metric 功能；

## 修復

- [#1262](https://github.com/hyperf/hyperf/pull/1262) 修復 keepaliveIO 功能下 socket 會被消耗光的問題；
- [#1266](https://github.com/hyperf/hyperf/pull/1266) 修復當自定義程序存在 Timer 的情況下會無法重啟的問題；
- [#1272](https://github.com/hyperf/hyperf/pull/1272) 修復 JSONRPC 下當 Request ID 為 null 時檢查會失敗的問題； 

## 優化

- [#1273](https://github.com/hyperf/hyperf/pull/1273) 優化 gRPC 客戶端：
  - 優化使 gRPC 客戶端在當連線與 Server 斷開時會自動重連；
  - 優化使當 gRPC 客戶端被垃圾回收時，已建立的連線會自動關閉；
  - 修復關閉了的客戶端依舊會持有 HTTP2 連線的問題；
  - 修復 gRPC 客戶端的 channel pool 可能會存在非空 channel 的問題；
  - 優化使 gRPC 客戶端會自動初始化，所以現在可以在建構函式和容器注入下使用；

## 刪除

- [#1286](https://github.com/hyperf/hyperf/pull/1286) 從 require-dev 中移除 [phpstan/phpstan](https://github.com/phpstan/phpstan) 包的依賴。

# v1.1.15 - 2020-01-10

## 修復

- [#1258](https://github.com/hyperf/hyperf/pull/1258) 修復 AMQP 傳送心跳失敗，會導致子程序 Socket 通訊不可用的問題；
- [#1260](https://github.com/hyperf/hyperf/pull/1260) 修復 JSONRPC 在同一協程內，連線會混淆複用的問題；

# v1.1.14 - 2020-01-10

## 新增

- [#1166](https://github.com/hyperf/hyperf/pull/1166) 為 AMQP 增加 KeepaliveIO 功能；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 為 JSON-RPC 的響應增加了 `error.data.code` 值來傳遞 Exception Code；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 為 `Hyperf\Rpc\Contract\TransporterInterface` 增加了 `recv` 方法；
- [#1215](https://github.com/hyperf/hyperf/pull/1215) 新增 [hyperf/super-globals](https://github.com/hyperf/super-globals) 元件，用來適配一些不支援 PSR-7 的第三方包；
- [#1219](https://github.com/hyperf/hyperf/pull/1219) 為 AMQP 消費者增加 `enable` 屬性，通過該屬性來控制該消費者是否跟隨 Server 一同啟動；

## 修復

- [#1208](https://github.com/hyperf/hyperf/pull/1208) 修復 Exception 和 error 在 JSON-RPC TCP Server 下無法被正確處理的問題；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 修復 JSON-RPC 沒有檢查 Request ID 和 Response ID 是否一致的問題；
- [#1223](https://github.com/hyperf/hyperf/pull/1223) 修復 ConfigProvider 掃描器不會掃描 composer.json 內 require-dev 的配置；
- [#1254](https://github.com/hyperf/hyperf/pull/1254) 修復執行 `init-proxy.sh` 命令在某些環境如 Alpine 下會報 bash 不存在的問題；

## 優化

- [#1208](https://github.com/hyperf/hyperf/pull/1208) 優化了 JSON-RPC 元件的部分邏輯；
- [#1174](https://github.com/hyperf/hyperf/pull/1174) 調整了 `Hyperf\Utils\Parallel` 在輸出異常時的格式，現在會一同列印 Trace 資訊；
- [#1224](https://github.com/hyperf/hyperf/pull/1224) 允許 Aliyun ACM 配置中心的配置獲取程序解析 UTF-8 字元，同時在 Worker 啟動後會自動獲取一次配置，以及拉取的配置現在會傳遞到自定義程序了；
- [#1235](https://github.com/hyperf/hyperf/pull/1235) 在 AMQP 生產者執行 declare 後釋放對應的連線；

## 修改

- [#1227](https://github.com/hyperf/hyperf/pull/1227) 升級 `jcchavezs/zipkin-php-opentracing` 依賴至 0.1.4 版本；

# v1.1.13 - 2020-01-03

## 新增

- [#1137](https://github.com/hyperf/hyperf/pull/1137) `constants` 元件增加國際化支援；
- [#1165](https://github.com/hyperf/hyperf/pull/1165) `Hyperf\HttpServer\Contract\RequestInterface` 新增 `route` 方法；
- [#1195](https://github.com/hyperf/hyperf/pull/1195) 註解 `Cacheable` 和 `CachePut` 增加最大超時時間偏移量配置；
- [#1204](https://github.com/hyperf/hyperf/pull/1204) `database` 元件增加了 `insertOrIgnore` 方法；
- [#1216](https://github.com/hyperf/hyperf/pull/1216) `RenderInterface::render()` 方法的 `$data` 引數，添加了預設值；
- [#1221](https://github.com/hyperf/hyperf/pull/1221) `swoole-tracker` 元件添加了 `traceId` 和 `spanId`；

## 修復

- [#1175](https://github.com/hyperf/hyperf/pull/1175) 修復 `Hyperf\Utils\Collection::random` 當傳入 `null` 時，無法正常工作的 `BUG`；
- [#1199](https://github.com/hyperf/hyperf/pull/1199) 修復使用 `Task` 註解時，引數無法使用動態變數的 `BUG`；
- [#1200](https://github.com/hyperf/hyperf/pull/1200) 修復 `metric` 元件，請求路徑會攜帶引數的 `BUG`；
- [#1210](https://github.com/hyperf/hyperf/pull/1210) 修復驗證器規則 `size` 無法作用於 `integer` 的 `BUG`；

## 優化

- [#1211](https://github.com/hyperf/hyperf/pull/1211) 自動將專案名轉化為 `prometheus` 的規範命名；

## 修改

- [#1217](https://github.com/hyperf/hyperf/pull/1217) 將 `zendframework/zend-mime` 替換為 `laminas/laminas-mine`；

# v1.1.12 - 2019-12-26

## 新增

- [#1177](https://github.com/hyperf/hyperf/pull/1177) 為 `jsonrpc` 元件增加了新的協議 `jsonrpc-tcp-length-check`，並對部分程式碼進行了優化；

## 修復

- [#1175](https://github.com/hyperf/hyperf/pull/1175) 修復 `Hyperf\Utils\Collection::random` 方法不支援傳入 `null`；
- [#1178](https://github.com/hyperf/hyperf/pull/1178) 修復 `Hyperf\Database\Query\Builder::chunkById` 方法不支援元素是 `array` 的情況；
- [#1189](https://github.com/hyperf/hyperf/pull/1189) 修復 `Hyperf\Utils\Collection::operatorForWhere` 方法，`operator` 只能傳入 `string` 的 BUG；

## 優化

- [#1186](https://github.com/hyperf/hyperf/pull/1186) 日誌配置中，只填寫 `formatter.class` 的情況下，可以使用預設的 `formatter.constructor` 配置；

# v1.1.11 - 2019-12-19

## 新增

- [#849](https://github.com/hyperf/hyperf/pull/849) 為 hyperf/tracer 元件增加 span tag 配置功能；

## 修復

- [#1142](https://github.com/hyperf/hyperf/pull/1142) 修復 `Register::resolveConnection` 會返回 null 的問題；
- [#1144](https://github.com/hyperf/hyperf/pull/1144) 修復配置檔案形式下服務限流會失效的問題；
- [#1145](https://github.com/hyperf/hyperf/pull/1145) 修復 `CoroutineMemoryDriver::delKey` 方法的返回值錯誤的問題；
- [#1153](https://github.com/hyperf/hyperf/pull/1153) 修復驗證器的 `alpha_num` 規則無法按預期執行的問題；

# v1.1.10 - 2019-12-12

## 修復

- [#1104](https://github.com/hyperf/hyperf/pull/1104) 修復了 Guzzle 客戶端的重試中介軟體的狀態碼識別範圍為 2xx；
- [#1105](https://github.com/hyperf/hyperf/pull/1105) 修復了 Retry 元件在重試嘗試前不還原管道堆疊的問題；
- [#1106](https://github.com/hyperf/hyperf/pull/1106) 修復了資料庫在開啟 `sticky` 模式時連接回歸連線池時沒有重置狀態的問題；
- [#1119](https://github.com/hyperf/hyperf/pull/1119) 修復 TCP 協議下的 JSONRPC Server 在解析 JSON 失敗時無法正確的返回預期的 Error Response 的問題；
- [#1124](https://github.com/hyperf/hyperf/pull/1124) 修復 Session 中介軟體在儲存當前的 URL 時，當 URL 以 `/` 結尾時會忽略斜槓的問題；

## 變更

- [#1108](https://github.com/hyperf/hyperf/pull/1108) 重新命名 `Hyperf\Tracer\Middleware\TraceMiddeware` 為 `Hyperf\Tracer\Middleware\TraceMiddleware`；
- [#1108](https://github.com/hyperf/hyperf/pull/1111) 升級 `Hyperf\ServiceGovernance\Listener\ServiceRegisterListener` 類的成員屬性和方法的等級為 `protected`，以便更好的重寫相關方法；

# v1.1.9 - 2019-12-05

## 新增

- [#948](https://github.com/hyperf/hyperf/pull/948) 為 DI Container 增加懶載入功能；
- [#1044](https://github.com/hyperf/hyperf/pull/1044) 為 AMQP Consumer 增加 `basic_qos` 配置；
- [#1056](https://github.com/hyperf/hyperf/pull/1056) [#1081](https://github.com/hyperf/hyperf/pull/1081) DI Container 增加 `define()` 和 `set()` 方法，同時增加 `Hyperf\Contract\ContainerInterface`；
- [#1059](https://github.com/hyperf/hyperf/pull/1059) `job.stub` 模板增加建構函式；
- [#1084](https://github.com/hyperf/hyperf/pull/1084) 支援 PHP 7.4，TrvisCI 增加 PHP 7.4 執行支援；

## 修復

- [#1007](https://github.com/hyperf/hyperf/pull/1007) 修復 `vendor:: publish` 的命令返回值；
- [#1049](https://github.com/hyperf/hyperf/pull/1049) 修復 `Hyperf\Cache\Driver\RedisDriver::clear` 會有可能刪除所有快取失敗的問題；
- [#1055](https://github.com/hyperf/hyperf/pull/1055) 修復 Image 驗證時後綴大小寫的問題；
- [#1085](https://github.com/hyperf/hyperf/pull/1085) [#1091](https://github.com/hyperf/hyperf/pull/1091) Fixed `@Retry` 註解使用時會找不到容器的問題；

# v1.1.8 - 2019-11-28

## 新增

- [#965](https://github.com/hyperf/hyperf/pull/965) 新增 Redis Lua 模組，用於管理 Lua 指令碼；
- [#1023](https://github.com/hyperf/hyperf/pull/1023) hyperf/metric 元件的 Prometheus 驅動新增 CUSTOM_MODE 模式；

## 修復

- [#1013](https://github.com/hyperf/hyperf/pull/1013) 修復 JsonRpcPoolTransporter 配置合併失敗的問題；
- [#1006](https://github.com/hyperf/hyperf/pull/1006) 修復 `gen:model` 命令生成的屬性的順序；

## 變更

- [#1021](https://github.com/hyperf/hyperf/pull/1012) WebSocket 客戶端新增預設埠支援，根據協議預設為 80 和 443；
- [#1034](https://github.com/hyperf/hyperf/pull/1034) 去掉了 `Hyperf\Amqp\Builder\Builder` 的 `arguments` 引數的 array 型別限制，允許接受其他型別如 AmqpTable；

## 優化

- [#1014](https://github.com/hyperf/hyperf/pull/1014) 優化 `Command::execute` 的返回值型別；
- [#1022](https://github.com/hyperf/hyperf/pull/1022) 提供更清晰友好的連線池報錯資訊；
- [#1039](https://github.com/hyperf/hyperf/pull/1039) 在 CoreMiddleware 中自動設定最新的 ServerRequest 物件到 Context；

# v1.1.7 - 2019-11-21

## 新增

- [#860](https://github.com/hyperf/hyperf/pull/860) 新增 [hyperf/retry](https://github.com/hyperf/retry) 元件；
- [#952](https://github.com/hyperf/hyperf/pull/952) 新增 ThinkTemplate 檢視引擎支援；
- [#973](https://github.com/hyperf/hyperf/pull/973) 新增 JSON RPC 在 TCP 協議下的連線池支援，通過 `Hyperf\JsonRpc\JsonRpcPoolTransporter` 來使用連線池版本；
- [#976](https://github.com/hyperf/hyperf/pull/976) 為 `hyperf/amqp` 元件新增  `close_on_destruct` 選項引數，用來控制程式碼在執行解構函式時是否主動去關閉連線；

## 變更

- [#944](https://github.com/hyperf/hyperf/pull/944) 將元件內所有使用 `@Listener` 和 `@Process` 註解來註冊的改成通過 `ConfigProvider`來註冊；
- [#977](https://github.com/hyperf/hyperf/pull/977) 調整 `init-proxy.sh` 命令的行為，改成只刪除 `runtime/container` 目錄；

## 修復

- [#955](https://github.com/hyperf/hyperf/pull/955) 修復 `hyperf/db` 元件的 `port` 和 `charset` 引數無效的問題；
- [#956](https://github.com/hyperf/hyperf/pull/956) 修復模型快取中使用到`RedisHandler::incr` 在叢集模式下會失敗的問題；
- [#966](https://github.com/hyperf/hyperf/pull/966) 修復當在非 Worker 程序環境下使用分頁器會報錯的問題；
- [#968](https://github.com/hyperf/hyperf/pull/968) 修復當 `classes` 和 `annotations` 兩種 Aspect 切入模式同時存在於一個類時，其中一個可能會失效的問題；
- [#980](https://github.com/hyperf/hyperf/pull/980) 修復 Session 元件內 `migrate`, `save` 核 `has` 方法無法使用的問題；
- [#982](https://github.com/hyperf/hyperf/pull/982) 修復 `Hyperf\GrpcClient\GrpcClient::yield` 在獲取 Channel Pool 時沒有通過正確的獲取方式去獲取的問題；
- [#987](https://github.com/hyperf/hyperf/pull/987) 修復通過 `gen:command` 命令生成的命令類缺少呼叫 `parent::configure()` 方法的問題；

## 優化

- [#991](https://github.com/hyperf/hyperf/pull/991) 優化 `Hyperf\DbConnection\ConnectionResolver::connection`的異常情況處理；

# v1.1.6 - 2019-11-14

## 新增

- [#827](https://github.com/hyperf/hyperf/pull/827) 新增了極簡的高效能的 DB 元件；
- [#905](https://github.com/hyperf/hyperf/pull/905) 檢視元件增加了 `twig` 模板引擎；
- [#911](https://github.com/hyperf/hyperf/pull/911) 定時任務支援多例項情況下，只執行單一例項的定時任務；
- [#913](https://github.com/hyperf/hyperf/pull/913) 增加監聽器 `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`；
- [#921](https://github.com/hyperf/hyperf/pull/921) 新增 `Session` 元件；
- [#931](https://github.com/hyperf/hyperf/pull/931) 阿波羅配置中心增加 `strict_mode`，自動將配置轉化成對應資料型別；
- [#933](https://github.com/hyperf/hyperf/pull/933) 檢視元件增加了 `plates` 模板引擎；
- [#937](https://github.com/hyperf/hyperf/pull/937) Nats 元件新增消費者消費和訂閱事件；
- [#941](https://github.com/hyperf/hyperf/pull/941) 新增 `Zookeeper` 配置中心；

## 變更

- [#934](https://github.com/hyperf/hyperf/pull/934) 修改 `WaitGroup` 繼承 `\Swoole\Coroutine\WaitGroup`；

## 修復

- [#897](https://github.com/hyperf/hyperf/pull/897) 修復 `Nats` 消費者，`pool` 配置無效的 BUG；
- [#901](https://github.com/hyperf/hyperf/pull/901) 修復 `GraphQL` 元件，`Factory` 註解無法正常使用的 BUG；
- [#903](https://github.com/hyperf/hyperf/pull/903) 修復新增 `hyperf/rpc-client` 依賴後，`init-proxy` 指令碼無法正常停止的 BUG；
- [#904](https://github.com/hyperf/hyperf/pull/904) 修復監聽器監聽 `Hyperf\Framework\Event\BeforeMainServerStart` 事件時，無法使用 `IO` 操作的 BUG；
- [#906](https://github.com/hyperf/hyperf/pull/906) 修復 `Hyperf\HttpMessage\Server\Request` 埠獲取有誤的 BUG；
- [#907](https://github.com/hyperf/hyperf/pull/907) 修復 `Nats` 元件 `requestSync` 方法，超時時間不準確的 BUG；
- [#909](https://github.com/hyperf/hyperf/pull/909) 修復 `Parallel` 內邏輯拋錯後，無法正常停止的 BUG；
- [#925](https://github.com/hyperf/hyperf/pull/925) 修復因 `Socket` 無法正常建立，導致程序頻繁重啟的 BUG；
- [#932](https://github.com/hyperf/hyperf/pull/932) 修復 `Translator::setLocale` 在協程環境下，資料混淆的 BUG；
- [#940](https://github.com/hyperf/hyperf/pull/940) 修復 `WebSocketClient::push` 方法 `finish` 引數型別錯誤；

## 優化

- [#907](https://github.com/hyperf/hyperf/pull/907) 優化 `Nats` 消費者頻繁重啟；
- [#928](https://github.com/hyperf/hyperf/pull/928) `Hyperf\ModelCache\Cacheable::query` 批量修改資料時，可以刪除對應快取；
- [#936](https://github.com/hyperf/hyperf/pull/936) 優化呼叫模型快取 `increment` 時，可能因併發情況導致的資料有錯；

# v1.1.5 - 2019-11-07

## 新增

- [#812](https://github.com/hyperf/hyperf/pull/812) 新增計劃任務在叢集下僅執行一次的支援；
- [#820](https://github.com/hyperf/hyperf/pull/820) 新增 hyperf/nats 元件；
- [#832](https://github.com/hyperf/hyperf/pull/832) 新增 `Hyperf\Utils\Codec\Json`；
- [#833](https://github.com/hyperf/hyperf/pull/833) 新增 `Hyperf\Utils\Backoff`；
- [#852](https://github.com/hyperf/hyperf/pull/852) 為 `Hyperf\Utils\Parallel` 新增 `clear()` 方法來清理所有已新增的回撥；
- [#854](https://github.com/hyperf/hyperf/pull/854) 新增 `Hyperf\GraphQL\GraphQLMiddleware` 用於解析 GraphQL 請求；
- [#859](https://github.com/hyperf/hyperf/pull/859) 新增 Consul 叢集的支援，現在可以從 Consul 叢集中拉取服務提供者的節點資訊；
- [#873](https://github.com/hyperf/hyperf/pull/873) 新增 Redis 叢集的客戶端支援；

## 修復

- [#831](https://github.com/hyperf/hyperf/pull/831) 修復 Redis 客戶端連線在 Redis Server 重啟後不會自動重連的問題；
- [#835](https://github.com/hyperf/hyperf/pull/835) 修復 `Request::inputs` 方法的預設值引數與預期效果不一致的問題；
- [#841](https://github.com/hyperf/hyperf/pull/841) 修復資料庫遷移在多資料庫的情況下連線無效的問題；
- [#844](https://github.com/hyperf/hyperf/pull/844) 修復 Composer 閱讀器不支援根名稱空間的用法的問題；
- [#846](https://github.com/hyperf/hyperf/pull/846) 修復 Redis 客戶端的 `scan`, `hScan`, `zScan`, `sScan` 無法使用的問題；
- [#850](https://github.com/hyperf/hyperf/pull/850) 修復 Logger group 在 name 一樣時不生效的問題；

## 優化

- [#832](https://github.com/hyperf/hyperf/pull/832) 優化了 Response 物件在轉 JSON 格式時的異常處理邏輯；
- [#840](https://github.com/hyperf/hyperf/pull/840) 使用 `\Swoole\Timer::*` 來替代 `swoole_timer_*` 函式；
- [#859](https://github.com/hyperf/hyperf/pull/859) 優化了 RPC 客戶端去 Consul 獲取健康的節點資訊的邏輯；

# v1.1.4 - 2019-10-31

## 新增

- [#778](https://github.com/hyperf/hyperf/pull/778) `Hyperf\Testing\Client` 新增 `PUT` 和 `DELETE`方法；
- [#784](https://github.com/hyperf/hyperf/pull/784) 新增服務監控元件；
- [#795](https://github.com/hyperf/hyperf/pull/795) `AbstractProcess` 增加 `restartInterval` 引數，允許子程序異常或正常退出後，延遲重啟；
- [#804](https://github.com/hyperf/hyperf/pull/804) `Command` 增加事件 `BeforeHandle` `AfterHandle` 和 `FailToHandle`；

## 變更

- [#793](https://github.com/hyperf/hyperf/pull/793) `Pool::getConnectionsInChannel` 方法由 `protected` 改為 `public`.
- [#811](https://github.com/hyperf/hyperf/pull/811) 命令 `di:init-proxy` 不再主動清理代理快取，如果想清理快取請使用命令 `vendor/bin/init-proxy.sh`；

## 修復

- [#779](https://github.com/hyperf/hyperf/pull/779) 修復 `JPG` 檔案驗證不通過的問題；
- [#787](https://github.com/hyperf/hyperf/pull/787) 修復 `db:seed` 引數 `--class` 多餘，導致報錯的問題；
- [#795](https://github.com/hyperf/hyperf/pull/795) 修復自定義程序在異常丟擲後，無法正常重啟的 BUG；
- [#796](https://github.com/hyperf/hyperf/pull/796) 修復 `etcd` 配置中心 `enable` 即時設為 `false`，在專案啟動時，依然會拉取配置的 BUG；

## 優化

- [#781](https://github.com/hyperf/hyperf/pull/781) 可以根據國際化元件配置釋出驗證器語言包到規定位置；
- [#796](https://github.com/hyperf/hyperf/pull/796) 優化 `ETCD` 客戶端，不會多次建立 `HandlerStack`； 
- [#797](https://github.com/hyperf/hyperf/pull/797) 優化子程序重啟

# v1.1.3 - 2019-10-24

## 新增

- [#745](https://github.com/hyperf/hyperf/pull/745) 為 `gen:model` 命令增加 `with-comments` 選項，以標記是否生成欄位註釋；
- [#747](https://github.com/hyperf/hyperf/pull/747) 為 AMQP 消費者增加 `AfterConsume`, `BeforeConsume`, `FailToConsume` 事件； 
- [#762](https://github.com/hyperf/hyperf/pull/762) 為 Parallel 特性增加協程控制功能；

## 變更

- [#767](https://github.com/hyperf/hyperf/pull/767) 重新命名 `AbstractProcess` 的 `running` 屬性名為 `listening`；

## 修復

- [#741](https://github.com/hyperf/hyperf/pull/741) 修復執行 `db:seed` 命令缺少檔名報錯的問題；
- [#748](https://github.com/hyperf/hyperf/pull/748) 修復 `SymfonyNormalizer` 不處理 `array` 型別資料的問題；
- [#769](https://github.com/hyperf/hyperf/pull/769) 修復當 JSON RPC 響應的結果的 result 和 error 屬性為 null 時會丟擲一個無效請求的問題；

# v1.1.2 - 2019-10-17

## 新增

- [#722](https://github.com/hyperf-cloud/hyperf/pull/722) 為 AMQP Consumer 新增 `concurrent.limit` 配置來對協程消費進行速率限制；

## 變更

- [#678](https://github.com/hyperf-cloud/hyperf/pull/678) 為 `gen:model` 命令增加 `ignore-tables` 引數，同時預設遮蔽 `migrations` 表，即 `migrations` 表對應的模型在執行 `gen:model` 命令時不會生成；

## 修復

- [#694](https://github.com/hyperf-cloud/hyperf/pull/694) 修復 `Hyperf\Validation\Request\FormRequest` 的 `validationData` 方法不包含上傳的檔案的問題；
- [#700](https://github.com/hyperf-cloud/hyperf/pull/700) 修復 `Hyperf\HttpServer\Contract\ResponseInterface` 的 `download` 方法不能按預期執行的問題；
- [#701](https://github.com/hyperf-cloud/hyperf/pull/701) 修復自定義程序在出現未捕獲的異常時不會自動重啟的問題；
- [#704](https://github.com/hyperf-cloud/hyperf/pull/704) 修復 `Hyperf\Validation\Middleware\ValidationMiddleware` 在 action 引數沒有定義引數型別時會報錯的問題；
- [#713](https://github.com/hyperf-cloud/hyperf/pull/713) 修復當開啟了註解快取功能是，`ignoreAnnotations` 不能按預期工作的問題；
- [#717](https://github.com/hyperf-cloud/hyperf/pull/717) 修復 `getValidatorInstance` 方法會重複建立驗證器物件的問題；
- [#724](https://github.com/hyperf-cloud/hyperf/pull/724) 修復 `db:seed` 命令在沒有傳 `database` 引數時會報錯的問題； 
- [#729](https://github.com/hyperf-cloud/hyperf/pull/729) 修正元件配置項 `db:model` 為 `gen:model`；
- [#737](https://github.com/hyperf-cloud/hyperf/pull/737) 修復非 Worker 程序下無法使用 Tracer 元件來追蹤呼叫鏈的問題；

# v1.1.1 - 2019-10-08

## Fixed

- [#664](https://github.com/hyperf/hyperf/pull/664) 調整通過 `gen:request` 命令生成 FormRequest 時 `authorize` 方法的預設返回值；
- [#665](https://github.com/hyperf/hyperf/pull/665) 修復啟動時永遠會自動生成代理類的問題；
- [#667](https://github.com/hyperf/hyperf/pull/667) 修復當訪問一個不存在的路由時 `Hyperf\Validation\Middleware\ValidationMiddleware` 會丟擲異常的問題；
- [#672](https://github.com/hyperf/hyperf/pull/672) 修復當 Action 方法上的引數型別為非物件型別時 `Hyperf\Validation\Middleware\ValidationMiddleware` 會丟擲一個未捕獲的異常的問題；
- [#674](https://github.com/hyperf/hyperf/pull/674) 修復使用 `gen:model` 命令從資料庫生成模型時模型表名錯誤的問題；

# v1.1.0 - 2019-10-08

## 新增

- [#401](https://github.com/hyperf/hyperf/pull/401) 新增了 `Hyperf\HttpServer\Router\Dispatched` 物件來儲存解析的路由資訊，在使用者中介軟體之前便解析完成以便後續的使用，同時也修復了路由裡帶參時中介軟體失效的問題；
- [#402](https://github.com/hyperf/hyperf/pull/402) 新增 `@AsyncQueueMessage` 註解，通過定義此註解在方法上，表明這個方法的實際執行邏輯是投遞給 Async-Queue 佇列去消費；
- [#418](https://github.com/hyperf/hyperf/pull/418) 允許傳送 WebSocket 訊息到任意的 fd，即使當前的 Worker 程序不持有對應的 fd，框架會自動進行程序間通訊來實現傳送；
- [#420](https://github.com/hyperf/hyperf/pull/420) 為資料庫模型增加新的事件機制，與 PSR-15 的事件排程器相配合，可以解耦的定義 Listener 來監聽模型事件；
- [#429](https://github.com/hyperf/hyperf/pull/429) [#643](https://github.com/hyperf/hyperf/pull/643) 新增 Validation 表單驗證器元件，這是一個衍生於 [illuminate/validation](https://github.com/illuminate/validation) 的元件，感謝 Laravel 開發組提供如此好用的驗證器元件，；
- [#441](https://github.com/hyperf/hyperf/pull/441) 當 Redis 連線處於低使用頻率的情況下自動關閉空閒連線；
- [#478](https://github.com/hyperf/hyperf/pull/441) 更好的適配 OpenTracing 協議，同時適配 [Jaeger](https://www.jaegertracing.io/)，Jaeger 是一款優秀的開源的端對端分散式呼叫鏈追蹤系統；
- [#500](https://github.com/hyperf/hyperf/pull/499) 為 `Hyperf\HttpServer\Contract\ResponseInterface` 增加鏈式方法呼叫支援，解決呼叫了代理方法的方法後無法再呼叫原始方法的問題；
- [#523](https://github.com/hyperf/hyperf/pull/523) 為  `gen:model` 命令新增了 `table-mapping` 選項；
- [#555](https://github.com/hyperf/hyperf/pull/555) 新增了一個全域性函式 `swoole_hook_flags` 來獲取由常量 `SWOOLE_HOOK_FLAGS` 所定義的 Runtime Hook 等級，您可以在 `bin/hyperf.php` 通過 `! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);` 的方式來定義該常量，即 Runtime Hook 等級；
- [#596](https://github.com/hyperf/hyperf/pull/596)  為`@Inject` 註解增加了  `required` 引數，當您定義 `@Inject(required=false)` 註解到一個成員屬性上，那麼當該依賴項不存在時也不會丟擲 `Hyperf\Di\Exception\NotFoundException` 異常，而是以預設值 `null` 來注入， `required` 引數的預設值為 `true`，當在構造器注入的情況下，您可以通過對構造器的引數定義為 `nullable` 來達到同樣的目的；
- [#597](https://github.com/hyperf/hyperf/pull/597) 為 AsyncQueue 元件的消費者增加 `Concurrent` 來控制消費速率；
- [#599](https://github.com/hyperf/hyperf/pull/599) 為 AsyncQueue 元件的消費者增加根據當前重試次數來設定該訊息的重試等待時長的功能，可以為訊息設定階梯式的重試等待；
- [#619](https://github.com/hyperf/hyperf/pull/619) 為 Guzzle 客戶端增加 HandlerStackFactory 類，以便更便捷地建立一個 HandlerStack；
- [#620](https://github.com/hyperf/hyperf/pull/620) 為 AsyncQueue 元件的消費者增加自動重啟的機制；
- [#629](https://github.com/hyperf/hyperf/pull/629) 允許通過配置檔案的形式為 Apollo 客戶端定義  `clientIp`, `pullTimeout`, `intervalTimeout` 配置；
- [#647](https://github.com/hyperf/hyperf/pull/647) 根據 server 的配置，自動為 TCP Response 追加 `eof`；
- [#648](https://github.com/hyperf/hyperf/pull/648) 為 AMQP Consumer 增加 `nack` 的返回型別，當消費邏輯返回 `Hyperf\Amqp\Result::NACK` 時抽象消費者會以 `basic_nack` 方法來響應訊息；
- [#654](https://github.com/hyperf/hyperf/pull/654) 增加所有 Swoole Event 的預設回撥和對應的 Hyperf 事件；

## 變更

- [#437](https://github.com/hyperf/hyperf/pull/437) `Hyperf\Testing\Client` 在遇到異常時不再直接丟擲異常而是交給 ExceptionHandler 流程處理；
- [#463](https://github.com/hyperf/hyperf/pull/463) 簡化了 `container.php` 檔案及優化了註解快取機制；

新的 config/container.php 檔案內容如下：

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

- [#486](https://github.com/hyperf/hyperf/pull/486) `Hyperf\HttpMessage\Server\Request` 的 `getParsedBody` 方法現在可以直接處理 JSON 格式的資料了；
- [#523](https://github.com/hyperf/hyperf/pull/523) 調整 `gen:model` 命令生成的模型類名預設為單數，如果表名為複數，則預設生成的類名為單數；
- [#614](https://github.com/hyperf/hyperf/pull/614) [#617](https://github.com/hyperf/hyperf/pull/617) 調整了 ConfigProvider 類的結構, 同時將 `config/dependencies.php` 檔案移動到了 `config/autoload/dependencies.php` 內，且檔案結構去除了 `dependencies` 層，此後也意味著您也可以將 `dependencies` 配置寫到 `config/config.php` 檔案內；

Config Provider 內資料結構的變化：
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

> 增加了一層 annotations，這樣將與配置檔案結構一致，不再特殊

- [#630](https://github.com/hyperf/hyperf/pull/630) 變更了 `Hyperf\HttpServer\CoreMiddleware` 類的例項化方式，使用 `make()` 來替代了 `new`；
- [#631](https://github.com/hyperf/hyperf/pull/631) 變更了 AMQP Consumer 的例項化方式，使用 `make()` 來替代了 `new`；
- [#637](https://github.com/hyperf/hyperf/pull/637) 調整了 `Hyperf\Contract\OnMessageInterface` 和 `Hyperf\Contract\OnOpenInterface` 的第一個引數的型別約束， 使用 `Swoole\WebSocket\Server` 替代 `Swoole\Server`；
- [#638](https://github.com/hyperf/hyperf/pull/638) 重新命名了 `db:model` 命令為 `gen:model` 命令，同時增加了一個 Visitor 來優化建立的 `$connection` 成員屬性，如果要建立的模型類的 `$connection` 屬性的值與繼承的父類一致，那麼建立的模型類將不會包含此屬性；

## 移除

- [#401](https://github.com/hyperf/hyperf/pull/401) 移除了 `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\HttpServer\ServerFactory`, `Hyperf\GrpcServer\ServerFactory` 類；
- [#402](https://github.com/hyperf/hyperf/pull/402) 移除了棄用的 `AsyncQueue::delay` 方法；
- [#563](https://github.com/hyperf/hyperf/pull/563) 移除了棄用的 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量，使用 `Hyperf\Server\ServerInterface::SERVER_BASE` 來替代；
- [#602](https://github.com/hyperf/hyperf/pull/602) 移除了 `Hyperf\Utils\Coroutine\Concurrent` 的 `timeout` 引數；
- [#612](https://github.com/hyperf/hyperf/pull/612) 移除了 RingPHP Handler 裡沒有使用到的 `$url` 變數；
- [#616](https://github.com/hyperf/hyperf/pull/616) [#618](https://github.com/hyperf/hyperf/pull/618) 移除了 Guzzle 裡一些無用的程式碼；

## 優化

- [#644](https://github.com/hyperf/hyperf/pull/644) 優化了註解掃描的流程，分開 `app` 和 `vendor` 兩部分來掃描註解，大大減少了使用者的掃描耗時；
- [#653](https://github.com/hyperf/hyperf/pull/653) 優化了 Swoole shortname 的檢測邏輯，現在的檢測邏輯更加貼合 Swoole 的實際配置場景，也不只是 `swoole.use_shortname = "Off"` 才能通過檢測了；

## 修復

- [#448](https://github.com/hyperf/hyperf/pull/448) 修復了當 HTTP Server 或 WebSocket Server 存在時，TCP Server 有可能無法啟動的問題；
- [#623](https://github.com/hyperf/hyperf/pull/623) 修復了當傳遞一個 `null` 值到代理類的方法引數時，方法仍然會獲取方法預設值的問題；

# v1.0.16 - 2019-09-20

## 新增

- [#565](https://github.com/hyperf/hyperf/pull/565) 增加對 Redis 客戶端的 `options` 配置引數支援；
- [#580](https://github.com/hyperf/hyperf/pull/580) 增加協程併發控制特性，通過 `Hyperf\Utils\Coroutine\Concurrent` 可以實現一個程式碼塊內限制同時最多執行的協程數量；

## 變更

- [#583](https://github.com/hyperf/hyperf/pull/583) 當 `BaseClient::start` 失敗時會丟擲 `Hyperf\GrpcClient\Exception\GrpcClientException` 異常；
- [#585](https://github.com/hyperf/hyperf/pull/585) 當投遞到 TaskWorker 執行的 Task 失敗時，會回傳異常到 Worker 程序中；

## 修復

- [#564](https://github.com/hyperf/hyperf/pull/564) 修復某些情況下 `Coroutine\Http2\Client->send` 返回值不正確的問題；
- [#567](https://github.com/hyperf/hyperf/pull/567) 修復當 JSON RPC 消費者配置 name 不是介面時，無法生成代理類的問題；
- [#571](https://github.com/hyperf/hyperf/pull/571) 修復 ExceptionHandler 的 `stopPropagation` 的協程變數汙染的問題；
- [#579](https://github.com/hyperf/hyperf/pull/579) 動態初始化 `snowflake`  的 MetaData，主要修復當在命令模式下使用 Snowflake 時，比如 `di:init-proxy` 命令，會連線到 Redis 伺服器至超時；

# v1.0.15 - 2019-09-11

## 修復

- [#534](https://github.com/hyperf/hyperf/pull/534) 修復 Guzzle HTTP 客戶端的 `CoroutineHanlder` 沒有處理狀態碼為 `-3` 的情況；
- [#541](https://github.com/hyperf/hyperf/pull/541) 修復 gRPC 客戶端的 `$client` 引數設定錯誤的問題；
- [#542](https://github.com/hyperf/hyperf/pull/542) 修復 `Hyperf\Grpc\Parser::parseResponse` 無法支援 gRPC 標準狀態碼的問題；
- [#551](https://github.com/hyperf/hyperf/pull/551) 修復當服務端關閉了 gRPC 連線時，gRPC 客戶端會殘留一個死迴圈的協程；
- [#558](https://github.com/hyperf/hyperf/pull/558) 修復 `UDP Server` 無法正確配置啟動的問題；

## 優化

- [#549](https://github.com/hyperf/hyperf/pull/549) 優化了 `Hyperf\Amqp\Connection\SwooleIO` 的 `read` 和 `write` 方法，減少不必要的重試；
- [#559](https://github.com/hyperf/hyperf/pull/559) 優化 `Hyperf\HttpServer\Response::redirect()` 方法，自動識別連結首位是否為斜槓併合理修正引數；
- [#560](https://github.com/hyperf/hyperf/pull/560) 優化 `Hyperf\WebSocketServer\CoreMiddleware`，移除了不必要的程式碼；

## 移除

- [#545](https://github.com/hyperf/hyperf/pull/545) 移除了 `Hyperf\Database\Model\SoftDeletes` 內無用的 `restoring` 和 `restored` 靜態方法； 

## 即將移除

- [#558](https://github.com/hyperf/hyperf/pull/558) 標記了 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量為 `棄用` 狀態，該常量將於 `v1.1` 移除，由更合理的 `Hyperf\Server\ServerInterface::SERVER_BASE` 常量替代；

# v1.0.14 - 2019-09-05

## 新增

- [#389](https://github.com/hyperf/hyperf/pull/389) [#419](https://github.com/hyperf/hyperf/pull/419) [#432](https://github.com/hyperf/hyperf/pull/432) [#524](https://github.com/hyperf/hyperf/pull/524) 新增 Snowflake 官方元件, Snowflake 是一個由 Twitter 提出的分散式全域性唯一 ID 生成演算法，[hyperf/snowflake](https://github.com/hyperf/snowflake) 元件實現了該演算法並設計得易於使用，同時在設計上提供了很好的可擴充套件性，可以很輕易的將該元件轉換成其它基於 Snowflake 演算法的變體演算法；
- [#525](https://github.com/hyperf/hyperf/pull/525) 為 `Hyperf\HttpServer\Contract\ResponseInterface` 增加一個 `download()` 方法，提供便捷的下載響應返回；

## 變更

- [#482](https://github.com/hyperf/hyperf/pull/482) 生成模型檔案時，當設定了 `refresh-fillable` 選項時重新生成模型的 `fillable` 屬性，同時該命令的預設情況下將不會再覆蓋生成 `fillable` 屬性；
- [#501](https://github.com/hyperf/hyperf/pull/501) 當 `Mapping` 註解的 `path` 屬性為一個空字串時，那麼該路由則為 `/prefix`；
- [#513](https://github.com/hyperf/hyperf/pull/513) 如果專案設定了 `app_name` 屬性，則程序名稱會自動帶上該名稱；
- [#508](https://github.com/hyperf/hyperf/pull/508) [#526](https://github.com/hyperf/hyperf/pull/526) 當在非協程環境下執行 `Hyperf\Utils\Coroutine::parentId()` 方法時會返回一個 `null` 值；

## 修復

- [#479](https://github.com/hyperf/hyperf/pull/479) 修復了當 Elasticsearch client 的 `host` 屬性設定有誤時，返回型別錯誤的問題；
- [#514](https://github.com/hyperf/hyperf/pull/514) 修復當 Redis 密碼配置為空字串時鑑權失敗的問題；
- [#527](https://github.com/hyperf/hyperf/pull/527) 修復 Translator 無法重複翻譯的問題；

# v1.0.13 - 2019-08-28

## 新增

- [#449](https://github.com/hyperf/hyperf/pull/428) 新增一個獨立元件 [hyperf/translation](https://github.com/hyperf/translation)， 衍生於 [illuminate/translation](https://github.com/illuminate/translation)；
- [#449](https://github.com/hyperf/hyperf/pull/449) 為 GRPC-Server 增加標準錯誤碼；
- [#450](https://github.com/hyperf/hyperf/pull/450) 為 `Hyperf\Database\Schema\Schema` 類的魔術方法增加對應的靜態方法註釋，為 IDE 提供程式碼提醒的支援；

## 變更

- [#451](https://github.com/hyperf/hyperf/pull/451) 在使用 `@AutoController` 註解時不再會自動為魔術方法生成對應的路由；
- [#468](https://github.com/hyperf/hyperf/pull/468) 讓 GRPC-Server 和 HTTP-Server 提供的異常處理器處理所有的異常，而不只是 `ServerException`；

## 修復 

- [#466](https://github.com/hyperf/hyperf/pull/466) 修復分頁時資料不足時返回型別錯誤的問題；
- [#466](https://github.com/hyperf/hyperf/pull/470) 優化了 `vendor:publish` 命令，當要生成的目標資料夾存在時，不再重複生成；

# v1.0.12 - 2019-08-21

## 新增

- [#405](https://github.com/hyperf/hyperf/pull/405) 增加 `Hyperf\Utils\Context::override()` 方法，現在你可以通過 `override` 方法獲取某些協程上下文的值並修改覆蓋它；
- [#415](https://github.com/hyperf/hyperf/pull/415) 對 Logger 的配置檔案增加多個 Handler 的配置支援；

## 變更

- [#431](https://github.com/hyperf/hyperf/pull/431) 移除了 `Hyperf\GrpcClient\GrpcClient::openStream()` 的第 3 個引數，這個引數不會影響實際使用；

## 修復

- [#414](https://github.com/hyperf/hyperf/pull/414) 修復 `Hyperf\WebSockerServer\Exception\Handler\WebSocketExceptionHandler` 內的變數名稱錯誤的問題；
- [#424](https://github.com/hyperf/hyperf/pull/424) 修復 Guzzle 在使用 `Hyperf\Guzzle\CoroutineHandler` 時配置 `proxy` 引數時不支援陣列傳值的問題；
- [#430](https://github.com/hyperf/hyperf/pull/430) 修復 `Hyperf\HttpServer\Request::file()` 當以一個 Name 上傳多個檔案時，返回格式不正確的問題；
- [#431](https://github.com/hyperf/hyperf/pull/431) 修復 GRPC Client 的 Request 物件在傳送 Force-Close 請求時缺少引數的問題；

# v1.0.11 - 2019-08-15

## 新增

- [#366](https://github.com/hyperf/hyperf/pull/366) 增加 `Hyperf\Server\Listener\InitProcessTitleListener` 監聽者來設定程序名稱， 同時增加了 `Hyperf\Framework\Event\OnStart` 和 `Hyperf\Framework\Event\OnManagerStart` 事件；

## 修復

- [#361](https://github.com/hyperf/hyperf/pull/361) 修復 `db:model`命令在 MySQL 8 下不能正常執行；
- [#369](https://github.com/hyperf/hyperf/pull/369) 修復實現 `\Serializable` 介面的自定義異常類不能正確的序列化和反序列化問題；
- [#384](https://github.com/hyperf/hyperf/pull/384) 修復使用者自定義的 `ExceptionHandler` 在 JSON RPC Server 下無法正常工作的問題，因為框架預設自動處理了對應的異常；
- [#370](https://github.com/hyperf/hyperf/pull/370) 修復了 `Hyperf\GrpcClient\BaseClient` 的 `$client` 屬性在流式傳輸的時候設定了錯誤的型別的值的問題, 同時增加了預設的 `content-type`  為 `application/grpc+proto`，以及允許使用者通過自定義 `Request` 物件來重寫 `buildRequest()` 方法；

## 變更

- [#356](https://github.com/hyperf/hyperf/pull/356) [#390](https://github.com/hyperf/hyperf/pull/390) 優化 aysnc-queue 元件當生成 Job 時，如果 Job 實現了 `Hyperf\Contract\CompressInterface`，那麼 Job 物件會被壓縮為一個更小的物件；
- [#358](https://github.com/hyperf/hyperf/pull/358) 只有當 `$enableCache` 為 `true` 時才生成註解快取檔案；
- [#359](https://github.com/hyperf/hyperf/pull/359) [#390](https://github.com/hyperf/hyperf/pull/390) 為 `Collection` 和 `Model` 增加壓縮能力，當類實現 `Hyperf\Contract\CompressInterface` 可通過 `compress` 方法生成一個更小的物件；

# v1.0.10 - 2019-08-09

## 新增

- [#321](https://github.com/hyperf/hyperf/pull/321) 為 HTTP Server 的 Controller/RequestHandler 引數增加自定義物件型別的陣列支援，特別適用於 JSON RPC 下，現在你可以通過在方法上定義 `@var Object[]` 來獲得框架自動反序列化對應物件的支援；
- [#324](https://github.com/hyperf/hyperf/pull/324) 增加一個實現於 `Hyperf\Contract\IdGeneratorInterface` 的 ID 生成器 `NodeRequestIdGenerator`；
- [#336](https://github.com/hyperf/hyperf/pull/336) 增加動態代理的 RPC 客戶端功能；
- [#346](https://github.com/hyperf/hyperf/pull/346) [#348](https://github.com/hyperf/hyperf/pull/348) 為 `hyperf/cache` 快取元件增加檔案驅動；

## 變更

- [#330](https://github.com/hyperf/hyperf/pull/330) 當掃描的 $paths 為空時，不輸出掃描資訊；
- [#328](https://github.com/hyperf/hyperf/pull/328) 根據 Composer 的 PSR-4 定義的規則載入業務專案；
- [#329](https://github.com/hyperf/hyperf/pull/329) 優化 JSON RPC 服務端和客戶端的異常訊息處理；
- [#340](https://github.com/hyperf/hyperf/pull/340) 為 `make` 函式增加索引陣列的傳參方式；
- [#349](https://github.com/hyperf/hyperf/pull/349) 重新命名下列類，修正由於拼寫錯誤導致的命名錯誤；

|                     原類名                      |                  修改後的類名                     |
|:----------------------------------------------|:-----------------------------------------------|
| Hyperf\\Database\\Commands\\Ast\\ModelUpdateVistor | Hyperf\\Database\\Commands\\Ast\\ModelUpdateVisitor |
|       Hyperf\\Di\\Aop\\ProxyClassNameVistor       |       Hyperf\\Di\\Aop\\ProxyClassNameVisitor       |
|         Hyperf\\Di\\Aop\\ProxyCallVistor          |         Hyperf\\Di\\Aop\\ProxyCallVisitor          |

## 修復

- [#325](https://github.com/hyperf/hyperf/pull/325) 優化 RPC 服務註冊時會多次呼叫 Consul Services 的問題；
- [#332](https://github.com/hyperf/hyperf/pull/332) 修復 `Hyperf\Tracer\Middleware\TraceMiddeware` 在新版的 openzipkin/zipkin 下的型別約束錯誤；
- [#333](https://github.com/hyperf/hyperf/pull/333) 修復 `Redis::delete()` 方法在 5.0 版不存在的問題；
- [#334](https://github.com/hyperf/hyperf/pull/334) 修復向阿里雲 ACM 配置中心拉取配置時，部分情況下部分配置無法更新的問題；
- [#337](https://github.com/hyperf/hyperf/pull/337) 修復當 Header 的 key 為非字串型別時，會返回 500 響應的問題；
- [#338](https://github.com/hyperf/hyperf/pull/338) 修復 `ProviderConfig::load` 在遇到重複 key 時會導致在深度合併時將字串轉換成陣列的問題；

# v1.0.9 - 2019-08-03

## 新增

- [#317](https://github.com/hyperf/hyperf/pull/317) 增加 `composer-json-fixer` 來優化 composer.json 檔案的內容；
- [#320](https://github.com/hyperf/hyperf/pull/320) DI 定義 Definition 時，允許 value 為一個匿名函式；

## 修復

- [#300](https://github.com/hyperf/hyperf/pull/300) 讓 AsyncQueue 的訊息於子協程內來進行處理，修復 `attempts` 引數與實際重試次數不一致的問題；
- [#305](https://github.com/hyperf/hyperf/pull/305) 修復 `Hyperf\Utils\Arr::set` 方法的 `$key` 引數不支援 `int` 個 `null` 的問題；
- [#312](https://github.com/hyperf/hyperf/pull/312) 修復 `Hyperf\Amqp\BeforeMainServerStartListener` 監聽器的優先順序錯誤的問題；
- [#315](https://github.com/hyperf/hyperf/pull/315) 修復 ETCD 配置中心在 Worker 程序重啟後或在自定義程序內無法使用問題；
- [#318](https://github.com/hyperf/hyperf/pull/318) 修復服務會持續註冊到服務中心的問題；

## 變更

- [#323](https://github.com/hyperf/hyperf/pull/323) 強制轉換 `Cacheable` 和 `CachePut` 註解的 `$ttl` 屬性為 `int` 型別；

# v1.0.8 - 2019-07-31

## 新增

- [#276](https://github.com/hyperf/hyperf/pull/276) AMQP 消費者支援配置及繫結多個 `routing_key`；
- [#277](https://github.com/hyperf/hyperf/pull/277) 增加 ETCD 客戶端元件及 ETCD 配置中心元件；

## 變更

- [#297](https://github.com/hyperf/hyperf/pull/297) 如果服務註冊失敗，會於 10 秒後重試註冊，且遮蔽了連線不上服務中心(Consul)而丟擲的異常；
- [#298](https://github.com/hyperf/hyperf/pull/298) [#301](https://github.com/hyperf/hyperf/pull/301) 適配 `openzipkin/zipkin` v1.3.3+ 版本；

## 修復

- [#271](https://github.com/hyperf/hyperf/pull/271) 修復了 AOP 在 `classes` 只會策略下配置同一個類的多個方法只會實現第一個方法的代理方法的問題；
- [#285](https://github.com/hyperf/hyperf/pull/285) 修復了 AOP 在匿名類下生成節點存在丟失的問題；
- [#286](https://github.com/hyperf/hyperf/pull/286) 自動 `rollback` 沒有 `commit` 或 `rollback` 的 MySQL 連線；
- [#292](https://github.com/hyperf/hyperf/pull/292) 修復了 `Request::header` 方法的 `$default` 引數無效的問題；
- [#293](https://github.com/hyperf/hyperf/pull/293) 修復了 `Arr::get` 方法的 `$key` 引數不支援 `int` and `null` 傳值的問題；

# v1.0.7 - 2019-07-26

## 修復

- [#266](https://github.com/hyperf/hyperf/pull/266) 修復投遞 AMQP 訊息時的超時邏輯；
- [#273](https://github.com/hyperf/hyperf/pull/273) 修復當有一個服務註冊到服務中心的時候所有服務會被移除的問題；
- [#274](https://github.com/hyperf/hyperf/pull/274) 修復檢視響應的 Content-Type ；

# v1.0.6 - 2019-07-24

## 新增

- [#203](https://github.com/hyperf/hyperf/pull/203) [#236](https://github.com/hyperf/hyperf/pull/236) [#247](https://github.com/hyperf/hyperf/pull/247) [#252](https://github.com/hyperf/hyperf/pull/252) 增加檢視元件，支援 Blade 引擎和 Smarty 引擎； 
- [#203](https://github.com/hyperf/hyperf/pull/203) 增加 Task 元件，適配 Swoole Task 機制；
- [#245](https://github.com/hyperf/hyperf/pull/245) 增加 TaskWorkerStrategy 和 WorkerStrategy 兩種定時任務排程策略.
- [#251](https://github.com/hyperf/hyperf/pull/251) 增加用協程上下文作為儲存的快取驅動；
- [#254](https://github.com/hyperf/hyperf/pull/254) 增加 `RequestMapping::$methods` 對陣列傳值的支援, 現在可以通過 `@RequestMapping(methods={"GET"})` 和 `@RequestMapping(methods={RequestMapping::GET})` 兩種新的方式定義方法；
- [#255](https://github.com/hyperf/hyperf/pull/255) 控制器返回 `Hyperf\Utils\Contracts\Arrayable` 會自動轉換為 Response 物件, 同時對返回字串的響應物件增加  `text/plain` Content-Type;
- [#256](https://github.com/hyperf/hyperf/pull/256) 如果 `Hyperf\Contract\IdGeneratorInterface` 存在容器繫結關係, 那麼 `json-rpc` 客戶端會根據該類自動生成一個請求 ID 並儲存在 Request attribute 裡，同時完善了 `JSON RPC` 在 TCP 協議下的服務註冊及健康檢查；

## 變更

- [#247](https://github.com/hyperf/hyperf/pull/247) 使用 `WorkerStrategy` 作為預設的計劃任務排程策略；
- [#256](https://github.com/hyperf/hyperf/pull/256) 優化 `JSON RPC` 的錯誤處理，現在當方法不存在時也會返回一個標準的 `JSON RPC` 錯誤物件；

## 修復

- [#235](https://github.com/hyperf/hyperf/pull/235) 為 `grpc-server` 增加了預設的錯誤處理器，防止錯誤丟擲.
- [#240](https://github.com/hyperf/hyperf/pull/240) 優化了 OnPipeMessage 事件的觸發，修復會被多個監聽器獲取錯誤資料的問題；
- [#257](https://github.com/hyperf/hyperf/pull/257) 修復了在某些環境下無法獲得內網 IP 的問題；

# v1.0.5 - 2019-07-17

## 新增

- [#185](https://github.com/hyperf/hyperf/pull/185) `響應(Response)` 增加 `xml` 格式支援；
- [#202](https://github.com/hyperf/hyperf/pull/202) 在協程內丟擲未捕獲的異常時，預設輸出異常的 trace 資訊；
- [#138](https://github.com/hyperf/hyperf/pull/138) [#197](https://github.com/hyperf/hyperf/pull/197) 增加秒級定時任務元件；

# 變更

- [#195](https://github.com/hyperf/hyperf/pull/195) 變更 `retry()` 函式的 `$times` 引數的行為意義, 表示重試的次數而不是執行的次數；
- [#198](https://github.com/hyperf/hyperf/pull/198) 優化 `Hyperf\Di\Container` 的 `has()` 方法, 當傳遞一個不可例項化的示例（如介面）至 `$container->has($interface)` 方法時，會返回 `false`；
- [#199](https://github.com/hyperf/hyperf/pull/199) 當生產 AMQP 訊息失敗時，會自動重試一次；
- [#200](https://github.com/hyperf/hyperf/pull/200) 通過 Git 打包專案的部署包時，不再包含 `tests` 資料夾；

## 修復

- [#176](https://github.com/hyperf/hyperf/pull/176) 修復 `LengthAwarePaginator::nextPageUrl()` 方法返回值的型別約束；
- [#188](https://github.com/hyperf/hyperf/pull/188) 修復 Guzzle Client 的代理設定不生效的問題；
- [#211](https://github.com/hyperf/hyperf/pull/211) 修復 RPC Client 存在多個時會被最後一個覆蓋的問題；
- [#212](https://github.com/hyperf/hyperf/pull/212) 修復 Guzzle Client 的 `ssl_key` 和 `cert` 配置項不能正常工作的問題；

# v1.0.4 - 2019-07-08

## 新增

- [#140](https://github.com/hyperf/hyperf/pull/140) 支援 Swoole v4.4.0.
- [#152](https://github.com/hyperf/hyperf/pull/152) 資料庫連線在低使用率時連線池會自動釋放連線
- [#163](https://github.com/hyperf/hyperf/pull/163) constants 元件的`AbstractConstants::__callStatic` 支援自定義引數

## 變更

- [#124](https://github.com/hyperf/hyperf/pull/124) `DriverInterface::push` 增加 `$delay` 引數用於設定延遲時間, 同時 `DriverInterface::delay` 將標記為棄用的，將於 1.1 版本移除 
- [#125](https://github.com/hyperf/hyperf/pull/125) 更改 `config()` 函式的 `$default` 引數的預設值為 `null`.

## 修復

- [#110](https://github.com/hyperf/hyperf/pull/110) [#111](https://github.com/hyperf/hyperf/pull/111) 修復 `Redis::select` 無法正常切換資料庫的問題
- [#131](https://github.com/hyperf/hyperf/pull/131) 修復 `middlewares` 配置在 `Router::addGroup` 下無法正常設定的問題
- [#132](https://github.com/hyperf/hyperf/pull/132) 修復 `request->hasFile` 判斷條件錯誤的問題
- [#135](https://github.com/hyperf/hyperf/pull/135) 修復 `response->redirect` 在調整外鏈時無法正確生成連結的問題
- [#139](https://github.com/hyperf/hyperf/pull/139) 修復 ConsulAgent 的 URI 無法自定義設定的問題
- [#148](https://github.com/hyperf/hyperf/pull/148) 修復當 `migrates` 資料夾不存在時無法生成遷移模板的問題
- [#169](https://github.com/hyperf/hyperf/pull/169) 修復處理請求時沒法正確處理陣列型別的引數
- [#170](https://github.com/hyperf/hyperf/pull/170) 修復當路由不存在時 WebSocket Server 無法正確捕獲異常的問題

## 移除

- [#131](https://github.com/hyperf/hyperf/pull/131) 移除 `Router` `options` 裡的 `server` 引數

# v1.0.3 - 2019-07-02

## 新增

- [#48](https://github.com/hyperf/hyperf/pull/48) 增加 WebSocket 協程客戶端及服務端
- [#51](https://github.com/hyperf/hyperf/pull/51) 增加了 `enableCache` 引數去控制 `DefinitionSource` 是否啟用註解掃描快取 
- [#61](https://github.com/hyperf/hyperf/pull/61) 通過 `db:model` 命令建立模型時增加屬性型別
- [#65](https://github.com/hyperf/hyperf/pull/65) 模型快取增加 JSON 格式支援

## 變更

- [#46](https://github.com/hyperf/hyperf/pull/46) 移除了 `hyperf/di`, `hyperf/command` and `hyperf/dispatcher` 元件對 `hyperf/framework` 元件的依賴

## 修復

- [#45](https://github.com/hyperf/hyperf/pull/55) 修復當引用了 `hyperf/websocket-server` 元件時有可能會導致 HTTP Server 啟動失敗的問題
- [#55](https://github.com/hyperf/hyperf/pull/55) 修復方法級別的 `@Middleware` 註解可能會被覆蓋的問題
- [#73](https://github.com/hyperf/hyperf/pull/73) 修復 `db:model` 命令對短屬性處理不正確的問題
- [#88](https://github.com/hyperf/hyperf/pull/88) 修復當控制器存在多層資料夾時生成的路由可能不正確的問題
- [#101](https://github.com/hyperf/hyperf/pull/101) 修復常量不存在 `@Message` 註解時會報錯的問題

# v1.0.2 - 2019-06-25

## 新增

- 接入 Travis CI，目前 Hyperf 共存在 426 個單測，1124 個斷言； [#25](https://github.com/hyperf/hyperf/pull/25)
- 完善了對 `Redis::connect` 方法的引數支援； [#29](https://github.com/hyperf/hyperf/pull/29)

## 修復

- 修復了 HTTP Server 會被 WebSocket Server 影響的問題（WebSocket Server 尚未釋出）；
- 修復了代理類部分註解沒有生成的問題；
- 修復了資料庫連線池在單測環境下會無法獲取連線的問題；
- 修復了 co-phpunit 在某些情況下不能按預期執行的問題；
- 修復了模型事件 `creating`, `updating` ... 執行與預期不一致的問題；
- 修復了 `flushContext` 方法在單測環境下不能按預期執行的問題；
