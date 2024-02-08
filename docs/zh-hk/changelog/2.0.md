# 版本更新記錄

# v2.0.25 - 2020-12-28

## 新增

- [#3015](https://github.com/hyperf/hyperf/pull/3015) 為 `socketio-server` 增加了可以自動清理垃圾的機制。
- [#3030](https://github.com/hyperf/hyperf/pull/3030) 新增了方法 `ProceedingJoinPoint::getInstance()`，可以允許在使用 `AOP` 時，拿到被切入的實例。

## 優化

- [#3011](https://github.com/hyperf/hyperf/pull/3011) 優化 `hyperf/tracer` 組件，可以在鏈路追蹤中記錄異常信息。

# v2.0.24 - 2020-12-21

## 修復

- [#2978](https://github.com/hyperf/hyperf/pull/2980) 修復當沒有引用 `hyperf/contract` 時，`hyperf/snowflake` 組件會無法正常使用的問題。
- [#2983](https://github.com/hyperf/hyperf/pull/2983) 修復使用協程風格服務時，常量 `SWOOLE_HOOK_FLAGS` 無法生效的問題。
- [#2993](https://github.com/hyperf/hyperf/pull/2993) 修復方法 `Arr::merge()` 入參 `$array1` 為空時，會將關聯數組，錯誤的轉化為索引數組的問題。

## 優化

- [#2973](https://github.com/hyperf/hyperf/pull/2973) 支持自定義的 `HTTP` 狀態碼。
- [#2992](https://github.com/hyperf/hyperf/pull/2992) 優化組件 `hyperf/validation` 的依賴關係，移除 `hyperf/devtool` 組件。

# v2.0.23 - 2020-12-14

## 新增

- [#2872](https://github.com/hyperf/hyperf/pull/2872) 新增 `hyperf/phar` 組件，用於將 `Hyperf` 項目打包成 `phar`。

## 修復

- [#2952](https://github.com/hyperf/hyperf/pull/2952) 修復 `Nacos` 配置中心，在協程風格服務中無法正常使用的問題。

## 變更

- [#2934](https://github.com/hyperf/hyperf/pull/2934) 變更配置文件 `scout.php`，默認使用 `Elasticsearch` 索引作為模型索引。
- [#2958](https://github.com/hyperf/hyperf/pull/2958) 變更 `view` 組件默認的渲染引擎為 `NoneEngine`。

## 優化

- [#2951](https://github.com/hyperf/hyperf/pull/2951) 優化 `model-cache` 組件，使其執行完多次事務後，只會刪除一次緩存。
- [#2953](https://github.com/hyperf/hyperf/pull/2953) 隱藏命令行因執行 `exit` 導致的異常 `Swoole\ExitException`。
- [#2963](https://github.com/hyperf/hyperf/pull/2963) 當異步風格服務使用 `SWOOLE_BASE` 時，會從默認的事件回調中移除 `onStart` 事件。

# v2.0.22 - 2020-12-07

## 新增

- [#2896](https://github.com/hyperf/hyperf/pull/2896) 允許 `view-engine` 組件配置自定義加載類組件和匿名組件。
- [#2921](https://github.com/hyperf/hyperf/pull/2921) 為 `Parallel` 增加 `count()` 方法，返回同時執行的個數。

## 修復

- [#2913](https://github.com/hyperf/hyperf/pull/2913) 修復使用 `ORM` 中的 `with` 預加載邏輯時，會因循環依賴導致內存泄露的問題。
- [#2915](https://github.com/hyperf/hyperf/pull/2915) 修復 `WebSocket` 工作進程會因 `onMessage` or `onClose` 回調失敗，導致進程退出的問題。
- [#2927](https://github.com/hyperf/hyperf/pull/2927) 修復驗證器規則 `alpha_dash` 不支持 `int` 的問題。

## 變更

- [#2918](https://github.com/hyperf/hyperf/pull/2918) 當使用 `watcher` 組件時，不可以開啓 `daemonize`。
- [#2930](https://github.com/hyperf/hyperf/pull/2930) 更新 `php-amqplib` 組件最低版本由 `v2.7` 到 `v2.9.2`。

## 優化

- [#2931](https://github.com/hyperf/hyperf/pull/2931) 判斷控制器方法是否存在時，使用實際從容器中得到的對象，而非命名空間。

# v2.0.21 - 2020-11-30

## 新增

- [#2857](https://github.com/hyperf/hyperf/pull/2857) 為 `service-governance` 組件新增 `Consul` 的 `ACL Token` 支持。
- [#2870](https://github.com/hyperf/hyperf/pull/2870) 為腳本 `vendor:publish` 支持發佈配置目錄的能力。
- [#2875](https://github.com/hyperf/hyperf/pull/2875) 為 `watcher` 組件新增可選項 `no-restart`，允許動態修改註解緩存，但不重啓服務。
- [#2883](https://github.com/hyperf/hyperf/pull/2883) 為 `scout` 組件數據導入腳本，增加可選項 `--chunk` 和 `--column|c`，允許用户指定任一字段，進行數據插入，解決偏移量過大導致查詢效率慢的問題。
- [#2891](https://github.com/hyperf/hyperf/pull/2891) 為 `crontab` 組件新增可用於發佈的配置文件。

## 修復

- [#2874](https://github.com/hyperf/hyperf/pull/2874) 修復在使用 `watcher` 組件時， `scan.ignore_annotations` 配置不生效的問題。
- [#2878](https://github.com/hyperf/hyperf/pull/2878) 修復 `nsq` 組件中，`nsqd` 配置無法正常工作的問題。

## 變更

- [#2851](https://github.com/hyperf/hyperf/pull/2851) 修改 `view` 組件默認的配置文件，使用 `view-engine` 引擎，而非第三方 `blade` 引擎。

## 優化

- [#2785](https://github.com/hyperf/hyperf/pull/2785) 優化 `watcher` 組件，使其異常信息更加人性化。
- [#2861](https://github.com/hyperf/hyperf/pull/2861) 優化 `Guzzle Coroutine Handler`，當其 `statusCode` 小於 `0` 時，拋出對應異常。
- [#2868](https://github.com/hyperf/hyperf/pull/2868) 優化 `Guzzle` 的 `sink` 配置，使其支持傳入 `resource`。

# v2.0.20 - 2020-11-23

## 新增

- [#2824](https://github.com/hyperf/hyperf/pull/2824) 為 `Hyperf\Database\Query\Builder` 增加方法 `simplePaginate()`。

## 修復

- [#2820](https://github.com/hyperf/hyperf/pull/2820) 修復使用 `fanout` 交換機時，`AMQP` 消費者無法正常工作的問題。
- [#2831](https://github.com/hyperf/hyperf/pull/2831) 修復 `AMQP` 連接會被客户端意外關閉的問題。
- [#2848](https://github.com/hyperf/hyperf/pull/2848) 修復在 `defer` 中使用數據庫組件時，會導致數據庫連接會同時被其他協程綁定的問題。

## 變更

- [#2824](https://github.com/hyperf/hyperf/pull/2824) 修改 `Hyperf\Database\Query\Builder` 方法 `paginate()` 返回值類型，由 `PaginatorInterface` 變更為 `LengthAwarePaginatorInterface`。

## 優化

- [#2766](https://github.com/hyperf/hyperf/pull/2766) 優化 `Tracer` 組件，在拋出異常的情況下，也可以執行 `finish` 方法，記錄鏈路。
- [#2805](https://github.com/hyperf/hyperf/pull/2805) 優化 `Nacos` 進程，可以安全停止。
- [#2821](https://github.com/hyperf/hyperf/pull/2821) 優化工具類 `Json` 和 `Xml`，使其拋出一致的異常。
- [#2827](https://github.com/hyperf/hyperf/pull/2827) 優化 `Hyperf\Server\ServerConfig`，解決方法 `__set` 因返回值不為 `void`，導致不兼容 `PHP8` 的問題。
- [#2839](https://github.com/hyperf/hyperf/pull/2839) 優化 `Hyperf\Database\Schema\ColumnDefinition` 的註釋。

# v2.0.19 - 2020-11-17

## 新增

- [#2794](https://github.com/hyperf/hyperf/pull/2794) [#2802](https://github.com/hyperf/hyperf/pull/2802) 為 `Session` 組件新增配置項 `options.cookie_lifetime`, 允許用户自己設置 `Cookies` 的超時時間。

## 修復

- [#2783](https://github.com/hyperf/hyperf/pull/2783) 修復 `NSQ` 消費者無法在協程風格下正常使用的問題。
- [#2788](https://github.com/hyperf/hyperf/pull/2788) 修復非靜態方法 `__handlePropertyHandler()` 在代理類中，被靜態調用的問題。
- [#2790](https://github.com/hyperf/hyperf/pull/2790) 修復 `ETCD` 配置中心，`BootProcessListener` 監聽器無法在協程風格下正常使用的問題。
- [#2803](https://github.com/hyperf/hyperf/pull/2803) 修復當 `Request` 無法實例化時，`HTTP` 響應數據被清除的問題。
- [#2807](https://github.com/hyperf/hyperf/pull/2807) 修復當存在重複的中間件時，中間件的表現會與預期不符的問題。

## 優化

- [#2750](https://github.com/hyperf/hyperf/pull/2750) 優化 `Scout` 組件，當沒有配置搜索引擎 `index` 或 `Elasticsearch` 版本高於 `7.0` 時，使用 `index` 而非 `type` 作為模型的搜索條件。

# v2.0.18 - 2020-11-09

## 新增

- [#2752](https://github.com/hyperf/hyperf/pull/2752) 為註解 `@AutoController` `@Controller` 和 `@Mapping` 添加 `options` 參數，用於設置路由元數據。

## 修復

- [#2768](https://github.com/hyperf/hyperf/pull/2768) 修復 `WebSocket` 握手失敗時導致內存泄露的問題。
- [#2777](https://github.com/hyperf/hyperf/pull/2777) 修復低版本 `redis` 擴展，`RedisCluster` 構造函數 `$auth` 不支持 `null`，導致報錯的問題。
- [#2779](https://github.com/hyperf/hyperf/pull/2779) 修復因沒有設置 `translation` 配置文件導致服務啓動失敗的問題。

## 變更

- [#2765](https://github.com/hyperf/hyperf/pull/2765) 變更 `Concurrent` 類中創建協程邏輯，由方法 `Hyperf\Utils\Coroutine::create()` 代替原來的 `Swoole\Coroutine::create()`。

## 優化

- [#2347](https://github.com/hyperf/hyperf/pull/2347) 為 `AMQP` 的 `ConsumerMessage` 增加參數 `$waitTimeout`，用於在協程風格服務中，安全停止服務。

# v2.0.17 - 2020-11-02

## 新增

- [#2625](https://github.com/hyperf/hyperf/pull/2625) 新增 `Hyperf\Tracer\Aspect\JsonRpcAspect`, 可以讓 `Tracer` 組件支持 `JsonRPC` 的鏈路追蹤。
- [#2709](https://github.com/hyperf/hyperf/pull/2709) [#2733](https://github.com/hyperf/hyperf/pull/2733) 為 `Model` 新增了對應的 `@mixin` 註釋，提升模型的靜態方法提示能力。
- [#2726](https://github.com/hyperf/hyperf/pull/2726) [#2733](https://github.com/hyperf/hyperf/pull/2733) 為 `gen:model` 腳本增加可選項 `--with-ide`, 可以生成對應的 `IDE` 文件。
- [#2737](https://github.com/hyperf/hyperf/pull/2737) 新增 [view-engine](https://github.com/hyperf/view-engine) 組件，可以不需要在 `Task` 進程中渲染頁面。

## 修復

- [#2719](https://github.com/hyperf/hyperf/pull/2719) 修復 `Arr::merge` 會因 `array1` 中不包含 `array2` 中存在的 `$key` 時，導致的報錯問題。
- [#2723](https://github.com/hyperf/hyperf/pull/2723) 修復 `Paginator::resolveCurrentPath` 無法正常工作的問題。

## 優化

- [#2746](https://github.com/hyperf/hyperf/pull/2746) 優化 `@Task` 註解，只會在 `worker` 進程中執行時，會投遞到 `task` 進程執行對應邏輯，其他進程則會降級為同步執行。

## 變更

- [#2728](https://github.com/hyperf/hyperf/pull/2728) `JsonRPC` 中，以 `__` 為前綴的方法，都不會在註冊到 `RPC` 服務中，例如 `__construct`, '__call'。

# v2.0.16 - 2020-10-26

## 新增

- [#2682](https://github.com/hyperf/hyperf/pull/2682) 為 `CacheableInterface` 新增方法 `getCacheTTL` 可根據不同模型設置不同的緩存時間。
- [#2696](https://github.com/hyperf/hyperf/pull/2696) 新增 Swoole Tracker 的內存檢測工具。

## 修復

- [#2680](https://github.com/hyperf/hyperf/pull/2680) 修復 `CastsValue` 因為沒有設置 `$isSynchronized` 默認值，導致的類型錯誤。
- [#2680](https://github.com/hyperf/hyperf/pull/2680) 修復 `CastsValue` 中 `$items` 默認值會被 `__construct` 覆蓋的問題。
- [#2693](https://github.com/hyperf/hyperf/pull/2693) 修復 `hyperf/retry` 組件，`Budget` 表現不符合期望的問題。
- [#2695](https://github.com/hyperf/hyperf/pull/2695) 修復方法 `Container::define()` 因為容器中的對象已被實例化，而無法重定義的問題。

## 優化

- [#2611](https://github.com/hyperf/hyperf/pull/2611) 優化 `hyperf/watcher` 組件 `FindDriver` ，使其可以在 `Alpine` 鏡像中使用。
- [#2662](https://github.com/hyperf/hyperf/pull/2662) 優化 `Amqp` 消費者進程，使其可以配合 `Signal` 組件安全停止。
- [#2690](https://github.com/hyperf/hyperf/pull/2690) 優化 `hyperf/tracer` 組件，確保其可以正常執行 `finish` 和 `flush` 方法。

# v2.0.15 - 2020-10-19

## 新增

- [#2654](https://github.com/hyperf/hyperf/pull/2654) 新增方法 `Hyperf\Utils\Resource::from`，可以方便的將 `string` 轉化為 `resource`。

## 修復

- [#2634](https://github.com/hyperf/hyperf/pull/2634) [#2640](https://github.com/hyperf/hyperf/pull/2640) 修復 `snowflake` 組件中，元數據生成器 `RedisSecondMetaGenerator` 會產生相同元數據的問題。
- [#2639](https://github.com/hyperf/hyperf/pull/2639) 修復 `json-rpc` 組件中，異常無法正常被序列化的問題。
- [#2643](https://github.com/hyperf/hyperf/pull/2643) 修復 `scout:flush` 執行失敗的問題。

## 優化

- [#2656](https://github.com/hyperf/hyperf/pull/2656) 優化了 `json-rpc` 組件中，參數解析失敗後，也可以返回對應的錯誤信息。

# v2.0.14 - 2020-10-12

## 新增

- [#1172](https://github.com/hyperf/hyperf/pull/1172) 新增基於 `laravel/scout` 實現的組件 `hyperf/scout`, 可以通過搜索引擎進行模型查詢。
- [#1868](https://github.com/hyperf/hyperf/pull/1868) 新增 `Redis` 組件的哨兵模式。
- [#1969](https://github.com/hyperf/hyperf/pull/1969) 新增組件 `hyperf/resource` and `hyperf/resource-grpc`，可以更加方便的將模型轉化為 Response。

## 修復

- [#2594](https://github.com/hyperf/hyperf/pull/2594) 修復 `hyperf/crontab` 組件因為無法正常響應 `hyperf/signal`，導致無法停止的問題。
- [#2601](https://github.com/hyperf/hyperf/pull/2601) 修復命令 `gen:model` 因為 `getter` 和 `setter` 同時存在時，註釋 `@property` 會被 `@property-read` 覆蓋的問題。
- [#2607](https://github.com/hyperf/hyperf/pull/2607) [#2637](https://github.com/hyperf/hyperf/pull/2637) 修復使用 `RetryAnnotationAspect` 時，會有一定程度內存泄露的問題。
- [#2624](https://github.com/hyperf/hyperf/pull/2624) 修復組件 `hyperf/testing` 因使用了 `guzzle 7.0` 和 `CURL HOOK` 導致無法正常工作的問題。
- [#2632](https://github.com/hyperf/hyperf/pull/2632) [#2635](https://github.com/hyperf/hyperf/pull/2635) 修復 `hyperf\redis` 組件集羣模式，無法設置密碼的問題。

## 優化

- [#2603](https://github.com/hyperf/hyperf/pull/2603) 允許 `hyperf/database` 組件，`whereNull` 方法接受 `array` 作為入參。

# v2.0.13 - 2020-09-28

## 新增

- [#2445](https://github.com/hyperf/hyperf/pull/2445) 當使用異常捕獲器 `WhoopsExceptionHandler` 返回 `JSON` 格式化的數據時，自動添加異常的 `Trace` 信息。
- [#2580](https://github.com/hyperf/hyperf/pull/2580) 新增 `grpc-client` 組件的 `metadata` 支持。

## 修復

- [#2559](https://github.com/hyperf/hyperf/pull/2559) 修復使用 `socket-io` 連接 `socketio-server` 時，因為攜帶 `query` 信息，導致事件無法被觸發的問題。
- [#2565](https://github.com/hyperf/hyperf/pull/2565) 修復生成代理類時，因為存在匿名類，導致代理類在沒有父類的情況下使用了 `parent::class` 而報錯的問題。
- [#2578](https://github.com/hyperf/hyperf/pull/2578) 修復當自定義進程拋錯後，事件 `AfterProcessHandle` 無法被觸發的問題。
- [#2582](https://github.com/hyperf/hyperf/pull/2582) 修復使用 `Redis::multi` 且在 `defer` 中使用了其他 `Redis` 指令後，導致 `Redis` 同時被兩個協程使用而報錯的問題。
- [#2589](https://github.com/hyperf/hyperf/pull/2589) 修復使用了協程風格服務時，`AMQP` 消費者無法正常啓動的問題。
- [#2590](https://github.com/hyperf/hyperf/pull/2590) 修復使用了協程風格服務時，`Crontab` 無法正常工作的問題。

## 優化

- [#2561](https://github.com/hyperf/hyperf/pull/2561) 優化關閉 `AMQP` 連接失敗時的錯誤信息。
- [#2584](https://github.com/hyperf/hyperf/pull/2584) 當服務關閉時，不再刪除 `Nacos` 中對應的服務。

# v2.0.12 - 2020-09-21

## 新增

- [#2512](https://github.com/hyperf/hyperf/pull/2512) 為 [hyperf/database](https://github.com/hyperf/database) 組件方法 `MySqlGrammar::compileColumnListing` 新增返回字段 `column_type`。

## 修復

- [#2490](https://github.com/hyperf/hyperf/pull/2490) 修復 [hyperf/grpc-client](https://github.com/hyperf/grpc-client) 組件中，流式客户端無法正常工作的問題。
- [#2509](https://github.com/hyperf/hyperf/pull/2509) 修復 [hyperf/database](https://github.com/hyperf/database) 組件中，使用小駝峯模式後，訪問器無法正常工作的問題。
- [#2535](https://github.com/hyperf/hyperf/pull/2535) 修復 [hyperf/database](https://github.com/hyperf/database) 組件中，使用 `gen:model` 後，通過訪問器生成的註釋 `@property` 會被 `morphTo` 覆蓋的問題。
- [#2546](https://github.com/hyperf/hyperf/pull/2546) 修復 [hyperf/db-connection](https://github.com/hyperf/db-connection) 組件中，使用 `left join` 等複雜查詢後，`MySQL` 連接無法正常釋放的問題。

## 優化

- [#2490](https://github.com/hyperf/hyperf/pull/2490) 優化 [hyperf/grpc-client](https://github.com/hyperf/grpc-client) 組件中的異常和單元測試。

# v2.0.11 - 2020-09-14

## 新增

- [#2455](https://github.com/hyperf/hyperf/pull/2455) 為 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 組件新增方法 `Socket::getRequest` 用於獲取 `Psr7` 規範的 `Request`。
- [#2459](https://github.com/hyperf/hyperf/pull/2459) 為 [hyperf/async-queue](https://github.com/hyperf/async-queue) 組件新增監聽器 `ReloadChannelListener` 用於自動將超時隊列裏的消息移動到等待執行隊列中。
- [#2463](https://github.com/hyperf/hyperf/pull/2463) 為 [hyperf/database](https://github.com/hyperf/database) 組件新增可選的 `ModelRewriteGetterSetterVisitor` 用於為模型生成對應的 `Getter` 和 `Setter`。
- [#2475](https://github.com/hyperf/hyperf/pull/2475) 為 [hyperf/retry](https://github.com/hyperf/retry) 組件的 `Fallback` 回調，默認增加 `throwable` 參數。

## 修復

- [#2464](https://github.com/hyperf/hyperf/pull/2464) 修復 [hyperf/database](https://github.com/hyperf/database) 組件中，小駝峯模式模型的 `fill` 方法無法正常使用的問題。
- [#2478](https://github.com/hyperf/hyperf/pull/2478) 修復 [hyperf/websocket-server](https://github.com/hyperf/websocket-server) 組件中，`Sender::check` 無法檢測非 `WebSocket` 的 `fd` 值。
- [#2488](https://github.com/hyperf/hyperf/pull/2488) 修復 [hyperf/database](https://github.com/hyperf/database) 組件中，當 `pdo` 實例化失敗後 `beginTransaction` 調用失敗的問題。

## 優化

- [#2461](https://github.com/hyperf/hyperf/pull/2461) 優化 [hyperf/reactive-x](https://github.com/hyperf/reactive-x) 組件 `HTTP` 路由監聽器，可以監聽任意端口路由。
- [#2465](https://github.com/hyperf/hyperf/pull/2465) 優化 [hyperf/retry](https://github.com/hyperf/retry) 組件 `FallbackRetryPolicy` 中 `fallback` 除了可以填寫被 `is_callable` 識別的代碼外，還可以填寫形如 `class@method` 的格式，框架會從 `Container` 中拿到對應的 `class`，然後執行其 `method` 方法。

## 變更

- [#2492](https://github.com/hyperf/hyperf/pull/2492) 調整 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 組件中的事件收集順序，確保 `sid` 早於自定義 `onConnect` 被添加到房間中。

# v2.0.10 - 2020-09-07

## 新增

- [#2411](https://github.com/hyperf/hyperf/pull/2411) 為 [hyperf/database](https://github.com/hyperf/database) 組件新增 `Hyperf\Database\Query\Builder::forPageBeforeId` 方法。
- [#2420](https://github.com/hyperf/hyperf/pull/2420) [#2426](https://github.com/hyperf/hyperf/pull/2426) 為 [hyperf/command](https://github.com/hyperf/command) 組件新增默認選項 `enable-event-dispatcher` 用於初始化事件觸發器。
- [#2433](https://github.com/hyperf/hyperf/pull/2433) 為 [hyperf/grpc-server](https://github.com/hyperf/grpc-server) 組件路由新增匿名函數支持。
- [#2441](https://github.com/hyperf/hyperf/pull/2441) 為 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 組件中 `SocketIO` 新增了一些 `setters`。

## 修復

- [#2427](https://github.com/hyperf/hyperf/pull/2427) 修復事件觸發器在使用 `Pivot` 或 `MorphPivot` 不生效的問題。
- [#2443](https://github.com/hyperf/hyperf/pull/2443) 修復使用 [hyperf/Guzzle](https://github.com/hyperf/guzzle) 組件的 `Coroutine Handler` 時，無法正確獲取和傳遞 `traceid` 和 `spanid` 的問題。
- [#2449](https://github.com/hyperf/hyperf/pull/2449) 修復發佈 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) 組件的配置文件時，配置文件名稱錯誤的問題。

## 優化

- [#2429](https://github.com/hyperf/hyperf/pull/2429) 優化使用 `@Inject` 並且沒有設置 `@var` 時的錯誤信息，方便定位問題，改善編程體驗。
- [#2438](https://github.com/hyperf/hyperf/pull/2438) 優化當使用 [hyperf/model-cache](https://github.com/hyperf/model-cache) 組件與數據庫事務搭配使用時，在事務中刪除或修改模型數據會在事務提交後即時再刪除緩存，而不再是在刪除或修改模型數據時刪除緩存數據。

# v2.0.9 - 2020-08-31

## 新增

- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 組件增加授權接口。
- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 組件增加 `nacos.enable` 配置，用於控制是否啓用 `Nacos` 服務。
- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 組件增加配置合併類型，默認使用全量覆蓋。
- [#2377](https://github.com/hyperf/hyperf/pull/2377) 為 gRPC 客户端 的 request 增加 `ts` 請求頭，以兼容 Node.js gRPC server 等。
- [#2384](https://github.com/hyperf/hyperf/pull/2384) 新增助手函數 `optional()`，以創建 `Hyperf\Utils\Optional` 對象或更方便 Optional 的使用。

## 修改

- [#2331](https://github.com/hyperf/hyperf/pull/2331) 修復 [hyperf/nacos](https://github.com/hyperf/nacos) 組件，服務或配置不存在時，會拋出異常的問題。
- [#2356](https://github.com/hyperf/hyperf/pull/2356) [#2368](https://github.com/hyperf/hyperf/pull/2368) 修復 `pid_file` 被用户修改後，命令行 `server:start` 啓動失敗的問題。
- [#2358](https://github.com/hyperf/hyperf/pull/2358) 修復驗證器規則 `digits` 不支持 `int` 類型的問題。

## 優化

- [#2359](https://github.com/hyperf/hyperf/pull/2359) 優化自定義進程，在協程風格服務下，可以更加友好的停止。
- [#2363](https://github.com/hyperf/hyperf/pull/2363) 優化 [hyperf/di](https://github.com/hyperf/di) 組件，使其不需要依賴 [hyperf/config](https://github.com/hyperf/config) 組件。
- [#2373](https://github.com/hyperf/hyperf/pull/2373) 優化 [hyperf/validation](https://github.com/hyperf/validation) 組件的異常捕獲器，使其返回 `Response` 時，自動添加 `content-type` 頭。


# v2.0.8 - 2020-08-24

## 新增

- [#2334](https://github.com/hyperf/hyperf/pull/2334) 新增更加友好的數組遞歸合併方法 `Arr::merge`。
- [#2335](https://github.com/hyperf/hyperf/pull/2335) 新增 `Hyperf/Utils/Optional`，它可以接受任意參數，並允許訪問該對象上的屬性或調用其方法，即使給定的對象為 `null`，也不會引發錯誤。
- [#2336](https://github.com/hyperf/hyperf/pull/2336) 新增 `RedisNsqAdapter`，它通過 `NSQ` 發佈消息，使用 `Redis` 記錄房間信息。

## 修復

- [#2338](https://github.com/hyperf/hyperf/pull/2338) 修復文件系統使用 `S3` 適配器時，文件是否存在的邏輯與預期不符的 BUG。
- [#2340](https://github.com/hyperf/hyperf/pull/2340) 修復 `__FUNCTION__` 和 `__METHOD__` 魔術方法無法在被 `AOP` 重寫的方法里正常工作的 BUG。

## 優化

- [#2319](https://github.com/hyperf/hyperf/pull/2319) 優化 `ResolverDispatcher` ，使項目發生循環依賴時，可以提供更加友好的錯誤提示。

# v2.0.7 - 2020-08-17

## 新增

- [#2307](https://github.com/hyperf/hyperf/pull/2307) [#2312](https://github.com/hyperf/hyperf/pull/2312) [hyperf/nsq](https://github.com/hyperf/nsq) 組件，新增 `NSQD` 的 `HTTP` 客户端。

## 修復

- [#2275](https://github.com/hyperf/hyperf/pull/2275) 修復配置中心，拉取配置進程會出現阻塞的 BUG。
- [#2276](https://github.com/hyperf/hyperf/pull/2276) 修復 `Apollo` 配置中心，當配置沒有變更時，會清除所有本地配置項的 BUG。
- [#2280](https://github.com/hyperf/hyperf/pull/2280) 修復 `Interface` 的方法會被 `AOP` 重寫，導致啓動報錯的 BUG。
- [#2281](https://github.com/hyperf/hyperf/pull/2281) 當使用 `Task` 組件，且沒有啓動協程時，`Signal` 組件會導致啓動報錯的 BUG。
- [#2304](https://github.com/hyperf/hyperf/pull/2304) 修復當使用 `SocketIOServer` 的內存適配器，刪除 `sid` 時，會導致死循環的 BUG。
- [#2309](https://github.com/hyperf/hyperf/pull/2309) 修復 `JsonRpcHttpTransporter` 無法設置自定義超時時間的 BUG。

# v2.0.6 - 2020-08-10

## 新增

- [#2125](https://github.com/hyperf/hyperf/pull/2125) 新增 [hyperf/jet](https://github.com/hyperf/jet) 組件。`Jet` 是一個統一模型的 RPC 客户端，內置 JSONRPC 協議的適配，該組件可適用於所有的 `PHP (>= 7.2)` 環境，包括 PHP-FPM 和 Swoole 或 Hyperf。

## 修復

- [#2236](https://github.com/hyperf/hyperf/pull/2236) 修復 `Nacos` 使用負載均衡器選擇節點失敗的 BUG。
- [#2242](https://github.com/hyperf/hyperf/pull/2242) 修復 `watcher` 組件會重複收集多次註解的 BUG。

# v2.0.5 - 2020-08-03

## 新增

- [#2001](https://github.com/hyperf/hyperf/pull/2001) 新增參數 `$signature`，用於簡化命令行的初始化工作。
- [#2204](https://github.com/hyperf/hyperf/pull/2204) 為方法 `parallel` 增加 `$concurrent` 參數，用於快速設置併發量。

## 修復

- [#2210](https://github.com/hyperf/hyperf/pull/2210) 修復 `WebSocket` 握手成功後，不會立馬觸發 `OnOpen` 事件的 BUG。
- [#2214](https://github.com/hyperf/hyperf/pull/2214) 修復 `WebSocket` 主動關閉連接時，不會觸發 `OnClose` 事件的 BUG。
- [#2218](https://github.com/hyperf/hyperf/pull/2218) 修復在 `協程 Server` 下，`Sender::disconnect` 報錯的 BUG。
- [#2227](https://github.com/hyperf/hyperf/pull/2227) 修復在 `協程 Server` 下，建立 `keepalive` 連接後，上下文數據無法在請求結束後銷燬的 BUG。

## 優化

- [#2193](https://github.com/hyperf/hyperf/pull/2193) 優化 `Hyperf\Watcher\Driver\FindDriver`，使其掃描有變動的文件更加精確。
- [#2232](https://github.com/hyperf/hyperf/pull/2232) 優化 `model-cache` 的預加載功能，使其支持 `In` 和 `InRaw`。

# v2.0.4 - 2020-07-27

## 新增

- [#2144](https://github.com/hyperf/hyperf/pull/2144) 數據庫查詢事件 `Hyperf\Database\Events\QueryExecuted` 添加 `$result` 字段。
- [#2158](https://github.com/hyperf/hyperf/pull/2158) 路由 `Hyperf\HttpServer\Router\Handler` 中，添加 `$options` 字段。
- [#2162](https://github.com/hyperf/hyperf/pull/2162) 熱更新組件添加 `Hyperf\Watcher\Driver\FindDriver`。
- [#2169](https://github.com/hyperf/hyperf/pull/2169) `Session` 組件新增配置 `session.options.domain`，用於替換 `Request` 中獲取的 `domain`。
- [#2174](https://github.com/hyperf/hyperf/pull/2174) 模型生成器添加 `ModelRewriteTimestampsVisitor`，用於根據數據庫字段 `created_at` 和 `updated_at`， 重寫模型字段 `$timestamps`。
- [#2175](https://github.com/hyperf/hyperf/pull/2175) 模型生成器添加 `ModelRewriteSoftDeletesVisitor`，用於根據數據庫字段 `deleted_at`， 添加或者移除 `SoftDeletes`。
- [#2176](https://github.com/hyperf/hyperf/pull/2176) 模型生成器添加 `ModelRewriteKeyInfoVisitor`，用於根據數據庫主鍵，重寫模型字段 `$incrementing` `$primaryKey` 和 `$keyType`。

## 修復

- [#2149](https://github.com/hyperf/hyperf/pull/2149) 修復自定義進程運行過程中無法從 Nacos 正常更新配置的 BUG。
- [#2159](https://github.com/hyperf/hyperf/pull/2159) 修復使用 `gen:migration` 時，由於文件已經存在導致的 `FATAL` 異常。

## 優化

- [#2043](https://github.com/hyperf/hyperf/pull/2043) 當 `SCAN` 目錄都不存在時，拋出更加友好的異常。
- [#2182](https://github.com/hyperf/hyperf/pull/2182) 當使用 `WebSocket` 和 `Http` 服務且 `Http` 接口被訪問時，不會記錄 `WebSocket` 關閉連接的日誌。

# v2.0.3 - 2020-07-20

## 新增

- [#1554](https://github.com/hyperf/hyperf/pull/1554) 新增 `hyperf/nacos` 組件。
- [#2082](https://github.com/hyperf/hyperf/pull/2082) 監聽器 `Hyperf\Signal\Handler\WorkerStopHandler` 添加信號 `SIGINT` 監聽。
- [#2097](https://github.com/hyperf/hyperf/pull/2097) `hyperf/filesystem` 新增 TencentCloud COS 支持.
- [#2122](https://github.com/hyperf/hyperf/pull/2122) 添加 Trait `\Hyperf\Snowflake\Concern\HasSnowflake` 為模型自動生成雪花算法的主鍵。

## 修復

- [#2017](https://github.com/hyperf/hyperf/pull/2017) 修復 Prometheus 使用 redis 打點時，改變 label 會導致收集報錯的 BUG。
- [#2117](https://github.com/hyperf/hyperf/pull/2117) 修復使用 `server:watch` 時，註解 `@Inject` 有時會失效的 BUG。
- [#2123](https://github.com/hyperf/hyperf/pull/2123) 修復 `tracer` 會記錄兩次 `Redis 指令` 的 BUG。
- [#2139](https://github.com/hyperf/hyperf/pull/2139) 修復 `ValidationMiddleware` 在 `WebSocket` 服務下使用會報錯的 BUG。
- [#2140](https://github.com/hyperf/hyperf/pull/2140) 修復請求拋出異常時，`Session` 無法保存的 BUG。

## 優化

- [#2080](https://github.com/hyperf/hyperf/pull/2080) 方法 `Hyperf\Database\Model\Builder::paginate` 中參數 `$perPage` 的類型從 `int` 更改為 `?int`。
- [#2110](https://github.com/hyperf/hyperf/pull/2110) 在使用 `hyperf/watcher` 時，會先檢查進程是否存在，如果不存在，才會發送 `SIGTERM` 信號。
- [#2116](https://github.com/hyperf/hyperf/pull/2116) 優化組件 `hyperf/di` 的依賴。
- [#2121](https://github.com/hyperf/hyperf/pull/2121) 在使用 `gen:model` 時，如果用户自定義了與數據庫字段一致的字段時，則會替換對應的 `@property`。
- [#2129](https://github.com/hyperf/hyperf/pull/2129) 當 Response Json 格式化失敗時，會拋出更加友好的錯誤提示。

# v2.0.2 - 2020-07-13

## 修復

- [#1898](https://github.com/hyperf/hyperf/pull/1898) 修復定時器規則 `$min-$max` 解析有誤的 BUG。
- [#2037](https://github.com/hyperf/hyperf/pull/2037) 修復 TCP 服務，連接後共用一個協程，導致 DB 等連接池無法正常回收連接的 BUG。
- [#2051](https://github.com/hyperf/hyperf/pull/2051) 修復 `CoroutineServer` 不會生成 `hyperf.pid` 的 BUG。
- [#2055](https://github.com/hyperf/hyperf/pull/1695) 修復 `Guzzle` 在傳輸大數據包時會自動添加頭 `Expect: 100-Continue`，導致請求失敗的 BUG。
- [#2059](https://github.com/hyperf/hyperf/pull/2059) 修復 `SocketIOServer` 中 `Redis` 重連失敗的 BUG。
- [#2067](https://github.com/hyperf/hyperf/pull/2067) 修復 `hyperf/watcher` 組件 `Syntax` 錯誤會導致進程異常。
- [#2085](https://github.com/hyperf/hyperf/pull/2085) 修復註解 `RetryFalsy` 會導致獲得正確的結果後，再次重試。
- [#2089](https://github.com/hyperf/hyperf/pull/2089) 修復使用 `gen:command` 後，腳本必須要進行修改，才能被加載到的 BUG。
- [#2093](https://github.com/hyperf/hyperf/pull/2093) 修復腳本 `vendor:publish` 沒有返回碼導致報錯的 BUG。

## 新增

- [#1860](https://github.com/hyperf/hyperf/pull/1860) 為 `Server` 添加默認的 `OnWorkerExit` 回調。
- [#2042](https://github.com/hyperf/hyperf/pull/2042) 為熱更新組件，添加文件掃描驅動。
- [#2054](https://github.com/hyperf/hyperf/pull/2054) 為模型緩存添加 `Eager Load` 功能。

## 優化

- [#2049](https://github.com/hyperf/hyperf/pull/2049) 優化熱更新組件的 Stdout 輸出。
- [#2090](https://github.com/hyperf/hyperf/pull/2090) 為 `hyperf/session` 組件適配非 `Hyperf` 的 `Response`。

## 變更

- [#2031](https://github.com/hyperf/hyperf/pull/2031) 常量組件的錯誤碼只支持 `int` 和 `string`。
- [#2065](https://github.com/hyperf/hyperf/pull/2065) `WebSocket` 消息發送器 `Hyperf\WebSocketServer\Sender` 支持 `push` 和 `disconnect`。
- [#2100](https://github.com/hyperf/hyperf/pull/2100) 組件 `hyperf/utils` 更新依賴 `doctrine/inflector` 版本到 `^2.0`。

## 移除

- [#2065](https://github.com/hyperf/hyperf/pull/2065) 移除 `Hyperf\WebSocketServer\Sender` 對方法 `send` `sendto` 和 `close` 的支持，請使用 `push` 和 `disconnect`。

# v2.0.1 - 2020-07-02

## 新增

- [#1934](https://github.com/hyperf/hyperf/pull/1934) 增加腳本 `gen:constant` 用於創建常量類。
- [#1982](https://github.com/hyperf/hyperf/pull/1982) 添加熱更新組件，文件修改後自動收集註解，自動重啓。

## 修復

- [#1952](https://github.com/hyperf/hyperf/pull/1952) 修復數據庫遷移類存在時，也會生成同類名類，導致類名衝突的 BUG。
- [#1960](https://github.com/hyperf/hyperf/pull/1960) 修復 `Hyperf\HttpServer\ResponseEmitter::isMethodsExists()` 判斷錯誤的 BUG。
- [#1961](https://github.com/hyperf/hyperf/pull/1961) 修復因文件 `config/autoload/aspects.php` 不存在導致服務無法啓動的 BUG。
- [#1964](https://github.com/hyperf/hyperf/pull/1964) 修復接口請求時，數據體為空會導致 `500` 錯誤的 BUG。
- [#1965](https://github.com/hyperf/hyperf/pull/1965) 修復 `initRequestAndResponse` 失敗後，會導致請求狀態碼與實際不符的 BUG。
- [#1968](https://github.com/hyperf/hyperf/pull/1968) 修復當修改 `aspects.php` 文件後，`Aspect` 無法安裝修改後的結果運行的 BUG。
- [#1985](https://github.com/hyperf/hyperf/pull/1985) 修復註解全局配置不全為小寫時，會導致 `global_imports` 失敗的 BUG。
- [#1990](https://github.com/hyperf/hyperf/pull/1990) 修復當父類存在與子類一樣的成員變量時， `@Inject` 無法正常使用的 BUG。
- [#2019](https://github.com/hyperf/hyperf/pull/2019) 修復腳本 `gen:model` 因為使用了 `morphTo` 或 `where` 導致生成對應的 `@property` 失敗的 BUG。
- [#2026](https://github.com/hyperf/hyperf/pull/2026) 修復當使用了魔術方法時，LazyLoad 代理生成有誤的 BUG。

## 變更

- [#1986](https://github.com/hyperf/hyperf/pull/1986) 當沒有設置正確的 `swoole.use_shortname` 變更腳本 `exit_code` 為 `SIGTERM`。

## 優化

- [#1959](https://github.com/hyperf/hyperf/pull/1959) 優化類 `ClassLoader` 可以更容易被用户繼承並修改。
- [#2002](https://github.com/hyperf/hyperf/pull/2002) 當 `PHP` 版本大於等於 `7.3` 時，支持 `AOP` 切入 `Trait`。

# v2.0 - 2020-06-22

## 主要功能

1. 重構 [hyperf/di](https://github.com/hyperf/di) 組件，特別是對 AOP 和註解的優化，在 2.0 版本，該組件使用了一個全新的加載機制來提供 AOP 功能的支持。
    1. 對比 1.x 版本來説最顯著的一個功能就是現在你可以通過 AOP 功能切入任何方式實例化的一個類了，比如説，在 1.x 版本，你只能切入由 DI 容器創建的類，你無法切入一個由 `new` 關鍵詞實例化的類，但在 2.0 版本都可以生效了。不過仍有一些例外的情況，您仍無法切入那些在啓動階段用來提供 AOP 功能的類；
    2. 在 1.x 版本，AOP 只能作用於普通的類，無法支持 `Final` 類，但在 2.0 版本您可以這麼做了；
    3. 在 1.x 版本，您無法在當前類的構造函數中使用 `@Inject` 或 `@Value` 註解標記的類成員屬性的值，但在 2.0 版本里，您可以這麼做了；
    4. 在 1.x 版本，只有通過 DI 容器創建的對象才能使 `@Inject` 和 `@Value` 註解的功能生效，通過 `new` 關鍵詞創建的對象無法生效，但在 2.0 版本，都可以生效了；
    5. 在 1.x 版本，在使用註解時，您必須定義註解的命名空間來指定使用的註解類，但在 2.0 版本下，您可以為任一註解提供一個別名，這樣在使用這個註解時可以直接使用別名而無需引入註解類的命名空間。比如您可以直接在任意類屬性上標記 `@Inject` 註解而無需編寫 `use Hyperf\Di\Annotation\Inject;`；
    6. 在 1.x 版本，創建的代理類是一個目標類的子類，這樣的實現機制會導致一些魔術常量獲得的值返回的是代理類子類的信息，而不是目標類的信息，但在 2.0 版本，代理類會與目標類保持一樣的類名和代碼結構；
    7. 在 1.x 版本，當代理類緩存存在時則不會重新生成緩存，就算源代碼發生了變化，這樣的機制有助於掃描耗時的提升，但與此同時，這也會導致開發階段的一些不便利，但在 2.0 版本，代理類緩存會根據源代碼的變化而自動變化，這一改變會減少很多在開發階段的心智負擔；
    8. 為 Aspect 類增加了 `priority` 優先級屬性，現在您可以組織多個 Aspect 之間的順序了；
    9. 在 1.x 版本，您只能通過 `@Aspect` 註解類定義一個 Aspect 類，但在 2.0 版本，您還可以通過配置文件、ConfigProvider 來定義 Aspect 類；
    10. 在 1.x 版本，您在使用到依賴懶加載功能時，必須註冊一個 `Hyperf\Di\Listener\LazyLoaderBootApplicationListener` 監聽器，但在 2.0 版本，您可以直接使用該功能而無需做任何的註冊動作；
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
- 移除了 hyperf/utils 組件中 ConfigProvider 中的 `Hyperf\Contract\NormalizerInterface => Hyperf\Utils\Serializer\SymfonyNormalizer` 關係；
- 移除了 `Hyperf\Contract\OnOpenInterface`、`Hyperf\Contract\OnCloseInterface`、`Hyperf\Contract\OnMessageInterface`、`Hyperf\Contract\OnReceiveInterface` 接口中的 `$server` 參數的強類型聲明；

## 新增

- [#992](https://github.com/hyperf/hyperf/pull/992) 新增 [hyperf/reactive-x](https://github.com/hyperf/reactive-x) 組件；
- [#1245](https://github.com/hyperf/hyperf/pull/1245) 為 `ExceptionHandler` 新增了註解的定義方式；
- [#1245](https://github.com/hyperf/hyperf/pull/1245) `ExceptionHandler` 新增了 `priority` 優先級屬性，通過配置文件或註解方式均可定義優先級；
- [#1819](https://github.com/hyperf/hyperf/pull/1819) 新增 [hyperf/signal](https://github.com/hyperf/signal) 組件；
- [#1844](https://github.com/hyperf/hyperf/pull/1844) 為 [hyperf/model-cache](https://github.com/hyperf/model-cache) 組件中的 `ttl` 屬性增加了 `\DateInterval` 類型的支持；
- [#1855](https://github.com/hyperf/hyperf/pull/1855) 連接池新增了 `ConstantFrequency` 恆定頻率策略來釋放限制的連接；
- [#1871](https://github.com/hyperf/hyperf/pull/1871) 為 Guzzle 增加 `sink` 選項支持；
- [#1805](https://github.com/hyperf/hyperf/pull/1805) 新增 Coroutine Server 協程服務支持；
  - 變更了 `Hyperf\Contract\ProcessInterface` 中的 `bind(Server $server)` 方法聲明為 `bind($server)`；
  - 變更了 `Hyperf\Contract\ProcessInterface` 中的 `isEnable()` 方法聲明為 `isEnable($server)`；
  - 配置中心、Crontab、服務監控、消息隊列消費者現在可以通過協程模式來運行，且在使用協程服務模式時，也必須以協程模式來運行；
  - `Hyperf\AsyncQueue\Environment` 的作用域改為當前協程內，而不是整個進程；
  - 協程模式下不再支持 Task 機制；
- [#1877](https://github.com/hyperf/hyperf/pull/1877) 在 PHP 8 下使用 `@Inject` 註解時支持通過成員屬性強類型聲明來替代 `@var` 聲明，如下所示：

```
class Example {
    /**
     * @Inject
     */
    private ExampleService $exampleService;
}
```

- [#1890](https://github.com/hyperf/hyperf/pull/1890) 新增 `Hyperf\HttpServer\ResponseEmitter` 類來響應任意符合 PSR-7 標準的 Response 對象，同時抽象了 `Hyperf\Contract\ResponseEmitterInterface` 契約；
- [#1890](https://github.com/hyperf/hyperf/pull/1890) 為 `Hyperf\HttpMessage\Server\Response` 類新增了 `getTrailers()` 和 `getTrailer(string $key)` 和 `withTrailer(string $key, $value)` 方法；
- [#1920](https://github.com/hyperf/hyperf/pull/1920) 新增方法 `Hyperf\WebSocketServer\Sender::close(int $fd, bool $reset = null)`.

## 修復

- [#1825](https://github.com/hyperf/hyperf/pull/1825) 修復了 `StartServer::execute` 的 `TypeError`；
- [#1854](https://github.com/hyperf/hyperf/pull/1854) 修復了在 filesystem 中使用 `Runtime::enableCoroutine()` 時，`is_resource` 不能工作的問題；
- [#1900](https://github.com/hyperf/hyperf/pull/1900) 修復了 `Model` 中的 `asDecimal` 方法類型有可能錯誤的問題；
- [#1917](https://github.com/hyperf/hyperf/pull/1917) 修復了 `Request::isXmlHttpRequest` 方法無法正常工作的問題；

## 變更

- [#705](https://github.com/hyperf/hyperf/pull/705) 統一了 HTTP 異常的處理方式，現在統一拋出一個 `Hyperf\HttpMessage\Exception\HttpException` 依賴類來替代在 `Dispatcher` 中直接響應的方式，同時提供了 `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` 異常處理器來處理該類異常；
- [#1846](https://github.com/hyperf/hyperf/pull/1846) 當您 require 了 `symfony/serializer` 庫，不再自動映射 `Hyperf\Contract\NormalizerInterface` 的實現類，您需要手動添加該映射關係，如下：

```php
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

- [#1924](https://github.com/hyperf/hyperf/pull/1924) 重命名 `Hyperf\GrpcClient\BaseClient` 內 `simpleRequest, getGrpcClient, clientStreamRequest` 方法名為 `_simpleRequest, _getGrpcClient, _clientStreamRequest`；

## 移除

- [#1890](https://github.com/hyperf/hyperf/pull/1890) Removed `Hyperf\Contract\Sendable` interface and all implementations of it.
- [#1905](https://github.com/hyperf/hyperf/pull/1905) Removed config `config/server.php`, you can merge it into `config/config.php`.

## 優化

- [#1793](https://github.com/hyperf/hyperf/pull/1793) Socket.io 服務現在只在 onOpen and onClose 中觸發 connect/disconnect 事件，同時將一些類方法從 private 級別調整到了 protected 級別，以便用户可以方便的重寫這些方法；
- [#1848](https://github.com/hyperf/hyperf/pull/1848) 當 RPC 客户端對應的 Contract 發生變更時，自動重寫生成對應的動態代理客户端類；
- [#1863](https://github.com/hyperf/hyperf/pull/1863) 為 async-queue 組件提供更加安全的停止機制；
- [#1896](https://github.com/hyperf/hyperf/pull/1896) 當在 constants 組件中使用了同樣的 code 時，keys 會被合併起來；
