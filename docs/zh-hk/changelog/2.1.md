# 版本更新記錄

# v2.1.23 - 2021-07-12

## 優化

- [#3787](https://github.com/hyperf/hyperf/pull/3787) 優化 `JSON RPC` 服務，優先初始化 `PSR Response`，用於避免 `PSR Request` 初始化失敗後，無法從上下文中獲取 `Response` 的問題。

# v2.1.22 - 2021-06-28

## 安全性更新

- [#3723](https://github.com/hyperf/hyperf/pull/3723) 修復驗證器規則 `active_url` 無法正確檢查 `dns` 記錄，從而導致繞過驗證的問題。
- [#3724](https://github.com/hyperf/hyperf/pull/3724) 修復可以利用 `RequiredIf` 規則生成用於反序列化漏洞的小工具鏈的問題。

## 修復

- [#3721](https://github.com/hyperf/hyperf/pull/3721) 修復了驗證器規則 `in` 和 `not in` 判斷有誤的問題，例如規則為 `in:00` 時，`0`不應該被允許通過。

# v2.1.21 - 2021-06-21

## 修復

- [#3684](https://github.com/hyperf/hyperf/pull/3684) 修復使用熔斷器時，成功次數和失敗次數的界限判斷有誤的問題。

# v2.1.20 - 2021-06-07

## 修復

- [#3667](https://github.com/hyperf/hyperf/pull/3667) 修復形如 `10-12/1,14-15/1` 的定時任務規則無法正常使用的問題。
- [#3669](https://github.com/hyperf/hyperf/pull/3669) 修復了沒有反斜線形如 `10-12` 的定時任務規則無法正常使用的問題。
- [#3674](https://github.com/hyperf/hyperf/pull/3674) 修復 `@Task` 註解中，參數 `$workerId` 無法正常使用的問題。

## 優化

- [#3663](https://github.com/hyperf/hyperf/pull/3663) 優化 `AbstractServiceClient::getNodesFromConsul()` 方法，排除了可能找不到端口的隱患。
- [#3668](https://github.com/hyperf/hyperf/pull/3668) 優化 `Guzzle` 組件中 `CoroutineHandler` 代理相關的代碼，增強其兼容性。

# v2.1.19 - 2021-05-31

## 修復

- [#3618](https://github.com/hyperf/hyperf/pull/3618) 修復使用了相同路徑但不同實現邏輯的路由會在命令 `describe:routes` 中，被合併成一條的問題。
- [#3625](https://github.com/hyperf/hyperf/pull/3625) 修復 `Hyperf\Di\Annotation\Scanner` 中無法正常使用 `class_map` 功能的問題。

## 新增

- [#3626](https://github.com/hyperf/hyperf/pull/3626) 為 `RPC` 組件增加了新的路徑打包器 `Hyperf\Rpc\PathGenerator\DotPathGenerator`。

## 新組件孵化

- [nacos-sdk](https://github.com/hyperf/nacos-sdk-incubator) 基於 Nacos Open API 實現的 SDK。

# v2.1.18 - 2021-05-24

## 修復

- [#3598](https://github.com/hyperf/hyperf/pull/3598) 修復事務回滾時，模型累加、累減操作會導致模型緩存產生髒數據的問題。
- [#3607](https://github.com/hyperf/hyperf/pull/3607) 修復在使用協程風格的 `WebSocket` 服務時，`onOpen` 事件無法在事件結束後銷燬協程的問題。
- [#3610](https://github.com/hyperf/hyperf/pull/3610) 修復數據庫存在前綴時，`fromSub()` 和 `joinSub()` 無法正常使用的問題。

# v2.1.17 - 2021-05-17

## 修復

- [#3856](https://github.com/hyperf/hyperf/pull/3586) 修復 `Swow` 服務處理 `keepalive` 的請求時，協程無法在每個請求後結束的問題。

## 新增

- [#3329](https://github.com/hyperf/hyperf/pull/3329) `@Crontab` 註解的 `enable` 參數增加支持設置數組, 你可以通過它動態的控制定時任務是否啓動。

# v2.1.16 - 2021-04-26

## 修復

- [#3510](https://github.com/hyperf/hyperf/pull/3510) 修復 `consul` 無法將節點強制離線的問題。
- [#3513](https://github.com/hyperf/hyperf/pull/3513) 修復 `Nats` 因為 `Socket` 超時時間小於最大閒置時間，導致連接意外關閉的問題。
- [#3520](https://github.com/hyperf/hyperf/pull/3520) 修復 `@Inject` 無法作用於嵌套 `Trait` 的問題。

## 新增

- [#3514](https://github.com/hyperf/hyperf/pull/3514) 新增方法 `Hyperf\HttpServer\Request::clearStoredParsedData()`。

## 優化

- [#3517](https://github.com/hyperf/hyperf/pull/3517) 優化 `Hyperf\Di\Aop\PropertyHandlerTrait`。

# v2.1.15 - 2021-04-19

## 新增

- [#3484](https://github.com/hyperf/hyperf/pull/3484) 新增 `ORM` 方法 `withMax()` `withMin()` `withSum()` 和 `withAvg()`.

# v2.1.14 - 2021-04-12

## 修復

- [#3465](https://github.com/hyperf/hyperf/pull/3465) 修復協程風格下，`WebSocket` 服務不支持配置多個端口的問題。
- [#3467](https://github.com/hyperf/hyperf/pull/3467) 修復協程風格下，`WebSocket` 服務無法正常釋放連接池的問題。

## 新增

- [#3472](https://github.com/hyperf/hyperf/pull/3472) 新增方法 `Sender::getResponse()`，可以在協程風格的 `WebSocket` 服務裏，獲得與 `fd` 一一對應的 `Response` 對象。

# v2.1.13 - 2021-04-06

## 修復

- [#3432](https://github.com/hyperf/hyperf/pull/3432) 修復 `SocketIO` 服務，定時清理失效 `fd` 的功能無法作用到其他 `worker` 進程的問題。
- [#3434](https://github.com/hyperf/hyperf/pull/3434) 修復 `RPC` 結果不支持允許為 `null` 的類型，例如 `?array` 會被強制轉化為數組。
- [#3447](https://github.com/hyperf/hyperf/pull/3447) 修復模型緩存中，因為存在表前綴，導致模型默認值無法生效的問題。
- [#3450](https://github.com/hyperf/hyperf/pull/3450) 修復註解 `@Crontab` 無法作用於 `方法` 的問題，支持一個類中，配置多個 `@Crontab`。

## 優化

- [#3453](https://github.com/hyperf/hyperf/pull/3453) 優化了類 `Hyperf\Utils\Channel\Caller` 回收實例時的機制，防止因為實例為 `null` 時，導致無法正確回收的問題。
- [#3455](https://github.com/hyperf/hyperf/pull/3455) 優化腳本 `phar:build`，支持使用軟連接方式加載的組件包。

# v2.1.12 - 2021-03-29

## 修復

- [#3423](https://github.com/hyperf/hyperf/pull/3423) 修復 `worker_num` 設置為非 `Integer` 時，導致定時任務中 `Task` 策略無法正常使用的問題。
- [#3426](https://github.com/hyperf/hyperf/pull/3426) 修復為可選參數路由設置中間件時，導致中間件被意外執行兩次的問題。

## 優化

- [#3422](https://github.com/hyperf/hyperf/pull/3422) 優化了 `co-phpunit` 的代碼。

# v2.1.11 - 2021-03-22

## 新增

- [#3376](https://github.com/hyperf/hyperf/pull/3376) 為註解 `Hyperf\DbConnection\Annotation\Transactional` 增加參數 `$connection` 和 `$attempts`，用户可以按需設置事務連接和重試次數。
- [#3403](https://github.com/hyperf/hyperf/pull/3403) 新增方法 `Hyperf\Testing\Client::sendRequest()`，用户可以使用自己構造的 `ServerRequest`，比如設置 `Cookies`。

## 修復

- [#3380](https://github.com/hyperf/hyperf/pull/3380) 修復超全局變量，在協程上下文裏沒有 `Request` 對象時，無法正常工作的問題。
- [#3394](https://github.com/hyperf/hyperf/pull/3394) 修復使用 `@Inject` 注入的對象，會被 `trait` 中注入的對象覆蓋的問題。
- [#3395](https://github.com/hyperf/hyperf/pull/3395) 修復當繼承使用 `@Inject` 注入私有變量的父類時，而導致子類實例化報錯的問題。
- [#3398](https://github.com/hyperf/hyperf/pull/3398) 修復單元測試中使用 `UploadedFile::isValid()` 時，無法正確判斷結果的問題。

# v2.1.10 - 2021-03-15

## 修復

- [#3348](https://github.com/hyperf/hyperf/pull/3348) 修復當使用 `Arr::forget` 方法在 `key` 為 `integer` 且不存在時，執行報錯的問題。
- [#3351](https://github.com/hyperf/hyperf/pull/3351) 修復 `hyperf/validation` 組件中，`FormRequest` 無法從協程上下文中獲取到修改後的 `ServerRequest`，從而導致驗證器驗證失敗的問題。
- [#3356](https://github.com/hyperf/hyperf/pull/3356) 修復 `hyperf/testing` 組件中，客户端 `Hyperf\Testing\Client` 無法模擬構造正常的 `UriInterface` 的問題。
- [#3363](https://github.com/hyperf/hyperf/pull/3363) 修復在入口文件 `bin/hyperf.php` 中自定義的常量，無法在命令 `server:watch` 中使用的問題。
- [#3365](https://github.com/hyperf/hyperf/pull/3365) 修復當使用協程風格服務時，如果用户沒有配置 `pid_file`，仍然會意外生成 `runtime/hyperf.pid` 文件的問題。

## 優化

- [#3364](https://github.com/hyperf/hyperf/pull/3364) 優化命令 `phar:build`，你可以在不使用 `php` 腳本的情況下執行 `phar` 文件，就像使用命令 `./composer.phar` 而非 `php composer.phar`。
- [#3367](https://github.com/hyperf/hyperf/pull/3367) 優化使用 `gen:model` 生成模型字段的類型註釋時，儘量讀取自定義轉換器轉換後的對象類型。

# v2.1.9 - 2021-03-08

## 修復

- [#3326](https://github.com/hyperf/hyperf/pull/3326) 修復使用 `JsonEofPacker` 無法正確解包自定義 `eof` 數據的問題。
- [#3330](https://github.com/hyperf/hyperf/pull/3330) 修復因其他協程修改靜態變量 `$constraints`，導致模型關係查詢錯誤的問題。

## 新增

- [#3325](https://github.com/hyperf/hyperf/pull/3325) 為 `Crontab` 註解增加 `enable` 參數，用於控制當前任務是否註冊到定時任務中。

## 優化

- [#3338](https://github.com/hyperf/hyperf/pull/3338) 優化了 `testing` 組件，使模擬請求的方法運行在獨立的協程當中，避免協程變量污染。

# v2.1.8 - 2021-03-01

## 修復

- [#3301](https://github.com/hyperf/hyperf/pull/3301) 修復 `hyperf/cache` 組件，當沒有在註解中設置超時時間時，會將超時時間強制轉化為 0，導致緩存不失效的問題。

## 新增

- [#3310](https://github.com/hyperf/hyperf/pull/3310) 新增方法 `Blueprint::comment()`，可以允許在使用 `Migration` 的時候，設置表註釋。 
- [#3311](https://github.com/hyperf/hyperf/pull/3311) 新增方法 `RouteCollector::getRouteParser`，可以方便的從 `RouteCollector` 中獲取到 `RouteParser` 對象。
- [#3316](https://github.com/hyperf/hyperf/pull/3316) 允許用户在 `hyperf/db` 組件中，註冊自定義數據庫適配器。

## 優化

- [#3308](https://github.com/hyperf/hyperf/pull/3308) 優化 `WebSocket` 服務，當找不到對應路由時，直接返回響應。
- [#3319](https://github.com/hyperf/hyperf/pull/3319) 優化從連接池獲取連接的代碼邏輯，避免因重寫低頻組件導致報錯，使得連接被意外丟棄。

## 新組件孵化

- [rpc-multiplex](https://github.com/hyperf/rpc-multiplex-incubator) 基於 Channel 實現的多路複用 RPC 組件。
- [db-pgsql](https://github.com/hyperf/db-pgsql-incubator) 適配於 `hyperf/db` 的 `PgSQL` 適配器。

# v2.1.7 - 2021-02-22

## 修復

- [#3272](https://github.com/hyperf/hyperf/pull/3272) 修復使用 `doctrine/dbal` 修改數據庫字段名報錯的問題。

## 新增

- [#3261](https://github.com/hyperf/hyperf/pull/3261) 新增方法 `Pipeline::handleCarry`，可以方便處理返回值。
- [#3267](https://github.com/hyperf/hyperf/pull/3267) 新增 `Hyperf\Utils\Reflection\ClassInvoker`，用於執行非公共方法和讀取非公共變量。
- [#3268](https://github.com/hyperf/hyperf/pull/3268) 為 `kafka` 消費者新增訂閲多個主題的能力。
- [#3193](https://github.com/hyperf/hyperf/pull/3193) [#3296](https://github.com/hyperf/hyperf/pull/3296) 為 `phar:build` 新增選項 `-M`，可以用來映射外部的文件或目錄到 `Phar` 包中。 

## 變更

- [#3258](https://github.com/hyperf/hyperf/pull/3258) 為不同的 `kafka` 消費者設置不同的 Client ID。
- [#3282](https://github.com/hyperf/hyperf/pull/3282) 為 `hyperf/signal` 將拼寫錯誤的 `stoped` 修改為 `stopped`。

# v2.1.6 - 2021-02-08

## 修復

- [#3233](https://github.com/hyperf/hyperf/pull/3233) 修復 `AMQP` 組件，因連接服務端失敗，導致連接池耗盡的問題。
- [#3245](https://github.com/hyperf/hyperf/pull/3245) 修復 `hyperf/kafka` 組件設置 `autoCommit` 為 `false` 無效的問題。
- [#3255](https://github.com/hyperf/hyperf/pull/3255) 修復 `Nsq` 消費者進程，無法觸發 `defer` 方法的問題。

## 優化

- [#3249](https://github.com/hyperf/hyperf/pull/3249) 優化 `hyperf/kafka` 組件，可以重用連接進行消息發佈。

## 移除

- [#3235](https://github.com/hyperf/hyperf/pull/3235) 移除 `hyperf/kafka` 組件 `rebalance` 檢查，因為底層庫 `longlang/phpkafka` 增加了對應的檢查。

# v2.1.5 - 2021-02-01

## 修復

- [#3204](https://github.com/hyperf/hyperf/pull/3204) 修復在 `hyperf/rpc-server` 組件中，中間件會被意外替換的問題。
- [#3209](https://github.com/hyperf/hyperf/pull/3209) 修復 `hyperf/amqp` 組件在使用協程風格服務，且因超時意外報錯時，沒有辦法正常回收到連接池的問題。
- [#3222](https://github.com/hyperf/hyperf/pull/3222) 修復 `hyperf/database` 組件中 `JOIN` 查詢會導致內存泄露的問題。
- [#3228](https://github.com/hyperf/hyperf/pull/3228) 修復 `hyperf/tracer` 組件中，在 `defer` 中調用 `flush` 失敗時，會導致進程異常退出的問題。
- [#3230](https://github.com/hyperf/hyperf/pull/3230) 修復 `hyperf/scout` 組件中 `orderBy` 方法無效的問題。

## 新增

- [#3211](https://github.com/hyperf/hyperf/pull/3211) 為 `hyperf/nacos` 組件添加了新的配置項 `url`，用於訪問 `Nacos` 服務。
- [#3214](https://github.com/hyperf/hyperf/pull/3214) 新增類 `Hyperf\Utils\Channel\Caller`，可以允許用户使用協程安全的連接，避免連接被多個協程綁定，導致報錯的問題。
- [#3224](https://github.com/hyperf/hyperf/pull/3224) 新增方法 `Hyperf\Utils\CodeGen\Package::getPrettyVersion()`，允許用户獲取組件的版本。

## 變更

- [#3218](https://github.com/hyperf/hyperf/pull/3218) 默認為 `AMQP` 配置 `QOS` 參數，`prefetch_count` 為 `1`，`global` 為 `false`，`prefetch_size` 為 `0`。
- [#3224](https://github.com/hyperf/hyperf/pull/3224) 為組件 `jean85/pretty-package-versions` 升級版本到 `^1.2|^2.0`, 支持 `Composer 2.x`。

> 如果使用 composer 2.x，則需要安裝 jean85/pretty-package-versions 的 ^2.0 版本，反之安裝 ^1.2 版本

## 優化

- [#3226](https://github.com/hyperf/hyperf/pull/3226) 優化 `hyperf/database` 組件，使用 `group by` 或 `having` 時執行子查詢獲得總數。

# v2.1.4 - 2021-01-25

## 修復

- [#3165](https://github.com/hyperf/hyperf/pull/3165) 修復方法 `Hyperf\Database\Schema\MySqlBuilder::getColumnListing` 在 `MySQL 8.0` 版本中無法正常使用的問題。
- [#3174](https://github.com/hyperf/hyperf/pull/3174) 修復 `hyperf/database` 組件中 `where` 語句因為不嚴謹的代碼編寫，導致被綁定參數會被惡意替換的問題。
- [#3179](https://github.com/hyperf/hyperf/pull/3179) 修復 `json-rpc` 客户端因對端服務重啓，導致接收數據一直異常的問題。
- [#3189](https://github.com/hyperf/hyperf/pull/3189) 修復 `kafka` 在集羣模式下無法正常使用的問題。
- [#3191](https://github.com/hyperf/hyperf/pull/3191) 修復 `json-rpc` 客户端因對端服務重啓，導致連接池中的連接全部失效，新的請求進來時，首次使用皆會報錯的問題。

## 新增

- [#3170](https://github.com/hyperf/hyperf/pull/3170) 為 `hyperf/watcher` 組件新增了更加友好的驅動器 `FindNewerDriver`，支持 `Mac` `Linux` 和 `Docker`。
- [#3195](https://github.com/hyperf/hyperf/pull/3195) 為 `JsonRpcPoolTransporter` 新增了重試機制, 當連接、發包、收包失敗時，默認重試 2 次，收包超時不進行重試。

## 優化

- [#3169](https://github.com/hyperf/hyperf/pull/3169) 優化了 `ErrorExceptionHandler` 中與 `set_error_handler` 相關的入參代碼, 解決靜態檢測因入參不匹配導致報錯的問題。
- [#3191](https://github.com/hyperf/hyperf/pull/3191) 優化了 `hyperf/json-rpc` 組件, 當連接中斷後，會先嚐試重連。

## 變更

- [#3174](https://github.com/hyperf/hyperf/pull/3174) 嚴格檢查 `hyperf/database` 組件中 `where` 語句綁定參數。

## 新組件孵化

- [DAG](https://github.com/hyperf/dag-incubator) 輕量級有向無環圖任務編排庫。
- [RPN](https://github.com/hyperf/rpn-incubator) 逆波蘭表示法。

# v2.1.3 - 2021-01-18

## 修復

- [#3070](https://github.com/hyperf/hyperf/pull/3070) 修復 `tracer` 組件無法正常使用的問題。
- [#3106](https://github.com/hyperf/hyperf/pull/3106) 修復協程從已被銷燬的協程中複製協程上下文時導致報錯的問題。
- [#3108](https://github.com/hyperf/hyperf/pull/3108) 修復使用 `describe:routes` 命令時，相同 `callback` 不同路由組的路由會被替換覆蓋的問題。
- [#3118](https://github.com/hyperf/hyperf/pull/3118) 修復 `migrations` 配置名位置錯誤的問題。
- [#3126](https://github.com/hyperf/hyperf/pull/3126) 修復 `Swoole` 擴展 `v4.6` 版本中，`SWOOLE_HOOK_SOCKETS` 與 `jaeger` 衝突的問題。
- [#3137](https://github.com/hyperf/hyperf/pull/3137) 修復 `database` 組件，當沒有主動設置 `PDO::ATTR_PERSISTENT` 為 `true` 時，導致的類型錯誤。
- [#3141](https://github.com/hyperf/hyperf/pull/3141) 修復使用 `Migration` 時，`doctrine/dbal` 無法正常工作的問題。

## 新增

- [#3059](https://github.com/hyperf/hyperf/pull/3059) 為 `view-engine` 組件增加合併任意標籤的能力。
- [#3123](https://github.com/hyperf/hyperf/pull/3123) 為 `view-engine` 組件增加 `ComponentAttributeBag::has()` 方法。

# v2.1.2 - 2021-01-11

## 修復

- [#3050](https://github.com/hyperf/hyperf/pull/3050) 修復在 `increment()` 後使用 `save()` 時，導致 `extra` 數據被保存兩次的問題。
- [#3082](https://github.com/hyperf/hyperf/pull/3082) 修復 `hyperf/db` 組件在 `defer` 中使用時，會導致連接被其他協程綁定的問題。
- [#3084](https://github.com/hyperf/hyperf/pull/3084) 修復 `phar` 打包後 `getRealPath` 無法正常工作的問題。
- [#3087](https://github.com/hyperf/hyperf/pull/3087) 修復使用 `AOP` 時，`pipeline` 導致內存泄露的問題。
- [#3095](https://github.com/hyperf/hyperf/pull/3095) 修復 `hyperf/scout` 組件中，`ElasticsearchEngine::getTotalCount()` 無法兼容 `Elasticsearch 7.0` 版本的問題。

## 新增

- [#2847](https://github.com/hyperf/hyperf/pull/2847) 新增 `hyperf/kafka` 組件。
- [#3066](https://github.com/hyperf/hyperf/pull/3066) 為 `hyperf/db` 組件新增 `ConnectionInterface::run(Closure $closure)` 方法。

## 優化

- [#3046](https://github.com/hyperf/hyperf/pull/3046) 打包 `phar` 時，優化了重寫 `scan_cacheable` 的代碼。

## 變更

- [#3077](https://github.com/hyperf/hyperf/pull/3077) 因組件 `league/flysystem` 的 `2.0` 版本無法兼容，故降級到 `^1.0`。

# v2.1.1 - 2021-01-04

## 修復

- [#3045](https://github.com/hyperf/hyperf/pull/3045) 修復 `database` 組件，當沒有主動設置 `PDO::ATTR_PERSISTENT` 為 `true` 時，導致的類型錯誤。
- [#3047](https://github.com/hyperf/hyperf/pull/3047) 修復 `socketio-server` 組件，為 `sid` 續約時報錯的問題。
- [#3062](https://github.com/hyperf/hyperf/pull/3062) 修復 `grpc-server` 組件，入參無法被正確解析的問題。

## 新增

- [#3052](https://github.com/hyperf/hyperf/pull/3052) 為 `metric` 組件，新增了收集命令行指標的功能。
- [#3054](https://github.com/hyperf/hyperf/pull/3054) 為 `socketio-server` 組件，新增了 `Engine::close` 協議支持，並在調用方法 `getRequest` 失敗時，拋出連接已被關閉的異常。

# v2.1.0 - 2020-12-28

## 依賴升級

- 升級 `php` 版本到 `>=7.3`。
- 升級組件 `phpunit/phpunit` 版本到 `^9.0`。
- 升級組件 `guzzlehttp/guzzle` 版本到 `^6.0|^7.0`。
- 升級組件 `vlucas/phpdotenv` 版本到 `^5.0`。
- 升級組件 `endclothing/prometheus_client_php` 版本到 `^1.0`。
- 升級組件 `twig/twig` 版本到 `^3.0`。
- 升級組件 `jcchavezs/zipkin-opentracing` 版本到 `^0.2.0`。
- 升級組件 `doctrine/dbal` 版本到 `^3.0`。
- 升級組件 `league/flysystem` 版本到 `^1.0|^2.0`。

## 移除

- 移除 `Hyperf\Amqp\Builder` 已棄用的成員變量 `$name`。
- 移除 `Hyperf\Amqp\Message\ConsumerMessageInterface` 已棄用的方法 `consume()`。
- 移除 `Hyperf\AsyncQueue\Driver\Driver` 已棄用的成員變量 `$running`。
- 移除 `Hyperf\HttpServer\CoreMiddleware` 已棄用的方法 `parseParameters()`。
- 移除 `Hyperf\Utils\Coordinator\Constants` 已棄用的常量 `ON_WORKER_START` 和 `ON_WORKER_EXIT`。
- 移除 `Hyperf\Utils\Coordinator` 已棄用的方法 `get()`。
- 移除配置文件 `rate-limit.php`, 請使用 `rate_limit.php`。
- 移除無用的類 `Hyperf\Resource\Response\ResponseEmitter`。
- 將組件 `hyperf/paginator` 從 `hyperf/database` 依賴中移除。
- 移除 `Hyperf\Utils\Coroutine\Concurrent` 中的方法 `stats()`。

## 變更

- 方法 `Hyperf\Utils\Coroutine::parentId` 返回父協程的協程 ID
  * 如果在主協程中，則會返回 0。
  * 如果在非協程環境中使用，則會拋出 `RunningInNonCoroutineException` 異常。
  * 如果協程環境已被銷燬，則會拋出 `CoroutineDestroyedException` 異常。

- 類 `Hyperf\Guzzle\CoroutineHandler`
  * 刪除了 `execute()` 方法。
  * 方法 `initHeaders()` 將會返回初始化好的 Header 列表, 而不是直接將 `$headers` 賦值到客户端中。
  * 刪除了 `checkStatusCode()` 方法。

- [#2720](https://github.com/hyperf/hyperf/pull/2720) 不再在方法 `PDOStatement::bindValue()` 中設置 `data_type`，已避免字符串索引中使用整形時，導致索引無法被命中的問題。
- [#2871](https://github.com/hyperf/hyperf/pull/2871) 從 `StreamInterface` 中獲取數據時，使用 `(string) $body` 而不是 `$body->getContents()`，因為方法 `getContents()` 只會返回剩餘的數據，而非全部數據。
- [#2909](https://github.com/hyperf/hyperf/pull/2909) 允許設置重複的中間件。
- [#2935](https://github.com/hyperf/hyperf/pull/2935) 修改了 `Exception Formatter` 的默認規則。
- [#2979](https://github.com/hyperf/hyperf/pull/2979) 命令行 `gen:model` 不再自動將 `decimal` 格式轉化為 `float`。

## 即將廢棄

- 類 `Hyperf\AsyncQueue\Signal\DriverStopHandler` 將會在 `v2.2` 版本中棄用, 請使用 `Hyperf\Process\Handler\ProcessStopHandler` 代替。
- 類 `Hyperf\Server\SwooleEvent` 將會在 `v3.0` 版本中棄用, 請使用 `Hyperf\Server\Event` 代替。

## 新增

- [#2659](https://github.com/hyperf/hyperf/pull/2659) [#2663](https://github.com/hyperf/hyperf/pull/2663) 新增了 [Swow](https://github.com/swow/swow) 驅動支持。
- [#2671](https://github.com/hyperf/hyperf/pull/2671) 新增監聽器 `Hyperf\AsyncQueue\Listener\QueueHandleListener`，用來記錄異步隊列的運行日誌。
- [#2923](https://github.com/hyperf/hyperf/pull/2923) 新增類 `Hyperf\Utils\Waiter`，可以用來等待一個協程結束。
- [#3001](https://github.com/hyperf/hyperf/pull/3001) 新增方法 `Hyperf\Database\Model\Collection::columns()`，類似於 `array_column`。
- [#3002](https://github.com/hyperf/hyperf/pull/3002) 為 `Json::decode` 和 `Json::encode` 新增參數 `$depth` 和 `$flags`。

## 修復

- [#2741](https://github.com/hyperf/hyperf/pull/2741) 修復自定義進程無法在 `Swow` 驅動下使用的問題。

## 優化

- [#3009](https://github.com/hyperf/hyperf/pull/3009) 優化了 `prometheus`，使其支持 `https` 和 `http` 協議。
