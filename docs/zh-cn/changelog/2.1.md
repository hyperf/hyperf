# 版本更新记录

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
