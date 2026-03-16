# 版本更新記錄

# v2.2.33 - 2022-05-30

## 修復

- [#4776](https://github.com/hyperf/hyperf/pull/4776) 修復 `GraphQL` 事件收集失敗的問題。
- [#4790](https://github.com/hyperf/hyperf/pull/4790) 修復 `RPN` 組件中方法 `toRPNExpression` 在某些場景無法正常工作的問題。

## Added

- [#4763](https://github.com/hyperf/hyperf/pull/4763) 新增驗證規則 `array:key1,key2`，確保數組中除 `key1` `key2` 以外無其他 `key` 鍵。
- [#4781](https://github.com/hyperf/hyperf/pull/4781) 新增配置 `close-pull-request.yml`，用來自動關閉只讀的倉庫。

# v2.2.32 - 2022-05-16

## 修復

- [#4745](https://github.com/hyperf/hyperf/pull/4745) 當使用 `kafka` 組件的 `Producer::close` 方法時，修復可能拋出空指針異常的問題。
- [#4754](https://github.com/hyperf/hyperf/pull/4754) 通過配置 `monolog>=2.6.0` 解決新版本的 `monolog` 無法正常工作的問題。

## 優化

- [#4738](https://github.com/hyperf/hyperf/pull/4738) 當使用 `kafka` 組件時，如果沒有設置 `GroupID` 則自動配置一個。

# v2.2.31 - 2022-04-18

## 修復

- [#4677](https://github.com/hyperf/hyperf/pull/4677) 修復使用 `kafka` 發佈者後，會導致進程無法正常退出的問題。
- [#4686](https://github.com/hyperf/hyperf/pull/4687) 修復使用 `WebSocket` 服務時，因為解析 `Request` 失敗會導致進程崩潰的問題。

## 新增

- [#4576](https://github.com/hyperf/hyperf/pull/4576) 為 `RPC` 客户端的節點，增加路由前綴 `path_prefix`。
- [#4683](https://github.com/hyperf/hyperf/pull/4683) 新增容器方法 `unbind()` 用來從容器中解綁對象。

# v2.2.30 - 2022-04-04

## 修復

- [#4648](https://github.com/hyperf/hyperf/pull/4648) 當使用 `retry` 組件中的熔斷器時，修復在 `open` 狀態下，無法自動調用 `fallback` 方法的問題。
- [#4657](https://github.com/hyperf/hyperf/pull/4657) 修復使用 `session` 中的文件適配器時，相同的 `Session ID` 在被重寫後，最後修改時間仍是上次修改時間的問題。

## 新增

- [#4646](https://github.com/hyperf/hyperf/pull/4646) 為 `Redis` 哨兵模式增加設置密碼的功能。

# v2.2.29 - 2022-03-28

## 修復

- [#4620](https://github.com/hyperf/hyperf/pull/4620) 修復 `Hyperf\Memory\LockManager::initialize()` 方法中，`$filename` 默認值錯誤的問題。

# v2.2.28 - 2022-03-14

## 修復

- [#4588](https://github.com/hyperf/hyperf/pull/4588) 修復 `database` 組件不支持 `bit` 類型的問題。
- [#4589](https://github.com/hyperf/hyperf/pull/4589) 修復使用 `Nacos` 時，無法正確的註冊臨時實例的問題。

## 新增

- [#4580](https://github.com/hyperf/hyperf/pull/4580) 新增方法 `Hyperf\Utils\Coroutine\Concurrent::getChannel()`。

## 優化

- [#4602](https://github.com/hyperf/hyperf/pull/4602) 將方法 `Hyperf\ModelCache\Manager::formatModels()` 更改為公共方法。

# v2.2.27 - 2022-03-07

## 優化

- [#4572](https://github.com/hyperf/hyperf/pull/4572) 當負載均衡器 `hyperf/load-balancer` 選擇節點失敗時，使用 `Hyperf\LoadBalancer\Exception\RuntimeException` 代替 `\RuntimeException`。

# v2.2.26 - 2022-02-21

## 修復

- [#4536](https://github.com/hyperf/hyperf/pull/4536) 修復使用 `JsonRPC` 時，會設置多次 `content-type` 的問題。

## 新增

- [#4527](https://github.com/hyperf/hyperf/pull/4527) 為 `Hyperf\Database\Schema\Blueprint` 增加了一些比較有用的方法。

## 優化

- [#4514](https://github.com/hyperf/hyperf/pull/4514) 通過使用小寫 `key` 獲取 `HTTP` 的 `Header` 信息，提升一部分性能。
- [#4521](https://github.com/hyperf/hyperf/pull/4521) 在使用 Redis 的哨兵模式時，如果第一個哨兵節點連接失敗，則嘗試連接其餘哨兵節點。
- [#4529](https://github.com/hyperf/hyperf/pull/4529) 將組件 `hyperf/context` 從組件 `hyperf/utils` 中分離出來。

# v2.2.25 - 2022-01-30

## 修復

- [#4484](https://github.com/hyperf/hyperf/pull/4484) 修復使用 `Nacos v2.0.4` 版本時，服務是否註冊過，判斷有誤的問題。

## 新增

- [#4477](https://github.com/hyperf/hyperf/pull/4477) 為 `Hyperf\HttpServer\Request` 新增 `Macroable` 支持。

## 優化

- [#4254](https://github.com/hyperf/hyperf/pull/4254) 當使用 `Hyperf\Di\ScanHandlerPcntlScanHandler` 時，增加 `grpc.enable_fork_support` 檢測。

# v2.2.24 - 2022-01-24

## 修復

- [#4474](https://github.com/hyperf/hyperf/pull/4474) 修復使用多路複用 RPC 時，導致測試腳本無法正常停止的問題。

## 優化

- [#4451](https://github.com/hyperf/hyperf/pull/4451) 優化了 `Hyperf\Watcher\Driver\FindNewerDriver` 的代碼。

# v2.2.23 - 2022-01-17

## 修復

- [#4426](https://github.com/hyperf/hyperf/pull/4426) 修復 `view-engine` 模板引擎，在併發請求下導致模板緩存生成錯誤的問題。

## 新增

- [#4449](https://github.com/hyperf/hyperf/pull/4449) 為 `Hyperf\Utils\Collection` 增加多條件排序的能力。
- [#4455](https://github.com/hyperf/hyperf/pull/4455) 新增命令 `gen:view-engine-cache` 可以預生成模板緩存，避免併發帶來的一系列問題。
- [#4453](https://github.com/hyperf/hyperf/pull/4453) 新增 `Hyperf\Tracer\Aspect\ElasticserachAspect`，用來記錄 `elasticsearch` 客户端的調用記錄。
- [#4458](https://github.com/hyperf/hyperf/pull/4458) 新增 `Hyperf\Di\ScanHandler\ProcScanHandler`，用來支持 `Windows` + `Swow` 環境下啓動服務。

# v2.2.22 - 2022-01-04

## 修復

- [#4399](https://github.com/hyperf/hyperf/pull/4399) 修復使用 `RedisCluster` 時，無法使用 `scan` 方法的問題。

## 新增

- [#4409](https://github.com/hyperf/hyperf/pull/4409) 為 `session` 增加數據庫支持。
- [#4411](https://github.com/hyperf/hyperf/pull/4411) 為 `tracer` 組件，新增 `Hyperf\Tracer\Aspect\DbAspect`，用於記錄 `hyperf/db` 組件產生的 `SQL` 日誌。
- [#4420](https://github.com/hyperf/hyperf/pull/4420) 為 `Hyperf\Amqp\IO\SwooleIO` 增加 `SSL` 支持。

## 優化

- [#4406](https://github.com/hyperf/hyperf/pull/4406) 刪除 `Swoole PSR-0` 風格代碼，更加友好的支持 `Swoole 5.0` 版本。
- [#4429](https://github.com/hyperf/hyperf/pull/4429) 為 `Debug::getRefCount()` 方法增加類型檢測，只能用於輸出對象的 `RefCount`。

# v2.2.21 - 2021-12-20

## 修復

- [#4347](https://github.com/hyperf/hyperf/pull/4347) 修復使用 `AMQP` 組件時，如果連接緩衝區溢出，會導致連接被綁定到多個協程從而報錯的問題。
- [#4373](https://github.com/hyperf/hyperf/pull/4373) 修復使用 `Snowflake` 組件時，由於 `getWorkerId()` 中存在 `IO` 操作進而導致協程切換，最終導致元數據生成重複的問題。

## 新增

- [#4344](https://github.com/hyperf/hyperf/pull/4344) 新增事件 `Hyperf\Crontab\Event\FailToExecute`，此事件會在 `Crontab` 任務執行失敗時觸發。
- [#4348](https://github.com/hyperf/hyperf/pull/4348) 支持使用 `gen:*` 命令創建文件時，自動吊起對應的 `IDE`，並打開當前文件。

## 優化

- [#4350](https://github.com/hyperf/hyperf/pull/4350) 優化了未開啓 `swoole.use_shortname` 時的錯誤信息。
- [#4360](https://github.com/hyperf/hyperf/pull/4360) 將 `Hyperf\Amqp\IO\SwooleIO` 進行重構，使用更加穩定和高效的 `Swoole\Coroutine\Socket` 而非 `Swoole\Coroutine\Client`。

# v2.2.20 - 2021-12-13

## 修復

- [#4338](https://github.com/hyperf/hyperf/pull/4338) 修復使用單測客户端時，路徑中帶有參數會導致無法正確匹配路由的問題。
- [#4346](https://github.com/hyperf/hyperf/pull/4346) 修復使用組件 `php-amqplib/php-amqplib:3.1.1` 時，啓動報錯的問題。

## 新增

- [#4330](https://github.com/hyperf/hyperf/pull/4330) 為 `phar` 組件支持打包 `vendor/bin` 目錄。
- [#4331](https://github.com/hyperf/hyperf/pull/4331) 新增方法 `Hyperf\Testing\Debug::getRefCount($object)`。

# v2.2.19 - 2021-12-06

## 修復

- [#4308](https://github.com/hyperf/hyperf/pull/4308) 修復執行 `server:watch` 時，因為使用相對路徑導致 `collector-reload` 文件找不到的問題。

## 優化

- [#4317](https://github.com/hyperf/hyperf/pull/4317) 為 `Hyperf\Utils\Collection` 和 `Hyperf\Database\Model\Collection` 增強類型提示功能。

# v2.2.18 - 2021-11-29

## 修復

- [#4283](https://github.com/hyperf/hyperf/pull/4283) 修復當 `GRPC` 結果為 `null` 時，`Hyperf\Grpc\Parser::deserializeMessage()` 報錯的問題。

## 新增

- [#4284](https://github.com/hyperf/hyperf/pull/4284) 新增方法 `Hyperf\Utils\Network::ip()` 獲取本地 `IP`。
- [#4290](https://github.com/hyperf/hyperf/pull/4290) 為 `HTTP` 服務增加 `chunk` 功能。
- [#4291](https://github.com/hyperf/hyperf/pull/4291) 為 `value()` 方法增加動態參數功能。
- [#4293](https://github.com/hyperf/hyperf/pull/4293) 為 `server:watch` 命令增加相對路徑支持。
- [#4295](https://github.com/hyperf/hyperf/pull/4295) 為 `Hyperf\Database\Schema\Blueprint::bigIncrements()` 增加別名 `id()`。

# v2.2.17 - 2021-11-22

## 修復

- [#4243](https://github.com/hyperf/hyperf/pull/4243) 修復使用 `parallel` 時，結果集的順序與入參不一致的問題。

## 新增

- [#4109](https://github.com/hyperf/hyperf/pull/4109) 為 `hyperf/tracer` 增加 `PHP8` 的支持。
- [#4260](https://github.com/hyperf/hyperf/pull/4260) 為 `hyperf/database` 增加指定索引的功能。

# v2.2.16 - 2021-11-15

## 新增

- [#4252](https://github.com/hyperf/hyperf/pull/4252) 為 `Hyperf\RpcClient\AbstractServiceClient` 新增 `getServiceName()` 方法。

## 優化

- [#4253](https://github.com/hyperf/hyperf/pull/4253) 在掃描階段時，如果類庫找不到，則跳過且報出警告。

# v2.2.15 - 2021-11-08

## 修復

- [#4200](https://github.com/hyperf/hyperf/pull/4200) 修復當 `runtime/caches` 不是目錄時，使用文件緩存失敗的問題。

## 新增

- [#4157](https://github.com/hyperf/hyperf/pull/4157) 為 `Hyperf\Utils\Arr` 增加 `Macroable` 支持。

# v2.2.14 - 2021-11-01

## 新增

- [#4181](https://github.com/hyperf/hyperf/pull/4181) [#4192](https://github.com/hyperf/hyperf/pull/4192) 為框架增加 `psr/log` 組件版本 `v1.0`、`v2.0`、`v3.0` 的支持。

## 修復

- [#4171](https://github.com/hyperf/hyperf/pull/4171) 修復使用 `consul` 組件時，開啓 `ACL` 驗證後，健康檢測失敗的問題。
- [#4188](https://github.com/hyperf/hyperf/pull/4188) 修復使用 `composer 1.x` 版本時，打包 `phar` 失敗的問題。

# v2.2.13 - 2021-10-25

## 新增

- [#4159](https://github.com/hyperf/hyperf/pull/4159) 為 `Macroable::mixin` 方法增加參數 `$replace`，當其設置為 `false` 時，會優先判斷是否已經存在。

## 修復

- [#4158](https://github.com/hyperf/hyperf/pull/4158) 修復因為使用了 `Union` 類型，導致生成代理類失敗的問題。

## 優化

- [#4159](https://github.com/hyperf/hyperf/pull/4159) [#4166](https://github.com/hyperf/hyperf/pull/4166) 將組件 `hyperf/macroable` 從 `hyperf/utils` 中分離出來。

# v2.2.12 - 2021-10-18

## 新增

- [#4129](https://github.com/hyperf/hyperf/pull/4129) 新增方法 `Str::stripTags()` 和 `Stringable::stripTags()`。

## 修復

- [#4130](https://github.com/hyperf/hyperf/pull/4130) 修復生成模型時，因為使用了選項 `--with-ide` 和 `scope` 方法導致報錯的問題。
- [#4141](https://github.com/hyperf/hyperf/pull/4141) 修復驗證器工廠不支持其他驗證器的問題。

# v2.2.11 - 2021-10-11

## 修復

- [#4101](https://github.com/hyperf/hyperf/pull/4101) 修復 Nacos 使用的密碼攜帶特殊字符時，密碼會被 `urlencode` 導致密碼錯誤的問題。

# 優化

- [#4114](https://github.com/hyperf/hyperf/pull/4114) 優化 WebSocket 客户端初始化失敗時的錯誤信息。
- [#4119](https://github.com/hyperf/hyperf/pull/4119) 優化單測客户端在上傳文件時，因為默認的上傳路徑已經存在，導致報錯的問題（只發生在最新的 Swoole 版本中）。

# v2.2.10 - 2021-09-26

## 修復

- [#4088](https://github.com/hyperf/hyperf/pull/4088) 修復使用定時器規則時，會將空字符串轉化為 `0` 的問題。
- [#4096](https://github.com/hyperf/hyperf/pull/4096) 修復當帶有類型的動態參數生成代理類時，會出現類型錯誤的問題。

# v2.2.9 - 2021-09-22

## 修復

- [#4061](https://github.com/hyperf/hyperf/pull/4061) 修復 `hyperf/metric` 組件與最新版本的 `prometheus_client_php` 存在衝突的問題。
- [#4068](https://github.com/hyperf/hyperf/pull/4068) 修復命令行拋出錯誤時，退出碼與實際不符的問題。
- [#4076](https://github.com/hyperf/hyperf/pull/4076) 修復 `HTTP` 服務因返回數據不是標準 `HTTP` 協議時，導致服務宕機的問題。

## 新增

- [#4014](https://github.com/hyperf/hyperf/pull/4014) [#4080](https://github.com/hyperf/hyperf/pull/4080) 為 `kafka` 組件增加 `sasl` 和 `ssl` 的支持。
- [#4045](https://github.com/hyperf/hyperf/pull/4045) [#4082](https://github.com/hyperf/hyperf/pull/4082) 為 `tracer` 組件新增配置 `opentracing.enable.exception`，用來判斷是否收集異常信息。
- [#4086](https://github.com/hyperf/hyperf/pull/4086) 支持收集接口 `Interface` 的註解信息。

# 優化

- [#4084](https://github.com/hyperf/hyperf/pull/4084) 優化了註解找不到時的錯誤信息。

# v2.2.8 - 2021-09-14

## 修復

- [#4028](https://github.com/hyperf/hyperf/pull/4028) 修復 `grafana` 面板中，請求數結果計算錯誤的問題。
- [#4030](https://github.com/hyperf/hyperf/pull/4030) 修復異步隊列會因為解壓縮模型失敗，導致進程中斷隨後重啓的問題。
- [#4042](https://github.com/hyperf/hyperf/pull/4042) 修復因 `SocketIO` 服務關閉時清理過期的 `fd`，進而導致協程死鎖的問題。

## 新增

- [#4013](https://github.com/hyperf/hyperf/pull/4013) 為 `Cookies` 增加 `sameSite=None` 的支持。
- [#4017](https://github.com/hyperf/hyperf/pull/4017) 為 `Hyperf\Utils\Collection` 增加 `Macroable`。
- [#4021](https://github.com/hyperf/hyperf/pull/4021) 為 `retry()` 方法中 `$callback` 匿名函數增加 `$attempts` 變量。
- [#4040](https://github.com/hyperf/hyperf/pull/4040) 為 `AMQP` 組件新增方法 `ConsumerDelayedMessageTrait::getDeadLetterExchange()`，可以用來重寫 `x-dead-letter-exchange` 參數。

## 移除

- [#4017](https://github.com/hyperf/hyperf/pull/4017) 從 `Hyperf\Database\Model\Collection` 中移除 `Macroable`，因為它的基類 `Hyperf\Utils\Collection` 已引入了對應的 `Macroable`。

# v2.2.7 - 2021-09-06

# 修復

- [#3997](https://github.com/hyperf/hyperf/pull/3997) 修復 `Nats` 消費者會在連接超時後崩潰的問題。
- [#3998](https://github.com/hyperf/hyperf/pull/3998) 修復 `Apollo` 不支持 `https` 協議的問題。

## 優化

- [#4009](https://github.com/hyperf/hyperf/pull/4009) 優化方法 `MethodDefinitionCollector::getOrParse()`，避免在 PHP8 環境下，觸發即將廢棄的錯誤。

## 新增

- [#4002](https://github.com/hyperf/hyperf/pull/4002) [#4012](https://github.com/hyperf/hyperf/pull/4012) 為驗證器增加場景功能，允許不同場景下，使用不同的驗證規則。
- [#4011](https://github.com/hyperf/hyperf/pull/4011) 為工具類 `Hyperf\Utils\Str` 增加了一些新的便捷方法。

# v2.2.6 - 2021-08-30

## 修復

- [#3969](https://github.com/hyperf/hyperf/pull/3969) 修復 PHP8 環境下使用 `Hyperf\Validation\Rules\Unique::__toString()` 導致類型錯誤的問題。
- [#3979](https://github.com/hyperf/hyperf/pull/3979) 修復熔斷器組件，`timeout` 變量無法使用的問題。 
- [#3986](https://github.com/hyperf/hyperf/pull/3986) 修復文件系統組件，開啓 `SWOOLE_HOOK_NATIVE_CURL` 後導致 OSS hook 失敗的問題。

## 新增

- [#3987](https://github.com/hyperf/hyperf/pull/3987) AMQP 組件支持延時隊列。
- [#3989](https://github.com/hyperf/hyperf/pull/3989) [#3992](https://github.com/hyperf/hyperf/pull/3992) 為熱更新組件新增了配置 `command`，可以用來定義自己的啓動腳本，支持 [nano](https://github.com/hyperf/nano) 組件。

# v2.2.5 - 2021-08-23

## 修復

- [#3959](https://github.com/hyperf/hyperf/pull/3959) 修復驗證器規則 `date` 在入參為 `string` 時，無法正常使用的問題。
- [#3960](https://github.com/hyperf/hyperf/pull/3960) 修復協程風格服務下，`Crontab` 無法平滑關閉的問題。

## 新增

- [code-generator](https://github.com/hyperf/code-generator) 新增組件 `code-generator`，可以用來將 `Doctrine` 註解轉化為 `PHP8` 的原生註解。

## 優化

- [#3957](https://github.com/hyperf/hyperf/pull/3957) 使用命令 `gen:model` 生成 `getAttribute` 註釋時，支持基於 `@return` 註釋返回對應的類型。

# v2.2.4 - 2021-08-16

## 修復

- [#3925](https://github.com/hyperf/hyperf/pull/3925) 修復 `Nacos` 開啓 `light beat` 功能後，心跳失敗的問題。
- [#3926](https://github.com/hyperf/hyperf/pull/3926) 修復配置項 `config_center.drivers.nacos.client` 無法正常工作的問題。

## 新增

- [#3924](https://github.com/hyperf/hyperf/pull/3924) 為 `Consul` 服務註冊中心增加配置項 `services.drivers.consul.check`。
- [#3932](https://github.com/hyperf/hyperf/pull/3932) 為 `AMQP` 消費者增加重新入隊列的配置，允許用户返回 `NACK` 後，消息重入隊列。
- [#3941](https://github.com/hyperf/hyperf/pull/3941) 允許多路複用的 `RPC` 組件使用註冊中心的能力。
- [#3947](https://github.com/hyperf/hyperf/pull/3947) 新增方法 `Str::mask`，允許用户對一段文本某段內容打馬賽克。

## 優化

- [#3944](https://github.com/hyperf/hyperf/pull/3944) 封裝了讀取 `Aspect` 元數據的方法。

# v2.2.3 - 2021-08-09

## 修復

- [#3897](https://github.com/hyperf/hyperf/pull/3897) 修復因為 `lightBeatEnabled` 導致心跳失敗，進而導致 `Nacos` 服務註冊多次的問題。
- [#3905](https://github.com/hyperf/hyperf/pull/3905) 修復 `AMQP` 連接在關閉時導致空指針的問題。
- [#3906](https://github.com/hyperf/hyperf/pull/3906) 修復 `AMQP` 連接關閉時，因已經銷燬所有等待通道而導致失敗的問題。
- [#3908](https://github.com/hyperf/hyperf/pull/3908) 修復使用了以 `CoordinatorManager` 為基礎的循環邏輯時，自定義進程無法正常重啓的問題。

# v2.2.2 - 2021-08-03

## 修復

- [#3872](https://github.com/hyperf/hyperf/pull/3872) [#3873](https://github.com/hyperf/hyperf/pull/3873) 修復使用 `Nacos` 服務時，因為沒有使用默認的組名，導致心跳失敗的問題。
- [#3877](https://github.com/hyperf/hyperf/pull/3877) 修復 `Nacos` 服務，心跳會被註冊多次的問題。
- [#3879](https://github.com/hyperf/hyperf/pull/3879) 修復熱更新因為代理類被覆蓋，導致無法正常使用的問題。

## 優化

- [#3877](https://github.com/hyperf/hyperf/pull/3877) 為 `Nacos` 服務，增加 `lightBeatEnabled` 支持。

# v2.2.1 - 2021-07-27

## 修復

- [#3750](https://github.com/hyperf/hyperf/pull/3750) 修復使用 `SocketIO` 時，由於觸發了一個不存在的命名空間，而導致致命錯誤的問題。
- [#3828](https://github.com/hyperf/hyperf/pull/3828) 修復在 `PHP 8.0` 版本中，無法對 `Hyperf\Redis\Redis` 使用懶加載注入的問題。
- [#3845](https://github.com/hyperf/hyperf/pull/3845) 修復 `watcher` 組件無法在 `v2.2` 版本中正常使用的問題。
- [#3848](https://github.com/hyperf/hyperf/pull/3848) 修復 `Nacos` 組件無法像 `v2.1` 版本註冊自身到 `Nacos` 服務中的問題。
- [#3866](https://github.com/hyperf/hyperf/pull/3866) 修復 `Nacos` 實例無法正常註冊元數據的問題。

## 優化

- [#3763](https://github.com/hyperf/hyperf/pull/3763) 使 `JsonResource::wrap()` 和 `JsonResource::withoutWrapping()` 支持鏈式調用。
- [#3843](https://github.com/hyperf/hyperf/pull/3843) 在 `Nacos` 註冊服務時，根據 `HTTP` 響應的返回碼和數據協同判斷，以確保是否已註冊過。
- [#3854](https://github.com/hyperf/hyperf/pull/3854) 為文件下載方法支持 `RFC 5987`，它允許使用 `UTF-8` 格式和 `URL` 格式化。
