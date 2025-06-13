# 版本更新記錄

# v1.0.16 - 2019-09-20

## 新增

- [#565](https://github.com/hyperf/hyperf/pull/565) 增加對  Redis 客户端的 `options` 配置參數支持；
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
- [#558](https://github.com/hyperf/hyperf/pull/558) 修復 `UDP Server` 無法正確配置啓動的問題；

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
- [#315](https://github.com/hyperf/hyperf/pull/315) 修復 ETCD 配置中心在 Worker 進程重啓後或在自定義進程內無法使用問題；
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
- [#51](https://github.com/hyperf/hyperf/pull/51) 增加了 `enableCache` 參數去控制 `DefinitionSource` 是否啓用註解掃描緩存
- [#61](https://github.com/hyperf/hyperf/pull/61) 通過 `db:model` 命令創建模型時增加屬性類型
- [#65](https://github.com/hyperf/hyperf/pull/65) 模型緩存增加 JSON 格式支持

## 變更

- [#46](https://github.com/hyperf/hyperf/pull/46) 移除了 `hyperf/di`, `hyperf/command` and `hyperf/dispatcher` 組件對 `hyperf/framework` 組件的依賴

## 修復

- [#45](https://github.com/hyperf/hyperf/pull/55) 修復當引用了 `hyperf/websocket-server` 組件時有可能會導致 HTTP Server 啓動失敗的問題
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
