# Changelogs

# v2.2.33 - 2022-05-30

## fix

- [#4776](https://github.com/hyperf/hyperf/pull/4776) Fix `GraphQL` event collection failure issue.
- [#4790](https://github.com/hyperf/hyperf/pull/4790) Fix the problem that the method `toRPNExpression` in the `RPN` component does not work properly in some scenarios.

## Added

- [#4763](https://github.com/hyperf/hyperf/pull/4763) Added validation rule `array:key1,key2` to ensure that there is no other `key` in the array except `key1` `key2` key.
- [#4781](https://github.com/hyperf/hyperf/pull/4781) Added configuration `close-pull-request.yml` to automatically close read-only repositories.
# v2.2.32 - 2022-05-16

## Fixed

- [#4745](https://github.com/hyperf/hyperf/pull/4745) Fixed null pointer exception when using `Producer::close`.
- [#4754](https://github.com/hyperf/hyperf/pull/4754) Fixed the bug that monolog does not work in `2.6.0` by configuring `conflict` with `monolog>=2.6.0`.

## Optimized

- [#4738](https://github.com/hyperf/hyperf/pull/4738) Configuring a default groupId when it is null when using `hyperf/kafka`.

# v2.2.31.1 - 2022-04-18

## Fixed

- [#4692](https://github.com/hyperf/hyperf/pull/4692) Fixed type hint error for node `$weight` cased by nacos driver.

# v2.2.31 - 2022-04-18

## Fixed

- [#4677](https://github.com/hyperf/hyperf/pull/4677) Fixed bug that process exit failed when using kafka producer.
- [#4686](https://github.com/hyperf/hyperf/pull/4687) Fixed bug that server shutdown when parse request failed for websocket server.

## Added

- [#4576](https://github.com/hyperf/hyperf/pull/4576) Support `path_prefix` for `node` when using `rpc-client`.
- [#4683](https://github.com/hyperf/hyperf/pull/4683) Added `Container::unbind()` to unbind an arbitrary resolved entry.

# v2.2.30 - 2022-04-04

## Fixed

- [#4648](https://github.com/hyperf/hyperf/pull/4648) Fixed bug that circuit breaker couldn't call fallback on `open` state when using `hyperf/retry`.
- [#4657](https://github.com/hyperf/hyperf/pull/4657) Fixed bug that last modified time was not updated after write session again when using `hyperf/session`.

## Added

- [#4646](https://github.com/hyperf/hyperf/pull/4646) Support setting `auth` for `RedisSentinel`.

# v2.2.29 - 2022-03-28

## Fixed

- [#4620](https://github.com/hyperf/hyperf/pull/4620) Fixed bug that the file name should be an empty string by default for `Hyperf\Memory\LockManager`.

# v2.2.28 - 2022-03-14

## Fixed

- [#4588](https://github.com/hyperf/hyperf/pull/4588) Fixed bug that `database` does not support `bit`.
- [#4589](https://github.com/hyperf/hyperf/pull/4589) Fixed bug that ephemeral instance register failed when using nacos.

## Added

- [#4580](https://github.com/hyperf/hyperf/pull/4580) Added method `Hyperf\Utils\Coroutine\Concurrent::getChannel()`.

## Optimized

- [#4603](https://github.com/hyperf/hyperf/pull/4603) Make public for method `Hyperf\ModelCache\Manager::formatModels()`.

# v2.2.27 - 2022-03-07

## Optimized

- [#4572](https://github.com/hyperf/hyperf/pull/4572) Use Hyperf\LoadBalancer\Exception\RuntimeException instead of \RuntimeException for `hyperf/load-balancer`.

# v2.2.26 - 2022-02-21

## Fixed

- [#4536](https://github.com/hyperf/hyperf/pull/4536) Fixed bug that response header `content-type` will be set more than once sometimes when using json-rpc.

## Added

- [#4527](https://github.com/hyperf/hyperf/pull/4527) Added some useful methods for `Hyperf\Database\Schema\Blueprint`.

## Optimized

- [#4514](https://github.com/hyperf/hyperf/pull/4514) Improved some performance by using lowercase headers.
- [#4521](https://github.com/hyperf/hyperf/pull/4521) Try to connect to another one when connected redis sentinel failed.
- [#4529](https://github.com/hyperf/hyperf/pull/4529) Split `hyperf/context` from `hyperf/utils`.

# v2.2.25 - 2022-01-30

## Fixed

- [#4484](https://github.com/hyperf/hyperf/pull/4484) Fixed bug that `NacosDriver::isRegistered` does not work when using nacos `2.0.4`.

## Added

- [#4477](https://github.com/hyperf/hyperf/pull/4477) Support `Macroable` for `Hyperf\HttpServer\Request`.

## Optimized

- [#4254](https://github.com/hyperf/hyperf/pull/4254) Added check of `grpc.enable_fork_support` option and `pcntl` extension.

# v2.2.24 - 2022-01-24

## Fixed

- [#4474](https://github.com/hyperf/hyperf/pull/4474) Fixed bug that multiplex connection don't close after running test cases.

## Optimized

- [#4451](https://github.com/hyperf/hyperf/pull/4451) Optimized code for `Hyperf\Watcher\Driver\FindNewerDriver`.

# v2.2.23 - 2022-01-17

## Fixed

- [#4426](https://github.com/hyperf/hyperf/pull/4426) Fixed bug that view cache generated failed caused by concurrent request.

## Added

- [#4449](https://github.com/hyperf/hyperf/pull/4449) Allow sorting on multiple criteria for `Hyperf\Utils\Collection`.
- [#4455](https://github.com/hyperf/hyperf/pull/4455) Added command `gen:view-engine-cache` which used to generate cache files in advance.
- [#4453](https://github.com/hyperf/hyperf/pull/4453) Added `Hyperf\Tracer\Aspect\ElasticserachAspect` which used to record traces for elasticsearch.
- [#4458](https://github.com/hyperf/hyperf/pull/4458) Added `Hyperf\Di\ScanHandler\ProcScanHandler` which used to run application when using swow and windows.

# v2.2.22 - 2022-01-04

## Fixed

- [#4399](https://github.com/hyperf/hyperf/pull/4399) Fixed bug that `Redis::scan` does not work when using redis cluster.

## Added

- [#4409](https://github.com/hyperf/hyperf/pull/4409) Added database handler for `session`.
- [#4411](https://github.com/hyperf/hyperf/pull/4411) Added `Hyperf\Tracer\Aspect\DbAspect` to log db records when using `hyperf/db`.
- [#4420](https://github.com/hyperf/hyperf/pull/4420) Support `SSL` for `Hyperf\Amqp\IO\SwooleIO`.

## Optimized

- [#4406](https://github.com/hyperf/hyperf/pull/4406) Adapt swoole 5.0 by removing swoole classes with `PSR-0`.
- [#4429](https://github.com/hyperf/hyperf/pull/4429) Added type hint for `Debug::getRefCount()` which only support `object`.

# v2.2.21 - 2021-12-20

## Fixed

- [#4347](https://github.com/hyperf/hyperf/pull/4347) Fixed bug that amqp io has been bound to more than one coroutine when out of buffer.
- [#4373](https://github.com/hyperf/hyperf/pull/4373) Fixed the metadata generation error caused by switching coroutine for snowflake.

## Added

- [#4344](https://github.com/hyperf/hyperf/pull/4344) Added `Hyperf\Crontab\Event\FailToExecute` event which will be dispatched when executing crontab failed.
- [#4348](https://github.com/hyperf/hyperf/pull/4348) Support to open the generated file with your IDE automatically.

## Optimized

- [#4350](https://github.com/hyperf/hyperf/pull/4350) Optimized the error message for `swoole.use_shortname`.
- [#4360](https://github.com/hyperf/hyperf/pull/4360) No longer uses `Swoole\Coroutine\Client`, but uses `Swoole\Coroutine\Socket`, which is more stable and has better performance in `Hyperf\Amqp\IO\SwooleIO`.

# v2.2.20 - 2021-12-13

## Fixed

- [#4338](https://github.com/hyperf/hyperf/pull/4338) Fixed bug that the path with query params won't match route when using testing client.
- [#4346](https://github.com/hyperf/hyperf/pull/4346) Fixed fatal error for declaration when using amqplib `3.1.1`.

## Added

- [#4330](https://github.com/hyperf/hyperf/pull/4330) Support pack vendor/bin files for `hyperf/phar`.
- [#4331](https://github.com/hyperf/hyperf/pull/4331) Added method `Hyperf\Testing\Debug::getRefCount($object)`.

# v2.2.19 - 2021-12-06

## Fixed

- [#4308](https://github.com/hyperf/hyperf/pull/4308) Fixed bug that `collector-reload` file not found when running `server:watch` with absolute path.

## Optimized

- [#4317](https://github.com/hyperf/hyperf/pull/4317) Improves `Hyperf\Utils\Collection` and `Hyperf\Database\Model\Collection` type definitions.

# v2.2.18 - 2021-11-29

## Fixed

- [#4283](https://github.com/hyperf/hyperf/pull/4283) Fixed type hint error for `Hyperf\Grpc\Parser::deserializeMessage()` when `$response->data` is null.

## Added

- [#4284](https://github.com/hyperf/hyperf/pull/4284) Added method `Hyperf\Utils\Network::ip()`.
- [#4290](https://github.com/hyperf/hyperf/pull/4290) Added HTTP chunk support for `hyperf/http-message`.
- [#4291](https://github.com/hyperf/hyperf/pull/4291) Support dynamic `$arguments` for function `value()`.
- [#4293](https://github.com/hyperf/hyperf/pull/4293) Support run with absolute paths for `server:watch`.
- [#4295](https://github.com/hyperf/hyperf/pull/4295) Added alias `id()` for `Hyperf\Database\Schema\Blueprint::bigIncrements()`.

# v2.2.17 - 2021-11-22

## Fixed

- [#4243](https://github.com/hyperf/hyperf/pull/4243) Fixed the bug that key sort of the result is inconsistent with `$callables` for `parallel`.

## Added

- [#4109](https://github.com/hyperf/hyperf/pull/4109) Added PHP8 support for `hyperf/tracer`.
- [#4260](https://github.com/hyperf/hyperf/pull/4260) Added force index for `hyperf/database`.

# v2.2.16 - 2021-11-15

## Added

- [#4252](https://github.com/hyperf/hyperf/pull/4252) Added method `getServiceName` for rpc client.

## Optimized

- [#4253](https://github.com/hyperf/hyperf/pull/4253) Skip class which is not found by class loader at scan time.

# v2.2.15 - 2021-11-08

## Fixed

- [#4200](https://github.com/hyperf/hyperf/pull/4200) Fixed bug that filesystem cache driver does not work when `runtime/caches` is not a directory.

## Added

- [#4157](https://github.com/hyperf/hyperf/pull/4157) Added `Macroable` for `Hyperf\Utils\Arr`.

# v2.2.14 - 2021-11-01

## Added

- [#4181](https://github.com/hyperf/hyperf/pull/4181) [#4192](https://github.com/hyperf/hyperf/pull/4192) Added versions (v1.0, v2.0, v3.0) support for `psr/log`.

## Fixed

- [#4171](https://github.com/hyperf/hyperf/pull/4171) Fixed health check failed when using consul with token.
- [#4188](https://github.com/hyperf/hyperf/pull/4188) Fixed bug that build phar failed when using composer `1.x`.

# v2.2.13 - 2021-10-25

## Added

- [#4159](https://github.com/hyperf/hyperf/pull/4159) Allow `Macroable::mixin` to only add macros that do not exist yet.

## Fixed

- [#4158](https://github.com/hyperf/hyperf/pull/4158) Fixed bug that generate proxy class failed when using union type.

## Optimized

- [#4159](https://github.com/hyperf/hyperf/pull/4159) [#4166](https://github.com/hyperf/hyperf/pull/4166) Split `hyperf/macroable` from `hyperf/utils`.

# v2.2.12 - 2021-10-18

## Added

- [#4129](https://github.com/hyperf/hyperf/pull/4129) Added methods `Str::stripTags()` and `Stringable::stripTags()`.

## Fixed

- [#4130](https://github.com/hyperf/hyperf/pull/4130) Fixed bug that generate model failed when using option `--with-ide` and `scope` methods.
- [#4141](https://github.com/hyperf/hyperf/pull/4141) Fixed bug that validator factory does not support other validators.

# v2.2.11 - 2021-10-11

## Fixed

- [#4101](https://github.com/hyperf/hyperf/pull/4101) Fixed bug that auth failed when password has special charsets for nacos.

# Optimized

- [#4114](https://github.com/hyperf/hyperf/pull/4114) Optimized get error code after Websocket upgrade failed.
- [#4119](https://github.com/hyperf/hyperf/pull/4119) Optimized testing client which create the directory again when the directory does not exist.

# v2.2.10 - 2021-09-26

## Fixed

- [#4088](https://github.com/hyperf/hyperf/pull/4088) Fixed bug that crontab rule convert `empty string` into `0` accidentally.
- [#4096](https://github.com/hyperf/hyperf/pull/4096) Fixed bug that generate proxy class failed caused by variadic parameters with type.

# v2.2.9 - 2021-09-22

## Fixed

- [#4061](https://github.com/hyperf/hyperf/pull/4061) Fixed the conflict between the latest version of prometheus_client_php and `hyperf/metric`.
- [#4068](https://github.com/hyperf/hyperf/pull/4068) Fixed bug that exit code of `Command` is incorrect when throwing an exception.
- [#4076](https://github.com/hyperf/hyperf/pull/4076) Fixed server broken caused by sending response failed.

## Added

- [#4014](https://github.com/hyperf/hyperf/pull/4014) [#4080](https://github.com/hyperf/hyperf/pull/4080) Support `sasl` and `ssl` for kafka.
- [#4045](https://github.com/hyperf/hyperf/pull/4045) [#4082](https://github.com/hyperf/hyperf/pull/4082) Support to control whether to report by `tracer` through config `opentracing.enable.exception`.
- [#4086](https://github.com/hyperf/hyperf/pull/4086) Support annotation for interface.

# Optimized

- [#4084](https://github.com/hyperf/hyperf/pull/4084) Optimized the exception message when the attribute not found.

# v2.2.8 - 2021-09-14

## Fixed

- [#4028](https://github.com/hyperf/hyperf/pull/4028) Fixed the success rate calculation in grafana dashboard.
- [#4030](https://github.com/hyperf/hyperf/pull/4030) Fixed bug that async-queue broken caused by uncompressing model failed.
- [#4042](https://github.com/hyperf/hyperf/pull/4042) Fixed coroutines deadlock caused by cleaning up expired fds in socketio-server when stop server.

## Added

- [#4013](https://github.com/hyperf/hyperf/pull/4013) Support `sameSite=None` when return response with cookies.
- [#4017](https://github.com/hyperf/hyperf/pull/4017) Added `Macroable` into `Hyperf\Utils\Collection`.
- [#4021](https://github.com/hyperf/hyperf/pull/4021) Added argument `$attempts` into `$callback` when using function `retry()`.
- [#4040](https://github.com/hyperf/hyperf/pull/4040) Added method `ConsumerDelayedMessageTrait::getDeadLetterExchange()` which used to rewrite `x-dead-letter-exchange` by yourself.

## Removed

- [#4017](https://github.com/hyperf/hyperf/pull/4017) Removed `Macroable` from `Hyperf\Database\Model\Collection` because it already exists in `Hyperf\Utils\Collection`.

# v2.2.7 - 2021-09-06

# Fixed

- [#3997](https://github.com/hyperf/hyperf/pull/3997) Fixed unexpected termination of nats consumer after timeout.
- [#3998](https://github.com/hyperf/hyperf/pull/3998) Fixed bug that `apollo` does not support `https`.

## Optimized

- [#4009](https://github.com/hyperf/hyperf/pull/4009) Optimized method `MethodDefinitionCollector::getOrParse()` to avoid deprecated in PHP8.

## Added

- [#4002](https://github.com/hyperf/hyperf/pull/4002) [#4012](https://github.com/hyperf/hyperf/pull/4012) Support method `FormRequest::scene()` which used to rewrite different rules according to different scenes.
- [#4011](https://github.com/hyperf/hyperf/pull/4011) Added some methods for `Hyperf\Utils\Str`.

# v2.2.6 - 2021-08-30

## Fixed

- [#3969](https://github.com/hyperf/hyperf/pull/3969) Fixed type error when using `Hyperf\Validation\Rules\Unique::__toString()` in PHP8.
- [#3979](https://github.com/hyperf/hyperf/pull/3979) Fixed bug that timeout property does not work in circuit breaker.
- [#3986](https://github.com/hyperf/hyperf/pull/3986) Fixed OSS hook failed when using `SWOOLE_HOOK_NATIVE_CURL`.

## Added

- [#3987](https://github.com/hyperf/hyperf/pull/3987) Support delayed message exchange for AMQP.
- [#3989](https://github.com/hyperf/hyperf/pull/3989) [#3992](https://github.com/hyperf/hyperf/pull/3992) Added option `command` which used to define your own start command.

# v2.2.5 - 2021-08-23

## Fixed

- [#3959](https://github.com/hyperf/hyperf/pull/3959) Fixed validate rule `date` does not work as expected when the value isn't string.
- [#3960](https://github.com/hyperf/hyperf/pull/3960) Fixed bug that crontab cannot be closed safely in coroutine style server.

## Added

- [code-generator](https://github.com/hyperf/code-generator) Added `code-generator` which used to regenerate classes with `Attributes` instead of `Doctrine Annotations`.

## Optimized

- [#3957](https://github.com/hyperf/hyperf/pull/3957) Support generate the type of getAttribute with `@return` for command `gen:model`.

# v2.2.4 - 2021-08-16

## Fixed

- [#3925](https://github.com/hyperf/hyperf/pull/3925) Fixed bug that heartbeat failed caused by nacos light beat enabled.
- [#3926](https://github.com/hyperf/hyperf/pull/3926) Fixed bug that the config of `config_center.drivers.nacos.client` does not work.

## Added

- [#3924](https://github.com/hyperf/hyperf/pull/3924) Added health check parameters for consul service register.
- [#3932](https://github.com/hyperf/hyperf/pull/3932) Support requeue the message when return `NACK` for `AMQP` consumer.
- [#3941](https://github.com/hyperf/hyperf/pull/3941) Support service register for `rpc-multiplex`.
- [#3947](https://github.com/hyperf/hyperf/pull/3947) Added method `Str::mask` which used to replace chars from a string by a given char.

## Optimized

- [#3944](https://github.com/hyperf/hyperf/pull/3944) Encapsulated the code for reading aspect meta properties.

# v2.2.3 - 2021-08-09

## Fixed

- [#3897](https://github.com/hyperf/hyperf/pull/3897) Fixed bug that nacos instance will be registered more than once, because heartbeat failed caused by light beat enabled.
- [#3905](https://github.com/hyperf/hyperf/pull/3905) Fixed null pointer exception when closing AMQPConnection.
- [#3906](https://github.com/hyperf/hyperf/pull/3906) Fixed bug that close connection failed caused by wait channels flushed.
- [#3908](https://github.com/hyperf/hyperf/pull/3908) Fixed bug that the process couldn't be restarted caused by loop which using `CoordinatorManager`.

# v2.2.2 - 2021-08-03

## Fixed

- [#3872](https://github.com/hyperf/hyperf/pull/3872) [#3873](https://github.com/hyperf/hyperf/pull/3873) Fixed bug that heartbeat failed when using nacos without default group.
- [#3877](https://github.com/hyperf/hyperf/pull/3877) Fixed bug that heartbeat will be registered more than once.
- [#3879](https://github.com/hyperf/hyperf/pull/3879) Fixed bug that `watcher` does not work caused by proxies replaced.

## Optimized

- [#3877](https://github.com/hyperf/hyperf/pull/3877) Support `lightBeatEnabled` for Nacos heartbeat.

# v2.2.1 - 2021-07-27

## Fixed

- [#3750](https://github.com/hyperf/hyperf/pull/3750) Fixed fatal error which caused by dispatching a non exist namespace when using `socket-io`.
- [#3828](https://github.com/hyperf/hyperf/pull/3828) Fixed bug that lazy inject does not work for `Hyperf\Redis\Redis` in `PHP8.0`.
- [#3845](https://github.com/hyperf/hyperf/pull/3845) Fixed bug that `watcher` does not work for `v2.2`.
- [#3848](https://github.com/hyperf/hyperf/pull/3848) Fixed bug that the usage of registering itself like `nacos v2.1` does not work.
- [#3866](https://github.com/hyperf/hyperf/pull/3866) Fixed bug that the metadata of nacos instance can't be registered successfully.

## Optimized

- [#3763](https://github.com/hyperf/hyperf/pull/3763) Support chained calls for `JsonResource::wrap()` and `JsonResource::withoutWrapping()`.
- [#3843](https://github.com/hyperf/hyperf/pull/3843) Check the status code and body of the response to ensure whether the instance already be registered.
- [#3854](https://github.com/hyperf/hyperf/pull/3854) Support RFC 5987 for `Hyperf\HttpServer\Contract\ResponseInterface::download()` which allows utf-8 encoding, percentage encoded (url-encoded).

# v2.2.0 - 2021-07-19

## Dependencies Upgrade

- Upgraded `friendsofphp/php-cs-fixer` to `^3.0`;
- Upgraded `psr/container` to `^1.0|^2.0`;
- Upgraded `egulias/email-validator` to `^3.0`;
- Upgraded `markrogoyski/math-php` to `^2.0`;
- [3783](https://github.com/hyperf/hyperf/pull/3783) Upgraded `league/flysystem` to `^1.0|^2.0`;

## Dependencies Changed

- [#3577](https://github.com/hyperf/hyperf/pull/3577) `domnikl/statsd` is abandoned and no longer maintained. The author suggests using the `slickdeals/statsd` package instead.

## Changed

- [#3334](https://github.com/hyperf/hyperf/pull/3334) Changed the return value of `LengthAwarePaginator::toArray()` to be consistent with that of `Paginator::toArray()`.
- [#3550](https://github.com/hyperf/hyperf/pull/3550) Removed `broker` and `bootstrap_server` from `kafka`, please use `brokers` and `bootstrap_servers` instead.
- [#3580](https://github.com/hyperf/hyperf/pull/3580) Changed the default priority of aspect to 0.
- [#3582](https://github.com/hyperf/hyperf/pull/3582) Changed the consumer tag of amqp to empty string.
- [#3634](https://github.com/hyperf/hyperf/pull/3634) Use Fork Process strategy to replace BetterReflection strategy.
  - [#3649](https://github.com/hyperf/hyperf/pull/3649) Removed `roave/better-reflection` from `hyperf/database` when using `gen:model`.
  - [#3651](https://github.com/hyperf/hyperf/pull/3651) Removed `roave/better-reflection` from LazyLoader.
  - [#3654](https://github.com/hyperf/hyperf/pull/3654) Removed `roave/better-reflection` from other components.
- [#3676](https://github.com/hyperf/hyperf/pull/3676) Use `promphp/prometheus_client_php` instead of `endclothing/prometheus_client_php`.
- [#3694](https://github.com/hyperf/hyperf/pull/3694) Changed `Hyperf\CircuitBreaker\CircuitBreakerInterface` to support php8.
  - Changed `CircuitBreaker::inc*Counter()` to `CircuitBreaker::incr*Counter()`.
  - Changed type hint for method `AbstractHandler::switch()`.
- [#3706](https://github.com/hyperf/hyperf/pull/3706) Changed the style of writing to `#[Middlewares(FooMiddleware::class)]` from `@Middlewares({@Middleware(FooMiddleware::class)})` in PHP8.
- [#3715](https://github.com/hyperf/hyperf/pull/3715) Restructure nacos component, be sure to reread the documents.
- [#3722](https://github.com/hyperf/hyperf/pull/3722) Removed config `config_apollo.php`, please use `config_center.php` instead.
- [#3725](https://github.com/hyperf/hyperf/pull/3725) Removed config `config_etcd.php`, please use `config_center.php` instead.
- [#3730](https://github.com/hyperf/hyperf/pull/3730) Removed config `brokers` and `update_brokers` from kafka.
- [#3733](https://github.com/hyperf/hyperf/pull/3733) Removed config `zookeeper.php`, please use `config_center.php` instead.
- [#3734](https://github.com/hyperf/hyperf/pull/3734) Split `nacos` into `config-nacos` and `service-governance-nacos`.
  - [#3772](https://github.com/hyperf/hyperf/pull/3772) Fixed bug that nacos driver do not work.
- [#3734](https://github.com/hyperf/hyperf/pull/3734) Renamed `nacos-sdk` as `nacos`.
- [#3737](https://github.com/hyperf/hyperf/pull/3737) Refactor config-center and config driver
  - Added `AbstractDriver` and merge the duplicate code into the abstraction class
  - Added `PipeMessageInterface` to uniform the message struct of config fetcher process
- [#3817](https://github.com/hyperf/hyperf/pull/3817) [#3818](https://github.com/hyperf/hyperf/pull/3818) Split `service-governance-consul` from `service-governance`.
- [#3819](https://github.com/hyperf/hyperf/pull/3819) Use their own configuration below `config_center.php` for config center component which using ETCD and Nacos.

## Deprecated

- [#3636](https://github.com/hyperf/hyperf/pull/3636) `Hyperf\Utils\Resource` will be deprecated in v2.3, please use `Hyperf\Utils\ResourceGenerator` instead.

## Added

- [#3589](https://github.com/hyperf/hyperf/pull/3589) Added DAG component.
- [#3606](https://github.com/hyperf/hyperf/pull/3606) Added RPN component.
- [#3629](https://github.com/hyperf/hyperf/pull/3629) Added `Hyperf\Utils\Channel\ChannelManager` which used to manage channels.
- [#3631](https://github.com/hyperf/hyperf/pull/3631) Support multiplexing for AMQP component.
  - [#3639](https://github.com/hyperf/hyperf/pull/3639) Close push channel and socket when worker exited.
  - [#3640](https://github.com/hyperf/hyperf/pull/3640) Optimized log level for SwooleIO.
  - [#3657](https://github.com/hyperf/hyperf/pull/3657) Fixed memory exhausted for rabbitmq caused by confirm channel.
  - [#3659](https://github.com/hyperf/hyperf/pull/3659) Optimized code which be used to close connection friendly.
  - [#3681](https://github.com/hyperf/hyperf/pull/3681) Fixed bug that rpc client does not work for amqp.
- [#3635](https://github.com/hyperf/hyperf/pull/3635) Added `Hyperf\Utils\CodeGen\PhpParser` which used to generate AST for reflection.
- [#3648](https://github.com/hyperf/hyperf/pull/3648) Added `Hyperf\Utils\CodeGen\PhpDocReaderManager` to manage `PhpDocReader`.
- [#3679](https://github.com/hyperf/hyperf/pull/3679) Added Nacos SDK component.
  - [#3712](https://github.com/hyperf/hyperf/pull/3712) The input parameters of `InstanceProvider::update()` are modified to make it more friendly.
- [#3698](https://github.com/hyperf/hyperf/pull/3698) Support PHP8 Attribute which can replace doctrine annotations.
- [#3714](https://github.com/hyperf/hyperf/pull/3714) Added ide-helper component.
- [#3722](https://github.com/hyperf/hyperf/pull/3722) Added config-center component.
- [#3728](https://github.com/hyperf/hyperf/pull/3728) Added support for `secret` of Apollo.
- [#3743](https://github.com/hyperf/hyperf/pull/3743) Support custom register for service governance.
- [#3753](https://github.com/hyperf/hyperf/pull/3753) Support long pulling mode for Apollo Client.
- [#3759](https://github.com/hyperf/hyperf/pull/3759) Added `rpc-multiplex` component.
- [#3791](https://github.com/hyperf/hyperf/pull/3791) Support setting multiple annotations by inheriting `AbstractMultipleAnnotation`, such as `@Middleware`.
- [#3806](https://github.com/hyperf/hyperf/pull/3806) Added heartbeat for nacos service governance.

## Optimized

- [#3670](https://github.com/hyperf/hyperf/pull/3670) Adapt database component to support php8.
- [#3673](https://github.com/hyperf/hyperf/pull/3673) Adapt all components to support php8.
- [#3730](https://github.com/hyperf/hyperf/pull/3730) Optimized code for kafka component.
  - Support `timeout` for `Producer` to avoid requests not responding.
  - Removed useless code with pool.
  - Throw exceptions when connect kafka failed.
- [#3758](https://github.com/hyperf/hyperf/pull/3758) Optimized code for pool which get connection again when first failed.

## Fixed

- [#3650](https://github.com/hyperf/hyperf/pull/3650) Fixed bug that `ReflectionParameter::getClass()` will be deprecated in php8.
- [#3692](https://github.com/hyperf/hyperf/pull/3692) Fixed bug that class proxies couldn't be included when building phar.
- [#3769](https://github.com/hyperf/hyperf/pull/3769) Fixed bug that `config-center` conflicts with `metrics`.
- [#3770](https://github.com/hyperf/hyperf/pull/3770) Fixed type error when using `Str::slug()`.
- [#3788](https://github.com/hyperf/hyperf/pull/3788) Fixed type error when using `BladeCompiler::getRawPlaceholder()`.
- [#3794](https://github.com/hyperf/hyperf/pull/3794) Fixed bug that `retry_interval` does not work for `rpc-multiplex`.
- [#3798](https://github.com/hyperf/hyperf/pull/3798) Fixed bug that amqp consumer couldn't restart when rabbitmq server stopped.
- [#3814](https://github.com/hyperf/hyperf/pull/3814) Fixed bug that `libxml_disable_entity_loader()` has been deprecated as of PHP 8.0.0.

# v2.1.23 - 2021-07-12

## Optimized

- [#3787](https://github.com/hyperf/hyperf/pull/3787) Initialize PSR Response first to avoid problems caused by the failure of building PSR Request.

# v2.1.22 - 2021-06-28

## Security

- [#3723](https://github.com/hyperf/hyperf/pull/3723) Fixed the active_url rule for validation in input fails to correctly check dns record with dns_get_record resulting in bypassing the validation.
- [#3724](https://github.com/hyperf/hyperf/pull/3724) Fixed bug that `RequiredIf` can be exploited to generate gadget chains for deserialization vulnerabiltiies.

## Fixed

- [#3721](https://github.com/hyperf/hyperf/pull/3721) Fixed the `in` and `not in` rule for validation in input fails to correctly check `in:00` rule when passing `0`.

# v2.1.21 - 2021-06-21

## Fixed

- [#3684](https://github.com/hyperf/hyperf/pull/3684) Fixed the wrong judgment of `counter` or `duration` for circuit breaker.

# v2.1.20 - 2021-06-07

## Fixed

- [#3667](https://github.com/hyperf/hyperf/pull/3667) Fixed bug that the crontab rule like `10-12/1,14-15/1` does not works.
- [#3669](https://github.com/hyperf/hyperf/pull/3669) Fixed bug that the crontab rule without backslash like `10-12` does not works.
- [#3674](https://github.com/hyperf/hyperf/pull/3674) Fixed bug that property `$workerId` does not works in annotation `@Task`.

## Optimized

- [#3663](https://github.com/hyperf/hyperf/pull/3663) Optimized code of `AbstractServiceClient::getNodesFromConsul()`.
- [#3668](https://github.com/hyperf/hyperf/pull/3668) Optimized proxy code of `CoroutineHandler`, which is more friendly than before.

# v2.1.19 - 2021-05-31

## Fixed

- [#3618](https://github.com/hyperf/hyperf/pull/3618) Fixed routes with same path but different methods will be merged when using `describe:routes`.
- [#3625](https://github.com/hyperf/hyperf/pull/3625) Fixed bug that `class_map` does not works in `Hyperf\Di\Annotation\Scanner`.

## Added

- [#3626](https://github.com/hyperf/hyperf/pull/3626) Added `Hyperf\Rpc\PathGenerator\DotPathGenerator`.

## Incubator

- [nacos-sdk](https://github.com/hyperf/nacos-sdk-incubator) Nacos SDK for Open API.

# v2.1.18 - 2021-05-24

## Fixed

- [#3598](https://github.com/hyperf/hyperf/pull/3598) Fixed bug that `increment/decrement` does not works as expect when used in transaction for model-cache.
- [#3607](https://github.com/hyperf/hyperf/pull/3607) Fixed bug that coroutine won't destruct when using `onOpen` in coroutine style websocket server.
- [#3610](https://github.com/hyperf/hyperf/pull/3610) Fixed bug that `fromSub()` and `joinSub()` don't work with table prefix.

# v2.1.17 - 2021-05-17

## Fixed

- [#3856](https://github.com/hyperf/hyperf/pull/3586) Fixed bug that coroutine won't destruct for keepalive request in swow server.

## Added

- [#3329](https://github.com/hyperf/hyperf/pull/3329) The `enable` parameter of the `@Crontab` supports `array`, which you can dynamically control whether the task is executed or not.

# v2.1.16 - 2021-04-26

## Fixed

- [#3510](https://github.com/hyperf/hyperf/pull/3510) Fixed bug that consul couldn't force a node into the left state.
- [#3513](https://github.com/hyperf/hyperf/pull/3513) Fixed nats connection closed accidentally when socket timeout is smaller than max idle time.
- [#3520](https://github.com/hyperf/hyperf/pull/3520) Fixed `@Inject` does not works in nested trait.

## Added

- [#3514](https://github.com/hyperf/hyperf/pull/3514) Added method `Hyperf\HttpServer\Request::clearStoredParsedData()`.

## Optimized

- [#3517](https://github.com/hyperf/hyperf/pull/3517) Optimized code for `Hyperf\Di\Aop\PropertyHandlerTrait`.

# v2.1.15 - 2021-04-19

## Added

- [#3484](https://github.com/hyperf/hyperf/pull/3484) Added methods `withMax()` `withMin()` `withSum()` and `withAvg()`.

# v2.1.14 - 2021-04-12

## Fixed

- [#3465](https://github.com/hyperf/hyperf/pull/3465) Fixed bug that websocket does not works when exist more than one server in coroutine style.
- [#3467](https://github.com/hyperf/hyperf/pull/3467) Fixed bug that db connection couldn't be released to pool when using coroutine style websocket server.

## Added

- [#3472](https://github.com/hyperf/hyperf/pull/3472) Added method `Sender::getResponse()` which you can used to get response from coroutine style server.

# v2.1.13 - 2021-04-06

## Fixed

- [#3432](https://github.com/hyperf/hyperf/pull/3432) Fixed bug that `ttl` does not works on other workers for socketio-server.
- [#3434](https://github.com/hyperf/hyperf/pull/3434) Fixed bug that the type of rpc result does not support types which allows null.
- [#3447](https://github.com/hyperf/hyperf/pull/3447) Fixed default value of column does not works in model-cache when has table prefix.
- [#3450](https://github.com/hyperf/hyperf/pull/3450) Fixed bug that `@Crontab` does not works when used in methods.

## Optimized

- [#3453](https://github.com/hyperf/hyperf/pull/3453) Optimized code for releasing instance in `Hyperf\Utils\Channel\Caller`.
- [#3455](https://github.com/hyperf/hyperf/pull/3455) Optimized `phar:build`, which can support `symlink` package.

# v2.1.12 - 2021-03-29

## Fixed

- [#3423](https://github.com/hyperf/hyperf/pull/3423) Fixed crontab does not works when worker_num isn't integer for task worker strategy.
- [#3426](https://github.com/hyperf/hyperf/pull/3426) Fixed bug that middleware will be handled twice when used in optional route.

## Optimized

- [#3422](https://github.com/hyperf/hyperf/pull/3422) Optimized code for `co-phpunit`.

# v2.1.11 - 2021-03-22

## Added

- [#3376](https://github.com/hyperf/hyperf/pull/3376) Support `$connection` and `$attempts` for `Hyperf\DbConnection\Annotation\Transactional`.
- [#3403](https://github.com/hyperf/hyperf/pull/3403) Added method `Hyperf\Testing\Client::sendRequest()` that you can use your own server request.

## Fixed

- [#3380](https://github.com/hyperf/hyperf/pull/3380) Fixed bug that super globals does not work when request don't persist to context.
- [#3394](https://github.com/hyperf/hyperf/pull/3394) Fixed bug that the injected property will be replaced by injected property defined in trait.
- [#3395](https://github.com/hyperf/hyperf/pull/3395) Fixed bug that the private property which injected by parent class does not exists in class.
- [#3398](https://github.com/hyperf/hyperf/pull/3398) Fixed UploadedFile::isValid() does not works in phpunit.

# v2.1.10 - 2021-03-15

## Fixed

- [#3348](https://github.com/hyperf/hyperf/pull/3348) Fixed bug that `Arr::forget` failed when the integer key does not exists.
- [#3351](https://github.com/hyperf/hyperf/pull/3351) Fixed bug that `FormRequest` could't get the changed data from `Context`.
- [#3356](https://github.com/hyperf/hyperf/pull/3356) Fixed bug that could't get the valid `uri` when using `Hyperf\Testing\Client`.
- [#3363](https://github.com/hyperf/hyperf/pull/3363) Fixed bug that `constants` which defined in `bin/hyperf.php` does not works for `server:start`.
- [#3365](https://github.com/hyperf/hyperf/pull/3365) Fixed bug that `pid_file` will be created accidently when you don't configure `pid_file` in coroutine style server.

## Optimized

- [#3364](https://github.com/hyperf/hyperf/pull/3364) Optimized `phar:build` that you can run phar without `php`, such as `./composer.phar` instead of `php composer.phar`.
- [#3367](https://github.com/hyperf/hyperf/pull/3367) Optimized code for guessing the return type for custom caster when using `gen:model`.

# v2.1.9 - 2021-03-08

## Fixed

- [#3326](https://github.com/hyperf/hyperf/pull/3326) Fixed bug that `unpack` custom data failed when using `JsonEofPacker`.
- [#3330](https://github.com/hyperf/hyperf/pull/3330) Fixed data query error caused by unexpected change of `$constraints` by other coroutine.

## Added

- [#3325](https://github.com/hyperf/hyperf/pull/3325) Added `enable` to control the crontab task which to register or not.

## Optimized

- [#3338](https://github.com/hyperf/hyperf/pull/3338) Optimized code for `testing` which mock request in an alone coroutine.

# v2.1.8 - 2021-03-01

## Fixed

- [#3301](https://github.com/hyperf/hyperf/pull/3301) Fixed bug that the value of ttl will be converted to 0 when you don't set it for `hyperf/cache`.

## Added

- [#3310](https://github.com/hyperf/hyperf/pull/3310) Added `Blueprint::comment()` which you can set comment of table for migration.
- [#3311](https://github.com/hyperf/hyperf/pull/3311) Added `RouteCollector::getRouteParser` which you can get `RouteParser` from `RouteCollector`.
- [#3316](https://github.com/hyperf/hyperf/pull/3316) Allow custom driver which you can used to register your own driver for `hyperf/db`.

## Optimized

- [#3308](https://github.com/hyperf/hyperf/pull/3308) Send response directly when the handler does not exists.
- [#3319](https://github.com/hyperf/hyperf/pull/3319) Optimized code that get connection from pool.

## Incubator

- [rpc-multiplex](https://github.com/hyperf/rpc-multiplex-incubator) Rpc for multiplexing connection
- [db-pgsql](https://github.com/hyperf/db-pgsql-incubator) PgSQL driver for Hyperf DB Component

# v2.1.7 - 2021-02-22

## Fixed

- [#3272](https://github.com/hyperf/hyperf/pull/3272) Fixed bug that rename column name failed when using `doctrine/dbal`.

## Added

- [#3261](https://github.com/hyperf/hyperf/pull/3261) Added method `Pipeline::handleCarry()` which to handle the returning value.
- [#3267](https://github.com/hyperf/hyperf/pull/3267) Added `Hyperf\Utils\Reflection\ClassInvoker` which you can used to execute non public methods or get non public properties.
- [#3268](https://github.com/hyperf/hyperf/pull/3268) Added support for kafka consumers to subscribe to multiple topics.
- [#3193](https://github.com/hyperf/hyperf/pull/3193) [#3296](https://github.com/hyperf/hyperf/pull/3296) Added option `-M` which you can mount external files or dirs to a virtual location within the phar archive for `phar:build`.

## Changed

- [#3258](https://github.com/hyperf/hyperf/pull/3258) Set different client ids based on different kafka consumers.
- [#3282](https://github.com/hyperf/hyperf/pull/3282) Renamed `stoped` to `stopped` for `hyperf/signal`.

# v2.1.6 - 2021-02-08

## Fixed

- [#3233](https://github.com/hyperf/hyperf/pull/3233) Fixed connection exhausted, when connect amqp server failed.
- [#3245](https://github.com/hyperf/hyperf/pull/3245) Fixed `autoCommit` does not works when you set `false` for `hyperf/kafka`.
- [#3255](https://github.com/hyperf/hyperf/pull/3255) Fixed bug that `defer` cannot be triggered in nsq consumer.

## Optimized

- [#3249](https://github.com/hyperf/hyperf/pull/3249) Optimized `hyperf/kafka` which won't make a new producer to requeue message.

## Removed

- [#3235](https://github.com/hyperf/hyperf/pull/3235) Removed rebalance check, because `longlang/phpkafka` checked.

# v2.1.5 - 2021-02-01

## Fixed

- [#3204](https://github.com/hyperf/hyperf/pull/3204) Fixed unexpected behavior for `middlewares` when using `rpc-server`.
- [#3209](https://github.com/hyperf/hyperf/pull/3209) Fixed bug that connection was not be released to pool when the amqp consumer broken in coroutine style server.
- [#3222](https://github.com/hyperf/hyperf/pull/3222) Fixed memory leak for join queries in `hyperf/database`.
- [#3228](https://github.com/hyperf/hyperf/pull/3228) Fixed bug that server crash when tracer flush failed in defer.
- [#3230](https://github.com/hyperf/hyperf/pull/3230) Fixed `orderBy` does not works for `hyperf/scout`.

## Added

- [#3211](https://github.com/hyperf/hyperf/pull/3211) Added optional configuration url for nacos which used to request nacos server.
- [#3214](https://github.com/hyperf/hyperf/pull/3214) Added Caller which help you to use instance in coroutine security mode.
- [#3224](https://github.com/hyperf/hyperf/pull/3224) Added `Hyperf\Utils\CodeGen\Package::getPrettyVersion()`.

## Changed

- [#3218](https://github.com/hyperf/hyperf/pull/3218) Set qos of amqp by default.
- [#3224](https://github.com/hyperf/hyperf/pull/3224) Upgrade `jean85/pretty-package-versions` to `^1.2|^2.0`, which support `composer 2.x`.

## Optimized

- [#3226](https://github.com/hyperf/hyperf/pull/3226) Run pagination count as subquery for group by and havings.

# v2.1.4 - 2021-01-25

## Fixed

- [#3165](https://github.com/hyperf/hyperf/pull/3165) Fixed `Hyperf\Database\Schema\MySqlBuilder::getColumnListing` does not works in `MySQL 8.0`.
- [#3174](https://github.com/hyperf/hyperf/pull/3174) Fixed bug that the where bindings will be replaced by not rigorous code.
- [#3179](https://github.com/hyperf/hyperf/pull/3179) Fixed json-rpc client failed to receive data when the target server restart.
- [#3189](https://github.com/hyperf/hyperf/pull/3189) Fixed kafka producer unusable in cluster setup.
- [#3191](https://github.com/hyperf/hyperf/pull/3191) Fixed rpc-client with pool transporter recv failed once when the server restart in the next request.

## Added

- [#3170](https://github.com/hyperf/hyperf/pull/3170) Added `FindNewerDriver` which is friendly with mac, linux and docker for watcher.
- [#3195](https://github.com/hyperf/hyperf/pull/3195) Added `retry_count` for JsonRpcPoolTransporter, the default retry count is 2.

## Optimized

- [#3169](https://github.com/hyperf/hyperf/pull/3169) Optimized code for `set_error_handler` of `ErrorExceptionHandler`, which expects `callable(int, string, string, int, array): bool`.
- [#3191](https://github.com/hyperf/hyperf/pull/3191) Optimized code for `hyperf/json-rpc`, try to reconnect the server when connection closed.

## Changed

- [#3174](https://github.com/hyperf/hyperf/pull/3174) Assert the binding values for database by default.

## Incubator

- [DAG](https://github.com/hyperf/dag-incubator) Directed Acyclic Graph.
- [RPN](https://github.com/hyperf/rpn-incubator) Reverse Polish Notation.

# v2.1.3 - 2021-01-18

## Fixed

- [#3070](https://github.com/hyperf/hyperf/pull/3070) Fixed `tracer` does not works in hyperf `v2.1`.
- [#3106](https://github.com/hyperf/hyperf/pull/3106) Fixed bug that call to a member function getArrayCopy() on null when the parent coroutine context destroyed.
- [#3108](https://github.com/hyperf/hyperf/pull/3108) Fixed routes will be replaced by another group when using `describe:routes` command.
- [#3118](https://github.com/hyperf/hyperf/pull/3118) Fixed bug that the config key of migrations is not correct.
- [#3126](https://github.com/hyperf/hyperf/pull/3126) Fixed bug that swoole v4.6 `SWOOLE_HOOK_SOCKETS` conflicts with jaeger tracing.
- [#3137](https://github.com/hyperf/hyperf/pull/3137) Fixed type hint error, when don't set `true` for `PDO::ATTR_PERSISTENT`.
- [#3141](https://github.com/hyperf/hyperf/pull/3141) Fixed `doctrine/dbal` does not works when using migration.

## Added

- [#3059](https://github.com/hyperf/hyperf/pull/3059) The merged attributes in the view component support attributes other than 'class'.
- [#3123](https://github.com/hyperf/hyperf/pull/3123) Added method `ComponentAttributeBag::has()` for `view-engine`.

# v2.1.2 - 2021-01-11

## Fixed

- [#3050](https://github.com/hyperf/hyperf/pull/3050) Fixed extra data saved twice when use `save()` after `increment()` with `extra`.
- [#3082](https://github.com/hyperf/hyperf/pull/3082) Fixed connection has already been bound to another coroutine when used in defer for `hyperf/db`.
- [#3084](https://github.com/hyperf/hyperf/pull/3084) Fixed `getRealPath` does not works in phar.
- [#3087](https://github.com/hyperf/hyperf/pull/3087) Fixed memory leak when using pipeline sometimes.
- [#3095](https://github.com/hyperf/hyperf/pull/3095) Fixed unexpected behavior for `ElasticsearchEngine::getTotalCount()` in `hyperf/scout`.

## Added

- [#2847](https://github.com/hyperf/hyperf/pull/2847) Added `hyperf/kafka` component.
- [#3066](https://github.com/hyperf/hyperf/pull/3066) Added method `ConnectionInterface::run(Closure $closure)` for `hyperf/db`.

## Optimized

- [#3046](https://github.com/hyperf/hyperf/pull/3046) Optimized `phar:build` for rewriting `scan_cacheable`.

## Changed

- [#3077](https://github.com/hyperf/hyperf/pull/3077) Reduced `league/flysystem` to `^1.0`.

# v2.1.1 - 2021-01-04

## Fixed

- [#3045](https://github.com/hyperf/hyperf/pull/3045) Fixed type hint error, when don't set `true` for `PDO::ATTR_PERSISTENT`.
- [#3047](https://github.com/hyperf/hyperf/pull/3047) Fixed bug that renew sid in all namespaces failed.
- [#3062](https://github.com/hyperf/hyperf/pull/3062) Fixed bug that parameters don't parsed correctly in grpc server.

## Added

- [#3052](https://github.com/hyperf/hyperf/pull/3052) Support collecting metrics while running command.
- [#3054](https://github.com/hyperf/hyperf/pull/3054) Support `Engine::close` protocol and improve error handling for `socketio-server`.

# v2.1.0 - 2020-12-28

## Dependencies Upgrade

- Upgraded `php` to `>=7.3`;
- Upgraded `phpunit/phpunit` to `^9.0`;
- Upgraded `guzzlehttp/guzzle` to `^6.0|^7.0`;
- Upgraded `vlucas/phpdotenv` to `^5.0`;
- Upgraded `endclothing/prometheus_client_php` to `^1.0`;
- Upgraded `twig/twig` to `^3.0`;
- Upgraded `jcchavezs/zipkin-opentracing` to `^0.2.0`;
- Upgraded `doctrine/dbal` to `^3.0`;
- Upgraded `league/flysystem` to `^1.0|^2.0`;

## Removed

- Removed deprecated property `$name` from `Hyperf\Amqp\Builder`.
- Removed deprecated method `consume` from `Hyperf\Amqp\Message\ConsumerMessageInterface`.
- Removed deprecated property `$running` from `Hyperf\AsyncQueue\Driver\Driver`.
- Removed deprecated method `parseParameters` from `Hyperf\HttpServer\CoreMiddleware`.
- Removed deprecated const `ON_WORKER_START` and `ON_WORKER_EXIT` from `Hyperf\Utils\Coordinator\Constants`.
- Removed deprecated method `get` from `Hyperf\Utils\Coordinator`.
- Removed config `rate-limit.php`, please use `rate_limit.php` instead.
- Removed useless class `Hyperf\Resource\Response\ResponseEmitter`.
- Removed component `hyperf/paginator` from database's dependencies.
- Removed method `stats` from `Hyperf\Utils\Coroutine\Concurrent`.

## Changed

- `Hyperf\Utils\Coroutine::parentId` which returns the parent coroutine ID
  * Returns 0 when running in the top level coroutine.
  * Throws RunningInNonCoroutineException when running in non-coroutine context
  * Throws CoroutineDestroyedException when the coroutine has been destroyed

- `Hyperf\Guzzle\CoroutineHandler`
  * Deleted method `execute`
  * Method `initHeaders` will return `$headers`, instead of assigning "$headers" directly to the client.
  * Deleted method `checkStatusCode`

- [#2720](https://github.com/hyperf/hyperf/pull/2720) Don't set `data_type` for `PDOStatement::bindValue`.
- [#2871](https://github.com/hyperf/hyperf/pull/2871) Use `(string) $body` instead of `$body->getContents()` for getting contents from `StreamInterface`, because method `getContents()` only returns the remaining contents in a string.
- [#2909](https://github.com/hyperf/hyperf/pull/2909) Allow setting repeated middlewares.
- [#2935](https://github.com/hyperf/hyperf/pull/2935) Changed the string format for default exception formatter.
- [#2979](https://github.com/hyperf/hyperf/pull/2979) Don't format `decimal` to `float` for command `gen:model` by default.

## Deprecated

- `Hyperf\AsyncQueue\Signal\DriverStopHandler` will be deprecated in v2.2, please use `Hyperf\Process\Handler\ProcessStopHandler` instead.
- `Hyperf\Server\SwooleEvent` will be deprecated in v3.0, please use `Hyperf\Server\Event` instead.

## Added

- [#2659](https://github.com/hyperf/hyperf/pull/2659) [#2663](https://github.com/hyperf/hyperf/pull/2663) Support `HttpServer` for [Swow](https://github.com/swow/swow).
- [#2671](https://github.com/hyperf/hyperf/pull/2671) Added `Hyperf\AsyncQueue\Listener\QueueHandleListener` which can record running logs for async-queue.
- [#2923](https://github.com/hyperf/hyperf/pull/2923) Added `Hyperf\Utils\Waiter` which can wait coroutine to end.
- [#3001](https://github.com/hyperf/hyperf/pull/3001) Added method `Hyperf\Database\Model\Collection::columns()`.
- [#3002](https://github.com/hyperf/hyperf/pull/3002) Added params `$depth` and `$flags` for `Json::decode` and `Json::encode`.

## Fixed

- [#2741](https://github.com/hyperf/hyperf/pull/2741) Fixed bug that process does not works in swow server.

## Optimized

- [#3009](https://github.com/hyperf/hyperf/pull/3009) Optimized code for prometheus which support `https` not only `http`.

# v2.0.25 - 2020-12-28

## Added

- [#3015](https://github.com/hyperf/hyperf/pull/3015) Added a mechanism to clean up garbage sid automatically for `socketio-server`.
- [#3030](https://github.com/hyperf/hyperf/pull/3030) Added method `ProceedingJoinPoint::getInstance()` to get instance which will be called by `AOP`.

## Optimized

- [#3011](https://github.com/hyperf/hyperf/pull/3011) Optimized `hyperf/tracer` which will log and tag exception in a span.

# v2.0.24 - 2020-12-21

## Fixed

- [#2978](https://github.com/hyperf/hyperf/pull/2980) Fixed bug that `hyperf/snowflake` is broken due to missing `hyperf/contract`.
- [#2983](https://github.com/hyperf/hyperf/pull/2983) Fixed swoole hook flags does works for co server.
- [#2993](https://github.com/hyperf/hyperf/pull/2993) Fixed `Arr::merge()` does not works when `$array1` is empty.

## Optimized

- [#2973](https://github.com/hyperf/hyperf/pull/2973) Support custom HTTP status code.
- [#2992](https://github.com/hyperf/hyperf/pull/2992) Optimized requirements for `hyperf/validation`.

# v2.0.23 - 2020-12-14

## Added

- [#2872](https://github.com/hyperf/hyperf/pull/2872) Added `hyperf/phar` component.

## Fixed

- [#2952](https://github.com/hyperf/hyperf/pull/2952) Fixed bug that nacos config center does not works in coroutine server.

## Changed

- [#2934](https://github.com/hyperf/hyperf/pull/2934) Changed config file `scout.php` which search engine index is used as the model index name by default.
- [#2958](https://github.com/hyperf/hyperf/pull/2958) Added NoneEngine as the default engine of view config.

## Optimized

- [#2951](https://github.com/hyperf/hyperf/pull/2951) Optimized code for model-cache, which will delete model cache only once, when using it in transaction.
- [#2953](https://github.com/hyperf/hyperf/pull/2953) Hide `Swoole\ExitException` trace message in command.
- [#2963](https://github.com/hyperf/hyperf/pull/2963) Removed `onStart` event from server default callbacks when the mode is `SWOOLE_BASE`.

# v2.0.22 - 2020-12-07

## Added

- [#2896](https://github.com/hyperf/hyperf/pull/2896) Support to define autoloaded view component classes and anonymous components.
- [#2921](https://github.com/hyperf/hyperf/pull/2921) Added method `count()` for `Parallel`.

## Fixed

- [#2913](https://github.com/hyperf/hyperf/pull/2913) Fixed memory leak when using `with()` for ORM.
- [#2915](https://github.com/hyperf/hyperf/pull/2915) Fixed bug that worker will be stopped when `onMessage` or `onClose` failed in websocket server.
- [#2927](https://github.com/hyperf/hyperf/pull/2927) Fixed validation rule `alpha_dash` does not support `int`.

## Changed

- [#2918](https://github.com/hyperf/hyperf/pull/2918) Don't allow to open `server.settings.daemonize` configuration when using `hyperf/watcher`.
- [#2930](https://github.com/hyperf/hyperf/pull/2930) Upgrade the minimum version of `php-amqplib` to `v2.9.2`.

## Optimized

- [#2931](https://github.com/hyperf/hyperf/pull/2931) Pass controller instance as first argument to method_exists function not the class namespace string.

# v2.0.21 - 2020-11-30

## Added

- [#2857](https://github.com/hyperf/hyperf/pull/2857) Support Consul ACL Token for Service Governance.
- [#2870](https://github.com/hyperf/hyperf/pull/2870) The publish option of `ConfigProvider` allows publish directory.
- [#2875](https://github.com/hyperf/hyperf/pull/2875) Added option `no-restart` for watcher.
- [#2883](https://github.com/hyperf/hyperf/pull/2883) Added options `--chunk` and `--column|c` into command `scout:import`.
- [#2891](https://github.com/hyperf/hyperf/pull/2891) Added config file for crontab.

## Fixed

- [#2874](https://github.com/hyperf/hyperf/pull/2874) Fixed `scan.ignore_annotations` does not works when using watcher.
- [#2878](https://github.com/hyperf/hyperf/pull/2878) Fixed config of nsqd does not works.

## Changed

- [#2851](https://github.com/hyperf/hyperf/pull/2851) Changed default engine of view config.

## Optimized

- [#2785](https://github.com/hyperf/hyperf/pull/2785) Optimized code for watcher.
- [#2861](https://github.com/hyperf/hyperf/pull/2861) Optimized guzzle coroutine handler which throw exception when the status code below zero.
- [#2868](https://github.com/hyperf/hyperf/pull/2868) Optimized code for guzzle sink, which support resource not only string.

# v2.0.20 - 2020-11-23

## Added

- [#2824](https://github.com/hyperf/hyperf/pull/2824) Added method `simplePaginate()` which return `PaginatorInterface` in `Hyperf\Database\Query\Builder`.

## Fixed

- [#2820](https://github.com/hyperf/hyperf/pull/2820) Fixed amqp consumer does not works when using fanout exchange.
- [#2831](https://github.com/hyperf/hyperf/pull/2831) Fixed bug that amqp connection always be closed by client.
- [#2848](https://github.com/hyperf/hyperf/pull/2848) Fixed database connection has already been bound to another coroutine when used in defer.

## Changed

- [#2824](https://github.com/hyperf/hyperf/pull/2824) Changed the result from `PaginatorInterface` to `LengthAwarePaginatorInterface` for method `paginate()` in `Hyperf\Database\Query\Builder`.

## Optimized

- [#2766](https://github.com/hyperf/hyperf/pull/2766) Safely finish spans in case of exception for tracer.
- [#2805](https://github.com/hyperf/hyperf/pull/2805) Optimized nacos process which can stop safely.
- [#2821](https://github.com/hyperf/hyperf/pull/2821) Optimized the exceptions thrown by `Json` and `Xml`.
- [#2827](https://github.com/hyperf/hyperf/pull/2827) Optimized `Hyperf\Server\ServerConfig` which return type of `__set` should be void.
- [#2839](https://github.com/hyperf/hyperf/pull/2839) Optimized comments for `Hyperf\Database\Schema\ColumnDefinition`.

# v2.0.19 - 2020-11-17

## Added

- [#2794](https://github.com/hyperf/hyperf/pull/2794) [#2802](https://github.com/hyperf/hyperf/pull/2802) Added `options.cookie_lifetime` for `hyperf/session`, you can use it to control the expire time for cookies.

## Fixed

- [#2783](https://github.com/hyperf/hyperf/pull/2783) Fixed nsq consumer does not works in coroutine style server.
- [#2788](https://github.com/hyperf/hyperf/pull/2788) Fixed call non-static method `__handlePropertyHandler()` statically in class proxy.
- [#2790](https://github.com/hyperf/hyperf/pull/2790) Fixed `BootProcessListener` of `config-etcd` does not works in coroutine style server.
- [#2803](https://github.com/hyperf/hyperf/pull/2803) Fixed response body does not exists when bad request.
- [#2807](https://github.com/hyperf/hyperf/pull/2807) Fixed Middleware does not work as expected when repeatedly configured.

## Optimized

- [#2750](https://github.com/hyperf/hyperf/pull/2750) Use elastic `index` instead of `type` for `searchableAs`, when the config of `index` is `null` or the elastic version is more than `7.0.0`.

# v2.0.18 - 2020-11-09

## Added

- [#2752](https://github.com/hyperf/hyperf/pull/2752) Support route `options` for `@AutoController` `@Controller` and `@Mapping`.

## Fixed

- [#2768](https://github.com/hyperf/hyperf/pull/2768) Fixed memory leak when websocket hande shake failed.
- [#2777](https://github.com/hyperf/hyperf/pull/2777) Fixed `$auth` does not support `null` for low version of `ext-redis`.
- [#2779](https://github.com/hyperf/hyperf/pull/2779) Fixed server start failed, when don't publish config of translation.

## Changed

- [#2765](https://github.com/hyperf/hyperf/pull/2765) Use `Hyperf\Utils\Coroutine::create()` instead of `Swoole\Coroutine::create()` for `Concurrent`.

## Optimzied

- [#2347](https://github.com/hyperf/hyperf/pull/2347) You can set `$waitTimeout` for `ConsumerMessage` to stop amqp consumer safely in coroutine style server.

# v2.0.17 - 2020-11-02

## Added

- [#2625](https://github.com/hyperf/hyperf/pull/2625) Added aspect `Hyperf\Tracer\Aspect\JsonRpcAspect` which support json-rpc for tracer component.
- [#2709](https://github.com/hyperf/hyperf/pull/2709) [#2733](https://github.com/hyperf/hyperf/pull/2733) Added `@mixin` into Model, you can use static methods friendly.
- [#2726](https://github.com/hyperf/hyperf/pull/2726) [#2733](https://github.com/hyperf/hyperf/pull/2733) Added option `--with-ide` which used to generate ide file.
- [#2737](https://github.com/hyperf/hyperf/pull/2737) Added [view-engine](https://github.com/hyperf/view-engine) component.

## Fixed

- [#2719](https://github.com/hyperf/hyperf/pull/2719) Fixed method `Arr::merge` does not works when `array1` does not constains the `$key`.
- [#2723](https://github.com/hyperf/hyperf/pull/2723) Fixed `Paginator::resolveCurrentPath` deos not works.

## Optimized

- [#2746](https://github.com/hyperf/hyperf/pull/2746) Only execute task in the worker process.

## Changed

- [#2728](https://github.com/hyperf/hyperf/pull/2728) The methods with prefix `__` will not be registered into service for `rpc-server`.

# v2.0.16 - 2020-10-26

## Added

- [#2682](https://github.com/hyperf/hyperf/pull/2682) Added method `getCacheTTL` for `CacheableInterface` which can control cache time each models.
- [#2696](https://github.com/hyperf/hyperf/pull/2696) Added swoole tracker leak tool.

## Fixed

- [#2680](https://github.com/hyperf/hyperf/pull/2680) Fixed Type error for `CastsValue`, because `$isSynchronized` don't have default value.
- [#2680](https://github.com/hyperf/hyperf/pull/2680) Fixed default value in `$items` will be replaced by `__construct` for `CastsValue`.
- [#2693](https://github.com/hyperf/hyperf/pull/2693) Fixed unexpected behavior in retry budget for `hyperf/retry`.
- [#2695](https://github.com/hyperf/hyperf/pull/2695) Fixed method `Container::define()` does not works when the class has been resolved.

## Optimized

- [#2611](https://github.com/hyperf/hyperf/pull/2611) Optimized `FindDriver` for watcher, you can use it in alpine image.
- [#2662](https://github.com/hyperf/hyperf/pull/2662) Optimized amqp consumer which can stop safely.
- [#2690](https://github.com/hyperf/hyperf/pull/2690) Optimized `tracer` which ensure span finished and flushed.

# v2.0.15 - 2020-10-19

## Added

- [#2654](https://github.com/hyperf/hyperf/pull/2654) Added method `Hyperf\Utils\Resource::from` which can convert `string` to `resource`.

## Fixed

- [#2634](https://github.com/hyperf/hyperf/pull/2634) [#2640](https://github.com/hyperf/hyperf/pull/2640) Fixed bug that `RedisSecondMetaGenerator` will generate the same meta.
- [#2639](https://github.com/hyperf/hyperf/pull/2639) Fixed exception will not be normalized for json-rpc.
- [#2643](https://github.com/hyperf/hyperf/pull/2643) Fixed undefined method unsearchable for `scout:flush`.

## Optimized

- [#2656](https://github.com/hyperf/hyperf/pull/2656) Optimized the response when parse parameters failed for json-rpc.

# v2.0.14 - 2020-10-12

## Added

- [#1172](https://github.com/hyperf/hyperf/pull/1172) Added `hyperf/scout`, a coroutine friendly version of `laravel/scout`.
- [#1868](https://github.com/hyperf/hyperf/pull/1868) Added sentinel mode for redis.
- [#1969](https://github.com/hyperf/hyperf/pull/1969) Added `hyperf/resource` and `hyperf/resource-grpc` which can format model to response easily.

## Fixed

- [#2594](https://github.com/hyperf/hyperf/pull/2594) Fixed crontab does not stops when using signal.
- [#2601](https://github.com/hyperf/hyperf/pull/2601) Fixed `@property` will be replaced by `@property-read` when the property has `getter` and `setter` at the same time.
- [#2607](https://github.com/hyperf/hyperf/pull/2607) [#2637](https://github.com/hyperf/hyperf/pull/2637) Fixed memory leak in `RetryAnnotationAspect`.
- [#2624](https://github.com/hyperf/hyperf/pull/2624) Fixed http client does not works when using guzzle 7.0 and curl hook for `hyperf/testing`.
- [#2632](https://github.com/hyperf/hyperf/pull/2632) [#2635](https://github.com/hyperf/hyperf/pull/2635) Fixed redis cluster does not support password.

## Optimized

- [#2603](https://github.com/hyperf/hyperf/pull/2603) Allow `whereNull` to accept array columns argument.

# v2.0.13 - 2020-09-28

## Added

- [#2445](https://github.com/hyperf/hyperf/pull/2445) Added trace info for `WhoopsExceptionHandler` when the header `accept` is `application/json`.
- [#2580](https://github.com/hyperf/hyperf/pull/2580) Support metadata for grpc client side.

## Fixed

- [#2559](https://github.com/hyperf/hyperf/pull/2559) Fixed the event does not works which caused by connecting with `query` for socketio-server.
- [#2565](https://github.com/hyperf/hyperf/pull/2565) Fixed proxy class generate keyword `parent::class` but the class scope has on parent.
- [#2578](https://github.com/hyperf/hyperf/pull/2578) Fixed event `AfterProcessHandle` won't be dispatched when throw exception in process.
- [#2582](https://github.com/hyperf/hyperf/pull/2582) Fixed redis connection has already been bound to another coroutine.
- [#2589](https://github.com/hyperf/hyperf/pull/2589) Fixed amqp consumer does not starts when using coroutine style server.
- [#2590](https://github.com/hyperf/hyperf/pull/2590) Fixed crontab does not works when using coroutine style server.

## Optimized

- [#2561](https://github.com/hyperf/hyperf/pull/2561) Optimized error message when close amqp connection failed.
- [#2584](https://github.com/hyperf/hyperf/pull/2584) Don't delete nacos service when server shutdown.

# v2.0.12 - 2020-09-21

## Added

- [#2512](https://github.com/hyperf/hyperf/pull/2512) Added `column_type` for `MySqlGrammar::compileColumnListing`.

## Fixed

- [#2490](https://github.com/hyperf/hyperf/pull/2490) Fixed streaming grpc-client does not works.
- [#2509](https://github.com/hyperf/hyperf/pull/2509) Fixed mutated attributes do not work in camel case for `hyperf/database`.
- [#2535](https://github.com/hyperf/hyperf/pull/2535) Fixed `@property` of mutated attribute will be replaced by morphTo for `gen:model`.
- [#2546](https://github.com/hyperf/hyperf/pull/2546) Fixed db connection don't destruct when using left join.

## Optimized

- [#2490](https://github.com/hyperf/hyperf/pull/2490) Optimized exception and test cases for grpc-client.

# v2.0.11 - 2020-09-14

## Added

- [#2455](https://github.com/hyperf/hyperf/pull/2455) Added method `Socket::getRequest` to retrieve psr7 request from socket for socketio-server.
- [#2459](https://github.com/hyperf/hyperf/pull/2459) Added `ReloadChannelListener` to reload timeout or failed channels automatically for async-queue.
- [#2463](https://github.com/hyperf/hyperf/pull/2463) Added optional visitor `ModelRewriteGetterSetterVisitor` for `gen:model`.
- [#2475](https://github.com/hyperf/hyperf/pull/2475) Added `throwable` to the end of arguments of fallback for `retry` component.

## Fixed

- [#2464](https://github.com/hyperf/hyperf/pull/2464) Fixed method `fill` does not works for camel case model.
- [#2478](https://github.com/hyperf/hyperf/pull/2478) Fixed `Sender::check` does not works when the checked fd not belong to websocket.
- [#2488](https://github.com/hyperf/hyperf/pull/2488) Fixed `beginTransaction` failed when the pdo is `null`.

## Optimized

- [#2461](https://github.com/hyperf/hyperf/pull/2461) Optimized the http route observer which you can observe any one not only `http` for `reactive-x`.
- [#2465](https://github.com/hyperf/hyperf/pull/2465) Optimized the fallback of `FallbackRetryPolicy` which support `class@method`, the class will be get from Container.

## Changed

- [#2492](https://github.com/hyperf/hyperf/pull/2492) Adjust event sequence to ensure sid is added to room for socketio-server.

# v2.0.10 - 2020-09-07

## Added

- [#2411](https://github.com/hyperf/hyperf/pull/2411) Added method `Hyperf\Database\Query\Builder::forPageBeforeId` for database.
- [#2420](https://github.com/hyperf/hyperf/pull/2420) [#2426](https://github.com/hyperf/hyperf/pull/2426) Added option `enable-event-dispatcher` to initialize EventDispatcher for command.
- [#2433](https://github.com/hyperf/hyperf/pull/2433) Added support for gRPC Server routing definition by anonymous functions.
- [#2441](https://github.com/hyperf/hyperf/pull/2441) Added some setters for `SocketIO`.

## Fixed

- [#2427](https://github.com/hyperf/hyperf/pull/2427) Fixed model event dispatcher does not works for `Pivot` and `MorphPivot`.
- [#2443](https://github.com/hyperf/hyperf/pull/2443) Fixed traceid does not exists when using coroutine handler.
- [#2449](https://github.com/hyperf/hyperf/pull/2449) Fixed apollo config file name error.

## Optimized

- [#2429](https://github.com/hyperf/hyperf/pull/2429) Optimized error message when does not set the value of `@var` for `@Inject`.
- [#2438](https://github.com/hyperf/hyperf/pull/2438) Optimized code for deleting model cache when model deleted or saved in transaction.

# v2.0.9 - 2020-08-31

## Added

- [#2331](https://github.com/hyperf/hyperf/pull/2331) Added auth api for [hyperf/nacos](https://github.com/hyperf/nacos) component.
- [#2331](https://github.com/hyperf/hyperf/pull/2331) Added config `nacos.enable` to control the [hyperf/nacos](https://github.com/hyperf/nacos) component.
- [#2331](https://github.com/hyperf/hyperf/pull/2331) Added array merge mode for [hyperf/nacos](https://github.com/hyperf/nacos) component.
- [#2377](https://github.com/hyperf/hyperf/pull/2377) Added `ts` header for gRPC request of client, compatible with Node.js gRPC server etc.
- [#2384](https://github.com/hyperf/hyperf/pull/2384) Added global function `optional()` to create `Hyperf\Utils\Optional` object or for more convenient way to use.

## Fixed

- [#2331](https://github.com/hyperf/hyperf/pull/2331) Fixed exception thrown when the service or config was not found for [hyperf/nacos](https://github.com/hyperf/nacos) component.
- [#2356](https://github.com/hyperf/hyperf/pull/2356) [#2368](https://github.com/hyperf/hyperf/pull/2368) Fixed `server:start` failed, when the config of pid_file changed.
- [#2358](https://github.com/hyperf/hyperf/pull/2358) Fixed validation rule `digits` does not support `int`.

## Optimized

- [#2359](https://github.com/hyperf/hyperf/pull/2359) Optimized custom process which stop friendly when running in coroutine server.
- [#2363](https://github.com/hyperf/hyperf/pull/2363) Optimized [hyperf/di](https://github.com/hyperf/di) component which is no need to depend on [hyperf/config](https://github.com/hyperf/config) component.
- [#2373](https://github.com/hyperf/hyperf/pull/2373) Optimized the exception handler which add `content-type` header automatically by default for [hyperf/validation](https://github.com/hyperf/validation) component.

# v2.0.8 - 2020-08-24

## Added

- [#2334](https://github.com/hyperf/hyperf/pull/2334) Added method `Arr::merge` to merge array more friendly than `array_merge_recursive`.
- [#2335](https://github.com/hyperf/hyperf/pull/2335) Added `Hyperf/Utils/Optional` which accepts any argument and allows you to access properties or call methods on that object.
- [#2336](https://github.com/hyperf/hyperf/pull/2336) Added `RedisNsqAdapter` which publish message through nsq for `socketio-server`.

## Fixed

- [#2338](https://github.com/hyperf/hyperf/pull/2338) Fixed filesystem does not works when using s3 adapter.
- [#2340](https://github.com/hyperf/hyperf/pull/2340) Fixed `__FUNCTION__` and `__METHOD__` magic constants does work in closure of aop proxy class

## Optimized

- [#2319](https://github.com/hyperf/hyperf/pull/2319) Optimized the `ResolverDispatcher` which is friendly for circular dependencies.

## Dependencies Upgrade

- Upgraded `markrogoyski/math-php` requirement from `^0.49.0` to `^1.2.0`

# v2.0.7 - 2020-08-17

## Added

- [#2307](https://github.com/hyperf/hyperf/pull/2307) [#2312](https://github.com/hyperf/hyperf/pull/2312) Added NSQD HTTP API client support for [hyperf/nsq](https://github.com/hyperf/nsq) component.

## Fixed

- [#2275](https://github.com/hyperf/hyperf/pull/2275) Fixed bug that fetch process blocking for config center.
- [#2276](https://github.com/hyperf/hyperf/pull/2276) Fixed bug that the config is cleared when the config is not modified in apollo.
- [#2280](https://github.com/hyperf/hyperf/pull/2280) Fixed bug that interface methods will be rewriten by aop.
- [#2281](https://github.com/hyperf/hyperf/pull/2281) Fixed `co::create` failed in non-coroutine environment for `hyperf/signal`.
- [#2304](https://github.com/hyperf/hyperf/pull/2304) Fixed dead cycle when del sid for socketio memory adapter.
- [#2309](https://github.com/hyperf/hyperf/pull/2309) Fixed JsonRpcHttpTransporter cannot set the custom timeout property.

# v2.0.6 - 2020-08-10

## Added

- [#2125](https://github.com/hyperf/hyperf/pull/2125) Added Jet component, Jet is a unification model RPC Client, built-in JSONRPC protocol, available to running in ALL PHP environments, including PHP-FPM and Swoole/Hyperf environments.

## Fixed

- [#2236](https://github.com/hyperf/hyperf/pull/2236) Fixed bug that select node failed when using `loadBalancer` for nacos.
- [#2242](https://github.com/hyperf/hyperf/pull/2242) Fixed bug that collect more than once time when using watcher.

# v2.0.5 - 2020-08-03

## Added

- [#2001](https://github.com/hyperf/hyperf/pull/2001) Added `$signature` to init command easily.
- [#2204](https://github.com/hyperf/hyperf/pull/2204) Added `$concurrent` for function `parallel`.

## Fixed

- [#2210](https://github.com/hyperf/hyperf/pull/2210) Fixed bug that open event won't be executed after handshake right now.
- [#2214](https://github.com/hyperf/hyperf/pull/2214) Fixed bug that close event won't be executed when close the connection by websocket server.
- [#2218](https://github.com/hyperf/hyperf/pull/2218) Fixed bug that sender does not works for coroutine server.
- [#2227](https://github.com/hyperf/hyperf/pull/2227) Fixed context won't be destroyed when accept keepalive connection for co server.

## Optimized

- [#2193](https://github.com/hyperf/hyperf/pull/2193) Optimized the scan accuracy for `Hyperf\Watcher\Driver\FindDriver`.
- [#2232](https://github.com/hyperf/hyperf/pull/2232) Optimized eager load when the type is `In` or `InRaw` for model-cache.

# v2.0.4 - 2020-07-27

## Added

- [#2144](https://github.com/hyperf/hyperf/pull/2144) Added filed `$result` for `QueryExecuted`.
- [#2158](https://github.com/hyperf/hyperf/pull/2158) Added route options to route handler.
- [#2162](https://github.com/hyperf/hyperf/pull/2162) Added `Hyperf\Watcher\Driver\FindDriver` for `hyperf/watcher`.
- [#2169](https://github.com/hyperf/hyperf/pull/2169) Added `session.options.domain` for `hyperf/session` to change the domain which get from request.
- [#2174](https://github.com/hyperf/hyperf/pull/2174) Added `ModelRewriteTimestampsVisitor` to rewrite `$timestamps` based on `created_at` and `updated_at` for Model.
- [#2175](https://github.com/hyperf/hyperf/pull/2175) Added `ModelRewriteSoftDeletesVisitor` to insert or remove `SoftDeletes` based on `deleted_at` for Model.
- [#2176](https://github.com/hyperf/hyperf/pull/2176) Added `ModelRewriteKeyInfoVisitor` to rewrite `$incrementing` `$primaryKey` and `$keyType` for Model.

## Fixed

- [#2149](https://github.com/hyperf/hyperf/pull/2149) Fixed bug that custom processes cannot fetch config from nacos.
- [#2159](https://github.com/hyperf/hyperf/pull/2159) Fixed fatal exception caused by exist file when using `gen:migration`.

## Optimized

- [#2043](https://github.com/hyperf/hyperf/pull/2043) Throw an exception when none of the scan directories exists.
- [#2182](https://github.com/hyperf/hyperf/pull/2182) Don't record the close message when the server is not websocket server.

# v2.0.3 - 2020-07-20

## Added

- [#1554](https://github.com/hyperf/hyperf/pull/1554) Added `hyperf/nacos` component.
- [#2082](https://github.com/hyperf/hyperf/pull/2082) Added `SIGINT` listened by `Hyperf\Signal\Handler\WorkerStopHandler`.
- [#2097](https://github.com/hyperf/hyperf/pull/2097) Added TencentCloud COS for `hyperf/filesystem`.
- [#2122](https://github.com/hyperf/hyperf/pull/2122) Added `\Hyperf\Snowflake\Concern\HasSnowflake` Trait to integrate `hyperf/snowflake` and database models.

## Fixed

- [#2017](https://github.com/hyperf/hyperf/pull/2017) Fixed when prometheus using the redis record, an error is reported during the rendering of data due to the change in the number of label.
- [#2117](https://github.com/hyperf/hyperf/pull/2117) Fixed `@Inject` will be useless sometimes when using `server:watch`.
- [#2123](https://github.com/hyperf/hyperf/pull/2123) Fixed bug that `redis::call` will be recorded twice.
- [#2139](https://github.com/hyperf/hyperf/pull/2139) Fixed bug that `ValidationMiddleware` will throw exception in websocket.
- [#2140](https://github.com/hyperf/hyperf/pull/2140) Fixed a case where session are not saved when exception occurs.

## Optimized

- [#2080](https://github.com/hyperf/hyperf/pull/2080) Optimized the type of `$perPage` from `int` to `?int` for method `Hyperf\Database\Model\Builder::paginate`.
- [#2110](https://github.com/hyperf/hyperf/pull/2110) Don't kill `SIGTERM` if the process not exists for `hyperf/watcher`.
- [#2116](https://github.com/hyperf/hyperf/pull/2116) Optimized requirement for `hyperf/di`.
- [#2121](https://github.com/hyperf/hyperf/pull/2121) Replaced the default `@property` if user redeclare it when using `gen:model`.
- [#2129](https://github.com/hyperf/hyperf/pull/2129) Optimized the exception message when the response json encoding failed.

# v2.0.2 - 2020-07-13

## Added

- [#2018](https://github.com/hyperf/hyperf/pull/2018) Make prometheus use redis to store data to support cluster mode

## Fixed

- [#1898](https://github.com/hyperf/hyperf/pull/1898) Fixed crontab rule `$min-$max` parsing errors.
- [#2037](https://github.com/hyperf/hyperf/pull/2037) Fixed bug that tcp server running in only one coroutine.
- [#2051](https://github.com/hyperf/hyperf/pull/2051) Fixed `hyperf.pid` won't be created in coroutine server.
- [#2055](https://github.com/hyperf/hyperf/pull/1695) Fixed guzzle auto add `Expect: 100-Continue` header when put a large file.
- [#2059](https://github.com/hyperf/hyperf/pull/2059) Fixed redis reconnection bug in socket.io server.
- [#2067](https://github.com/hyperf/hyperf/pull/2067) Fixed bug that syntax parse error will cause worker exceptions for `hyperf/watcher`.
- [#2085](https://github.com/hyperf/hyperf/pull/2085) Fixed bug in RetryFalsy Annotation that leads to retrying truthy results.
- [#2089](https://github.com/hyperf/hyperf/pull/2089) Fixed class of command won't be loaded after `gen:command`.
- [#2093](https://github.com/hyperf/hyperf/pull/2093) Fixed type error for command `vendor:publish`.

## Added

- [#1860](https://github.com/hyperf/hyperf/pull/1860) Added `OnWorkerExit` callback by default for server.
- [#2042](https://github.com/hyperf/hyperf/pull/2042) Added `ScanFileDriver` to watch file changes for `hyperf/watcher`.
- [#2054](https://github.com/hyperf/hyperf/pull/2054) Added eager load relation for model-cache.

## Optimized

- [#2049](https://github.com/hyperf/hyperf/pull/2049) Optimized stdout when server restart for `hyperf/watcher`.
- [#2090](https://github.com/hyperf/hyperf/pull/2090) Adapte original response object for `hyperf/session`.

## Changed

- [#2031](https://github.com/hyperf/hyperf/pull/2031) The code of constants only support `int` and `string`.
- [#2065](https://github.com/hyperf/hyperf/pull/2065) Changed `Hyperf\WebSocketServer\Sender` which only support `push` and `disconnect`.
- [#2100](https://github.com/hyperf/hyperf/pull/2100) Upgrade `doctrine/inflector` to `^2.0` for `hyperf/utils`.

## Removed

- [#2065](https://github.com/hyperf/hyperf/pull/2065) Removed methods `send` `sendto` and `close` from `Hyperf\WebSocketServer\Sender`.

# v2.0.1 - 2020-07-02

## Added

- [#1934](https://github.com/hyperf/hyperf/pull/1934) Added command `gen:constant`.
- [#1982](https://github.com/hyperf/hyperf/pull/1982) Added watcher component.

## Fixed

- [#1952](https://github.com/hyperf/hyperf/pull/1952) Fixed bug that migration will be created although class already exists.
- [#1960](https://github.com/hyperf/hyperf/pull/1960) Fixed `Hyperf\HttpServer\ResponseEmitter::isMethodsExists()` method does not works as expected.
- [#1961](https://github.com/hyperf/hyperf/pull/1961) Fixed start failed when `config/autoload/aspects.php` does not exists.
- [#1964](https://github.com/hyperf/hyperf/pull/1964) Fixed http status code 500 caused by empty body.
- [#1965](https://github.com/hyperf/hyperf/pull/1965) Fixed the wrong http code when `initRequestAndResponse` failed.
- [#1968](https://github.com/hyperf/hyperf/pull/1968) Fixed aspect does not work as expected after `aspects.php` is edited.
- [#1985](https://github.com/hyperf/hyperf/pull/1985) Fixed global_imports do not work when the aliases are not all lowercase letters.
- [#1990](https://github.com/hyperf/hyperf/pull/1990) Fixed `@Inject` does not work when the parent class has the same property.
- [#2019](https://github.com/hyperf/hyperf/pull/2019) Fixed bug that `gen:model` generate property failed, when used `morphTo` or `where`.
- [#2026](https://github.com/hyperf/hyperf/pull/2026) Fixed invalid lazy proxy generation when magic methods are used.

## Changed

- [#1986](https://github.com/hyperf/hyperf/pull/1986) Changed exit_code `0` to `SIGTERM` when swoole short name do not set disable.

## Optimized

- [#1959](https://github.com/hyperf/hyperf/pull/1959) Make ClassLoader easier to be extended.
- [#2002](https://github.com/hyperf/hyperf/pull/2002) Support aop in trait when php version >= `7.3`.

# v2.0.0 - 2020-06-22

## Major Changes

1. Refactor [hyperf/di](https://github.com/hyperf/di) component, in particular, AOP and Annotation Scanner are optimized, in v2.0, the component use a brand new loading mechanism to provided an incredible AOP function.
    1. The most significant functional differences compared to v1.x is that you can cut into any classes in any ways with Aspect. For example, in v1.x, you can only use AOP in the class instance that created by Hyperf DI container, you cannot cut into the class instance that created by `new` identifier. But now, in v2.0, it is available. But there is still has an exception, the classes that used in bootstrap stage still cannot works.
    2. In v1.x, the AOP ONLY available for the normal classes, not for Final class that cannot be inherited by a subclass. But now, in v2.0. it is available.
    3. In v1.x, you cannot use the property value that marked by `@Inject` or `@Value` annotation in the constructor of current class. But now, in v2.0, it is available.
    4. In v1.x, you can only use `@Inject` and `@Value` annotation in the class instance that created by Hyperf DI container. But now, in v2.0, it is available in any ways, such as the class instance that created by `new` identifier.
    5. In v1.x, you have to define the full namespace of Annotation class when you use the Annotation. But now, in v2.0, the component provide a global import mechanism, you cloud define an alias for Annotation to use the Annotation directly without using the namespace. For example, you cloud define `@Inject` annotation in any class without define `use Hyperf\Di\Annotation\Inject;`.
    6. In v1.x, the proxy class that created by the DI container is a subclass of the target class, this mechanism will cause the magic constant will return the value of proxy class but not original class, such as `__CLASS__`. But now, in v2.0, the proxy class will keep the same structure with the original class, will not change the class name or the class structure.
    7. In v1.x, the proxy class will not re-generate when the proxy file exists even the code of the proxy class changed, this strategy will improve the time-consuming of scan, but at the same time, this will lead to a certain degree of development inconvenience. And now, in v2.0, the file cache of proxy class will generated according to the code content of the proxy class, this changes will reduces the mental burden of development.
    8. Add `priority` parameter for Aspect, now you could define `priority` in Aspect class by class property or annotation property, to manage the order of the aspects.
    9. In v1.x, you can only define an Aspect class by `@Aspect` annotation, you cannot define the Aspect class by configuration file. But now, in v2.0, it is available to define the Aspect class by configuration file or ConfigProvider.
    10. In v1.x, you have to add `Hyperf\Di\Listener\LazyLoaderBootApplicationListener` to enable lazy loading. In 2.0, lazy loading can be used directly. This listener is therefore removed.
    11. Added `annotations.scan.class_map` configuration, now you could replace any content of class dynamically above the autoload rules.

## Dependencies Upgrade

- Upgraded `ext-swoole` to `>=4.5`;
- Upgraded `psr/event-dispatcher` to `^1.0`;
- Upgraded `monolog/monolog` to `^2.0`;
- Upgraded `phpstan/phpstan` to `^0.12.18`;
- Upgraded `vlucas/phpdotenv` to `^4.0`;
- Upgraded `symfony/finder` to `^5.0`;
- Upgraded `symfony/event-dispatcher` to `^5.0`;
- Upgraded `symfony/console` to `^5.0`;
- Upgraded `symfony/property-access` to `^5.0`;
- Upgraded `symfony/serializer` to `^5.0`;
- Upgraded `elasticsearch/elasticsearch` to `^7.0`;

## Removed

- Removed `Hyperf\Di\Aop\AstCollector`;
- Removed `Hyperf\Di\Aop\ProxyClassNameVisitor`;
- Removed `Hyperf\Di\Listener\LazyLoaderBootApplicationListener`
- Removed method `dispatch(...$params)` from `Hyperf\Dispatcher\AbstractDispatcher`
- Removed mapping for `Hyperf\Contract\NormalizerInterface => Hyperf\Utils\Serializer\SymfonyNormalizer` from `ConfigProvider` in utils.
- Removed the typehint of `$server` parameter of `Hyperf\Contract\OnOpenInterface`、`Hyperf\Contract\OnCloseInterface`、`Hyperf\Contract\OnMessageInterface`、`Hyperf\Contract\OnReceiveInterface`;

## Added

- [#992](https://github.com/hyperf/hyperf/pull/992) Added ReactiveX component.
- [#1245](https://github.com/hyperf/hyperf/pull/1245) Added Annotation `ExceptionHandler`.
- [#1245](https://github.com/hyperf/hyperf/pull/1245) Exception handler's config and annotation support priority.
- [#1819](https://github.com/hyperf/hyperf/pull/1819) Added `hyperf/signal` component.
- [#1844](https://github.com/hyperf/hyperf/pull/1844) Support type `\DateInterval` for `ttl` in `model-cache`.
- [#1855](https://github.com/hyperf/hyperf/pull/1855) Added `ConstantFrequency` to flush one connection, when it is idle connection for the interval of time.
- [#1871](https://github.com/hyperf/hyperf/pull/1871) Added `sink` for guzzle.
- [#1805](https://github.com/hyperf/hyperf/pull/1805) Added Coroutine Server.
  - Changed method `bind(Server $server)` to `bind($server)` in `Hyperf\Contract\ProcessInterface`.
  - Changed method `isEnable()` to `isEnable($server)` in `Hyperf\Contract\ProcessInterface`
  - Process mode of config-center, crontab, metric, comsumers of MQ can not running in coroutine server.
  - Change the life-cycle of `Hyperf\AsyncQueue\Environment`, can only applies in the current coroutine, not the whole current process.
  - Coroutine Server does not support task mechanism.

- [#1877](https://github.com/hyperf/hyperf/pull/1877) Support to use typehint of property on PHP 8 to replace `@var` when using `@Inject` annotation, for example:

```
class Example {
    /**
    * @Inject
    */
    private ExampleService $exampleService;
}
```

- [#1890](https://github.com/hyperf/hyperf/pull/1890) Added `Hyperf\HttpServer\ResponseEmitter` class to emit any PSR-7 response object with Swoole server, and extracted `Hyperf\Contract\ResponseEmitterInterface`.
- [#1890](https://github.com/hyperf/hyperf/pull/1890) Added `getTrailers()` and `getTrailer(string $key)` and `withTrailer(string $key, $value)` methods for `Hyperf\HttpMessage\Server\Response`.
- [#1920](https://github.com/hyperf/hyperf/pull/1920) Added method `Hyperf\WebSocketServer\Sender::close(int $fd, bool $reset = null)`.

## Fixed

- [#1825](https://github.com/hyperf/hyperf/pull/1825) Fixed `TypeError` for `StartServer::execute`.
- [#1854](https://github.com/hyperf/hyperf/pull/1854) Fixed `is_resource` does not works when use `Runtime::enableCoroutine()` privately in filesystem.
- [#1900](https://github.com/hyperf/hyperf/pull/1900) Fixed caster decimal of Model does not work.
- [#1917](https://github.com/hyperf/hyperf/pull/1917) Fixed `Request::isXmlHttpRequest` does not work.

## Changed

- [#705](https://github.com/hyperf/hyperf/pull/705) Uniformed the handling of HTTP exceptions, now unified throwing a `Hyperf\HttpMessage\Exception\HttpException` exception class to replace the way of direct response in `Dispatcher`, also provided an `Hyperf\HttpServer\Exception\Handler\ httptionHandler` ExceptionHandler to handle these HTTP Exception;
- [#1846](https://github.com/hyperf/hyperf/pull/1846) Don't auto change the impl for `Hyperf\Contract\NormalizerInterface` when you require `symfony/serialize`. You can added dependiencies below to use symfony serializer.
```php
use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

- [#1924](https://github.com/hyperf/hyperf/pull/1924) Changed `Hyperf\GrpcClient\BaseClient` methods `simpleRequest, getGrpcClient, clientStreamRequest` to `_simpleRequest, _getGrpcClient, _clientStreamRequest`.

## Removed

- [#1890](https://github.com/hyperf/hyperf/pull/1890) Removed `Hyperf\Contract\Sendable` interface and all implementations of it.
- [#1905](https://github.com/hyperf/hyperf/pull/1905) Removed config `config/server.php`, you can merge it into `config/config.php`.

## Optimized

- [#1793](https://github.com/hyperf/hyperf/pull/1793) Socket.io server now only dispatch connect/disconnect events in onOpen and onClose. Also upgrade some class members from private to protected, so users can hack them.
- [#1848](https://github.com/hyperf/hyperf/pull/1848) Auto generate rpc client code when server start and the interface is changed.
- [#1863](https://github.com/hyperf/hyperf/pull/1863) Support async-queue stop safely.
- [#1896](https://github.com/hyperf/hyperf/pull/1896) Keys will be merged when different constants use the same code.



# v1.1.32 - 2020-05-21

## Fixed

- [#1734](https://github.com/hyperf/hyperf/pull/1734) Fixed the bug that the morph association is empty and cannot be queried.
- [#1739](https://github.com/hyperf/hyperf/pull/1739) Fixed the wrong bitwise operator in oss hook.
- [#1743](https://github.com/hyperf/hyperf/pull/1743) Fixed the wrong `refId` for `grafana.json`.
- [#1748](https://github.com/hyperf/hyperf/pull/1748) Fixed `concurrent.limit` does not works when using another pool.
- [#1750](https://github.com/hyperf/hyperf/pull/1750) Fixed the incorrent number of current connections when close failed.
- [#1754](https://github.com/hyperf/hyperf/pull/1754) Fixed the wrong start info for base server.
- [#1764](https://github.com/hyperf/hyperf/pull/1764) Fixed datetime validate failed when the value is null.
- [#1769](https://github.com/hyperf/hyperf/pull/1769) Fixed a notice when client initiate disconnects in `socketio-server`.

## Added

- [#1724](https://github.com/hyperf/hyperf/pull/1724) Added `Model::orWhereHasMorph` ,`Model::whereDoesntHaveMorph` and `Model::orWhereDoesntHaveMorph`.
- [#1741](https://github.com/hyperf/hyperf/pull/1741) Added `Hyperf\Command\Command::choiceMultiple(): array` method, because the return type of `choice` method is `string`, so the methed cannot handle the multiple selections, even though setted `$multiple` argument.
- [#1742](https://github.com/hyperf/hyperf/pull/1742) Added Custom Casts for model.
  - Added interface `Castable`, `CastsAttributes` and `CastsInboundAttributes`.
  - Added `Model\Builder::withCasts`.
  - Added `Model::loadMorph`, `Model::loadMorphCount` and `Model::syncAttributes`.

# v1.1.31 - 2020-05-14

## Added

- [#1723](https://github.com/hyperf/hyperf/pull/1723) Added filp/whoops integration in hyperf/exception-handler component.
- [#1730](https://github.com/hyperf/hyperf/pull/1730) Added shortcut `-R` of `--refresh-fillable` for command `gen:model`.

## Fixed

- [#1696](https://github.com/hyperf/hyperf/pull/1696) Fixed `Context::copy` does not works when use keys.
- [#1708](https://github.com/hyperf/hyperf/pull/1708) [#1718](https://github.com/hyperf/hyperf/pull/1718) Fixed a series of issues for `hyperf/socketio-server`.

## Optimized

- [#1710](https://github.com/hyperf/hyperf/pull/1710) Don't set process title in Darwin OS.

# v1.1.30 - 2020-05-07

## Added

- [#1616](https://github.com/hyperf/hyperf/pull/1616) Added `morphWith` and `whereHasMorph` for hyperf/database component.
- [#1651](https://github.com/hyperf/hyperf/pull/1651) Added socket.io-server component.
- [#1666](https://github.com/hyperf/hyperf/pull/1666) [#1669](https://github.com/hyperf/hyperf/pull/1669) Added support for AMQP RPC mode.

## Fixed

- [#1682](https://github.com/hyperf/hyperf/pull/1682) Fixed the connection pool does not works in JSONRPC pool transporter.
- [#1683](https://github.com/hyperf/hyperf/pull/1683) Fixed JSONRPC client connection reset failed, when the connection was closed in context.

## Optimized

- [#1670](https://github.com/hyperf/hyperf/pull/1670) Optimized a meaningless redis delete instruction for cache component.

# v1.1.28 - 2020-04-30

## Added

- [#1645](https://github.com/hyperf/hyperf/pull/1645) Added parameter injection support for closure route.
- [#1647](https://github.com/hyperf/hyperf/pull/1647) Added `Hyperf\ModelCache\Handler\RedisStringHandler` for [hyperf/model-cache](https://github.com/hyperf/model-cache) component, store the cache data in string type.
- [#1654](https://github.com/hyperf/hyperf/pull/1654) Added `Hyperf\View\Exception\RenderException` to rethrow render exceptions in view.

## Fixed

- [#1639](https://github.com/hyperf/hyperf/pull/1639) Fixed bug that the unhealthy node will be got from `consul`.
- [#1641](https://github.com/hyperf/hyperf/pull/1641) Fixed request exception will be thrown when the JSONRPC result is null.
- [#1641](https://github.com/hyperf/hyperf/pull/1641) Fixed service health check does not works for `jsonrpc-tcp-length-check` protocol.
- [#1650](https://github.com/hyperf/hyperf/pull/1650) Fixed bug that command `describe:routes` will show the wrong list.
- [#1655](https://github.com/hyperf/hyperf/pull/1655) Fixed `MysqlProcessor::processColumns` does not work when the MySQL server is 8.0 version.

## Optimized

- [#1636](https://github.com/hyperf/hyperf/pull/1636) Optimized `co-phpunit` do not broken in coroutine environment, when cases failed.

# v1.1.27 - 2020-04-23

## Added

- [#1575](https://github.com/hyperf/hyperf/pull/1575) Added document of property with relation, scope and attributes.
- [#1586](https://github.com/hyperf/hyperf/pull/1586) Added conflict of symfony/event-dispatcher which < 4.3.
- [#1597](https://github.com/hyperf/hyperf/pull/1597) Added `maxConsumption` for amqp consumer.
- [#1603](https://github.com/hyperf/hyperf/pull/1603) Added WebSocket Context to save data from the same fd.

## Fixed

- [#1553](https://github.com/hyperf/hyperf/pull/1553) Fixed the rpc client do not work, when jsonrpc server register the same service to consul with jsonrpc and jsonrpc-http protocol.
- [#1589](https://github.com/hyperf/hyperf/pull/1589) Fixed unsafe file locks in coroutines.
- [#1607](https://github.com/hyperf/hyperf/pull/1607) Fixed bug that the return value of function `go` is not adaptive with `swoole`.
- [#1624](https://github.com/hyperf/hyperf/pull/1624) Fixed `describe:routes` failed when router handler is `Closure`.

# v1.1.26 - 2020-04-16

## Added

- [#1578](https://github.com/hyperf/hyperf/pull/1578) Support `getStream` method in `UploadedFile.php`.

## Added

- [#1603](https://github.com/hyperf/hyperf/pull/1603) Added connection level context for `hyperf/websocket-server`.

## Fixed

- [#1563](https://github.com/hyperf/hyperf/pull/1563) Fixed crontab's `onOneServer` option not resetting mutex on shutdown.
- [#1565](https://github.com/hyperf/hyperf/pull/1565) Reset transaction level to zero, when reconnent to mysql server.
- [#1572](https://github.com/hyperf/hyperf/pull/1572) Fixed parent class does not exists in `Hyperf\GrpcServer\CoreMiddleware`.
- [#1577](https://github.com/hyperf/hyperf/pull/1577) Fixed `describe:routes` command's `server` option not take effect.
- [#1579](https://github.com/hyperf/hyperf/pull/1579) Fixed `migrate:refresh` command's `step` is int.

## Changed

- [#1560](https://github.com/hyperf/hyperf/pull/1560) Changed functions of file to `filesystem` for `FileSystemDriver` in `hyperf/cache`.
- [#1568](https://github.com/hyperf/hyperf/pull/1568) Changed `\Redis` to `RedisProxy` for `RedisDriver` in `async-queue`.

# v1.1.25 - 2020-04-09

## Fixed

- [#1532](https://github.com/hyperf/hyperf/pull/1532) Fixed interface 'Symfony\Component\EventDispatcher\EventDispatcherInterface' not found.

# v1.1.24 - 2020-04-09

## Added

- [#1501](https://github.com/hyperf/hyperf/pull/1501) Bridged Symfony command events to Hyperf event dispatcher.
- [#1502](https://github.com/hyperf/hyperf/pull/1502) Added `maxAttempts` parameter for `Hyperf\AsyncQueue\Annotation\AsyncQueueMessage` annotation to control the maximum retry time of job.
- [#1510](https://github.com/hyperf/hyperf/pull/1510) Added `Hyperf/Utils/CoordinatorManager` to better handling of graceful start and graceful stop.
- [#1517](https://github.com/hyperf/hyperf/pull/1517) Added support lazy-loading over interface inheritance and abstract method inheritance etc.
- [#1529](https://github.com/hyperf/hyperf/pull/1529) Handled SameSite property of response cookies.

## Fixed

- [#1494](https://github.com/hyperf/hyperf/pull/1494) Ignore `@mixin` annotation in redis component.
- [#1499](https://github.com/hyperf/hyperf/pull/1499) Fixed dynamic parameter does not work after requiring translation for `hyperf/constants`.
- [#1504](https://github.com/hyperf/hyperf/pull/1504) Fixed the proxy client of RPC does not handle the Nullable return type.
- [#1507](https://github.com/hyperf/hyperf/pull/1507) Fixed consul catalog register method, modified to PUT from GET.

# v1.1.23 - 2020-04-02

## Added

- [#1467](https://github.com/hyperf/hyperf/pull/1467) Added default configuration for filesystem component.
- [#1469](https://github.com/hyperf/hyperf/pull/1469) Added method `getHandler()` for `Hyperf/Guzzle/HandlerStackFactory` and use `make()` function to create the handler instead of `new` operator when it is possible.
- [#1480](https://github.com/hyperf/hyperf/pull/1480) RPC client will generate the methods of inherited interface automatically now.

## Fixed

- [#1471](https://github.com/hyperf/hyperf/pull/1471) Fixed data recved failed, when the body is larger than max-output-buffer-size.
- [#1472](https://github.com/hyperf/hyperf/pull/1472) Fixed consume failed when publish message in consumer of NSQ.
- [#1474](https://github.com/hyperf/hyperf/pull/1474) Fixed the consumer of NSQ will restart when requeue message.
- [#1477](https://github.com/hyperf/hyperf/pull/1477) Fixed Invalid argument supplied for `Hyperf\Testing\Client::flushContext`.

## Changed

- [#1481](https://github.com/hyperf/hyperf/pull/1481) Creating message with `make` instead of `new` for `async-queue`.

# v1.1.22 - 2020-03-26

## Added

- [#1440](https://github.com/hyperf/hyperf/pull/1440) Added config `enable` of every NSQ connection to control the consumer whether they start automatically.
- [#1451](https://github.com/hyperf/hyperf/pull/1451) Added Filesystem component.
- [#1459](https://github.com/hyperf/hyperf/pull/1459) Support macroable model, as laravel does.
- [#1463](https://github.com/hyperf/hyperf/pull/1463) Added option `on_stats` for guzzle handler.

## Fixed

- [#1445](https://github.com/hyperf/hyperf/pull/1445) Fixed command describe:route missing variable route.
- [#1449](https://github.com/hyperf/hyperf/pull/1449) Fixed memory overflow for high cardinality request path.
- [#1454](https://github.com/hyperf/hyperf/pull/1454) Fixed `flatten()` failed, bacause `INF` is `float`.
- [#1458](https://github.com/hyperf/hyperf/pull/1458) Fixed guzzle handler not support elasticsearch which version is larger than 7.0.

## Changed

- [#1452](https://github.com/hyperf/hyperf/pull/1452) Encourage the use of `\Hyperf\Redis\Redis` instead of `\Redis` because of [#938](https://github.com/hyperf/hyperf/issues/938).

# v1.1.21 - 2020-03-19

## Added

- [#1393](https://github.com/hyperf/hyperf/pull/1393) Implemented more methods for `Hyperf\HttpMessage\Stream\SwooleStream`.
- [#1419](https://github.com/hyperf/hyperf/pull/1419) Allow config fetcher to start in a coroutine instead of a process.
- [#1424](https://github.com/hyperf/hyperf/pull/1424) Allow user modify the session_name by configuration file.
- [#1435](https://github.com/hyperf/hyperf/pull/1435) Added config `use_default_value` for model-cache to correct the cache data with database data automatically.
- [#1436](https://github.com/hyperf/hyperf/pull/1436) Added `isEnable()` for NSQ Consumer to control the consumer whether they start automatically.

# v1.1.20 - 2020-03-12

## Added

- [#1402](https://github.com/hyperf/hyperf/pull/1402) Added `Hyperf\DbConnection\Annotation\Transactional` annotation to begin a transaction automatically.
- [#1412](https://github.com/hyperf/hyperf/pull/1412) Added `Hyperf\View\RenderInterface::getContents()` method to get the contents of view render directly.
- [#1416](https://github.com/hyperf/hyperf/pull/1416) Added Swoole event constant `ON_WORKER_ERROR`.

## Fixed

- [#1405](https://github.com/hyperf/hyperf/pull/1405) Fixed the cached attributes are not right, when the model has property `hidden`.
- [#1410](https://github.com/hyperf/hyperf/pull/1410) Fixed tracer cannot trace the call chains of redis connection that created by `Hyperf\Redis\RedisFactory`.
- [#1415](https://github.com/hyperf/hyperf/pull/1415) Fixed the bug that Aliyun acm client decode sts token failed when optional header `SecurityToken` is empty.

# v1.1.19 - 2020-03-05

## Added

- [#1339](https://github.com/hyperf/hyperf/pull/1339) [#1394](https://github.com/hyperf/hyperf/pull/1394) Added `describe:routes` command to describe the routes information by command.
- [#1354](https://github.com/hyperf/hyperf/pull/1354) Added ecs ram authorization for `config-aliyun-acm`.
- [#1362](https://github.com/hyperf/hyperf/pull/1362) Added `getPoolNames()` method for `Hyperf\Pool\SimplePool\PoolFactory`.
- [#1371](https://github.com/hyperf/hyperf/pull/1371) Added `Hyperf\DB\DB::connection()` to use the specified connection.

## Changed

- [#1384](https://github.com/hyperf/hyperf/pull/1384) Added option `property-case` for command `gen:model`.

## Fixed

- [#1386](https://github.com/hyperf/hyperf/pull/1386) Fixed variadic arguments do not work in async message annotation.

# v1.1.18 - 2020-02-27

## Added

- [#1305](https://github.com/hyperf/hyperf/pull/1305) Added pre-made `Grafana` dashboard for `hyperf\metric`.
- [#1328](https://github.com/hyperf/hyperf/pull/1328) Added `ModelRewriteInheritanceVisitor` to rewrite the model inheritance for command `gen:model`.
- [#1331](https://github.com/hyperf/hyperf/pull/1331) Added `Hyperf\LoadBalancer\LoadBalancerInterface::getNodes()`.
- [#1335](https://github.com/hyperf/hyperf/pull/1335) Added event `AfterExecute` for `command`.
- [#1361](https://github.com/hyperf/hyperf/pull/1361) Added config of `processors` for logger.

## Changed

- [#1324](https://github.com/hyperf/hyperf/pull/1324) `Hyperf\AsyncQueue\Listener\QueueLengthListener` is no longer as the default listener of [hyperf/async-queue](https://github.com/hyperf/async-queue).

## Optimized

- [#1305](https://github.com/hyperf/hyperf/pull/1305) Optimize edge cases in `hyperf\metric`.
- [#1322](https://github.com/hyperf/hyperf/pull/1322) HTTP Server Handle HEAD request automatically, now will not response the body on HEAD request.'

## Deleted

- [#1303](https://github.com/hyperf/hyperf/pull/1303) Deleted useless `$httpMethod` for `Hyperf\RpcServer\Router\Router`.

## Fixed

- [#1330](https://github.com/hyperf/hyperf/pull/1330) Fixed bug when using `(new Parallel())->add($callback, $key)` and the parameter `$key` is a not string index, the returned result will sort `$key` from 0.
- [#1338](https://github.com/hyperf/hyperf/pull/1338) Fixed bug that root settings do not works when the slave servers set their own settings.
- [#1344](https://github.com/hyperf/hyperf/pull/1344) Fixed bug that queue length check every time when not set max messages.

# v1.1.17 - 2020-01-24

## Added

- [#1288](https://github.com/hyperf/hyperf/pull/1288) Added driver object into `Hyperf\AsyncQueue\Event\QueueLength` event as the first parameter
- [#1292](https://github.com/hyperf/hyperf/pull/1292) Added `Hyperf\Database\Schema\ForeignKeyDefinition` for return type of `Hyperf\Database\Schema\Blueprint::foreign()` method.
- [#1313](https://github.com/hyperf/hyperf/pull/1313) Added Command mode support to `hyperf\crontab`.
- [#1321](https://github.com/hyperf/hyperf/pull/1321) Added [hyperf/nsq](https://github.com/hyperf/nsq) component, [NSQ](https://nsq.io) is a realtime distributed messaging platform.

## Fixed

- [#1291](https://github.com/hyperf/hyperf/pull/1291) Fixed `$_SERVER` has lower keys for super-globals.
- [#1302](https://github.com/hyperf/hyperf/pull/1302) Fixed JSONRPC reconnect failed, when the node is invalid.
- [#1308](https://github.com/hyperf/hyperf/pull/1308) Fixed some missing traslation of validation, like gt, gte, ipv4, ipv6, lt, lte, mimetypes, not_regex, starts_with, uuid.
- [#1310](https://github.com/hyperf/hyperf/pull/1310) Fixed register failed because has the exactly same service.
- [#1315](https://github.com/hyperf/hyperf/pull/1315) Fixed the missing config variable for `Hyperf\AsyncQueue\Process\ConsumerProcess`.

# v1.1.16 - 2020-01-16

## Added

- [#1263](https://github.com/hyperf/hyperf/pull/1263) Added Event `QueueLength` for async-queue.
- [#1276](https://github.com/hyperf/hyperf/pull/1276) Added ACL token for Consul client.
- [#1277](https://github.com/hyperf/hyperf/pull/1277) Added NoOp Driver to hyperf/metric.

## Fixed

- [#1262](https://github.com/hyperf/hyperf/pull/1262) Fixed bug that socket of keepaliveIO always exhausted.
- [#1266](https://github.com/hyperf/hyperf/pull/1266) Fixed bug that process does not restart when use timer.
- [#1272](https://github.com/hyperf/hyperf/pull/1272) Fixed bug that request id will be checked failed, when the id is null.

## Optimized

- [#1273](https://github.com/hyperf/hyperf/pull/1273) Optimized grpc client.
  - gRPC client now automatically reconnects to the server after disconnection.
  - When gRPC client is garbage collected, the connection is automatically closed.
  - Fixed a bug where a closed gRPC client still holds the underlying http2 connection.
  - Fixed a bug where channel pool for gRPC may contain non-empty channels.
  - gRPC client now initializes itself lazily, so it can be used in constructor and container.

## Deleted

- [#1286](https://github.com/hyperf/hyperf/pull/1286) Removed [phpstan/phpstan](https://github.com/phpstan/phpstan) requires from require-dev.


# v1.1.15 - 2020-01-10

## Fixed

- [#1258](https://github.com/hyperf/hyperf/pull/1258) Fixed CRITICAL error that socket of process is unavailable when amqp send heartbeat failed.
- [#1260](https://github.com/hyperf/hyperf/pull/1260) Fixed json rpc connection confused.

# v1.1.14 - 2020-01-10

## Added

- [#1166](https://github.com/hyperf/hyperf/pull/1166) Added KeepaliveIO for amqp.
- [#1208](https://github.com/hyperf/hyperf/pull/1208) Added exception code `error.data.code` to json-rpc response.
- [#1208](https://github.com/hyperf/hyperf/pull/1208) Added `recv` method to `Hyperf\Rpc\Contract\TransporterInterface`.
- [#1215](https://github.com/hyperf/hyperf/pull/1215) Added super-globals component.
- [#1219](https://github.com/hyperf/hyperf/pull/1219) Added property `enable` for amqp consumer, which controls whether consumers should start along with the service.

## Fixed

- [#1208](https://github.com/hyperf/hyperf/pull/1208) Fixed bug that exception and error cannot be resolved successfully in TcpServer.
- [#1208](https://github.com/hyperf/hyperf/pull/1208) Fixed bug that json-rpc has not validated the request id whether is equal to response id.
- [#1223](https://github.com/hyperf/hyperf/pull/1223) Fixed the scanner will missing the packages at require-dev of composer.json
- [#1254](https://github.com/hyperf/hyperf/pull/1254) Fixed bash not found on some environment like Alpine when execute `init-proxy.sh`.

## Optimized

- [#1208](https://github.com/hyperf/hyperf/pull/1208) Optimized json-rpc logical.
- [#1174](https://github.com/hyperf/hyperf/pull/1174) Adjusted the format of exception printer of `Hyperf\Utils\Parallel`.
- [#1224](https://github.com/hyperf/hyperf/pull/1224) Allows config fetcher of Aliyun ACM parse UTF-8 charater, and fetch configuration once after worker start automatically, also allows pass the configutation to user process.
- [#1235](https://github.com/hyperf/hyperf/pull/1235) Release connection after declared for amqp producers.

## Changed

- [#1227](https://github.com/hyperf/hyperf/pull/1227) Upgraded jcchavezs/zipkin-php-opentracing to 0.1.4.

# v1.1.13 - 2020-01-03

## Added

- [#1137](https://github.com/hyperf/hyperf/pull/1137) Added translator for constants.
- [#1165](https://github.com/hyperf/hyperf/pull/1165) Added a method `route` for `Hyperf\HttpServer\Contract\RequestInterface`.
- [#1195](https://github.com/hyperf/hyperf/pull/1195) Added max offset for `Cacheable` and `CachePut`.
- [#1204](https://github.com/hyperf/hyperf/pull/1204) Added `insertOrIgnore` for database.
- [#1216](https://github.com/hyperf/hyperf/pull/1216) Added default value for `$data` of `RenderInterface::render()`.
- [#1221](https://github.com/hyperf/hyperf/pull/1221) Added `traceId` and `spanId` of the `swoole-tracker` component.

## Fixed

- [#1175](https://github.com/hyperf/hyperf/pull/1175) Fixed `Hyperf\Utils\Collection::random` does not works when the number is null.
- [#1199](https://github.com/hyperf/hyperf/pull/1199) Fixed variadic arguments do not work in task annotation.
- [#1200](https://github.com/hyperf/hyperf/pull/1200) Request path shouldn't include query parameters in hyperf/metric middleware.
- [#1210](https://github.com/hyperf/hyperf/pull/1210) Fixed validation `size` does not works without `numeric` or `integer` rules when the type of value is numeric.

## Optimized

- [#1211](https://github.com/hyperf/hyperf/pull/1211) Convert app name to valid prometheus namespace.

## Changed

- [#1217](https://github.com/hyperf/hyperf/pull/1217) Replaced `zendframework/zend-mime` into `laminas/laminas-mine`.

# v1.1.12 - 2019-12-26

## Added

- [#1177](https://github.com/hyperf/hyperf/pull/1177) Added protocol `jsonrpc-tcp-length-check` for `jsonrpc`.

## Fixed

- [#1175](https://github.com/hyperf/hyperf/pull/1175) Fixed `Hyperf\Utils\Collection::random` does not works when the number is null.
- [#1178](https://github.com/hyperf/hyperf/pull/1178) Fixed `Hyperf\Database\Query\Builder::chunkById` does not works when the collection item is array.
- [#1189](https://github.com/hyperf/hyperf/pull/1189) Fixed default operator does not works for `Hyperf\Utils\Collection::operatorForWhere`.

## Optimized

- [#1186](https://github.com/hyperf/hyperf/pull/1186) Automatically added default constructor's configuration, when you forgetton to set it.

# v1.1.11 - 2019-12-19

## Added

- [#849](https://github.com/hyperf/hyperf/pull/849) Added configuration of span tag for `tracer` component.

## Fixed

- [#1142](https://github.com/hyperf/hyperf/pull/1142) Fixed bug that Register::resolveConnection will return null.
- [#1144](https://github.com/hyperf/hyperf/pull/1144) Fixed rate-limit config does not works.
- [#1145](https://github.com/hyperf/hyperf/pull/1145) Fixed error return value for method `CoroutineMemoryDriver::delKey`.
- [#1153](https://github.com/hyperf/hyperf/pull/1153) Fixed validation rule `alpha_num` does not works.

# v1.1.10 - 2019-12-12

## Fixed

- [#1104](https://github.com/hyperf/hyperf/pull/1104) Fixed guzzle will be retried when the response has the correct status code 2xx.
- [#1105](https://github.com/hyperf/hyperf/pull/1105) Fixed Retry Component not restoring pipeline stack before retry attempts.
- [#1106](https://github.com/hyperf/hyperf/pull/1106) Fixed bug that sticky mode will affect the next request.
- [#1119](https://github.com/hyperf/hyperf/pull/1119) Fixed JSONRPC on TCP Server cannot response the expected error response when cannot unpack the data.
- [#1124](https://github.com/hyperf/hyperf/pull/1124) Fixed Session middleware does not store the current url correctly when the path of url end with a slash.

## Changed

- [#1108](https://github.com/hyperf/hyperf/pull/1108) Renamed `Hyperf\Tracer\Middleware\TraceMiddeware` to `Hyperf\Tracer\Middleware\TraceMiddleware`.
- [#1108](https://github.com/hyperf/hyperf/pull/1111) Upgrade the access level of methods and properties of `Hyperf\ServiceGovernance\Listener\ServiceRegisterListener` , for better override it.

# v1.1.9 - 2019-12-05

## Added

- [#948](https://github.com/hyperf/hyperf/pull/948) Added Lazy loader to DI.
- [#1044](https://github.com/hyperf/hyperf/pull/1044) Added `basic_qos` for amqp consumer.
- [#1056](https://github.com/hyperf/hyperf/pull/1056) [#1081](https://github.com/hyperf/hyperf/pull/1081) Added `define()` and `set()` to Container. Added `Hyperf\Contract\ContainerInterface`.
- [#1059](https://github.com/hyperf/hyperf/pull/1059) Added constructor for `job.stub`.
- [#1084](https://github.com/hyperf/hyperf/pull/1084) Added php 7.4 support.

## Fixed

- [#1049](https://github.com/hyperf/hyperf/pull/1049) Fixed `Hyperf\Cache\Driver\RedisDriver::clear` sometimes fails to delete all caches.
- [#1055](https://github.com/hyperf/hyperf/pull/1055) Fixed image extension validation failed.
- [#1085](https://github.com/hyperf/hyperf/pull/1085) [#1091](https://github.com/hyperf/hyperf/pull/1091) Fixed broken retry annotation.

## Optimized

- [#1007](https://github.com/hyperf/hyperf/pull/1007)  Optimized `vendor:: publish` return value does not support null.

# v1.1.8 - 2019-11-28

## Added

- [#965](https://github.com/hyperf/hyperf/pull/965) Added Redis Lua Module.
- [#1023](https://github.com/hyperf/hyperf/pull/1023) Added CUSTOM_MODE to hyperf/metric prometheus driver.

## Fixed

- [#1013](https://github.com/hyperf/hyperf/pull/1013) Fixed config of JsonRpcPoolTransporter merge failed.
- [#1006](https://github.com/hyperf/hyperf/pull/1006) Fixed the order of properties of Model.

## Changed

- [#1021](https://github.com/hyperf/hyperf/pull/1021) Added default port to WebSocket client.

## Optimized

- [#1014](https://github.com/hyperf/hyperf/pull/1014) Optimized `Command:: execute` return value does not support null.
- [#1022](https://github.com/hyperf/hyperf/pull/1022) Provided cleaner connection pool error message without implementation details.
- [#1039](https://github.com/hyperf/hyperf/pull/1039) Updated the ServerRequest object to context in CoreMiddleware automatically.
- [#1034](https://github.com/hyperf/hyperf/pull/1034) The property `arguments` of `Hyperf\Amqp\Builder\Builder` not only support array.

# v1.1.7 - 2019-11-21

## Added

- [#860](https://github.com/hyperf/hyperf/pull/860) Added retry component.
- [#952](https://github.com/hyperf/hyperf/pull/952) Added think template engine for view.
- [#973](https://github.com/hyperf/hyperf/pull/973) Added `Hyperf\JsonRpc\JsonRpcPoolTransporter`.
- [#976](https://github.com/hyperf/hyperf/pull/976) Added params `close_on_destruct` for `hyperf/amqp`.

## Fixed

- [#955](https://github.com/hyperf/hyperf/pull/955) Fixed bug that port and charset do not work for `hyperf/db`.
- [#956](https://github.com/hyperf/hyperf/pull/956) Fixed bug that `RedisHandler::incr` fails in cluster mode for model cache.
- [#966](https://github.com/hyperf/hyperf/pull/966) Fixed type error, when use paginator in non-worker process.
- [#968](https://github.com/hyperf/hyperf/pull/968) Fixed aspect does not works when class and annotation exist at the same time.
- [#980](https://github.com/hyperf/hyperf/pull/980) Fixed `migrate`, `save` and `has` methods of Session do not work as expected.
- [#982](https://github.com/hyperf/hyperf/pull/982) Fixed `Hyperf\GrpcClient\GrpcClient::yield` does not get the correct channel pool.
- [#987](https://github.com/hyperf/hyperf/pull/987) Fixed missing method call `parent::configure()` of `command.stub`.

## Optimized

- [#991](https://github.com/hyperf/hyperf/pull/991) Optimized `Hyperf\DbConnection\ConnectionResolver::connection`.

## Changed

- [#944](https://github.com/hyperf/hyperf/pull/944) Replaced annotation `@Listener` and `@Process` into config which `listeners` and `processes` in `ConfigProvider`.
- [#977](https://github.com/hyperf/hyperf/pull/977) Changed `init-proxy.sh` command to only delete the `runtime/container` directory.

# v1.1.6 - 2019-11-14

## Added

- [#827](https://github.com/hyperf/hyperf/pull/827) Added a simple db component.
- [#905](https://github.com/hyperf/hyperf/pull/905) Added twig template engine for view.
- [#911](https://github.com/hyperf/hyperf/pull/911) Added support for crontab task run on one server.
- [#913](https://github.com/hyperf/hyperf/pull/913) Added `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.
- [#931](https://github.com/hyperf/hyperf/pull/931) Added `strict_mode` for config-apollo.
- [#933](https://github.com/hyperf/hyperf/pull/933) Added plates template engine for view.
- [#937](https://github.com/hyperf/hyperf/pull/937) Added consume events for nats.
- [#941](https://github.com/hyperf/hyperf/pull/941) Added an zookeeper adapter for Hyperf config component.

## Fixed

- [#897](https://github.com/hyperf/hyperf/pull/897) Fixed connection pool of `Hyperf\Nats\Annotation\Consumer` does not works as expected.
- [#901](https://github.com/hyperf/hyperf/pull/901) Fixed Annotation `Factory` does not works for GraphQL.
- [#903](https://github.com/hyperf/hyperf/pull/903) Fixed execute `init-proxy` command can not stop when `hyperf/rpc-client` component exists.
- [#904](https://github.com/hyperf/hyperf/pull/904) Fixed the hooked I/O request does not works in the listener that listening `Hyperf\Framework\Event\BeforeMainServerStart` event.
- [#906](https://github.com/hyperf/hyperf/pull/906) Fixed `port` property of URI of `Hyperf\HttpMessage\Server\Request`.
- [#907](https://github.com/hyperf/hyperf/pull/907) Fixed the expire time is double of the config for `requestSync` in nats.
- [#909](https://github.com/hyperf/hyperf/pull/909) Fixed a issue that causes staled parallel execution.
- [#925](https://github.com/hyperf/hyperf/pull/925) Fixed the dead cycle caused by socket closed.
- [#932](https://github.com/hyperf/hyperf/pull/932) Fixed `Translator::setLocale` does not works in coroutine evnironment.
- [#940](https://github.com/hyperf/hyperf/pull/940) Fixed WebSocketClient::push TypeError, expects integer, but boolean given.

## Optimized

- [#907](https://github.com/hyperf/hyperf/pull/907) Optimized nats consumer process restart frequently.
- [#928](https://github.com/hyperf/hyperf/pull/928) Optimized `Hyperf\ModelCache\Cacheable::query` to delete the model cache when batch update
- [#936](https://github.com/hyperf/hyperf/pull/936) Optimized `increment` to atomic operation for model-cache.

## Changed

- [#934](https://github.com/hyperf/hyperf/pull/934) WaitGroup inherit \Swoole\Coroutine\WaitGroup.

# v1.1.5 - 2019-11-07

## Added

- [#812](https://github.com/hyperf/hyperf/pull/812) Added singleton crontab task support.
- [#820](https://github.com/hyperf/hyperf/pull/820) Added nats component.
- [#832](https://github.com/hyperf/hyperf/pull/832) Added `Hyperf\Utils\Codec\Json`.
- [#833](https://github.com/hyperf/hyperf/pull/833) Added `Hyperf\Utils\Backoff`.
- [#852](https://github.com/hyperf/hyperf/pull/852) Added a `clear()` method for `Hyperf\Utils\Parallel` to clear added callbacks.
- [#854](https://github.com/hyperf/hyperf/pull/854) Added `GraphQLMiddleware`.
- [#859](https://github.com/hyperf/hyperf/pull/859) Added Consul cluster mode support, now available to fetch the service information from Consul cluster.
- [#873](https://github.com/hyperf/hyperf/pull/873) Added redis cluster.

## Fixed

- [#831](https://github.com/hyperf/hyperf/pull/831) Fixed Redis client can not reconnect the server after the Redis server restarted.
- [#835](https://github.com/hyperf/hyperf/pull/835) Fixed `Request::inputs` default value does not works.
- [#841](https://github.com/hyperf/hyperf/pull/841) Fixed migration does not take effect under multiple data sources.
- [#844](https://github.com/hyperf/hyperf/pull/844) Fixed the reader of `composer.json` does not support the root namespace.
- [#846](https://github.com/hyperf/hyperf/pull/846) Fixed `scan` `hScan` `zScan` and `sScan` don't works for Redis.
- [#850](https://github.com/hyperf/hyperf/pull/850) Fixed logger group does not works when the name is same.

## Optimized

- [#832](https://github.com/hyperf/hyperf/pull/832) Optimized that response will throw a exception when json format failed.
- [#840](https://github.com/hyperf/hyperf/pull/840) Use `\Swoole\Timer::*` to instead of `swoole_timer_*` functions.
- [#859](https://github.com/hyperf/hyperf/pull/859) Optimized the logical of fetch health nodes infomation from consul.

# v1.1.4 - 2019-10-31

## Added

- [#778](https://github.com/hyperf/hyperf/pull/778) Added `PUT` and `DELETE` for `Hyperf\Testing\Client`.
- [#784](https://github.com/hyperf/hyperf/pull/784) Add Metric Component
- [#795](https://github.com/hyperf/hyperf/pull/795) Added `restartInterval` for `AbstractProcess`.
- [#804](https://github.com/hyperf/hyperf/pull/804) Added `BeforeHandle` `AfterHandle` and `FailToHandle` for command.

## Fixed

- [#779](https://github.com/hyperf/hyperf/pull/779) Fixed bug that JPG file cannot be verified.
- [#787](https://github.com/hyperf/hyperf/pull/787) Fixed bug that "--class" option does not exist.
- [#795](https://github.com/hyperf/hyperf/pull/795) Fixed process not restart when throw an exception.
- [#796](https://github.com/hyperf/hyperf/pull/796) Fixed `config_etcd.enable` does not works.

## Optimized

- [#781](https://github.com/hyperf/hyperf/pull/781) Publish validation language package according to translation setting.
- [#796](https://github.com/hyperf/hyperf/pull/796) Don't remake HandlerStack for etcd.
- [#797](https://github.com/hyperf/hyperf/pull/797) Use channel to communicate, instead of sharing mem

## Changed

- [#793](https://github.com/hyperf/hyperf/pull/793) Changed `protected` to `public` for `Pool::getConnectionsInChannel`.
- [#811](https://github.com/hyperf/hyperf/pull/811) Command `di:init-proxy` does not clear the runtime cache, If you want to delete them, use `vendor/bin/init-proxy.sh` instead.

# v1.1.3 - 2019-10-24

## Added

- [#745](https://github.com/hyperf/hyperf/pull/745) Added option `with-comments` for command `gen:model`.
- [#747](https://github.com/hyperf/hyperf/pull/747) Added `AfterConsume`,`BeforeConsume`,`FailToConsume` events for AMQP consumer.
- [#762](https://github.com/hyperf/hyperf/pull/762) Add concurrent for parallel.

## Fixed

- [#741](https://github.com/hyperf/hyperf/pull/741) Fixed `db:seed` without filename.
- [#748](https://github.com/hyperf/hyperf/pull/748) Fixed bug that `SymfonyNormalizer` not denormalize result of type `array`.
- [#769](https://github.com/hyperf/hyperf/pull/769) Fixed invalid response exception throwed when result/error of jsonrpc response is null.

# Changed

- [#767](https://github.com/hyperf/hyperf/pull/767) Renamed property `running` to `listening` for `AbstractProcess`.

# v1.1.2 - 2019-10-17

## Added

- [#722](https://github.com/hyperf/hyperf/pull/722) Added config `concurrent.limit` for AMQP consumer.

## Changed

- [#678](https://github.com/hyperf/hyperf/pull/678) Added ignore-tables for `gen:model`, and ignore `migrations` table, and `migrations` table will not generate when execute the `gen:model` command.
- [#729](https://github.com/hyperf/hyperf/pull/729) Renamed config `db:model` to `gen:model`.

## Fixed

- [#678](https://github.com/hyperf/hyperf/pull/678) Added ignore-tables for `gen:model`, and ignore `migrations` table.
- [#694](https://github.com/hyperf/hyperf/pull/694) Fixed `validationData` method of `Hyperf\Validation\Request\FormRequest` does not contains the uploaded files.
- [#700](https://github.com/hyperf/hyperf/pull/700) Fixed the `download` method of `Hyperf\HttpServer\Contract\ResponseInterface` does not works as expected.
- [#701](https://github.com/hyperf/hyperf/pull/701) Fixed the custom process will not restart automatically when throw an uncaptured exception.
- [#704](https://github.com/hyperf/hyperf/pull/704) Fixed bug that `Call to a member function getName() on null` in `Hyperf\Validation\Middleware\ValidationMiddleware` when the argument of action method does not define the argument type.
- [#713](https://github.com/hyperf/hyperf/pull/713) Fixed `ignoreAnnotations` does not works when cache is used.
- [#717](https://github.com/hyperf/hyperf/pull/717) Fixed the validator will be created repeatedly in `getValidatorInstance`.
- [#724](https://github.com/hyperf/hyperf/pull/724) Fixed `db:seed` command without database selected.
- [#737](https://github.com/hyperf/hyperf/pull/737) Fixed custom process does not enable for tracer.

# v1.1.1 - 2019-10-08

## Fixed

- [#664](https://github.com/hyperf/hyperf/pull/664) Changed the default return value of FormRequest::authorize which generate via `gen:request` command.
- [#665](https://github.com/hyperf/hyperf/pull/665) Fixed framework will generate proxy class of all classes that in app directory every time.
- [#667](https://github.com/hyperf/hyperf/pull/667) Fixed trying to get property 'callback' of non-object in `Hyperf\Validation\Middleware\ValidationMiddleware`.
- [#672](https://github.com/hyperf/hyperf/pull/672) Fixed  `Hyperf\Validation\Middleware\ValidationMiddleware` will throw an unexpected exception when the action method has defined a non-object parameter.
- [#674](https://github.com/hyperf/hyperf/pull/674) Fixed the table of Model is not correct when using `gen:model`.

# v1.1.0 - 2019-10-08

## Added

- [#401](https://github.com/hyperf/hyperf/pull/401) Optimized server and fixed middleware that user defined does not works.
- [#402](https://github.com/hyperf/hyperf/pull/402) Added Annotation `@AsyncQueueMessage`.
- [#418](https://github.com/hyperf/hyperf/pull/418) Allows send WebSocket message to any `fd` in current server, even the worker process does not hold the `fd`
- [#420](https://github.com/hyperf/hyperf/pull/420) Added listener for model.
- [#429](https://github.com/hyperf/hyperf/pull/429) [#643](https://github.com/hyperf/hyperf/pull/643) Added validation component, a component similar to [illuminate/validation](https://github.com/illuminate/validation).
- [#441](https://github.com/hyperf/hyperf/pull/441) Automatically close the spare redis client when it is used in low frequency.
- [#478](https://github.com/hyperf/hyperf/pull/441) Adopt opentracing interfaces and support [Jaeger](https://www.jaegertracing.io/).
- [#500](https://github.com/hyperf/hyperf/pull/499) Added fluent method calls of `Hyperf\HttpServer\Contract\ResponseInterface`.
- [#523](https://github.com/hyperf/hyperf/pull/523) Added option `table-mapping` for command `db:model`.
- [#555](https://github.com/hyperf/hyperf/pull/555) Added global function `swoole_hook_flags` to get the hook flags by constant `SWOOLE_HOOK_FLAGS`, and you could define in `bin/hyperf.php` via `! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);` to define the constant.
- [#596](https://github.com/hyperf/hyperf/pull/596) [#658](https://github.com/hyperf/hyperf/pull/658) Added `required` parameter for `@Inject`, if you define `@Inject(required=false)` annotation to a property, therefore the DI container will not throw an `Hyperf\Di\Exception\NotFoundException` when the dependency of the property does not exists, the default value of `required` parameter is `true`. In constructor injection mode, you could define the default value of the parameter of the `__construct` to `null` or define the parameter as a `nullable` parameter , this means this parameter is nullable and will not throw the exception too.
- [#597](https://github.com/hyperf/hyperf/pull/597) Added concurrent for async-queue.
- [#599](https://github.com/hyperf/hyperf/pull/599) Allows set the retry seconds according to attempt times of async queue consumer.
- [#619](https://github.com/hyperf/hyperf/pull/619) Added HandlerStackFactory of guzzle.
- [#620](https://github.com/hyperf/hyperf/pull/620) Add automatic restart mechanism for consumer of async queue.
- [#629](https://github.com/hyperf/hyperf/pull/629) Allows to modify the `clientIp`, `pullTimeout`, `intervalTimeout` of Apollo client via config file.
- [#648](https://github.com/hyperf/hyperf/pull/648) Added `nack` return type of AMQP consumer, the abstract consumer will execute `basic_nack` method when the message handler return a `Hyperf\Amqp\Result::NACK`.
- [#654](https://github.com/hyperf/hyperf/pull/654) Added all Swoole events and abstract hyperf events.

## Changed

- [#437](https://github.com/hyperf/hyperf/pull/437) Changed `Hyperf\Testing\Client` handle exception handlers instead of throw an exception directly.
- [#463](https://github.com/hyperf/hyperf/pull/463) Simplify `container.php` and improve annotation caching mechanism.

config/container.php

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

- [#486](https://github.com/hyperf/hyperf/pull/486) Changed `getParsedBody` of Request is available to return JSON formatted data normally.
- [#523](https://github.com/hyperf/hyperf/pull/523) The command `db:model` will generate the singular class name of an plural table as default.
- [#614](https://github.com/hyperf/hyperf/pull/614) [#617](https://github.com/hyperf/hyperf/pull/617) Changed the structure of config provider, also moved `config/dependencies.php` to `config/autoload/dependencies.php`, also you could place `dependencies` into config/config.php.

Changed the structure of config provider:
Before:
```php
'scan' => [
    'paths' => [
        __DIR__,
    ],
    'collectors' => [],
],
```
Now:
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

- [#630](https://github.com/hyperf/hyperf/pull/630) Changed the way to instantiate `Hyperf\HttpServer\CoreMiddleware`, use `make()` instead of `new`.
- [#631](https://github.com/hyperf/hyperf/pull/631) Changed the way to instantiate AMQP Consumer, use `make()` instead of `new`.
- [#637](https://github.com/hyperf/hyperf/pull/637) Changed the argument 1 of `Hyperf\Contract\OnMessageInterface` and `Hyperf\Contract\OnOpenInterface`, use `Swoole\WebSocket\Server` instead of `Swoole\Server`.
- [#638](https://github.com/hyperf/hyperf/pull/638) Renamed command `db:model` to `gen:model` and added rewrite connection name visitor.

## Deleted

- [#401](https://github.com/hyperf/hyperf/pull/401) Deleted class `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\HttpServer\ServerFactory`, `Hyperf\GrpcServer\ServerFactory`.
- [#402](https://github.com/hyperf/hyperf/pull/402) Deleted deprecated method `AsyncQueue::delay`.
- [#563](https://github.com/hyperf/hyperf/pull/563) Deleted deprecated constants `Hyperf\Server\ServerInterface::SERVER_TCP`, use `Hyperf\Server\ServerInterface::SERVER_BASE` to instead of it.
- [#602](https://github.com/hyperf/hyperf/pull/602) Removed timeout property of `Hyperf\Utils\Coroutine\Concurrent`.
- [#612](https://github.com/hyperf/hyperf/pull/612) Deleted useless `$url` for RingPHP Handlers.
- [#616](https://github.com/hyperf/hyperf/pull/616) [#618](https://github.com/hyperf/hyperf/pull/618) Deleted useless code of guzzle.

## Optimized

- [#644](https://github.com/hyperf/hyperf/pull/644) Optimized annotation scan process, separate to two scan parts `app` and `vendor`, greatly decrease the elapsed time.
- [#653](https://github.com/hyperf/hyperf/pull/653) Optimized the detect logical of swoole shortname.

## Fixed

- [#448](https://github.com/hyperf/hyperf/pull/448) Fixed TCP Server does not works when HTTP Server or WebSocket Server exists.
- [#623](https://github.com/hyperf/hyperf/pull/623) Fixed the argument value will be replaced by default value when pass a `null` to the method of proxy class.
- [#647](https://github.com/hyperf/hyperf/pull/647) Append `eof` to TCP response, according to the server configuration.

## Fixed

- [#636](https://github.com/hyperf/hyperf/pull/636) Fixed http client with pool handler may be used by different coroutine at the same time.

# v1.0.17 - 2019-10-08

## Fixed

- [#636](https://github.com/hyperf/hyperf/pull/636) Fixed http client with pool handler may be used by different coroutine at the same time.

# v1.0.16 - 2019-09-20

## Added

- [#565](https://github.com/hyperf/hyperf/pull/565) Added options config for redis.
- [#580](https://github.com/hyperf/hyperf/pull/580) Added coroutine concurrency control features.

## Fixed

- [#564](https://github.com/hyperf/hyperf/pull/564) Fixed typehint error, when `Coroutine\Http2\Client->send` failed.
- [#567](https://github.com/hyperf/hyperf/pull/567) Fixed rpc-client `getReturnType` failed, when the name is not equal of service.
- [#571](https://github.com/hyperf/hyperf/pull/571) Fixed the next request will be effected after using stopPropagation.
- [#579](https://github.com/hyperf/hyperf/pull/579) Dynamic init snowflake meta data, fixed the problem that when using snowflake in command mode (e.g. `di:init-proxy`) will connect to redis server and wait timeout.

# Changed

- [#583](https://github.com/hyperf/hyperf/pull/583) Throw `GrpcClientException`, when `BaseClient::start` failed.
- [#585](https://github.com/hyperf/hyperf/pull/585) Throw exception when execute failed in task worker.

# v1.0.15 - 2019-09-11

## Fixed

- [#534](https://github.com/hyperf/hyperf/pull/534) Fixed Guzzle HTTP Client does not handle the response status is equal to `-3`;
- [#541](https://github.com/hyperf/hyperf/pull/541) Fixed bug grpc client cannot be set correctly.
- [#542](https://github.com/hyperf/hyperf/pull/542) Fixed `Hyperf\Grpc\Parser::parseResponse` returns a non-standard error code for grpc.
- [#551](https://github.com/hyperf/hyperf/pull/551) Fixed infinite loop in grpc client when the server closed the connection.
- [#558](https://github.com/hyperf/hyperf/pull/558) Fixed UDP Server does not works.

## Deleted

- [#545](https://github.com/hyperf/hyperf/pull/545) Deleted useless static methods `restoring` and `restored` of trait SoftDeletes.

## Optimized

- [#549](https://github.com/hyperf/hyperf/pull/549) Optimized `read` and `write` of `Hyperf\Amqp\Connection\SwooleIO`.
- [#559](https://github.com/hyperf/hyperf/pull/559) Optimized `redirect ` of `Hyperf\HttpServer\Response`.
- [#560](https://github.com/hyperf/hyperf/pull/560) Optimized class `Hyperf\WebSocketServer\CoreMiddleware`.

## Deprecated

- [#558](https://github.com/hyperf/hyperf/pull/558) Marked `Hyperf\Server\ServerInterface::SERVER_TCP` as deprecated, will be removed in `v1.1`.

# v1.0.14 - 2019-09-05

## Added

- [#389](https://github.com/hyperf/hyperf/pull/389) [#419](https://github.com/hyperf/hyperf/pull/419) [#432](https://github.com/hyperf/hyperf/pull/432) [#524](https://github.com/hyperf/hyperf/pull/524) [#531](https://github.com/hyperf/hyperf/pull/531) Added snowflake component, snowflake is a distributed global unique ID generation algorithm put forward by Twitter, this component implemented this algorithm for easy to use.
- [#525](https://github.com/hyperf/hyperf/pull/525) Added `download()` method of `Hyperf\HttpServer\Contract\ResponseInterface`.

## Changed

- [#482](https://github.com/hyperf/hyperf/pull/482) Re-generate the `fillable` argument of Model when use `refresh-fillable` option, at the same time, the command will keep the `fillable` argument as default behaviours.
- [#501](https://github.com/hyperf/hyperf/pull/501) When the path argument of Mapping annotation is an empty string, then the path is equal to prefix of Controller annotation.
- [#513](https://github.com/hyperf/hyperf/pull/513) Rewrite process name with `app_name`.
- [#508](https://github.com/hyperf/hyperf/pull/508) [#526](https://github.com/hyperf/hyperf/pull/526) When execute `Hyperf\Utils\Coroutine::parentId()` static method in non-coroutine context will return null.

## Fixed

- [#479](https://github.com/hyperf/hyperf/pull/479) Fixed typehint error when host of Elasticsearch client does not reached.
- [#514](https://github.com/hyperf/hyperf/pull/514) Fixed redis auth failed when the password is an empty string.
- [#527](https://github.com/hyperf/hyperf/pull/527) Fixed translator cannot translate repeatedly.

# v1.0.13 - 2019-08-28

## Added

- [#428](https://github.com/hyperf/hyperf/pull/428) Added an independent component [hyperf/translation](https://github.com/hyperf/translation), forked by illuminate/translation.
- [#449](https://github.com/hyperf/hyperf/pull/449) Added standard error code for grpc-server.
- [#450](https://github.com/hyperf/hyperf/pull/450) Added comments of static methods for `Hyperf\Database\Schema\Schema`.

## Changed

- [#451](https://github.com/hyperf/hyperf/pull/451) Removed routes of magic methods from `AuthController`.
- [#468](https://github.com/hyperf/hyperf/pull/468) Default exception handlers catch all exceptions.

## Fixed

- [#466](https://github.com/hyperf/hyperf/pull/466) Fixed error when the number of data is not enough to paginate.
- [#466](https://github.com/hyperf/hyperf/pull/470) Optimized `vendor:publish` command, if the destination folder exists, then will not repeatedly create the folder.

# v1.0.12 - 2019-08-21

## Added

- [#405](https://github.com/hyperf/hyperf/pull/405) Added Context::override() method.
- [#415](https://github.com/hyperf/hyperf/pull/415) Added handlers configuration for logger, now you could config multiple handlers to logger.

## Changed

- [#431](https://github.com/hyperf/hyperf/pull/431) The third parameter of Hyperf\GrpcClient\GrpcClient::openStream() have been removed.

## Fixed

- [#414](https://github.com/hyperf/hyperf/pull/414) Fixed WebSocketExceptionHandler typo
- [#424](https://github.com/hyperf/hyperf/pull/424) Fixed proxy configuration of `Hyperf\Guzzle\CoroutineHandler` does not support array parameter.
- [#430](https://github.com/hyperf/hyperf/pull/430) Fixed file() method of Request will threw an exception, when upload files with same name of form.
- [#431](https://github.com/hyperf/hyperf/pull/431) Fixed missing parameters of the grpc request.

## Deprecated

- [#425](https://github.com/hyperf/hyperf/pull/425) Marked `Hyperf\HttpServer\HttpServerFactory`, `Hyperf\JsonRpc\HttpServerFactory`, `Hyperf\JsonRpc\TcpServerFactory` as deprecated, will be removed in `v1.1`.

# v1.0.11 - 2019-08-15

## Added

- [#366](https://github.com/hyperf/hyperf/pull/366) Added `Hyperf\Server\Listener\InitProcessTitleListener` to init th process name, also added `Hyperf\Framework\Event\OnStart` and `Hyperf\Framework\Event\OnManagerStart` events.
- [#389](https://github.com/hyperf/hyperf/pull/389) Added Snowflake component.

## Fixed

- [#361](https://github.com/hyperf/hyperf/pull/361) Fixed command `db:model` does not works in `MySQL 8`.
- [#369](https://github.com/hyperf/hyperf/pull/369) Fixed the exception which implemented `\Serializable`, call `serialize()` and `unserialize()` functions failed.
- [#384](https://github.com/hyperf/hyperf/pull/384) Fixed the `ExceptionHandler` that user defined does not works, because the framework has handled the exception automatically.
- [#370](https://github.com/hyperf/hyperf/pull/370) Fixed set the error type client to `Hyperf\GrpcClient\BaseClient`, and added default content-type `application/grpc+proto` to the Request object, also allows the grpc client that user-defined to override the `buildRequest()` method to create a new Request object.

## Changed

- [#356](https://github.com/hyperf/hyperf/pull/356) [#390](https://github.com/hyperf/hyperf/pull/390) Optimized aysnc-queue when push a job that implemented `Hyperf\Contract\CompressInterface`, will compress the job to a small object automatically.
- [#358](https://github.com/hyperf/hyperf/pull/358) Only write the annotation cache file when `$enableCache` is `true`.
- [#359](https://github.com/hyperf/hyperf/pull/359) [#390](https://github.com/hyperf/hyperf/pull/390) Added compression ability for `Collection` and `Model`, if the object implemented `Hyperf\Contract\CompressInterface`, then the object could compress to a small one by call `compress` method.

# v1.0.10 - 2019-08-09

## Added

- [#321](https://github.com/hyperf/hyperf/pull/321) Adding custom object types of array support for the Controller/RequestHandler parameter of HTTP Server, especially for JSON RPC HTTP Server, now you can get support for auto-deserialization of objects by defining `@var Object[]` on the method.
- [#324](https://github.com/hyperf/hyperf/pull/324) Added NodeRequestIdGenerator, an implementation of `Hyperf\Contract\IdGeneratorInterface`
- [#336](https://github.com/hyperf/hyperf/pull/336) Added Dynamic Proxy RPC Client.
- [#346](https://github.com/hyperf/hyperf/pull/346) [#348](https://github.com/hyperf/hyperf/pull/348) Added filesystem driver for `hyperf/cache`.

## Changed

- [#330](https://github.com/hyperf/hyperf/pull/330) Hidden the scan message of DI when $paths is empty.
- [#328](https://github.com/hyperf/hyperf/pull/328) Added support for user defined project path according to the rules defined by composer.json's psr-4 autoload.
- [#329](https://github.com/hyperf/hyperf/pull/329) Optimized exception handler of rpc-server and json-rpc component.
- [#340](https://github.com/hyperf/hyperf/pull/340) Added support for `make` function accept index-based array as parameters.
- [#349](https://github.com/hyperf/hyperf/pull/349) Renamed the class name below, fixed the typo.

|                     Before                      |                  After                     |
|:----------------------------------------------:|:-----------------------------------------------:|
| Hyperf\Database\Commands\Ast\ModelUpdateVistor | Hyperf\Database\Commands\Ast\ModelUpdateVisitor |
|       Hyperf\Di\Aop\ProxyClassNameVistor       |       Hyperf\Di\Aop\ProxyClassNameVisitor       |
|         Hyperf\Di\Aop\ProxyCallVistor          |         Hyperf\Di\Aop\ProxyCallVisitor          |

## Fixed

- [#325](https://github.com/hyperf/hyperf/pull/325) Fixed check the service registration status via consul services more than one times.
- [#332](https://github.com/hyperf/hyperf/pull/332) Fixed type error in `Hyperf\Tracer\Middleware\TraceMiddeware`, only appears in openzipkin/zipkin v1.3.3+.
- [#333](https://github.com/hyperf/hyperf/pull/333) Fixed Redis::delete() method has been removed in redis 5.0+.
- [#334](https://github.com/hyperf/hyperf/pull/334) Fixed the configuration fetch from aliyun acm is not work expected in some case.
- [#337](https://github.com/hyperf/hyperf/pull/337) Fixed the server will return 500 Response when the key of header is not a string.
- [#338](https://github.com/hyperf/hyperf/pull/338) Fixed the problem of `ProviderConfig::load` will convert a string to a array when the dependencies has the same key in deep merging.

# v1.0.9 - 2019-08-03

## Added

- [#317](https://github.com/hyperf/hyperf/pull/317) Added composer-json-fixer and Optimized composer.json. @[wenbinye](https://github.com/wenbinye)
- [#320](https://github.com/hyperf/hyperf/pull/320) DI added support for closure definition.

## Fixed

- [#300](https://github.com/hyperf/hyperf/pull/300) Let message queues run in sub-coroutines. Fixed async queue attempts twice to handle message, but only once actually.
- [#305](https://github.com/hyperf/hyperf/pull/305) Fixed `$key` of method `Arr::set` not support `int` and `null`.
- [#312](https://github.com/hyperf/hyperf/pull/312) Fixed amqp process collect listener will be handled later than the process boot listener.
- [#315](https://github.com/hyperf/hyperf/pull/315) Fixed config etcd center not work after worker restart or in user process.
- [#318](https://github.com/hyperf/hyperf/pull/318) Fixed service will register to service center ceaselessly.

## Changed

- [#323](https://github.com/hyperf/hyperf/pull/323) Force convert type of `$ttl` in annotation `Cacheable` and `CachePut` into int.

# v1.0.8 - 2019-07-31

## Added

- [#276](https://github.com/hyperf/hyperf/pull/276) Amqp consumer support multi routing_key.
- [#277](https://github.com/hyperf/hyperf/pull/277) Added etcd client and etcd config center.

## Changed

- [#297](https://github.com/hyperf/hyperf/pull/297) If register service failed, then sleep 10s and re-register, also hided the useless exception message when register service failed.
- [#298](https://github.com/hyperf/hyperf/pull/298) [#301](https://github.com/hyperf/hyperf/pull/301) Adapted openzipkin/zipkin v1.3.3+

## Fixed

- [#271](https://github.com/hyperf/hyperf/pull/271) Fixed aop only rewrite the first method in classes and method patten is not work.
- [#285](https://github.com/hyperf/hyperf/pull/285) Fixed anonymous class should not rewrite in proxy class.
- [#286](https://github.com/hyperf/hyperf/pull/286) Fixed not auto rollback when forgotten to commit or rollback in multi transactions.
- [#292](https://github.com/hyperf/hyperf/pull/292) Fixed `$default` is not work in method `Request::header`.
- [#293](https://github.com/hyperf/hyperf/pull/293) Fixed `$key` of method `Arr::get` not support `int` and `null`.

# v1.0.7 - 2019-07-26

## Fixed

- [#266](https://github.com/hyperf/hyperf/pull/266) Fixed timeout when produce a amqp message.
- [#273](https://github.com/hyperf/hyperf/pull/273) Fixed all services have been registered to Consul will be deleted by the last register action.
- [#274](https://github.com/hyperf/hyperf/pull/274) Fixed the content type of view response.

# v1.0.6 - 2019-07-24

## Added

- [#203](https://github.com/hyperf/hyperf/pull/203) [#236](https://github.com/hyperf/hyperf/pull/236) [#247](https://github.com/hyperf/hyperf/pull/247) [#252](https://github.com/hyperf/hyperf/pull/252) Added View component, support for Blade engine and Smarty engine.
- [#203](https://github.com/hyperf/hyperf/pull/203) Added support for Swoole Task mechanism.
- [#245](https://github.com/hyperf/hyperf/pull/245) Added TaskWorkerStrategy and WorkerStrategy crontab strategies.
- [#251](https://github.com/hyperf/hyperf/pull/251) Added coroutine memory driver for cache.
- [#254](https://github.com/hyperf/hyperf/pull/254) Added support for array value of `RequestMapping::$methods`, `@RequestMapping(methods={"GET"})` and `@RequestMapping(methods={RequestMapping::GET})` are available now.
- [#255](https://github.com/hyperf/hyperf/pull/255) Transfer `Hyperf\Utils\Contracts\Arrayable` result of Request to Response automatically, and added `text/plain` content-type header for string Response.
- [#256](https://github.com/hyperf/hyperf/pull/256) If `Hyperf\Contract\IdGeneratorInterface` exist, the `json-rpc` client will generate a Request ID via IdGenerator automatically, and stored in Request attibute. Also added support for service register and health checks of `jsonrpc` TCP protocol.

## Changed

- [#247](https://github.com/hyperf/hyperf/pull/247) Use `WorkerStrategy` as the default crontab strategy.
- [#256](https://github.com/hyperf/hyperf/pull/256) Optimized error handling of json-rpc, server will response a standard json-rpc error object when the rpc method does not exist.

## Fixed

- [#235](https://github.com/hyperf/hyperf/pull/235) Added default exception handler for `grpc-server` and optimized code.
- [#240](https://github.com/hyperf/hyperf/pull/240) Fixed OnPipeMessage event will be dispatch by another listener.
- [#257](https://github.com/hyperf/hyperf/pull/257) Fixed cannot get the Internal IP in some special environment.

# v1.0.5 - 2019-07-17

## Added

- [#185](https://github.com/hyperf/hyperf/pull/185) [#224](https://github.com/hyperf/hyperf/pull/224) Added support for xml format of response.
- [#202](https://github.com/hyperf/hyperf/pull/202) Added trace message when throw a uncaptured exception in function `go`.
- [#138](https://github.com/hyperf/hyperf/pull/138) [#197](https://github.com/hyperf/hyperf/pull/197) Added crontab component.

## Changed

- [#195](https://github.com/hyperf/hyperf/pull/195) Changed the behavior of parameter `$times` of `retry()` function, means the retry times of the callable function.
- [#198](https://github.com/hyperf/hyperf/pull/198) Optimized `has()` method of `Hyperf\Di\Container`, if pass a un-instantiable object (like an interface) to `$container->has($interface)`, the method result is `false` now.
- [#199](https://github.com/hyperf/hyperf/pull/199) Re-produce one times when the amqp message produce failure.
- [#200](https://github.com/hyperf/hyperf/pull/200) Make tests directory out of production package.

## Fixed

- [#176](https://github.com/hyperf/hyperf/pull/176) Fixed TypeError: Return value of LengthAwarePaginator::nextPageUrl() must be of the type string or null, none returned.
- [#188](https://github.com/hyperf/hyperf/pull/188) Fixed proxy of guzzle client does not work expected.
- [#211](https://github.com/hyperf/hyperf/pull/211) Fixed rpc client will be replaced by the latest one.
- [#212](https://github.com/hyperf/hyperf/pull/212) Fixed config `ssl_key` and `cert` of guzzle client does not work expected.

# v1.0.4 - 2019-07-08

## Added

- [#140](https://github.com/hyperf/hyperf/pull/140) Support Swoole v4.4.0.
- [#163](https://github.com/hyperf/hyperf/pull/163) Added custom arguments support to AbstractConstants::__callStatic in `hyperf/constants`.

## Changed

- [#124](https://github.com/hyperf/hyperf/pull/124) Added `$delay` parameter for `DriverInterface::push`, and marked `DriverInterface::delay` method to deprecated.
- [#125](https://github.com/hyperf/hyperf/pull/125) Changed the default value of parameter $default of config() function to null.

## Fixed

- [#110](https://github.com/hyperf/hyperf/pull/110) [#111](https://github.com/hyperf/hyperf/pull/111) Fixed Redis::select is not work expected.
- [#131](https://github.com/hyperf/hyperf/pull/131) Fixed property middlewares not work in `Router::addGroup`.
- [#132](https://github.com/hyperf/hyperf/pull/132) Fixed request->hasFile does not work expected.
- [#135](https://github.com/hyperf/hyperf/pull/135) Fixed response->redirect does not work expected.
- [#139](https://github.com/hyperf/hyperf/pull/139) Fixed the BaseUri of ConsulAgent will be replaced by default BaseUri.
- [#148](https://github.com/hyperf/hyperf/pull/148) Fixed cannot generate the migration when migrates directory does not exist.
- [#152](https://github.com/hyperf/hyperf/pull/152) Fixed db connection will not be closed when a low use frequency.
- [#169](https://github.com/hyperf/hyperf/pull/169) Fixed array parse failed when handle http request.
- [#170](https://github.com/hyperf/hyperf/pull/170) Fixed websocket server interrupt when request a not exist route.

## Removed

- [#131](https://github.com/hyperf/hyperf/pull/131) Removed `server` property from Router options.

# v1.0.3 - 2019-07-02

## Added

- [#48](https://github.com/hyperf/hyperf/pull/48) Added WebSocket Client.
- [#51](https://github.com/hyperf/hyperf/pull/51) Added property `enableCache` to `DefinitionSource` to enable annotation cache.
- [#61](https://github.com/hyperf/hyperf/pull/61) Added property type of `Model` created by command `db:model`.
- [#65](https://github.com/hyperf/hyperf/pull/65) Added JSON support for model-cache.
- Added WebSocket Server.

## Changed

- [#46](https://github.com/hyperf/hyperf/pull/46) Removed hyperf/framework requirement of `hyperf/di`, `hyperf/command` and `hyperf/dispatcher`.

## Fixed

- [#45](https://github.com/hyperf/hyperf/pull/55) Fixed http server start failed, when the skeleton included `hyperf/websocket-server`.
- [#55](https://github.com/hyperf/hyperf/pull/55) Fixed the method level middleware annotation.
- [#73](https://github.com/hyperf/hyperf/pull/73) Fixed short name is not work for `db:model`.
- [#88](https://github.com/hyperf/hyperf/pull/88) Fixed prefix is not right in deep directory.
- [#101](https://github.com/hyperf/hyperf/pull/101) Fixed constants resolution failed when no message annotation exists.

# v1.0.2 - 2019-06-25

## Added

- [#25](https://github.com/hyperf/hyperf/pull/25) Added Travis CI.
- [#29](https://github.com/hyperf/hyperf/pull/29) Added some paramater of `Redis::connect`.

## Fixed

- Fixed http server will be affected of websocket server.
- Fixed proxy class
- Fixed database pool will be fulled in testing.
- Fixed co-phpunit work not expected.
- Fixed model event `creating`, `updating` ... not work expected.
- Fixed `flushContext` not work expected for testing.
