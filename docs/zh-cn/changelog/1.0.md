# 版本更新记录

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
