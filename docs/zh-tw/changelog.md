# 版本更新記錄

# v3.0.24 - 2023-06-10

## 修復

- [#5794](https://github.com/hyperf/hyperf/pull/5794) 修復代理類中 `__FILE__` 和 `__DIR__` 定位錯誤的問題。
- [#5803](https://github.com/hyperf/hyperf/pull/5803) 修復元件 `hyperf/http-server` 不適配新版本 `Psr7` 的問題。
- [#5808](https://github.com/hyperf/hyperf/pull/5808) 修復驗證器規則 `le`、`lte`、`gt`、`gte` 不發正常比較 `numeric` 和 `string`。

## 最佳化

- [#5789](https://github.com/hyperf/hyperf/pull/5789) 支援高版本 `psr/http-message`。
- [#5806](https://github.com/hyperf/hyperf/pull/5806) 最佳化 Swow 服務，預設情況下合併通用配置。
- [#5814](https://github.com/hyperf/hyperf/pull/5814) 增加方法 `build_sql`，在丟擲異常 `QueryException` 時，可以快速的構建 `SQL` .

# v3.0.23 - 2023-06-02

## 新增

- [#5757](https://github.com/hyperf/hyperf/pull/5757) 支援 `Nacos` 服務註冊與發現簽名機制。
- [#5765](https://github.com/hyperf/hyperf/pull/5765) 為 `database` 元件增加全文檢索的功能。

## 修復

- [#5782](https://github.com/hyperf/hyperf/pull/5782) 修復 `prometheus` 無法正常收集 `histograms` 的問題。

## 最佳化

- [#5768](https://github.com/hyperf/hyperf/pull/5768) 為 `Hyperf\Command\Annotation\Command` 元件增加引數支援。
- [#5780](https://github.com/hyperf/hyperf/pull/5780) 修復 `Zipkin\Propagation\Map` 中 `String` 型別檢測錯誤的問題。

# v3.0.22 - 2023-05-27

## 新增

- [#5760](https://github.com/hyperf/hyperf/pull/5760) 為元件 `hyperf/translation` 元件的助手函式增加名稱空間。
- [#5761](https://github.com/hyperf/hyperf/pull/5761) 新增方法 `Hyperf\Coordinator\Timer::until()`.

## 最佳化

- [#5741](https://github.com/hyperf/hyperf/pull/5741) 為 `Hyperf\DB\MySQLConnection` 增加即將過期的標籤。
- [#5702](https://github.com/hyperf/hyperf/pull/5702) 優化了 `Hyperf\Metric\Adapter\Prometheus\Redis` 的程式碼，使其允許被重寫 `KEY` 鍵字首。
- [#5762](https://github.com/hyperf/hyperf/pull/5762) 自定義程序預設使用非阻塞模式。

# v3.0.21 - 2023-05-18

## 新增

- [#5721](https://github.com/hyperf/hyperf/pull/5721) 為 `Request` 生命週期事件，增加 `exception` 引數。
- [#5723](https://github.com/hyperf/hyperf/pull/5723) 為 `hyperf/db` 元件增加 `Swoole5.x` 的 `PgSQL` 支援。
- [#5725](https://github.com/hyperf/hyperf/pull/5725) 為 `hyperf/db` 元件增加 `Swoole4.x` 的 `PgSQL` 支援。
- [#5731](https://github.com/hyperf/hyperf/pull/5731) 新增方法 `Arr::hasAny()`。

## 修復

- [#5726](https://github.com/hyperf/hyperf/pull/5726) [#5730](https://github.com/hyperf/hyperf/pull/5730) 修復使用 `pgsql-swoole` 型別的 `ORM` 時，`PgSQL` 連結不會自動初始化的問題。

## 最佳化

- [#5718](https://github.com/hyperf/hyperf/pull/5718) 優化了 `view-engine` 元件的程式碼，並增加了一些單元測試。
- [#5719](https://github.com/hyperf/hyperf/pull/5719) 優化了 `metric` 元件的程式碼，並增加了一些單元測試。
- [#5720](https://github.com/hyperf/hyperf/pull/5720) 優化了 `Hyperf\Metric\Listener\OnPipeMessage` 的程式碼，來避免訊息阻塞的問題。

# v3.0.20 - 2023-05-12

## 新增

- [#5707](https://github.com/hyperf/hyperf/pull/5707) 新增助手函式 `Hyperf\Config\config`。
- [#5711](https://github.com/hyperf/hyperf/pull/5711) 新增方法 `Arr::mapWithKeys()`。
- [#5715](https://github.com/hyperf/hyperf/pull/5715) 增加請求級別生命週期事件。

## 修復

- [#5709](https://github.com/hyperf/hyperf/pull/5709) 當日志組不存在時，修復錯誤日誌記錄有誤的問題。
- [#5713](https://github.com/hyperf/hyperf/pull/5713) 為 `Hyperf\SuperGlobals\Proxy\Server` 增加透過自身進行例項化的能力。

## 最佳化

- [#5716](https://github.com/hyperf/hyperf/pull/5716) 為協程風格服務增加超全域性變數的支援。

# v3.0.19 - 2023-05-06

## 修復

- [#5679](https://github.com/hyperf/hyperf/pull/5679) 修復 `#[Task]` 註解的 `$timeout` 型別與 `TaskAspect` 不一致的問題。
- [#5684](https://github.com/hyperf/hyperf/pull/5684) 修復使用了 `break` 語法後，`blade` 檢視模板無法正常使用的問題。

## 新增

- [#5680](https://github.com/hyperf/hyperf/pull/5680) 為 `rpc-multiplex` 增加儲存 `RPC` 上下文的能力。
- [#5695](https://github.com/hyperf/hyperf/pull/5695) 為資料庫遷移元件，增加設定 `datetime` 型別的建立時間和修改時間的功能。
- [#5699](https://github.com/hyperf/hyperf/pull/5699) 增加 `Model::resolveRelationUsing()`，用來動態建立模型關係。

## 最佳化

- [#5694](https://github.com/hyperf/hyperf/pull/5694) 將 `hyperf/utils` 從 `hyperf/rpc` 元件中移除。
- [#5696](https://github.com/hyperf/hyperf/pull/5694) 使用 `Hyperf\Coroutine\Coroutine::sleep()` 替代 `Swoole\Coroutine::sleep()`。

# v3.0.18 - 2023-04-26

## 新增

- [#5672](https://github.com/hyperf/hyperf/pull/5672) 將部分 `utils` 中的方法，複製到 `hyperf/support` 元件中，並增加對應的名稱空間。

## 修復

- [#5662](https://github.com/hyperf/hyperf/pull/5662) 修復 `pgsql-swoole` 執行失敗時，無法丟擲異常的問題。

## 最佳化

- [#5660](https://github.com/hyperf/hyperf/pull/5660) 將 `hyperf/codec` 從 `hyperf/utils` 分離出來。
- [#5663](https://github.com/hyperf/hyperf/pull/5663) 將 `hyperf/serializer` 從 `hyperf/utils` 分離出來。
- [#5666](https://github.com/hyperf/hyperf/pull/5666) 將 `Packers` 從 `hyperf/utils` 分離到 `hyperf/codec` 中。
- [#5668](https://github.com/hyperf/hyperf/pull/5668) 將 `hyperf/support` 從 `hyperf/utils` 分離出來。
- [#5670](https://github.com/hyperf/hyperf/pull/5670) 將 `hyperf/code-parser` 從 `hyperf/utils` 分離出來。
- [#5671](https://github.com/hyperf/hyperf/pull/5671) 使用 `Hyperf\Coroutine\Channel\Pool` 代替 `Hyperf\Utils\ChannelPool` 。
- [#5674](https://github.com/hyperf/hyperf/pull/5674) 將 `Hyperf\Utils` 名稱空間的類和方法，使用新元件進行替換。

# v3.0.17 - 2023-04-19

## 修復

- [#5642](https://github.com/hyperf/hyperf/pull/5642) 修復使用批次讀取模型快取時，遇到不存在的資料時，無法初始化空快取的問題。
- [#5643](https://github.com/hyperf/hyperf/pull/5643) 修復使用批次讀取模型快取時，空快取無法正常使用的問題。
- [#5649](https://github.com/hyperf/hyperf/pull/5649) 修復協程風格下，無法初始化資料庫欄位收集器的問題。

## 新增

- [#5634](https://github.com/hyperf/hyperf/pull/5634) 新增助手函式 `Hyperf\Stringable\str()`。
- [#5639](https://github.com/hyperf/hyperf/pull/5639) 新增方法 `Redis::pipeline()` 和 `Redis::transaction()`。
- [#5641](https://github.com/hyperf/hyperf/pull/5641) 為模型快取 `loadCache` 增加巢狀初始化快取的能力。
- [#5646](https://github.com/hyperf/hyperf/pull/5646) 增加 `PriorityDefinition` 類，來處理容器 `dependencies` 優先順序的問題。

## 最佳化

- [#5634](https://github.com/hyperf/hyperf/pull/5634) 使用 `Hyperf\Stringable\Str` 替代 `Hyperf\Utils\Str`。
- [#5636](https://github.com/hyperf/hyperf/pull/5636) 最佳化 `kafka` 消費者，啟動時等待消費過長的問題。
- [#5648](https://github.com/hyperf/hyperf/pull/5648) 將依賴 `hyperf/utils` 從 `hyperf/guzzle` 中移除。

# v3.0.16 - 2023-04-12

## 修復

- [#5627](https://github.com/hyperf/hyperf/pull/5627) 修復方法 `Hyperf\Context\Context::destroy` 支援協程下呼叫。

## 最佳化

- [#5616](https://github.com/hyperf/hyperf/pull/5616) 將 `ApplicationContext` 從 `hyperf/utils` 分離到 `hyperf/context`。
- [#5617](https://github.com/hyperf/hyperf/pull/5617) 將 `hyperf/guzzle` 從 `hyperf/consul` 依賴中移除。
- [#5618](https://github.com/hyperf/hyperf/pull/5618) 支援在 Swagger 面板中設定預設路由。
- [#5619](https://github.com/hyperf/hyperf/pull/5619) [#5620](https://github.com/hyperf/hyperf/pull/5620) 將 `hyperf/coroutine` 從 `hyperf/utils` 分離出來。
- [#5621](https://github.com/hyperf/hyperf/pull/5621) 使用 `Hyperf\Context\ApplicationContext` 代替 `Hyperf\Utils\ApplicationContext`。
- [#5622](https://github.com/hyperf/hyperf/pull/5622) 將 `CoroutineProxy` 從 `hyperf/utils` 分離到 `hyperf/context`。
- [#5623](https://github.com/hyperf/hyperf/pull/5623) 使用 `Hyperf\Coroutine\Coroutine` 替代 `Hyperf\Utils\Coroutine`。
- [#5624](https://github.com/hyperf/hyperf/pull/5624) 將 `Channel` 相關方法從 `hyperf/utils` 分離到 `hyperf/coroutine`。
- [#5629](https://github.com/hyperf/hyperf/pull/5629) 將 `Hyperf\Utils\Arr` 繼承 `Hyperf\Collection\Arr`。

# v3.0.15 - 2023-04-07

## 新增

- [#5606](https://github.com/hyperf/hyperf/pull/5606) 新增配置 `server.options.send_channel_capacity` 用來控制使用 `協程風格` 服務時，是否使用 `SafeSocket` 來返回資料。

## 最佳化

- [#5593](https://github.com/hyperf/hyperf/pull/5593) [#5598](https://github.com/hyperf/hyperf/pull/5598) 使用 `Hyperf\Collection\Collection` 替代 `Hyperf\Utils\Collection`。
- [#5594](https://github.com/hyperf/hyperf/pull/5594) 使用 `Hyperf\Collection\Arr` 替代 `Hyperf\Utils\Arr`。
- [#5596](https://github.com/hyperf/hyperf/pull/5596) 將 `hyperf/pipeline` 從 `hyperf/utils` 分離出來。
- [#5599](https://github.com/hyperf/hyperf/pull/5599) 使用 `Hyperf\Pipeline\Pipeline` 替代 `Hyperf\Utils\Pipeline`。

# v3.0.14 - 2023-04-01

## 修復

- [#5578](https://github.com/hyperf/hyperf/pull/5578) 修復了無法序列化 `Crontab` 的問題。
- [#5579](https://github.com/hyperf/hyperf/pull/5579) 修復 `crontab:run` 無法正常工作的問題。

## 最佳化

- [#5572](https://github.com/hyperf/hyperf/pull/5572) 優化了 `HTTP` 服務，使用 `WritableConnection` 實現，支援 `Swow`。
- [#5577](https://github.com/hyperf/hyperf/pull/5577) 將元件 `hyperf/collection` 從 `hyperf/utils` 分離。
- [#5580](https://github.com/hyperf/hyperf/pull/5580) 將元件 `hyperf/conditionable` 和 `hyperf/tappable` 從 `hyperf/utils` 分離。
- [#5585](https://github.com/hyperf/hyperf/pull/5585) 最佳化 `service-governance` 元件，去除了 `consul` 的依賴關係。

# v3.0.13 - 2023-03-26

## 新增

- [#5561](https://github.com/hyperf/hyperf/pull/5561) 為 `hyperf/kafka` 增加自定義定時器的配置。
- [#5562](https://github.com/hyperf/hyperf/pull/5562) 為 `MySQL` 資料庫元件，增加 `upsert()` 支援。
- [#5563](https://github.com/hyperf/hyperf/pull/5563) 為 `Crontab` 任務增加是否執行完的邏輯。

## 最佳化

- [#5544](https://github.com/hyperf/hyperf/pull/5554) 為 `grpc-server` 元件取消 `hyperf/rpc` 的依賴。
- [#5550](https://github.com/hyperf/hyperf/pull/5550) 優化了 `Coordinator Timer` 和 `Crontab Parser` 的程式碼。
- [#5566](https://github.com/hyperf/hyperf/pull/5566) 基於模型生成 `Swagger Schemas` 時，最佳化變數型別可以為 `Null`。
- [#5569](https://github.com/hyperf/hyperf/pull/5569) 優化了 `Crontab RunCommand` 的依賴關係。

# v3.0.12 - 2023-03-20

## 新增

- [#4112](https://github.com/hyperf/hyperf/pull/4112) 新增配置項 `kafka.default.enable` 用來控制消費者是否啟動。
- [#5533](https://github.com/hyperf/hyperf/pull/5533) [#5535](https://github.com/hyperf/hyperf/pull/5535) 為 `kafka` 元件增加 `client` 和 `socket` 配置，允許開發者自定義。
- [#5536](https://github.com/hyperf/hyperf/pull/5536) 新增元件 `hyperf/http2-client`。
- [#5538](https://github.com/hyperf/hyperf/pull/5538) 為 `hyperf/http2-client` 增加雙向流支援。
- [#5511](https://github.com/hyperf/hyperf/pull/5511) 將 `GRPC` 服務統一到 `RPC` 服務中，可以更加方便的進行服務註冊與發現。
- [#5543](https://github.com/hyperf/hyperf/pull/5543) 增加 `Nacos` 雙向流支援，可以監聽到配置中心實時更新的事件。
- [#5545](https://github.com/hyperf/hyperf/pull/5545) 為元件 `hyperf/http2-client` 增加雙向流相關的測試。
- [#5546](https://github.com/hyperf/hyperf/pull/5546) 為 `Nacos` 配置中心增加 `GRPC` 功能，可以實時監聽配置的變化。

## 最佳化

- [#5539](https://github.com/hyperf/hyperf/pull/5539) 優化了 `AMQPConnection` 的程式碼，以支援最新版本的 `php-amqplib` 元件。
- [#5528](https://github.com/hyperf/hyperf/pull/5528) 優化了 `aspects` 的配置，對熱重啟有更好的支援。
- [#5541](https://github.com/hyperf/hyperf/pull/5541) 提升了 `FactoryResolver` 基於 `XXXFactory` 例項化物件的能力，增加了可選引數配置。

# v3.0.11 - 2023-03-15

## 新增

- [#5499](https://github.com/hyperf/hyperf/pull/5499) 為 `hyperf/constants` 元件增加列舉(>=PHP8.1)型別支援。
- [#5508](https://github.com/hyperf/hyperf/pull/5508) 新增方法 `Hyperf\Rpc\Protocol::getNormalizer`。
- [#5509](https://github.com/hyperf/hyperf/pull/5509) 為 `json-rpc` 元件自動註冊 `normalizer`。
- [#5513](https://github.com/hyperf/hyperf/pull/5513) 元件 `rpc-multiplex` 使用預設的 `normalizer` 並對 `rpc-server` 增加自定義 `protocol.normalizer` 的支援。
- [#5518](https://github.com/hyperf/hyperf/pull/5518) 增加方法 `SwooleConnection::getSocket` 用來獲取 `Swoole` 的 `Response`。
- [#5520](https://github.com/hyperf/hyperf/pull/5520) 新增方法 `Coroutine::stats()` 和 `Coroutine::exists()`。
- [#5525](https://github.com/hyperf/hyperf/pull/5525) 新增配置 `kafka.default.consume_timeout` 用來控制消費者消費資料的超時時間。
- [#5526](https://github.com/hyperf/hyperf/pull/5526) 新增方法 `Hyperf\Kafka\AbstractConsumer::isEnable()` 用來控制 `kafka` 消費者是否啟動。

## 修復

- [#5519](https://github.com/hyperf/hyperf/pull/5519) 修復因 `kafka` 生產者 `loop` 方法導致程序無法正常退出的問題。
- [#5523](https://github.com/hyperf/hyperf/pull/5523) 修復在發生 `kafka rebalance` 的時候，程序無故停止的問題。

## 最佳化

- [#5510](https://github.com/hyperf/hyperf/pull/5510) 允許開發者自定義 `RPC 客戶端` 的 `normalizer` 的實現。
- [#5525](https://github.com/hyperf/hyperf/pull/5525) 當消費 `kafka` 訊息時，每個訊息會在獨立的協程中進行處理。

# v3.0.10 - 2023-03-11

## 修復

- [#5497](https://github.com/hyperf/hyperf/pull/5497) 修復 `apollo` 配置中心，無法正常觸發 `ConfigChanged` 事件的問題。

## 新增

- [#5491](https://github.com/hyperf/hyperf/pull/5491) 為 `Str` 和 `Stringable` 新增 `charAt` 方法。
- [#5503](https://github.com/hyperf/hyperf/pull/5503) 新增 `Hyperf\Contract\JsonDeSerializable`。
- [#5504](https://github.com/hyperf/hyperf/pull/5504) 新增 `Hyperf\Utils\Serializer\JsonDeNormalizer`。

## 最佳化

- [#5493](https://github.com/hyperf/hyperf/pull/5493) 最佳化 `Nacos` 服務註冊器的程式碼，使其支援 `1.x` 和 `2.x` 版本。
- [#5494](https://github.com/hyperf/hyperf/pull/5494) [#5501](https://github.com/hyperf/hyperf/pull/5501) 最佳化 `hyperf/guzzle` 元件，當使用 `Swoole` 且不支援 `native-curl` 時，才會預設替換 `Handler`。

## 變更

- [#5492](https://github.com/hyperf/hyperf/pull/5492) 將 `Hyperf\DbConnection\Listener\CreatingListener` 重新命名為 `Hyperf\DbConnection\Listener\InitUidOnCreatingListener`.

# v3.0.9 - 2023-03-05

## 新增

- [#5467](https://github.com/hyperf/hyperf/pull/5467) 為 `GRPC` 增加 `Google\Rpc\Status` 的支援。
- [#5472](https://github.com/hyperf/hyperf/pull/5472) 為模型增加 `ulid` 和 `uuid` 的支援。
- [#5476](https://github.com/hyperf/hyperf/pull/5476) 為 `Stringable` 增加 `ArrayAccess` 的支援。
- [#5478](https://github.com/hyperf/hyperf/pull/5478) 為 `Stringable` 和 `Str` 增加 `isMatch` 方法。

## 最佳化

- [#5469](https://github.com/hyperf/hyperf/pull/5469) 當資料庫連接出現問題時，確保連線在歸還到連線池前被重置。

# v3.0.8 - 2023-02-26

## 修復

- [#5433](https://github.com/hyperf/hyperf/pull/5433) [#5438](https://github.com/hyperf/hyperf/pull/5438) 修復 `Nacos` 臨時例項，不需要傳送心跳的問題。 
- [#5464](https://github.com/hyperf/hyperf/pull/5464) 修復 `Swagger` 服務無法在非同步風格中，正常啟動的問題。

## 新增

- [#5434](https://github.com/hyperf/hyperf/pull/5434) 為 `Swow` 增加 `UDP` 服務的支援。
- [#5444](https://github.com/hyperf/hyperf/pull/5444) 新增指令碼 `GenSchemaCommand` 用來生成 `Swagger Schema`。
- [#5451](https://github.com/hyperf/hyperf/pull/5451) 為模型集合新增 `appends($attributes)` 方法。
- [#5453](https://github.com/hyperf/hyperf/pull/5453) 為測試元件增加 `put()` 和 `patch()` 方法。
- [#5454](https://github.com/hyperf/hyperf/pull/5454) 為 `GRPC` 元件新增方法 `Hyperf\Grpc\Parser::statusFromResponse`。
- [#5459](https://github.com/hyperf/hyperf/pull/5459) 為 `Str` 和 `Stringable` 新增方法 `uuid` 和 `ulid`。

## 最佳化

- [#5437](https://github.com/hyperf/hyperf/pull/5437) 為 `Str::length` 移除了沒用的 `if` 判斷。
- [#5439](https://github.com/hyperf/hyperf/pull/5439) 優化了 `Arr::shuffle` 的程式碼。

# v3.0.7 - 2023-02-18

## 新增

- [#5042](https://github.com/hyperf/hyperf/pull/5402) 為 `Swagger` 元件增加配置 `swagger.scan.paths` 可以用來重寫預設的掃描目錄。
- [#5403](https://github.com/hyperf/hyperf/pull/5403) 為 `Swow` 增加 `Swoole Server` 配置項的適配。
- [#5404](https://github.com/hyperf/hyperf/pull/5404) 為 `Swagger` 增加多埠服務的支援。
- [#5406](https://github.com/hyperf/hyperf/pull/5406) 為 `Hyperf\Database\Model\Builder` 增加 `mixin` 方法。
- [#5407](https://github.com/hyperf/hyperf/pull/5407) 為 `Swagger` 增加請求方法 `Delete` 和 `Options` 的支援。
- [#5409](https://github.com/hyperf/hyperf/pull/5409) 為資料庫元件中 `Query\Builder` 和 `Paginator` 類增加了一部分方法。
- [#5414](https://github.com/hyperf/hyperf/pull/5414) 為 `Hyperf\Database\Model\Builder` 增加了 `clone` 方法。
- [#5418](https://github.com/hyperf/hyperf/pull/5418) 為配置中心增加了 `ConfigChanged` 事件。
- [#5429](https://github.com/hyperf/hyperf/pull/5429) 在連線 `Aliyun Nacos` 服務時，增加了配置項 `access_key` 和 `access_secret`。

## 修復

- [#5405](https://github.com/hyperf/hyperf/pull/5405) 修復了當系統支援 `IPv6` 時，`get local ip` 無法正常讀取 ip 的問題。
- [#5417](https://github.com/hyperf/hyperf/pull/5417) 修復 `PgSQL` 無法正常使用資料庫遷移功能的問題。
- [#5421](https://github.com/hyperf/hyperf/pull/5421) 修復資料庫 `Json` 結構無法正常使用 `boolean` 型別的問題。
- [#5428](https://github.com/hyperf/hyperf/pull/5428) 修復 `Metric` 中介軟體遇到異常時，服務端引數統計有誤的問題。
- [#5424](https://github.com/hyperf/hyperf/pull/5424) 修復資料庫遷移元件，不支援 `PHP8.2` 的問題。

## 最佳化

- [#5411](https://github.com/hyperf/hyperf/pull/5411) 最佳化程式碼，異常 `WebSocketHandeShakeException` 應繼承 `BadRequestHttpException`。
- [#5419](https://github.com/hyperf/hyperf/pull/5419) 最佳化 `RPN` 元件的實現邏輯，可以更好的進行自定義擴充套件。
- [#5422](https://github.com/hyperf/hyperf/pull/5422) 當安裝 `Swagger` 元件後，預設啟動 `Swagger` 的能力。

# v3.0.6 - 2023-02-12

## 修復

- [#5361](https://github.com/hyperf/hyperf/pull/5361) 修復 `Nacos` 注入臨時例項失敗的問題。
- [#5382](https://github.com/hyperf/hyperf/pull/5382) 修復 `SocketIO` 中使用 `mix-subscriber` 時，因為沒有設定密碼而報錯的問題。
- [#5386](https://github.com/hyperf/hyperf/pull/5386) 修復 `SwoolePostgresqlClient` 會被執行到不存在的方法 `exec` 的問題。
- [#5394](https://github.com/hyperf/hyperf/pull/5394) 修復 `hyperf/config-apollo` 無法正常使用的問題。

## 新增

- [#5366](https://github.com/hyperf/hyperf/pull/5366) 為 `hyperf/database` 增加 `forceDeleting` 事件。
- [#5373](https://github.com/hyperf/hyperf/pull/5373) 為 `SwowServer` 增加 `settings` 配置。
- [#5376](https://github.com/hyperf/hyperf/pull/5376) 為 `hyperf/metric` 增加協程風格下服務狀態收集的能力。
- [#5379](https://github.com/hyperf/hyperf/pull/5379) 當 `Nacos` 心跳失敗時，增加日誌記錄。
- [#5389](https://github.com/hyperf/hyperf/pull/5389) 增加 `Swagger` 支援。
- [#5395](https://github.com/hyperf/hyperf/pull/5395) 為 `Swagger` 元件，增加驗證器功能。
- [#5397](https://github.com/hyperf/hyperf/pull/5397) 支援所有已知的 `Swagger` 註解。

# v3.0.5 - 2023-02-06

## 新增

- [#5338](https://github.com/hyperf/hyperf/pull/5338) 為 `SoftDeletingScope` 新增了 `addRestoreOrCreate` 方法。
- [#5349](https://github.com/hyperf/hyperf/pull/5349) 新增監聽器 `ResumeExitCoordinatorListener`。
- [#5355](https://github.com/hyperf/hyperf/pull/5355) 新增方法 `System::getCpuCoresNum()`。

## 修復

- [#5357](https://github.com/hyperf/hyperf/pull/5357) 修復在匿名函式中拋錯時，`coordinator` 定時器無法正常停止的問題。

## 最佳化

- [#5342](https://github.com/hyperf/hyperf/pull/5342) 優化了 `Redis` 哨兵模式的地址讀取方式。

# v3.0.4 - 2023-01-22

## 修復

- [#5332](https://github.com/hyperf/hyperf/pull/5332) 修復了 `PgSQLSwooleConnection::unprepared` 無法正常使用的問題。
- [#5333](https://github.com/hyperf/hyperf/pull/5333) 修復資料庫元件在閒置時間過長，連線斷開導致資料庫讀寫報錯的問題。

# v3.0.3 - 2023-01-16

## 修復

- [#5318](https://github.com/hyperf/hyperf/pull/5318) 修復在使用 PHP 8.1 版本時，限流器無法使用的問題。
- [#5324](https://github.com/hyperf/hyperf/pull/5324) 修復 MySQL 連線斷開時，資料庫元件無法使用的問題。
- [#5322](https://github.com/hyperf/hyperf/pull/5322) 修復 Kafka 消費者在沒有設定 `memberId` 等引數時，無法使用的問題。
- [#5327](https://github.com/hyperf/hyperf/pull/5327) 修復 PgSQL 在建立連線失敗時，導致型別錯誤的問題。

## 新增

- [#5314](https://github.com/hyperf/hyperf/pull/5314) 新增方法 `Hyperf\Coordinator\Timer::stats()`.
- [#5323](https://github.com/hyperf/hyperf/pull/5323) 新增方法 `Hyperf\Nacos\Provider\ConfigProvider::listener()`.

## 最佳化

- [#5308](https://github.com/hyperf/hyperf/pull/5308) [#5309](https://github.com/hyperf/hyperf/pull/5309) [#5310](https://github.com/hyperf/hyperf/pull/5310) [#5311](https://github.com/hyperf/hyperf/pull/5311) 為 `hyperf/metric` 增加協程服務的支援。
- [#5315](https://github.com/hyperf/hyperf/pull/5315) 增加 `hyperf/metric` 元件的監控指標。
- [#5326](https://github.com/hyperf/hyperf/pull/5326) 在迴圈中，收集服務當前的狀態。

# v3.0.2 - 2023-01-09

# 修復

- [#5305](https://github.com/hyperf/hyperf/pull/5305) 使用 `PolarDB` 讀寫分離時，修復因沒有修改資料的情況下，提交事務會導致此連結存在異常，但又被回收進連線池的問題。
- [#5307](https://github.com/hyperf/hyperf/pull/5307) 修復 `hyperf/metric` 元件中，`Timer::tick()` 的 `$timeout` 引數設定錯誤的問題。

## 最佳化

- [#5306](https://github.com/hyperf/hyperf/pull/5306) 當連線池回收連線失敗時，記錄日誌。

# v3.0.1 - 2023-01-09

## 修復

- [#5289](https://github.com/hyperf/hyperf/pull/5289) 修復使用 `Swow` 引擎時，`Signal` 元件無法使用的問題。
- [#5303](https://github.com/hyperf/hyperf/pull/5303) 修復 `SocketIO` 的 `Redis NSQ 介面卡`，當首次使用，`topics` 為 `null` 時，無法正常工作的問題。

## 最佳化

- [#5287](https://github.com/hyperf/hyperf/pull/5287) 當服務端響應資料時，如果出現異常，則記錄對應日誌。
- [#5292](https://github.com/hyperf/hyperf/pull/5292) 為元件 `hyperf/metric` 增加 `Swow` 引擎的支援。
- [#5301](https://github.com/hyperf/hyperf/pull/5301) 最佳化 `Hyperf\Rpc\PathGenerator\PathGenerator` 的程式碼實現。

# v3.0.0 - 2023-01-03

- [#4238](https://github.com/hyperf/hyperf/issues/4238) 更新所有元件 PHP 最低版本到 8.0
- [#5087](https://github.com/hyperf/hyperf/pull/5087) 支援 PHP 8.2

## BC breaks

- 框架移除了 `@Annotation` 的使用，全面使用 `PHP8` 的原生註解 `Attribute`。更新框架前，請確保已經全部替換到 PHP8 的原生註解。

我們提供了指令碼，可以更加方便的將 `Doctrine Annotations` 替換為 `PHP8 Attributes`。

!> Note: 以下指令碼只能在框架 2.2 版本下執行

```shell
composer require hyperf/code-generator
php bin/hyperf.php code:generate -D app
```

- 模型升級指令碼

> 因為模型基類，增加了型別限制，所以你需要使用以下指令碼，將所有模型更新到新的寫法。

```shell
composer require hyperf/code-generator
php vendor/bin/regenerate-models.php $PWD/app/Model
```

- 框架增加了很多型別限制，所以當你從 `2.2` 升級到 `3.0`版本時，你需要呼叫靜態檢測指令碼，檢查並確保其可以正常工作。

```shell
composer analysis
```

- 框架基於 `gRPC` 標準修改了 `gRPC` 服務的 HTTP 返回碼。

# v2.2.33 - 2022-05-30

## 修復

- [#4776](https://github.com/hyperf/hyperf/pull/4776) 修復 `GraphQL` 事件收集失敗的問題。
- [#4790](https://github.com/hyperf/hyperf/pull/4790) 修復 `RPN` 元件中方法 `toRPNExpression` 在某些場景無法正常工作的問題。

## Added

- [#4763](https://github.com/hyperf/hyperf/pull/4763) 新增驗證規則 `array:key1,key2`，確保陣列中除 `key1` `key2` 以外無其他 `key` 鍵。
- [#4781](https://github.com/hyperf/hyperf/pull/4781) 新增配置 `close-pull-request.yml`，用來自動關閉只讀的倉庫。

# v2.2.32 - 2022-05-16

## 修復

- [#4745](https://github.com/hyperf/hyperf/pull/4745) 當使用 `kafka` 元件的 `Producer::close` 方法時，修復可能丟擲空指標異常的問題。
- [#4754](https://github.com/hyperf/hyperf/pull/4754) 透過配置 `monolog>=2.6.0` 解決新版本的 `monolog` 無法正常工作的問題。

## 最佳化

- [#4738](https://github.com/hyperf/hyperf/pull/4738) 當使用 `kafka` 元件時，如果沒有設定 `GroupID` 則自動配置一個。

# v2.2.31 - 2022-04-18

## 修復

- [#4677](https://github.com/hyperf/hyperf/pull/4677) 修復使用 `kafka` 釋出者後，會導致程序無法正常退出的問題。
- [#4686](https://github.com/hyperf/hyperf/pull/4687) 修復使用 `WebSocket` 服務時，因為解析 `Request` 失敗會導致程序崩潰的問題。

## 新增

- [#4576](https://github.com/hyperf/hyperf/pull/4576) 為 `RPC` 客戶端的節點，增加路由字首 `path_prefix`。
- [#4683](https://github.com/hyperf/hyperf/pull/4683) 新增容器方法 `unbind()` 用來從容器中解綁物件。

# v2.2.30 - 2022-04-04

## 修復

- [#4648](https://github.com/hyperf/hyperf/pull/4648) 當使用 `retry` 元件中的熔斷器時，修復在 `open` 狀態下，無法自動呼叫 `fallback` 方法的問題。
- [#4657](https://github.com/hyperf/hyperf/pull/4657) 修復使用 `session` 中的檔案介面卡時，相同的 `Session ID` 在被重寫後，最後修改時間仍是上次修改時間的問題。

## 新增

- [#4646](https://github.com/hyperf/hyperf/pull/4646) 為 `Redis` 哨兵模式增加設定密碼的功能。

# v2.2.29 - 2022-03-28

## 修復

- [#4620](https://github.com/hyperf/hyperf/pull/4620) 修復 `Hyperf\Memory\LockManager::initialize()` 方法中，`$filename` 預設值錯誤的問題。

# v2.2.28 - 2022-03-14

## 修復

- [#4588](https://github.com/hyperf/hyperf/pull/4588) 修復 `database` 元件不支援 `bit` 型別的問題。
- [#4589](https://github.com/hyperf/hyperf/pull/4589) 修復使用 `Nacos` 時，無法正確的註冊臨時例項的問題。

## 新增

- [#4580](https://github.com/hyperf/hyperf/pull/4580) 新增方法 `Hyperf\Utils\Coroutine\Concurrent::getChannel()`。

## 最佳化

- [#4602](https://github.com/hyperf/hyperf/pull/4602) 將方法 `Hyperf\ModelCache\Manager::formatModels()` 更改為公共方法。

# v2.2.27 - 2022-03-07

## 最佳化

- [#4572](https://github.com/hyperf/hyperf/pull/4572) 當負載均衡器 `hyperf/load-balancer` 選擇節點失敗時，使用 `Hyperf\LoadBalancer\Exception\RuntimeException` 代替 `\RuntimeException`。

# v2.2.26 - 2022-02-21

## 修復

- [#4536](https://github.com/hyperf/hyperf/pull/4536) 修復使用 `JsonRPC` 時，會設定多次 `content-type` 的問題。

## 新增

- [#4527](https://github.com/hyperf/hyperf/pull/4527) 為 `Hyperf\Database\Schema\Blueprint` 增加了一些比較有用的方法。

## 最佳化

- [#4514](https://github.com/hyperf/hyperf/pull/4514) 透過使用小寫 `key` 獲取 `HTTP` 的 `Header` 資訊，提升一部分效能。
- [#4521](https://github.com/hyperf/hyperf/pull/4521) 在使用 Redis 的哨兵模式時，如果第一個哨兵節點連線失敗，則嘗試連線其餘哨兵節點。
- [#4529](https://github.com/hyperf/hyperf/pull/4529) 將元件 `hyperf/context` 從元件 `hyperf/utils` 中分離出來。

# v2.2.25 - 2022-01-30

## 修復

- [#4484](https://github.com/hyperf/hyperf/pull/4484) 修復使用 `Nacos v2.0.4` 版本時，服務是否註冊過，判斷有誤的問題。

## 新增

- [#4477](https://github.com/hyperf/hyperf/pull/4477) 為 `Hyperf\HttpServer\Request` 新增 `Macroable` 支援。

## 最佳化

- [#4254](https://github.com/hyperf/hyperf/pull/4254) 當使用 `Hyperf\Di\ScanHandlerPcntlScanHandler` 時，增加 `grpc.enable_fork_support` 檢測。

# v2.2.24 - 2022-01-24

## 修復

- [#4474](https://github.com/hyperf/hyperf/pull/4474) 修復使用多路複用 RPC 時，導致測試指令碼無法正常停止的問題。

## 最佳化

- [#4451](https://github.com/hyperf/hyperf/pull/4451) 優化了 `Hyperf\Watcher\Driver\FindNewerDriver` 的程式碼。

# v2.2.23 - 2022-01-17

## 修復

- [#4426](https://github.com/hyperf/hyperf/pull/4426) 修復 `view-engine` 模板引擎，在併發請求下導致模板快取生成錯誤的問題。

## 新增

- [#4449](https://github.com/hyperf/hyperf/pull/4449) 為 `Hyperf\Utils\Collection` 增加多條件排序的能力。
- [#4455](https://github.com/hyperf/hyperf/pull/4455) 新增命令 `gen:view-engine-cache` 可以預生成模板快取，避免併發帶來的一系列問題。
- [#4453](https://github.com/hyperf/hyperf/pull/4453) 新增 `Hyperf\Tracer\Aspect\ElasticserachAspect`，用來記錄 `elasticsearch` 客戶端的呼叫記錄。
- [#4458](https://github.com/hyperf/hyperf/pull/4458) 新增 `Hyperf\Di\ScanHandler\ProcScanHandler`，用來支援 `Windows` + `Swow` 環境下啟動服務。

# v2.2.22 - 2022-01-04

## 修復

- [#4399](https://github.com/hyperf/hyperf/pull/4399) 修復使用 `RedisCluster` 時，無法使用 `scan` 方法的問題。

## 新增

- [#4409](https://github.com/hyperf/hyperf/pull/4409) 為 `session` 增加資料庫支援。
- [#4411](https://github.com/hyperf/hyperf/pull/4411) 為 `tracer` 元件，新增 `Hyperf\Tracer\Aspect\DbAspect`，用於記錄 `hyperf/db` 元件產生的 `SQL` 日誌。
- [#4420](https://github.com/hyperf/hyperf/pull/4420) 為 `Hyperf\Amqp\IO\SwooleIO` 增加 `SSL` 支援。

## 最佳化

- [#4406](https://github.com/hyperf/hyperf/pull/4406) 刪除 `Swoole PSR-0` 風格程式碼，更加友好的支援 `Swoole 5.0` 版本。
- [#4429](https://github.com/hyperf/hyperf/pull/4429) 為 `Debug::getRefCount()` 方法增加型別檢測，只能用於輸出物件的 `RefCount`。

# v2.2.21 - 2021-12-20

## 修復

- [#4347](https://github.com/hyperf/hyperf/pull/4347) 修復使用 `AMQP` 元件時，如果連線緩衝區溢位，會導致連線被繫結到多個協程從而報錯的問題。
- [#4373](https://github.com/hyperf/hyperf/pull/4373) 修復使用 `Snowflake` 元件時，由於 `getWorkerId()` 中存在 `IO` 操作進而導致協程切換，最終導致元資料生成重複的問題。

## 新增

- [#4344](https://github.com/hyperf/hyperf/pull/4344) 新增事件 `Hyperf\Crontab\Event\FailToExecute`，此事件會在 `Crontab` 任務執行失敗時觸發。
- [#4348](https://github.com/hyperf/hyperf/pull/4348) 支援使用 `gen:*` 命令建立檔案時，自動吊起對應的 `IDE`，並開啟當前檔案。

## 最佳化

- [#4350](https://github.com/hyperf/hyperf/pull/4350) 優化了未開啟 `swoole.use_shortname` 時的錯誤資訊。
- [#4360](https://github.com/hyperf/hyperf/pull/4360) 將 `Hyperf\Amqp\IO\SwooleIO` 進行重構，使用更加穩定和高效的 `Swoole\Coroutine\Socket` 而非 `Swoole\Coroutine\Client`。

# v2.2.20 - 2021-12-13

## 修復

- [#4338](https://github.com/hyperf/hyperf/pull/4338) 修復使用單測客戶端時，路徑中帶有引數會導致無法正確匹配路由的問題。
- [#4346](https://github.com/hyperf/hyperf/pull/4346) 修復使用元件 `php-amqplib/php-amqplib:3.1.1` 時，啟動報錯的問題。

## 新增

- [#4330](https://github.com/hyperf/hyperf/pull/4330) 為 `phar` 元件支援打包 `vendor/bin` 目錄。
- [#4331](https://github.com/hyperf/hyperf/pull/4331) 新增方法 `Hyperf\Testing\Debug::getRefCount($object)`。

# v2.2.19 - 2021-12-06

## 修復

- [#4308](https://github.com/hyperf/hyperf/pull/4308) 修復執行 `server:watch` 時，因為使用相對路徑導致 `collector-reload` 檔案找不到的問題。

## 最佳化

- [#4317](https://github.com/hyperf/hyperf/pull/4317) 為 `Hyperf\Utils\Collection` 和 `Hyperf\Database\Model\Collection` 增強型別提示功能。

# v2.2.18 - 2021-11-29

## 修復

- [#4283](https://github.com/hyperf/hyperf/pull/4283) 修復當 `GRPC` 結果為 `null` 時，`Hyperf\Grpc\Parser::deserializeMessage()` 報錯的問題。

## 新增

- [#4284](https://github.com/hyperf/hyperf/pull/4284) 新增方法 `Hyperf\Utils\Network::ip()` 獲取本地 `IP`。
- [#4290](https://github.com/hyperf/hyperf/pull/4290) 為 `HTTP` 服務增加 `chunk` 功能。
- [#4291](https://github.com/hyperf/hyperf/pull/4291) 為 `value()` 方法增加動態引數功能。
- [#4293](https://github.com/hyperf/hyperf/pull/4293) 為 `server:watch` 命令增加相對路徑支援。
- [#4295](https://github.com/hyperf/hyperf/pull/4295) 為 `Hyperf\Database\Schema\Blueprint::bigIncrements()` 增加別名 `id()`。

# v2.2.17 - 2021-11-22

## 修復

- [#4243](https://github.com/hyperf/hyperf/pull/4243) 修復使用 `parallel` 時，結果集的順序與入參不一致的問題。

## 新增

- [#4109](https://github.com/hyperf/hyperf/pull/4109) 為 `hyperf/tracer` 增加 `PHP8` 的支援。
- [#4260](https://github.com/hyperf/hyperf/pull/4260) 為 `hyperf/database` 增加指定索引的功能。

# v2.2.16 - 2021-11-15

## 新增

- [#4252](https://github.com/hyperf/hyperf/pull/4252) 為 `Hyperf\RpcClient\AbstractServiceClient` 新增 `getServiceName()` 方法。

## 最佳化

- [#4253](https://github.com/hyperf/hyperf/pull/4253) 在掃描階段時，如果類庫找不到，則跳過且報出警告。

# v2.2.15 - 2021-11-08

## 修復

- [#4200](https://github.com/hyperf/hyperf/pull/4200) 修復當 `runtime/caches` 不是目錄時，使用檔案快取失敗的問題。

## 新增

- [#4157](https://github.com/hyperf/hyperf/pull/4157) 為 `Hyperf\Utils\Arr` 增加 `Macroable` 支援。

# v2.2.14 - 2021-11-01

## 新增

- [#4181](https://github.com/hyperf/hyperf/pull/4181) [#4192](https://github.com/hyperf/hyperf/pull/4192) 為框架增加 `psr/log` 元件版本 `v1.0`、`v2.0`、`v3.0` 的支援。

## 修復

- [#4171](https://github.com/hyperf/hyperf/pull/4171) 修復使用 `consul` 元件時，開啟 `ACL` 驗證後，健康檢測失敗的問題。
- [#4188](https://github.com/hyperf/hyperf/pull/4188) 修復使用 `composer 1.x` 版本時，打包 `phar` 失敗的問題。

# v2.2.13 - 2021-10-25

## 新增

- [#4159](https://github.com/hyperf/hyperf/pull/4159) 為 `Macroable::mixin` 方法增加引數 `$replace`，當其設定為 `false` 時，會優先判斷是否已經存在。

## 修復

- [#4158](https://github.com/hyperf/hyperf/pull/4158) 修復因為使用了 `Union` 型別，導致生成代理類失敗的問題。

## 最佳化

- [#4159](https://github.com/hyperf/hyperf/pull/4159) [#4166](https://github.com/hyperf/hyperf/pull/4166) 將元件 `hyperf/macroable` 從 `hyperf/utils` 中分離出來。

# v2.2.12 - 2021-10-18

## 新增

- [#4129](https://github.com/hyperf/hyperf/pull/4129) 新增方法 `Str::stripTags()` 和 `Stringable::stripTags()`。

## 修復

- [#4130](https://github.com/hyperf/hyperf/pull/4130) 修復生成模型時，因為使用了選項 `--with-ide` 和 `scope` 方法導致報錯的問題。
- [#4141](https://github.com/hyperf/hyperf/pull/4141) 修復驗證器工廠不支援其他驗證器的問題。

# v2.2.11 - 2021-10-11

## 修復

- [#4101](https://github.com/hyperf/hyperf/pull/4101) 修復 Nacos 使用的密碼攜帶特殊字元時，密碼會被 `urlencode` 導致密碼錯誤的問題。

# 最佳化

- [#4114](https://github.com/hyperf/hyperf/pull/4114) 最佳化 WebSocket 客戶端初始化失敗時的錯誤資訊。
- [#4119](https://github.com/hyperf/hyperf/pull/4119) 最佳化單測客戶端在上傳檔案時，因為預設的上傳路徑已經存在，導致報錯的問題（只發生在最新的 Swoole 版本中）。

# v2.2.10 - 2021-09-26

## 修復

- [#4088](https://github.com/hyperf/hyperf/pull/4088) 修復使用定時器規則時，會將空字串轉化為 `0` 的問題。
- [#4096](https://github.com/hyperf/hyperf/pull/4096) 修復當帶有型別的動態引數生成代理類時，會出現型別錯誤的問題。

# v2.2.9 - 2021-09-22

## 修復

- [#4061](https://github.com/hyperf/hyperf/pull/4061) 修復 `hyperf/metric` 元件與最新版本的 `prometheus_client_php` 存在衝突的問題。
- [#4068](https://github.com/hyperf/hyperf/pull/4068) 修復命令列丟擲錯誤時，退出碼與實際不符的問題。
- [#4076](https://github.com/hyperf/hyperf/pull/4076) 修復 `HTTP` 服務因返回資料不是標準 `HTTP` 協議時，導致服務宕機的問題。

## 新增

- [#4014](https://github.com/hyperf/hyperf/pull/4014) [#4080](https://github.com/hyperf/hyperf/pull/4080) 為 `kafka` 元件增加 `sasl` 和 `ssl` 的支援。
- [#4045](https://github.com/hyperf/hyperf/pull/4045) [#4082](https://github.com/hyperf/hyperf/pull/4082) 為 `tracer` 元件新增配置 `opentracing.enable.exception`，用來判斷是否收集異常資訊。
- [#4086](https://github.com/hyperf/hyperf/pull/4086) 支援收集介面 `Interface` 的註解資訊。

# 最佳化

- [#4084](https://github.com/hyperf/hyperf/pull/4084) 優化了註解找不到時的錯誤資訊。

# v2.2.8 - 2021-09-14

## 修復

- [#4028](https://github.com/hyperf/hyperf/pull/4028) 修復 `grafana` 面板中，請求數結果計算錯誤的問題。
- [#4030](https://github.com/hyperf/hyperf/pull/4030) 修復非同步佇列會因為解壓縮模型失敗，導致程序中斷隨後重啟的問題。
- [#4042](https://github.com/hyperf/hyperf/pull/4042) 修復因 `SocketIO` 服務關閉時清理過期的 `fd`，進而導致協程死鎖的問題。

## 新增

- [#4013](https://github.com/hyperf/hyperf/pull/4013) 為 `Cookies` 增加 `sameSite=None` 的支援。
- [#4017](https://github.com/hyperf/hyperf/pull/4017) 為 `Hyperf\Utils\Collection` 增加 `Macroable`。
- [#4021](https://github.com/hyperf/hyperf/pull/4021) 為 `retry()` 方法中 `$callback` 匿名函式增加 `$attempts` 變數。
- [#4040](https://github.com/hyperf/hyperf/pull/4040) 為 `AMQP` 元件新增方法 `ConsumerDelayedMessageTrait::getDeadLetterExchange()`，可以用來重寫 `x-dead-letter-exchange` 引數。

## 移除

- [#4017](https://github.com/hyperf/hyperf/pull/4017) 從 `Hyperf\Database\Model\Collection` 中移除 `Macroable`，因為它的基類 `Hyperf\Utils\Collection` 已引入了對應的 `Macroable`。

# v2.2.7 - 2021-09-06

# 修復

- [#3997](https://github.com/hyperf/hyperf/pull/3997) 修復 `Nats` 消費者會在連線超時後崩潰的問題。
- [#3998](https://github.com/hyperf/hyperf/pull/3998) 修復 `Apollo` 不支援 `https` 協議的問題。

## 最佳化

- [#4009](https://github.com/hyperf/hyperf/pull/4009) 最佳化方法 `MethodDefinitionCollector::getOrParse()`，避免在 PHP8 環境下，觸發即將廢棄的錯誤。

## 新增

- [#4002](https://github.com/hyperf/hyperf/pull/4002) [#4012](https://github.com/hyperf/hyperf/pull/4012) 為驗證器增加場景功能，允許不同場景下，使用不同的驗證規則。
- [#4011](https://github.com/hyperf/hyperf/pull/4011) 為工具類 `Hyperf\Utils\Str` 增加了一些新的便捷方法。

# v2.2.6 - 2021-08-30

## 修復

- [#3969](https://github.com/hyperf/hyperf/pull/3969) 修復 PHP8 環境下使用 `Hyperf\Validation\Rules\Unique::__toString()` 導致型別錯誤的問題。
- [#3979](https://github.com/hyperf/hyperf/pull/3979) 修復熔斷器元件，`timeout` 變數無法使用的問題。 
- [#3986](https://github.com/hyperf/hyperf/pull/3986) 修復檔案系統元件，開啟 `SWOOLE_HOOK_NATIVE_CURL` 後導致 OSS hook 失敗的問題。

## 新增

- [#3987](https://github.com/hyperf/hyperf/pull/3987) AMQP 元件支援延時佇列。
- [#3989](https://github.com/hyperf/hyperf/pull/3989) [#3992](https://github.com/hyperf/hyperf/pull/3992) 為熱更新元件新增了配置 `command`，可以用來定義自己的啟動指令碼，支援 [nano](https://github.com/hyperf/nano) 元件。

# v2.2.5 - 2021-08-23

## 修復

- [#3959](https://github.com/hyperf/hyperf/pull/3959) 修復驗證器規則 `date` 在入參為 `string` 時，無法正常使用的問題。
- [#3960](https://github.com/hyperf/hyperf/pull/3960) 修復協程風格服務下，`Crontab` 無法平滑關閉的問題。

## 新增

- [code-generator](https://github.com/hyperf/code-generator) 新增元件 `code-generator`，可以用來將 `Doctrine` 註解轉化為 `PHP8` 的原生註解。

## 最佳化

- [#3957](https://github.com/hyperf/hyperf/pull/3957) 使用命令 `gen:model` 生成 `getAttribute` 註釋時，支援基於 `@return` 註釋返回對應的型別。

# v2.2.4 - 2021-08-16

## 修復

- [#3925](https://github.com/hyperf/hyperf/pull/3925) 修復 `Nacos` 開啟 `light beat` 功能後，心跳失敗的問題。
- [#3926](https://github.com/hyperf/hyperf/pull/3926) 修復配置項 `config_center.drivers.nacos.client` 無法正常工作的問題。

## 新增

- [#3924](https://github.com/hyperf/hyperf/pull/3924) 為 `Consul` 服務註冊中心增加配置項 `services.drivers.consul.check`。
- [#3932](https://github.com/hyperf/hyperf/pull/3932) 為 `AMQP` 消費者增加重新入佇列的配置，允許使用者返回 `NACK` 後，訊息重入佇列。
- [#3941](https://github.com/hyperf/hyperf/pull/3941) 允許多路複用的 `RPC` 元件使用註冊中心的能力。
- [#3947](https://github.com/hyperf/hyperf/pull/3947) 新增方法 `Str::mask`，允許使用者對一段文字某段內容打馬賽克。

## 最佳化

- [#3944](https://github.com/hyperf/hyperf/pull/3944) 封裝了讀取 `Aspect` 元資料的方法。

# v2.2.3 - 2021-08-09

## 修復

- [#3897](https://github.com/hyperf/hyperf/pull/3897) 修復因為 `lightBeatEnabled` 導致心跳失敗，進而導致 `Nacos` 服務註冊多次的問題。
- [#3905](https://github.com/hyperf/hyperf/pull/3905) 修復 `AMQP` 連線在關閉時導致空指標的問題。
- [#3906](https://github.com/hyperf/hyperf/pull/3906) 修復 `AMQP` 連線關閉時，因已經銷燬所有等待通道而導致失敗的問題。
- [#3908](https://github.com/hyperf/hyperf/pull/3908) 修復使用了以 `CoordinatorManager` 為基礎的迴圈邏輯時，自定義程序無法正常重啟的問題。

# v2.2.2 - 2021-08-03

## 修復

- [#3872](https://github.com/hyperf/hyperf/pull/3872) [#3873](https://github.com/hyperf/hyperf/pull/3873) 修復使用 `Nacos` 服務時，因為沒有使用預設的組名，導致心跳失敗的問題。
- [#3877](https://github.com/hyperf/hyperf/pull/3877) 修復 `Nacos` 服務，心跳會被註冊多次的問題。
- [#3879](https://github.com/hyperf/hyperf/pull/3879) 修復熱更新因為代理類被覆蓋，導致無法正常使用的問題。

## 最佳化

- [#3877](https://github.com/hyperf/hyperf/pull/3877) 為 `Nacos` 服務，增加 `lightBeatEnabled` 支援。

# v2.2.1 - 2021-07-27

## 修復

- [#3750](https://github.com/hyperf/hyperf/pull/3750) 修復使用 `SocketIO` 時，由於觸發了一個不存在的名稱空間，而導致致命錯誤的問題。
- [#3828](https://github.com/hyperf/hyperf/pull/3828) 修復在 `PHP 8.0` 版本中，無法對 `Hyperf\Redis\Redis` 使用懶載入注入的問題。
- [#3845](https://github.com/hyperf/hyperf/pull/3845) 修復 `watcher` 元件無法在 `v2.2` 版本中正常使用的問題。
- [#3848](https://github.com/hyperf/hyperf/pull/3848) 修復 `Nacos` 元件無法像 `v2.1` 版本註冊自身到 `Nacos` 服務中的問題。
- [#3866](https://github.com/hyperf/hyperf/pull/3866) 修復 `Nacos` 例項無法正常註冊元資料的問題。

## 最佳化

- [#3763](https://github.com/hyperf/hyperf/pull/3763) 使 `JsonResource::wrap()` 和 `JsonResource::withoutWrapping()` 支援鏈式呼叫。
- [#3843](https://github.com/hyperf/hyperf/pull/3843) 在 `Nacos` 註冊服務時，根據 `HTTP` 響應的返回碼和資料協同判斷，以確保是否已註冊過。
- [#3854](https://github.com/hyperf/hyperf/pull/3854) 為檔案下載方法支援 `RFC 5987`，它允許使用 `UTF-8` 格式和 `URL` 格式化。

# v2.1.23 - 2021-07-12

## 最佳化

- [#3787](https://github.com/hyperf/hyperf/pull/3787) 最佳化 `JSON RPC` 服務，優先初始化 `PSR Response`，用於避免 `PSR Request` 初始化失敗後，無法從上下文中獲取 `Response` 的問題。

# v2.1.22 - 2021-06-28

## 安全性更新

- [#3723](https://github.com/hyperf/hyperf/pull/3723) 修復驗證器規則 `active_url` 無法正確檢查 `dns` 記錄，從而導致繞過驗證的問題。
- [#3724](https://github.com/hyperf/hyperf/pull/3724) 修復可以利用 `RequiredIf` 規則生成用於反序列化漏洞的小工具鏈的問題。

## 修復

- [#3721](https://github.com/hyperf/hyperf/pull/3721) 修復了驗證器規則 `in` 和 `not in` 判斷有誤的問題，例如規則為 `in:00` 時，`0`不應該被允許透過。

# v2.1.21 - 2021-06-21

## 修復

- [#3684](https://github.com/hyperf/hyperf/pull/3684) 修復使用熔斷器時，成功次數和失敗次數的界限判斷有誤的問題。

# v2.1.20 - 2021-06-07

## 修復

- [#3667](https://github.com/hyperf/hyperf/pull/3667) 修復形如 `10-12/1,14-15/1` 的定時任務規則無法正常使用的問題。
- [#3669](https://github.com/hyperf/hyperf/pull/3669) 修復了沒有反斜線形如 `10-12` 的定時任務規則無法正常使用的問題。
- [#3674](https://github.com/hyperf/hyperf/pull/3674) 修復 `@Task` 註解中，引數 `$workerId` 無法正常使用的問題。

## 最佳化

- [#3663](https://github.com/hyperf/hyperf/pull/3663) 最佳化 `AbstractServiceClient::getNodesFromConsul()` 方法，排除了可能找不到埠的隱患。
- [#3668](https://github.com/hyperf/hyperf/pull/3668) 最佳化 `Guzzle` 元件中 `CoroutineHandler` 代理相關的程式碼，增強其相容性。

# v2.1.19 - 2021-05-31

## 修復

- [#3618](https://github.com/hyperf/hyperf/pull/3618) 修復使用了相同路徑但不同實現邏輯的路由會在命令 `describe:routes` 中，被合併成一條的問題。
- [#3625](https://github.com/hyperf/hyperf/pull/3625) 修復 `Hyperf\Di\Annotation\Scanner` 中無法正常使用 `class_map` 功能的問題。

## 新增

- [#3626](https://github.com/hyperf/hyperf/pull/3626) 為 `RPC` 元件增加了新的路徑打包器 `Hyperf\Rpc\PathGenerator\DotPathGenerator`。

## 新元件孵化

- [nacos-sdk](https://github.com/hyperf/nacos-sdk-incubator) 基於 Nacos Open API 實現的 SDK。

# v2.1.18 - 2021-05-24

## 修復

- [#3598](https://github.com/hyperf/hyperf/pull/3598) 修復事務回滾時，模型累加、累減操作會導致模型快取產生髒資料的問題。
- [#3607](https://github.com/hyperf/hyperf/pull/3607) 修復在使用協程風格的 `WebSocket` 服務時，`onOpen` 事件無法在事件結束後銷燬協程的問題。
- [#3610](https://github.com/hyperf/hyperf/pull/3610) 修復資料庫存在字首時，`fromSub()` 和 `joinSub()` 無法正常使用的問題。

# v2.1.17 - 2021-05-17

## 修復

- [#3856](https://github.com/hyperf/hyperf/pull/3586) 修復 `Swow` 服務處理 `keepalive` 的請求時，協程無法在每個請求後結束的問題。

## 新增

- [#3329](https://github.com/hyperf/hyperf/pull/3329) `@Crontab` 註解的 `enable` 引數增加支援設定陣列, 你可以透過它動態的控制定時任務是否啟動。

# v2.1.16 - 2021-04-26

## 修復

- [#3510](https://github.com/hyperf/hyperf/pull/3510) 修復 `consul` 無法將節點強制離線的問題。
- [#3513](https://github.com/hyperf/hyperf/pull/3513) 修復 `Nats` 因為 `Socket` 超時時間小於最大閒置時間，導致連線意外關閉的問題。
- [#3520](https://github.com/hyperf/hyperf/pull/3520) 修復 `@Inject` 無法作用於巢狀 `Trait` 的問題。

## 新增

- [#3514](https://github.com/hyperf/hyperf/pull/3514) 新增方法 `Hyperf\HttpServer\Request::clearStoredParsedData()`。

## 最佳化

- [#3517](https://github.com/hyperf/hyperf/pull/3517) 最佳化 `Hyperf\Di\Aop\PropertyHandlerTrait`。

# v2.1.15 - 2021-04-19

## 新增

- [#3484](https://github.com/hyperf/hyperf/pull/3484) 新增 `ORM` 方法 `withMax()` `withMin()` `withSum()` 和 `withAvg()`.

# v2.1.14 - 2021-04-12

## 修復

- [#3465](https://github.com/hyperf/hyperf/pull/3465) 修復協程風格下，`WebSocket` 服務不支援配置多個埠的問題。
- [#3467](https://github.com/hyperf/hyperf/pull/3467) 修復協程風格下，`WebSocket` 服務無法正常釋放連線池的問題。

## 新增

- [#3472](https://github.com/hyperf/hyperf/pull/3472) 新增方法 `Sender::getResponse()`，可以在協程風格的 `WebSocket` 服務裡，獲得與 `fd` 一一對應的 `Response` 物件。

# v2.1.13 - 2021-04-06

## 修復

- [#3432](https://github.com/hyperf/hyperf/pull/3432) 修復 `SocketIO` 服務，定時清理失效 `fd` 的功能無法作用到其他 `worker` 程序的問題。
- [#3434](https://github.com/hyperf/hyperf/pull/3434) 修復 `RPC` 結果不支援允許為 `null` 的型別，例如 `?array` 會被強制轉化為陣列。
- [#3447](https://github.com/hyperf/hyperf/pull/3447) 修復模型快取中，因為存在表字首，導致模型預設值無法生效的問題。
- [#3450](https://github.com/hyperf/hyperf/pull/3450) 修復註解 `@Crontab` 無法作用於 `方法` 的問題，支援一個類中，配置多個 `@Crontab`。

## 最佳化

- [#3453](https://github.com/hyperf/hyperf/pull/3453) 優化了類 `Hyperf\Utils\Channel\Caller` 回收例項時的機制，防止因為例項為 `null` 時，導致無法正確回收的問題。
- [#3455](https://github.com/hyperf/hyperf/pull/3455) 最佳化指令碼 `phar:build`，支援使用軟連線方式載入的元件包。

# v2.1.12 - 2021-03-29

## 修復

- [#3423](https://github.com/hyperf/hyperf/pull/3423) 修復 `worker_num` 設定為非 `Integer` 時，導致定時任務中 `Task` 策略無法正常使用的問題。
- [#3426](https://github.com/hyperf/hyperf/pull/3426) 修復為可選引數路由設定中介軟體時，導致中介軟體被意外執行兩次的問題。

## 最佳化

- [#3422](https://github.com/hyperf/hyperf/pull/3422) 優化了 `co-phpunit` 的程式碼。

# v2.1.11 - 2021-03-22

## 新增

- [#3376](https://github.com/hyperf/hyperf/pull/3376) 為註解 `Hyperf\DbConnection\Annotation\Transactional` 增加引數 `$connection` 和 `$attempts`，使用者可以按需設定事務連線和重試次數。
- [#3403](https://github.com/hyperf/hyperf/pull/3403) 新增方法 `Hyperf\Testing\Client::sendRequest()`，使用者可以使用自己構造的 `ServerRequest`，比如設定 `Cookies`。

## 修復

- [#3380](https://github.com/hyperf/hyperf/pull/3380) 修復超全域性變數，在協程上下文裡沒有 `Request` 物件時，無法正常工作的問題。
- [#3394](https://github.com/hyperf/hyperf/pull/3394) 修復使用 `@Inject` 注入的物件，會被 `trait` 中注入的物件覆蓋的問題。
- [#3395](https://github.com/hyperf/hyperf/pull/3395) 修復當繼承使用 `@Inject` 注入私有變數的父類時，而導致子類例項化報錯的問題。
- [#3398](https://github.com/hyperf/hyperf/pull/3398) 修復單元測試中使用 `UploadedFile::isValid()` 時，無法正確判斷結果的問題。

# v2.1.10 - 2021-03-15

## 修復

- [#3348](https://github.com/hyperf/hyperf/pull/3348) 修復當使用 `Arr::forget` 方法在 `key` 為 `integer` 且不存在時，執行報錯的問題。
- [#3351](https://github.com/hyperf/hyperf/pull/3351) 修復 `hyperf/validation` 元件中，`FormRequest` 無法從協程上下文中獲取到修改後的 `ServerRequest`，從而導致驗證器驗證失敗的問題。
- [#3356](https://github.com/hyperf/hyperf/pull/3356) 修復 `hyperf/testing` 元件中，客戶端 `Hyperf\Testing\Client` 無法模擬構造正常的 `UriInterface` 的問題。
- [#3363](https://github.com/hyperf/hyperf/pull/3363) 修復在入口檔案 `bin/hyperf.php` 中自定義的常量，無法在命令 `server:watch` 中使用的問題。
- [#3365](https://github.com/hyperf/hyperf/pull/3365) 修復當使用協程風格服務時，如果使用者沒有配置 `pid_file`，仍然會意外生成 `runtime/hyperf.pid` 檔案的問題。

## 最佳化

- [#3364](https://github.com/hyperf/hyperf/pull/3364) 最佳化命令 `phar:build`，你可以在不使用 `php` 指令碼的情況下執行 `phar` 檔案，就像使用命令 `./composer.phar` 而非 `php composer.phar`。
- [#3367](https://github.com/hyperf/hyperf/pull/3367) 最佳化使用 `gen:model` 生成模型欄位的型別註釋時，儘量讀取自定義轉換器轉換後的物件型別。

# v2.1.9 - 2021-03-08

## 修復

- [#3326](https://github.com/hyperf/hyperf/pull/3326) 修復使用 `JsonEofPacker` 無法正確解包自定義 `eof` 資料的問題。
- [#3330](https://github.com/hyperf/hyperf/pull/3330) 修復因其他協程修改靜態變數 `$constraints`，導致模型關係查詢錯誤的問題。

## 新增

- [#3325](https://github.com/hyperf/hyperf/pull/3325) 為 `Crontab` 註解增加 `enable` 引數，用於控制當前任務是否註冊到定時任務中。

## 最佳化

- [#3338](https://github.com/hyperf/hyperf/pull/3338) 優化了 `testing` 元件，使模擬請求的方法執行在獨立的協程當中，避免協程變數汙染。

# v2.1.8 - 2021-03-01

## 修復

- [#3301](https://github.com/hyperf/hyperf/pull/3301) 修復 `hyperf/cache` 元件，當沒有在註解中設定超時時間時，會將超時時間強制轉化為 0，導致快取不失效的問題。

## 新增

- [#3310](https://github.com/hyperf/hyperf/pull/3310) 新增方法 `Blueprint::comment()`，可以允許在使用 `Migration` 的時候，設定表註釋。 
- [#3311](https://github.com/hyperf/hyperf/pull/3311) 新增方法 `RouteCollector::getRouteParser`，可以方便的從 `RouteCollector` 中獲取到 `RouteParser` 物件。
- [#3316](https://github.com/hyperf/hyperf/pull/3316) 允許使用者在 `hyperf/db` 元件中，註冊自定義資料庫介面卡。

## 最佳化

- [#3308](https://github.com/hyperf/hyperf/pull/3308) 最佳化 `WebSocket` 服務，當找不到對應路由時，直接返回響應。
- [#3319](https://github.com/hyperf/hyperf/pull/3319) 最佳化從連線池獲取連線的程式碼邏輯，避免因重寫低頻元件導致報錯，使得連線被意外丟棄。

## 新元件孵化

- [rpc-multiplex](https://github.com/hyperf/rpc-multiplex-incubator) 基於 Channel 實現的多路複用 RPC 元件。
- [db-pgsql](https://github.com/hyperf/db-pgsql-incubator) 適配於 `hyperf/db` 的 `PgSQL` 介面卡。

# v2.1.7 - 2021-02-22

## 修復

- [#3272](https://github.com/hyperf/hyperf/pull/3272) 修復使用 `doctrine/dbal` 修改資料庫欄位名報錯的問題。

## 新增

- [#3261](https://github.com/hyperf/hyperf/pull/3261) 新增方法 `Pipeline::handleCarry`，可以方便處理返回值。
- [#3267](https://github.com/hyperf/hyperf/pull/3267) 新增 `Hyperf\Utils\Reflection\ClassInvoker`，用於執行非公共方法和讀取非公共變數。
- [#3268](https://github.com/hyperf/hyperf/pull/3268) 為 `kafka` 消費者新增訂閱多個主題的能力。
- [#3193](https://github.com/hyperf/hyperf/pull/3193) [#3296](https://github.com/hyperf/hyperf/pull/3296) 為 `phar:build` 新增選項 `-M`，可以用來對映外部的檔案或目錄到 `Phar` 包中。 

## 變更

- [#3258](https://github.com/hyperf/hyperf/pull/3258) 為不同的 `kafka` 消費者設定不同的 Client ID。
- [#3282](https://github.com/hyperf/hyperf/pull/3282) 為 `hyperf/signal` 將拼寫錯誤的 `stoped` 修改為 `stopped`。

# v2.1.6 - 2021-02-08

## 修復

- [#3233](https://github.com/hyperf/hyperf/pull/3233) 修復 `AMQP` 元件，因連線服務端失敗，導致連線池耗盡的問題。
- [#3245](https://github.com/hyperf/hyperf/pull/3245) 修復 `hyperf/kafka` 元件設定 `autoCommit` 為 `false` 無效的問題。
- [#3255](https://github.com/hyperf/hyperf/pull/3255) 修復 `Nsq` 消費者程序，無法觸發 `defer` 方法的問題。

## 最佳化

- [#3249](https://github.com/hyperf/hyperf/pull/3249) 最佳化 `hyperf/kafka` 元件，可以重用連線進行訊息釋出。

## 移除

- [#3235](https://github.com/hyperf/hyperf/pull/3235) 移除 `hyperf/kafka` 元件 `rebalance` 檢查，因為底層庫 `longlang/phpkafka` 增加了對應的檢查。

# v2.1.5 - 2021-02-01

## 修復

- [#3204](https://github.com/hyperf/hyperf/pull/3204) 修復在 `hyperf/rpc-server` 元件中，中介軟體會被意外替換的問題。
- [#3209](https://github.com/hyperf/hyperf/pull/3209) 修復 `hyperf/amqp` 元件在使用協程風格服務，且因超時意外報錯時，沒有辦法正常回收到連線池的問題。
- [#3222](https://github.com/hyperf/hyperf/pull/3222) 修復 `hyperf/database` 元件中 `JOIN` 查詢會導致記憶體洩露的問題。
- [#3228](https://github.com/hyperf/hyperf/pull/3228) 修復 `hyperf/tracer` 元件中，在 `defer` 中呼叫 `flush` 失敗時，會導致程序異常退出的問題。
- [#3230](https://github.com/hyperf/hyperf/pull/3230) 修復 `hyperf/scout` 元件中 `orderBy` 方法無效的問題。

## 新增

- [#3211](https://github.com/hyperf/hyperf/pull/3211) 為 `hyperf/nacos` 元件添加了新的配置項 `url`，用於訪問 `Nacos` 服務。
- [#3214](https://github.com/hyperf/hyperf/pull/3214) 新增類 `Hyperf\Utils\Channel\Caller`，可以允許使用者使用協程安全的連線，避免連線被多個協程繫結，導致報錯的問題。
- [#3224](https://github.com/hyperf/hyperf/pull/3224) 新增方法 `Hyperf\Utils\CodeGen\Package::getPrettyVersion()`，允許使用者獲取元件的版本。

## 變更

- [#3218](https://github.com/hyperf/hyperf/pull/3218) 預設為 `AMQP` 配置 `QOS` 引數，`prefetch_count` 為 `1`，`global` 為 `false`，`prefetch_size` 為 `0`。
- [#3224](https://github.com/hyperf/hyperf/pull/3224) 為元件 `jean85/pretty-package-versions` 升級版本到 `^1.2|^2.0`, 支援 `Composer 2.x`。

> 如果使用 composer 2.x，則需要安裝 jean85/pretty-package-versions 的 ^2.0 版本，反之安裝 ^1.2 版本

## 最佳化

- [#3226](https://github.com/hyperf/hyperf/pull/3226) 最佳化 `hyperf/database` 元件，使用 `group by` 或 `having` 時執行子查詢獲得總數。

# v2.1.4 - 2021-01-25

## 修復

- [#3165](https://github.com/hyperf/hyperf/pull/3165) 修復方法 `Hyperf\Database\Schema\MySqlBuilder::getColumnListing` 在 `MySQL 8.0` 版本中無法正常使用的問題。
- [#3174](https://github.com/hyperf/hyperf/pull/3174) 修復 `hyperf/database` 元件中 `where` 語句因為不嚴謹的程式碼編寫，導致被繫結引數會被惡意替換的問題。
- [#3179](https://github.com/hyperf/hyperf/pull/3179) 修復 `json-rpc` 客戶端因對端服務重啟，導致接收資料一直異常的問題。
- [#3189](https://github.com/hyperf/hyperf/pull/3189) 修復 `kafka` 在叢集模式下無法正常使用的問題。
- [#3191](https://github.com/hyperf/hyperf/pull/3191) 修復 `json-rpc` 客戶端因對端服務重啟，導致連線池中的連線全部失效，新的請求進來時，首次使用皆會報錯的問題。

## 新增

- [#3170](https://github.com/hyperf/hyperf/pull/3170) 為 `hyperf/watcher` 元件新增了更加友好的驅動器 `FindNewerDriver`，支援 `Mac` `Linux` 和 `Docker`。
- [#3195](https://github.com/hyperf/hyperf/pull/3195) 為 `JsonRpcPoolTransporter` 新增了重試機制, 當連線、發包、收包失敗時，預設重試 2 次，收包超時不進行重試。

## 最佳化

- [#3169](https://github.com/hyperf/hyperf/pull/3169) 優化了 `ErrorExceptionHandler` 中與 `set_error_handler` 相關的入參程式碼, 解決靜態檢測因入參不匹配導致報錯的問題。
- [#3191](https://github.com/hyperf/hyperf/pull/3191) 優化了 `hyperf/json-rpc` 元件, 當連線中斷後，會先嚐試重連。

## 變更

- [#3174](https://github.com/hyperf/hyperf/pull/3174) 嚴格檢查 `hyperf/database` 元件中 `where` 語句繫結引數。

## 新元件孵化

- [DAG](https://github.com/hyperf/dag-incubator) 輕量級有向無環圖任務編排庫。
- [RPN](https://github.com/hyperf/rpn-incubator) 逆波蘭表示法。

# v2.1.3 - 2021-01-18

## 修復

- [#3070](https://github.com/hyperf/hyperf/pull/3070) 修復 `tracer` 元件無法正常使用的問題。
- [#3106](https://github.com/hyperf/hyperf/pull/3106) 修復協程從已被銷燬的協程中複製協程上下文時導致報錯的問題。
- [#3108](https://github.com/hyperf/hyperf/pull/3108) 修復使用 `describe:routes` 命令時，相同 `callback` 不同路由組的路由會被替換覆蓋的問題。
- [#3118](https://github.com/hyperf/hyperf/pull/3118) 修復 `migrations` 配置名位置錯誤的問題。
- [#3126](https://github.com/hyperf/hyperf/pull/3126) 修復 `Swoole` 擴充套件 `v4.6` 版本中，`SWOOLE_HOOK_SOCKETS` 與 `jaeger` 衝突的問題。
- [#3137](https://github.com/hyperf/hyperf/pull/3137) 修復 `database` 元件，當沒有主動設定 `PDO::ATTR_PERSISTENT` 為 `true` 時，導致的型別錯誤。
- [#3141](https://github.com/hyperf/hyperf/pull/3141) 修復使用 `Migration` 時，`doctrine/dbal` 無法正常工作的問題。

## 新增

- [#3059](https://github.com/hyperf/hyperf/pull/3059) 為 `view-engine` 元件增加合併任意標籤的能力。
- [#3123](https://github.com/hyperf/hyperf/pull/3123) 為 `view-engine` 元件增加 `ComponentAttributeBag::has()` 方法。

# v2.1.2 - 2021-01-11

## 修復

- [#3050](https://github.com/hyperf/hyperf/pull/3050) 修復在 `increment()` 後使用 `save()` 時，導致 `extra` 資料被儲存兩次的問題。
- [#3082](https://github.com/hyperf/hyperf/pull/3082) 修復 `hyperf/db` 元件在 `defer` 中使用時，會導致連線被其他協程繫結的問題。
- [#3084](https://github.com/hyperf/hyperf/pull/3084) 修復 `phar` 打包後 `getRealPath` 無法正常工作的問題。
- [#3087](https://github.com/hyperf/hyperf/pull/3087) 修復使用 `AOP` 時，`pipeline` 導致記憶體洩露的問題。
- [#3095](https://github.com/hyperf/hyperf/pull/3095) 修復 `hyperf/scout` 元件中，`ElasticsearchEngine::getTotalCount()` 無法相容 `Elasticsearch 7.0` 版本的問題。

## 新增

- [#2847](https://github.com/hyperf/hyperf/pull/2847) 新增 `hyperf/kafka` 元件。
- [#3066](https://github.com/hyperf/hyperf/pull/3066) 為 `hyperf/db` 元件新增 `ConnectionInterface::run(Closure $closure)` 方法。

## 最佳化

- [#3046](https://github.com/hyperf/hyperf/pull/3046) 打包 `phar` 時，優化了重寫 `scan_cacheable` 的程式碼。

## 變更

- [#3077](https://github.com/hyperf/hyperf/pull/3077) 因元件 `league/flysystem` 的 `2.0` 版本無法相容，故降級到 `^1.0`。

# v2.1.1 - 2021-01-04

## 修復

- [#3045](https://github.com/hyperf/hyperf/pull/3045) 修復 `database` 元件，當沒有主動設定 `PDO::ATTR_PERSISTENT` 為 `true` 時，導致的型別錯誤。
- [#3047](https://github.com/hyperf/hyperf/pull/3047) 修復 `socketio-server` 元件，為 `sid` 續約時報錯的問題。
- [#3062](https://github.com/hyperf/hyperf/pull/3062) 修復 `grpc-server` 元件，入參無法被正確解析的問題。

## 新增

- [#3052](https://github.com/hyperf/hyperf/pull/3052) 為 `metric` 元件，新增了收集命令列指標的功能。
- [#3054](https://github.com/hyperf/hyperf/pull/3054) 為 `socketio-server` 元件，新增了 `Engine::close` 協議支援，並在呼叫方法 `getRequest` 失敗時，丟擲連線已被關閉的異常。

# v2.1.0 - 2020-12-28

## 依賴升級

- 升級 `php` 版本到 `>=7.3`。
- 升級元件 `phpunit/phpunit` 版本到 `^9.0`。
- 升級元件 `guzzlehttp/guzzle` 版本到 `^6.0|^7.0`。
- 升級元件 `vlucas/phpdotenv` 版本到 `^5.0`。
- 升級元件 `endclothing/prometheus_client_php` 版本到 `^1.0`。
- 升級元件 `twig/twig` 版本到 `^3.0`。
- 升級元件 `jcchavezs/zipkin-opentracing` 版本到 `^0.2.0`。
- 升級元件 `doctrine/dbal` 版本到 `^3.0`。
- 升級元件 `league/flysystem` 版本到 `^1.0|^2.0`。

## 移除

- 移除 `Hyperf\Amqp\Builder` 已棄用的成員變數 `$name`。
- 移除 `Hyperf\Amqp\Message\ConsumerMessageInterface` 已棄用的方法 `consume()`。
- 移除 `Hyperf\AsyncQueue\Driver\Driver` 已棄用的成員變數 `$running`。
- 移除 `Hyperf\HttpServer\CoreMiddleware` 已棄用的方法 `parseParameters()`。
- 移除 `Hyperf\Utils\Coordinator\Constants` 已棄用的常量 `ON_WORKER_START` 和 `ON_WORKER_EXIT`。
- 移除 `Hyperf\Utils\Coordinator` 已棄用的方法 `get()`。
- 移除配置檔案 `rate-limit.php`, 請使用 `rate_limit.php`。
- 移除無用的類 `Hyperf\Resource\Response\ResponseEmitter`。
- 將元件 `hyperf/paginator` 從 `hyperf/database` 依賴中移除。
- 移除 `Hyperf\Utils\Coroutine\Concurrent` 中的方法 `stats()`。

## 變更

- 方法 `Hyperf\Utils\Coroutine::parentId` 返回父協程的協程 ID
  * 如果在主協程中，則會返回 0。
  * 如果在非協程環境中使用，則會丟擲 `RunningInNonCoroutineException` 異常。
  * 如果協程環境已被銷燬，則會丟擲 `CoroutineDestroyedException` 異常。

- 類 `Hyperf\Guzzle\CoroutineHandler`
  * 刪除了 `execute()` 方法。
  * 方法 `initHeaders()` 將會返回初始化好的 Header 列表, 而不是直接將 `$headers` 賦值到客戶端中。
  * 刪除了 `checkStatusCode()` 方法。

- [#2720](https://github.com/hyperf/hyperf/pull/2720) 不再在方法 `PDOStatement::bindValue()` 中設定 `data_type`，已避免字串索引中使用整形時，導致索引無法被命中的問題。
- [#2871](https://github.com/hyperf/hyperf/pull/2871) 從 `StreamInterface` 中獲取資料時，使用 `(string) $body` 而不是 `$body->getContents()`，因為方法 `getContents()` 只會返回剩餘的資料，而非全部資料。
- [#2909](https://github.com/hyperf/hyperf/pull/2909) 允許設定重複的中介軟體。
- [#2935](https://github.com/hyperf/hyperf/pull/2935) 修改了 `Exception Formatter` 的預設規則。
- [#2979](https://github.com/hyperf/hyperf/pull/2979) 命令列 `gen:model` 不再自動將 `decimal` 格式轉化為 `float`。

## 即將廢棄

- 類 `Hyperf\AsyncQueue\Signal\DriverStopHandler` 將會在 `v2.2` 版本中棄用, 請使用 `Hyperf\Process\Handler\ProcessStopHandler` 代替。
- 類 `Hyperf\Server\SwooleEvent` 將會在 `v3.0` 版本中棄用, 請使用 `Hyperf\Server\Event` 代替。

## 新增

- [#2659](https://github.com/hyperf/hyperf/pull/2659) [#2663](https://github.com/hyperf/hyperf/pull/2663) 新增了 [Swow](https://github.com/swow/swow) 驅動支援。
- [#2671](https://github.com/hyperf/hyperf/pull/2671) 新增監聽器 `Hyperf\AsyncQueue\Listener\QueueHandleListener`，用來記錄非同步佇列的執行日誌。
- [#2923](https://github.com/hyperf/hyperf/pull/2923) 新增類 `Hyperf\Utils\Waiter`，可以用來等待一個協程結束。
- [#3001](https://github.com/hyperf/hyperf/pull/3001) 新增方法 `Hyperf\Database\Model\Collection::columns()`，類似於 `array_column`。
- [#3002](https://github.com/hyperf/hyperf/pull/3002) 為 `Json::decode` 和 `Json::encode` 新增引數 `$depth` 和 `$flags`。

## 修復

- [#2741](https://github.com/hyperf/hyperf/pull/2741) 修復自定義程序無法在 `Swow` 驅動下使用的問題。

## 最佳化

- [#3009](https://github.com/hyperf/hyperf/pull/3009) 優化了 `prometheus`，使其支援 `https` 和 `http` 協議。

# v2.0.25 - 2020-12-28

## 新增

- [#3015](https://github.com/hyperf/hyperf/pull/3015) 為 `socketio-server` 增加了可以自動清理垃圾的機制。
- [#3030](https://github.com/hyperf/hyperf/pull/3030) 新增了方法 `ProceedingJoinPoint::getInstance()`，可以允許在使用 `AOP` 時，拿到被切入的例項。

## 最佳化

- [#3011](https://github.com/hyperf/hyperf/pull/3011) 最佳化 `hyperf/tracer` 元件，可以在鏈路追蹤中記錄異常資訊。

# v2.0.24 - 2020-12-21

## 修復

- [#2978](https://github.com/hyperf/hyperf/pull/2980) 修復當沒有引用 `hyperf/contract` 時，`hyperf/snowflake` 元件會無法正常使用的問題。
- [#2983](https://github.com/hyperf/hyperf/pull/2983) 修復使用協程風格服務時，常量 `SWOOLE_HOOK_FLAGS` 無法生效的問題。
- [#2993](https://github.com/hyperf/hyperf/pull/2993) 修復方法 `Arr::merge()` 入參 `$array1` 為空時，會將關聯陣列，錯誤的轉化為索引陣列的問題。

## 最佳化

- [#2973](https://github.com/hyperf/hyperf/pull/2973) 支援自定義的 `HTTP` 狀態碼。
- [#2992](https://github.com/hyperf/hyperf/pull/2992) 最佳化元件 `hyperf/validation` 的依賴關係，移除 `hyperf/devtool` 元件。

# v2.0.23 - 2020-12-14

## 新增

- [#2872](https://github.com/hyperf/hyperf/pull/2872) 新增 `hyperf/phar` 元件，用於將 `Hyperf` 專案打包成 `phar`。

## 修復

- [#2952](https://github.com/hyperf/hyperf/pull/2952) 修復 `Nacos` 配置中心，在協程風格服務中無法正常使用的問題。

## 變更

- [#2934](https://github.com/hyperf/hyperf/pull/2934) 變更配置檔案 `scout.php`，預設使用 `Elasticsearch` 索引作為模型索引。
- [#2958](https://github.com/hyperf/hyperf/pull/2958) 變更 `view` 元件預設的渲染引擎為 `NoneEngine`。

## 最佳化

- [#2951](https://github.com/hyperf/hyperf/pull/2951) 最佳化 `model-cache` 元件，使其執行完多次事務後，只會刪除一次快取。
- [#2953](https://github.com/hyperf/hyperf/pull/2953) 隱藏命令列因執行 `exit` 導致的異常 `Swoole\ExitException`。
- [#2963](https://github.com/hyperf/hyperf/pull/2963) 當非同步風格服務使用 `SWOOLE_BASE` 時，會從預設的事件回撥中移除 `onStart` 事件。

# v2.0.22 - 2020-12-07

## 新增

- [#2896](https://github.com/hyperf/hyperf/pull/2896) 允許 `view-engine` 元件配置自定義載入類元件和匿名元件。
- [#2921](https://github.com/hyperf/hyperf/pull/2921) 為 `Parallel` 增加 `count()` 方法，返回同時執行的個數。

## 修復

- [#2913](https://github.com/hyperf/hyperf/pull/2913) 修復使用 `ORM` 中的 `with` 預載入邏輯時，會因迴圈依賴導致記憶體洩露的問題。
- [#2915](https://github.com/hyperf/hyperf/pull/2915) 修復 `WebSocket` 工作程序會因 `onMessage` or `onClose` 回撥失敗，導致程序退出的問題。
- [#2927](https://github.com/hyperf/hyperf/pull/2927) 修復驗證器規則 `alpha_dash` 不支援 `int` 的問題。

## 變更

- [#2918](https://github.com/hyperf/hyperf/pull/2918) 當使用 `watcher` 元件時，不可以開啟 `daemonize`。
- [#2930](https://github.com/hyperf/hyperf/pull/2930) 更新 `php-amqplib` 元件最低版本由 `v2.7` 到 `v2.9.2`。

## 最佳化

- [#2931](https://github.com/hyperf/hyperf/pull/2931) 判斷控制器方法是否存在時，使用實際從容器中得到的物件，而非名稱空間。

# v2.0.21 - 2020-11-30

## 新增

- [#2857](https://github.com/hyperf/hyperf/pull/2857) 為 `service-governance` 元件新增 `Consul` 的 `ACL Token` 支援。
- [#2870](https://github.com/hyperf/hyperf/pull/2870) 為指令碼 `vendor:publish` 支援釋出配置目錄的能力。
- [#2875](https://github.com/hyperf/hyperf/pull/2875) 為 `watcher` 元件新增可選項 `no-restart`，允許動態修改註解快取，但不重啟服務。
- [#2883](https://github.com/hyperf/hyperf/pull/2883) 為 `scout` 元件資料匯入指令碼，增加可選項 `--chunk` 和 `--column|c`，允許使用者指定任一欄位，進行資料插入，解決偏移量過大導致查詢效率慢的問題。
- [#2891](https://github.com/hyperf/hyperf/pull/2891) 為 `crontab` 元件新增可用於釋出的配置檔案。

## 修復

- [#2874](https://github.com/hyperf/hyperf/pull/2874) 修復在使用 `watcher` 元件時， `scan.ignore_annotations` 配置不生效的問題。
- [#2878](https://github.com/hyperf/hyperf/pull/2878) 修復 `nsq` 元件中，`nsqd` 配置無法正常工作的問題。

## 變更

- [#2851](https://github.com/hyperf/hyperf/pull/2851) 修改 `view` 元件預設的配置檔案，使用 `view-engine` 引擎，而非第三方 `blade` 引擎。

## 最佳化

- [#2785](https://github.com/hyperf/hyperf/pull/2785) 最佳化 `watcher` 元件，使其異常資訊更加人性化。
- [#2861](https://github.com/hyperf/hyperf/pull/2861) 最佳化 `Guzzle Coroutine Handler`，當其 `statusCode` 小於 `0` 時，丟擲對應異常。
- [#2868](https://github.com/hyperf/hyperf/pull/2868) 最佳化 `Guzzle` 的 `sink` 配置，使其支援傳入 `resource`。

# v2.0.20 - 2020-11-23

## 新增

- [#2824](https://github.com/hyperf/hyperf/pull/2824) 為 `Hyperf\Database\Query\Builder` 增加方法 `simplePaginate()`。

## 修復

- [#2820](https://github.com/hyperf/hyperf/pull/2820) 修復使用 `fanout` 交換機時，`AMQP` 消費者無法正常工作的問題。
- [#2831](https://github.com/hyperf/hyperf/pull/2831) 修復 `AMQP` 連線會被客戶端意外關閉的問題。
- [#2848](https://github.com/hyperf/hyperf/pull/2848) 修復在 `defer` 中使用資料庫元件時，會導致資料庫連線會同時被其他協程繫結的問題。

## 變更

- [#2824](https://github.com/hyperf/hyperf/pull/2824) 修改 `Hyperf\Database\Query\Builder` 方法 `paginate()` 返回值型別，由 `PaginatorInterface` 變更為 `LengthAwarePaginatorInterface`。

## 最佳化

- [#2766](https://github.com/hyperf/hyperf/pull/2766) 最佳化 `Tracer` 元件，在丟擲異常的情況下，也可以執行 `finish` 方法，記錄鏈路。
- [#2805](https://github.com/hyperf/hyperf/pull/2805) 最佳化 `Nacos` 程序，可以安全停止。
- [#2821](https://github.com/hyperf/hyperf/pull/2821) 最佳化工具類 `Json` 和 `Xml`，使其丟擲一致的異常。
- [#2827](https://github.com/hyperf/hyperf/pull/2827) 最佳化 `Hyperf\Server\ServerConfig`，解決方法 `__set` 因返回值不為 `void`，導致不相容 `PHP8` 的問題。
- [#2839](https://github.com/hyperf/hyperf/pull/2839) 最佳化 `Hyperf\Database\Schema\ColumnDefinition` 的註釋。

# v2.0.19 - 2020-11-17

## 新增

- [#2794](https://github.com/hyperf/hyperf/pull/2794) [#2802](https://github.com/hyperf/hyperf/pull/2802) 為 `Session` 元件新增配置項 `options.cookie_lifetime`, 允許使用者自己設定 `Cookies` 的超時時間。

## 修復

- [#2783](https://github.com/hyperf/hyperf/pull/2783) 修復 `NSQ` 消費者無法在協程風格下正常使用的問題。
- [#2788](https://github.com/hyperf/hyperf/pull/2788) 修復非靜態方法 `__handlePropertyHandler()` 在代理類中，被靜態呼叫的問題。
- [#2790](https://github.com/hyperf/hyperf/pull/2790) 修復 `ETCD` 配置中心，`BootProcessListener` 監聽器無法在協程風格下正常使用的問題。
- [#2803](https://github.com/hyperf/hyperf/pull/2803) 修復當 `Request` 無法例項化時，`HTTP` 響應資料被清除的問題。
- [#2807](https://github.com/hyperf/hyperf/pull/2807) 修復當存在重複的中介軟體時，中介軟體的表現會與預期不符的問題。

## 最佳化

- [#2750](https://github.com/hyperf/hyperf/pull/2750) 最佳化 `Scout` 元件，當沒有配置搜尋引擎 `index` 或 `Elasticsearch` 版本高於 `7.0` 時，使用 `index` 而非 `type` 作為模型的搜尋條件。

# v2.0.18 - 2020-11-09

## 新增

- [#2752](https://github.com/hyperf/hyperf/pull/2752) 為註解 `@AutoController` `@Controller` 和 `@Mapping` 新增 `options` 引數，用於設定路由元資料。

## 修復

- [#2768](https://github.com/hyperf/hyperf/pull/2768) 修復 `WebSocket` 握手失敗時導致記憶體洩露的問題。
- [#2777](https://github.com/hyperf/hyperf/pull/2777) 修復低版本 `redis` 擴充套件，`RedisCluster` 建構函式 `$auth` 不支援 `null`，導致報錯的問題。
- [#2779](https://github.com/hyperf/hyperf/pull/2779) 修復因沒有設定 `translation` 配置檔案導致服務啟動失敗的問題。

## 變更

- [#2765](https://github.com/hyperf/hyperf/pull/2765) 變更 `Concurrent` 類中建立協程邏輯，由方法 `Hyperf\Utils\Coroutine::create()` 代替原來的 `Swoole\Coroutine::create()`。

## 最佳化

- [#2347](https://github.com/hyperf/hyperf/pull/2347) 為 `AMQP` 的 `ConsumerMessage` 增加引數 `$waitTimeout`，用於在協程風格服務中，安全停止服務。

# v2.0.17 - 2020-11-02

## 新增

- [#2625](https://github.com/hyperf/hyperf/pull/2625) 新增 `Hyperf\Tracer\Aspect\JsonRpcAspect`, 可以讓 `Tracer` 元件支援 `JsonRPC` 的鏈路追蹤。
- [#2709](https://github.com/hyperf/hyperf/pull/2709) [#2733](https://github.com/hyperf/hyperf/pull/2733) 為 `Model` 新增了對應的 `@mixin` 註釋，提升模型的靜態方法提示能力。
- [#2726](https://github.com/hyperf/hyperf/pull/2726) [#2733](https://github.com/hyperf/hyperf/pull/2733) 為 `gen:model` 指令碼增加可選項 `--with-ide`, 可以生成對應的 `IDE` 檔案。
- [#2737](https://github.com/hyperf/hyperf/pull/2737) 新增 [view-engine](https://github.com/hyperf/view-engine) 元件，可以不需要在 `Task` 程序中渲染頁面。

## 修復

- [#2719](https://github.com/hyperf/hyperf/pull/2719) 修復 `Arr::merge` 會因 `array1` 中不包含 `array2` 中存在的 `$key` 時，導致的報錯問題。
- [#2723](https://github.com/hyperf/hyperf/pull/2723) 修復 `Paginator::resolveCurrentPath` 無法正常工作的問題。

## 最佳化

- [#2746](https://github.com/hyperf/hyperf/pull/2746) 最佳化 `@Task` 註解，只會在 `worker` 程序中執行時，會投遞到 `task` 程序執行對應邏輯，其他程序則會降級為同步執行。

## 變更

- [#2728](https://github.com/hyperf/hyperf/pull/2728) `JsonRPC` 中，以 `__` 為字首的方法，都不會在註冊到 `RPC` 服務中，例如 `__construct`, '__call'。

# v2.0.16 - 2020-10-26

## 新增

- [#2682](https://github.com/hyperf/hyperf/pull/2682) 為 `CacheableInterface` 新增方法 `getCacheTTL` 可根據不同模型設定不同的快取時間。
- [#2696](https://github.com/hyperf/hyperf/pull/2696) 新增 Swoole Tracker 的記憶體檢測工具。

## 修復

- [#2680](https://github.com/hyperf/hyperf/pull/2680) 修復 `CastsValue` 因為沒有設定 `$isSynchronized` 預設值，導致的型別錯誤。
- [#2680](https://github.com/hyperf/hyperf/pull/2680) 修復 `CastsValue` 中 `$items` 預設值會被 `__construct` 覆蓋的問題。
- [#2693](https://github.com/hyperf/hyperf/pull/2693) 修復 `hyperf/retry` 元件，`Budget` 表現不符合期望的問題。
- [#2695](https://github.com/hyperf/hyperf/pull/2695) 修復方法 `Container::define()` 因為容器中的物件已被例項化，而無法重定義的問題。

## 最佳化

- [#2611](https://github.com/hyperf/hyperf/pull/2611) 最佳化 `hyperf/watcher` 元件 `FindDriver` ，使其可以在 `Alpine` 映象中使用。
- [#2662](https://github.com/hyperf/hyperf/pull/2662) 最佳化 `Amqp` 消費者程序，使其可以配合 `Signal` 元件安全停止。
- [#2690](https://github.com/hyperf/hyperf/pull/2690) 最佳化 `hyperf/tracer` 元件，確保其可以正常執行 `finish` 和 `flush` 方法。

# v2.0.15 - 2020-10-19

## 新增

- [#2654](https://github.com/hyperf/hyperf/pull/2654) 新增方法 `Hyperf\Utils\Resource::from`，可以方便的將 `string` 轉化為 `resource`。

## 修復

- [#2634](https://github.com/hyperf/hyperf/pull/2634) [#2640](https://github.com/hyperf/hyperf/pull/2640) 修復 `snowflake` 元件中，元資料生成器 `RedisSecondMetaGenerator` 會產生相同元資料的問題。
- [#2639](https://github.com/hyperf/hyperf/pull/2639) 修復 `json-rpc` 元件中，異常無法正常被序列化的問題。
- [#2643](https://github.com/hyperf/hyperf/pull/2643) 修復 `scout:flush` 執行失敗的問題。

## 最佳化

- [#2656](https://github.com/hyperf/hyperf/pull/2656) 優化了 `json-rpc` 元件中，引數解析失敗後，也可以返回對應的錯誤資訊。

# v2.0.14 - 2020-10-12

## 新增

- [#1172](https://github.com/hyperf/hyperf/pull/1172) 新增基於 `laravel/scout` 實現的元件 `hyperf/scout`, 可以透過搜尋引擎進行模型查詢。
- [#1868](https://github.com/hyperf/hyperf/pull/1868) 新增 `Redis` 元件的哨兵模式。
- [#1969](https://github.com/hyperf/hyperf/pull/1969) 新增元件 `hyperf/resource` and `hyperf/resource-grpc`，可以更加方便的將模型轉化為 Response。

## 修復

- [#2594](https://github.com/hyperf/hyperf/pull/2594) 修復 `hyperf/crontab` 元件因為無法正常響應 `hyperf/signal`，導致無法停止的問題。
- [#2601](https://github.com/hyperf/hyperf/pull/2601) 修復命令 `gen:model` 因為 `getter` 和 `setter` 同時存在時，註釋 `@property` 會被 `@property-read` 覆蓋的問題。
- [#2607](https://github.com/hyperf/hyperf/pull/2607) [#2637](https://github.com/hyperf/hyperf/pull/2637) 修復使用 `RetryAnnotationAspect` 時，會有一定程度記憶體洩露的問題。
- [#2624](https://github.com/hyperf/hyperf/pull/2624) 修復元件 `hyperf/testing` 因使用了 `guzzle 7.0` 和 `CURL HOOK` 導致無法正常工作的問題。
- [#2632](https://github.com/hyperf/hyperf/pull/2632) [#2635](https://github.com/hyperf/hyperf/pull/2635) 修復 `hyperf\redis` 元件叢集模式，無法設定密碼的問題。

## 最佳化

- [#2603](https://github.com/hyperf/hyperf/pull/2603) 允許 `hyperf/database` 元件，`whereNull` 方法接受 `array` 作為入參。

# v2.0.13 - 2020-09-28

## 新增

- [#2445](https://github.com/hyperf/hyperf/pull/2445) 當使用異常捕獲器 `WhoopsExceptionHandler` 返回 `JSON` 格式化的資料時，自動新增異常的 `Trace` 資訊。
- [#2580](https://github.com/hyperf/hyperf/pull/2580) 新增 `grpc-client` 元件的 `metadata` 支援。

## 修復

- [#2559](https://github.com/hyperf/hyperf/pull/2559) 修復使用 `socket-io` 連線 `socketio-server` 時，因為攜帶 `query` 資訊，導致事件無法被觸發的問題。
- [#2565](https://github.com/hyperf/hyperf/pull/2565) 修復生成代理類時，因為存在匿名類，導致代理類在沒有父類的情況下使用了 `parent::class` 而報錯的問題。
- [#2578](https://github.com/hyperf/hyperf/pull/2578) 修復當自定義程序拋錯後，事件 `AfterProcessHandle` 無法被觸發的問題。
- [#2582](https://github.com/hyperf/hyperf/pull/2582) 修復使用 `Redis::multi` 且在 `defer` 中使用了其他 `Redis` 指令後，導致 `Redis` 同時被兩個協程使用而報錯的問題。
- [#2589](https://github.com/hyperf/hyperf/pull/2589) 修復使用了協程風格服務時，`AMQP` 消費者無法正常啟動的問題。
- [#2590](https://github.com/hyperf/hyperf/pull/2590) 修復使用了協程風格服務時，`Crontab` 無法正常工作的問題。

## 最佳化

- [#2561](https://github.com/hyperf/hyperf/pull/2561) 最佳化關閉 `AMQP` 連線失敗時的錯誤資訊。
- [#2584](https://github.com/hyperf/hyperf/pull/2584) 當服務關閉時，不再刪除 `Nacos` 中對應的服務。

# v2.0.12 - 2020-09-21

## 新增

- [#2512](https://github.com/hyperf/hyperf/pull/2512) 為 [hyperf/database](https://github.com/hyperf/database) 元件方法 `MySqlGrammar::compileColumnListing` 新增返回欄位 `column_type`。

## 修復

- [#2490](https://github.com/hyperf/hyperf/pull/2490) 修復 [hyperf/grpc-client](https://github.com/hyperf/grpc-client) 元件中，流式客戶端無法正常工作的問題。
- [#2509](https://github.com/hyperf/hyperf/pull/2509) 修復 [hyperf/database](https://github.com/hyperf/database) 元件中，使用小駝峰模式後，訪問器無法正常工作的問題。
- [#2535](https://github.com/hyperf/hyperf/pull/2535) 修復 [hyperf/database](https://github.com/hyperf/database) 元件中，使用 `gen:model` 後，透過訪問器生成的註釋 `@property` 會被 `morphTo` 覆蓋的問題。
- [#2546](https://github.com/hyperf/hyperf/pull/2546) 修復 [hyperf/db-connection](https://github.com/hyperf/db-connection) 元件中，使用 `left join` 等複雜查詢後，`MySQL` 連線無法正常釋放的問題。

## 最佳化

- [#2490](https://github.com/hyperf/hyperf/pull/2490) 最佳化 [hyperf/grpc-client](https://github.com/hyperf/grpc-client) 元件中的異常和單元測試。

# v2.0.11 - 2020-09-14

## 新增

- [#2455](https://github.com/hyperf/hyperf/pull/2455) 為 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 元件新增方法 `Socket::getRequest` 用於獲取 `Psr7` 規範的 `Request`。
- [#2459](https://github.com/hyperf/hyperf/pull/2459) 為 [hyperf/async-queue](https://github.com/hyperf/async-queue) 元件新增監聽器 `ReloadChannelListener` 用於自動將超時佇列裡的訊息移動到等待執行佇列中。
- [#2463](https://github.com/hyperf/hyperf/pull/2463) 為 [hyperf/database](https://github.com/hyperf/database) 元件新增可選的 `ModelRewriteGetterSetterVisitor` 用於為模型生成對應的 `Getter` 和 `Setter`。
- [#2475](https://github.com/hyperf/hyperf/pull/2475) 為 [hyperf/retry](https://github.com/hyperf/retry) 元件的 `Fallback` 回撥，預設增加 `throwable` 引數。

## 修復

- [#2464](https://github.com/hyperf/hyperf/pull/2464) 修復 [hyperf/database](https://github.com/hyperf/database) 元件中，小駝峰模式模型的 `fill` 方法無法正常使用的問題。
- [#2478](https://github.com/hyperf/hyperf/pull/2478) 修復 [hyperf/websocket-server](https://github.com/hyperf/websocket-server) 元件中，`Sender::check` 無法檢測非 `WebSocket` 的 `fd` 值。
- [#2488](https://github.com/hyperf/hyperf/pull/2488) 修復 [hyperf/database](https://github.com/hyperf/database) 元件中，當 `pdo` 例項化失敗後 `beginTransaction` 呼叫失敗的問題。

## 最佳化

- [#2461](https://github.com/hyperf/hyperf/pull/2461) 最佳化 [hyperf/reactive-x](https://github.com/hyperf/reactive-x) 元件 `HTTP` 路由監聽器，可以監聽任意埠路由。
- [#2465](https://github.com/hyperf/hyperf/pull/2465) 最佳化 [hyperf/retry](https://github.com/hyperf/retry) 元件 `FallbackRetryPolicy` 中 `fallback` 除了可以填寫被 `is_callable` 識別的程式碼外，還可以填寫形如 `class@method` 的格式，框架會從 `Container` 中拿到對應的 `class`，然後執行其 `method` 方法。

## 變更

- [#2492](https://github.com/hyperf/hyperf/pull/2492) 調整 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 元件中的事件收集順序，確保 `sid` 早於自定義 `onConnect` 被新增到房間中。

# v2.0.10 - 2020-09-07

## 新增

- [#2411](https://github.com/hyperf/hyperf/pull/2411) 為 [hyperf/database](https://github.com/hyperf/database) 元件新增 `Hyperf\Database\Query\Builder::forPageBeforeId` 方法。
- [#2420](https://github.com/hyperf/hyperf/pull/2420) [#2426](https://github.com/hyperf/hyperf/pull/2426) 為 [hyperf/command](https://github.com/hyperf/command) 元件新增預設選項 `enable-event-dispatcher` 用於初始化事件觸發器。
- [#2433](https://github.com/hyperf/hyperf/pull/2433) 為 [hyperf/grpc-server](https://github.com/hyperf/grpc-server) 元件路由新增匿名函式支援。
- [#2441](https://github.com/hyperf/hyperf/pull/2441) 為 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 元件中 `SocketIO` 新增了一些 `setters`。

## 修復

- [#2427](https://github.com/hyperf/hyperf/pull/2427) 修復事件觸發器在使用 `Pivot` 或 `MorphPivot` 不生效的問題。
- [#2443](https://github.com/hyperf/hyperf/pull/2443) 修復使用 [hyperf/Guzzle](https://github.com/hyperf/guzzle) 元件的 `Coroutine Handler` 時，無法正確獲取和傳遞 `traceid` 和 `spanid` 的問題。
- [#2449](https://github.com/hyperf/hyperf/pull/2449) 修復釋出 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) 元件的配置檔案時，配置檔名稱錯誤的問題。

## 最佳化

- [#2429](https://github.com/hyperf/hyperf/pull/2429) 最佳化使用 `@Inject` 並且沒有設定 `@var` 時的錯誤資訊，方便定位問題，改善程式設計體驗。
- [#2438](https://github.com/hyperf/hyperf/pull/2438) 最佳化當使用 [hyperf/model-cache](https://github.com/hyperf/model-cache) 元件與資料庫事務搭配使用時，在事務中刪除或修改模型資料會在事務提交後即時再刪除快取，而不再是在刪除或修改模型資料時刪除快取資料。

# v2.0.9 - 2020-08-31

## 新增

- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 元件增加授權介面。
- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 元件增加 `nacos.enable` 配置，用於控制是否啟用 `Nacos` 服務。
- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 元件增加配置合併型別，預設使用全量覆蓋。
- [#2377](https://github.com/hyperf/hyperf/pull/2377) 為 gRPC 客戶端 的 request 增加 `ts` 請求頭，以相容 Node.js gRPC server 等。
- [#2384](https://github.com/hyperf/hyperf/pull/2384) 新增助手函式 `optional()`，以建立 `Hyperf\Utils\Optional` 物件或更方便 Optional 的使用。

## 修改

- [#2331](https://github.com/hyperf/hyperf/pull/2331) 修復 [hyperf/nacos](https://github.com/hyperf/nacos) 元件，服務或配置不存在時，會丟擲異常的問題。
- [#2356](https://github.com/hyperf/hyperf/pull/2356) [#2368](https://github.com/hyperf/hyperf/pull/2368) 修復 `pid_file` 被使用者修改後，命令列 `server:start` 啟動失敗的問題。
- [#2358](https://github.com/hyperf/hyperf/pull/2358) 修復驗證器規則 `digits` 不支援 `int` 型別的問題。

## 最佳化

- [#2359](https://github.com/hyperf/hyperf/pull/2359) 最佳化自定義程序，在協程風格服務下，可以更加友好的停止。
- [#2363](https://github.com/hyperf/hyperf/pull/2363) 最佳化 [hyperf/di](https://github.com/hyperf/di) 元件，使其不需要依賴 [hyperf/config](https://github.com/hyperf/config) 元件。
- [#2373](https://github.com/hyperf/hyperf/pull/2373) 最佳化 [hyperf/validation](https://github.com/hyperf/validation) 元件的異常捕獲器，使其返回 `Response` 時，自動新增 `content-type` 頭。


# v2.0.8 - 2020-08-24

## 新增

- [#2334](https://github.com/hyperf/hyperf/pull/2334) 新增更加友好的陣列遞迴合併方法 `Arr::merge`。
- [#2335](https://github.com/hyperf/hyperf/pull/2335) 新增 `Hyperf/Utils/Optional`，它可以接受任意引數，並允許訪問該物件上的屬性或呼叫其方法，即使給定的物件為 `null`，也不會引發錯誤。
- [#2336](https://github.com/hyperf/hyperf/pull/2336) 新增 `RedisNsqAdapter`，它透過 `NSQ` 釋出訊息，使用 `Redis` 記錄房間資訊。

## 修復

- [#2338](https://github.com/hyperf/hyperf/pull/2338) 修復檔案系統使用 `S3` 介面卡時，檔案是否存在的邏輯與預期不符的 BUG。
- [#2340](https://github.com/hyperf/hyperf/pull/2340) 修復 `__FUNCTION__` 和 `__METHOD__` 魔術方法無法在被 `AOP` 重寫的方法里正常工作的 BUG。

## 最佳化

- [#2319](https://github.com/hyperf/hyperf/pull/2319) 最佳化 `ResolverDispatcher` ，使專案發生迴圈依賴時，可以提供更加友好的錯誤提示。

# v2.0.7 - 2020-08-17

## 新增

- [#2307](https://github.com/hyperf/hyperf/pull/2307) [#2312](https://github.com/hyperf/hyperf/pull/2312) [hyperf/nsq](https://github.com/hyperf/nsq) 元件，新增 `NSQD` 的 `HTTP` 客戶端。

## 修復

- [#2275](https://github.com/hyperf/hyperf/pull/2275) 修復配置中心，拉取配置程序會出現阻塞的 BUG。
- [#2276](https://github.com/hyperf/hyperf/pull/2276) 修復 `Apollo` 配置中心，當配置沒有變更時，會清除所有本地配置項的 BUG。
- [#2280](https://github.com/hyperf/hyperf/pull/2280) 修復 `Interface` 的方法會被 `AOP` 重寫，導致啟動報錯的 BUG。
- [#2281](https://github.com/hyperf/hyperf/pull/2281) 當使用 `Task` 元件，且沒有啟動協程時，`Signal` 元件會導致啟動報錯的 BUG。
- [#2304](https://github.com/hyperf/hyperf/pull/2304) 修復當使用 `SocketIOServer` 的記憶體介面卡，刪除 `sid` 時，會導致死迴圈的 BUG。
- [#2309](https://github.com/hyperf/hyperf/pull/2309) 修復 `JsonRpcHttpTransporter` 無法設定自定義超時時間的 BUG。

# v2.0.6 - 2020-08-10

## 新增

- [#2125](https://github.com/hyperf/hyperf/pull/2125) 新增 [hyperf/jet](https://github.com/hyperf/jet) 元件。`Jet` 是一個統一模型的 RPC 客戶端，內建 JSONRPC 協議的適配，該元件可適用於所有的 `PHP (>= 7.2)` 環境，包括 PHP-FPM 和 Swoole 或 Hyperf。

## 修復

- [#2236](https://github.com/hyperf/hyperf/pull/2236) 修復 `Nacos` 使用負載均衡器選擇節點失敗的 BUG。
- [#2242](https://github.com/hyperf/hyperf/pull/2242) 修復 `watcher` 元件會重複收集多次註解的 BUG。

# v2.0.5 - 2020-08-03

## 新增

- [#2001](https://github.com/hyperf/hyperf/pull/2001) 新增引數 `$signature`，用於簡化命令列的初始化工作。
- [#2204](https://github.com/hyperf/hyperf/pull/2204) 為方法 `parallel` 增加 `$concurrent` 引數，用於快速設定併發量。

## 修復

- [#2210](https://github.com/hyperf/hyperf/pull/2210) 修復 `WebSocket` 握手成功後，不會立馬觸發 `OnOpen` 事件的 BUG。
- [#2214](https://github.com/hyperf/hyperf/pull/2214) 修復 `WebSocket` 主動關閉連線時，不會觸發 `OnClose` 事件的 BUG。
- [#2218](https://github.com/hyperf/hyperf/pull/2218) 修復在 `協程 Server` 下，`Sender::disconnect` 報錯的 BUG。
- [#2227](https://github.com/hyperf/hyperf/pull/2227) 修復在 `協程 Server` 下，建立 `keepalive` 連線後，上下文資料無法在請求結束後銷燬的 BUG。

## 最佳化

- [#2193](https://github.com/hyperf/hyperf/pull/2193) 最佳化 `Hyperf\Watcher\Driver\FindDriver`，使其掃描有變動的檔案更加精確。
- [#2232](https://github.com/hyperf/hyperf/pull/2232) 最佳化 `model-cache` 的預載入功能，使其支援 `In` 和 `InRaw`。

# v2.0.4 - 2020-07-27

## 新增

- [#2144](https://github.com/hyperf/hyperf/pull/2144) 資料庫查詢事件 `Hyperf\Database\Events\QueryExecuted` 新增 `$result` 欄位。
- [#2158](https://github.com/hyperf/hyperf/pull/2158) 路由 `Hyperf\HttpServer\Router\Handler` 中，新增 `$options` 欄位。
- [#2162](https://github.com/hyperf/hyperf/pull/2162) 熱更新元件新增 `Hyperf\Watcher\Driver\FindDriver`。
- [#2169](https://github.com/hyperf/hyperf/pull/2169) `Session` 元件新增配置 `session.options.domain`，用於替換 `Request` 中獲取的 `domain`。
- [#2174](https://github.com/hyperf/hyperf/pull/2174) 模型生成器新增 `ModelRewriteTimestampsVisitor`，用於根據資料庫欄位 `created_at` 和 `updated_at`， 重寫模型欄位 `$timestamps`。
- [#2175](https://github.com/hyperf/hyperf/pull/2175) 模型生成器新增 `ModelRewriteSoftDeletesVisitor`，用於根據資料庫欄位 `deleted_at`， 新增或者移除 `SoftDeletes`。
- [#2176](https://github.com/hyperf/hyperf/pull/2176) 模型生成器新增 `ModelRewriteKeyInfoVisitor`，用於根據資料庫主鍵，重寫模型欄位 `$incrementing` `$primaryKey` 和 `$keyType`。

## 修復

- [#2149](https://github.com/hyperf/hyperf/pull/2149) 修復自定義程序執行過程中無法從 Nacos 正常更新配置的 BUG。
- [#2159](https://github.com/hyperf/hyperf/pull/2159) 修復使用 `gen:migration` 時，由於檔案已經存在導致的 `FATAL` 異常。

## 最佳化

- [#2043](https://github.com/hyperf/hyperf/pull/2043) 當 `SCAN` 目錄都不存在時，丟擲更加友好的異常。
- [#2182](https://github.com/hyperf/hyperf/pull/2182) 當使用 `WebSocket` 和 `Http` 服務且 `Http` 介面被訪問時，不會記錄 `WebSocket` 關閉連線的日誌。

# v2.0.3 - 2020-07-20

## 新增

- [#1554](https://github.com/hyperf/hyperf/pull/1554) 新增 `hyperf/nacos` 元件。
- [#2082](https://github.com/hyperf/hyperf/pull/2082) 監聽器 `Hyperf\Signal\Handler\WorkerStopHandler` 新增訊號 `SIGINT` 監聽。
- [#2097](https://github.com/hyperf/hyperf/pull/2097) `hyperf/filesystem` 新增 TencentCloud COS 支援.
- [#2122](https://github.com/hyperf/hyperf/pull/2122) 新增 Trait `\Hyperf\Snowflake\Concern\HasSnowflake` 為模型自動生成雪花演算法的主鍵。

## 修復

- [#2017](https://github.com/hyperf/hyperf/pull/2017) 修復 Prometheus 使用 redis 打點時，改變 label 會導致收集報錯的 BUG。
- [#2117](https://github.com/hyperf/hyperf/pull/2117) 修復使用 `server:watch` 時，註解 `@Inject` 有時會失效的 BUG。
- [#2123](https://github.com/hyperf/hyperf/pull/2123) 修復 `tracer` 會記錄兩次 `Redis 指令` 的 BUG。
- [#2139](https://github.com/hyperf/hyperf/pull/2139) 修復 `ValidationMiddleware` 在 `WebSocket` 服務下使用會報錯的 BUG。
- [#2140](https://github.com/hyperf/hyperf/pull/2140) 修復請求丟擲異常時，`Session` 無法儲存的 BUG。

## 最佳化

- [#2080](https://github.com/hyperf/hyperf/pull/2080) 方法 `Hyperf\Database\Model\Builder::paginate` 中引數 `$perPage` 的型別從 `int` 更改為 `?int`。
- [#2110](https://github.com/hyperf/hyperf/pull/2110) 在使用 `hyperf/watcher` 時，會先檢查程序是否存在，如果不存在，才會傳送 `SIGTERM` 訊號。
- [#2116](https://github.com/hyperf/hyperf/pull/2116) 最佳化元件 `hyperf/di` 的依賴。
- [#2121](https://github.com/hyperf/hyperf/pull/2121) 在使用 `gen:model` 時，如果使用者自定義了與資料庫欄位一致的欄位時，則會替換對應的 `@property`。
- [#2129](https://github.com/hyperf/hyperf/pull/2129) 當 Response Json 格式化失敗時，會丟擲更加友好的錯誤提示。

# v2.0.2 - 2020-07-13

## 修復

- [#1898](https://github.com/hyperf/hyperf/pull/1898) 修復定時器規則 `$min-$max` 解析有誤的 BUG。
- [#2037](https://github.com/hyperf/hyperf/pull/2037) 修復 TCP 服務，連線後共用一個協程，導致 DB 等連線池無法正常回收連線的 BUG。
- [#2051](https://github.com/hyperf/hyperf/pull/2051) 修復 `CoroutineServer` 不會生成 `hyperf.pid` 的 BUG。
- [#2055](https://github.com/hyperf/hyperf/pull/1695) 修復 `Guzzle` 在傳輸大資料包時會自動新增頭 `Expect: 100-Continue`，導致請求失敗的 BUG。
- [#2059](https://github.com/hyperf/hyperf/pull/2059) 修復 `SocketIOServer` 中 `Redis` 重連失敗的 BUG。
- [#2067](https://github.com/hyperf/hyperf/pull/2067) 修復 `hyperf/watcher` 元件 `Syntax` 錯誤會導致程序異常。
- [#2085](https://github.com/hyperf/hyperf/pull/2085) 修復註解 `RetryFalsy` 會導致獲得正確的結果後，再次重試。
- [#2089](https://github.com/hyperf/hyperf/pull/2089) 修復使用 `gen:command` 後，指令碼必須要進行修改，才能被載入到的 BUG。
- [#2093](https://github.com/hyperf/hyperf/pull/2093) 修復指令碼 `vendor:publish` 沒有返回碼導致報錯的 BUG。

## 新增

- [#1860](https://github.com/hyperf/hyperf/pull/1860) 為 `Server` 新增預設的 `OnWorkerExit` 回撥。
- [#2042](https://github.com/hyperf/hyperf/pull/2042) 為熱更新元件，新增檔案掃描驅動。
- [#2054](https://github.com/hyperf/hyperf/pull/2054) 為模型快取新增 `Eager Load` 功能。

## 最佳化

- [#2049](https://github.com/hyperf/hyperf/pull/2049) 最佳化熱更新元件的 Stdout 輸出。
- [#2090](https://github.com/hyperf/hyperf/pull/2090) 為 `hyperf/session` 元件適配非 `Hyperf` 的 `Response`。

## 變更

- [#2031](https://github.com/hyperf/hyperf/pull/2031) 常量元件的錯誤碼只支援 `int` 和 `string`。
- [#2065](https://github.com/hyperf/hyperf/pull/2065) `WebSocket` 訊息傳送器 `Hyperf\WebSocketServer\Sender` 支援 `push` 和 `disconnect`。
- [#2100](https://github.com/hyperf/hyperf/pull/2100) 元件 `hyperf/utils` 更新依賴 `doctrine/inflector` 版本到 `^2.0`。

## 移除

- [#2065](https://github.com/hyperf/hyperf/pull/2065) 移除 `Hyperf\WebSocketServer\Sender` 對方法 `send` `sendto` 和 `close` 的支援，請使用 `push` 和 `disconnect`。

# v2.0.1 - 2020-07-02

## 新增

- [#1934](https://github.com/hyperf/hyperf/pull/1934) 增加指令碼 `gen:constant` 用於建立常量類。
- [#1982](https://github.com/hyperf/hyperf/pull/1982) 新增熱更新元件，檔案修改後自動收集註解，自動重啟。

## 修復

- [#1952](https://github.com/hyperf/hyperf/pull/1952) 修復資料庫遷移類存在時，也會生成同類名類，導致類名衝突的 BUG。
- [#1960](https://github.com/hyperf/hyperf/pull/1960) 修復 `Hyperf\HttpServer\ResponseEmitter::isMethodsExists()` 判斷錯誤的 BUG。
- [#1961](https://github.com/hyperf/hyperf/pull/1961) 修復因檔案 `config/autoload/aspects.php` 不存在導致服務無法啟動的 BUG。
- [#1964](https://github.com/hyperf/hyperf/pull/1964) 修復介面請求時，資料體為空會導致 `500` 錯誤的 BUG。
- [#1965](https://github.com/hyperf/hyperf/pull/1965) 修復 `initRequestAndResponse` 失敗後，會導致請求狀態碼與實際不符的 BUG。
- [#1968](https://github.com/hyperf/hyperf/pull/1968) 修復當修改 `aspects.php` 檔案後，`Aspect` 無法安裝修改後的結果執行的 BUG。
- [#1985](https://github.com/hyperf/hyperf/pull/1985) 修復註解全域性配置不全為小寫時，會導致 `global_imports` 失敗的 BUG。
- [#1990](https://github.com/hyperf/hyperf/pull/1990) 修復當父類存在與子類一樣的成員變數時， `@Inject` 無法正常使用的 BUG。
- [#2019](https://github.com/hyperf/hyperf/pull/2019) 修復指令碼 `gen:model` 因為使用了 `morphTo` 或 `where` 導致生成對應的 `@property` 失敗的 BUG。
- [#2026](https://github.com/hyperf/hyperf/pull/2026) 修復當使用了魔術方法時，LazyLoad 代理生成有誤的 BUG。

## 變更

- [#1986](https://github.com/hyperf/hyperf/pull/1986) 當沒有設定正確的 `swoole.use_shortname` 變更指令碼 `exit_code` 為 `SIGTERM`。

## 最佳化

- [#1959](https://github.com/hyperf/hyperf/pull/1959) 最佳化類 `ClassLoader` 可以更容易被使用者繼承並修改。
- [#2002](https://github.com/hyperf/hyperf/pull/2002) 當 `PHP` 版本大於等於 `7.3` 時，支援 `AOP` 切入 `Trait`。

# v2.0 - 2020-06-22

## 主要功能

1. 重構 [hyperf/di](https://github.com/hyperf/di) 元件，特別是對 AOP 和註解的最佳化，在 2.0 版本，該元件使用了一個全新的載入機制來提供 AOP 功能的支援。
    1. 對比 1.x 版本來說最顯著的一個功能就是現在你可以透過 AOP 功能切入任何方式例項化的一個類了，比如說，在 1.x 版本，你只能切入由 DI 容器建立的類，你無法切入一個由 `new` 關鍵詞例項化的類，但在 2.0 版本都可以生效了。不過仍有一些例外的情況，您仍無法切入那些在啟動階段用來提供 AOP 功能的類；
    2. 在 1.x 版本，AOP 只能作用於普通的類，無法支援 `Final` 類，但在 2.0 版本您可以這麼做了；
    3. 在 1.x 版本，您無法在當前類的建構函式中使用 `@Inject` 或 `@Value` 註解標記的類成員屬性的值，但在 2.0 版本里，您可以這麼做了；
    4. 在 1.x 版本，只有透過 DI 容器建立的物件才能使 `@Inject` 和 `@Value` 註解的功能生效，透過 `new` 關鍵詞建立的物件無法生效，但在 2.0 版本，都可以生效了；
    5. 在 1.x 版本，在使用註解時，您必須定義註解的名稱空間來指定使用的註解類，但在 2.0 版本下，您可以為任一註解提供一個別名，這樣在使用這個註解時可以直接使用別名而無需引入註解類的名稱空間。比如您可以直接在任意類屬性上標記 `@Inject` 註解而無需編寫 `use Hyperf\Di\Annotation\Inject;`；
    6. 在 1.x 版本，建立的代理類是一個目標類的子類，這樣的實現機制會導致一些魔術常量獲得的值返回的是代理類子類的資訊，而不是目標類的資訊，但在 2.0 版本，代理類會與目標類保持一樣的類名和程式碼結構；
    7. 在 1.x 版本，當代理類快取存在時則不會重新生成快取，就算原始碼發生了變化，這樣的機制有助於掃描耗時的提升，但與此同時，這也會導致開發階段的一些不便利，但在 2.0 版本，代理類快取會根據原始碼的變化而自動變化，這一改變會減少很多在開發階段的心智負擔；
    8. 為 Aspect 類增加了 `priority` 優先順序屬性，現在您可以組織多個 Aspect 之間的順序了；
    9. 在 1.x 版本，您只能透過 `@Aspect` 註解類定義一個 Aspect 類，但在 2.0 版本，您還可以透過配置檔案、ConfigProvider 來定義 Aspect 類；
    10. 在 1.x 版本，您在使用到依賴懶載入功能時，必須註冊一個 `Hyperf\Di\Listener\LazyLoaderBootApplicationListener` 監聽器，但在 2.0 版本，您可以直接使用該功能而無需做任何的註冊動作；
    11. 增加了 `annotations.scan.class_map` 配置項，透過該配置您可以將任意類替換成您自己的類，而使用時無需做任何的改變；

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
- [#1245](https://github.com/hyperf/hyperf/pull/1245) `ExceptionHandler` 新增了 `priority` 優先順序屬性，透過配置檔案或註解方式均可定義優先順序；
- [#1819](https://github.com/hyperf/hyperf/pull/1819) 新增 [hyperf/signal](https://github.com/hyperf/signal) 元件；
- [#1844](https://github.com/hyperf/hyperf/pull/1844) 為 [hyperf/model-cache](https://github.com/hyperf/model-cache) 元件中的 `ttl` 屬性增加了 `\DateInterval` 型別的支援；
- [#1855](https://github.com/hyperf/hyperf/pull/1855) 連線池新增了 `ConstantFrequency` 恆定頻率策略來釋放限制的連線；
- [#1871](https://github.com/hyperf/hyperf/pull/1871) 為 Guzzle 增加 `sink` 選項支援；
- [#1805](https://github.com/hyperf/hyperf/pull/1805) 新增 Coroutine Server 協程服務支援；
  - 變更了 `Hyperf\Contract\ProcessInterface` 中的 `bind(Server $server)` 方法宣告為 `bind($server)`；
  - 變更了 `Hyperf\Contract\ProcessInterface` 中的 `isEnable()` 方法宣告為 `isEnable($server)`；
  - 配置中心、Crontab、服務監控、訊息佇列消費者現在可以透過協程模式來執行，且在使用協程服務模式時，也必須以協程模式來執行；
  - `Hyperf\AsyncQueue\Environment` 的作用域改為當前協程內，而不是整個程序；
  - 協程模式下不再支援 Task 機制；
- [#1877](https://github.com/hyperf/hyperf/pull/1877) 在 PHP 8 下使用 `@Inject` 註解時支援透過成員屬性強型別宣告來替代 `@var` 宣告，如下所示：

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

## 最佳化

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

- [#1696](https://github.com/hyperf/hyperf/pull/1696) 修復方法 `Context::copy` 傳入欄位 `keys` 後無法正常使用的 BUG。
- [#1708](https://github.com/hyperf/hyperf/pull/1708) [#1718](https://github.com/hyperf/hyperf/pull/1718) 修復 `hyperf/socketio-server` 元件記憶體溢位等 BUG。

## 最佳化

- [#1710](https://github.com/hyperf/hyperf/pull/1710) MAC 系統下不再使用 `cli_set_process_title` 方法設定程序名。

# v1.1.30 - 2020-05-07

## 新增

- [#1616](https://github.com/hyperf/hyperf/pull/1616) 新增 ORM 方法 `morphWith` 和 `whereHasMorph`。
- [#1651](https://github.com/hyperf/hyperf/pull/1651) 新增 `socket.io-server` 元件。
- [#1666](https://github.com/hyperf/hyperf/pull/1666) [#1669](https://github.com/hyperf/hyperf/pull/1669) 新增 AMQP RPC 客戶端。

## 修復

- [#1682](https://github.com/hyperf/hyperf/pull/1682) 修復 `RpcPoolTransporter` 的連線池配置不生效的 BUG。
- [#1683](https://github.com/hyperf/hyperf/pull/1683) 修復 `RpcConnection` 連線失敗後，相同協程內無法正常重置連線的 BUG。

## 最佳化

- [#1670](https://github.com/hyperf/hyperf/pull/1670) 最佳化掉 `Cache 元件` 一條無意義的刪除指令。

# v1.1.28 - 2020-04-30

## 新增

- [#1645](https://github.com/hyperf/hyperf/pull/1645) 匿名函式路由支援引數注入。
- [#1647](https://github.com/hyperf/hyperf/pull/1647) 為 `model-cache` 元件新增 `RedisStringHandler`。
- [#1654](https://github.com/hyperf/hyperf/pull/1654) 新增 `RenderException` 統一捕獲 `view` 元件丟擲的異常。

## 修復

- [#1639](https://github.com/hyperf/hyperf/pull/1639) 修復 `rpc-client` 會從 `consul` 中獲取到不健康節點的 BUG。
- [#1641](https://github.com/hyperf/hyperf/pull/1641) 修復 `rpc-client` 獲取到的結果為 `null` 時，會丟擲 `RequestException` 的 BUG。
- [#1641](https://github.com/hyperf/hyperf/pull/1641) 修復 `rpc-server` 中 `jsonrpc-tcp-length-check` 協議，無法在 `consul` 中新增心跳檢查的 BUG。
- [#1650](https://github.com/hyperf/hyperf/pull/1650) 修復指令碼 `describe:routes` 列表展示有誤的 BUG。
- [#1655](https://github.com/hyperf/hyperf/pull/1655) 修復 `MysqlProcessor::processColumns` 無法在 `MySQL Server 8.0` 版本中正常工作的 BUG。

## 最佳化

- [#1636](https://github.com/hyperf/hyperf/pull/1636) 最佳化 `co-phpunit` 指令碼，當出現 `case` 驗證失敗後，協程也可以正常結束。


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
- [#1419](https://github.com/hyperf/hyperf/pull/1419) 允許 ConfigFetcher 透過一個協程啟動而無需額外啟動一個程序；
- [#1424](https://github.com/hyperf/hyperf/pull/1424) 允許使用者透過配置檔案的形式修改 `session_name` 配置；
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

## 最佳化

- [#1305](https://github.com/hyperf/hyperf/pull/1305) 最佳化 `hyperf\metric` 中的邊界條件；
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

## 最佳化

- [#1273](https://github.com/hyperf/hyperf/pull/1273) 最佳化 gRPC 客戶端：
  - 最佳化使 gRPC 客戶端在當連線與 Server 斷開時會自動重連；
  - 最佳化使當 gRPC 客戶端被垃圾回收時，已建立的連線會自動關閉；
  - 修復關閉了的客戶端依舊會持有 HTTP2 連線的問題；
  - 修復 gRPC 客戶端的 channel pool 可能會存在非空 channel 的問題；
  - 最佳化使 gRPC 客戶端會自動初始化，所以現在可以在建構函式和容器注入下使用；

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
- [#1219](https://github.com/hyperf/hyperf/pull/1219) 為 AMQP 消費者增加 `enable` 屬性，透過該屬性來控制該消費者是否跟隨 Server 一同啟動；

## 修復

- [#1208](https://github.com/hyperf/hyperf/pull/1208) 修復 Exception 和 error 在 JSON-RPC TCP Server 下無法被正確處理的問題；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 修復 JSON-RPC 沒有檢查 Request ID 和 Response ID 是否一致的問題；
- [#1223](https://github.com/hyperf/hyperf/pull/1223) 修復 ConfigProvider 掃描器不會掃描 composer.json 內 require-dev 的配置；
- [#1254](https://github.com/hyperf/hyperf/pull/1254) 修復執行 `init-proxy.sh` 命令在某些環境如 Alpine 下會報 bash 不存在的問題；

## 最佳化

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

## 最佳化

- [#1211](https://github.com/hyperf/hyperf/pull/1211) 自動將專案名轉化為 `prometheus` 的規範命名；

## 修改

- [#1217](https://github.com/hyperf/hyperf/pull/1217) 將 `zendframework/zend-mime` 替換為 `laminas/laminas-mine`；

# v1.1.12 - 2019-12-26

## 新增

- [#1177](https://github.com/hyperf/hyperf/pull/1177) 為 `jsonrpc` 元件增加了新的協議 `jsonrpc-tcp-length-check`，並對部分程式碼進行了最佳化；

## 修復

- [#1175](https://github.com/hyperf/hyperf/pull/1175) 修復 `Hyperf\Utils\Collection::random` 方法不支援傳入 `null`；
- [#1178](https://github.com/hyperf/hyperf/pull/1178) 修復 `Hyperf\Database\Query\Builder::chunkById` 方法不支援元素是 `array` 的情況；
- [#1189](https://github.com/hyperf/hyperf/pull/1189) 修復 `Hyperf\Utils\Collection::operatorForWhere` 方法，`operator` 只能傳入 `string` 的 BUG；

## 最佳化

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

## 最佳化

- [#1014](https://github.com/hyperf/hyperf/pull/1014) 最佳化 `Command::execute` 的返回值型別；
- [#1022](https://github.com/hyperf/hyperf/pull/1022) 提供更清晰友好的連線池報錯資訊；
- [#1039](https://github.com/hyperf/hyperf/pull/1039) 在 CoreMiddleware 中自動設定最新的 ServerRequest 物件到 Context；

# v1.1.7 - 2019-11-21

## 新增

- [#860](https://github.com/hyperf/hyperf/pull/860) 新增 [hyperf/retry](https://github.com/hyperf/retry) 元件；
- [#952](https://github.com/hyperf/hyperf/pull/952) 新增 ThinkTemplate 檢視引擎支援；
- [#973](https://github.com/hyperf/hyperf/pull/973) 新增 JSON RPC 在 TCP 協議下的連線池支援，透過 `Hyperf\JsonRpc\JsonRpcPoolTransporter` 來使用連線池版本；
- [#976](https://github.com/hyperf/hyperf/pull/976) 為 `hyperf/amqp` 元件新增  `close_on_destruct` 選項引數，用來控制程式碼在執行解構函式時是否主動去關閉連線；

## 變更

- [#944](https://github.com/hyperf/hyperf/pull/944) 將元件內所有使用 `@Listener` 和 `@Process` 註解來註冊的改成透過 `ConfigProvider`來註冊；
- [#977](https://github.com/hyperf/hyperf/pull/977) 調整 `init-proxy.sh` 命令的行為，改成只刪除 `runtime/container` 目錄；

## 修復

- [#955](https://github.com/hyperf/hyperf/pull/955) 修復 `hyperf/db` 元件的 `port` 和 `charset` 引數無效的問題；
- [#956](https://github.com/hyperf/hyperf/pull/956) 修復模型快取中使用到`RedisHandler::incr` 在叢集模式下會失敗的問題；
- [#966](https://github.com/hyperf/hyperf/pull/966) 修復當在非 Worker 程序環境下使用分頁器會報錯的問題；
- [#968](https://github.com/hyperf/hyperf/pull/968) 修復當 `classes` 和 `annotations` 兩種 Aspect 切入模式同時存在於一個類時，其中一個可能會失效的問題；
- [#980](https://github.com/hyperf/hyperf/pull/980) 修復 Session 元件內 `migrate`, `save` 核 `has` 方法無法使用的問題；
- [#982](https://github.com/hyperf/hyperf/pull/982) 修復 `Hyperf\GrpcClient\GrpcClient::yield` 在獲取 Channel Pool 時沒有透過正確的獲取方式去獲取的問題；
- [#987](https://github.com/hyperf/hyperf/pull/987) 修復透過 `gen:command` 命令生成的命令類缺少呼叫 `parent::configure()` 方法的問題；

## 最佳化

- [#991](https://github.com/hyperf/hyperf/pull/991) 最佳化 `Hyperf\DbConnection\ConnectionResolver::connection`的異常情況處理；

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

## 最佳化

- [#907](https://github.com/hyperf/hyperf/pull/907) 最佳化 `Nats` 消費者頻繁重啟；
- [#928](https://github.com/hyperf/hyperf/pull/928) `Hyperf\ModelCache\Cacheable::query` 批次修改資料時，可以刪除對應快取；
- [#936](https://github.com/hyperf/hyperf/pull/936) 最佳化呼叫模型快取 `increment` 時，可能因併發情況導致的資料有錯；

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

## 最佳化

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

- [#779](https://github.com/hyperf/hyperf/pull/779) 修復 `JPG` 檔案驗證不透過的問題；
- [#787](https://github.com/hyperf/hyperf/pull/787) 修復 `db:seed` 引數 `--class` 多餘，導致報錯的問題；
- [#795](https://github.com/hyperf/hyperf/pull/795) 修復自定義程序在異常丟擲後，無法正常重啟的 BUG；
- [#796](https://github.com/hyperf/hyperf/pull/796) 修復 `etcd` 配置中心 `enable` 即時設為 `false`，在專案啟動時，依然會拉取配置的 BUG；

## 最佳化

- [#781](https://github.com/hyperf/hyperf/pull/781) 可以根據國際化元件配置釋出驗證器語言包到規定位置；
- [#796](https://github.com/hyperf/hyperf/pull/796) 最佳化 `ETCD` 客戶端，不會多次建立 `HandlerStack`；
- [#797](https://github.com/hyperf/hyperf/pull/797) 最佳化子程序重啟

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

- [#664](https://github.com/hyperf/hyperf/pull/664) 調整透過 `gen:request` 命令生成 FormRequest 時 `authorize` 方法的預設返回值；
- [#665](https://github.com/hyperf/hyperf/pull/665) 修復啟動時永遠會自動生成代理類的問題；
- [#667](https://github.com/hyperf/hyperf/pull/667) 修復當訪問一個不存在的路由時 `Hyperf\Validation\Middleware\ValidationMiddleware` 會丟擲異常的問題；
- [#672](https://github.com/hyperf/hyperf/pull/672) 修復當 Action 方法上的引數型別為非物件型別時 `Hyperf\Validation\Middleware\ValidationMiddleware` 會丟擲一個未捕獲的異常的問題；
- [#674](https://github.com/hyperf/hyperf/pull/674) 修復使用 `gen:model` 命令從資料庫生成模型時模型表名錯誤的問題；

# v1.1.0 - 2019-10-08

## 新增

- [#401](https://github.com/hyperf/hyperf/pull/401) 新增了 `Hyperf\HttpServer\Router\Dispatched` 物件來儲存解析的路由資訊，在使用者中介軟體之前便解析完成以便後續的使用，同時也修復了路由裡帶參時中介軟體失效的問題；
- [#402](https://github.com/hyperf/hyperf/pull/402) 新增 `@AsyncQueueMessage` 註解，透過定義此註解在方法上，表明這個方法的實際執行邏輯是投遞給 Async-Queue 佇列去消費；
- [#418](https://github.com/hyperf/hyperf/pull/418) 允許傳送 WebSocket 訊息到任意的 fd，即使當前的 Worker 程序不持有對應的 fd，框架會自動進行程序間通訊來實現傳送；
- [#420](https://github.com/hyperf/hyperf/pull/420) 為資料庫模型增加新的事件機制，與 PSR-15 的事件排程器相配合，可以解耦的定義 Listener 來監聽模型事件；
- [#429](https://github.com/hyperf/hyperf/pull/429) [#643](https://github.com/hyperf/hyperf/pull/643) 新增 Validation 表單驗證器元件，這是一個衍生於 [illuminate/validation](https://github.com/illuminate/validation) 的元件，感謝 Laravel 開發組提供如此好用的驗證器元件，；
- [#441](https://github.com/hyperf/hyperf/pull/441) 當 Redis 連線處於低使用頻率的情況下自動關閉空閒連線；
- [#478](https://github.com/hyperf/hyperf/pull/441) 更好的適配 OpenTracing 協議，同時適配 [Jaeger](https://www.jaegertracing.io/)，Jaeger 是一款優秀的開源的端對端分散式呼叫鏈追蹤系統；
- [#500](https://github.com/hyperf/hyperf/pull/499) 為 `Hyperf\HttpServer\Contract\ResponseInterface` 增加鏈式方法呼叫支援，解決呼叫了代理方法的方法後無法再呼叫原始方法的問題；
- [#523](https://github.com/hyperf/hyperf/pull/523) 為  `gen:model` 命令新增了 `table-mapping` 選項；
- [#555](https://github.com/hyperf/hyperf/pull/555) 新增了一個全域性函式 `swoole_hook_flags` 來獲取由常量 `SWOOLE_HOOK_FLAGS` 所定義的 Runtime Hook 等級，您可以在 `bin/hyperf.php` 透過 `! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);` 的方式來定義該常量，即 Runtime Hook 等級；
- [#596](https://github.com/hyperf/hyperf/pull/596)  為`@Inject` 註解增加了  `required` 引數，當您定義 `@Inject(required=false)` 註解到一個成員屬性上，那麼當該依賴項不存在時也不會丟擲 `Hyperf\Di\Exception\NotFoundException` 異常，而是以預設值 `null` 來注入， `required` 引數的預設值為 `true`，當在構造器注入的情況下，您可以透過對構造器的引數定義為 `nullable` 來達到同樣的目的；
- [#597](https://github.com/hyperf/hyperf/pull/597) 為 AsyncQueue 元件的消費者增加 `Concurrent` 來控制消費速率；
- [#599](https://github.com/hyperf/hyperf/pull/599) 為 AsyncQueue 元件的消費者增加根據當前重試次數來設定該訊息的重試等待時長的功能，可以為訊息設定階梯式的重試等待；
- [#619](https://github.com/hyperf/hyperf/pull/619) 為 Guzzle 客戶端增加 HandlerStackFactory 類，以便更便捷地建立一個 HandlerStack；
- [#620](https://github.com/hyperf/hyperf/pull/620) 為 AsyncQueue 元件的消費者增加自動重啟的機制；
- [#629](https://github.com/hyperf/hyperf/pull/629) 允許透過配置檔案的形式為 Apollo 客戶端定義  `clientIp`, `pullTimeout`, `intervalTimeout` 配置；
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
use Hyperf\Context\ApplicationContext;

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
- [#638](https://github.com/hyperf/hyperf/pull/638) 重新命名了 `db:model` 命令為 `gen:model` 命令，同時增加了一個 Visitor 來最佳化建立的 `$connection` 成員屬性，如果要建立的模型類的 `$connection` 屬性的值與繼承的父類一致，那麼建立的模型類將不會包含此屬性；

## 移除

- [#401](https://github.com/hyperf/hyperf/pull/401) 移除了 `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\HttpServer\ServerFactory`, `Hyperf\GrpcServer\ServerFactory` 類；
- [#402](https://github.com/hyperf/hyperf/pull/402) 移除了棄用的 `AsyncQueue::delay` 方法；
- [#563](https://github.com/hyperf/hyperf/pull/563) 移除了棄用的 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量，使用 `Hyperf\Server\ServerInterface::SERVER_BASE` 來替代；
- [#602](https://github.com/hyperf/hyperf/pull/602) 移除了 `Hyperf\Utils\Coroutine\Concurrent` 的 `timeout` 引數；
- [#612](https://github.com/hyperf/hyperf/pull/612) 移除了 RingPHP Handler 裡沒有使用到的 `$url` 變數；
- [#616](https://github.com/hyperf/hyperf/pull/616) [#618](https://github.com/hyperf/hyperf/pull/618) 移除了 Guzzle 裡一些無用的程式碼；

## 最佳化

- [#644](https://github.com/hyperf/hyperf/pull/644) 優化了註解掃描的流程，分開 `app` 和 `vendor` 兩部分來掃描註解，大大減少了使用者的掃描耗時；
- [#653](https://github.com/hyperf/hyperf/pull/653) 優化了 Swoole shortname 的檢測邏輯，現在的檢測邏輯更加貼合 Swoole 的實際配置場景，也不只是 `swoole.use_shortname = "Off"` 才能透過檢測了；

## 修復

- [#448](https://github.com/hyperf/hyperf/pull/448) 修復了當 HTTP Server 或 WebSocket Server 存在時，TCP Server 有可能無法啟動的問題；
- [#623](https://github.com/hyperf/hyperf/pull/623) 修復了當傳遞一個 `null` 值到代理類的方法引數時，方法仍然會獲取方法預設值的問題；

# v1.0.16 - 2019-09-20

## 新增

- [#565](https://github.com/hyperf/hyperf/pull/565) 增加對 Redis 客戶端的 `options` 配置引數支援；
- [#580](https://github.com/hyperf/hyperf/pull/580) 增加協程併發控制特性，透過 `Hyperf\Utils\Coroutine\Concurrent` 可以實現一個程式碼塊內限制同時最多執行的協程數量；

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

## 最佳化

- [#549](https://github.com/hyperf/hyperf/pull/549) 優化了 `Hyperf\Amqp\Connection\SwooleIO` 的 `read` 和 `write` 方法，減少不必要的重試；
- [#559](https://github.com/hyperf/hyperf/pull/559) 最佳化 `Hyperf\HttpServer\Response::redirect()` 方法，自動識別連結首位是否為斜槓併合理修正引數；
- [#560](https://github.com/hyperf/hyperf/pull/560) 最佳化 `Hyperf\WebSocketServer\CoreMiddleware`，移除了不必要的程式碼；

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

- [#405](https://github.com/hyperf/hyperf/pull/405) 增加 `Hyperf\Utils\Context::override()` 方法，現在你可以透過 `override` 方法獲取某些協程上下文的值並修改覆蓋它；
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
- [#370](https://github.com/hyperf/hyperf/pull/370) 修復了 `Hyperf\GrpcClient\BaseClient` 的 `$client` 屬性在流式傳輸的時候設定了錯誤的型別的值的問題, 同時增加了預設的 `content-type`  為 `application/grpc+proto`，以及允許使用者透過自定義 `Request` 物件來重寫 `buildRequest()` 方法；

## 變更

- [#356](https://github.com/hyperf/hyperf/pull/356) [#390](https://github.com/hyperf/hyperf/pull/390) 最佳化 aysnc-queue 元件當生成 Job 時，如果 Job 實現了 `Hyperf\Contract\CompressInterface`，那麼 Job 物件會被壓縮為一個更小的物件；
- [#358](https://github.com/hyperf/hyperf/pull/358) 只有當 `$enableCache` 為 `true` 時才生成註解快取檔案；
- [#359](https://github.com/hyperf/hyperf/pull/359) [#390](https://github.com/hyperf/hyperf/pull/390) 為 `Collection` 和 `Model` 增加壓縮能力，當類實現 `Hyperf\Contract\CompressInterface` 可透過 `compress` 方法生成一個更小的物件；

# v1.0.10 - 2019-08-09

## 新增

- [#321](https://github.com/hyperf/hyperf/pull/321) 為 HTTP Server 的 Controller/RequestHandler 引數增加自定義物件型別的陣列支援，特別適用於 JSON RPC 下，現在你可以透過在方法上定義 `@var Object[]` 來獲得框架自動反序列化對應物件的支援；
- [#324](https://github.com/hyperf/hyperf/pull/324) 增加一個實現於 `Hyperf\Contract\IdGeneratorInterface` 的 ID 生成器 `NodeRequestIdGenerator`；
- [#336](https://github.com/hyperf/hyperf/pull/336) 增加動態代理的 RPC 客戶端功能；
- [#346](https://github.com/hyperf/hyperf/pull/346) [#348](https://github.com/hyperf/hyperf/pull/348) 為 `hyperf/cache` 快取元件增加檔案驅動；

## 變更

- [#330](https://github.com/hyperf/hyperf/pull/330) 當掃描的 $paths 為空時，不輸出掃描資訊；
- [#328](https://github.com/hyperf/hyperf/pull/328) 根據 Composer 的 PSR-4 定義的規則載入業務專案；
- [#329](https://github.com/hyperf/hyperf/pull/329) 最佳化 JSON RPC 服務端和客戶端的異常訊息處理；
- [#340](https://github.com/hyperf/hyperf/pull/340) 為 `make` 函式增加索引陣列的傳參方式；
- [#349](https://github.com/hyperf/hyperf/pull/349) 重新命名下列類，修正由於拼寫錯誤導致的命名錯誤；

|                     原類名                      |                  修改後的類名                     |
|:----------------------------------------------|:-----------------------------------------------|
| Hyperf\\Database\\Commands\\Ast\\ModelUpdateVistor | Hyperf\\Database\\Commands\\Ast\\ModelUpdateVisitor |
|       Hyperf\\Di\\Aop\\ProxyClassNameVistor       |       Hyperf\\Di\\Aop\\ProxyClassNameVisitor       |
|         Hyperf\\Di\\Aop\\ProxyCallVistor          |         Hyperf\\Di\\Aop\\ProxyCallVisitor          |

## 修復

- [#325](https://github.com/hyperf/hyperf/pull/325) 最佳化 RPC 服務註冊時會多次呼叫 Consul Services 的問題；
- [#332](https://github.com/hyperf/hyperf/pull/332) 修復 `Hyperf\Tracer\Middleware\TraceMiddeware` 在新版的 openzipkin/zipkin 下的型別約束錯誤；
- [#333](https://github.com/hyperf/hyperf/pull/333) 修復 `Redis::delete()` 方法在 5.0 版不存在的問題；
- [#334](https://github.com/hyperf/hyperf/pull/334) 修復向阿里雲 ACM 配置中心拉取配置時，部分情況下部分配置無法更新的問題；
- [#337](https://github.com/hyperf/hyperf/pull/337) 修復當 Header 的 key 為非字串型別時，會返回 500 響應的問題；
- [#338](https://github.com/hyperf/hyperf/pull/338) 修復 `ProviderConfig::load` 在遇到重複 key 時會導致在深度合併時將字串轉換成陣列的問題；

# v1.0.9 - 2019-08-03

## 新增

- [#317](https://github.com/hyperf/hyperf/pull/317) 增加 `composer-json-fixer` 來最佳化 composer.json 檔案的內容；
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
- [#254](https://github.com/hyperf/hyperf/pull/254) 增加 `RequestMapping::$methods` 對陣列傳值的支援, 現在可以透過 `@RequestMapping(methods={"GET"})` 和 `@RequestMapping(methods={RequestMapping::GET})` 兩種新的方式定義方法；
- [#255](https://github.com/hyperf/hyperf/pull/255) 控制器返回 `Hyperf\Utils\Contracts\Arrayable` 會自動轉換為 Response 物件, 同時對返回字串的響應物件增加  `text/plain` Content-Type;
- [#256](https://github.com/hyperf/hyperf/pull/256) 如果 `Hyperf\Contract\IdGeneratorInterface` 存在容器繫結關係, 那麼 `json-rpc` 客戶端會根據該類自動生成一個請求 ID 並儲存在 Request attribute 裡，同時完善了 `JSON RPC` 在 TCP 協議下的服務註冊及健康檢查；

## 變更

- [#247](https://github.com/hyperf/hyperf/pull/247) 使用 `WorkerStrategy` 作為預設的計劃任務排程策略；
- [#256](https://github.com/hyperf/hyperf/pull/256) 最佳化 `JSON RPC` 的錯誤處理，現在當方法不存在時也會返回一個標準的 `JSON RPC` 錯誤物件；

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
- [#198](https://github.com/hyperf/hyperf/pull/198) 最佳化 `Hyperf\Di\Container` 的 `has()` 方法, 當傳遞一個不可例項化的示例（如介面）至 `$container->has($interface)` 方法時，會返回 `false`；
- [#199](https://github.com/hyperf/hyperf/pull/199) 當生產 AMQP 訊息失敗時，會自動重試一次；
- [#200](https://github.com/hyperf/hyperf/pull/200) 透過 Git 打包專案的部署包時，不再包含 `tests` 資料夾；

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
- [#61](https://github.com/hyperf/hyperf/pull/61) 透過 `db:model` 命令建立模型時增加屬性型別
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
