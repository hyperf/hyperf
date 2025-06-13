# 版本更新记录

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
