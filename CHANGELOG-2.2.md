# v2.2.36 - TBD

# v2.2.35 - 2022-08-27

## Fixed

- [#5062](https://github.com/hyperf/hyperf/pull/5062) Fixed bug that cannot stop server by `Ctrl C`.

## Optimized

- [#5002](https://github.com/hyperf/hyperf/pull/5002) Optimized the generation rules of rpc proxy class suffix, which can be customized more easily.
- [#5005](https://github.com/hyperf/hyperf/pull/5005) Don't generate rpc proxy again when open `scan cacheable`.

# v2.2.34 - 2022-07-21

## Fixed

- [#4948](https://github.com/hyperf/hyperf/pull/4948) Fixed memory leak caused by an exception which occurred in `Parallel`.

# v2.2.33.1 - 2022-05-30

## Fixed

- [#4796](https://github.com/hyperf/hyperf/pull/4796) Fixed id repeat when generate snowflake id caused by calling `init` more than once.

# v2.2.33 - 2022-05-30

## Fixed

- [#4776](https://github.com/hyperf/hyperf/pull/4776) Fixed bug that graphql event collect failed.
- [#4790](https://github.com/hyperf/hyperf/pull/4790) Fixed bug that rpn method `toRPNExpression` does not work in some cases.

## Added

- [#4763](https://github.com/hyperf/hyperf/pull/4763) Added validation rule `array:key1,key2` which make sure the array has no keys other than `key1` and `key2`.
- [#4781](https://github.com/hyperf/hyperf/pull/4781) Added `close-pull-request.yml` to close pr for `read-only` repositories.

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
