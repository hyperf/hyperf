# 版本更新記錄

# v3.0.39 - 2023-10-13

## 新增

- [#6188](https://github.com/hyperf/hyperf/pull/6188) 為 `Redis` 的 options 配置增加 string 類型 key 支持。
- [#6193](https://github.com/hyperf/hyperf/pull/6193) 為 `Swow` 服務增加 HTTP 和 WebSocket 雙協議端口支持。
- [#6198](https://github.com/hyperf/hyperf/pull/6198) 為 `hyperf/tracer` 組件增加 `RpcAspect` 用以替代 `JsonRpcAspect`。
- [#6200](https://github.com/hyperf/hyperf/pull/6200) 為 `hyperf/tracer` 組件增加 `ElasticserachAspect` 和 `CoroutineAspect` 的開關。
- [#6203](https://github.com/hyperf/hyperf/pull/6203) 為 `hyperf/tracer` 組件增加 `Hyperf\Tracer\Aspect\GrpcAspect`。
- [#6207](https://github.com/hyperf/hyperf/pull/6207) 為 `kafka` 組件增加 `exception_callback` 配置。

# v3.0.38 - 2023-10-05

## 修復

- [#6183](https://github.com/hyperf/hyperf/pull/6183) 修復使用單測組件時，中間件權重無法正常使用的問題。
- [#6185](https://github.com/hyperf/hyperf/pull/6185) 修復使用 `socketio-server` 時，`cleanUpExpiredOnce` 當 `sids` 為空時，無法正常使用的問題。

## 優化

- [#6177](https://github.com/hyperf/hyperf/pull/6177) 使 `hyperf/codec` 組件 `Base62` 工具類，可以更加便捷的被繼承重寫。

# v3.0.37 - 2023-09-22

## 新增

- [#6156](https://github.com/hyperf/hyperf/pull/6156) 為 `stringable` 組件增加了 `Str::replaceStart()` 等方法。

## 優化

- [#6154](https://github.com/hyperf/hyperf/pull/6154) 在使用驗證器組件時，如果原生方法 `json_validate` 存在，則使用其進行 `Json` 格式驗證。
- [#6157](https://github.com/hyperf/hyperf/pull/6157) 在使用 `trace` 組件時，只記錄打開記錄異常開關，並且不在忽略列表中的信息。

## 修復

- [#6160](https://github.com/hyperf/hyperf/pull/6160) 修復當設置配置 `services.enable.register` 為 `false` 時，仍然會發布服務到服務中心的問題。
- [#6162](https://github.com/hyperf/hyperf/pull/6162) 修復使用 `Crontab` 的時，當執行任務超過默認超時時間後，則不能很好的進行控制任務運行時機的問題。

# v3.0.36 - 2023-09-15

# 新增

- [#6062](https://github.com/hyperf/hyperf/pull/6057) 為 `hyperf/tracer` 新增 `RequestTraceListener` 監聽器。
- [#6143](https://github.com/hyperf/hyperf/pull/6143) 為 `hyperf/tracer` 增加 `ignore_exceptions` 配置。

## 優化

- [#6151](https://github.com/hyperf/hyperf/pull/6151) 優化 `hyperf/kafka` 組件中 `FailToConsume` 的觸發時機。

## 修復

- [#6117](https://github.com/hyperf/hyperf/pull/6117) 修復 `GRPC` 客户端無法被複用的問題。
- [#6146](https://github.com/hyperf/hyperf/pull/6146) 修復 `validateJson` 驗證規則在高於 PHP 8.0 版本後，無法正常使用的問題。

# v3.0.35 - 2023-09-01

## 修復

- [#6097](https://github.com/hyperf/hyperf/pull/6097) 修復使用非 `zipkin` 的 `tracer` 組件時，會出現報錯的問題。
- [#6099](https://github.com/hyperf/hyperf/pull/6099) 修復使用 `Redis` 時，恆定頻率釋放鏈接模式無法正常使用的問題。
- [#6110](https://github.com/hyperf/hyperf/pull/6110) 修復使用 `Nacos GRPC` 的配置中心時，多進程模式無法正常工作的問題。

## 新增

- [#6096](https://github.com/hyperf/hyperf/pull/6096) 為 `Crontab` 組件中的事件增加 `getThrowable` 方法。
- [#6094](https://github.com/hyperf/hyperf/pull/6094) 新增驗證器規則 `ExcludeIf` `File` `ImageFile` 和 `ProhibitedIf`。
- [#6112](https://github.com/hyperf/hyperf/pull/6112) 為 `Hyperf\Kafka\Producer` 增加 `sendAsync` 和 `sendBatchAsync` 兩個新方法。

## 優化

- [#6098](https://github.com/hyperf/hyperf/pull/6098) 優化 `hyperf/tracer` `kafka` 上報器，增加其穩定性。
- [#6100](https://github.com/hyperf/hyperf/pull/6100) 優化 `hyperf/tracer` `HTTP` 上報器，增加其性能和穩定性。
- [#6108](https://github.com/hyperf/hyperf/pull/6108) 優化命令 `describe:routes`，展示排序後的中間件。
- [#6111](https://github.com/hyperf/hyperf/pull/6111) 為 `tracer` 上報器，增加日誌輸出能力。

# v3.0.34 - 2023-08-25

## 新增

- [#6060](https://github.com/hyperf/hyperf/pull/6060) 為 `tracer` 組件增加 `request.uri` 標籤。
- [#6063](https://github.com/hyperf/hyperf/pull/6063) 為 `Request` 相關事件，增加服務名參數 `$server`。
- [#6070](https://github.com/hyperf/hyperf/pull/6070) 為組件 `hyperf/rpc-multilex` 增加 `php_serialize` 協議。
- [#6069](https://github.com/hyperf/hyperf/pull/6069) [#6075](https://github.com/hyperf/hyperf/pull/6075) 為組件 `hyperf/tracer` 增加 `kafka` 上報器。
- [#6078](https://github.com/hyperf/hyperf/pull/6078) 新增方法 `Hyperf\Support\Composer::hasPackage()`。
- [#6083](https://github.com/hyperf/hyperf/pull/6083) [#6084](https://github.com/hyperf/hyperf/pull/6084) 支持中間件排序功能。

## 修復

- [#6065](https://github.com/hyperf/hyperf/pull/6065) 修復方法 `Context::override` 和 `Context::getOrSet` 沒法對指定協程 ID 使用的問題。

## 優化

- [#6046](https://github.com/hyperf/hyperf/pull/6046) 從協程上線文中讀取 `tracer` 實例。
- [#6061](https://github.com/hyperf/hyperf/pull/6061) 為 `server` 配置，增加 `key-value` 模式的支持。
- [#6077](https://github.com/hyperf/hyperf/pull/6077) 當使用 `#[Hyperf\Constants\Annotation\Constants]` 時，避免 `IDE` 觸發 `deprecated` 警告。

# v3.0.33 - 2023-08-18

## 修復

- [#6011](https://github.com/hyperf/hyperf/pull/6011) 修復 `invocable` 控制器路由無法正常使用驗證器的 BUG。
- [#6013](https://github.com/hyperf/hyperf/pull/6013) 修復 `no_aspect` 會被覆蓋的問題。
- [#6053](https://github.com/hyperf/hyperf/pull/6053) 修復方法 `Arr::has` 時，`Interger` 類型參數會導致報錯的問題。

## 優化

- [#6023](https://github.com/hyperf/hyperf/pull/6023) 使用 `Tracer` 實例時，優先從協程上下文中獲取。
- [#6027](https://github.com/hyperf/hyperf/pull/6027) 優化協程下 `Tracer` 的使用邏輯。

## 即將廢棄

- [#6044](https://github.com/hyperf/hyperf/pull/6044) 設置 `Hyperf\Coroutine\Traits\Container` 為即將廢棄。

# v3.0.32 - 2023-08-09

## 新增

- [#5996](https://github.com/hyperf/hyperf/pull/5996) 允許 `tracer` 切入 `GuzzleHttp\Client::request()` 方法，進行數據記錄。

## 修復

- [#6004](https://github.com/hyperf/hyperf/pull/6004) 修復在使用 `Command` 時，拋出異常後，命令行退出碼不合規的問題。

# v3.0.31 - 2023-07-27

## 修復

- [#5969](https://github.com/hyperf/hyperf/pull/5969) 修復使用 `Str::contains` 時，如果 `$needles` 為 `[null]` 則會導致判斷錯誤的問題。
- [#5970](https://github.com/hyperf/hyperf/pull/5970) 修復使用 `Str::startsWith` 和 `Str::endsWith` 時，如果 `$needles` 為 `[null]` 則會導致判斷錯誤的問題。

## 新增

- [#5971](https://github.com/hyperf/hyperf/pull/5971) 新增方法 `Str::containsIgnoreCase()` 可以在不區分大小寫的情況下，用來判斷是否是包含關係。

# v3.0.30 - 2023-07-21

## 修復

- [#5947](https://github.com/hyperf/hyperf/pull/5947) 修復使用 `amqp` 時，存在多個配置時，協程鎖失效的問題。

## 優化

- [#5954](https://github.com/hyperf/hyperf/pull/5954) 優化模型生成器，使其生成正確的參數註釋。

## 新增

- [#5951](https://github.com/hyperf/hyperf/pull/5951) 為 `Session` 的 `Cookies` 功能增加 `SameSite` 支持。
- [#5955](https://github.com/hyperf/hyperf/pull/5955) 為 `Nacos` 服務註冊與發現，增加 `access_key` 和 `access_secret` 的支持。
- [#5957](https://github.com/hyperf/hyperf/pull/5957) 新增 `Hyperf\Codec\Packer\IgbinarySerializerPacker`。
- [#5962](https://github.com/hyperf/hyperf/pull/5962) 當使用測試組件時，增加支持修改子協程上下文的能力。

# v3.0.29 - 2023-07-14

## 修復

- [#5921](https://github.com/hyperf/hyperf/pull/5921) 修復 `http2-client` 在沒有開啓心跳時，無法正常關閉的問題。
- [#5923](https://github.com/hyperf/hyperf/pull/5923) 修復 `nacos grpc client` 當進程退出時，無法友好關閉的問題。
- [#5922](https://github.com/hyperf/hyperf/pull/5922) 修復使用 `grpc-client` 時，會找不到 `ApplicationContext` 的問題。

## 優化

- [#5924](https://github.com/hyperf/hyperf/pull/5924) 當進程退出時，隱藏 `nacos grpc client` 相關的正常的錯誤信息。

# v3.0.28 - 2023-07-08

## 修復

- [#5909](https://github.com/hyperf/hyperf/pull/5909) 修復 `ACM` 配置中心因 `client::$servers` 沒有進行初始化而報錯的問題。
- [#5911](https://github.com/hyperf/hyperf/pull/5911) 修復 `Nacos Grpc 客户端` 權限驗證失敗的問題。
- [#5912](https://github.com/hyperf/hyperf/pull/5912) 修復 `Nacos Grpc 客户端` 在 `Nacos 服務` 重啓後，重連失敗的問題。

## 新增

- [#5895](https://github.com/hyperf/hyperf/pull/5895) 為驗證器規則 `Integer` 和 `Boolean` 增加嚴格模式。

## 優化

- [#5910](https://github.com/hyperf/hyperf/pull/5910) 優化工廠類 `NacosClientFactory`，使其實例化 `NacosClient` 而非 `Nacos Application` 對象。

# v3.0.27 - 2023-06-30

## 修復

- [#5880](https://github.com/hyperf/hyperf/pull/5880) 修復因 `Swagger` 服務名隨機成為數字時，導致服務無法正常啓動的問題。
- [#5890](https://github.com/hyperf/hyperf/pull/5890) 增加了部分，需要重連 `PDO` 的錯誤信息，避免 `PDO` 鏈接無法重連的問題。

## 優化

- [#5886](https://github.com/hyperf/hyperf/pull/5886) 當使用 `hyperf/db` 連接 `clickhouse` 時，如果 `SQL` 執行錯誤，則會拋出異常。

# v3.0.26 - 2023-06-24

## 修復

- [#5861](https://github.com/hyperf/hyperf/pull/5861) 修復緩存組件中，使用 `CoroutineMemory` 時，`CoroutineMemory::clearPrefix()` 無法正常工作的問題。

## 優化

- [#5858](https://github.com/hyperf/hyperf/pull/5858) 當調用數據庫組件中 `chunkById` 時，如果 `Id` 為 `Null`，則拋出異常。

# v3.0.25 - 2023-06-19

## 修復

- [#5829](https://github.com/hyperf/hyperf/pull/5829) 修復 `Hyperf\Database\Model\Builder::value()` 當使用形如 `table.column` 的字段時，無法正常使用的問題。
- [#5831](https://github.com/hyperf/hyperf/pull/5831) 修復在特殊場景下 `socket.io` 組件在解析 `namespace` 時，會造成死循環的問題。

# v3.0.24 - 2023-06-10

## 修復

- [#5794](https://github.com/hyperf/hyperf/pull/5794) 修復代理類中 `__FILE__` 和 `__DIR__` 定位錯誤的問題。
- [#5803](https://github.com/hyperf/hyperf/pull/5803) 修復組件 `hyperf/http-server` 不適配新版本 `Psr7` 的問題。
- [#5808](https://github.com/hyperf/hyperf/pull/5808) 修復驗證器規則 `le`、`lte`、`gt`、`gte` 不會正常比較 `numeric` 和 `string`。

## 優化

- [#5789](https://github.com/hyperf/hyperf/pull/5789) 支持高版本 `psr/http-message`。
- [#5806](https://github.com/hyperf/hyperf/pull/5806) 優化 Swow 服務，默認情況下合併通用配置。
- [#5814](https://github.com/hyperf/hyperf/pull/5814) 增加方法 `build_sql`，在拋出異常 `QueryException` 時，可以快速的構建 `SQL` .

# v3.0.23 - 2023-06-02

## 新增

- [#5757](https://github.com/hyperf/hyperf/pull/5757) 支持 `Nacos` 服務註冊與發現簽名機制。
- [#5765](https://github.com/hyperf/hyperf/pull/5765) 為 `database` 組件增加全文檢索的功能。

## 修復

- [#5782](https://github.com/hyperf/hyperf/pull/5782) 修復 `prometheus` 無法正常收集 `histograms` 的問題。

## 優化

- [#5768](https://github.com/hyperf/hyperf/pull/5768) 為 `Hyperf\Command\Annotation\Command` 組件增加參數支持。
- [#5780](https://github.com/hyperf/hyperf/pull/5780) 修復 `Zipkin\Propagation\Map` 中 `String` 類型檢測錯誤的問題。

# v3.0.22 - 2023-05-27

## 新增

- [#5760](https://github.com/hyperf/hyperf/pull/5760) 為組件 `hyperf/translation` 組件的助手函數增加命名空間。
- [#5761](https://github.com/hyperf/hyperf/pull/5761) 新增方法 `Hyperf\Coordinator\Timer::until()`.

## 優化

- [#5741](https://github.com/hyperf/hyperf/pull/5741) 為 `Hyperf\DB\MySQLConnection` 增加即將過期的標籤。
- [#5702](https://github.com/hyperf/hyperf/pull/5702) 優化了 `Hyperf\Metric\Adapter\Prometheus\Redis` 的代碼，使其允許被重寫 `KEY` 鍵前綴。
- [#5762](https://github.com/hyperf/hyperf/pull/5762) 自定義進程默認使用非阻塞模式。

# v3.0.21 - 2023-05-18

## 新增

- [#5721](https://github.com/hyperf/hyperf/pull/5721) 為 `Request` 生命週期事件，增加 `exception` 參數。
- [#5723](https://github.com/hyperf/hyperf/pull/5723) 為 `hyperf/db` 組件增加 `Swoole5.x` 的 `PgSQL` 支持。
- [#5725](https://github.com/hyperf/hyperf/pull/5725) 為 `hyperf/db` 組件增加 `Swoole4.x` 的 `PgSQL` 支持。
- [#5731](https://github.com/hyperf/hyperf/pull/5731) 新增方法 `Arr::hasAny()`。

## 修復

- [#5726](https://github.com/hyperf/hyperf/pull/5726) [#5730](https://github.com/hyperf/hyperf/pull/5730) 修復使用 `pgsql-swoole` 類型的 `ORM` 時，`PgSQL` 鏈接不會自動初始化的問題。

## 優化

- [#5718](https://github.com/hyperf/hyperf/pull/5718) 優化了 `view-engine` 組件的代碼，並增加了一些單元測試。
- [#5719](https://github.com/hyperf/hyperf/pull/5719) 優化了 `metric` 組件的代碼，並增加了一些單元測試。
- [#5720](https://github.com/hyperf/hyperf/pull/5720) 優化了 `Hyperf\Metric\Listener\OnPipeMessage` 的代碼，來避免消息阻塞的問題。

# v3.0.20 - 2023-05-12

## 新增

- [#5707](https://github.com/hyperf/hyperf/pull/5707) 新增助手函數 `Hyperf\Config\config`。
- [#5711](https://github.com/hyperf/hyperf/pull/5711) 新增方法 `Arr::mapWithKeys()`。
- [#5715](https://github.com/hyperf/hyperf/pull/5715) 增加請求級別生命週期事件。

## 修復

- [#5709](https://github.com/hyperf/hyperf/pull/5709) 當日志組不存在時，修復錯誤日誌記錄有誤的問題。
- [#5713](https://github.com/hyperf/hyperf/pull/5713) 為 `Hyperf\SuperGlobals\Proxy\Server` 增加通過自身進行實例化的能力。

## 優化

- [#5716](https://github.com/hyperf/hyperf/pull/5716) 為協程風格服務增加超全局變量的支持。

# v3.0.19 - 2023-05-06

## 修復

- [#5679](https://github.com/hyperf/hyperf/pull/5679) 修復 `#[Task]` 註解的 `$timeout` 類型與 `TaskAspect` 不一致的問題。
- [#5684](https://github.com/hyperf/hyperf/pull/5684) 修復使用了 `break` 語法後，`blade` 視圖模板無法正常使用的問題。

## 新增

- [#5680](https://github.com/hyperf/hyperf/pull/5680) 為 `rpc-multiplex` 增加存儲 `RPC` 上下文的能力。
- [#5695](https://github.com/hyperf/hyperf/pull/5695) 為數據庫遷移組件，增加設置 `datetime` 類型的創建時間和修改時間的功能。
- [#5699](https://github.com/hyperf/hyperf/pull/5699) 增加 `Model::resolveRelationUsing()`，用來動態創建模型關係。

## 優化

- [#5694](https://github.com/hyperf/hyperf/pull/5694) 將 `hyperf/utils` 從 `hyperf/rpc` 組件中移除。
- [#5696](https://github.com/hyperf/hyperf/pull/5694) 使用 `Hyperf\Coroutine\Coroutine::sleep()` 替代 `Swoole\Coroutine::sleep()`。

# v3.0.18 - 2023-04-26

## 新增

- [#5672](https://github.com/hyperf/hyperf/pull/5672) 將部分 `utils` 中的方法，複製到 `hyperf/support` 組件中，並增加對應的命名空間。

## 修復

- [#5662](https://github.com/hyperf/hyperf/pull/5662) 修復 `pgsql-swoole` 執行失敗時，無法拋出異常的問題。

## 優化

- [#5660](https://github.com/hyperf/hyperf/pull/5660) 將 `hyperf/codec` 從 `hyperf/utils` 分離出來。
- [#5663](https://github.com/hyperf/hyperf/pull/5663) 將 `hyperf/serializer` 從 `hyperf/utils` 分離出來。
- [#5666](https://github.com/hyperf/hyperf/pull/5666) 將 `Packers` 從 `hyperf/utils` 分離到 `hyperf/codec` 中。
- [#5668](https://github.com/hyperf/hyperf/pull/5668) 將 `hyperf/support` 從 `hyperf/utils` 分離出來。
- [#5670](https://github.com/hyperf/hyperf/pull/5670) 將 `hyperf/code-parser` 從 `hyperf/utils` 分離出來。
- [#5671](https://github.com/hyperf/hyperf/pull/5671) 使用 `Hyperf\Coroutine\Channel\Pool` 代替 `Hyperf\Utils\ChannelPool` 。
- [#5674](https://github.com/hyperf/hyperf/pull/5674) 將 `Hyperf\Utils` 命名空間的類和方法，使用新組件進行替換。

# v3.0.17 - 2023-04-19

## 修復

- [#5642](https://github.com/hyperf/hyperf/pull/5642) 修復使用批量讀取模型緩存時，遇到不存在的數據時，無法初始化空緩存的問題。
- [#5643](https://github.com/hyperf/hyperf/pull/5643) 修復使用批量讀取模型緩存時，空緩存無法正常使用的問題。
- [#5649](https://github.com/hyperf/hyperf/pull/5649) 修復協程風格下，無法初始化數據庫字段收集器的問題。

## 新增

- [#5634](https://github.com/hyperf/hyperf/pull/5634) 新增助手函數 `Hyperf\Stringable\str()`。
- [#5639](https://github.com/hyperf/hyperf/pull/5639) 新增方法 `Redis::pipeline()` 和 `Redis::transaction()`。
- [#5641](https://github.com/hyperf/hyperf/pull/5641) 為模型緩存 `loadCache` 增加嵌套初始化緩存的能力。
- [#5646](https://github.com/hyperf/hyperf/pull/5646) 增加 `PriorityDefinition` 類，來處理容器 `dependencies` 優先級的問題。

## 優化

- [#5634](https://github.com/hyperf/hyperf/pull/5634) 使用 `Hyperf\Stringable\Str` 替代 `Hyperf\Utils\Str`。
- [#5636](https://github.com/hyperf/hyperf/pull/5636) 優化 `kafka` 消費者，啓動時等待消費過長的問題。
- [#5648](https://github.com/hyperf/hyperf/pull/5648) 將依賴 `hyperf/utils` 從 `hyperf/guzzle` 中移除。

# v3.0.16 - 2023-04-12

## 修復

- [#5627](https://github.com/hyperf/hyperf/pull/5627) 修復方法 `Hyperf\Context\Context::destroy` 支持協程下調用。

## 優化

- [#5616](https://github.com/hyperf/hyperf/pull/5616) 將 `ApplicationContext` 從 `hyperf/utils` 分離到 `hyperf/context`。
- [#5617](https://github.com/hyperf/hyperf/pull/5617) 將 `hyperf/guzzle` 從 `hyperf/consul` 依賴中移除。
- [#5618](https://github.com/hyperf/hyperf/pull/5618) 支持在 Swagger 面板中設置默認路由。
- [#5619](https://github.com/hyperf/hyperf/pull/5619) [#5620](https://github.com/hyperf/hyperf/pull/5620) 將 `hyperf/coroutine` 從 `hyperf/utils` 分離出來。
- [#5621](https://github.com/hyperf/hyperf/pull/5621) 使用 `Hyperf\Context\ApplicationContext` 代替 `Hyperf\Utils\ApplicationContext`。
- [#5622](https://github.com/hyperf/hyperf/pull/5622) 將 `CoroutineProxy` 從 `hyperf/utils` 分離到 `hyperf/context`。
- [#5623](https://github.com/hyperf/hyperf/pull/5623) 使用 `Hyperf\Coroutine\Coroutine` 替代 `Hyperf\Utils\Coroutine`。
- [#5624](https://github.com/hyperf/hyperf/pull/5624) 將 `Channel` 相關方法從 `hyperf/utils` 分離到 `hyperf/coroutine`。
- [#5629](https://github.com/hyperf/hyperf/pull/5629) 將 `Hyperf\Utils\Arr` 繼承 `Hyperf\Collection\Arr`。

# v3.0.15 - 2023-04-07

## 新增

- [#5606](https://github.com/hyperf/hyperf/pull/5606) 新增配置 `server.options.send_channel_capacity` 用來控制使用 `協程風格` 服務時，是否使用 `SafeSocket` 來返回數據。

## 優化

- [#5593](https://github.com/hyperf/hyperf/pull/5593) [#5598](https://github.com/hyperf/hyperf/pull/5598) 使用 `Hyperf\Collection\Collection` 替代 `Hyperf\Utils\Collection`。
- [#5594](https://github.com/hyperf/hyperf/pull/5594) 使用 `Hyperf\Collection\Arr` 替代 `Hyperf\Utils\Arr`。
- [#5596](https://github.com/hyperf/hyperf/pull/5596) 將 `hyperf/pipeline` 從 `hyperf/utils` 分離出來。
- [#5599](https://github.com/hyperf/hyperf/pull/5599) 使用 `Hyperf\Pipeline\Pipeline` 替代 `Hyperf\Utils\Pipeline`。

# v3.0.14 - 2023-04-01

## 修復

- [#5578](https://github.com/hyperf/hyperf/pull/5578) 修復了無法序列化 `Crontab` 的問題。
- [#5579](https://github.com/hyperf/hyperf/pull/5579) 修復 `crontab:run` 無法正常工作的問題。

## 優化

- [#5572](https://github.com/hyperf/hyperf/pull/5572) 優化了 `HTTP` 服務，使用 `WritableConnection` 實現，支持 `Swow`。
- [#5577](https://github.com/hyperf/hyperf/pull/5577) 將組件 `hyperf/collection` 從 `hyperf/utils` 分離。
- [#5580](https://github.com/hyperf/hyperf/pull/5580) 將組件 `hyperf/conditionable` 和 `hyperf/tappable` 從 `hyperf/utils` 分離。
- [#5585](https://github.com/hyperf/hyperf/pull/5585) 優化 `service-governance` 組件，去除了 `consul` 的依賴關係。

# v3.0.13 - 2023-03-26

## 新增

- [#5561](https://github.com/hyperf/hyperf/pull/5561) 為 `hyperf/kafka` 增加自定義定時器的配置。
- [#5562](https://github.com/hyperf/hyperf/pull/5562) 為 `MySQL` 數據庫組件，增加 `upsert()` 支持。
- [#5563](https://github.com/hyperf/hyperf/pull/5563) 為 `Crontab` 任務增加是否執行完的邏輯。

## 優化

- [#5544](https://github.com/hyperf/hyperf/pull/5554) 為 `grpc-server` 組件取消 `hyperf/rpc` 的依賴。
- [#5550](https://github.com/hyperf/hyperf/pull/5550) 優化了 `Coordinator Timer` 和 `Crontab Parser` 的代碼。
- [#5566](https://github.com/hyperf/hyperf/pull/5566) 基於模型生成 `Swagger Schemas` 時，優化變量類型可以為 `Null`。
- [#5569](https://github.com/hyperf/hyperf/pull/5569) 優化了 `Crontab RunCommand` 的依賴關係。

# v3.0.12 - 2023-03-20

## 新增

- [#4112](https://github.com/hyperf/hyperf/pull/4112) 新增配置項 `kafka.default.enable` 用來控制消費者是否啓動。
- [#5533](https://github.com/hyperf/hyperf/pull/5533) [#5535](https://github.com/hyperf/hyperf/pull/5535) 為 `kafka` 組件增加 `client` 和 `socket` 配置，允許開發者自定義。
- [#5536](https://github.com/hyperf/hyperf/pull/5536) 新增組件 `hyperf/http2-client`。
- [#5538](https://github.com/hyperf/hyperf/pull/5538) 為 `hyperf/http2-client` 增加雙向流支持。
- [#5511](https://github.com/hyperf/hyperf/pull/5511) 將 `GRPC` 服務統一到 `RPC` 服務中，可以更加方便的進行服務註冊與發現。
- [#5543](https://github.com/hyperf/hyperf/pull/5543) 增加 `Nacos` 雙向流支持，可以監聽到配置中心實時更新的事件。
- [#5545](https://github.com/hyperf/hyperf/pull/5545) 為組件 `hyperf/http2-client` 增加雙向流相關的測試。
- [#5546](https://github.com/hyperf/hyperf/pull/5546) 為 `Nacos` 配置中心增加 `GRPC` 功能，可以實時監聽配置的變化。

## 優化

- [#5539](https://github.com/hyperf/hyperf/pull/5539) 優化了 `AMQPConnection` 的代碼，以支持最新版本的 `php-amqplib` 組件。
- [#5528](https://github.com/hyperf/hyperf/pull/5528) 優化了 `aspects` 的配置，對熱重啓有更好的支持。
- [#5541](https://github.com/hyperf/hyperf/pull/5541) 提升了 `FactoryResolver` 基於 `XXXFactory` 實例化對象的能力，增加了可選參數配置。

# v3.0.11 - 2023-03-15

## 新增

- [#5499](https://github.com/hyperf/hyperf/pull/5499) 為 `hyperf/constants` 組件增加枚舉(>=PHP8.1)類型支持。
- [#5508](https://github.com/hyperf/hyperf/pull/5508) 新增方法 `Hyperf\Rpc\Protocol::getNormalizer`。
- [#5509](https://github.com/hyperf/hyperf/pull/5509) 為 `json-rpc` 組件自動註冊 `normalizer`。
- [#5513](https://github.com/hyperf/hyperf/pull/5513) 組件 `rpc-multiplex` 使用默認的 `normalizer` 並對 `rpc-server` 增加自定義 `protocol.normalizer` 的支持。
- [#5518](https://github.com/hyperf/hyperf/pull/5518) 增加方法 `SwooleConnection::getSocket` 用來獲取 `Swoole` 的 `Response`。
- [#5520](https://github.com/hyperf/hyperf/pull/5520) 新增方法 `Coroutine::stats()` 和 `Coroutine::exists()`。
- [#5525](https://github.com/hyperf/hyperf/pull/5525) 新增配置 `kafka.default.consume_timeout` 用來控制消費者消費數據的超時時間。
- [#5526](https://github.com/hyperf/hyperf/pull/5526) 新增方法 `Hyperf\Kafka\AbstractConsumer::isEnable()` 用來控制 `kafka` 消費者是否啓動。

## 修復

- [#5519](https://github.com/hyperf/hyperf/pull/5519) 修復因 `kafka` 生產者 `loop` 方法導致進程無法正常退出的問題。
- [#5523](https://github.com/hyperf/hyperf/pull/5523) 修復在發生 `kafka rebalance` 的時候，進程無故停止的問題。

## 優化

- [#5510](https://github.com/hyperf/hyperf/pull/5510) 允許開發者自定義 `RPC 客户端` 的 `normalizer` 的實現。
- [#5525](https://github.com/hyperf/hyperf/pull/5525) 當消費 `kafka` 消息時，每個消息會在獨立的協程中進行處理。

# v3.0.10 - 2023-03-11

## 修復

- [#5497](https://github.com/hyperf/hyperf/pull/5497) 修復 `apollo` 配置中心，無法正常觸發 `ConfigChanged` 事件的問題。

## 新增

- [#5491](https://github.com/hyperf/hyperf/pull/5491) 為 `Str` 和 `Stringable` 新增 `charAt` 方法。
- [#5503](https://github.com/hyperf/hyperf/pull/5503) 新增 `Hyperf\Contract\JsonDeSerializable`。
- [#5504](https://github.com/hyperf/hyperf/pull/5504) 新增 `Hyperf\Utils\Serializer\JsonDeNormalizer`。

## 優化

- [#5493](https://github.com/hyperf/hyperf/pull/5493) 優化 `Nacos` 服務註冊器的代碼，使其支持 `1.x` 和 `2.x` 版本。
- [#5494](https://github.com/hyperf/hyperf/pull/5494) [#5501](https://github.com/hyperf/hyperf/pull/5501) 優化 `hyperf/guzzle` 組件，當使用 `Swoole` 且不支持 `native-curl` 時，才會默認替換 `Handler`。

## 變更

- [#5492](https://github.com/hyperf/hyperf/pull/5492) 將 `Hyperf\DbConnection\Listener\CreatingListener` 重命名為 `Hyperf\DbConnection\Listener\InitUidOnCreatingListener`.

# v3.0.9 - 2023-03-05

## 新增

- [#5467](https://github.com/hyperf/hyperf/pull/5467) 為 `GRPC` 增加 `Google\Rpc\Status` 的支持。
- [#5472](https://github.com/hyperf/hyperf/pull/5472) 為模型增加 `ulid` 和 `uuid` 的支持。
- [#5476](https://github.com/hyperf/hyperf/pull/5476) 為 `Stringable` 增加 `ArrayAccess` 的支持。
- [#5478](https://github.com/hyperf/hyperf/pull/5478) 為 `Stringable` 和 `Str` 增加 `isMatch` 方法。

## 優化

- [#5469](https://github.com/hyperf/hyperf/pull/5469) 當數據庫連接出現問題時，確保連接在歸還到連接池前被重置。

# v3.0.8 - 2023-02-26

## 修復

- [#5433](https://github.com/hyperf/hyperf/pull/5433) [#5438](https://github.com/hyperf/hyperf/pull/5438) 修復 `Nacos` 臨時實例，不需要發送心跳的問題。 
- [#5464](https://github.com/hyperf/hyperf/pull/5464) 修復 `Swagger` 服務無法在異步風格中，正常啓動的問題。

## 新增

- [#5434](https://github.com/hyperf/hyperf/pull/5434) 為 `Swow` 增加 `UDP` 服務的支持。
- [#5444](https://github.com/hyperf/hyperf/pull/5444) 新增腳本 `GenSchemaCommand` 用來生成 `Swagger Schema`。
- [#5451](https://github.com/hyperf/hyperf/pull/5451) 為模型集合新增 `appends($attributes)` 方法。
- [#5453](https://github.com/hyperf/hyperf/pull/5453) 為測試組件增加 `put()` 和 `patch()` 方法。
- [#5454](https://github.com/hyperf/hyperf/pull/5454) 為 `GRPC` 組件新增方法 `Hyperf\Grpc\Parser::statusFromResponse`。
- [#5459](https://github.com/hyperf/hyperf/pull/5459) 為 `Str` 和 `Stringable` 新增方法 `uuid` 和 `ulid`。

## 優化

- [#5437](https://github.com/hyperf/hyperf/pull/5437) 為 `Str::length` 移除了沒用的 `if` 判斷。
- [#5439](https://github.com/hyperf/hyperf/pull/5439) 優化了 `Arr::shuffle` 的代碼。

# v3.0.7 - 2023-02-18

## 新增

- [#5042](https://github.com/hyperf/hyperf/pull/5402) 為 `Swagger` 組件增加配置 `swagger.scan.paths` 可以用來重寫默認的掃描目錄。
- [#5403](https://github.com/hyperf/hyperf/pull/5403) 為 `Swow` 增加 `Swoole Server` 配置項的適配。
- [#5404](https://github.com/hyperf/hyperf/pull/5404) 為 `Swagger` 增加多端口服務的支持。
- [#5406](https://github.com/hyperf/hyperf/pull/5406) 為 `Hyperf\Database\Model\Builder` 增加 `mixin` 方法。
- [#5407](https://github.com/hyperf/hyperf/pull/5407) 為 `Swagger` 增加請求方法 `Delete` 和 `Options` 的支持。
- [#5409](https://github.com/hyperf/hyperf/pull/5409) 為數據庫組件中 `Query\Builder` 和 `Paginator` 類增加了一部分方法。
- [#5414](https://github.com/hyperf/hyperf/pull/5414) 為 `Hyperf\Database\Model\Builder` 增加了 `clone` 方法。
- [#5418](https://github.com/hyperf/hyperf/pull/5418) 為配置中心增加了 `ConfigChanged` 事件。
- [#5429](https://github.com/hyperf/hyperf/pull/5429) 在連接 `Aliyun Nacos` 服務時，增加了配置項 `access_key` 和 `access_secret`。

## 修復

- [#5405](https://github.com/hyperf/hyperf/pull/5405) 修復了當系統支持 `IPv6` 時，`get local ip` 無法正常讀取 ip 的問題。
- [#5417](https://github.com/hyperf/hyperf/pull/5417) 修復 `PgSQL` 無法正常使用數據庫遷移功能的問題。
- [#5421](https://github.com/hyperf/hyperf/pull/5421) 修復數據庫 `Json` 結構無法正常使用 `boolean` 類型的問題。
- [#5428](https://github.com/hyperf/hyperf/pull/5428) 修復 `Metric` 中間件遇到異常時，服務端參數統計有誤的問題。
- [#5424](https://github.com/hyperf/hyperf/pull/5424) 修復數據庫遷移組件，不支持 `PHP8.2` 的問題。

## 優化

- [#5411](https://github.com/hyperf/hyperf/pull/5411) 優化代碼，異常 `WebSocketHandeShakeException` 應繼承 `BadRequestHttpException`。
- [#5419](https://github.com/hyperf/hyperf/pull/5419) 優化 `RPN` 組件的實現邏輯，可以更好的進行自定義擴展。
- [#5422](https://github.com/hyperf/hyperf/pull/5422) 當安裝 `Swagger` 組件後，默認啓動 `Swagger` 的能力。

# v3.0.6 - 2023-02-12

## 修復

- [#5361](https://github.com/hyperf/hyperf/pull/5361) 修復 `Nacos` 注入臨時實例失敗的問題。
- [#5382](https://github.com/hyperf/hyperf/pull/5382) 修復 `SocketIO` 中使用 `mix-subscriber` 時，因為沒有設置密碼而報錯的問題。
- [#5386](https://github.com/hyperf/hyperf/pull/5386) 修復 `SwoolePostgresqlClient` 會被執行到不存在的方法 `exec` 的問題。
- [#5394](https://github.com/hyperf/hyperf/pull/5394) 修復 `hyperf/config-apollo` 無法正常使用的問題。

## 新增

- [#5366](https://github.com/hyperf/hyperf/pull/5366) 為 `hyperf/database` 增加 `forceDeleting` 事件。
- [#5373](https://github.com/hyperf/hyperf/pull/5373) 為 `SwowServer` 增加 `settings` 配置。
- [#5376](https://github.com/hyperf/hyperf/pull/5376) 為 `hyperf/metric` 增加協程風格下服務狀態收集的能力。
- [#5379](https://github.com/hyperf/hyperf/pull/5379) 當 `Nacos` 心跳失敗時，增加日誌記錄。
- [#5389](https://github.com/hyperf/hyperf/pull/5389) 增加 `Swagger` 支持。
- [#5395](https://github.com/hyperf/hyperf/pull/5395) 為 `Swagger` 組件，增加驗證器功能。
- [#5397](https://github.com/hyperf/hyperf/pull/5397) 支持所有已知的 `Swagger` 註解。

# v3.0.5 - 2023-02-06

## 新增

- [#5338](https://github.com/hyperf/hyperf/pull/5338) 為 `SoftDeletingScope` 新增了 `addRestoreOrCreate` 方法。
- [#5349](https://github.com/hyperf/hyperf/pull/5349) 新增監聽器 `ResumeExitCoordinatorListener`。
- [#5355](https://github.com/hyperf/hyperf/pull/5355) 新增方法 `System::getCpuCoresNum()`。

## 修復

- [#5357](https://github.com/hyperf/hyperf/pull/5357) 修復在匿名函數中拋錯時，`coordinator` 定時器無法正常停止的問題。

## 優化

- [#5342](https://github.com/hyperf/hyperf/pull/5342) 優化了 `Redis` 哨兵模式的地址讀取方式。

# v3.0.4 - 2023-01-22

## 修復

- [#5332](https://github.com/hyperf/hyperf/pull/5332) 修復了 `PgSQLSwooleConnection::unprepared` 無法正常使用的問題。
- [#5333](https://github.com/hyperf/hyperf/pull/5333) 修復數據庫組件在閒置時間過長，連接斷開導致數據庫讀寫報錯的問題。

# v3.0.3 - 2023-01-16

## 修復

- [#5318](https://github.com/hyperf/hyperf/pull/5318) 修復在使用 PHP 8.1 版本時，限流器無法使用的問題。
- [#5324](https://github.com/hyperf/hyperf/pull/5324) 修復 MySQL 連接斷開時，數據庫組件無法使用的問題。
- [#5322](https://github.com/hyperf/hyperf/pull/5322) 修復 Kafka 消費者在沒有設置 `memberId` 等參數時，無法使用的問題。
- [#5327](https://github.com/hyperf/hyperf/pull/5327) 修復 PgSQL 在創建連接失敗時，導致類型錯誤的問題。

## 新增

- [#5314](https://github.com/hyperf/hyperf/pull/5314) 新增方法 `Hyperf\Coordinator\Timer::stats()`.
- [#5323](https://github.com/hyperf/hyperf/pull/5323) 新增方法 `Hyperf\Nacos\Provider\ConfigProvider::listener()`.

## 優化

- [#5308](https://github.com/hyperf/hyperf/pull/5308) [#5309](https://github.com/hyperf/hyperf/pull/5309) [#5310](https://github.com/hyperf/hyperf/pull/5310) [#5311](https://github.com/hyperf/hyperf/pull/5311) 為 `hyperf/metric` 增加協程服務的支持。
- [#5315](https://github.com/hyperf/hyperf/pull/5315) 增加 `hyperf/metric` 組件的監控指標。
- [#5326](https://github.com/hyperf/hyperf/pull/5326) 在循環中，收集服務當前的狀態。

# v3.0.2 - 2023-01-09

# 修復

- [#5305](https://github.com/hyperf/hyperf/pull/5305) 使用 `PolarDB` 讀寫分離時，修復因沒有修改數據的情況下，提交事務會導致此鏈接存在異常，但又被回收進連接池的問題。
- [#5307](https://github.com/hyperf/hyperf/pull/5307) 修復 `hyperf/metric` 組件中，`Timer::tick()` 的 `$timeout` 參數設置錯誤的問題。

## 優化

- [#5306](https://github.com/hyperf/hyperf/pull/5306) 當連接池回收連接失敗時，記錄日誌。

# v3.0.1 - 2023-01-09

## 修復

- [#5289](https://github.com/hyperf/hyperf/pull/5289) 修復使用 `Swow` 引擎時，`Signal` 組件無法使用的問題。
- [#5303](https://github.com/hyperf/hyperf/pull/5303) 修復 `SocketIO` 的 `Redis NSQ 適配器`，當首次使用，`topics` 為 `null` 時，無法正常工作的問題。

## 優化

- [#5287](https://github.com/hyperf/hyperf/pull/5287) 當服務端響應數據時，如果出現異常，則記錄對應日誌。
- [#5292](https://github.com/hyperf/hyperf/pull/5292) 為組件 `hyperf/metric` 增加 `Swow` 引擎的支持。
- [#5301](https://github.com/hyperf/hyperf/pull/5301) 優化 `Hyperf\Rpc\PathGenerator\PathGenerator` 的代碼實現。

# v3.0.0 - 2023-01-03

- [#4238](https://github.com/hyperf/hyperf/issues/4238) 更新所有組件 PHP 最低版本到 8.0
- [#5087](https://github.com/hyperf/hyperf/pull/5087) 支持 PHP 8.2

## BC breaks

- 框架移除了 `@Annotation` 的使用，全面使用 `PHP8` 的原生註解 `Attribute`。更新框架前，請確保已經全部替換到 PHP8 的原生註解。

我們提供了腳本，可以更加方便的將 `Doctrine Annotations` 替換為 `PHP8 Attributes`。

!> Note: 以下腳本只能在框架 2.2 版本下執行

```shell
composer require hyperf/code-generator
php bin/hyperf.php code:generate -D app
```

- 模型升級腳本

> 因為模型基類，增加了類型限制，所以你需要使用以下腳本，將所有模型更新到新的寫法。

```shell
composer require hyperf/code-generator
php vendor/bin/regenerate-models.php $PWD/app/Model
```

- 框架增加了很多類型限制，所以當你從 `2.2` 升級到 `3.0`版本時，你需要調用靜態檢測腳本，檢查並確保其可以正常工作。

```shell
composer analysis
```

- 框架基於 `gRPC` 標準修改了 `gRPC` 服務的 HTTP 返回碼。
