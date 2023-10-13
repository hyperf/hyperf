# 版本更新记录

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
