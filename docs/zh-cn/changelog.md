# 版本更新记录

# v3.0.24 - 2023-06-10

## 修复

- [#5794](https://github.com/hyperf/hyperf/pull/5794) 修复代理类中 `__FILE__` 和 `__DIR__` 定位错误的问题。
- [#5803](https://github.com/hyperf/hyperf/pull/5803) 修复组件 `hyperf/http-server` 不适配新版本 `Psr7` 的问题。
- [#5808](https://github.com/hyperf/hyperf/pull/5808) 修复验证器规则 `le`、`lte`、`gt`、`gte` 不发正常比较 `numeric` 和 `string`。

## 优化

- [#5789](https://github.com/hyperf/hyperf/pull/5789) 支持高版本 `psr/http-message`。
- [#5806](https://github.com/hyperf/hyperf/pull/5806) 优化 Swow 服务，默认情况下合并通用配置。
- [#5814](https://github.com/hyperf/hyperf/pull/5814) 增加方法 `build_sql`，在抛出异常 `QueryException` 时，可以快速的构建 `SQL` .

# v3.0.23 - 2023-06-02

## 新增

- [#5757](https://github.com/hyperf/hyperf/pull/5757) 支持 `Nacos` 服务注册与发现签名机制。
- [#5765](https://github.com/hyperf/hyperf/pull/5765) 为 `database` 组件增加全文检索的功能。

## 修复

- [#5782](https://github.com/hyperf/hyperf/pull/5782) 修复 `prometheus` 无法正常收集 `histograms` 的问题。

## 优化

- [#5768](https://github.com/hyperf/hyperf/pull/5768) 为 `Hyperf\Command\Annotation\Command` 组件增加参数支持。
- [#5780](https://github.com/hyperf/hyperf/pull/5780) 修复 `Zipkin\Propagation\Map` 中 `String` 类型检测错误的问题。

# v3.0.22 - 2023-05-27

## 新增

- [#5760](https://github.com/hyperf/hyperf/pull/5760) 为组件 `hyperf/translation` 组件的助手函数增加命名空间。
- [#5761](https://github.com/hyperf/hyperf/pull/5761) 新增方法 `Hyperf\Coordinator\Timer::until()`.

## 优化

- [#5741](https://github.com/hyperf/hyperf/pull/5741) 为 `Hyperf\DB\MySQLConnection` 增加即将过期的标签。
- [#5702](https://github.com/hyperf/hyperf/pull/5702) 优化了 `Hyperf\Metric\Adapter\Prometheus\Redis` 的代码，使其允许被重写 `KEY` 键前缀。
- [#5762](https://github.com/hyperf/hyperf/pull/5762) 自定义进程默认使用非阻塞模式。

# v3.0.21 - 2023-05-18

## 新增

- [#5721](https://github.com/hyperf/hyperf/pull/5721) 为 `Request` 生命周期事件，增加 `exception` 参数。
- [#5723](https://github.com/hyperf/hyperf/pull/5723) 为 `hyperf/db` 组件增加 `Swoole5.x` 的 `PgSQL` 支持。
- [#5725](https://github.com/hyperf/hyperf/pull/5725) 为 `hyperf/db` 组件增加 `Swoole4.x` 的 `PgSQL` 支持。
- [#5731](https://github.com/hyperf/hyperf/pull/5731) 新增方法 `Arr::hasAny()`。

## 修复

- [#5726](https://github.com/hyperf/hyperf/pull/5726) [#5730](https://github.com/hyperf/hyperf/pull/5730) 修复使用 `pgsql-swoole` 类型的 `ORM` 时，`PgSQL` 链接不会自动初始化的问题。

## 优化

- [#5718](https://github.com/hyperf/hyperf/pull/5718) 优化了 `view-engine` 组件的代码，并增加了一些单元测试。
- [#5719](https://github.com/hyperf/hyperf/pull/5719) 优化了 `metric` 组件的代码，并增加了一些单元测试。
- [#5720](https://github.com/hyperf/hyperf/pull/5720) 优化了 `Hyperf\Metric\Listener\OnPipeMessage` 的代码，来避免消息阻塞的问题。

# v3.0.20 - 2023-05-12

## 新增

- [#5707](https://github.com/hyperf/hyperf/pull/5707) 新增助手函数 `Hyperf\Config\config`。
- [#5711](https://github.com/hyperf/hyperf/pull/5711) 新增方法 `Arr::mapWithKeys()`。
- [#5715](https://github.com/hyperf/hyperf/pull/5715) 增加请求级别生命周期事件。

## 修复

- [#5709](https://github.com/hyperf/hyperf/pull/5709) 当日志组不存在时，修复错误日志记录有误的问题。
- [#5713](https://github.com/hyperf/hyperf/pull/5713) 为 `Hyperf\SuperGlobals\Proxy\Server` 增加通过自身进行实例化的能力。

## 优化

- [#5716](https://github.com/hyperf/hyperf/pull/5716) 为协程风格服务增加超全局变量的支持。

# v3.0.19 - 2023-05-06

## 修复

- [#5679](https://github.com/hyperf/hyperf/pull/5679) 修复 `#[Task]` 注解的 `$timeout` 类型与 `TaskAspect` 不一致的问题。
- [#5684](https://github.com/hyperf/hyperf/pull/5684) 修复使用了 `break` 语法后，`blade` 视图模板无法正常使用的问题。

## 新增

- [#5680](https://github.com/hyperf/hyperf/pull/5680) 为 `rpc-multiplex` 增加存储 `RPC` 上下文的能力。
- [#5695](https://github.com/hyperf/hyperf/pull/5695) 为数据库迁移组件，增加设置 `datetime` 类型的创建时间和修改时间的功能。
- [#5699](https://github.com/hyperf/hyperf/pull/5699) 增加 `Model::resolveRelationUsing()`，用来动态创建模型关系。

## 优化

- [#5694](https://github.com/hyperf/hyperf/pull/5694) 将 `hyperf/utils` 从 `hyperf/rpc` 组件中移除。
- [#5696](https://github.com/hyperf/hyperf/pull/5694) 使用 `Hyperf\Coroutine\Coroutine::sleep()` 替代 `Swoole\Coroutine::sleep()`。

# v3.0.18 - 2023-04-26

## 新增

- [#5672](https://github.com/hyperf/hyperf/pull/5672) 将部分 `utils` 中的方法，复制到 `hyperf/support` 组件中，并增加对应的命名空间。

## 修复

- [#5662](https://github.com/hyperf/hyperf/pull/5662) 修复 `pgsql-swoole` 执行失败时，无法抛出异常的问题。

## 优化

- [#5660](https://github.com/hyperf/hyperf/pull/5660) 将 `hyperf/codec` 从 `hyperf/utils` 分离出来。
- [#5663](https://github.com/hyperf/hyperf/pull/5663) 将 `hyperf/serializer` 从 `hyperf/utils` 分离出来。
- [#5666](https://github.com/hyperf/hyperf/pull/5666) 将 `Packers` 从 `hyperf/utils` 分离到 `hyperf/codec` 中。
- [#5668](https://github.com/hyperf/hyperf/pull/5668) 将 `hyperf/support` 从 `hyperf/utils` 分离出来。
- [#5670](https://github.com/hyperf/hyperf/pull/5670) 将 `hyperf/code-parser` 从 `hyperf/utils` 分离出来。
- [#5671](https://github.com/hyperf/hyperf/pull/5671) 使用 `Hyperf\Coroutine\Channel\Pool` 代替 `Hyperf\Utils\ChannelPool` 。
- [#5674](https://github.com/hyperf/hyperf/pull/5674) 将 `Hyperf\Utils` 命名空间的类和方法，使用新组件进行替换。

# v3.0.17 - 2023-04-19

## 修复

- [#5642](https://github.com/hyperf/hyperf/pull/5642) 修复使用批量读取模型缓存时，遇到不存在的数据时，无法初始化空缓存的问题。
- [#5643](https://github.com/hyperf/hyperf/pull/5643) 修复使用批量读取模型缓存时，空缓存无法正常使用的问题。
- [#5649](https://github.com/hyperf/hyperf/pull/5649) 修复协程风格下，无法初始化数据库字段收集器的问题。

## 新增

- [#5634](https://github.com/hyperf/hyperf/pull/5634) 新增助手函数 `Hyperf\Stringable\str()`。
- [#5639](https://github.com/hyperf/hyperf/pull/5639) 新增方法 `Redis::pipeline()` 和 `Redis::transaction()`。
- [#5641](https://github.com/hyperf/hyperf/pull/5641) 为模型缓存 `loadCache` 增加嵌套初始化缓存的能力。
- [#5646](https://github.com/hyperf/hyperf/pull/5646) 增加 `PriorityDefinition` 类，来处理容器 `dependencies` 优先级的问题。

## 优化

- [#5634](https://github.com/hyperf/hyperf/pull/5634) 使用 `Hyperf\Stringable\Str` 替代 `Hyperf\Utils\Str`。
- [#5636](https://github.com/hyperf/hyperf/pull/5636) 优化 `kafka` 消费者，启动时等待消费过长的问题。
- [#5648](https://github.com/hyperf/hyperf/pull/5648) 将依赖 `hyperf/utils` 从 `hyperf/guzzle` 中移除。

# v3.0.16 - 2023-04-12

## 修复

- [#5627](https://github.com/hyperf/hyperf/pull/5627) 修复方法 `Hyperf\Context\Context::destroy` 支持协程下调用。

## 优化

- [#5616](https://github.com/hyperf/hyperf/pull/5616) 将 `ApplicationContext` 从 `hyperf/utils` 分离到 `hyperf/context`。
- [#5617](https://github.com/hyperf/hyperf/pull/5617) 将 `hyperf/guzzle` 从 `hyperf/consul` 依赖中移除。
- [#5618](https://github.com/hyperf/hyperf/pull/5618) 支持在 Swagger 面板中设置默认路由。
- [#5619](https://github.com/hyperf/hyperf/pull/5619) [#5620](https://github.com/hyperf/hyperf/pull/5620) 将 `hyperf/coroutine` 从 `hyperf/utils` 分离出来。
- [#5621](https://github.com/hyperf/hyperf/pull/5621) 使用 `Hyperf\Context\ApplicationContext` 代替 `Hyperf\Utils\ApplicationContext`。
- [#5622](https://github.com/hyperf/hyperf/pull/5622) 将 `CoroutineProxy` 从 `hyperf/utils` 分离到 `hyperf/context`。
- [#5623](https://github.com/hyperf/hyperf/pull/5623) 使用 `Hyperf\Coroutine\Coroutine` 替代 `Hyperf\Utils\Coroutine`。
- [#5624](https://github.com/hyperf/hyperf/pull/5624) 将 `Channel` 相关方法从 `hyperf/utils` 分离到 `hyperf/coroutine`。
- [#5629](https://github.com/hyperf/hyperf/pull/5629) 将 `Hyperf\Utils\Arr` 继承 `Hyperf\Collection\Arr`。

# v3.0.15 - 2023-04-07

## 新增

- [#5606](https://github.com/hyperf/hyperf/pull/5606) 新增配置 `server.options.send_channel_capacity` 用来控制使用 `协程风格` 服务时，是否使用 `SafeSocket` 来返回数据。

## 优化

- [#5593](https://github.com/hyperf/hyperf/pull/5593) [#5598](https://github.com/hyperf/hyperf/pull/5598) 使用 `Hyperf\Collection\Collection` 替代 `Hyperf\Utils\Collection`。
- [#5594](https://github.com/hyperf/hyperf/pull/5594) 使用 `Hyperf\Collection\Arr` 替代 `Hyperf\Utils\Arr`。
- [#5596](https://github.com/hyperf/hyperf/pull/5596) 将 `hyperf/pipeline` 从 `hyperf/utils` 分离出来。
- [#5599](https://github.com/hyperf/hyperf/pull/5599) 使用 `Hyperf\Pipeline\Pipeline` 替代 `Hyperf\Utils\Pipeline`。

# v3.0.14 - 2023-04-01

## 修复

- [#5578](https://github.com/hyperf/hyperf/pull/5578) 修复了无法序列化 `Crontab` 的问题。
- [#5579](https://github.com/hyperf/hyperf/pull/5579) 修复 `crontab:run` 无法正常工作的问题。

## 优化

- [#5572](https://github.com/hyperf/hyperf/pull/5572) 优化了 `HTTP` 服务，使用 `WritableConnection` 实现，支持 `Swow`。
- [#5577](https://github.com/hyperf/hyperf/pull/5577) 将组件 `hyperf/collection` 从 `hyperf/utils` 分离。
- [#5580](https://github.com/hyperf/hyperf/pull/5580) 将组件 `hyperf/conditionable` 和 `hyperf/tappable` 从 `hyperf/utils` 分离。
- [#5585](https://github.com/hyperf/hyperf/pull/5585) 优化 `service-governance` 组件，去除了 `consul` 的依赖关系。

# v3.0.13 - 2023-03-26

## 新增

- [#5561](https://github.com/hyperf/hyperf/pull/5561) 为 `hyperf/kafka` 增加自定义定时器的配置。
- [#5562](https://github.com/hyperf/hyperf/pull/5562) 为 `MySQL` 数据库组件，增加 `upsert()` 支持。
- [#5563](https://github.com/hyperf/hyperf/pull/5563) 为 `Crontab` 任务增加是否执行完的逻辑。

## 优化

- [#5544](https://github.com/hyperf/hyperf/pull/5554) 为 `grpc-server` 组件取消 `hyperf/rpc` 的依赖。
- [#5550](https://github.com/hyperf/hyperf/pull/5550) 优化了 `Coordinator Timer` 和 `Crontab Parser` 的代码。
- [#5566](https://github.com/hyperf/hyperf/pull/5566) 基于模型生成 `Swagger Schemas` 时，优化变量类型可以为 `Null`。
- [#5569](https://github.com/hyperf/hyperf/pull/5569) 优化了 `Crontab RunCommand` 的依赖关系。

# v3.0.12 - 2023-03-20

## 新增

- [#4112](https://github.com/hyperf/hyperf/pull/4112) 新增配置项 `kafka.default.enable` 用来控制消费者是否启动。
- [#5533](https://github.com/hyperf/hyperf/pull/5533) [#5535](https://github.com/hyperf/hyperf/pull/5535) 为 `kafka` 组件增加 `client` 和 `socket` 配置，允许开发者自定义。
- [#5536](https://github.com/hyperf/hyperf/pull/5536) 新增组件 `hyperf/http2-client`。
- [#5538](https://github.com/hyperf/hyperf/pull/5538) 为 `hyperf/http2-client` 增加双向流支持。
- [#5511](https://github.com/hyperf/hyperf/pull/5511) 将 `GRPC` 服务统一到 `RPC` 服务中，可以更加方便的进行服务注册与发现。
- [#5543](https://github.com/hyperf/hyperf/pull/5543) 增加 `Nacos` 双向流支持，可以监听到配置中心实时更新的事件。
- [#5545](https://github.com/hyperf/hyperf/pull/5545) 为组件 `hyperf/http2-client` 增加双向流相关的测试。
- [#5546](https://github.com/hyperf/hyperf/pull/5546) 为 `Nacos` 配置中心增加 `GRPC` 功能，可以实时监听配置的变化。

## 优化

- [#5539](https://github.com/hyperf/hyperf/pull/5539) 优化了 `AMQPConnection` 的代码，以支持最新版本的 `php-amqplib` 组件。
- [#5528](https://github.com/hyperf/hyperf/pull/5528) 优化了 `aspects` 的配置，对热重启有更好的支持。
- [#5541](https://github.com/hyperf/hyperf/pull/5541) 提升了 `FactoryResolver` 基于 `XXXFactory` 实例化对象的能力，增加了可选参数配置。

# v3.0.11 - 2023-03-15

## 新增

- [#5499](https://github.com/hyperf/hyperf/pull/5499) 为 `hyperf/constants` 组件增加枚举(>=PHP8.1)类型支持。
- [#5508](https://github.com/hyperf/hyperf/pull/5508) 新增方法 `Hyperf\Rpc\Protocol::getNormalizer`。
- [#5509](https://github.com/hyperf/hyperf/pull/5509) 为 `json-rpc` 组件自动注册 `normalizer`。
- [#5513](https://github.com/hyperf/hyperf/pull/5513) 组件 `rpc-multiplex` 使用默认的 `normalizer` 并对 `rpc-server` 增加自定义 `protocol.normalizer` 的支持。
- [#5518](https://github.com/hyperf/hyperf/pull/5518) 增加方法 `SwooleConnection::getSocket` 用来获取 `Swoole` 的 `Response`。
- [#5520](https://github.com/hyperf/hyperf/pull/5520) 新增方法 `Coroutine::stats()` 和 `Coroutine::exists()`。
- [#5525](https://github.com/hyperf/hyperf/pull/5525) 新增配置 `kafka.default.consume_timeout` 用来控制消费者消费数据的超时时间。
- [#5526](https://github.com/hyperf/hyperf/pull/5526) 新增方法 `Hyperf\Kafka\AbstractConsumer::isEnable()` 用来控制 `kafka` 消费者是否启动。

## 修复

- [#5519](https://github.com/hyperf/hyperf/pull/5519) 修复因 `kafka` 生产者 `loop` 方法导致进程无法正常退出的问题。
- [#5523](https://github.com/hyperf/hyperf/pull/5523) 修复在发生 `kafka rebalance` 的时候，进程无故停止的问题。

## 优化

- [#5510](https://github.com/hyperf/hyperf/pull/5510) 允许开发者自定义 `RPC 客户端` 的 `normalizer` 的实现。
- [#5525](https://github.com/hyperf/hyperf/pull/5525) 当消费 `kafka` 消息时，每个消息会在独立的协程中进行处理。

# v3.0.10 - 2023-03-11

## 修复

- [#5497](https://github.com/hyperf/hyperf/pull/5497) 修复 `apollo` 配置中心，无法正常触发 `ConfigChanged` 事件的问题。

## 新增

- [#5491](https://github.com/hyperf/hyperf/pull/5491) 为 `Str` 和 `Stringable` 新增 `charAt` 方法。
- [#5503](https://github.com/hyperf/hyperf/pull/5503) 新增 `Hyperf\Contract\JsonDeSerializable`。
- [#5504](https://github.com/hyperf/hyperf/pull/5504) 新增 `Hyperf\Utils\Serializer\JsonDeNormalizer`。

## 优化

- [#5493](https://github.com/hyperf/hyperf/pull/5493) 优化 `Nacos` 服务注册器的代码，使其支持 `1.x` 和 `2.x` 版本。
- [#5494](https://github.com/hyperf/hyperf/pull/5494) [#5501](https://github.com/hyperf/hyperf/pull/5501) 优化 `hyperf/guzzle` 组件，当使用 `Swoole` 且不支持 `native-curl` 时，才会默认替换 `Handler`。

## 变更

- [#5492](https://github.com/hyperf/hyperf/pull/5492) 将 `Hyperf\DbConnection\Listener\CreatingListener` 重命名为 `Hyperf\DbConnection\Listener\InitUidOnCreatingListener`.

# v3.0.9 - 2023-03-05

## 新增

- [#5467](https://github.com/hyperf/hyperf/pull/5467) 为 `GRPC` 增加 `Google\Rpc\Status` 的支持。
- [#5472](https://github.com/hyperf/hyperf/pull/5472) 为模型增加 `ulid` 和 `uuid` 的支持。
- [#5476](https://github.com/hyperf/hyperf/pull/5476) 为 `Stringable` 增加 `ArrayAccess` 的支持。
- [#5478](https://github.com/hyperf/hyperf/pull/5478) 为 `Stringable` 和 `Str` 增加 `isMatch` 方法。

## 优化

- [#5469](https://github.com/hyperf/hyperf/pull/5469) 当数据库连接出现问题时，确保连接在归还到连接池前被重置。

# v3.0.8 - 2023-02-26

## 修复

- [#5433](https://github.com/hyperf/hyperf/pull/5433) [#5438](https://github.com/hyperf/hyperf/pull/5438) 修复 `Nacos` 临时实例，不需要发送心跳的问题。 
- [#5464](https://github.com/hyperf/hyperf/pull/5464) 修复 `Swagger` 服务无法在异步风格中，正常启动的问题。

## 新增

- [#5434](https://github.com/hyperf/hyperf/pull/5434) 为 `Swow` 增加 `UDP` 服务的支持。
- [#5444](https://github.com/hyperf/hyperf/pull/5444) 新增脚本 `GenSchemaCommand` 用来生成 `Swagger Schema`。
- [#5451](https://github.com/hyperf/hyperf/pull/5451) 为模型集合新增 `appends($attributes)` 方法。
- [#5453](https://github.com/hyperf/hyperf/pull/5453) 为测试组件增加 `put()` 和 `patch()` 方法。
- [#5454](https://github.com/hyperf/hyperf/pull/5454) 为 `GRPC` 组件新增方法 `Hyperf\Grpc\Parser::statusFromResponse`。
- [#5459](https://github.com/hyperf/hyperf/pull/5459) 为 `Str` 和 `Stringable` 新增方法 `uuid` 和 `ulid`。

## 优化

- [#5437](https://github.com/hyperf/hyperf/pull/5437) 为 `Str::length` 移除了没用的 `if` 判断。
- [#5439](https://github.com/hyperf/hyperf/pull/5439) 优化了 `Arr::shuffle` 的代码。

# v3.0.7 - 2023-02-18

## 新增

- [#5042](https://github.com/hyperf/hyperf/pull/5402) 为 `Swagger` 组件增加配置 `swagger.scan.paths` 可以用来重写默认的扫描目录。
- [#5403](https://github.com/hyperf/hyperf/pull/5403) 为 `Swow` 增加 `Swoole Server` 配置项的适配。
- [#5404](https://github.com/hyperf/hyperf/pull/5404) 为 `Swagger` 增加多端口服务的支持。
- [#5406](https://github.com/hyperf/hyperf/pull/5406) 为 `Hyperf\Database\Model\Builder` 增加 `mixin` 方法。
- [#5407](https://github.com/hyperf/hyperf/pull/5407) 为 `Swagger` 增加请求方法 `Delete` 和 `Options` 的支持。
- [#5409](https://github.com/hyperf/hyperf/pull/5409) 为数据库组件中 `Query\Builder` 和 `Paginator` 类增加了一部分方法。
- [#5414](https://github.com/hyperf/hyperf/pull/5414) 为 `Hyperf\Database\Model\Builder` 增加了 `clone` 方法。
- [#5418](https://github.com/hyperf/hyperf/pull/5418) 为配置中心增加了 `ConfigChanged` 事件。
- [#5429](https://github.com/hyperf/hyperf/pull/5429) 在连接 `Aliyun Nacos` 服务时，增加了配置项 `access_key` 和 `access_secret`。

## 修复

- [#5405](https://github.com/hyperf/hyperf/pull/5405) 修复了当系统支持 `IPv6` 时，`get local ip` 无法正常读取 ip 的问题。
- [#5417](https://github.com/hyperf/hyperf/pull/5417) 修复 `PgSQL` 无法正常使用数据库迁移功能的问题。
- [#5421](https://github.com/hyperf/hyperf/pull/5421) 修复数据库 `Json` 结构无法正常使用 `boolean` 类型的问题。
- [#5428](https://github.com/hyperf/hyperf/pull/5428) 修复 `Metric` 中间件遇到异常时，服务端参数统计有误的问题。
- [#5424](https://github.com/hyperf/hyperf/pull/5424) 修复数据库迁移组件，不支持 `PHP8.2` 的问题。

## 优化

- [#5411](https://github.com/hyperf/hyperf/pull/5411) 优化代码，异常 `WebSocketHandeShakeException` 应继承 `BadRequestHttpException`。
- [#5419](https://github.com/hyperf/hyperf/pull/5419) 优化 `RPN` 组件的实现逻辑，可以更好的进行自定义扩展。
- [#5422](https://github.com/hyperf/hyperf/pull/5422) 当安装 `Swagger` 组件后，默认启动 `Swagger` 的能力。

# v3.0.6 - 2023-02-12

## 修复

- [#5361](https://github.com/hyperf/hyperf/pull/5361) 修复 `Nacos` 注入临时实例失败的问题。
- [#5382](https://github.com/hyperf/hyperf/pull/5382) 修复 `SocketIO` 中使用 `mix-subscriber` 时，因为没有设置密码而报错的问题。
- [#5386](https://github.com/hyperf/hyperf/pull/5386) 修复 `SwoolePostgresqlClient` 会被执行到不存在的方法 `exec` 的问题。
- [#5394](https://github.com/hyperf/hyperf/pull/5394) 修复 `hyperf/config-apollo` 无法正常使用的问题。

## 新增

- [#5366](https://github.com/hyperf/hyperf/pull/5366) 为 `hyperf/database` 增加 `forceDeleting` 事件。
- [#5373](https://github.com/hyperf/hyperf/pull/5373) 为 `SwowServer` 增加 `settings` 配置。
- [#5376](https://github.com/hyperf/hyperf/pull/5376) 为 `hyperf/metric` 增加协程风格下服务状态收集的能力。
- [#5379](https://github.com/hyperf/hyperf/pull/5379) 当 `Nacos` 心跳失败时，增加日志记录。
- [#5389](https://github.com/hyperf/hyperf/pull/5389) 增加 `Swagger` 支持。
- [#5395](https://github.com/hyperf/hyperf/pull/5395) 为 `Swagger` 组件，增加验证器功能。
- [#5397](https://github.com/hyperf/hyperf/pull/5397) 支持所有已知的 `Swagger` 注解。

# v3.0.5 - 2023-02-06

## 新增

- [#5338](https://github.com/hyperf/hyperf/pull/5338) 为 `SoftDeletingScope` 新增了 `addRestoreOrCreate` 方法。
- [#5349](https://github.com/hyperf/hyperf/pull/5349) 新增监听器 `ResumeExitCoordinatorListener`。
- [#5355](https://github.com/hyperf/hyperf/pull/5355) 新增方法 `System::getCpuCoresNum()`。

## 修复

- [#5357](https://github.com/hyperf/hyperf/pull/5357) 修复在匿名函数中抛错时，`coordinator` 定时器无法正常停止的问题。

## 优化

- [#5342](https://github.com/hyperf/hyperf/pull/5342) 优化了 `Redis` 哨兵模式的地址读取方式。

# v3.0.4 - 2023-01-22

## 修复

- [#5332](https://github.com/hyperf/hyperf/pull/5332) 修复了 `PgSQLSwooleConnection::unprepared` 无法正常使用的问题。
- [#5333](https://github.com/hyperf/hyperf/pull/5333) 修复数据库组件在闲置时间过长，连接断开导致数据库读写报错的问题。

# v3.0.3 - 2023-01-16

## 修复

- [#5318](https://github.com/hyperf/hyperf/pull/5318) 修复在使用 PHP 8.1 版本时，限流器无法使用的问题。
- [#5324](https://github.com/hyperf/hyperf/pull/5324) 修复 MySQL 连接断开时，数据库组件无法使用的问题。
- [#5322](https://github.com/hyperf/hyperf/pull/5322) 修复 Kafka 消费者在没有设置 `memberId` 等参数时，无法使用的问题。
- [#5327](https://github.com/hyperf/hyperf/pull/5327) 修复 PgSQL 在创建连接失败时，导致类型错误的问题。

## 新增

- [#5314](https://github.com/hyperf/hyperf/pull/5314) 新增方法 `Hyperf\Coordinator\Timer::stats()`.
- [#5323](https://github.com/hyperf/hyperf/pull/5323) 新增方法 `Hyperf\Nacos\Provider\ConfigProvider::listener()`.

## 优化

- [#5308](https://github.com/hyperf/hyperf/pull/5308) [#5309](https://github.com/hyperf/hyperf/pull/5309) [#5310](https://github.com/hyperf/hyperf/pull/5310) [#5311](https://github.com/hyperf/hyperf/pull/5311) 为 `hyperf/metric` 增加协程服务的支持。
- [#5315](https://github.com/hyperf/hyperf/pull/5315) 增加 `hyperf/metric` 组件的监控指标。
- [#5326](https://github.com/hyperf/hyperf/pull/5326) 在循环中，收集服务当前的状态。

# v3.0.2 - 2023-01-09

# 修复

- [#5305](https://github.com/hyperf/hyperf/pull/5305) 使用 `PolarDB` 读写分离时，修复因没有修改数据的情况下，提交事务会导致此链接存在异常，但又被回收进连接池的问题。
- [#5307](https://github.com/hyperf/hyperf/pull/5307) 修复 `hyperf/metric` 组件中，`Timer::tick()` 的 `$timeout` 参数设置错误的问题。

## 优化

- [#5306](https://github.com/hyperf/hyperf/pull/5306) 当连接池回收连接失败时，记录日志。

# v3.0.1 - 2023-01-09

## 修复

- [#5289](https://github.com/hyperf/hyperf/pull/5289) 修复使用 `Swow` 引擎时，`Signal` 组件无法使用的问题。
- [#5303](https://github.com/hyperf/hyperf/pull/5303) 修复 `SocketIO` 的 `Redis NSQ 适配器`，当首次使用，`topics` 为 `null` 时，无法正常工作的问题。

## 优化

- [#5287](https://github.com/hyperf/hyperf/pull/5287) 当服务端响应数据时，如果出现异常，则记录对应日志。
- [#5292](https://github.com/hyperf/hyperf/pull/5292) 为组件 `hyperf/metric` 增加 `Swow` 引擎的支持。
- [#5301](https://github.com/hyperf/hyperf/pull/5301) 优化 `Hyperf\Rpc\PathGenerator\PathGenerator` 的代码实现。

# v3.0.0 - 2023-01-03

- [#4238](https://github.com/hyperf/hyperf/issues/4238) 更新所有组件 PHP 最低版本到 8.0
- [#5087](https://github.com/hyperf/hyperf/pull/5087) 支持 PHP 8.2

## BC breaks

- 框架移除了 `@Annotation` 的使用，全面使用 `PHP8` 的原生注解 `Attribute`。更新框架前，请确保已经全部替换到 PHP8 的原生注解。

我们提供了脚本，可以更加方便的将 `Doctrine Annotations` 替换为 `PHP8 Attributes`。

!> Note: 以下脚本只能在框架 2.2 版本下执行

```shell
composer require hyperf/code-generator
php bin/hyperf.php code:generate -D app
```

- 模型升级脚本

> 因为模型基类，增加了类型限制，所以你需要使用以下脚本，将所有模型更新到新的写法。

```shell
composer require hyperf/code-generator
php vendor/bin/regenerate-models.php $PWD/app/Model
```

- 框架增加了很多类型限制，所以当你从 `2.2` 升级到 `3.0`版本时，你需要调用静态检测脚本，检查并确保其可以正常工作。

```shell
composer analysis
```

- 框架基于 `gRPC` 标准修改了 `gRPC` 服务的 HTTP 返回码。

# v2.2.33 - 2022-05-30

## 修复

- [#4776](https://github.com/hyperf/hyperf/pull/4776) 修复 `GraphQL` 事件收集失败的问题。
- [#4790](https://github.com/hyperf/hyperf/pull/4790) 修复 `RPN` 组件中方法 `toRPNExpression` 在某些场景无法正常工作的问题。

## Added

- [#4763](https://github.com/hyperf/hyperf/pull/4763) 新增验证规则 `array:key1,key2`，确保数组中除 `key1` `key2` 以外无其他 `key` 键。
- [#4781](https://github.com/hyperf/hyperf/pull/4781) 新增配置 `close-pull-request.yml`，用来自动关闭只读的仓库。

# v2.2.32 - 2022-05-16

## 修复

- [#4745](https://github.com/hyperf/hyperf/pull/4745) 当使用 `kafka` 组件的 `Producer::close` 方法时，修复可能抛出空指针异常的问题。
- [#4754](https://github.com/hyperf/hyperf/pull/4754) 通过配置 `monolog>=2.6.0` 解决新版本的 `monolog` 无法正常工作的问题。

## 优化

- [#4738](https://github.com/hyperf/hyperf/pull/4738) 当使用 `kafka` 组件时，如果没有设置 `GroupID` 则自动配置一个。

# v2.2.31 - 2022-04-18

## 修复

- [#4677](https://github.com/hyperf/hyperf/pull/4677) 修复使用 `kafka` 发布者后，会导致进程无法正常退出的问题。
- [#4686](https://github.com/hyperf/hyperf/pull/4687) 修复使用 `WebSocket` 服务时，因为解析 `Request` 失败会导致进程崩溃的问题。

## 新增

- [#4576](https://github.com/hyperf/hyperf/pull/4576) 为 `RPC` 客户端的节点，增加路由前缀 `path_prefix`。
- [#4683](https://github.com/hyperf/hyperf/pull/4683) 新增容器方法 `unbind()` 用来从容器中解绑对象。

# v2.2.30 - 2022-04-04

## 修复

- [#4648](https://github.com/hyperf/hyperf/pull/4648) 当使用 `retry` 组件中的熔断器时，修复在 `open` 状态下，无法自动调用 `fallback` 方法的问题。
- [#4657](https://github.com/hyperf/hyperf/pull/4657) 修复使用 `session` 中的文件适配器时，相同的 `Session ID` 在被重写后，最后修改时间仍是上次修改时间的问题。

## 新增

- [#4646](https://github.com/hyperf/hyperf/pull/4646) 为 `Redis` 哨兵模式增加设置密码的功能。

# v2.2.29 - 2022-03-28

## 修复

- [#4620](https://github.com/hyperf/hyperf/pull/4620) 修复 `Hyperf\Memory\LockManager::initialize()` 方法中，`$filename` 默认值错误的问题。

# v2.2.28 - 2022-03-14

## 修复

- [#4588](https://github.com/hyperf/hyperf/pull/4588) 修复 `database` 组件不支持 `bit` 类型的问题。
- [#4589](https://github.com/hyperf/hyperf/pull/4589) 修复使用 `Nacos` 时，无法正确的注册临时实例的问题。

## 新增

- [#4580](https://github.com/hyperf/hyperf/pull/4580) 新增方法 `Hyperf\Utils\Coroutine\Concurrent::getChannel()`。

## 优化

- [#4602](https://github.com/hyperf/hyperf/pull/4602) 将方法 `Hyperf\ModelCache\Manager::formatModels()` 更改为公共方法。

# v2.2.27 - 2022-03-07

## 优化

- [#4572](https://github.com/hyperf/hyperf/pull/4572) 当负载均衡器 `hyperf/load-balancer` 选择节点失败时，使用 `Hyperf\LoadBalancer\Exception\RuntimeException` 代替 `\RuntimeException`。

# v2.2.26 - 2022-02-21

## 修复

- [#4536](https://github.com/hyperf/hyperf/pull/4536) 修复使用 `JsonRPC` 时，会设置多次 `content-type` 的问题。

## 新增

- [#4527](https://github.com/hyperf/hyperf/pull/4527) 为 `Hyperf\Database\Schema\Blueprint` 增加了一些比较有用的方法。

## 优化

- [#4514](https://github.com/hyperf/hyperf/pull/4514) 通过使用小写 `key` 获取 `HTTP` 的 `Header` 信息，提升一部分性能。
- [#4521](https://github.com/hyperf/hyperf/pull/4521) 在使用 Redis 的哨兵模式时，如果第一个哨兵节点连接失败，则尝试连接其余哨兵节点。
- [#4529](https://github.com/hyperf/hyperf/pull/4529) 将组件 `hyperf/context` 从组件 `hyperf/utils` 中分离出来。

# v2.2.25 - 2022-01-30

## 修复

- [#4484](https://github.com/hyperf/hyperf/pull/4484) 修复使用 `Nacos v2.0.4` 版本时，服务是否注册过，判断有误的问题。

## 新增

- [#4477](https://github.com/hyperf/hyperf/pull/4477) 为 `Hyperf\HttpServer\Request` 新增 `Macroable` 支持。

## 优化

- [#4254](https://github.com/hyperf/hyperf/pull/4254) 当使用 `Hyperf\Di\ScanHandlerPcntlScanHandler` 时，增加 `grpc.enable_fork_support` 检测。

# v2.2.24 - 2022-01-24

## 修复

- [#4474](https://github.com/hyperf/hyperf/pull/4474) 修复使用多路复用 RPC 时，导致测试脚本无法正常停止的问题。

## 优化

- [#4451](https://github.com/hyperf/hyperf/pull/4451) 优化了 `Hyperf\Watcher\Driver\FindNewerDriver` 的代码。

# v2.2.23 - 2022-01-17

## 修复

- [#4426](https://github.com/hyperf/hyperf/pull/4426) 修复 `view-engine` 模板引擎，在并发请求下导致模板缓存生成错误的问题。

## 新增

- [#4449](https://github.com/hyperf/hyperf/pull/4449) 为 `Hyperf\Utils\Collection` 增加多条件排序的能力。
- [#4455](https://github.com/hyperf/hyperf/pull/4455) 新增命令 `gen:view-engine-cache` 可以预生成模板缓存，避免并发带来的一系列问题。
- [#4453](https://github.com/hyperf/hyperf/pull/4453) 新增 `Hyperf\Tracer\Aspect\ElasticserachAspect`，用来记录 `elasticsearch` 客户端的调用记录。
- [#4458](https://github.com/hyperf/hyperf/pull/4458) 新增 `Hyperf\Di\ScanHandler\ProcScanHandler`，用来支持 `Windows` + `Swow` 环境下启动服务。

# v2.2.22 - 2022-01-04

## 修复

- [#4399](https://github.com/hyperf/hyperf/pull/4399) 修复使用 `RedisCluster` 时，无法使用 `scan` 方法的问题。

## 新增

- [#4409](https://github.com/hyperf/hyperf/pull/4409) 为 `session` 增加数据库支持。
- [#4411](https://github.com/hyperf/hyperf/pull/4411) 为 `tracer` 组件，新增 `Hyperf\Tracer\Aspect\DbAspect`，用于记录 `hyperf/db` 组件产生的 `SQL` 日志。
- [#4420](https://github.com/hyperf/hyperf/pull/4420) 为 `Hyperf\Amqp\IO\SwooleIO` 增加 `SSL` 支持。

## 优化

- [#4406](https://github.com/hyperf/hyperf/pull/4406) 删除 `Swoole PSR-0` 风格代码，更加友好的支持 `Swoole 5.0` 版本。
- [#4429](https://github.com/hyperf/hyperf/pull/4429) 为 `Debug::getRefCount()` 方法增加类型检测，只能用于输出对象的 `RefCount`。

# v2.2.21 - 2021-12-20

## 修复

- [#4347](https://github.com/hyperf/hyperf/pull/4347) 修复使用 `AMQP` 组件时，如果连接缓冲区溢出，会导致连接被绑定到多个协程从而报错的问题。
- [#4373](https://github.com/hyperf/hyperf/pull/4373) 修复使用 `Snowflake` 组件时，由于 `getWorkerId()` 中存在 `IO` 操作进而导致协程切换，最终导致元数据生成重复的问题。

## 新增

- [#4344](https://github.com/hyperf/hyperf/pull/4344) 新增事件 `Hyperf\Crontab\Event\FailToExecute`，此事件会在 `Crontab` 任务执行失败时触发。
- [#4348](https://github.com/hyperf/hyperf/pull/4348) 支持使用 `gen:*` 命令创建文件时，自动吊起对应的 `IDE`，并打开当前文件。

## 优化

- [#4350](https://github.com/hyperf/hyperf/pull/4350) 优化了未开启 `swoole.use_shortname` 时的错误信息。
- [#4360](https://github.com/hyperf/hyperf/pull/4360) 将 `Hyperf\Amqp\IO\SwooleIO` 进行重构，使用更加稳定和高效的 `Swoole\Coroutine\Socket` 而非 `Swoole\Coroutine\Client`。

# v2.2.20 - 2021-12-13

## 修复

- [#4338](https://github.com/hyperf/hyperf/pull/4338) 修复使用单测客户端时，路径中带有参数会导致无法正确匹配路由的问题。
- [#4346](https://github.com/hyperf/hyperf/pull/4346) 修复使用组件 `php-amqplib/php-amqplib:3.1.1` 时，启动报错的问题。

## 新增

- [#4330](https://github.com/hyperf/hyperf/pull/4330) 为 `phar` 组件支持打包 `vendor/bin` 目录。
- [#4331](https://github.com/hyperf/hyperf/pull/4331) 新增方法 `Hyperf\Testing\Debug::getRefCount($object)`。

# v2.2.19 - 2021-12-06

## 修复

- [#4308](https://github.com/hyperf/hyperf/pull/4308) 修复执行 `server:watch` 时，因为使用相对路径导致 `collector-reload` 文件找不到的问题。

## 优化

- [#4317](https://github.com/hyperf/hyperf/pull/4317) 为 `Hyperf\Utils\Collection` 和 `Hyperf\Database\Model\Collection` 增强类型提示功能。

# v2.2.18 - 2021-11-29

## 修复

- [#4283](https://github.com/hyperf/hyperf/pull/4283) 修复当 `GRPC` 结果为 `null` 时，`Hyperf\Grpc\Parser::deserializeMessage()` 报错的问题。

## 新增

- [#4284](https://github.com/hyperf/hyperf/pull/4284) 新增方法 `Hyperf\Utils\Network::ip()` 获取本地 `IP`。
- [#4290](https://github.com/hyperf/hyperf/pull/4290) 为 `HTTP` 服务增加 `chunk` 功能。
- [#4291](https://github.com/hyperf/hyperf/pull/4291) 为 `value()` 方法增加动态参数功能。
- [#4293](https://github.com/hyperf/hyperf/pull/4293) 为 `server:watch` 命令增加相对路径支持。
- [#4295](https://github.com/hyperf/hyperf/pull/4295) 为 `Hyperf\Database\Schema\Blueprint::bigIncrements()` 增加别名 `id()`。

# v2.2.17 - 2021-11-22

## 修复

- [#4243](https://github.com/hyperf/hyperf/pull/4243) 修复使用 `parallel` 时，结果集的顺序与入参不一致的问题。

## 新增

- [#4109](https://github.com/hyperf/hyperf/pull/4109) 为 `hyperf/tracer` 增加 `PHP8` 的支持。
- [#4260](https://github.com/hyperf/hyperf/pull/4260) 为 `hyperf/database` 增加指定索引的功能。

# v2.2.16 - 2021-11-15

## 新增

- [#4252](https://github.com/hyperf/hyperf/pull/4252) 为 `Hyperf\RpcClient\AbstractServiceClient` 新增 `getServiceName()` 方法。

## 优化

- [#4253](https://github.com/hyperf/hyperf/pull/4253) 在扫描阶段时，如果类库找不到，则跳过且报出警告。

# v2.2.15 - 2021-11-08

## 修复

- [#4200](https://github.com/hyperf/hyperf/pull/4200) 修复当 `runtime/caches` 不是目录时，使用文件缓存失败的问题。

## 新增

- [#4157](https://github.com/hyperf/hyperf/pull/4157) 为 `Hyperf\Utils\Arr` 增加 `Macroable` 支持。

# v2.2.14 - 2021-11-01

## 新增

- [#4181](https://github.com/hyperf/hyperf/pull/4181) [#4192](https://github.com/hyperf/hyperf/pull/4192) 为框架增加 `psr/log` 组件版本 `v1.0`、`v2.0`、`v3.0` 的支持。

## 修复

- [#4171](https://github.com/hyperf/hyperf/pull/4171) 修复使用 `consul` 组件时，开启 `ACL` 验证后，健康检测失败的问题。
- [#4188](https://github.com/hyperf/hyperf/pull/4188) 修复使用 `composer 1.x` 版本时，打包 `phar` 失败的问题。

# v2.2.13 - 2021-10-25

## 新增

- [#4159](https://github.com/hyperf/hyperf/pull/4159) 为 `Macroable::mixin` 方法增加参数 `$replace`，当其设置为 `false` 时，会优先判断是否已经存在。

## 修复

- [#4158](https://github.com/hyperf/hyperf/pull/4158) 修复因为使用了 `Union` 类型，导致生成代理类失败的问题。

## 优化

- [#4159](https://github.com/hyperf/hyperf/pull/4159) [#4166](https://github.com/hyperf/hyperf/pull/4166) 将组件 `hyperf/macroable` 从 `hyperf/utils` 中分离出来。

# v2.2.12 - 2021-10-18

## 新增

- [#4129](https://github.com/hyperf/hyperf/pull/4129) 新增方法 `Str::stripTags()` 和 `Stringable::stripTags()`。

## 修复

- [#4130](https://github.com/hyperf/hyperf/pull/4130) 修复生成模型时，因为使用了选项 `--with-ide` 和 `scope` 方法导致报错的问题。
- [#4141](https://github.com/hyperf/hyperf/pull/4141) 修复验证器工厂不支持其他验证器的问题。

# v2.2.11 - 2021-10-11

## 修复

- [#4101](https://github.com/hyperf/hyperf/pull/4101) 修复 Nacos 使用的密码携带特殊字符时，密码会被 `urlencode` 导致密码错误的问题。

# 优化

- [#4114](https://github.com/hyperf/hyperf/pull/4114) 优化 WebSocket 客户端初始化失败时的错误信息。
- [#4119](https://github.com/hyperf/hyperf/pull/4119) 优化单测客户端在上传文件时，因为默认的上传路径已经存在，导致报错的问题（只发生在最新的 Swoole 版本中）。

# v2.2.10 - 2021-09-26

## 修复

- [#4088](https://github.com/hyperf/hyperf/pull/4088) 修复使用定时器规则时，会将空字符串转化为 `0` 的问题。
- [#4096](https://github.com/hyperf/hyperf/pull/4096) 修复当带有类型的动态参数生成代理类时，会出现类型错误的问题。

# v2.2.9 - 2021-09-22

## 修复

- [#4061](https://github.com/hyperf/hyperf/pull/4061) 修复 `hyperf/metric` 组件与最新版本的 `prometheus_client_php` 存在冲突的问题。
- [#4068](https://github.com/hyperf/hyperf/pull/4068) 修复命令行抛出错误时，退出码与实际不符的问题。
- [#4076](https://github.com/hyperf/hyperf/pull/4076) 修复 `HTTP` 服务因返回数据不是标准 `HTTP` 协议时，导致服务宕机的问题。

## 新增

- [#4014](https://github.com/hyperf/hyperf/pull/4014) [#4080](https://github.com/hyperf/hyperf/pull/4080) 为 `kafka` 组件增加 `sasl` 和 `ssl` 的支持。
- [#4045](https://github.com/hyperf/hyperf/pull/4045) [#4082](https://github.com/hyperf/hyperf/pull/4082) 为 `tracer` 组件新增配置 `opentracing.enable.exception`，用来判断是否收集异常信息。
- [#4086](https://github.com/hyperf/hyperf/pull/4086) 支持收集接口 `Interface` 的注解信息。

# 优化

- [#4084](https://github.com/hyperf/hyperf/pull/4084) 优化了注解找不到时的错误信息。

# v2.2.8 - 2021-09-14

## 修复

- [#4028](https://github.com/hyperf/hyperf/pull/4028) 修复 `grafana` 面板中，请求数结果计算错误的问题。
- [#4030](https://github.com/hyperf/hyperf/pull/4030) 修复异步队列会因为解压缩模型失败，导致进程中断随后重启的问题。
- [#4042](https://github.com/hyperf/hyperf/pull/4042) 修复因 `SocketIO` 服务关闭时清理过期的 `fd`，进而导致协程死锁的问题。

## 新增

- [#4013](https://github.com/hyperf/hyperf/pull/4013) 为 `Cookies` 增加 `sameSite=None` 的支持。
- [#4017](https://github.com/hyperf/hyperf/pull/4017) 为 `Hyperf\Utils\Collection` 增加 `Macroable`。
- [#4021](https://github.com/hyperf/hyperf/pull/4021) 为 `retry()` 方法中 `$callback` 匿名函数增加 `$attempts` 变量。
- [#4040](https://github.com/hyperf/hyperf/pull/4040) 为 `AMQP` 组件新增方法 `ConsumerDelayedMessageTrait::getDeadLetterExchange()`，可以用来重写 `x-dead-letter-exchange` 参数。

## 移除

- [#4017](https://github.com/hyperf/hyperf/pull/4017) 从 `Hyperf\Database\Model\Collection` 中移除 `Macroable`，因为它的基类 `Hyperf\Utils\Collection` 已引入了对应的 `Macroable`。

# v2.2.7 - 2021-09-06

# 修复

- [#3997](https://github.com/hyperf/hyperf/pull/3997) 修复 `Nats` 消费者会在连接超时后崩溃的问题。
- [#3998](https://github.com/hyperf/hyperf/pull/3998) 修复 `Apollo` 不支持 `https` 协议的问题。

## 优化

- [#4009](https://github.com/hyperf/hyperf/pull/4009) 优化方法 `MethodDefinitionCollector::getOrParse()`，避免在 PHP8 环境下，触发即将废弃的错误。

## 新增

- [#4002](https://github.com/hyperf/hyperf/pull/4002) [#4012](https://github.com/hyperf/hyperf/pull/4012) 为验证器增加场景功能，允许不同场景下，使用不同的验证规则。
- [#4011](https://github.com/hyperf/hyperf/pull/4011) 为工具类 `Hyperf\Utils\Str` 增加了一些新的便捷方法。

# v2.2.6 - 2021-08-30

## 修复

- [#3969](https://github.com/hyperf/hyperf/pull/3969) 修复 PHP8 环境下使用 `Hyperf\Validation\Rules\Unique::__toString()` 导致类型错误的问题。
- [#3979](https://github.com/hyperf/hyperf/pull/3979) 修复熔断器组件，`timeout` 变量无法使用的问题。 
- [#3986](https://github.com/hyperf/hyperf/pull/3986) 修复文件系统组件，开启 `SWOOLE_HOOK_NATIVE_CURL` 后导致 OSS hook 失败的问题。

## 新增

- [#3987](https://github.com/hyperf/hyperf/pull/3987) AMQP 组件支持延时队列。
- [#3989](https://github.com/hyperf/hyperf/pull/3989) [#3992](https://github.com/hyperf/hyperf/pull/3992) 为热更新组件新增了配置 `command`，可以用来定义自己的启动脚本，支持 [nano](https://github.com/hyperf/nano) 组件。

# v2.2.5 - 2021-08-23

## 修复

- [#3959](https://github.com/hyperf/hyperf/pull/3959) 修复验证器规则 `date` 在入参为 `string` 时，无法正常使用的问题。
- [#3960](https://github.com/hyperf/hyperf/pull/3960) 修复协程风格服务下，`Crontab` 无法平滑关闭的问题。

## 新增

- [code-generator](https://github.com/hyperf/code-generator) 新增组件 `code-generator`，可以用来将 `Doctrine` 注解转化为 `PHP8` 的原生注解。

## 优化

- [#3957](https://github.com/hyperf/hyperf/pull/3957) 使用命令 `gen:model` 生成 `getAttribute` 注释时，支持基于 `@return` 注释返回对应的类型。

# v2.2.4 - 2021-08-16

## 修复

- [#3925](https://github.com/hyperf/hyperf/pull/3925) 修复 `Nacos` 开启 `light beat` 功能后，心跳失败的问题。
- [#3926](https://github.com/hyperf/hyperf/pull/3926) 修复配置项 `config_center.drivers.nacos.client` 无法正常工作的问题。

## 新增

- [#3924](https://github.com/hyperf/hyperf/pull/3924) 为 `Consul` 服务注册中心增加配置项 `services.drivers.consul.check`。
- [#3932](https://github.com/hyperf/hyperf/pull/3932) 为 `AMQP` 消费者增加重新入队列的配置，允许用户返回 `NACK` 后，消息重入队列。
- [#3941](https://github.com/hyperf/hyperf/pull/3941) 允许多路复用的 `RPC` 组件使用注册中心的能力。
- [#3947](https://github.com/hyperf/hyperf/pull/3947) 新增方法 `Str::mask`，允许用户对一段文本某段内容打马赛克。

## 优化

- [#3944](https://github.com/hyperf/hyperf/pull/3944) 封装了读取 `Aspect` 元数据的方法。

# v2.2.3 - 2021-08-09

## 修复

- [#3897](https://github.com/hyperf/hyperf/pull/3897) 修复因为 `lightBeatEnabled` 导致心跳失败，进而导致 `Nacos` 服务注册多次的问题。
- [#3905](https://github.com/hyperf/hyperf/pull/3905) 修复 `AMQP` 连接在关闭时导致空指针的问题。
- [#3906](https://github.com/hyperf/hyperf/pull/3906) 修复 `AMQP` 连接关闭时，因已经销毁所有等待通道而导致失败的问题。
- [#3908](https://github.com/hyperf/hyperf/pull/3908) 修复使用了以 `CoordinatorManager` 为基础的循环逻辑时，自定义进程无法正常重启的问题。

# v2.2.2 - 2021-08-03

## 修复

- [#3872](https://github.com/hyperf/hyperf/pull/3872) [#3873](https://github.com/hyperf/hyperf/pull/3873) 修复使用 `Nacos` 服务时，因为没有使用默认的组名，导致心跳失败的问题。
- [#3877](https://github.com/hyperf/hyperf/pull/3877) 修复 `Nacos` 服务，心跳会被注册多次的问题。
- [#3879](https://github.com/hyperf/hyperf/pull/3879) 修复热更新因为代理类被覆盖，导致无法正常使用的问题。

## 优化

- [#3877](https://github.com/hyperf/hyperf/pull/3877) 为 `Nacos` 服务，增加 `lightBeatEnabled` 支持。

# v2.2.1 - 2021-07-27

## 修复

- [#3750](https://github.com/hyperf/hyperf/pull/3750) 修复使用 `SocketIO` 时，由于触发了一个不存在的命名空间，而导致致命错误的问题。
- [#3828](https://github.com/hyperf/hyperf/pull/3828) 修复在 `PHP 8.0` 版本中，无法对 `Hyperf\Redis\Redis` 使用懒加载注入的问题。
- [#3845](https://github.com/hyperf/hyperf/pull/3845) 修复 `watcher` 组件无法在 `v2.2` 版本中正常使用的问题。
- [#3848](https://github.com/hyperf/hyperf/pull/3848) 修复 `Nacos` 组件无法像 `v2.1` 版本注册自身到 `Nacos` 服务中的问题。
- [#3866](https://github.com/hyperf/hyperf/pull/3866) 修复 `Nacos` 实例无法正常注册元数据的问题。

## 优化

- [#3763](https://github.com/hyperf/hyperf/pull/3763) 使 `JsonResource::wrap()` 和 `JsonResource::withoutWrapping()` 支持链式调用。
- [#3843](https://github.com/hyperf/hyperf/pull/3843) 在 `Nacos` 注册服务时，根据 `HTTP` 响应的返回码和数据协同判断，以确保是否已注册过。
- [#3854](https://github.com/hyperf/hyperf/pull/3854) 为文件下载方法支持 `RFC 5987`，它允许使用 `UTF-8` 格式和 `URL` 格式化。

# v2.1.23 - 2021-07-12

## 优化

- [#3787](https://github.com/hyperf/hyperf/pull/3787) 优化 `JSON RPC` 服务，优先初始化 `PSR Response`，用于避免 `PSR Request` 初始化失败后，无法从上下文中获取 `Response` 的问题。

# v2.1.22 - 2021-06-28

## 安全性更新

- [#3723](https://github.com/hyperf/hyperf/pull/3723) 修复验证器规则 `active_url` 无法正确检查 `dns` 记录，从而导致绕过验证的问题。
- [#3724](https://github.com/hyperf/hyperf/pull/3724) 修复可以利用 `RequiredIf` 规则生成用于反序列化漏洞的小工具链的问题。

## 修复

- [#3721](https://github.com/hyperf/hyperf/pull/3721) 修复了验证器规则 `in` 和 `not in` 判断有误的问题，例如规则为 `in:00` 时，`0`不应该被允许通过。

# v2.1.21 - 2021-06-21

## 修复

- [#3684](https://github.com/hyperf/hyperf/pull/3684) 修复使用熔断器时，成功次数和失败次数的界限判断有误的问题。

# v2.1.20 - 2021-06-07

## 修复

- [#3667](https://github.com/hyperf/hyperf/pull/3667) 修复形如 `10-12/1,14-15/1` 的定时任务规则无法正常使用的问题。
- [#3669](https://github.com/hyperf/hyperf/pull/3669) 修复了没有反斜线形如 `10-12` 的定时任务规则无法正常使用的问题。
- [#3674](https://github.com/hyperf/hyperf/pull/3674) 修复 `@Task` 注解中，参数 `$workerId` 无法正常使用的问题。

## 优化

- [#3663](https://github.com/hyperf/hyperf/pull/3663) 优化 `AbstractServiceClient::getNodesFromConsul()` 方法，排除了可能找不到端口的隐患。
- [#3668](https://github.com/hyperf/hyperf/pull/3668) 优化 `Guzzle` 组件中 `CoroutineHandler` 代理相关的代码，增强其兼容性。

# v2.1.19 - 2021-05-31

## 修复

- [#3618](https://github.com/hyperf/hyperf/pull/3618) 修复使用了相同路径但不同实现逻辑的路由会在命令 `describe:routes` 中，被合并成一条的问题。
- [#3625](https://github.com/hyperf/hyperf/pull/3625) 修复 `Hyperf\Di\Annotation\Scanner` 中无法正常使用 `class_map` 功能的问题。

## 新增

- [#3626](https://github.com/hyperf/hyperf/pull/3626) 为 `RPC` 组件增加了新的路径打包器 `Hyperf\Rpc\PathGenerator\DotPathGenerator`。

## 新组件孵化

- [nacos-sdk](https://github.com/hyperf/nacos-sdk-incubator) 基于 Nacos Open API 实现的 SDK。

# v2.1.18 - 2021-05-24

## 修复

- [#3598](https://github.com/hyperf/hyperf/pull/3598) 修复事务回滚时，模型累加、累减操作会导致模型缓存产生脏数据的问题。
- [#3607](https://github.com/hyperf/hyperf/pull/3607) 修复在使用协程风格的 `WebSocket` 服务时，`onOpen` 事件无法在事件结束后销毁协程的问题。
- [#3610](https://github.com/hyperf/hyperf/pull/3610) 修复数据库存在前缀时，`fromSub()` 和 `joinSub()` 无法正常使用的问题。

# v2.1.17 - 2021-05-17

## 修复

- [#3856](https://github.com/hyperf/hyperf/pull/3586) 修复 `Swow` 服务处理 `keepalive` 的请求时，协程无法在每个请求后结束的问题。

## 新增

- [#3329](https://github.com/hyperf/hyperf/pull/3329) `@Crontab` 注解的 `enable` 参数增加支持设置数组, 你可以通过它动态的控制定时任务是否启动。

# v2.1.16 - 2021-04-26

## 修复

- [#3510](https://github.com/hyperf/hyperf/pull/3510) 修复 `consul` 无法将节点强制离线的问题。
- [#3513](https://github.com/hyperf/hyperf/pull/3513) 修复 `Nats` 因为 `Socket` 超时时间小于最大闲置时间，导致连接意外关闭的问题。
- [#3520](https://github.com/hyperf/hyperf/pull/3520) 修复 `@Inject` 无法作用于嵌套 `Trait` 的问题。

## 新增

- [#3514](https://github.com/hyperf/hyperf/pull/3514) 新增方法 `Hyperf\HttpServer\Request::clearStoredParsedData()`。

## 优化

- [#3517](https://github.com/hyperf/hyperf/pull/3517) 优化 `Hyperf\Di\Aop\PropertyHandlerTrait`。

# v2.1.15 - 2021-04-19

## 新增

- [#3484](https://github.com/hyperf/hyperf/pull/3484) 新增 `ORM` 方法 `withMax()` `withMin()` `withSum()` 和 `withAvg()`.

# v2.1.14 - 2021-04-12

## 修复

- [#3465](https://github.com/hyperf/hyperf/pull/3465) 修复协程风格下，`WebSocket` 服务不支持配置多个端口的问题。
- [#3467](https://github.com/hyperf/hyperf/pull/3467) 修复协程风格下，`WebSocket` 服务无法正常释放连接池的问题。

## 新增

- [#3472](https://github.com/hyperf/hyperf/pull/3472) 新增方法 `Sender::getResponse()`，可以在协程风格的 `WebSocket` 服务里，获得与 `fd` 一一对应的 `Response` 对象。

# v2.1.13 - 2021-04-06

## 修复

- [#3432](https://github.com/hyperf/hyperf/pull/3432) 修复 `SocketIO` 服务，定时清理失效 `fd` 的功能无法作用到其他 `worker` 进程的问题。
- [#3434](https://github.com/hyperf/hyperf/pull/3434) 修复 `RPC` 结果不支持允许为 `null` 的类型，例如 `?array` 会被强制转化为数组。
- [#3447](https://github.com/hyperf/hyperf/pull/3447) 修复模型缓存中，因为存在表前缀，导致模型默认值无法生效的问题。
- [#3450](https://github.com/hyperf/hyperf/pull/3450) 修复注解 `@Crontab` 无法作用于 `方法` 的问题，支持一个类中，配置多个 `@Crontab`。

## 优化

- [#3453](https://github.com/hyperf/hyperf/pull/3453) 优化了类 `Hyperf\Utils\Channel\Caller` 回收实例时的机制，防止因为实例为 `null` 时，导致无法正确回收的问题。
- [#3455](https://github.com/hyperf/hyperf/pull/3455) 优化脚本 `phar:build`，支持使用软连接方式加载的组件包。

# v2.1.12 - 2021-03-29

## 修复

- [#3423](https://github.com/hyperf/hyperf/pull/3423) 修复 `worker_num` 设置为非 `Integer` 时，导致定时任务中 `Task` 策略无法正常使用的问题。
- [#3426](https://github.com/hyperf/hyperf/pull/3426) 修复为可选参数路由设置中间件时，导致中间件被意外执行两次的问题。

## 优化

- [#3422](https://github.com/hyperf/hyperf/pull/3422) 优化了 `co-phpunit` 的代码。

# v2.1.11 - 2021-03-22

## 新增

- [#3376](https://github.com/hyperf/hyperf/pull/3376) 为注解 `Hyperf\DbConnection\Annotation\Transactional` 增加参数 `$connection` 和 `$attempts`，用户可以按需设置事务连接和重试次数。
- [#3403](https://github.com/hyperf/hyperf/pull/3403) 新增方法 `Hyperf\Testing\Client::sendRequest()`，用户可以使用自己构造的 `ServerRequest`，比如设置 `Cookies`。

## 修复

- [#3380](https://github.com/hyperf/hyperf/pull/3380) 修复超全局变量，在协程上下文里没有 `Request` 对象时，无法正常工作的问题。
- [#3394](https://github.com/hyperf/hyperf/pull/3394) 修复使用 `@Inject` 注入的对象，会被 `trait` 中注入的对象覆盖的问题。
- [#3395](https://github.com/hyperf/hyperf/pull/3395) 修复当继承使用 `@Inject` 注入私有变量的父类时，而导致子类实例化报错的问题。
- [#3398](https://github.com/hyperf/hyperf/pull/3398) 修复单元测试中使用 `UploadedFile::isValid()` 时，无法正确判断结果的问题。

# v2.1.10 - 2021-03-15

## 修复

- [#3348](https://github.com/hyperf/hyperf/pull/3348) 修复当使用 `Arr::forget` 方法在 `key` 为 `integer` 且不存在时，执行报错的问题。
- [#3351](https://github.com/hyperf/hyperf/pull/3351) 修复 `hyperf/validation` 组件中，`FormRequest` 无法从协程上下文中获取到修改后的 `ServerRequest`，从而导致验证器验证失败的问题。
- [#3356](https://github.com/hyperf/hyperf/pull/3356) 修复 `hyperf/testing` 组件中，客户端 `Hyperf\Testing\Client` 无法模拟构造正常的 `UriInterface` 的问题。
- [#3363](https://github.com/hyperf/hyperf/pull/3363) 修复在入口文件 `bin/hyperf.php` 中自定义的常量，无法在命令 `server:watch` 中使用的问题。
- [#3365](https://github.com/hyperf/hyperf/pull/3365) 修复当使用协程风格服务时，如果用户没有配置 `pid_file`，仍然会意外生成 `runtime/hyperf.pid` 文件的问题。

## 优化

- [#3364](https://github.com/hyperf/hyperf/pull/3364) 优化命令 `phar:build`，你可以在不使用 `php` 脚本的情况下执行 `phar` 文件，就像使用命令 `./composer.phar` 而非 `php composer.phar`。
- [#3367](https://github.com/hyperf/hyperf/pull/3367) 优化使用 `gen:model` 生成模型字段的类型注释时，尽量读取自定义转换器转换后的对象类型。

# v2.1.9 - 2021-03-08

## 修复

- [#3326](https://github.com/hyperf/hyperf/pull/3326) 修复使用 `JsonEofPacker` 无法正确解包自定义 `eof` 数据的问题。
- [#3330](https://github.com/hyperf/hyperf/pull/3330) 修复因其他协程修改静态变量 `$constraints`，导致模型关系查询错误的问题。

## 新增

- [#3325](https://github.com/hyperf/hyperf/pull/3325) 为 `Crontab` 注解增加 `enable` 参数，用于控制当前任务是否注册到定时任务中。

## 优化

- [#3338](https://github.com/hyperf/hyperf/pull/3338) 优化了 `testing` 组件，使模拟请求的方法运行在独立的协程当中，避免协程变量污染。

# v2.1.8 - 2021-03-01

## 修复

- [#3301](https://github.com/hyperf/hyperf/pull/3301) 修复 `hyperf/cache` 组件，当没有在注解中设置超时时间时，会将超时时间强制转化为 0，导致缓存不失效的问题。

## 新增

- [#3310](https://github.com/hyperf/hyperf/pull/3310) 新增方法 `Blueprint::comment()`，可以允许在使用 `Migration` 的时候，设置表注释。 
- [#3311](https://github.com/hyperf/hyperf/pull/3311) 新增方法 `RouteCollector::getRouteParser`，可以方便的从 `RouteCollector` 中获取到 `RouteParser` 对象。
- [#3316](https://github.com/hyperf/hyperf/pull/3316) 允许用户在 `hyperf/db` 组件中，注册自定义数据库适配器。

## 优化

- [#3308](https://github.com/hyperf/hyperf/pull/3308) 优化 `WebSocket` 服务，当找不到对应路由时，直接返回响应。
- [#3319](https://github.com/hyperf/hyperf/pull/3319) 优化从连接池获取连接的代码逻辑，避免因重写低频组件导致报错，使得连接被意外丢弃。

## 新组件孵化

- [rpc-multiplex](https://github.com/hyperf/rpc-multiplex-incubator) 基于 Channel 实现的多路复用 RPC 组件。
- [db-pgsql](https://github.com/hyperf/db-pgsql-incubator) 适配于 `hyperf/db` 的 `PgSQL` 适配器。

# v2.1.7 - 2021-02-22

## 修复

- [#3272](https://github.com/hyperf/hyperf/pull/3272) 修复使用 `doctrine/dbal` 修改数据库字段名报错的问题。

## 新增

- [#3261](https://github.com/hyperf/hyperf/pull/3261) 新增方法 `Pipeline::handleCarry`，可以方便处理返回值。
- [#3267](https://github.com/hyperf/hyperf/pull/3267) 新增 `Hyperf\Utils\Reflection\ClassInvoker`，用于执行非公共方法和读取非公共变量。
- [#3268](https://github.com/hyperf/hyperf/pull/3268) 为 `kafka` 消费者新增订阅多个主题的能力。
- [#3193](https://github.com/hyperf/hyperf/pull/3193) [#3296](https://github.com/hyperf/hyperf/pull/3296) 为 `phar:build` 新增选项 `-M`，可以用来映射外部的文件或目录到 `Phar` 包中。 

## 变更

- [#3258](https://github.com/hyperf/hyperf/pull/3258) 为不同的 `kafka` 消费者设置不同的 Client ID。
- [#3282](https://github.com/hyperf/hyperf/pull/3282) 为 `hyperf/signal` 将拼写错误的 `stoped` 修改为 `stopped`。

# v2.1.6 - 2021-02-08

## 修复

- [#3233](https://github.com/hyperf/hyperf/pull/3233) 修复 `AMQP` 组件，因连接服务端失败，导致连接池耗尽的问题。
- [#3245](https://github.com/hyperf/hyperf/pull/3245) 修复 `hyperf/kafka` 组件设置 `autoCommit` 为 `false` 无效的问题。
- [#3255](https://github.com/hyperf/hyperf/pull/3255) 修复 `Nsq` 消费者进程，无法触发 `defer` 方法的问题。

## 优化

- [#3249](https://github.com/hyperf/hyperf/pull/3249) 优化 `hyperf/kafka` 组件，可以重用连接进行消息发布。

## 移除

- [#3235](https://github.com/hyperf/hyperf/pull/3235) 移除 `hyperf/kafka` 组件 `rebalance` 检查，因为底层库 `longlang/phpkafka` 增加了对应的检查。

# v2.1.5 - 2021-02-01

## 修复

- [#3204](https://github.com/hyperf/hyperf/pull/3204) 修复在 `hyperf/rpc-server` 组件中，中间件会被意外替换的问题。
- [#3209](https://github.com/hyperf/hyperf/pull/3209) 修复 `hyperf/amqp` 组件在使用协程风格服务，且因超时意外报错时，没有办法正常回收到连接池的问题。
- [#3222](https://github.com/hyperf/hyperf/pull/3222) 修复 `hyperf/database` 组件中 `JOIN` 查询会导致内存泄露的问题。
- [#3228](https://github.com/hyperf/hyperf/pull/3228) 修复 `hyperf/tracer` 组件中，在 `defer` 中调用 `flush` 失败时，会导致进程异常退出的问题。
- [#3230](https://github.com/hyperf/hyperf/pull/3230) 修复 `hyperf/scout` 组件中 `orderBy` 方法无效的问题。

## 新增

- [#3211](https://github.com/hyperf/hyperf/pull/3211) 为 `hyperf/nacos` 组件添加了新的配置项 `url`，用于访问 `Nacos` 服务。
- [#3214](https://github.com/hyperf/hyperf/pull/3214) 新增类 `Hyperf\Utils\Channel\Caller`，可以允许用户使用协程安全的连接，避免连接被多个协程绑定，导致报错的问题。
- [#3224](https://github.com/hyperf/hyperf/pull/3224) 新增方法 `Hyperf\Utils\CodeGen\Package::getPrettyVersion()`，允许用户获取组件的版本。

## 变更

- [#3218](https://github.com/hyperf/hyperf/pull/3218) 默认为 `AMQP` 配置 `QOS` 参数，`prefetch_count` 为 `1`，`global` 为 `false`，`prefetch_size` 为 `0`。
- [#3224](https://github.com/hyperf/hyperf/pull/3224) 为组件 `jean85/pretty-package-versions` 升级版本到 `^1.2|^2.0`, 支持 `Composer 2.x`。

> 如果使用 composer 2.x，则需要安装 jean85/pretty-package-versions 的 ^2.0 版本，反之安装 ^1.2 版本

## 优化

- [#3226](https://github.com/hyperf/hyperf/pull/3226) 优化 `hyperf/database` 组件，使用 `group by` 或 `having` 时执行子查询获得总数。

# v2.1.4 - 2021-01-25

## 修复

- [#3165](https://github.com/hyperf/hyperf/pull/3165) 修复方法 `Hyperf\Database\Schema\MySqlBuilder::getColumnListing` 在 `MySQL 8.0` 版本中无法正常使用的问题。
- [#3174](https://github.com/hyperf/hyperf/pull/3174) 修复 `hyperf/database` 组件中 `where` 语句因为不严谨的代码编写，导致被绑定参数会被恶意替换的问题。
- [#3179](https://github.com/hyperf/hyperf/pull/3179) 修复 `json-rpc` 客户端因对端服务重启，导致接收数据一直异常的问题。
- [#3189](https://github.com/hyperf/hyperf/pull/3189) 修复 `kafka` 在集群模式下无法正常使用的问题。
- [#3191](https://github.com/hyperf/hyperf/pull/3191) 修复 `json-rpc` 客户端因对端服务重启，导致连接池中的连接全部失效，新的请求进来时，首次使用皆会报错的问题。

## 新增

- [#3170](https://github.com/hyperf/hyperf/pull/3170) 为 `hyperf/watcher` 组件新增了更加友好的驱动器 `FindNewerDriver`，支持 `Mac` `Linux` 和 `Docker`。
- [#3195](https://github.com/hyperf/hyperf/pull/3195) 为 `JsonRpcPoolTransporter` 新增了重试机制, 当连接、发包、收包失败时，默认重试 2 次，收包超时不进行重试。

## 优化

- [#3169](https://github.com/hyperf/hyperf/pull/3169) 优化了 `ErrorExceptionHandler` 中与 `set_error_handler` 相关的入参代码, 解决静态检测因入参不匹配导致报错的问题。
- [#3191](https://github.com/hyperf/hyperf/pull/3191) 优化了 `hyperf/json-rpc` 组件, 当连接中断后，会先尝试重连。

## 变更

- [#3174](https://github.com/hyperf/hyperf/pull/3174) 严格检查 `hyperf/database` 组件中 `where` 语句绑定参数。

## 新组件孵化

- [DAG](https://github.com/hyperf/dag-incubator) 轻量级有向无环图任务编排库。
- [RPN](https://github.com/hyperf/rpn-incubator) 逆波兰表示法。

# v2.1.3 - 2021-01-18

## 修复

- [#3070](https://github.com/hyperf/hyperf/pull/3070) 修复 `tracer` 组件无法正常使用的问题。
- [#3106](https://github.com/hyperf/hyperf/pull/3106) 修复协程从已被销毁的协程中复制协程上下文时导致报错的问题。
- [#3108](https://github.com/hyperf/hyperf/pull/3108) 修复使用 `describe:routes` 命令时，相同 `callback` 不同路由组的路由会被替换覆盖的问题。
- [#3118](https://github.com/hyperf/hyperf/pull/3118) 修复 `migrations` 配置名位置错误的问题。
- [#3126](https://github.com/hyperf/hyperf/pull/3126) 修复 `Swoole` 扩展 `v4.6` 版本中，`SWOOLE_HOOK_SOCKETS` 与 `jaeger` 冲突的问题。
- [#3137](https://github.com/hyperf/hyperf/pull/3137) 修复 `database` 组件，当没有主动设置 `PDO::ATTR_PERSISTENT` 为 `true` 时，导致的类型错误。
- [#3141](https://github.com/hyperf/hyperf/pull/3141) 修复使用 `Migration` 时，`doctrine/dbal` 无法正常工作的问题。

## 新增

- [#3059](https://github.com/hyperf/hyperf/pull/3059) 为 `view-engine` 组件增加合并任意标签的能力。
- [#3123](https://github.com/hyperf/hyperf/pull/3123) 为 `view-engine` 组件增加 `ComponentAttributeBag::has()` 方法。

# v2.1.2 - 2021-01-11

## 修复

- [#3050](https://github.com/hyperf/hyperf/pull/3050) 修复在 `increment()` 后使用 `save()` 时，导致 `extra` 数据被保存两次的问题。
- [#3082](https://github.com/hyperf/hyperf/pull/3082) 修复 `hyperf/db` 组件在 `defer` 中使用时，会导致连接被其他协程绑定的问题。
- [#3084](https://github.com/hyperf/hyperf/pull/3084) 修复 `phar` 打包后 `getRealPath` 无法正常工作的问题。
- [#3087](https://github.com/hyperf/hyperf/pull/3087) 修复使用 `AOP` 时，`pipeline` 导致内存泄露的问题。
- [#3095](https://github.com/hyperf/hyperf/pull/3095) 修复 `hyperf/scout` 组件中，`ElasticsearchEngine::getTotalCount()` 无法兼容 `Elasticsearch 7.0` 版本的问题。

## 新增

- [#2847](https://github.com/hyperf/hyperf/pull/2847) 新增 `hyperf/kafka` 组件。
- [#3066](https://github.com/hyperf/hyperf/pull/3066) 为 `hyperf/db` 组件新增 `ConnectionInterface::run(Closure $closure)` 方法。

## 优化

- [#3046](https://github.com/hyperf/hyperf/pull/3046) 打包 `phar` 时，优化了重写 `scan_cacheable` 的代码。

## 变更

- [#3077](https://github.com/hyperf/hyperf/pull/3077) 因组件 `league/flysystem` 的 `2.0` 版本无法兼容，故降级到 `^1.0`。

# v2.1.1 - 2021-01-04

## 修复

- [#3045](https://github.com/hyperf/hyperf/pull/3045) 修复 `database` 组件，当没有主动设置 `PDO::ATTR_PERSISTENT` 为 `true` 时，导致的类型错误。
- [#3047](https://github.com/hyperf/hyperf/pull/3047) 修复 `socketio-server` 组件，为 `sid` 续约时报错的问题。
- [#3062](https://github.com/hyperf/hyperf/pull/3062) 修复 `grpc-server` 组件，入参无法被正确解析的问题。

## 新增

- [#3052](https://github.com/hyperf/hyperf/pull/3052) 为 `metric` 组件，新增了收集命令行指标的功能。
- [#3054](https://github.com/hyperf/hyperf/pull/3054) 为 `socketio-server` 组件，新增了 `Engine::close` 协议支持，并在调用方法 `getRequest` 失败时，抛出连接已被关闭的异常。

# v2.1.0 - 2020-12-28

## 依赖升级

- 升级 `php` 版本到 `>=7.3`。
- 升级组件 `phpunit/phpunit` 版本到 `^9.0`。
- 升级组件 `guzzlehttp/guzzle` 版本到 `^6.0|^7.0`。
- 升级组件 `vlucas/phpdotenv` 版本到 `^5.0`。
- 升级组件 `endclothing/prometheus_client_php` 版本到 `^1.0`。
- 升级组件 `twig/twig` 版本到 `^3.0`。
- 升级组件 `jcchavezs/zipkin-opentracing` 版本到 `^0.2.0`。
- 升级组件 `doctrine/dbal` 版本到 `^3.0`。
- 升级组件 `league/flysystem` 版本到 `^1.0|^2.0`。

## 移除

- 移除 `Hyperf\Amqp\Builder` 已弃用的成员变量 `$name`。
- 移除 `Hyperf\Amqp\Message\ConsumerMessageInterface` 已弃用的方法 `consume()`。
- 移除 `Hyperf\AsyncQueue\Driver\Driver` 已弃用的成员变量 `$running`。
- 移除 `Hyperf\HttpServer\CoreMiddleware` 已弃用的方法 `parseParameters()`。
- 移除 `Hyperf\Utils\Coordinator\Constants` 已弃用的常量 `ON_WORKER_START` 和 `ON_WORKER_EXIT`。
- 移除 `Hyperf\Utils\Coordinator` 已弃用的方法 `get()`。
- 移除配置文件 `rate-limit.php`, 请使用 `rate_limit.php`。
- 移除无用的类 `Hyperf\Resource\Response\ResponseEmitter`。
- 将组件 `hyperf/paginator` 从 `hyperf/database` 依赖中移除。
- 移除 `Hyperf\Utils\Coroutine\Concurrent` 中的方法 `stats()`。

## 变更

- 方法 `Hyperf\Utils\Coroutine::parentId` 返回父协程的协程 ID
  * 如果在主协程中，则会返回 0。
  * 如果在非协程环境中使用，则会抛出 `RunningInNonCoroutineException` 异常。
  * 如果协程环境已被销毁，则会抛出 `CoroutineDestroyedException` 异常。

- 类 `Hyperf\Guzzle\CoroutineHandler`
  * 删除了 `execute()` 方法。
  * 方法 `initHeaders()` 将会返回初始化好的 Header 列表, 而不是直接将 `$headers` 赋值到客户端中。
  * 删除了 `checkStatusCode()` 方法。

- [#2720](https://github.com/hyperf/hyperf/pull/2720) 不再在方法 `PDOStatement::bindValue()` 中设置 `data_type`，已避免字符串索引中使用整形时，导致索引无法被命中的问题。
- [#2871](https://github.com/hyperf/hyperf/pull/2871) 从 `StreamInterface` 中获取数据时，使用 `(string) $body` 而不是 `$body->getContents()`，因为方法 `getContents()` 只会返回剩余的数据，而非全部数据。
- [#2909](https://github.com/hyperf/hyperf/pull/2909) 允许设置重复的中间件。
- [#2935](https://github.com/hyperf/hyperf/pull/2935) 修改了 `Exception Formatter` 的默认规则。
- [#2979](https://github.com/hyperf/hyperf/pull/2979) 命令行 `gen:model` 不再自动将 `decimal` 格式转化为 `float`。

## 即将废弃

- 类 `Hyperf\AsyncQueue\Signal\DriverStopHandler` 将会在 `v2.2` 版本中弃用, 请使用 `Hyperf\Process\Handler\ProcessStopHandler` 代替。
- 类 `Hyperf\Server\SwooleEvent` 将会在 `v3.0` 版本中弃用, 请使用 `Hyperf\Server\Event` 代替。

## 新增

- [#2659](https://github.com/hyperf/hyperf/pull/2659) [#2663](https://github.com/hyperf/hyperf/pull/2663) 新增了 [Swow](https://github.com/swow/swow) 驱动支持。
- [#2671](https://github.com/hyperf/hyperf/pull/2671) 新增监听器 `Hyperf\AsyncQueue\Listener\QueueHandleListener`，用来记录异步队列的运行日志。
- [#2923](https://github.com/hyperf/hyperf/pull/2923) 新增类 `Hyperf\Utils\Waiter`，可以用来等待一个协程结束。
- [#3001](https://github.com/hyperf/hyperf/pull/3001) 新增方法 `Hyperf\Database\Model\Collection::columns()`，类似于 `array_column`。
- [#3002](https://github.com/hyperf/hyperf/pull/3002) 为 `Json::decode` 和 `Json::encode` 新增参数 `$depth` 和 `$flags`。

## 修复

- [#2741](https://github.com/hyperf/hyperf/pull/2741) 修复自定义进程无法在 `Swow` 驱动下使用的问题。

## 优化

- [#3009](https://github.com/hyperf/hyperf/pull/3009) 优化了 `prometheus`，使其支持 `https` 和 `http` 协议。

# v2.0.25 - 2020-12-28

## 新增

- [#3015](https://github.com/hyperf/hyperf/pull/3015) 为 `socketio-server` 增加了可以自动清理垃圾的机制。
- [#3030](https://github.com/hyperf/hyperf/pull/3030) 新增了方法 `ProceedingJoinPoint::getInstance()`，可以允许在使用 `AOP` 时，拿到被切入的实例。

## 优化

- [#3011](https://github.com/hyperf/hyperf/pull/3011) 优化 `hyperf/tracer` 组件，可以在链路追踪中记录异常信息。

# v2.0.24 - 2020-12-21

## 修复

- [#2978](https://github.com/hyperf/hyperf/pull/2980) 修复当没有引用 `hyperf/contract` 时，`hyperf/snowflake` 组件会无法正常使用的问题。
- [#2983](https://github.com/hyperf/hyperf/pull/2983) 修复使用协程风格服务时，常量 `SWOOLE_HOOK_FLAGS` 无法生效的问题。
- [#2993](https://github.com/hyperf/hyperf/pull/2993) 修复方法 `Arr::merge()` 入参 `$array1` 为空时，会将关联数组，错误的转化为索引数组的问题。

## 优化

- [#2973](https://github.com/hyperf/hyperf/pull/2973) 支持自定义的 `HTTP` 状态码。
- [#2992](https://github.com/hyperf/hyperf/pull/2992) 优化组件 `hyperf/validation` 的依赖关系，移除 `hyperf/devtool` 组件。

# v2.0.23 - 2020-12-14

## 新增

- [#2872](https://github.com/hyperf/hyperf/pull/2872) 新增 `hyperf/phar` 组件，用于将 `Hyperf` 项目打包成 `phar`。

## 修复

- [#2952](https://github.com/hyperf/hyperf/pull/2952) 修复 `Nacos` 配置中心，在协程风格服务中无法正常使用的问题。

## 变更

- [#2934](https://github.com/hyperf/hyperf/pull/2934) 变更配置文件 `scout.php`，默认使用 `Elasticsearch` 索引作为模型索引。
- [#2958](https://github.com/hyperf/hyperf/pull/2958) 变更 `view` 组件默认的渲染引擎为 `NoneEngine`。

## 优化

- [#2951](https://github.com/hyperf/hyperf/pull/2951) 优化 `model-cache` 组件，使其执行完多次事务后，只会删除一次缓存。
- [#2953](https://github.com/hyperf/hyperf/pull/2953) 隐藏命令行因执行 `exit` 导致的异常 `Swoole\ExitException`。
- [#2963](https://github.com/hyperf/hyperf/pull/2963) 当异步风格服务使用 `SWOOLE_BASE` 时，会从默认的事件回调中移除 `onStart` 事件。

# v2.0.22 - 2020-12-07

## 新增

- [#2896](https://github.com/hyperf/hyperf/pull/2896) 允许 `view-engine` 组件配置自定义加载类组件和匿名组件。
- [#2921](https://github.com/hyperf/hyperf/pull/2921) 为 `Parallel` 增加 `count()` 方法，返回同时执行的个数。

## 修复

- [#2913](https://github.com/hyperf/hyperf/pull/2913) 修复使用 `ORM` 中的 `with` 预加载逻辑时，会因循环依赖导致内存泄露的问题。
- [#2915](https://github.com/hyperf/hyperf/pull/2915) 修复 `WebSocket` 工作进程会因 `onMessage` or `onClose` 回调失败，导致进程退出的问题。
- [#2927](https://github.com/hyperf/hyperf/pull/2927) 修复验证器规则 `alpha_dash` 不支持 `int` 的问题。

## 变更

- [#2918](https://github.com/hyperf/hyperf/pull/2918) 当使用 `watcher` 组件时，不可以开启 `daemonize`。
- [#2930](https://github.com/hyperf/hyperf/pull/2930) 更新 `php-amqplib` 组件最低版本由 `v2.7` 到 `v2.9.2`。

## 优化

- [#2931](https://github.com/hyperf/hyperf/pull/2931) 判断控制器方法是否存在时，使用实际从容器中得到的对象，而非命名空间。

# v2.0.21 - 2020-11-30

## 新增

- [#2857](https://github.com/hyperf/hyperf/pull/2857) 为 `service-governance` 组件新增 `Consul` 的 `ACL Token` 支持。
- [#2870](https://github.com/hyperf/hyperf/pull/2870) 为脚本 `vendor:publish` 支持发布配置目录的能力。
- [#2875](https://github.com/hyperf/hyperf/pull/2875) 为 `watcher` 组件新增可选项 `no-restart`，允许动态修改注解缓存，但不重启服务。
- [#2883](https://github.com/hyperf/hyperf/pull/2883) 为 `scout` 组件数据导入脚本，增加可选项 `--chunk` 和 `--column|c`，允许用户指定任一字段，进行数据插入，解决偏移量过大导致查询效率慢的问题。
- [#2891](https://github.com/hyperf/hyperf/pull/2891) 为 `crontab` 组件新增可用于发布的配置文件。

## 修复

- [#2874](https://github.com/hyperf/hyperf/pull/2874) 修复在使用 `watcher` 组件时， `scan.ignore_annotations` 配置不生效的问题。
- [#2878](https://github.com/hyperf/hyperf/pull/2878) 修复 `nsq` 组件中，`nsqd` 配置无法正常工作的问题。

## 变更

- [#2851](https://github.com/hyperf/hyperf/pull/2851) 修改 `view` 组件默认的配置文件，使用 `view-engine` 引擎，而非第三方 `blade` 引擎。

## 优化

- [#2785](https://github.com/hyperf/hyperf/pull/2785) 优化 `watcher` 组件，使其异常信息更加人性化。
- [#2861](https://github.com/hyperf/hyperf/pull/2861) 优化 `Guzzle Coroutine Handler`，当其 `statusCode` 小于 `0` 时，抛出对应异常。
- [#2868](https://github.com/hyperf/hyperf/pull/2868) 优化 `Guzzle` 的 `sink` 配置，使其支持传入 `resource`。

# v2.0.20 - 2020-11-23

## 新增

- [#2824](https://github.com/hyperf/hyperf/pull/2824) 为 `Hyperf\Database\Query\Builder` 增加方法 `simplePaginate()`。

## 修复

- [#2820](https://github.com/hyperf/hyperf/pull/2820) 修复使用 `fanout` 交换机时，`AMQP` 消费者无法正常工作的问题。
- [#2831](https://github.com/hyperf/hyperf/pull/2831) 修复 `AMQP` 连接会被客户端意外关闭的问题。
- [#2848](https://github.com/hyperf/hyperf/pull/2848) 修复在 `defer` 中使用数据库组件时，会导致数据库连接会同时被其他协程绑定的问题。

## 变更

- [#2824](https://github.com/hyperf/hyperf/pull/2824) 修改 `Hyperf\Database\Query\Builder` 方法 `paginate()` 返回值类型，由 `PaginatorInterface` 变更为 `LengthAwarePaginatorInterface`。

## 优化

- [#2766](https://github.com/hyperf/hyperf/pull/2766) 优化 `Tracer` 组件，在抛出异常的情况下，也可以执行 `finish` 方法，记录链路。
- [#2805](https://github.com/hyperf/hyperf/pull/2805) 优化 `Nacos` 进程，可以安全停止。
- [#2821](https://github.com/hyperf/hyperf/pull/2821) 优化工具类 `Json` 和 `Xml`，使其抛出一致的异常。
- [#2827](https://github.com/hyperf/hyperf/pull/2827) 优化 `Hyperf\Server\ServerConfig`，解决方法 `__set` 因返回值不为 `void`，导致不兼容 `PHP8` 的问题。
- [#2839](https://github.com/hyperf/hyperf/pull/2839) 优化 `Hyperf\Database\Schema\ColumnDefinition` 的注释。

# v2.0.19 - 2020-11-17

## 新增

- [#2794](https://github.com/hyperf/hyperf/pull/2794) [#2802](https://github.com/hyperf/hyperf/pull/2802) 为 `Session` 组件新增配置项 `options.cookie_lifetime`, 允许用户自己设置 `Cookies` 的超时时间。

## 修复

- [#2783](https://github.com/hyperf/hyperf/pull/2783) 修复 `NSQ` 消费者无法在协程风格下正常使用的问题。
- [#2788](https://github.com/hyperf/hyperf/pull/2788) 修复非静态方法 `__handlePropertyHandler()` 在代理类中，被静态调用的问题。
- [#2790](https://github.com/hyperf/hyperf/pull/2790) 修复 `ETCD` 配置中心，`BootProcessListener` 监听器无法在协程风格下正常使用的问题。
- [#2803](https://github.com/hyperf/hyperf/pull/2803) 修复当 `Request` 无法实例化时，`HTTP` 响应数据被清除的问题。
- [#2807](https://github.com/hyperf/hyperf/pull/2807) 修复当存在重复的中间件时，中间件的表现会与预期不符的问题。

## 优化

- [#2750](https://github.com/hyperf/hyperf/pull/2750) 优化 `Scout` 组件，当没有配置搜索引擎 `index` 或 `Elasticsearch` 版本高于 `7.0` 时，使用 `index` 而非 `type` 作为模型的搜索条件。

# v2.0.18 - 2020-11-09

## 新增

- [#2752](https://github.com/hyperf/hyperf/pull/2752) 为注解 `@AutoController` `@Controller` 和 `@Mapping` 添加 `options` 参数，用于设置路由元数据。

## 修复

- [#2768](https://github.com/hyperf/hyperf/pull/2768) 修复 `WebSocket` 握手失败时导致内存泄露的问题。
- [#2777](https://github.com/hyperf/hyperf/pull/2777) 修复低版本 `redis` 扩展，`RedisCluster` 构造函数 `$auth` 不支持 `null`，导致报错的问题。
- [#2779](https://github.com/hyperf/hyperf/pull/2779) 修复因没有设置 `translation` 配置文件导致服务启动失败的问题。

## 变更

- [#2765](https://github.com/hyperf/hyperf/pull/2765) 变更 `Concurrent` 类中创建协程逻辑，由方法 `Hyperf\Utils\Coroutine::create()` 代替原来的 `Swoole\Coroutine::create()`。

## 优化

- [#2347](https://github.com/hyperf/hyperf/pull/2347) 为 `AMQP` 的 `ConsumerMessage` 增加参数 `$waitTimeout`，用于在协程风格服务中，安全停止服务。

# v2.0.17 - 2020-11-02

## 新增

- [#2625](https://github.com/hyperf/hyperf/pull/2625) 新增 `Hyperf\Tracer\Aspect\JsonRpcAspect`, 可以让 `Tracer` 组件支持 `JsonRPC` 的链路追踪。
- [#2709](https://github.com/hyperf/hyperf/pull/2709) [#2733](https://github.com/hyperf/hyperf/pull/2733) 为 `Model` 新增了对应的 `@mixin` 注释，提升模型的静态方法提示能力。
- [#2726](https://github.com/hyperf/hyperf/pull/2726) [#2733](https://github.com/hyperf/hyperf/pull/2733) 为 `gen:model` 脚本增加可选项 `--with-ide`, 可以生成对应的 `IDE` 文件。
- [#2737](https://github.com/hyperf/hyperf/pull/2737) 新增 [view-engine](https://github.com/hyperf/view-engine) 组件，可以不需要在 `Task` 进程中渲染页面。

## 修复

- [#2719](https://github.com/hyperf/hyperf/pull/2719) 修复 `Arr::merge` 会因 `array1` 中不包含 `array2` 中存在的 `$key` 时，导致的报错问题。
- [#2723](https://github.com/hyperf/hyperf/pull/2723) 修复 `Paginator::resolveCurrentPath` 无法正常工作的问题。

## 优化

- [#2746](https://github.com/hyperf/hyperf/pull/2746) 优化 `@Task` 注解，只会在 `worker` 进程中执行时，会投递到 `task` 进程执行对应逻辑，其他进程则会降级为同步执行。

## 变更

- [#2728](https://github.com/hyperf/hyperf/pull/2728) `JsonRPC` 中，以 `__` 为前缀的方法，都不会在注册到 `RPC` 服务中，例如 `__construct`, '__call'。

# v2.0.16 - 2020-10-26

## 新增

- [#2682](https://github.com/hyperf/hyperf/pull/2682) 为 `CacheableInterface` 新增方法 `getCacheTTL` 可根据不同模型设置不同的缓存时间。
- [#2696](https://github.com/hyperf/hyperf/pull/2696) 新增 Swoole Tracker 的内存检测工具。

## 修复

- [#2680](https://github.com/hyperf/hyperf/pull/2680) 修复 `CastsValue` 因为没有设置 `$isSynchronized` 默认值，导致的类型错误。
- [#2680](https://github.com/hyperf/hyperf/pull/2680) 修复 `CastsValue` 中 `$items` 默认值会被 `__construct` 覆盖的问题。
- [#2693](https://github.com/hyperf/hyperf/pull/2693) 修复 `hyperf/retry` 组件，`Budget` 表现不符合期望的问题。
- [#2695](https://github.com/hyperf/hyperf/pull/2695) 修复方法 `Container::define()` 因为容器中的对象已被实例化，而无法重定义的问题。

## 优化

- [#2611](https://github.com/hyperf/hyperf/pull/2611) 优化 `hyperf/watcher` 组件 `FindDriver` ，使其可以在 `Alpine` 镜像中使用。
- [#2662](https://github.com/hyperf/hyperf/pull/2662) 优化 `Amqp` 消费者进程，使其可以配合 `Signal` 组件安全停止。
- [#2690](https://github.com/hyperf/hyperf/pull/2690) 优化 `hyperf/tracer` 组件，确保其可以正常执行 `finish` 和 `flush` 方法。

# v2.0.15 - 2020-10-19

## 新增

- [#2654](https://github.com/hyperf/hyperf/pull/2654) 新增方法 `Hyperf\Utils\Resource::from`，可以方便的将 `string` 转化为 `resource`。

## 修复

- [#2634](https://github.com/hyperf/hyperf/pull/2634) [#2640](https://github.com/hyperf/hyperf/pull/2640) 修复 `snowflake` 组件中，元数据生成器 `RedisSecondMetaGenerator` 会产生相同元数据的问题。
- [#2639](https://github.com/hyperf/hyperf/pull/2639) 修复 `json-rpc` 组件中，异常无法正常被序列化的问题。
- [#2643](https://github.com/hyperf/hyperf/pull/2643) 修复 `scout:flush` 执行失败的问题。

## 优化

- [#2656](https://github.com/hyperf/hyperf/pull/2656) 优化了 `json-rpc` 组件中，参数解析失败后，也可以返回对应的错误信息。

# v2.0.14 - 2020-10-12

## 新增

- [#1172](https://github.com/hyperf/hyperf/pull/1172) 新增基于 `laravel/scout` 实现的组件 `hyperf/scout`, 可以通过搜索引擎进行模型查询。
- [#1868](https://github.com/hyperf/hyperf/pull/1868) 新增 `Redis` 组件的哨兵模式。
- [#1969](https://github.com/hyperf/hyperf/pull/1969) 新增组件 `hyperf/resource` and `hyperf/resource-grpc`，可以更加方便的将模型转化为 Response。

## 修复

- [#2594](https://github.com/hyperf/hyperf/pull/2594) 修复 `hyperf/crontab` 组件因为无法正常响应 `hyperf/signal`，导致无法停止的问题。
- [#2601](https://github.com/hyperf/hyperf/pull/2601) 修复命令 `gen:model` 因为 `getter` 和 `setter` 同时存在时，注释 `@property` 会被 `@property-read` 覆盖的问题。
- [#2607](https://github.com/hyperf/hyperf/pull/2607) [#2637](https://github.com/hyperf/hyperf/pull/2637) 修复使用 `RetryAnnotationAspect` 时，会有一定程度内存泄露的问题。
- [#2624](https://github.com/hyperf/hyperf/pull/2624) 修复组件 `hyperf/testing` 因使用了 `guzzle 7.0` 和 `CURL HOOK` 导致无法正常工作的问题。
- [#2632](https://github.com/hyperf/hyperf/pull/2632) [#2635](https://github.com/hyperf/hyperf/pull/2635) 修复 `hyperf\redis` 组件集群模式，无法设置密码的问题。

## 优化

- [#2603](https://github.com/hyperf/hyperf/pull/2603) 允许 `hyperf/database` 组件，`whereNull` 方法接受 `array` 作为入参。

# v2.0.13 - 2020-09-28

## 新增

- [#2445](https://github.com/hyperf/hyperf/pull/2445) 当使用异常捕获器 `WhoopsExceptionHandler` 返回 `JSON` 格式化的数据时，自动添加异常的 `Trace` 信息。
- [#2580](https://github.com/hyperf/hyperf/pull/2580) 新增 `grpc-client` 组件的 `metadata` 支持。

## 修复

- [#2559](https://github.com/hyperf/hyperf/pull/2559) 修复使用 `socket-io` 连接 `socketio-server` 时，因为携带 `query` 信息，导致事件无法被触发的问题。
- [#2565](https://github.com/hyperf/hyperf/pull/2565) 修复生成代理类时，因为存在匿名类，导致代理类在没有父类的情况下使用了 `parent::class` 而报错的问题。
- [#2578](https://github.com/hyperf/hyperf/pull/2578) 修复当自定义进程抛错后，事件 `AfterProcessHandle` 无法被触发的问题。
- [#2582](https://github.com/hyperf/hyperf/pull/2582) 修复使用 `Redis::multi` 且在 `defer` 中使用了其他 `Redis` 指令后，导致 `Redis` 同时被两个协程使用而报错的问题。
- [#2589](https://github.com/hyperf/hyperf/pull/2589) 修复使用了协程风格服务时，`AMQP` 消费者无法正常启动的问题。
- [#2590](https://github.com/hyperf/hyperf/pull/2590) 修复使用了协程风格服务时，`Crontab` 无法正常工作的问题。

## 优化

- [#2561](https://github.com/hyperf/hyperf/pull/2561) 优化关闭 `AMQP` 连接失败时的错误信息。
- [#2584](https://github.com/hyperf/hyperf/pull/2584) 当服务关闭时，不再删除 `Nacos` 中对应的服务。

# v2.0.12 - 2020-09-21

## 新增

- [#2512](https://github.com/hyperf/hyperf/pull/2512) 为 [hyperf/database](https://github.com/hyperf/database) 组件方法 `MySqlGrammar::compileColumnListing` 新增返回字段 `column_type`。

## 修复

- [#2490](https://github.com/hyperf/hyperf/pull/2490) 修复 [hyperf/grpc-client](https://github.com/hyperf/grpc-client) 组件中，流式客户端无法正常工作的问题。
- [#2509](https://github.com/hyperf/hyperf/pull/2509) 修复 [hyperf/database](https://github.com/hyperf/database) 组件中，使用小驼峰模式后，访问器无法正常工作的问题。
- [#2535](https://github.com/hyperf/hyperf/pull/2535) 修复 [hyperf/database](https://github.com/hyperf/database) 组件中，使用 `gen:model` 后，通过访问器生成的注释 `@property` 会被 `morphTo` 覆盖的问题。
- [#2546](https://github.com/hyperf/hyperf/pull/2546) 修复 [hyperf/db-connection](https://github.com/hyperf/db-connection) 组件中，使用 `left join` 等复杂查询后，`MySQL` 连接无法正常释放的问题。

## 优化

- [#2490](https://github.com/hyperf/hyperf/pull/2490) 优化 [hyperf/grpc-client](https://github.com/hyperf/grpc-client) 组件中的异常和单元测试。

# v2.0.11 - 2020-09-14

## 新增

- [#2455](https://github.com/hyperf/hyperf/pull/2455) 为 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 组件新增方法 `Socket::getRequest` 用于获取 `Psr7` 规范的 `Request`。
- [#2459](https://github.com/hyperf/hyperf/pull/2459) 为 [hyperf/async-queue](https://github.com/hyperf/async-queue) 组件新增监听器 `ReloadChannelListener` 用于自动将超时队列里的消息移动到等待执行队列中。
- [#2463](https://github.com/hyperf/hyperf/pull/2463) 为 [hyperf/database](https://github.com/hyperf/database) 组件新增可选的 `ModelRewriteGetterSetterVisitor` 用于为模型生成对应的 `Getter` 和 `Setter`。
- [#2475](https://github.com/hyperf/hyperf/pull/2475) 为 [hyperf/retry](https://github.com/hyperf/retry) 组件的 `Fallback` 回调，默认增加 `throwable` 参数。

## 修复

- [#2464](https://github.com/hyperf/hyperf/pull/2464) 修复 [hyperf/database](https://github.com/hyperf/database) 组件中，小驼峰模式模型的 `fill` 方法无法正常使用的问题。
- [#2478](https://github.com/hyperf/hyperf/pull/2478) 修复 [hyperf/websocket-server](https://github.com/hyperf/websocket-server) 组件中，`Sender::check` 无法检测非 `WebSocket` 的 `fd` 值。
- [#2488](https://github.com/hyperf/hyperf/pull/2488) 修复 [hyperf/database](https://github.com/hyperf/database) 组件中，当 `pdo` 实例化失败后 `beginTransaction` 调用失败的问题。

## 优化

- [#2461](https://github.com/hyperf/hyperf/pull/2461) 优化 [hyperf/reactive-x](https://github.com/hyperf/reactive-x) 组件 `HTTP` 路由监听器，可以监听任意端口路由。
- [#2465](https://github.com/hyperf/hyperf/pull/2465) 优化 [hyperf/retry](https://github.com/hyperf/retry) 组件 `FallbackRetryPolicy` 中 `fallback` 除了可以填写被 `is_callable` 识别的代码外，还可以填写形如 `class@method` 的格式，框架会从 `Container` 中拿到对应的 `class`，然后执行其 `method` 方法。

## 变更

- [#2492](https://github.com/hyperf/hyperf/pull/2492) 调整 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 组件中的事件收集顺序，确保 `sid` 早于自定义 `onConnect` 被添加到房间中。

# v2.0.10 - 2020-09-07

## 新增

- [#2411](https://github.com/hyperf/hyperf/pull/2411) 为 [hyperf/database](https://github.com/hyperf/database) 组件新增 `Hyperf\Database\Query\Builder::forPageBeforeId` 方法。
- [#2420](https://github.com/hyperf/hyperf/pull/2420) [#2426](https://github.com/hyperf/hyperf/pull/2426) 为 [hyperf/command](https://github.com/hyperf/command) 组件新增默认选项 `enable-event-dispatcher` 用于初始化事件触发器。
- [#2433](https://github.com/hyperf/hyperf/pull/2433) 为 [hyperf/grpc-server](https://github.com/hyperf/grpc-server) 组件路由新增匿名函数支持。
- [#2441](https://github.com/hyperf/hyperf/pull/2441) 为 [hyperf/socketio-server](https://github.com/hyperf/socketio-server) 组件中 `SocketIO` 新增了一些 `setters`。

## 修复

- [#2427](https://github.com/hyperf/hyperf/pull/2427) 修复事件触发器在使用 `Pivot` 或 `MorphPivot` 不生效的问题。
- [#2443](https://github.com/hyperf/hyperf/pull/2443) 修复使用 [hyperf/Guzzle](https://github.com/hyperf/guzzle) 组件的 `Coroutine Handler` 时，无法正确获取和传递 `traceid` 和 `spanid` 的问题。
- [#2449](https://github.com/hyperf/hyperf/pull/2449) 修复发布 [hyperf/config-apollo](https://github.com/hyperf/config-apollo) 组件的配置文件时，配置文件名称错误的问题。

## 优化

- [#2429](https://github.com/hyperf/hyperf/pull/2429) 优化使用 `@Inject` 并且没有设置 `@var` 时的错误信息，方便定位问题，改善编程体验。
- [#2438](https://github.com/hyperf/hyperf/pull/2438) 优化当使用 [hyperf/model-cache](https://github.com/hyperf/model-cache) 组件与数据库事务搭配使用时，在事务中删除或修改模型数据会在事务提交后即时再删除缓存，而不再是在删除或修改模型数据时删除缓存数据。

# v2.0.9 - 2020-08-31

## 新增

- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 组件增加授权接口。
- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 组件增加 `nacos.enable` 配置，用于控制是否启用 `Nacos` 服务。
- [#2331](https://github.com/hyperf/hyperf/pull/2331) [hyperf/nacos](https://github.com/hyperf/nacos) 组件增加配置合并类型，默认使用全量覆盖。
- [#2377](https://github.com/hyperf/hyperf/pull/2377) 为 gRPC 客户端 的 request 增加 `ts` 请求头，以兼容 Node.js gRPC server 等。
- [#2384](https://github.com/hyperf/hyperf/pull/2384) 新增助手函数 `optional()`，以创建 `Hyperf\Utils\Optional` 对象或更方便 Optional 的使用。

## 修改

- [#2331](https://github.com/hyperf/hyperf/pull/2331) 修复 [hyperf/nacos](https://github.com/hyperf/nacos) 组件，服务或配置不存在时，会抛出异常的问题。
- [#2356](https://github.com/hyperf/hyperf/pull/2356) [#2368](https://github.com/hyperf/hyperf/pull/2368) 修复 `pid_file` 被用户修改后，命令行 `server:start` 启动失败的问题。
- [#2358](https://github.com/hyperf/hyperf/pull/2358) 修复验证器规则 `digits` 不支持 `int` 类型的问题。

## 优化

- [#2359](https://github.com/hyperf/hyperf/pull/2359) 优化自定义进程，在协程风格服务下，可以更加友好的停止。
- [#2363](https://github.com/hyperf/hyperf/pull/2363) 优化 [hyperf/di](https://github.com/hyperf/di) 组件，使其不需要依赖 [hyperf/config](https://github.com/hyperf/config) 组件。
- [#2373](https://github.com/hyperf/hyperf/pull/2373) 优化 [hyperf/validation](https://github.com/hyperf/validation) 组件的异常捕获器，使其返回 `Response` 时，自动添加 `content-type` 头。


# v2.0.8 - 2020-08-24

## 新增

- [#2334](https://github.com/hyperf/hyperf/pull/2334) 新增更加友好的数组递归合并方法 `Arr::merge`。
- [#2335](https://github.com/hyperf/hyperf/pull/2335) 新增 `Hyperf/Utils/Optional`，它可以接受任意参数，并允许访问该对象上的属性或调用其方法，即使给定的对象为 `null`，也不会引发错误。
- [#2336](https://github.com/hyperf/hyperf/pull/2336) 新增 `RedisNsqAdapter`，它通过 `NSQ` 发布消息，使用 `Redis` 记录房间信息。

## 修复

- [#2338](https://github.com/hyperf/hyperf/pull/2338) 修复文件系统使用 `S3` 适配器时，文件是否存在的逻辑与预期不符的 BUG。
- [#2340](https://github.com/hyperf/hyperf/pull/2340) 修复 `__FUNCTION__` 和 `__METHOD__` 魔术方法无法在被 `AOP` 重写的方法里正常工作的 BUG。

## 优化

- [#2319](https://github.com/hyperf/hyperf/pull/2319) 优化 `ResolverDispatcher` ，使项目发生循环依赖时，可以提供更加友好的错误提示。

# v2.0.7 - 2020-08-17

## 新增

- [#2307](https://github.com/hyperf/hyperf/pull/2307) [#2312](https://github.com/hyperf/hyperf/pull/2312) [hyperf/nsq](https://github.com/hyperf/nsq) 组件，新增 `NSQD` 的 `HTTP` 客户端。

## 修复

- [#2275](https://github.com/hyperf/hyperf/pull/2275) 修复配置中心，拉取配置进程会出现阻塞的 BUG。
- [#2276](https://github.com/hyperf/hyperf/pull/2276) 修复 `Apollo` 配置中心，当配置没有变更时，会清除所有本地配置项的 BUG。
- [#2280](https://github.com/hyperf/hyperf/pull/2280) 修复 `Interface` 的方法会被 `AOP` 重写，导致启动报错的 BUG。
- [#2281](https://github.com/hyperf/hyperf/pull/2281) 当使用 `Task` 组件，且没有启动协程时，`Signal` 组件会导致启动报错的 BUG。
- [#2304](https://github.com/hyperf/hyperf/pull/2304) 修复当使用 `SocketIOServer` 的内存适配器，删除 `sid` 时，会导致死循环的 BUG。
- [#2309](https://github.com/hyperf/hyperf/pull/2309) 修复 `JsonRpcHttpTransporter` 无法设置自定义超时时间的 BUG。

# v2.0.6 - 2020-08-10

## 新增

- [#2125](https://github.com/hyperf/hyperf/pull/2125) 新增 [hyperf/jet](https://github.com/hyperf/jet) 组件。`Jet` 是一个统一模型的 RPC 客户端，内置 JSONRPC 协议的适配，该组件可适用于所有的 `PHP (>= 7.2)` 环境，包括 PHP-FPM 和 Swoole 或 Hyperf。

## 修复

- [#2236](https://github.com/hyperf/hyperf/pull/2236) 修复 `Nacos` 使用负载均衡器选择节点失败的 BUG。
- [#2242](https://github.com/hyperf/hyperf/pull/2242) 修复 `watcher` 组件会重复收集多次注解的 BUG。

# v2.0.5 - 2020-08-03

## 新增

- [#2001](https://github.com/hyperf/hyperf/pull/2001) 新增参数 `$signature`，用于简化命令行的初始化工作。
- [#2204](https://github.com/hyperf/hyperf/pull/2204) 为方法 `parallel` 增加 `$concurrent` 参数，用于快速设置并发量。

## 修复

- [#2210](https://github.com/hyperf/hyperf/pull/2210) 修复 `WebSocket` 握手成功后，不会立马触发 `OnOpen` 事件的 BUG。
- [#2214](https://github.com/hyperf/hyperf/pull/2214) 修复 `WebSocket` 主动关闭连接时，不会触发 `OnClose` 事件的 BUG。
- [#2218](https://github.com/hyperf/hyperf/pull/2218) 修复在 `协程 Server` 下，`Sender::disconnect` 报错的 BUG。
- [#2227](https://github.com/hyperf/hyperf/pull/2227) 修复在 `协程 Server` 下，建立 `keepalive` 连接后，上下文数据无法在请求结束后销毁的 BUG。

## 优化

- [#2193](https://github.com/hyperf/hyperf/pull/2193) 优化 `Hyperf\Watcher\Driver\FindDriver`，使其扫描有变动的文件更加精确。
- [#2232](https://github.com/hyperf/hyperf/pull/2232) 优化 `model-cache` 的预加载功能，使其支持 `In` 和 `InRaw`。

# v2.0.4 - 2020-07-27

## 新增

- [#2144](https://github.com/hyperf/hyperf/pull/2144) 数据库查询事件 `Hyperf\Database\Events\QueryExecuted` 添加 `$result` 字段。
- [#2158](https://github.com/hyperf/hyperf/pull/2158) 路由 `Hyperf\HttpServer\Router\Handler` 中，添加 `$options` 字段。
- [#2162](https://github.com/hyperf/hyperf/pull/2162) 热更新组件添加 `Hyperf\Watcher\Driver\FindDriver`。
- [#2169](https://github.com/hyperf/hyperf/pull/2169) `Session` 组件新增配置 `session.options.domain`，用于替换 `Request` 中获取的 `domain`。
- [#2174](https://github.com/hyperf/hyperf/pull/2174) 模型生成器添加 `ModelRewriteTimestampsVisitor`，用于根据数据库字段 `created_at` 和 `updated_at`， 重写模型字段 `$timestamps`。
- [#2175](https://github.com/hyperf/hyperf/pull/2175) 模型生成器添加 `ModelRewriteSoftDeletesVisitor`，用于根据数据库字段 `deleted_at`， 添加或者移除 `SoftDeletes`。
- [#2176](https://github.com/hyperf/hyperf/pull/2176) 模型生成器添加 `ModelRewriteKeyInfoVisitor`，用于根据数据库主键，重写模型字段 `$incrementing` `$primaryKey` 和 `$keyType`。

## 修复

- [#2149](https://github.com/hyperf/hyperf/pull/2149) 修复自定义进程运行过程中无法从 Nacos 正常更新配置的 BUG。
- [#2159](https://github.com/hyperf/hyperf/pull/2159) 修复使用 `gen:migration` 时，由于文件已经存在导致的 `FATAL` 异常。

## 优化

- [#2043](https://github.com/hyperf/hyperf/pull/2043) 当 `SCAN` 目录都不存在时，抛出更加友好的异常。
- [#2182](https://github.com/hyperf/hyperf/pull/2182) 当使用 `WebSocket` 和 `Http` 服务且 `Http` 接口被访问时，不会记录 `WebSocket` 关闭连接的日志。

# v2.0.3 - 2020-07-20

## 新增

- [#1554](https://github.com/hyperf/hyperf/pull/1554) 新增 `hyperf/nacos` 组件。
- [#2082](https://github.com/hyperf/hyperf/pull/2082) 监听器 `Hyperf\Signal\Handler\WorkerStopHandler` 添加信号 `SIGINT` 监听。
- [#2097](https://github.com/hyperf/hyperf/pull/2097) `hyperf/filesystem` 新增 TencentCloud COS 支持.
- [#2122](https://github.com/hyperf/hyperf/pull/2122) 添加 Trait `\Hyperf\Snowflake\Concern\HasSnowflake` 为模型自动生成雪花算法的主键。

## 修复

- [#2017](https://github.com/hyperf/hyperf/pull/2017) 修复 Prometheus 使用 redis 打点时，改变 label 会导致收集报错的 BUG。
- [#2117](https://github.com/hyperf/hyperf/pull/2117) 修复使用 `server:watch` 时，注解 `@Inject` 有时会失效的 BUG。
- [#2123](https://github.com/hyperf/hyperf/pull/2123) 修复 `tracer` 会记录两次 `Redis 指令` 的 BUG。
- [#2139](https://github.com/hyperf/hyperf/pull/2139) 修复 `ValidationMiddleware` 在 `WebSocket` 服务下使用会报错的 BUG。
- [#2140](https://github.com/hyperf/hyperf/pull/2140) 修复请求抛出异常时，`Session` 无法保存的 BUG。

## 优化

- [#2080](https://github.com/hyperf/hyperf/pull/2080) 方法 `Hyperf\Database\Model\Builder::paginate` 中参数 `$perPage` 的类型从 `int` 更改为 `?int`。
- [#2110](https://github.com/hyperf/hyperf/pull/2110) 在使用 `hyperf/watcher` 时，会先检查进程是否存在，如果不存在，才会发送 `SIGTERM` 信号。
- [#2116](https://github.com/hyperf/hyperf/pull/2116) 优化组件 `hyperf/di` 的依赖。
- [#2121](https://github.com/hyperf/hyperf/pull/2121) 在使用 `gen:model` 时，如果用户自定义了与数据库字段一致的字段时，则会替换对应的 `@property`。
- [#2129](https://github.com/hyperf/hyperf/pull/2129) 当 Response Json 格式化失败时，会抛出更加友好的错误提示。

# v2.0.2 - 2020-07-13

## 修复

- [#1898](https://github.com/hyperf/hyperf/pull/1898) 修复定时器规则 `$min-$max` 解析有误的 BUG。
- [#2037](https://github.com/hyperf/hyperf/pull/2037) 修复 TCP 服务，连接后共用一个协程，导致 DB 等连接池无法正常回收连接的 BUG。
- [#2051](https://github.com/hyperf/hyperf/pull/2051) 修复 `CoroutineServer` 不会生成 `hyperf.pid` 的 BUG。
- [#2055](https://github.com/hyperf/hyperf/pull/1695) 修复 `Guzzle` 在传输大数据包时会自动添加头 `Expect: 100-Continue`，导致请求失败的 BUG。
- [#2059](https://github.com/hyperf/hyperf/pull/2059) 修复 `SocketIOServer` 中 `Redis` 重连失败的 BUG。
- [#2067](https://github.com/hyperf/hyperf/pull/2067) 修复 `hyperf/watcher` 组件 `Syntax` 错误会导致进程异常。
- [#2085](https://github.com/hyperf/hyperf/pull/2085) 修复注解 `RetryFalsy` 会导致获得正确的结果后，再次重试。
- [#2089](https://github.com/hyperf/hyperf/pull/2089) 修复使用 `gen:command` 后，脚本必须要进行修改，才能被加载到的 BUG。
- [#2093](https://github.com/hyperf/hyperf/pull/2093) 修复脚本 `vendor:publish` 没有返回码导致报错的 BUG。

## 新增

- [#1860](https://github.com/hyperf/hyperf/pull/1860) 为 `Server` 添加默认的 `OnWorkerExit` 回调。
- [#2042](https://github.com/hyperf/hyperf/pull/2042) 为热更新组件，添加文件扫描驱动。
- [#2054](https://github.com/hyperf/hyperf/pull/2054) 为模型缓存添加 `Eager Load` 功能。

## 优化

- [#2049](https://github.com/hyperf/hyperf/pull/2049) 优化热更新组件的 Stdout 输出。
- [#2090](https://github.com/hyperf/hyperf/pull/2090) 为 `hyperf/session` 组件适配非 `Hyperf` 的 `Response`。

## 变更

- [#2031](https://github.com/hyperf/hyperf/pull/2031) 常量组件的错误码只支持 `int` 和 `string`。
- [#2065](https://github.com/hyperf/hyperf/pull/2065) `WebSocket` 消息发送器 `Hyperf\WebSocketServer\Sender` 支持 `push` 和 `disconnect`。
- [#2100](https://github.com/hyperf/hyperf/pull/2100) 组件 `hyperf/utils` 更新依赖 `doctrine/inflector` 版本到 `^2.0`。

## 移除

- [#2065](https://github.com/hyperf/hyperf/pull/2065) 移除 `Hyperf\WebSocketServer\Sender` 对方法 `send` `sendto` 和 `close` 的支持，请使用 `push` 和 `disconnect`。

# v2.0.1 - 2020-07-02

## 新增

- [#1934](https://github.com/hyperf/hyperf/pull/1934) 增加脚本 `gen:constant` 用于创建常量类。
- [#1982](https://github.com/hyperf/hyperf/pull/1982) 添加热更新组件，文件修改后自动收集注解，自动重启。

## 修复

- [#1952](https://github.com/hyperf/hyperf/pull/1952) 修复数据库迁移类存在时，也会生成同类名类，导致类名冲突的 BUG。
- [#1960](https://github.com/hyperf/hyperf/pull/1960) 修复 `Hyperf\HttpServer\ResponseEmitter::isMethodsExists()` 判断错误的 BUG。
- [#1961](https://github.com/hyperf/hyperf/pull/1961) 修复因文件 `config/autoload/aspects.php` 不存在导致服务无法启动的 BUG。
- [#1964](https://github.com/hyperf/hyperf/pull/1964) 修复接口请求时，数据体为空会导致 `500` 错误的 BUG。
- [#1965](https://github.com/hyperf/hyperf/pull/1965) 修复 `initRequestAndResponse` 失败后，会导致请求状态码与实际不符的 BUG。
- [#1968](https://github.com/hyperf/hyperf/pull/1968) 修复当修改 `aspects.php` 文件后，`Aspect` 无法安装修改后的结果运行的 BUG。
- [#1985](https://github.com/hyperf/hyperf/pull/1985) 修复注解全局配置不全为小写时，会导致 `global_imports` 失败的 BUG。
- [#1990](https://github.com/hyperf/hyperf/pull/1990) 修复当父类存在与子类一样的成员变量时， `@Inject` 无法正常使用的 BUG。
- [#2019](https://github.com/hyperf/hyperf/pull/2019) 修复脚本 `gen:model` 因为使用了 `morphTo` 或 `where` 导致生成对应的 `@property` 失败的 BUG。
- [#2026](https://github.com/hyperf/hyperf/pull/2026) 修复当使用了魔术方法时，LazyLoad 代理生成有误的 BUG。

## 变更

- [#1986](https://github.com/hyperf/hyperf/pull/1986) 当没有设置正确的 `swoole.use_shortname` 变更脚本 `exit_code` 为 `SIGTERM`。

## 优化

- [#1959](https://github.com/hyperf/hyperf/pull/1959) 优化类 `ClassLoader` 可以更容易被用户继承并修改。
- [#2002](https://github.com/hyperf/hyperf/pull/2002) 当 `PHP` 版本大于等于 `7.3` 时，支持 `AOP` 切入 `Trait`。

# v2.0 - 2020-06-22

## 主要功能

1. 重构 [hyperf/di](https://github.com/hyperf/di) 组件，特别是对 AOP 和注解的优化，在 2.0 版本，该组件使用了一个全新的加载机制来提供 AOP 功能的支持。
    1. 对比 1.x 版本来说最显著的一个功能就是现在你可以通过 AOP 功能切入任何方式实例化的一个类了，比如说，在 1.x 版本，你只能切入由 DI 容器创建的类，你无法切入一个由 `new` 关键词实例化的类，但在 2.0 版本都可以生效了。不过仍有一些例外的情况，您仍无法切入那些在启动阶段用来提供 AOP 功能的类；
    2. 在 1.x 版本，AOP 只能作用于普通的类，无法支持 `Final` 类，但在 2.0 版本您可以这么做了；
    3. 在 1.x 版本，您无法在当前类的构造函数中使用 `@Inject` 或 `@Value` 注解标记的类成员属性的值，但在 2.0 版本里，您可以这么做了；
    4. 在 1.x 版本，只有通过 DI 容器创建的对象才能使 `@Inject` 和 `@Value` 注解的功能生效，通过 `new` 关键词创建的对象无法生效，但在 2.0 版本，都可以生效了；
    5. 在 1.x 版本，在使用注解时，您必须定义注解的命名空间来指定使用的注解类，但在 2.0 版本下，您可以为任一注解提供一个别名，这样在使用这个注解时可以直接使用别名而无需引入注解类的命名空间。比如您可以直接在任意类属性上标记 `@Inject` 注解而无需编写 `use Hyperf\Di\Annotation\Inject;`；
    6. 在 1.x 版本，创建的代理类是一个目标类的子类，这样的实现机制会导致一些魔术常量获得的值返回的是代理类子类的信息，而不是目标类的信息，但在 2.0 版本，代理类会与目标类保持一样的类名和代码结构；
    7. 在 1.x 版本，当代理类缓存存在时则不会重新生成缓存，就算源代码发生了变化，这样的机制有助于扫描耗时的提升，但与此同时，这也会导致开发阶段的一些不便利，但在 2.0 版本，代理类缓存会根据源代码的变化而自动变化，这一改变会减少很多在开发阶段的心智负担；
    8. 为 Aspect 类增加了 `priority` 优先级属性，现在您可以组织多个 Aspect 之间的顺序了；
    9. 在 1.x 版本，您只能通过 `@Aspect` 注解类定义一个 Aspect 类，但在 2.0 版本，您还可以通过配置文件、ConfigProvider 来定义 Aspect 类；
    10. 在 1.x 版本，您在使用到依赖懒加载功能时，必须注册一个 `Hyperf\Di\Listener\LazyLoaderBootApplicationListener` 监听器，但在 2.0 版本，您可以直接使用该功能而无需做任何的注册动作；
    11. 增加了 `annotations.scan.class_map` 配置项，通过该配置您可以将任意类替换成您自己的类，而使用时无需做任何的改变；

## 依赖库更新

- 将 `ext-swoole` 升级到了 `>=4.5`;
- 将 `psr/event-dispatcher` 升级到了 `^1.0`;
- 将 `monolog/monolog` 升级到了 `^2.0`;
- 将 `phpstan/phpstan` 升级到了 `^0.12.18`;
- 将 `vlucas/phpdotenv` 升级到了 `^4.0`;
- 将 `symfony/finder` 升级到了 `^5.0`;
- 将 `symfony/event-dispatcher` 升级到了 `^5.0`;
- 将 `symfony/console` 升级到了 `^5.0`;
- 将 `symfony/property-access` 升级到了 `^5.0`;
- 将 `symfony/serializer` 升级到了 `^5.0`;
- 将 `elasticsearch/elasticsearch` 升级到了 `^7.0`;

## 类和方法的变更

- 移除了 `Hyperf\Di\Aop\AstCollector`；
- 移除了 `Hyperf\Di\Aop\ProxyClassNameVisitor`；
- 移除了 `Hyperf\Di\Listener\LazyLoaderBootApplicationListener`；
- 移除了 `Hyperf\Dispatcher\AbstractDispatcher` 类的 `dispatch(...$params)` 方法；
- 移除了 hyperf/utils 组件中 ConfigProvider 中的 `Hyperf\Contract\NormalizerInterface => Hyperf\Utils\Serializer\SymfonyNormalizer` 关系；
- 移除了 `Hyperf\Contract\OnOpenInterface`、`Hyperf\Contract\OnCloseInterface`、`Hyperf\Contract\OnMessageInterface`、`Hyperf\Contract\OnReceiveInterface` 接口中的 `$server` 参数的强类型声明；

## 新增

- [#992](https://github.com/hyperf/hyperf/pull/992) 新增 [hyperf/reactive-x](https://github.com/hyperf/reactive-x) 组件；
- [#1245](https://github.com/hyperf/hyperf/pull/1245) 为 `ExceptionHandler` 新增了注解的定义方式；
- [#1245](https://github.com/hyperf/hyperf/pull/1245) `ExceptionHandler` 新增了 `priority` 优先级属性，通过配置文件或注解方式均可定义优先级；
- [#1819](https://github.com/hyperf/hyperf/pull/1819) 新增 [hyperf/signal](https://github.com/hyperf/signal) 组件；
- [#1844](https://github.com/hyperf/hyperf/pull/1844) 为 [hyperf/model-cache](https://github.com/hyperf/model-cache) 组件中的 `ttl` 属性增加了 `\DateInterval` 类型的支持；
- [#1855](https://github.com/hyperf/hyperf/pull/1855) 连接池新增了 `ConstantFrequency` 恒定频率策略来释放限制的连接；
- [#1871](https://github.com/hyperf/hyperf/pull/1871) 为 Guzzle 增加 `sink` 选项支持；
- [#1805](https://github.com/hyperf/hyperf/pull/1805) 新增 Coroutine Server 协程服务支持；
  - 变更了 `Hyperf\Contract\ProcessInterface` 中的 `bind(Server $server)` 方法声明为 `bind($server)`；
  - 变更了 `Hyperf\Contract\ProcessInterface` 中的 `isEnable()` 方法声明为 `isEnable($server)`；
  - 配置中心、Crontab、服务监控、消息队列消费者现在可以通过协程模式来运行，且在使用协程服务模式时，也必须以协程模式来运行；
  - `Hyperf\AsyncQueue\Environment` 的作用域改为当前协程内，而不是整个进程；
  - 协程模式下不再支持 Task 机制；
- [#1877](https://github.com/hyperf/hyperf/pull/1877) 在 PHP 8 下使用 `@Inject` 注解时支持通过成员属性强类型声明来替代 `@var` 声明，如下所示：

```
class Example {
    /**
     * @Inject
     */
    private ExampleService $exampleService;
}
```

- [#1890](https://github.com/hyperf/hyperf/pull/1890) 新增 `Hyperf\HttpServer\ResponseEmitter` 类来响应任意符合 PSR-7 标准的 Response 对象，同时抽象了 `Hyperf\Contract\ResponseEmitterInterface` 契约；
- [#1890](https://github.com/hyperf/hyperf/pull/1890) 为 `Hyperf\HttpMessage\Server\Response` 类新增了 `getTrailers()` 和 `getTrailer(string $key)` 和 `withTrailer(string $key, $value)` 方法；
- [#1920](https://github.com/hyperf/hyperf/pull/1920) 新增方法 `Hyperf\WebSocketServer\Sender::close(int $fd, bool $reset = null)`.

## 修复

- [#1825](https://github.com/hyperf/hyperf/pull/1825) 修复了 `StartServer::execute` 的 `TypeError`；
- [#1854](https://github.com/hyperf/hyperf/pull/1854) 修复了在 filesystem 中使用 `Runtime::enableCoroutine()` 时，`is_resource` 不能工作的问题；
- [#1900](https://github.com/hyperf/hyperf/pull/1900) 修复了 `Model` 中的 `asDecimal` 方法类型有可能错误的问题；
- [#1917](https://github.com/hyperf/hyperf/pull/1917) 修复了 `Request::isXmlHttpRequest` 方法无法正常工作的问题；

## 变更

- [#705](https://github.com/hyperf/hyperf/pull/705) 统一了 HTTP 异常的处理方式，现在统一抛出一个 `Hyperf\HttpMessage\Exception\HttpException` 依赖类来替代在 `Dispatcher` 中直接响应的方式，同时提供了 `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` 异常处理器来处理该类异常；
- [#1846](https://github.com/hyperf/hyperf/pull/1846) 当您 require 了 `symfony/serializer` 库，不再自动映射 `Hyperf\Contract\NormalizerInterface` 的实现类，您需要手动添加该映射关系，如下：

```php
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

- [#1924](https://github.com/hyperf/hyperf/pull/1924) 重命名 `Hyperf\GrpcClient\BaseClient` 内 `simpleRequest, getGrpcClient, clientStreamRequest` 方法名为 `_simpleRequest, _getGrpcClient, _clientStreamRequest`；

## 移除

- [#1890](https://github.com/hyperf/hyperf/pull/1890) Removed `Hyperf\Contract\Sendable` interface and all implementations of it.
- [#1905](https://github.com/hyperf/hyperf/pull/1905) Removed config `config/server.php`, you can merge it into `config/config.php`.

## 优化

- [#1793](https://github.com/hyperf/hyperf/pull/1793) Socket.io 服务现在只在 onOpen and onClose 中触发 connect/disconnect 事件，同时将一些类方法从 private 级别调整到了 protected 级别，以便用户可以方便的重写这些方法；
- [#1848](https://github.com/hyperf/hyperf/pull/1848) 当 RPC 客户端对应的 Contract 发生变更时，自动重写生成对应的动态代理客户端类；
- [#1863](https://github.com/hyperf/hyperf/pull/1863) 为 async-queue 组件提供更加安全的停止机制；
- [#1896](https://github.com/hyperf/hyperf/pull/1896) 当在 constants 组件中使用了同样的 code 时，keys 会被合并起来；

# v1.1.32 - 2020-05-21

## 修复

- [#1734](https://github.com/hyperf/hyperf/pull/1734) 修复模型多态查询，关联关系为空时，也会查询 SQL 的问题；
- [#1739](https://github.com/hyperf/hyperf/pull/1739) 修复 `hyperf/filesystem` 组件 OSS HOOK 位运算错误，导致 resource 判断不准确的问题；
- [#1743](https://github.com/hyperf/hyperf/pull/1743) 修复 `grafana.json` 中错误的`refId` 字段值；
- [#1748](https://github.com/hyperf/hyperf/pull/1748) 修复 `hyperf/amqp` 组件在使用其他连接池时，对应的 `concurrent.limit` 配置不生效的问题；
- [#1750](https://github.com/hyperf/hyperf/pull/1750) 修复连接池组件，在连接关闭失败时会导致计数有误的问题；
- [#1754](https://github.com/hyperf/hyperf/pull/1754) 修复 BASE Server 服务，启动提示没有考虑 UDP 服务的情况；
- [#1764](https://github.com/hyperf/hyperf/pull/1764) 修复当时间值为 null 时，datatime 验证器执行失败的 BUG；
- [#1769](https://github.com/hyperf/hyperf/pull/1769) 修复 `hyperf/socketio-server` 组件中，客户端初始化断开连接操作时会报 Notice 的错误的问题；

## 新增

- [#1724](https://github.com/hyperf/hyperf/pull/1724) 新增模型方法 `Model::orWhereHasMorph` ,`Model::whereDoesntHaveMorph` and `Model::orWhereDoesntHaveMorph`；
- [#1741](https://github.com/hyperf/hyperf/pull/1741) 新增 `Hyperf\Command\Command::choiceMultiple(): array` 方法，因为 `choice` 方法的返回类型为 `string，所以就算设置了 `$multiple` 参数也无法处理多个选择的情况；
- [#1742](https://github.com/hyperf/hyperf/pull/1742) 新增模型 自定义类型转换器 功能；
  - 新增 interface `Castable`, `CastsAttributes` 和 `CastsInboundAttributes`；
  - 新增方法 `Model\Builder::withCasts`；
  - 新增方法 `Model::loadMorph`, `Model::loadMorphCount` 和 `Model::syncAttributes`；

# v1.1.31 - 2020-05-14

## 新增

- [#1723](https://github.com/hyperf/hyperf/pull/1723) 异常处理器集成了 filp/whoops 。
- [#1730](https://github.com/hyperf/hyperf/pull/1730) 为命令 `gen:model` 可选项 `--refresh-fillable` 添加简写 `-R`。

## 修复

- [#1696](https://github.com/hyperf/hyperf/pull/1696) 修复方法 `Context::copy` 传入字段 `keys` 后无法正常使用的 BUG。
- [#1708](https://github.com/hyperf/hyperf/pull/1708) [#1718](https://github.com/hyperf/hyperf/pull/1718) 修复 `hyperf/socketio-server` 组件内存溢出等 BUG。

## 优化

- [#1710](https://github.com/hyperf/hyperf/pull/1710) MAC 系统下不再使用 `cli_set_process_title` 方法设置进程名。

# v1.1.30 - 2020-05-07

## 新增

- [#1616](https://github.com/hyperf/hyperf/pull/1616) 新增 ORM 方法 `morphWith` 和 `whereHasMorph`。
- [#1651](https://github.com/hyperf/hyperf/pull/1651) 新增 `socket.io-server` 组件。
- [#1666](https://github.com/hyperf/hyperf/pull/1666) [#1669](https://github.com/hyperf/hyperf/pull/1669) 新增 AMQP RPC 客户端。

## 修复

- [#1682](https://github.com/hyperf/hyperf/pull/1682) 修复 `RpcPoolTransporter` 的连接池配置不生效的 BUG。
- [#1683](https://github.com/hyperf/hyperf/pull/1683) 修复 `RpcConnection` 连接失败后，相同协程内无法正常重置连接的 BUG。

## 优化

- [#1670](https://github.com/hyperf/hyperf/pull/1670) 优化掉 `Cache 组件` 一条无意义的删除指令。

# v1.1.28 - 2020-04-30

## 新增

- [#1645](https://github.com/hyperf/hyperf/pull/1645) 匿名函数路由支持参数注入。
- [#1647](https://github.com/hyperf/hyperf/pull/1647) 为 `model-cache` 组件添加 `RedisStringHandler`。
- [#1654](https://github.com/hyperf/hyperf/pull/1654) 新增 `RenderException` 统一捕获 `view` 组件抛出的异常。

## 修复

- [#1639](https://github.com/hyperf/hyperf/pull/1639) 修复 `rpc-client` 会从 `consul` 中获取到不健康节点的 BUG。
- [#1641](https://github.com/hyperf/hyperf/pull/1641) 修复 `rpc-client` 获取到的结果为 `null` 时，会抛出 `RequestException` 的 BUG。
- [#1641](https://github.com/hyperf/hyperf/pull/1641) 修复 `rpc-server` 中 `jsonrpc-tcp-length-check` 协议，无法在 `consul` 中添加心跳检查的 BUG。
- [#1650](https://github.com/hyperf/hyperf/pull/1650) 修复脚本 `describe:routes` 列表展示有误的 BUG。
- [#1655](https://github.com/hyperf/hyperf/pull/1655) 修复 `MysqlProcessor::processColumns` 无法在 `MySQL Server 8.0` 版本中正常工作的 BUG。

## 优化

- [#1636](https://github.com/hyperf/hyperf/pull/1636) 优化 `co-phpunit` 脚本，当出现 `case` 验证失败后，协程也可以正常结束。


# v1.1.27 - 2020-04-23

## 新增

- [#1575](https://github.com/hyperf/hyperf/pull/1575) 为脚本 `gen:model` 生成的模型，自动添加 `relation` `scope` 和 `attributes` 的变量注释。
- [#1586](https://github.com/hyperf/hyperf/pull/1586) 添加 `symfony/event-dispatcher` 组件小于 `4.3` 时的 `conflict` 配置。用于解决用户使用了 `4.3` 以下版本时，导致 `SymfonyDispatcher` 实现冲突的 BUG。
- [#1597](https://github.com/hyperf/hyperf/pull/1597) 为 `AMQP` 消费者，添加最大消费次数 `maxConsumption`。
- [#1603](https://github.com/hyperf/hyperf/pull/1603) 为 `WebSocket` 服务添加基于 `fd` 存储的 `Context`。

## 修复

- [#1553](https://github.com/hyperf/hyperf/pull/1553) 修复 `jsonrpc` 服务，发布了相同名字不同协议到 `consul` 后，客户端无法正常工作的 BUG。
- [#1589](https://github.com/hyperf/hyperf/pull/1589) 修复了文件锁在协程下可能会造成死锁的 BUG。
- [#1607](https://github.com/hyperf/hyperf/pull/1607) 修复了重写后的 `go` 方法，返回值与 `swoole` 原生方法不符的 BUG。
- [#1624](https://github.com/hyperf/hyperf/pull/1624) 修复当路由 `Handler` 是匿名函数时，脚本 `describe:routes` 执行失败的 BUG。

# v1.1.26 - 2020-04-16

## 新增

- [#1578](https://github.com/hyperf/hyperf/pull/1578) `UploadedFile` 支持 `getStream` 方法。

## 修复

- [#1563](https://github.com/hyperf/hyperf/pull/1563) 修复服务关停后，定时器的 `onOneServer` 配置不会被重置。
- [#1565](https://github.com/hyperf/hyperf/pull/1565) 当 `DB` 组件重连 `Mysql` 时，重置事务等级为 0。
- [#1572](https://github.com/hyperf/hyperf/pull/1572) 修复 `Hyperf\GrpcServer\CoreMiddleware` 中，自定义类的父类找不到时报错的 BUG。
- [#1577](https://github.com/hyperf/hyperf/pull/1577) 修复 `describe:routes` 脚本 `server` 配置不生效的 BUG。
- [#1579](https://github.com/hyperf/hyperf/pull/1579) 修复 `migrate:refresh` 脚本 `step` 参数不为 `int` 时会报错的 BUG。

## 变更

- [#1560](https://github.com/hyperf/hyperf/pull/1560) 修改 `hyperf/cache` 组件文件缓存引擎中 原生的文件操作为 `Filesystem`。
- [#1568](https://github.com/hyperf/hyperf/pull/1568) 修改 `hyperf/async-queue` 组件 `Redis` 引擎中的 `\Redis` 为 `RedisProxy`。

# v1.1.25 - 2020-04-09

## 修复

- [#1532](https://github.com/hyperf/hyperf/pull/1532) 修复 'Symfony\Component\EventDispatcher\EventDispatcherInterface' 在 --no-dev 条件下安装会出现找不到接口的问题；


# v1.1.24 - 2020-04-09

## 新增

- [#1501](https://github.com/hyperf/hyperf/pull/1501) 添加 `Symfony` 命令行事件触发器，使之可以与 `hyperf/event` 组件结合使用；
- [#1502](https://github.com/hyperf/hyperf/pull/1502) 为注解 `Hyperf\AsyncQueue\Annotation\AsyncQueueMessage` 添加 `maxAttempts` 参数，用于控制消息失败时重复消费的次数；
- [#1510](https://github.com/hyperf/hyperf/pull/1510) 添加 `Hyperf/Utils/CoordinatorManager`，用于提供更优雅的启动和停止服务，服务启动前不响应请求，服务停止前，保证某些循环逻辑能够正常结束；
- [#1517](https://github.com/hyperf/hyperf/pull/1517) 为依赖注入容器的懒加载功能添加了对接口继承和抽象方法继承的支持；
- [#1529](https://github.com/hyperf/hyperf/pull/1529) 处理 `response cookies` 中的 `SameSite` 属性；

## 修复

- [#1494](https://github.com/hyperf/hyperf/pull/1494) 修复单独使用 `Redis` 组件时，注释 `@mixin` 会被当成注解的 BUG；
- [#1499](https://github.com/hyperf/hyperf/pull/1499) 修复引入 `hyperf/translation` 组件后，`hyperf/constants` 组件的动态参数不生效的 BUG；
- [#1504](https://github.com/hyperf/hyperf/pull/1504) 修复 `RPC` 代理客户端无法正常处理返回值为 `nullable` 类型的方法；
- [#1507](https://github.com/hyperf/hyperf/pull/1507) 修复 `hyperf/consul` 组件的 `catalog` 注册方法调用会失败的 BUG；

# v1.1.23 - 2020-04-02

## 新增

- [#1467](https://github.com/hyperf/hyperf/pull/1467) 为 `filesystem` 组件添加默认配置；
- [#1469](https://github.com/hyperf/hyperf/pull/1469) 为 `Hyperf/Guzzle/HandlerStackFactory` 添加 `getHandler()` 方法，并尽可能的使用 `make()` 创建 `handler`；
- [#1480](https://github.com/hyperf/hyperf/pull/1480) RPC client 现在会自动代理父接口的方法定义；

## 变更

- [#1481](https://github.com/hyperf/hyperf/pull/1481) 异步队列创建消息时，使用 `make` 方法创建；

## 修复

- [#1471](https://github.com/hyperf/hyperf/pull/1471) 修复 `NSQ` 组件，数据量超过 `max-output-buffer-size` 接收数据失败的 `BUG`；
- [#1472](https://github.com/hyperf/hyperf/pull/1472) 修复 `NSQ` 组件，在消费者中发布消息时，会导致消费者无法正常消费的 `BUG`；
- [#1474](https://github.com/hyperf/hyperf/pull/1474) 修复 `NSQ` 组件，`requeue` 消息时，消费者会意外重启的 `BUG`；
- [#1477](https://github.com/hyperf/hyperf/pull/1477) 修复使用 `Hyperf\Testing\Client::flushContext` 时，会引发 `Fixed Invalid argument supplied` 异常的 `BUG`；

# v1.1.22 - 2020-03-26

## 新增

- [#1440](https://github.com/hyperf/hyperf/pull/1440) 为 NSQ 的每个连接新增 `enable` 配置项来控制连接下的所有消费者的自启功能；
- [#1451](https://github.com/hyperf/hyperf/pull/1451) 新增 Filesystem 组件；
- [#1459](https://github.com/hyperf/hyperf/pull/1459) 模型 Collection 新增 macroable 支持；
- [#1463](https://github.com/hyperf/hyperf/pull/1463) 为 Guzzle Handler 增加 `on_stats` 选项的功能支持；

## 变更

- [#1452](https://github.com/hyperf/hyperf/pull/1452) 在注入 Redis 客户端时，推荐使用 `\Hyperf\Redis\Redis` 来替代 `\Redis`，原因在 [#938](https://github.com/hyperf/hyperf/issues/938)；

## 修复

- [#1445](https://github.com/hyperf/hyperf/pull/1445) 修复命令 `describe:routes` 缺失了带参数的路由；
- [#1449](https://github.com/hyperf/hyperf/pull/1449) 修复了高基数请求路径的内存溢出的问题；
- [#1454](https://github.com/hyperf/hyperf/pull/1454) 修复 Collection 的 `flatten()` 方法因为 `INF` 参数值为 `float` 类型导致无法使用的问题；
- [#1458](https://github.com/hyperf/hyperf/pull/1458) 修复了 Guzzle 不支持 Elasticsearch 版本大于 7.0 的问题；

# v1.1.21 - 2020-03-19

## 新增

- [#1393](https://github.com/hyperf/hyperf/pull/1393) 为 `Hyperf\HttpMessage\Stream\SwooleStream` 实现更多的方法；
- [#1419](https://github.com/hyperf/hyperf/pull/1419) 允许 ConfigFetcher 通过一个协程启动而无需额外启动一个进程；
- [#1424](https://github.com/hyperf/hyperf/pull/1424) 允许用户通过配置文件的形式修改 `session_name` 配置；
- [#1435](https://github.com/hyperf/hyperf/pull/1435) 为模型缓存增加 `use_default_value` 属性来自动修正缓存数据与数据库数据之间的差异；
- [#1436](https://github.com/hyperf/hyperf/pull/1436) 为 NSQ 消费者增加 `isEnable()` 方法来控制消费者进程是否启用自启功能；

# v1.1.20 - 2020-03-12

## 新增

- [#1402](https://github.com/hyperf/hyperf/pull/1402) 增加 `Hyperf\DbConnection\Annotation\Transactional` 注解来自动开启一个事务；
- [#1412](https://github.com/hyperf/hyperf/pull/1412) 增加 `Hyperf\View\RenderInterface::getContents()` 方法来直接获取 View Render 的渲染内容；
- [#1416](https://github.com/hyperf/hyperf/pull/1416) 增加 Swoole 事件常量 `ON_WORKER_ERROR`.

## 修复

- [#1405](https://github.com/hyperf/hyperf/pull/1405) 修复当模型存在 `hidden` 属性时，模型缓存功能缓存的字段数据不正确的问题；
- [#1410](https://github.com/hyperf/hyperf/pull/1410) 修复 Tracer 无法追踪由 `Hyperf\Redis\RedisFactory` 创建的连接的调用链；
- [#1415](https://github.com/hyperf/hyperf/pull/1415) 修复阿里 ACM 客户端在当 `SecurityToken` Header 为空时 sts token 会解密失败的问题；


# v1.1.19 - 2020-03-05

## 新增

- [#1339](https://github.com/hyperf/hyperf/pull/1339) [#1394](https://github.com/hyperf/hyperf/pull/1394) 新增 `describe:routes` 命令来显示路由的细节信息；
- [#1354](https://github.com/hyperf/hyperf/pull/1354) 为  `config-aliyun-acm` 组件新增 ecs ram authorization；
- [#1362](https://github.com/hyperf/hyperf/pull/1362) 为 `Hyperf\Pool\SimplePool\PoolFactory` 增加 `getPoolNames()` 来获取连接池的名称；
- [#1371](https://github.com/hyperf/hyperf/pull/1371) 新增 `Hyperf\DB\DB::connection()` 方法来指定要使用的连接；
- [#1384](https://github.com/hyperf/hyperf/pull/1384) 为 `gen:model` 命令新增  `property-case` 选项来设定成员属性的命名风格；

## 修复

- [#1386](https://github.com/hyperf/hyperf/pull/1386) 修复异步消息投递注解当用在存在可变参数的方法上失效的问题；

# v1.1.18 - 2020-02-27

## 新增

- [#1305](https://github.com/hyperf/hyperf/pull/1305) 为 `hyperf\metric` 组件添加预制的 `Grafana` 面板；
- [#1328](https://github.com/hyperf/hyperf/pull/1328) 添加 `ModelRewriteInheritanceVisitor` 来重写 model 类继承的 `gen:model` 命令；
- [#1331](https://github.com/hyperf/hyperf/pull/1331) 添加 `Hyperf\LoadBalancer\LoadBalancerInterface::getNodes()`；
- [#1335](https://github.com/hyperf/hyperf/pull/1335) 为 `command` 添加 `AfterExecute` 事件；
- [#1361](https://github.com/hyperf/hyperf/pull/1361) logger 组件添加 `processors` 配置；

## 修复

- [#1330](https://github.com/hyperf/hyperf/pull/1330) 修复当使用 `(new Parallel())->add($callback, $key)` 并且参数 `$key` 并非 string 类型, 返回结果将会从 0 开始排序 `$key`；
- [#1338](https://github.com/hyperf/hyperf/pull/1338) 修复当从 server 设置自己的设置时, 主 server 的配置不生效的 bug；
- [#1344](https://github.com/hyperf/hyperf/pull/1344) 修复队列在没有设置最大消息数时每次都需要校验长度的 bug；

## 变更

- [#1324](https://github.com/hyperf/hyperf/pull/1324) [hyperf/async-queue](https://github.com/hyperf/async-queue) 组件不再提供默认启用 `Hyperf\AsyncQueue\Listener\QueueLengthListener`；

## 优化

- [#1305](https://github.com/hyperf/hyperf/pull/1305) 优化 `hyperf\metric` 中的边界条件；
- [#1322](https://github.com/hyperf/hyperf/pull/1322) HTTP Server 自动处理 HEAD 请求并且不会在 HEAD 请求时返回 Response body；

## 删除

- [#1303](https://github.com/hyperf/hyperf/pull/1303) 删除 `Hyperf\RpcServer\Router\Router` 中无用的 `$httpMethod`；

# v1.1.17 - 2020-01-24

## 新增

- [#1220](https://github.com/hyperf/hyperf/pull/1220) 为 Apollo 组件增加 BootProcessListener 来实现在服务启动时从 Apollo 拉取配置的功能；
- [#1292](https://github.com/hyperf/hyperf/pull/1292) 为 `Hyperf\Database\Schema\Blueprint::foreign()` 方法的返回类型增加了 `Hyperf\Database\Schema\ForeignKeyDefinition` 类型；
- [#1313](https://github.com/hyperf/hyperf/pull/1313) 为 `hyperf\crontab` 组件增加了 Command 模式支持；
- [#1321](https://github.com/hyperf/hyperf/pull/1321) 增加 [hyperf/nsq](https://github.com/hyperf/nsq) 组件，[NSQ](https://nsq.io) 是一个实时的分布式消息平台；

## 修复

- [#1291](https://github.com/hyperf/hyperf/pull/1291) 修复 [hyperf/super-globals](https://github.com/hyperf/super-globals) 组件的 `$_SERVER` 存在小写键值与 PHP-FPM 不统一的问题；
- [#1308](https://github.com/hyperf/hyperf/pull/1308) 修复 [hyperf/validation](https://github.com/hyperf/validation) 组件缺失的一些翻译内容, 包括 gt, gte, ipv4, ipv6, lt, lte, mimetypes, not_regex, starts_with, uuid；
- [#1310](https://github.com/hyperf/hyperf/pull/1310) 修复服务注册在当服务同名不同协议的情况下会被覆盖的问题；
- [#1315](https://github.com/hyperf/hyperf/pull/1315) 修复 `Hyperf\AsyncQueue\Process\ConsumerProcess` 类缺失的 $config 变量；

# v1.1.16 - 2020-01-16

## 新增

- [#1263](https://github.com/hyperf/hyperf/pull/1263) 为 async-queue 组件增加 `QueueLength` 事件；
- [#1276](https://github.com/hyperf/hyperf/pull/1276) 为 Consul 客户端增加 ACL token 支持；
- [#1277](https://github.com/hyperf/hyperf/pull/1277) 为 [hyperf/metric](https://github.com/hyperf/metric) 组件增加 NoOp 驱动，用来临时关闭 metric 功能；

## 修复

- [#1262](https://github.com/hyperf/hyperf/pull/1262) 修复 keepaliveIO 功能下 socket 会被消耗光的问题；
- [#1266](https://github.com/hyperf/hyperf/pull/1266) 修复当自定义进程存在 Timer 的情况下会无法重启的问题；
- [#1272](https://github.com/hyperf/hyperf/pull/1272) 修复 JSONRPC 下当 Request ID 为 null 时检查会失败的问题；

## 优化

- [#1273](https://github.com/hyperf/hyperf/pull/1273) 优化 gRPC 客户端：
  - 优化使 gRPC 客户端在当连接与 Server 断开时会自动重连；
  - 优化使当 gRPC 客户端被垃圾回收时，已建立的连接会自动关闭；
  - 修复关闭了的客户端依旧会持有 HTTP2 连接的问题；
  - 修复 gRPC 客户端的 channel pool 可能会存在非空 channel 的问题；
  - 优化使 gRPC 客户端会自动初始化，所以现在可以在构造函数和容器注入下使用；

## 删除

- [#1286](https://github.com/hyperf/hyperf/pull/1286) 从 require-dev 中移除 [phpstan/phpstan](https://github.com/phpstan/phpstan) 包的依赖。

# v1.1.15 - 2020-01-10

## 修复

- [#1258](https://github.com/hyperf/hyperf/pull/1258) 修复 AMQP 发送心跳失败，会导致子进程 Socket 通信不可用的问题；
- [#1260](https://github.com/hyperf/hyperf/pull/1260) 修复 JSONRPC 在同一协程内，连接会混淆复用的问题；

# v1.1.14 - 2020-01-10

## 新增

- [#1166](https://github.com/hyperf/hyperf/pull/1166) 为 AMQP 增加 KeepaliveIO 功能；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 为 JSON-RPC 的响应增加了 `error.data.code` 值来传递 Exception Code；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 为 `Hyperf\Rpc\Contract\TransporterInterface` 增加了 `recv` 方法；
- [#1215](https://github.com/hyperf/hyperf/pull/1215) 新增 [hyperf/super-globals](https://github.com/hyperf/super-globals) 组件，用来适配一些不支持 PSR-7 的第三方包；
- [#1219](https://github.com/hyperf/hyperf/pull/1219) 为 AMQP 消费者增加 `enable` 属性，通过该属性来控制该消费者是否跟随 Server 一同启动；

## 修复

- [#1208](https://github.com/hyperf/hyperf/pull/1208) 修复 Exception 和 error 在 JSON-RPC TCP Server 下无法被正确处理的问题；
- [#1208](https://github.com/hyperf/hyperf/pull/1208) 修复 JSON-RPC 没有检查 Request ID 和 Response ID 是否一致的问题；
- [#1223](https://github.com/hyperf/hyperf/pull/1223) 修复 ConfigProvider 扫描器不会扫描 composer.json 内 require-dev 的配置；
- [#1254](https://github.com/hyperf/hyperf/pull/1254) 修复执行 `init-proxy.sh` 命令在某些环境如 Alpine 下会报 bash 不存在的问题；

## 优化

- [#1208](https://github.com/hyperf/hyperf/pull/1208) 优化了 JSON-RPC 组件的部分逻辑；
- [#1174](https://github.com/hyperf/hyperf/pull/1174) 调整了 `Hyperf\Utils\Parallel` 在输出异常时的格式，现在会一同打印 Trace 信息；
- [#1224](https://github.com/hyperf/hyperf/pull/1224) 允许 Aliyun ACM 配置中心的配置获取进程解析 UTF-8 字符，同时在 Worker 启动后会自动获取一次配置，以及拉取的配置现在会传递到自定义进程了；
- [#1235](https://github.com/hyperf/hyperf/pull/1235) 在 AMQP 生产者执行 declare 后释放对应的连接；

## 修改

- [#1227](https://github.com/hyperf/hyperf/pull/1227) 升级 `jcchavezs/zipkin-php-opentracing` 依赖至 0.1.4 版本；

# v1.1.13 - 2020-01-03

## 新增

- [#1137](https://github.com/hyperf/hyperf/pull/1137) `constants` 组件增加国际化支持；
- [#1165](https://github.com/hyperf/hyperf/pull/1165) `Hyperf\HttpServer\Contract\RequestInterface` 新增 `route` 方法；
- [#1195](https://github.com/hyperf/hyperf/pull/1195) 注解 `Cacheable` 和 `CachePut` 增加最大超时时间偏移量配置；
- [#1204](https://github.com/hyperf/hyperf/pull/1204) `database` 组件增加了 `insertOrIgnore` 方法；
- [#1216](https://github.com/hyperf/hyperf/pull/1216) `RenderInterface::render()` 方法的 `$data` 参数，添加了默认值；
- [#1221](https://github.com/hyperf/hyperf/pull/1221) `swoole-tracker` 组件添加了 `traceId` 和 `spanId`；

## 修复

- [#1175](https://github.com/hyperf/hyperf/pull/1175) 修复 `Hyperf\Utils\Collection::random` 当传入 `null` 时，无法正常工作的 `BUG`；
- [#1199](https://github.com/hyperf/hyperf/pull/1199) 修复使用 `Task` 注解时，参数无法使用动态变量的 `BUG`；
- [#1200](https://github.com/hyperf/hyperf/pull/1200) 修复 `metric` 组件，请求路径会携带参数的 `BUG`；
- [#1210](https://github.com/hyperf/hyperf/pull/1210) 修复验证器规则 `size` 无法作用于 `integer` 的 `BUG`；

## 优化

- [#1211](https://github.com/hyperf/hyperf/pull/1211) 自动将项目名转化为 `prometheus` 的规范命名；

## 修改

- [#1217](https://github.com/hyperf/hyperf/pull/1217) 将 `zendframework/zend-mime` 替换为 `laminas/laminas-mine`；

# v1.1.12 - 2019-12-26

## 新增

- [#1177](https://github.com/hyperf/hyperf/pull/1177) 为 `jsonrpc` 组件增加了新的协议 `jsonrpc-tcp-length-check`，并对部分代码进行了优化；

## 修复

- [#1175](https://github.com/hyperf/hyperf/pull/1175) 修复 `Hyperf\Utils\Collection::random` 方法不支持传入 `null`；
- [#1178](https://github.com/hyperf/hyperf/pull/1178) 修复 `Hyperf\Database\Query\Builder::chunkById` 方法不支持元素是 `array` 的情况；
- [#1189](https://github.com/hyperf/hyperf/pull/1189) 修复 `Hyperf\Utils\Collection::operatorForWhere` 方法，`operator` 只能传入 `string` 的 BUG；

## 优化

- [#1186](https://github.com/hyperf/hyperf/pull/1186) 日志配置中，只填写 `formatter.class` 的情况下，可以使用默认的 `formatter.constructor` 配置；

# v1.1.11 - 2019-12-19

## 新增

- [#849](https://github.com/hyperf/hyperf/pull/849) 为 hyperf/tracer 组件增加 span tag 配置功能；

## 修复

- [#1142](https://github.com/hyperf/hyperf/pull/1142) 修复 `Register::resolveConnection` 会返回 null 的问题；
- [#1144](https://github.com/hyperf/hyperf/pull/1144) 修复配置文件形式下服务限流会失效的问题；
- [#1145](https://github.com/hyperf/hyperf/pull/1145) 修复 `CoroutineMemoryDriver::delKey` 方法的返回值错误的问题；
- [#1153](https://github.com/hyperf/hyperf/pull/1153) 修复验证器的 `alpha_num` 规则无法按预期运行的问题；

# v1.1.10 - 2019-12-12

## 修复

- [#1104](https://github.com/hyperf/hyperf/pull/1104) 修复了 Guzzle 客户端的重试中间件的状态码识别范围为 2xx；
- [#1105](https://github.com/hyperf/hyperf/pull/1105) 修复了 Retry 组件在重试尝试前不还原管道堆栈的问题；
- [#1106](https://github.com/hyperf/hyperf/pull/1106) 修复了数据库在开启 `sticky` 模式时连接回归连接池时没有重置状态的问题；
- [#1119](https://github.com/hyperf/hyperf/pull/1119) 修复 TCP 协议下的 JSONRPC Server 在解析 JSON 失败时无法正确的返回预期的 Error Response 的问题；
- [#1124](https://github.com/hyperf/hyperf/pull/1124) 修复 Session 中间件在储存当前的 URL 时，当 URL 以 `/` 结尾时会忽略斜杠的问题；

## 变更

- [#1108](https://github.com/hyperf/hyperf/pull/1108) 重命名 `Hyperf\Tracer\Middleware\TraceMiddeware` 为 `Hyperf\Tracer\Middleware\TraceMiddleware`；
- [#1108](https://github.com/hyperf/hyperf/pull/1111) 升级 `Hyperf\ServiceGovernance\Listener\ServiceRegisterListener` 类的成员属性和方法的等级为 `protected`，以便更好的重写相关方法；

# v1.1.9 - 2019-12-05

## 新增

- [#948](https://github.com/hyperf/hyperf/pull/948) 为 DI Container 增加懒加载功能；
- [#1044](https://github.com/hyperf/hyperf/pull/1044) 为 AMQP Consumer 增加 `basic_qos` 配置；
- [#1056](https://github.com/hyperf/hyperf/pull/1056) [#1081](https://github.com/hyperf/hyperf/pull/1081) DI Container 增加 `define()` 和 `set()` 方法，同时增加 `Hyperf\Contract\ContainerInterface`；
- [#1059](https://github.com/hyperf/hyperf/pull/1059) `job.stub` 模板增加构造函数；
- [#1084](https://github.com/hyperf/hyperf/pull/1084) 支持 PHP 7.4，TrvisCI 增加 PHP 7.4 运行支持；

## 修复

- [#1007](https://github.com/hyperf/hyperf/pull/1007) 修复 `vendor:: publish` 的命令返回值；
- [#1049](https://github.com/hyperf/hyperf/pull/1049) 修复 `Hyperf\Cache\Driver\RedisDriver::clear` 会有可能删除所有缓存失败的问题；
- [#1055](https://github.com/hyperf/hyperf/pull/1055) 修复 Image 验证时后缀大小写的问题；
- [#1085](https://github.com/hyperf/hyperf/pull/1085) [#1091](https://github.com/hyperf/hyperf/pull/1091) Fixed `@Retry` 注解使用时会找不到容器的问题；

# v1.1.8 - 2019-11-28

## 新增

- [#965](https://github.com/hyperf/hyperf/pull/965) 新增 Redis Lua 模块，用于管理 Lua 脚本；
- [#1023](https://github.com/hyperf/hyperf/pull/1023) hyperf/metric 组件的 Prometheus 驱动新增 CUSTOM_MODE 模式；

## 修复

- [#1013](https://github.com/hyperf/hyperf/pull/1013) 修复 JsonRpcPoolTransporter 配置合并失败的问题；
- [#1006](https://github.com/hyperf/hyperf/pull/1006) 修复 `gen:model` 命令生成的属性的顺序；

## 变更

- [#1021](https://github.com/hyperf/hyperf/pull/1012) WebSocket 客户端新增默认端口支持，根据协议默认为 80 和 443；
- [#1034](https://github.com/hyperf/hyperf/pull/1034) 去掉了 `Hyperf\Amqp\Builder\Builder` 的 `arguments` 参数的 array 类型限制，允许接受其他类型如 AmqpTable；

## 优化

- [#1014](https://github.com/hyperf/hyperf/pull/1014) 优化 `Command::execute` 的返回值类型；
- [#1022](https://github.com/hyperf/hyperf/pull/1022) 提供更清晰友好的连接池报错信息；
- [#1039](https://github.com/hyperf/hyperf/pull/1039) 在 CoreMiddleware 中自动设置最新的 ServerRequest 对象到 Context；

# v1.1.7 - 2019-11-21

## 新增

- [#860](https://github.com/hyperf/hyperf/pull/860) 新增 [hyperf/retry](https://github.com/hyperf/retry) 组件；
- [#952](https://github.com/hyperf/hyperf/pull/952) 新增 ThinkTemplate 视图引擎支持；
- [#973](https://github.com/hyperf/hyperf/pull/973) 新增 JSON RPC 在 TCP 协议下的连接池支持，通过 `Hyperf\JsonRpc\JsonRpcPoolTransporter` 来使用连接池版本；
- [#976](https://github.com/hyperf/hyperf/pull/976) 为 `hyperf/amqp` 组件新增  `close_on_destruct` 选项参数，用来控制代码在执行析构函数时是否主动去关闭连接；

## 变更

- [#944](https://github.com/hyperf/hyperf/pull/944) 将组件内所有使用 `@Listener` 和 `@Process` 注解来注册的改成通过 `ConfigProvider`来注册；
- [#977](https://github.com/hyperf/hyperf/pull/977) 调整 `init-proxy.sh` 命令的行为，改成只删除 `runtime/container` 目录；

## 修复

- [#955](https://github.com/hyperf/hyperf/pull/955) 修复 `hyperf/db` 组件的 `port` 和 `charset` 参数无效的问题；
- [#956](https://github.com/hyperf/hyperf/pull/956) 修复模型缓存中使用到`RedisHandler::incr` 在集群模式下会失败的问题；
- [#966](https://github.com/hyperf/hyperf/pull/966) 修复当在非 Worker 进程环境下使用分页器会报错的问题；
- [#968](https://github.com/hyperf/hyperf/pull/968) 修复当 `classes` 和 `annotations` 两种 Aspect 切入模式同时存在于一个类时，其中一个可能会失效的问题；
- [#980](https://github.com/hyperf/hyperf/pull/980) 修复 Session 组件内 `migrate`, `save` 核 `has` 方法无法使用的问题；
- [#982](https://github.com/hyperf/hyperf/pull/982) 修复 `Hyperf\GrpcClient\GrpcClient::yield` 在获取 Channel Pool 时没有通过正确的获取方式去获取的问题；
- [#987](https://github.com/hyperf/hyperf/pull/987) 修复通过 `gen:command` 命令生成的命令类缺少调用 `parent::configure()` 方法的问题；

## 优化

- [#991](https://github.com/hyperf/hyperf/pull/991) 优化 `Hyperf\DbConnection\ConnectionResolver::connection`的异常情况处理；

# v1.1.6 - 2019-11-14

## 新增

- [#827](https://github.com/hyperf/hyperf/pull/827) 新增了极简的高性能的 DB 组件；
- [#905](https://github.com/hyperf/hyperf/pull/905) 视图组件增加了 `twig` 模板引擎；
- [#911](https://github.com/hyperf/hyperf/pull/911) 定时任务支持多实例情况下，只运行单一实例的定时任务；
- [#913](https://github.com/hyperf/hyperf/pull/913) 增加监听器 `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`；
- [#921](https://github.com/hyperf/hyperf/pull/921) 新增 `Session` 组件；
- [#931](https://github.com/hyperf/hyperf/pull/931) 阿波罗配置中心增加 `strict_mode`，自动将配置转化成对应数据类型；
- [#933](https://github.com/hyperf/hyperf/pull/933) 视图组件增加了 `plates` 模板引擎；
- [#937](https://github.com/hyperf/hyperf/pull/937) Nats 组件添加消费者消费和订阅事件；
- [#941](https://github.com/hyperf/hyperf/pull/941) 新增 `Zookeeper` 配置中心；

## 变更

- [#934](https://github.com/hyperf/hyperf/pull/934) 修改 `WaitGroup` 继承 `\Swoole\Coroutine\WaitGroup`；

## 修复

- [#897](https://github.com/hyperf/hyperf/pull/897) 修复 `Nats` 消费者，`pool` 配置无效的 BUG；
- [#901](https://github.com/hyperf/hyperf/pull/901) 修复 `GraphQL` 组件，`Factory` 注解无法正常使用的 BUG；
- [#903](https://github.com/hyperf/hyperf/pull/903) 修复添加 `hyperf/rpc-client` 依赖后，`init-proxy` 脚本无法正常停止的 BUG；
- [#904](https://github.com/hyperf/hyperf/pull/904) 修复监听器监听 `Hyperf\Framework\Event\BeforeMainServerStart` 事件时，无法使用 `IO` 操作的 BUG；
- [#906](https://github.com/hyperf/hyperf/pull/906) 修复 `Hyperf\HttpMessage\Server\Request` 端口获取有误的 BUG；
- [#907](https://github.com/hyperf/hyperf/pull/907) 修复 `Nats` 组件 `requestSync` 方法，超时时间不准确的 BUG；
- [#909](https://github.com/hyperf/hyperf/pull/909) 修复 `Parallel` 内逻辑抛错后，无法正常停止的 BUG；
- [#925](https://github.com/hyperf/hyperf/pull/925) 修复因 `Socket` 无法正常建立，导致进程频繁重启的 BUG；
- [#932](https://github.com/hyperf/hyperf/pull/932) 修复 `Translator::setLocale` 在协程环境下，数据混淆的 BUG；
- [#940](https://github.com/hyperf/hyperf/pull/940) 修复 `WebSocketClient::push` 方法 `finish` 参数类型错误；

## 优化

- [#907](https://github.com/hyperf/hyperf/pull/907) 优化 `Nats` 消费者频繁重启；
- [#928](https://github.com/hyperf/hyperf/pull/928) `Hyperf\ModelCache\Cacheable::query` 批量修改数据时，可以删除对应缓存；
- [#936](https://github.com/hyperf/hyperf/pull/936) 优化调用模型缓存 `increment` 时，可能因并发情况导致的数据有错；

# v1.1.5 - 2019-11-07

## 新增

- [#812](https://github.com/hyperf/hyperf/pull/812) 新增计划任务在集群下仅执行一次的支持；
- [#820](https://github.com/hyperf/hyperf/pull/820) 新增 hyperf/nats 组件；
- [#832](https://github.com/hyperf/hyperf/pull/832) 新增 `Hyperf\Utils\Codec\Json`；
- [#833](https://github.com/hyperf/hyperf/pull/833) 新增 `Hyperf\Utils\Backoff`；
- [#852](https://github.com/hyperf/hyperf/pull/852) 为 `Hyperf\Utils\Parallel` 新增 `clear()` 方法来清理所有已添加的回调；
- [#854](https://github.com/hyperf/hyperf/pull/854) 新增 `Hyperf\GraphQL\GraphQLMiddleware` 用于解析 GraphQL 请求；
- [#859](https://github.com/hyperf/hyperf/pull/859) 新增 Consul 集群的支持，现在可以从 Consul 集群中拉取服务提供者的节点信息；
- [#873](https://github.com/hyperf/hyperf/pull/873) 新增 Redis 集群的客户端支持；

## 修复

- [#831](https://github.com/hyperf/hyperf/pull/831) 修复 Redis 客户端连接在 Redis Server 重启后不会自动重连的问题；
- [#835](https://github.com/hyperf/hyperf/pull/835) 修复 `Request::inputs` 方法的默认值参数与预期效果不一致的问题；
- [#841](https://github.com/hyperf/hyperf/pull/841) 修复数据库迁移在多数据库的情况下连接无效的问题；
- [#844](https://github.com/hyperf/hyperf/pull/844) 修复 Composer 阅读器不支持根命名空间的用法的问题；
- [#846](https://github.com/hyperf/hyperf/pull/846) 修复 Redis 客户端的 `scan`, `hScan`, `zScan`, `sScan` 无法使用的问题；
- [#850](https://github.com/hyperf/hyperf/pull/850) 修复 Logger group 在 name 一样时不生效的问题；

## 优化

- [#832](https://github.com/hyperf/hyperf/pull/832) 优化了 Response 对象在转 JSON 格式时的异常处理逻辑；
- [#840](https://github.com/hyperf/hyperf/pull/840) 使用 `\Swoole\Timer::*` 来替代 `swoole_timer_*` 函数；
- [#859](https://github.com/hyperf/hyperf/pull/859) 优化了 RPC 客户端去 Consul 获取健康的节点信息的逻辑；

# v1.1.4 - 2019-10-31

## 新增

- [#778](https://github.com/hyperf/hyperf/pull/778) `Hyperf\Testing\Client` 新增 `PUT` 和 `DELETE`方法；
- [#784](https://github.com/hyperf/hyperf/pull/784) 新增服务监控组件；
- [#795](https://github.com/hyperf/hyperf/pull/795) `AbstractProcess` 增加 `restartInterval` 参数，允许子进程异常或正常退出后，延迟重启；
- [#804](https://github.com/hyperf/hyperf/pull/804) `Command` 增加事件 `BeforeHandle` `AfterHandle` 和 `FailToHandle`；

## 变更

- [#793](https://github.com/hyperf/hyperf/pull/793) `Pool::getConnectionsInChannel` 方法由 `protected` 改为 `public`.
- [#811](https://github.com/hyperf/hyperf/pull/811) 命令 `di:init-proxy` 不再主动清理代理缓存，如果想清理缓存请使用命令 `vendor/bin/init-proxy.sh`；

## 修复

- [#779](https://github.com/hyperf/hyperf/pull/779) 修复 `JPG` 文件验证不通过的问题；
- [#787](https://github.com/hyperf/hyperf/pull/787) 修复 `db:seed` 参数 `--class` 多余，导致报错的问题；
- [#795](https://github.com/hyperf/hyperf/pull/795) 修复自定义进程在异常抛出后，无法正常重启的 BUG；
- [#796](https://github.com/hyperf/hyperf/pull/796) 修复 `etcd` 配置中心 `enable` 即时设为 `false`，在项目启动时，依然会拉取配置的 BUG；

## 优化

- [#781](https://github.com/hyperf/hyperf/pull/781) 可以根据国际化组件配置发布验证器语言包到规定位置；
- [#796](https://github.com/hyperf/hyperf/pull/796) 优化 `ETCD` 客户端，不会多次创建 `HandlerStack`；
- [#797](https://github.com/hyperf/hyperf/pull/797) 优化子进程重启

# v1.1.3 - 2019-10-24

## 新增

- [#745](https://github.com/hyperf/hyperf/pull/745) 为 `gen:model` 命令增加 `with-comments` 选项，以标记是否生成字段注释；
- [#747](https://github.com/hyperf/hyperf/pull/747) 为 AMQP 消费者增加 `AfterConsume`, `BeforeConsume`, `FailToConsume` 事件；
- [#762](https://github.com/hyperf/hyperf/pull/762) 为 Parallel 特性增加协程控制功能；

## 变更

- [#767](https://github.com/hyperf/hyperf/pull/767) 重命名 `AbstractProcess` 的 `running` 属性名为 `listening`；

## 修复

- [#741](https://github.com/hyperf/hyperf/pull/741) 修复执行 `db:seed` 命令缺少文件名报错的问题；
- [#748](https://github.com/hyperf/hyperf/pull/748) 修复 `SymfonyNormalizer` 不处理 `array` 类型数据的问题；
- [#769](https://github.com/hyperf/hyperf/pull/769) 修复当 JSON RPC 响应的结果的 result 和 error 属性为 null 时会抛出一个无效请求的问题；

# v1.1.2 - 2019-10-17

## 新增

- [#722](https://github.com/hyperf-cloud/hyperf/pull/722) 为 AMQP Consumer 新增 `concurrent.limit` 配置来对协程消费进行速率限制；

## 变更

- [#678](https://github.com/hyperf-cloud/hyperf/pull/678) 为 `gen:model` 命令增加 `ignore-tables` 参数，同时默认屏蔽 `migrations` 表，即 `migrations` 表对应的模型在执行 `gen:model` 命令时不会生成；

## 修复

- [#694](https://github.com/hyperf-cloud/hyperf/pull/694) 修复 `Hyperf\Validation\Request\FormRequest` 的 `validationData` 方法不包含上传的文件的问题；
- [#700](https://github.com/hyperf-cloud/hyperf/pull/700) 修复 `Hyperf\HttpServer\Contract\ResponseInterface` 的 `download` 方法不能按预期运行的问题；
- [#701](https://github.com/hyperf-cloud/hyperf/pull/701) 修复自定义进程在出现未捕获的异常时不会自动重启的问题；
- [#704](https://github.com/hyperf-cloud/hyperf/pull/704) 修复 `Hyperf\Validation\Middleware\ValidationMiddleware` 在 action 参数没有定义参数类型时会报错的问题；
- [#713](https://github.com/hyperf-cloud/hyperf/pull/713) 修复当开启了注解缓存功能是，`ignoreAnnotations` 不能按预期工作的问题；
- [#717](https://github.com/hyperf-cloud/hyperf/pull/717) 修复 `getValidatorInstance` 方法会重复创建验证器对象的问题；
- [#724](https://github.com/hyperf-cloud/hyperf/pull/724) 修复 `db:seed` 命令在没有传 `database` 参数时会报错的问题；
- [#729](https://github.com/hyperf-cloud/hyperf/pull/729) 修正组件配置项 `db:model` 为 `gen:model`；
- [#737](https://github.com/hyperf-cloud/hyperf/pull/737) 修复非 Worker 进程下无法使用 Tracer 组件来追踪调用链的问题；

# v1.1.1 - 2019-10-08

## Fixed

- [#664](https://github.com/hyperf/hyperf/pull/664) 调整通过 `gen:request` 命令生成 FormRequest 时 `authorize` 方法的默认返回值；
- [#665](https://github.com/hyperf/hyperf/pull/665) 修复启动时永远会自动生成代理类的问题；
- [#667](https://github.com/hyperf/hyperf/pull/667) 修复当访问一个不存在的路由时 `Hyperf\Validation\Middleware\ValidationMiddleware` 会抛出异常的问题；
- [#672](https://github.com/hyperf/hyperf/pull/672) 修复当 Action 方法上的参数类型为非对象类型时 `Hyperf\Validation\Middleware\ValidationMiddleware` 会抛出一个未捕获的异常的问题；
- [#674](https://github.com/hyperf/hyperf/pull/674) 修复使用 `gen:model` 命令从数据库生成模型时模型表名错误的问题；

# v1.1.0 - 2019-10-08

## 新增

- [#401](https://github.com/hyperf/hyperf/pull/401) 新增了 `Hyperf\HttpServer\Router\Dispatched` 对象来储存解析的路由信息，在用户中间件之前便解析完成以便后续的使用，同时也修复了路由里带参时中间件失效的问题；
- [#402](https://github.com/hyperf/hyperf/pull/402) 新增 `@AsyncQueueMessage` 注解，通过定义此注解在方法上，表明这个方法的实际运行逻辑是投递给 Async-Queue 队列去消费；
- [#418](https://github.com/hyperf/hyperf/pull/418) 允许发送 WebSocket 消息到任意的 fd，即使当前的 Worker 进程不持有对应的 fd，框架会自动进行进程间通讯来实现发送；
- [#420](https://github.com/hyperf/hyperf/pull/420) 为数据库模型增加新的事件机制，与 PSR-15 的事件调度器相配合，可以解耦的定义 Listener 来监听模型事件；
- [#429](https://github.com/hyperf/hyperf/pull/429) [#643](https://github.com/hyperf/hyperf/pull/643) 新增 Validation 表单验证器组件，这是一个衍生于 [illuminate/validation](https://github.com/illuminate/validation) 的组件，感谢 Laravel 开发组提供如此好用的验证器组件，；
- [#441](https://github.com/hyperf/hyperf/pull/441) 当 Redis 连接处于低使用频率的情况下自动关闭空闲连接；
- [#478](https://github.com/hyperf/hyperf/pull/441) 更好的适配 OpenTracing 协议，同时适配 [Jaeger](https://www.jaegertracing.io/)，Jaeger 是一款优秀的开源的端对端分布式调用链追踪系统；
- [#500](https://github.com/hyperf/hyperf/pull/499) 为 `Hyperf\HttpServer\Contract\ResponseInterface` 增加链式方法调用支持，解决调用了代理方法的方法后无法再调用原始方法的问题；
- [#523](https://github.com/hyperf/hyperf/pull/523) 为  `gen:model` 命令新增了 `table-mapping` 选项；
- [#555](https://github.com/hyperf/hyperf/pull/555) 新增了一个全局函数 `swoole_hook_flags` 来获取由常量 `SWOOLE_HOOK_FLAGS` 所定义的 Runtime Hook 等级，您可以在 `bin/hyperf.php` 通过 `! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);` 的方式来定义该常量，即 Runtime Hook 等级；
- [#596](https://github.com/hyperf/hyperf/pull/596)  为`@Inject` 注解增加了  `required` 参数，当您定义 `@Inject(required=false)` 注解到一个成员属性上，那么当该依赖项不存在时也不会抛出 `Hyperf\Di\Exception\NotFoundException` 异常，而是以默认值 `null` 来注入， `required` 参数的默认值为 `true`，当在构造器注入的情况下，您可以通过对构造器的参数定义为 `nullable` 来达到同样的目的；
- [#597](https://github.com/hyperf/hyperf/pull/597) 为 AsyncQueue 组件的消费者增加 `Concurrent` 来控制消费速率；
- [#599](https://github.com/hyperf/hyperf/pull/599) 为 AsyncQueue 组件的消费者增加根据当前重试次数来设定该消息的重试等待时长的功能，可以为消息设置阶梯式的重试等待；
- [#619](https://github.com/hyperf/hyperf/pull/619) 为 Guzzle 客户端增加 HandlerStackFactory 类，以便更便捷地创建一个 HandlerStack；
- [#620](https://github.com/hyperf/hyperf/pull/620) 为 AsyncQueue 组件的消费者增加自动重启的机制；
- [#629](https://github.com/hyperf/hyperf/pull/629) 允许通过配置文件的形式为 Apollo 客户端定义  `clientIp`, `pullTimeout`, `intervalTimeout` 配置；
- [#647](https://github.com/hyperf/hyperf/pull/647) 根据 server 的配置，自动为 TCP Response 追加 `eof`；
- [#648](https://github.com/hyperf/hyperf/pull/648) 为 AMQP Consumer 增加 `nack` 的返回类型，当消费逻辑返回 `Hyperf\Amqp\Result::NACK` 时抽象消费者会以 `basic_nack` 方法来响应消息；
- [#654](https://github.com/hyperf/hyperf/pull/654) 增加所有 Swoole Event 的默认回调和对应的 Hyperf 事件；

## 变更

- [#437](https://github.com/hyperf/hyperf/pull/437) `Hyperf\Testing\Client` 在遇到异常时不再直接抛出异常而是交给 ExceptionHandler 流程处理；
- [#463](https://github.com/hyperf/hyperf/pull/463) 简化了 `container.php` 文件及优化了注解缓存机制；

新的 config/container.php 文件内容如下：

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

- [#486](https://github.com/hyperf/hyperf/pull/486) `Hyperf\HttpMessage\Server\Request` 的 `getParsedBody` 方法现在可以直接处理 JSON 格式的数据了；
- [#523](https://github.com/hyperf/hyperf/pull/523) 调整 `gen:model` 命令生成的模型类名默认为单数，如果表名为复数，则默认生成的类名为单数；
- [#614](https://github.com/hyperf/hyperf/pull/614) [#617](https://github.com/hyperf/hyperf/pull/617) 调整了 ConfigProvider 类的结构, 同时将 `config/dependencies.php` 文件移动到了 `config/autoload/dependencies.php` 内，且文件结构去除了 `dependencies` 层，此后也意味着您也可以将 `dependencies` 配置写到 `config/config.php` 文件内；

Config Provider 内数据结构的变化：
之前：

```php
'scan' => [
    'paths' => [
        __DIR__,
    ],
    'collectors' => [],
],
```

现在：

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

> 增加了一层 annotations，这样将与配置文件结构一致，不再特殊

- [#630](https://github.com/hyperf/hyperf/pull/630) 变更了 `Hyperf\HttpServer\CoreMiddleware` 类的实例化方式，使用 `make()` 来替代了 `new`；
- [#631](https://github.com/hyperf/hyperf/pull/631) 变更了 AMQP Consumer 的实例化方式，使用 `make()` 来替代了 `new`；
- [#637](https://github.com/hyperf/hyperf/pull/637) 调整了 `Hyperf\Contract\OnMessageInterface` 和 `Hyperf\Contract\OnOpenInterface` 的第一个参数的类型约束， 使用 `Swoole\WebSocket\Server` 替代 `Swoole\Server`；
- [#638](https://github.com/hyperf/hyperf/pull/638) 重命名了 `db:model` 命令为 `gen:model` 命令，同时增加了一个 Visitor 来优化创建的 `$connection` 成员属性，如果要创建的模型类的 `$connection` 属性的值与继承的父类一致，那么创建的模型类将不会包含此属性；

## 移除

- [#401](https://github.com/hyperf/hyperf/pull/401) 移除了 `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\HttpServer\ServerFactory`, `Hyperf\GrpcServer\ServerFactory` 类；
- [#402](https://github.com/hyperf/hyperf/pull/402) 移除了弃用的 `AsyncQueue::delay` 方法；
- [#563](https://github.com/hyperf/hyperf/pull/563) 移除了弃用的 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量，使用 `Hyperf\Server\ServerInterface::SERVER_BASE` 来替代；
- [#602](https://github.com/hyperf/hyperf/pull/602) 移除了 `Hyperf\Utils\Coroutine\Concurrent` 的 `timeout` 参数；
- [#612](https://github.com/hyperf/hyperf/pull/612) 移除了 RingPHP Handler 里没有使用到的 `$url` 变量；
- [#616](https://github.com/hyperf/hyperf/pull/616) [#618](https://github.com/hyperf/hyperf/pull/618) 移除了 Guzzle 里一些无用的代码；

## 优化

- [#644](https://github.com/hyperf/hyperf/pull/644) 优化了注解扫描的流程，分开 `app` 和 `vendor` 两部分来扫描注解，大大减少了用户的扫描耗时；
- [#653](https://github.com/hyperf/hyperf/pull/653) 优化了 Swoole shortname 的检测逻辑，现在的检测逻辑更加贴合 Swoole 的实际配置场景，也不只是 `swoole.use_shortname = "Off"` 才能通过检测了；

## 修复

- [#448](https://github.com/hyperf/hyperf/pull/448) 修复了当 HTTP Server 或 WebSocket Server 存在时，TCP Server 有可能无法启动的问题；
- [#623](https://github.com/hyperf/hyperf/pull/623) 修复了当传递一个 `null` 值到代理类的方法参数时，方法仍然会获取方法默认值的问题；

# v1.0.16 - 2019-09-20

## 新增

- [#565](https://github.com/hyperf/hyperf/pull/565) 增加对 Redis 客户端的 `options` 配置参数支持；
- [#580](https://github.com/hyperf/hyperf/pull/580) 增加协程并发控制特性，通过 `Hyperf\Utils\Coroutine\Concurrent` 可以实现一个代码块内限制同时最多运行的协程数量；

## 变更

- [#583](https://github.com/hyperf/hyperf/pull/583) 当 `BaseClient::start` 失败时会抛出 `Hyperf\GrpcClient\Exception\GrpcClientException` 异常；
- [#585](https://github.com/hyperf/hyperf/pull/585) 当投递到 TaskWorker 执行的 Task 失败时，会回传异常到 Worker 进程中；

## 修复

- [#564](https://github.com/hyperf/hyperf/pull/564) 修复某些情况下 `Coroutine\Http2\Client->send` 返回值不正确的问题；
- [#567](https://github.com/hyperf/hyperf/pull/567) 修复当 JSON RPC 消费者配置 name 不是接口时，无法生成代理类的问题；
- [#571](https://github.com/hyperf/hyperf/pull/571) 修复 ExceptionHandler 的 `stopPropagation` 的协程变量污染的问题；
- [#579](https://github.com/hyperf/hyperf/pull/579) 动态初始化 `snowflake`  的 MetaData，主要修复当在命令模式下使用 Snowflake 时，比如 `di:init-proxy` 命令，会连接到 Redis 服务器至超时；

# v1.0.15 - 2019-09-11

## 修复

- [#534](https://github.com/hyperf/hyperf/pull/534) 修复 Guzzle HTTP 客户端的 `CoroutineHanlder` 没有处理状态码为 `-3` 的情况；
- [#541](https://github.com/hyperf/hyperf/pull/541) 修复 gRPC 客户端的 `$client` 参数设置错误的问题；
- [#542](https://github.com/hyperf/hyperf/pull/542) 修复 `Hyperf\Grpc\Parser::parseResponse` 无法支持 gRPC 标准状态码的问题；
- [#551](https://github.com/hyperf/hyperf/pull/551) 修复当服务端关闭了 gRPC 连接时，gRPC 客户端会残留一个死循环的协程；
- [#558](https://github.com/hyperf/hyperf/pull/558) 修复 `UDP Server` 无法正确配置启动的问题；

## 优化

- [#549](https://github.com/hyperf/hyperf/pull/549) 优化了 `Hyperf\Amqp\Connection\SwooleIO` 的 `read` 和 `write` 方法，减少不必要的重试；
- [#559](https://github.com/hyperf/hyperf/pull/559) 优化 `Hyperf\HttpServer\Response::redirect()` 方法，自动识别链接首位是否为斜杠并合理修正参数；
- [#560](https://github.com/hyperf/hyperf/pull/560) 优化 `Hyperf\WebSocketServer\CoreMiddleware`，移除了不必要的代码；

## 移除

- [#545](https://github.com/hyperf/hyperf/pull/545) 移除了 `Hyperf\Database\Model\SoftDeletes` 内无用的 `restoring` 和 `restored` 静态方法；

## 即将移除

- [#558](https://github.com/hyperf/hyperf/pull/558) 标记了 `Hyperf\Server\ServerInterface::SERVER_TCP` 常量为 `弃用` 状态，该常量将于 `v1.1` 移除，由更合理的 `Hyperf\Server\ServerInterface::SERVER_BASE` 常量替代；

# v1.0.14 - 2019-09-05

## 新增

- [#389](https://github.com/hyperf/hyperf/pull/389) [#419](https://github.com/hyperf/hyperf/pull/419) [#432](https://github.com/hyperf/hyperf/pull/432) [#524](https://github.com/hyperf/hyperf/pull/524) 新增 Snowflake 官方组件, Snowflake 是一个由 Twitter 提出的分布式全局唯一 ID 生成算法，[hyperf/snowflake](https://github.com/hyperf/snowflake) 组件实现了该算法并设计得易于使用，同时在设计上提供了很好的可扩展性，可以很轻易的将该组件转换成其它基于 Snowflake 算法的变体算法；
- [#525](https://github.com/hyperf/hyperf/pull/525) 为 `Hyperf\HttpServer\Contract\ResponseInterface` 增加一个 `download()` 方法，提供便捷的下载响应返回；

## 变更

- [#482](https://github.com/hyperf/hyperf/pull/482) 生成模型文件时，当设置了 `refresh-fillable` 选项时重新生成模型的 `fillable` 属性，同时该命令的默认情况下将不会再覆盖生成 `fillable` 属性；
- [#501](https://github.com/hyperf/hyperf/pull/501) 当 `Mapping` 注解的 `path` 属性为一个空字符串时，那么该路由则为 `/prefix`；
- [#513](https://github.com/hyperf/hyperf/pull/513) 如果项目设置了 `app_name` 属性，则进程名称会自动带上该名称；
- [#508](https://github.com/hyperf/hyperf/pull/508) [#526](https://github.com/hyperf/hyperf/pull/526) 当在非协程环境下执行 `Hyperf\Utils\Coroutine::parentId()` 方法时会返回一个 `null` 值；

## 修复

- [#479](https://github.com/hyperf/hyperf/pull/479) 修复了当 Elasticsearch client 的 `host` 属性设置有误时，返回类型错误的问题；
- [#514](https://github.com/hyperf/hyperf/pull/514) 修复当 Redis 密码配置为空字符串时鉴权失败的问题；
- [#527](https://github.com/hyperf/hyperf/pull/527) 修复 Translator 无法重复翻译的问题；

# v1.0.13 - 2019-08-28

## 新增

- [#449](https://github.com/hyperf/hyperf/pull/428) 新增一个独立组件 [hyperf/translation](https://github.com/hyperf/translation)， 衍生于 [illuminate/translation](https://github.com/illuminate/translation)；
- [#449](https://github.com/hyperf/hyperf/pull/449) 为 GRPC-Server 增加标准错误码；
- [#450](https://github.com/hyperf/hyperf/pull/450) 为 `Hyperf\Database\Schema\Schema` 类的魔术方法增加对应的静态方法注释，为 IDE 提供代码提醒的支持；

## 变更

- [#451](https://github.com/hyperf/hyperf/pull/451) 在使用 `@AutoController` 注解时不再会自动为魔术方法生成对应的路由；
- [#468](https://github.com/hyperf/hyperf/pull/468) 让 GRPC-Server 和 HTTP-Server 提供的异常处理器处理所有的异常，而不只是 `ServerException`；

## 修复

- [#466](https://github.com/hyperf/hyperf/pull/466) 修复分页时数据不足时返回类型错误的问题；
- [#466](https://github.com/hyperf/hyperf/pull/470) 优化了 `vendor:publish` 命令，当要生成的目标文件夹存在时，不再重复生成；

# v1.0.12 - 2019-08-21

## 新增

- [#405](https://github.com/hyperf/hyperf/pull/405) 增加 `Hyperf\Utils\Context::override()` 方法，现在你可以通过 `override` 方法获取某些协程上下文的值并修改覆盖它；
- [#415](https://github.com/hyperf/hyperf/pull/415) 对 Logger 的配置文件增加多个 Handler 的配置支持；

## 变更

- [#431](https://github.com/hyperf/hyperf/pull/431) 移除了 `Hyperf\GrpcClient\GrpcClient::openStream()` 的第 3 个参数，这个参数不会影响实际使用；

## 修复

- [#414](https://github.com/hyperf/hyperf/pull/414) 修复 `Hyperf\WebSockerServer\Exception\Handler\WebSocketExceptionHandler` 内的变量名称错误的问题；
- [#424](https://github.com/hyperf/hyperf/pull/424) 修复 Guzzle 在使用 `Hyperf\Guzzle\CoroutineHandler` 时配置 `proxy` 参数时不支持数组传值的问题；
- [#430](https://github.com/hyperf/hyperf/pull/430) 修复 `Hyperf\HttpServer\Request::file()` 当以一个 Name 上传多个文件时，返回格式不正确的问题；
- [#431](https://github.com/hyperf/hyperf/pull/431) 修复 GRPC Client 的 Request 对象在发送 Force-Close 请求时缺少参数的问题；

# v1.0.11 - 2019-08-15

## 新增

- [#366](https://github.com/hyperf/hyperf/pull/366) 增加 `Hyperf\Server\Listener\InitProcessTitleListener` 监听者来设置进程名称， 同时增加了 `Hyperf\Framework\Event\OnStart` 和 `Hyperf\Framework\Event\OnManagerStart` 事件；

## 修复

- [#361](https://github.com/hyperf/hyperf/pull/361) 修复 `db:model`命令在 MySQL 8 下不能正常运行；
- [#369](https://github.com/hyperf/hyperf/pull/369) 修复实现 `\Serializable` 接口的自定义异常类不能正确的序列化和反序列化问题；
- [#384](https://github.com/hyperf/hyperf/pull/384) 修复用户自定义的 `ExceptionHandler` 在 JSON RPC Server 下无法正常工作的问题，因为框架默认自动处理了对应的异常；
- [#370](https://github.com/hyperf/hyperf/pull/370) 修复了 `Hyperf\GrpcClient\BaseClient` 的 `$client` 属性在流式传输的时候设置了错误的类型的值的问题, 同时增加了默认的 `content-type`  为 `application/grpc+proto`，以及允许用户通过自定义 `Request` 对象来重写 `buildRequest()` 方法；

## 变更

- [#356](https://github.com/hyperf/hyperf/pull/356) [#390](https://github.com/hyperf/hyperf/pull/390) 优化 aysnc-queue 组件当生成 Job 时，如果 Job 实现了 `Hyperf\Contract\CompressInterface`，那么 Job 对象会被压缩为一个更小的对象；
- [#358](https://github.com/hyperf/hyperf/pull/358) 只有当 `$enableCache` 为 `true` 时才生成注解缓存文件；
- [#359](https://github.com/hyperf/hyperf/pull/359) [#390](https://github.com/hyperf/hyperf/pull/390) 为 `Collection` 和 `Model` 增加压缩能力，当类实现 `Hyperf\Contract\CompressInterface` 可通过 `compress` 方法生成一个更小的对象；

# v1.0.10 - 2019-08-09

## 新增

- [#321](https://github.com/hyperf/hyperf/pull/321) 为 HTTP Server 的 Controller/RequestHandler 参数增加自定义对象类型的数组支持，特别适用于 JSON RPC 下，现在你可以通过在方法上定义 `@var Object[]` 来获得框架自动反序列化对应对象的支持；
- [#324](https://github.com/hyperf/hyperf/pull/324) 增加一个实现于 `Hyperf\Contract\IdGeneratorInterface` 的 ID 生成器 `NodeRequestIdGenerator`；
- [#336](https://github.com/hyperf/hyperf/pull/336) 增加动态代理的 RPC 客户端功能；
- [#346](https://github.com/hyperf/hyperf/pull/346) [#348](https://github.com/hyperf/hyperf/pull/348) 为 `hyperf/cache` 缓存组件增加文件驱动；

## 变更

- [#330](https://github.com/hyperf/hyperf/pull/330) 当扫描的 $paths 为空时，不输出扫描信息；
- [#328](https://github.com/hyperf/hyperf/pull/328) 根据 Composer 的 PSR-4 定义的规则加载业务项目；
- [#329](https://github.com/hyperf/hyperf/pull/329) 优化 JSON RPC 服务端和客户端的异常消息处理；
- [#340](https://github.com/hyperf/hyperf/pull/340) 为 `make` 函数增加索引数组的传参方式；
- [#349](https://github.com/hyperf/hyperf/pull/349) 重命名下列类，修正由于拼写错误导致的命名错误；

|                     原类名                      |                  修改后的类名                     |
|:----------------------------------------------|:-----------------------------------------------|
| Hyperf\\Database\\Commands\\Ast\\ModelUpdateVistor | Hyperf\\Database\\Commands\\Ast\\ModelUpdateVisitor |
|       Hyperf\\Di\\Aop\\ProxyClassNameVistor       |       Hyperf\\Di\\Aop\\ProxyClassNameVisitor       |
|         Hyperf\\Di\\Aop\\ProxyCallVistor          |         Hyperf\\Di\\Aop\\ProxyCallVisitor          |

## 修复

- [#325](https://github.com/hyperf/hyperf/pull/325) 优化 RPC 服务注册时会多次调用 Consul Services 的问题；
- [#332](https://github.com/hyperf/hyperf/pull/332) 修复 `Hyperf\Tracer\Middleware\TraceMiddeware` 在新版的 openzipkin/zipkin 下的类型约束错误；
- [#333](https://github.com/hyperf/hyperf/pull/333) 修复 `Redis::delete()` 方法在 5.0 版不存在的问题；
- [#334](https://github.com/hyperf/hyperf/pull/334) 修复向阿里云 ACM 配置中心拉取配置时，部分情况下部分配置无法更新的问题；
- [#337](https://github.com/hyperf/hyperf/pull/337) 修复当 Header 的 key 为非字符串类型时，会返回 500 响应的问题；
- [#338](https://github.com/hyperf/hyperf/pull/338) 修复 `ProviderConfig::load` 在遇到重复 key 时会导致在深度合并时将字符串转换成数组的问题；

# v1.0.9 - 2019-08-03

## 新增

- [#317](https://github.com/hyperf/hyperf/pull/317) 增加 `composer-json-fixer` 来优化 composer.json 文件的内容；
- [#320](https://github.com/hyperf/hyperf/pull/320) DI 定义 Definition 时，允许 value 为一个匿名函数；

## 修复

- [#300](https://github.com/hyperf/hyperf/pull/300) 让 AsyncQueue 的消息于子协程内来进行处理，修复 `attempts` 参数与实际重试次数不一致的问题；
- [#305](https://github.com/hyperf/hyperf/pull/305) 修复 `Hyperf\Utils\Arr::set` 方法的 `$key` 参数不支持 `int` 个 `null` 的问题；
- [#312](https://github.com/hyperf/hyperf/pull/312) 修复 `Hyperf\Amqp\BeforeMainServerStartListener` 监听器的优先级错误的问题；
- [#315](https://github.com/hyperf/hyperf/pull/315) 修复 ETCD 配置中心在 Worker 进程重启后或在自定义进程内无法使用问题；
- [#318](https://github.com/hyperf/hyperf/pull/318) 修复服务会持续注册到服务中心的问题；

## 变更

- [#323](https://github.com/hyperf/hyperf/pull/323) 强制转换 `Cacheable` 和 `CachePut` 注解的 `$ttl` 属性为 `int` 类型；

# v1.0.8 - 2019-07-31

## 新增

- [#276](https://github.com/hyperf/hyperf/pull/276) AMQP 消费者支持配置及绑定多个 `routing_key`；
- [#277](https://github.com/hyperf/hyperf/pull/277) 增加 ETCD 客户端组件及 ETCD 配置中心组件；

## 变更

- [#297](https://github.com/hyperf/hyperf/pull/297) 如果服务注册失败，会于 10 秒后重试注册，且屏蔽了连接不上服务中心(Consul)而抛出的异常；
- [#298](https://github.com/hyperf/hyperf/pull/298) [#301](https://github.com/hyperf/hyperf/pull/301) 适配 `openzipkin/zipkin` v1.3.3+ 版本；

## 修复

- [#271](https://github.com/hyperf/hyperf/pull/271) 修复了 AOP 在 `classes` 只会策略下配置同一个类的多个方法只会实现第一个方法的代理方法的问题；
- [#285](https://github.com/hyperf/hyperf/pull/285) 修复了 AOP 在匿名类下生成节点存在丢失的问题；
- [#286](https://github.com/hyperf/hyperf/pull/286) 自动 `rollback` 没有 `commit` 或 `rollback` 的 MySQL 连接；
- [#292](https://github.com/hyperf/hyperf/pull/292) 修复了 `Request::header` 方法的 `$default` 参数无效的问题；
- [#293](https://github.com/hyperf/hyperf/pull/293) 修复了 `Arr::get` 方法的 `$key` 参数不支持 `int` and `null` 传值的问题；

# v1.0.7 - 2019-07-26

## 修复

- [#266](https://github.com/hyperf/hyperf/pull/266) 修复投递 AMQP 消息时的超时逻辑；
- [#273](https://github.com/hyperf/hyperf/pull/273) 修复当有一个服务注册到服务中心的时候所有服务会被移除的问题；
- [#274](https://github.com/hyperf/hyperf/pull/274) 修复视图响应的 Content-Type ；

# v1.0.6 - 2019-07-24

## 新增

- [#203](https://github.com/hyperf/hyperf/pull/203) [#236](https://github.com/hyperf/hyperf/pull/236) [#247](https://github.com/hyperf/hyperf/pull/247) [#252](https://github.com/hyperf/hyperf/pull/252) 增加视图组件，支持 Blade 引擎和 Smarty 引擎；
- [#203](https://github.com/hyperf/hyperf/pull/203) 增加 Task 组件，适配 Swoole Task 机制；
- [#245](https://github.com/hyperf/hyperf/pull/245) 增加 TaskWorkerStrategy 和 WorkerStrategy 两种定时任务调度策略.
- [#251](https://github.com/hyperf/hyperf/pull/251) 增加用协程上下文作为储存的缓存驱动；
- [#254](https://github.com/hyperf/hyperf/pull/254) 增加 `RequestMapping::$methods` 对数组传值的支持, 现在可以通过 `@RequestMapping(methods={"GET"})` 和 `@RequestMapping(methods={RequestMapping::GET})` 两种新的方式定义方法；
- [#255](https://github.com/hyperf/hyperf/pull/255) 控制器返回 `Hyperf\Utils\Contracts\Arrayable` 会自动转换为 Response 对象, 同时对返回字符串的响应对象增加  `text/plain` Content-Type;
- [#256](https://github.com/hyperf/hyperf/pull/256) 如果 `Hyperf\Contract\IdGeneratorInterface` 存在容器绑定关系, 那么 `json-rpc` 客户端会根据该类自动生成一个请求 ID 并储存在 Request attribute 里，同时完善了 `JSON RPC` 在 TCP 协议下的服务注册及健康检查；

## 变更

- [#247](https://github.com/hyperf/hyperf/pull/247) 使用 `WorkerStrategy` 作为默认的计划任务调度策略；
- [#256](https://github.com/hyperf/hyperf/pull/256) 优化 `JSON RPC` 的错误处理，现在当方法不存在时也会返回一个标准的 `JSON RPC` 错误对象；

## 修复

- [#235](https://github.com/hyperf/hyperf/pull/235) 为 `grpc-server` 增加了默认的错误处理器，防止错误抛出.
- [#240](https://github.com/hyperf/hyperf/pull/240) 优化了 OnPipeMessage 事件的触发，修复会被多个监听器获取错误数据的问题；
- [#257](https://github.com/hyperf/hyperf/pull/257) 修复了在某些环境下无法获得内网 IP 的问题；

# v1.0.5 - 2019-07-17

## 新增

- [#185](https://github.com/hyperf/hyperf/pull/185) `响应(Response)` 增加 `xml` 格式支持；
- [#202](https://github.com/hyperf/hyperf/pull/202) 在协程内抛出未捕获的异常时，默认输出异常的 trace 信息；
- [#138](https://github.com/hyperf/hyperf/pull/138) [#197](https://github.com/hyperf/hyperf/pull/197) 增加秒级定时任务组件；

# 变更

- [#195](https://github.com/hyperf/hyperf/pull/195) 变更 `retry()` 函数的 `$times` 参数的行为意义, 表示重试的次数而不是执行的次数；
- [#198](https://github.com/hyperf/hyperf/pull/198) 优化 `Hyperf\Di\Container` 的 `has()` 方法, 当传递一个不可实例化的示例（如接口）至 `$container->has($interface)` 方法时，会返回 `false`；
- [#199](https://github.com/hyperf/hyperf/pull/199) 当生产 AMQP 消息失败时，会自动重试一次；
- [#200](https://github.com/hyperf/hyperf/pull/200) 通过 Git 打包项目的部署包时，不再包含 `tests` 文件夹；

## 修复

- [#176](https://github.com/hyperf/hyperf/pull/176) 修复 `LengthAwarePaginator::nextPageUrl()` 方法返回值的类型约束；
- [#188](https://github.com/hyperf/hyperf/pull/188) 修复 Guzzle Client 的代理设置不生效的问题；
- [#211](https://github.com/hyperf/hyperf/pull/211) 修复 RPC Client 存在多个时会被最后一个覆盖的问题；
- [#212](https://github.com/hyperf/hyperf/pull/212) 修复 Guzzle Client 的 `ssl_key` 和 `cert` 配置项不能正常工作的问题；

# v1.0.4 - 2019-07-08

## 新增

- [#140](https://github.com/hyperf/hyperf/pull/140) 支持 Swoole v4.4.0.
- [#152](https://github.com/hyperf/hyperf/pull/152) 数据库连接在低使用率时连接池会自动释放连接
- [#163](https://github.com/hyperf/hyperf/pull/163) constants 组件的`AbstractConstants::__callStatic` 支持自定义参数

## 变更

- [#124](https://github.com/hyperf/hyperf/pull/124) `DriverInterface::push` 增加 `$delay` 参数用于设置延迟时间, 同时 `DriverInterface::delay` 将标记为弃用的，将于 1.1 版本移除
- [#125](https://github.com/hyperf/hyperf/pull/125) 更改 `config()` 函数的 `$default` 参数的默认值为 `null`.

## 修复

- [#110](https://github.com/hyperf/hyperf/pull/110) [#111](https://github.com/hyperf/hyperf/pull/111) 修复 `Redis::select` 无法正常切换数据库的问题
- [#131](https://github.com/hyperf/hyperf/pull/131) 修复 `middlewares` 配置在 `Router::addGroup` 下无法正常设置的问题
- [#132](https://github.com/hyperf/hyperf/pull/132) 修复 `request->hasFile` 判断条件错误的问题
- [#135](https://github.com/hyperf/hyperf/pull/135) 修复 `response->redirect` 在调整外链时无法正确生成链接的问题
- [#139](https://github.com/hyperf/hyperf/pull/139) 修复 ConsulAgent 的 URI 无法自定义设置的问题
- [#148](https://github.com/hyperf/hyperf/pull/148) 修复当 `migrates` 文件夹不存在时无法生成迁移模板的问题
- [#169](https://github.com/hyperf/hyperf/pull/169) 修复处理请求时没法正确处理数组类型的参数
- [#170](https://github.com/hyperf/hyperf/pull/170) 修复当路由不存在时 WebSocket Server 无法正确捕获异常的问题

## 移除

- [#131](https://github.com/hyperf/hyperf/pull/131) 移除 `Router` `options` 里的 `server` 参数

# v1.0.3 - 2019-07-02

## 新增

- [#48](https://github.com/hyperf/hyperf/pull/48) 增加 WebSocket 协程客户端及服务端
- [#51](https://github.com/hyperf/hyperf/pull/51) 增加了 `enableCache` 参数去控制 `DefinitionSource` 是否启用注解扫描缓存
- [#61](https://github.com/hyperf/hyperf/pull/61) 通过 `db:model` 命令创建模型时增加属性类型
- [#65](https://github.com/hyperf/hyperf/pull/65) 模型缓存增加 JSON 格式支持

## 变更

- [#46](https://github.com/hyperf/hyperf/pull/46) 移除了 `hyperf/di`, `hyperf/command` and `hyperf/dispatcher` 组件对 `hyperf/framework` 组件的依赖

## 修复

- [#45](https://github.com/hyperf/hyperf/pull/55) 修复当引用了 `hyperf/websocket-server` 组件时有可能会导致 HTTP Server 启动失败的问题
- [#55](https://github.com/hyperf/hyperf/pull/55) 修复方法级别的 `@Middleware` 注解可能会被覆盖的问题
- [#73](https://github.com/hyperf/hyperf/pull/73) 修复 `db:model` 命令对短属性处理不正确的问题
- [#88](https://github.com/hyperf/hyperf/pull/88) 修复当控制器存在多层文件夹时生成的路由可能不正确的问题
- [#101](https://github.com/hyperf/hyperf/pull/101) 修复常量不存在 `@Message` 注解时会报错的问题

# v1.0.2 - 2019-06-25

## 新增

- 接入 Travis CI，目前 Hyperf 共存在 426 个单测，1124 个断言； [#25](https://github.com/hyperf/hyperf/pull/25)
- 完善了对 `Redis::connect` 方法的参数支持； [#29](https://github.com/hyperf/hyperf/pull/29)

## 修复

- 修复了 HTTP Server 会被 WebSocket Server 影响的问题（WebSocket Server 尚未发布）；
- 修复了代理类部分注解没有生成的问题；
- 修复了数据库连接池在单测环境下会无法获取连接的问题；
- 修复了 co-phpunit 在某些情况下不能按预期运行的问题；
- 修复了模型事件 `creating`, `updating` ... 运行与预期不一致的问题；
- 修复了 `flushContext` 方法在单测环境下不能按预期运行的问题；
