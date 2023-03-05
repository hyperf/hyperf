# v3.0.9 - TBD

## Added

- [#5467](https://github.com/hyperf/hyperf/pull/5467) Support `Google\Rpc\Status` for grpc.

## Optimized

- [#5469](https://github.com/hyperf/hyperf/pull/5469) Ensure that the connection must be reset the next time after broken.

# v3.0.8 - 2023-02-26

## Fixed

- [#5433](https://github.com/hyperf/hyperf/pull/5433) [#5438](https://github.com/hyperf/hyperf/pull/5438) Fixed bug that the persistent service no need to send heartbeat.
- [#5464](https://github.com/hyperf/hyperf/pull/5464) Fixed bug that swagger server cannot work when using async style server.

## Added

- [#5434](https://github.com/hyperf/hyperf/pull/5434) Support UDP Server for Swow.
- [#5444](https://github.com/hyperf/hyperf/pull/5444) Added `GenSchemaCommand` to generate schemas for swagger.
- [#5451](https://github.com/hyperf/hyperf/pull/5451) Added method `appends($attributes)` to model collections.
- [#5453](https://github.com/hyperf/hyperf/pull/5453) Added missing methods `put()` and `patch()` to testing HTTP client.
- [#5454](https://github.com/hyperf/hyperf/pull/5454) Added method `Hyperf\Grpc\Parser::statusFromResponse`.
- [#5459](https://github.com/hyperf/hyperf/pull/5459) Added some methods of `uuid` and `ulid` for `Str` and `Stringable`.

## Optimized

- [#5437](https://github.com/hyperf/hyperf/pull/5437) Remove unnecessary `if `statement in `Str::length`.
- [#5439](https://github.com/hyperf/hyperf/pull/5439) Improve `Arr::shuffle`.

# v3.0.7 - 2023-02-18

## Added

- [#5042](https://github.com/hyperf/hyperf/pull/5402) Added `swagger.scan.paths` to rewrite `scan paths` for swagger.
- [#5403](https://github.com/hyperf/hyperf/pull/5403) Support swoole server settings for swow server.
- [#5404](https://github.com/hyperf/hyperf/pull/5404) Support multiport server for swagger.
- [#5406](https://github.com/hyperf/hyperf/pull/5406) Added `mixin` method to `Hyperf\Database\Model\Builder`.
- [#5407](https://github.com/hyperf/hyperf/pull/5407) Support HTTP methods `Delete` and `Options` for swagger.
- [#5409](https://github.com/hyperf/hyperf/pull/5409) Adds `methods` for `Query\Builder` and `Paginator`.
- [#5414](https://github.com/hyperf/hyperf/pull/5414) Added `clone` method to `Hyperf\Database\Model\Builder`.
- [#5418](https://github.com/hyperf/hyperf/pull/5418) Added `ConfigChanged` event to `config-center`.
- [#5429](https://github.com/hyperf/hyperf/pull/5429) Added `access_key` and `access_secret` which used to connect aliyun nacos.

## Fixed

- [#5405](https://github.com/hyperf/hyperf/pull/5405) Fixed get local ip error when IPv6 exists.
- [#5417](https://github.com/hyperf/hyperf/pull/5417) Fixed bug that database-pgsql does not support migration.
- [#5421](https://github.com/hyperf/hyperf/pull/5421) Fixed database about boolean types for where in the json type.
- [#5428](https://github.com/hyperf/hyperf/pull/5428) Fixed bug that metric middleware cannot work well when encountered an exception.
- [#5424](https://github.com/hyperf/hyperf/pull/5424) Fixed bug that migrator cannot work when using `PHP8.2`.

## Optimized

- [#5411](https://github.com/hyperf/hyperf/pull/5411) Optimized the code of `WebSocketHandeShakeException` which should inheritance `BadRequestHttpException`.
- [#5419](https://github.com/hyperf/hyperf/pull/5419) Optimized the code of `RPN`.
- [#5422](https://github.com/hyperf/hyperf/pull/5422) Enable swagger by default when installed swagger component.

# v3.0.6 - 2023-02-12

## Fixed

- [#5361](https://github.com/hyperf/hyperf/pull/5361) Fixed bug that the current service XXX is persistent service, can't register ephemeral instance.
- [#5382](https://github.com/hyperf/hyperf/pull/5382) Fixed bug that mix-subscriber cannot work caused by the empty auth.
- [#5386](https://github.com/hyperf/hyperf/pull/5386) Fixed bug that non-existing method `exec` called by `SwoolePostgresqlClient`.
- [#5394](https://github.com/hyperf/hyperf/pull/5394) Fixed bug that `hyperf/config-apollo` cannot work.

## Added

- [#5366](https://github.com/hyperf/hyperf/pull/5366) Added `forceDeleting` event to `hyperf/database`.
- [#5373](https://github.com/hyperf/hyperf/pull/5373) Support server settings for `SwowServer`.
- [#5376](https://github.com/hyperf/hyperf/pull/5376) Support coroutine server stats for `hyperf/metric`.
- [#5379](https://github.com/hyperf/hyperf/pull/5379) Added log records when nacos heartbeat failed.
- [#5389](https://github.com/hyperf/hyperf/pull/5389) Added swagger support.
- [#5395](https://github.com/hyperf/hyperf/pull/5395) Support validation for swagger.
- [#5397](https://github.com/hyperf/hyperf/pull/5397) Support all swagger annotations.

# v3.0.5 - 2023-02-05

## Added

- [#5338](https://github.com/hyperf/hyperf/pull/5338) Added `addRestoreOrCreate` extension to `SoftDeletingScope`.
- [#5349](https://github.com/hyperf/hyperf/pull/5349) Added `ResumeExitCoordinatorListener`.
- [#5355](https://github.com/hyperf/hyperf/pull/5355) Added `System::getCpuCoresNum()`.

## Fixed

- [#5357](https://github.com/hyperf/hyperf/pull/5357) Fixed bug that the coordinator timer can't stop when an exception occurs inside `$closure`.

## Optimized

- [#5342](https://github.com/hyperf/hyperf/pull/5342) Compatible with `tcp://host:port` configuration redis sentry address.

# v3.0.4 - 2023-01-22

## Fixed

- [#5332](https://github.com/hyperf/hyperf/pull/5332) Fixed bug that `PgSQLSwooleConnection::unprepared` cannot work.
- [#5333](https://github.com/hyperf/hyperf/pull/5333) Fixed bug that database cannot work when disconnect failed.

# v3.0.3 - 2023-01-16

## Fixed

- [#5318](https://github.com/hyperf/hyperf/pull/5318) Fixed bug that rate-limit cannot work when using php `8.1`.
- [#5324](https://github.com/hyperf/hyperf/pull/5324) Fixed bug that database cannot work when disconnect caused by connection reset by mysql.
- [#5322](https://github.com/hyperf/hyperf/pull/5322) Fixed bug that kafka consumer cannot work when don't set `memberId` and so on.
- [#5327](https://github.com/hyperf/hyperf/pull/5327) Fixed bug that PostgresSQL can't work when create connection timed out.

## Added

- [#5314](https://github.com/hyperf/hyperf/pull/5314) Added method `Hyperf\Coordinator\Timer::stats()`.
- [#5323](https://github.com/hyperf/hyperf/pull/5323) Added method `Hyperf\Nacos\Provider\ConfigProvider::listener()`.

## Optimized

- [#5308](https://github.com/hyperf/hyperf/pull/5308) [#5309](https://github.com/hyperf/hyperf/pull/5309) [#5310](https://github.com/hyperf/hyperf/pull/5310) [#5311](https://github.com/hyperf/hyperf/pull/5311) Added `CoroutineServer` Support for `hyperf/metric`.
- [#5315](https://github.com/hyperf/hyperf/pull/5315) Improve `hyperf/metric`.
- [#5326](https://github.com/hyperf/hyperf/pull/5326) Collect the metric of `Server::stats()` by loop.

# v3.0.2 - 2023-01-09

# Fixed

- [#5305](https://github.com/hyperf/hyperf/pull/5305) Fixed bug that commit failed when has no active transaction for polardb.
- [#5307](https://github.com/hyperf/hyperf/pull/5307) Fixed the parameter `$timeout` of `Timer::tick()` in `hyperf/metric`.

## Optimized

- [#5306](https://github.com/hyperf/hyperf/pull/5306) Log records when release to pool failed.

# v3.0.1 - 2023-01-09

## Fixed

- [#5289](https://github.com/hyperf/hyperf/pull/5289) Fixed bug that `signal` cannot work when using `swow`.
- [#5303](https://github.com/hyperf/hyperf/pull/5303) Fixed bug that redis nsq adapter cannot work when topics is null.

## Optimized

- [#5287](https://github.com/hyperf/hyperf/pull/5287) Added log records about the exception message when emit failed.
- [#5292](https://github.com/hyperf/hyperf/pull/5292) Support Swow for `hyperf/metric`.
- [#5301](https://github.com/hyperf/hyperf/pull/5301) Optimized code for `Hyperf\Rpc\PathGenerator\PathGenerator`.

# v3.0.0 - 2023-01-03

- [#4238](https://github.com/hyperf/hyperf/issues/4238) Upgraded the minimum php version to `^8.0` for all components;
- [#5087](https://github.com/hyperf/hyperf/pull/5087) Support PHP 8.2;

## BC breaks

- The framework removes `@Annotation` support, and uses `PHP8` native annotation `Attribute`. Before updating, be sure to check whether the project has been replaced by `Attribute`.

The following script can be executed to convert `Doctrine Annotations` to `PHP8 Attributes`.

**Note: This script can only be executed under version 2.2**

```shell
composer require hyperf/code-generator
php bin/hyperf.php code:generate -D app
```

- Database Model upgrade script

> Because the model base class has added type support for member variables, you need to use the following script to upgrade it to a new version.

```shell
composer require hyperf/code-generator
php vendor/bin/regenerate-models.php $PWD/app/Model
```

- The framework adds more type restrictions to the class library, so when updating from `2.2` to `3.0`, you need to run a static check to make sure it is works.

```shell
composer analysis
```

- The framework modifies the `Http status` returned by `gRPC Server` according to the `gRPC` specification. It is fixed at 200, and `gRPC Server` returns the corresponding `status code`. Service upgrade to version 3.x

## Dependencies Upgrade

- Upgraded `php-amqplib/php-amqplib` to `^3.1`;
- Upgraded `phpstan/phpstan` to `^1.0`;
- Upgraded `mix/redis-subscribe` to `mix/redis-subscriber:^3.0`
- Upgraded `psr/simple-cache` to `^1.0|^2.0|^3.0`
- Upgraded `monolog/monolog` to `^2.7|^3.1`
- Upgraded `league/flysystem` to `^1.0|^2.0|^3.0`

## Added

- [#4196](https://github.com/hyperf/hyperf/pull/4196) Added `Hyperf\Amqp\IO\IOFactory` which used to create amqp io by yourself.
- [#4304](https://github.com/hyperf/hyperf/pull/4304) Support `$suffix` for trait `Hyperf\Utils\Traits\StaticInstance`.
- [#4400](https://github.com/hyperf/hyperf/pull/4400) Added `$description` which used to set command description easily for `Hyperf\Command\Command`.
- [#4277](https://github.com/hyperf/hyperf/pull/4277) Added `Hyperf\Utils\IPReader` to get local IP.
- [#4497](https://github.com/hyperf/hyperf/pull/4497) Added `Hyperf\Coordinator\Timer` which can be stopped safely.
- [#4523](https://github.com/hyperf/hyperf/pull/4523) Support callback conditions for `Conditionable::when()` and `Conditionable::unless()`.
- [#4663](https://github.com/hyperf/hyperf/pull/4663) Make `Hyperf\Utils\Stringable` implements `Stringable`.
- [#4700](https://github.com/hyperf/hyperf/pull/4700) Support coroutine style server for `socketio-server`.
- [#4852](https://github.com/hyperf/hyperf/pull/4852) Added `NullDisableEventDispatcher` to disable event dispatcher by default.
- [#4866](https://github.com/hyperf/hyperf/pull/4866) [#4869](https://github.com/hyperf/hyperf/pull/4869) Added Annotation `Scene` which use scene in FormRequest easily.
- [#4908](https://github.com/hyperf/hyperf/pull/4908) Added `Db::beforeExecuting()` to register a hook which to be run just before a database query is executed.
- [#4909](https://github.com/hyperf/hyperf/pull/4909) Added `ConsumerMessageInterface::getNums()` to change the number of amqp consumer by dynamically.
- [#4918](https://github.com/hyperf/hyperf/pull/4918) Added `LoadBalancerInterface::afterRefreshed()` to register a hook which to be run after refresh nodes.
- [#4992](https://github.com/hyperf/hyperf/pull/4992) Added config `amqp.enable` which used to control amqp consumer whether to start automatically and producer whether to declare automatically.
- [#4994](https://github.com/hyperf/hyperf/pull/4994) [#5016](https://github.com/hyperf/hyperf/pull/5016) Added component `hyperf/database-pgsql` which you can be used to connect pgsql server.
- [#5007](https://github.com/hyperf/hyperf/pull/5007) Support for SSL encrypted connection to Redis.
- [#5046](https://github.com/hyperf/hyperf/pull/5046) Added `Hyperf\Database\Model\Concerns\HasAttributes::getRawOriginal()`.
- [#5052](https://github.com/hyperf/hyperf/pull/5052) Support parsing IPv6 host.
- [#5061](https://github.com/hyperf/hyperf/pull/5061) Added config `symfony.event.enable` to control whether to use `SymfonyEventDispatcher`.
- [#5163](https://github.com/hyperf/hyperf/pull/5163) Added `Pipeline::thenReturn()` method to run pipes and return the result
- [#5160](https://github.com/hyperf/hyperf/pull/5160) Added `$dictionary` for `Str::slug`, your can rewrite some tags easily.
- [#5186](https://github.com/hyperf/hyperf/pull/5186) Added option `config` for command `server:watch`.
- [#5206](https://github.com/hyperf/hyperf/pull/5206) Support the transformation of object type to AST nodes.
- [#5211](https://github.com/hyperf/hyperf/pull/5211) Added Annotation `CacheAhead` which used to cache data ahead.
- [#5227](https://github.com/hyperf/hyperf/pull/5227) Added `Hyperf\WebSocketServer\Sender::getResponses()`.
- [#5250](https://github.com/hyperf/hyperf/pull/5250) Added `defer_release` config in `hyperf/db`
- [#5261](https://github.com/hyperf/hyperf/pull/5261) Added requirement `ext-posix` for `watcher`.

## Optimized

- [#4147](https://github.com/hyperf/hyperf/pull/4147) Optimized code for nacos which you can use `http://xxx.com/yyy/` instead of `http://xxx.com:8848/` to connect `nacos`.
- [#4367](https://github.com/hyperf/hyperf/pull/4367) Optimized `DataFormatterInterface` which uses object instead of array as inputs.
- [#4547](https://github.com/hyperf/hyperf/pull/4547) Optimized code of `Str::contains` `Str::startsWith` and `Str::endsWith` based on `PHP8`.
- [#4596](https://github.com/hyperf/hyperf/pull/4596) Optimized `Hyperf\Context\Context` which support `coroutineId` for `set()` `override()` and `getOrSet()`.
- [#4658](https://github.com/hyperf/hyperf/pull/4658) The method name is used as the routing path, when the path is null in route annotations.
- [#4668](https://github.com/hyperf/hyperf/pull/4668) Optimized class `Hyperf\Utils\Str` whose methods `padBoth` `padLeft` and `padRight` support `multibyte`.
- [#4678](https://github.com/hyperf/hyperf/pull/4679) Close all another servers when one of them closed.
- [#4688](https://github.com/hyperf/hyperf/pull/4688) Added `SafeCaller` to avoid server shutdown which caused by exceptions.
- [#4715](https://github.com/hyperf/hyperf/pull/4715) Adjust the order of injections for controllers to avoid inject null preferentially.
- [#4865](https://github.com/hyperf/hyperf/pull/4865) No need to check `Redis::isConnected()`, because it could be connected defer or reconnected after disconnected.
- [#4874](https://github.com/hyperf/hyperf/pull/4874) Use `wait` instead of `parallel` for coroutine style tcp server.
- [#4875](https://github.com/hyperf/hyperf/pull/4875) Use the original style when regenerating models.
- [#4880](https://github.com/hyperf/hyperf/pull/4880) Support `ignoreAnnotations` for `Annotation Reader`.
- [#4888](https://github.com/hyperf/hyperf/pull/4888) Removed useless `Hyperf\Di\ClassLoader::$proxies`, because merge it into `Composer\Autoload\ClassLoader::$classMap`.
- [#4905](https://github.com/hyperf/hyperf/pull/4905) Removed the redundant parameters of method `Hyperf\Database\Model\Concerns\HasEvents::fireModelEvent()`.
- [#4949](https://github.com/hyperf/hyperf/pull/4949) Removed useless `call()` from `Coroutine::create()`.
- [#4961](https://github.com/hyperf/hyperf/pull/4961) Removed proxy mode from `Hyperf\Di\ClassLoader` and Optimized `Composer::getLoader()`.
- [#4981](https://github.com/hyperf/hyperf/pull/4981) Confirm before proceeding with the action when using `ConfirmableTrait`, such as `migrate` command.
- [#5017](https://github.com/hyperf/hyperf/pull/5017) Check validity of file descriptor before sending message to it when using `socketio-server`.
- [#5029](https://github.com/hyperf/hyperf/pull/5029) Removed useless method `call()` from `callable function`.
- [#5078](https://github.com/hyperf/hyperf/pull/5078) Optimized code about creating exception from another exception.
- [#5079](https://github.com/hyperf/hyperf/pull/5079) Catch exception for function `defer` by default.

## Changed

- [#4199](https://github.com/hyperf/hyperf/pull/4199) Changed the `public` property `$message` to `protected` for `Hyperf\AsyncQueue\Event\Event`.
- [#4214](https://github.com/hyperf/hyperf/pull/4214) Renamed `$circularDependences` to `$checkCircularDependencies` for `Dag`.
- [#4225](https://github.com/hyperf/hyperf/pull/4225) Split `hyperf/coordinator` from `hyperf/utils`.
- [#4269](https://github.com/hyperf/hyperf/pull/4269) Changed the default priority of listener to `0` from `1`.
- [#4345](https://github.com/hyperf/hyperf/pull/4345) Renamed `Hyperf\Kafka\Exception\ConnectionCLosedException` to `Hyperf\Kafka\Exception\ConnectionClosedException`.
- [#4434](https://github.com/hyperf/hyperf/pull/4434) The method `Hyperf\Database\Model\Builder::insertOrIgnore` will be return affected count.
- [#4495](https://github.com/hyperf/hyperf/pull/4495) Changed the default value to `null` for `Hyperf\DbConnection\Db::__connection()`.
- [#4460](https://github.com/hyperf/hyperf/pull/4460) Use `??` instead of `?:` for `$callback` when using `Stringable::when()`.
- [#4502](https://github.com/hyperf/hyperf/pull/4502) Use `Hyperf\Engine\Channel` instead of `Hyperf\Coroutine\Channel` in `hyperf/reactive-x`.
- [#4611](https://github.com/hyperf/hyperf/pull/4611) Changed return type to `void` for `Hyperf\Event\Contract\ListenerInterface::process()`.
- [#4669](https://github.com/hyperf/hyperf/pull/4669) Changed all annotations which only support `PHP` >= `8.0`.
- [#4678](https://github.com/hyperf/hyperf/pull/4678) Support event dispatcher for command by default.
- [#4680](https://github.com/hyperf/hyperf/pull/4680) Stop processes which controlled by `ProcessManager` when server shutdown.
- [#4848](https://github.com/hyperf/hyperf/pull/4848) Changed `$value.timeout` to `$options.timeout` for `CircuitBreaker`.
- [#4930](https://github.com/hyperf/hyperf/pull/4930) Renamed method `AnnotationManager::getFormatedKey()` to `AnnotationManager::getFormattedKey()`.
- [#4934](https://github.com/hyperf/hyperf/pull/4934) Throw `NoNodesAvailableException` when cannot select any node from load balancer.
- [#4952](https://github.com/hyperf/hyperf/pull/4952) Don't write pid when the `settings.pid_file` is null when using swow server.
- [#4979](https://github.com/hyperf/hyperf/pull/4979) Don't support database commands by default, please require `hyperf/devtool` or set them in `autoload/commands`.
- [#5008](https://github.com/hyperf/hyperf/pull/5008) Removed array type of `Trace Annotation`, because don't support array.
- [#5036](https://github.com/hyperf/hyperf/pull/5036) Changed grpc server StatsCode and serializeMessage.
- [#5601](https://github.com/hyperf/hyperf/pull/5061) Don't use `Hyperf\Framework\SymfonyEventDispatcher` by default, if you listen symfony events, you must open `symfony.event.enable`.
- [#5079](https://github.com/hyperf/hyperf/pull/5079) Use `(string) $throwable` instead of `sprintf` for `Hyperf\ExceptionHandler\Formatter\FormatterInterface::format()`.
- [#5091](https://github.com/hyperf/hyperf/pull/5091) Move `Jsonable` and `Xmlable` to `contract` from `utils`.
- [#5092](https://github.com/hyperf/hyperf/pull/5092) Move `MessageBag` and `MessageProvider` to `contract` from `utils`.
- [#5204](https://github.com/hyperf/hyperf/pull/5204) Transform the type of param `$server` in `Hyperf\WebSocketServer\Server::deferOnOpen()` to `mixed`.
- [#5239](https://github.com/hyperf/hyperf/pull/5239) Throw exception when using `chunkById` but the column is not existed.

## Swow Supported

- [#4756](https://github.com/hyperf/hyperf/pull/4756) Support `hyperf/amqp`.
- [#4757](https://github.com/hyperf/hyperf/pull/4757) Support `Hyperf\Utils\Coroutine\Locker`.
- [#4804](https://github.com/hyperf/hyperf/pull/4804) Support `Hyperf\Utils\WaitGroup`.
- [#4808](https://github.com/hyperf/hyperf/pull/4808) Replaced `Swoole\Coroutine\Channel` by `Hyperf\Engine\Channel` for all components.
- [#4873](https://github.com/hyperf/hyperf/pull/4873) Support `hyperf/websocket-server`.
- [#4917](https://github.com/hyperf/hyperf/pull/4917) Support `hyperf/load-balancer`.
- [#4924](https://github.com/hyperf/hyperf/pull/4924) Support TcpServer for `hyperf/server`.
- [#4984](https://github.com/hyperf/hyperf/pull/4984) Support `hyperf/retry`.
- [#4988](https://github.com/hyperf/hyperf/pull/4988) Support `hyperf/pool`.
- [#4989](https://github.com/hyperf/hyperf/pull/4989) Support `hyperf/crontab`.
- [#4990](https://github.com/hyperf/hyperf/pull/4990) Support `hyperf/nsq`.
- [#5070](https://github.com/hyperf/hyperf/pull/5070) Support `hyperf/signal`.

## Removed

- [#4199](https://github.com/hyperf/hyperf/pull/4199) Removed deprecated handler `Hyperf\AsyncQueue\Signal\DriverStopHandler`.
- [#4482](https://github.com/hyperf/hyperf/pull/4482) Removed deprecated `Hyperf\Utils\Resource`.
- [#4487](https://github.com/hyperf/hyperf/pull/4487) Removed log warning from cache component when the key is greater than 64 characters.
- [#4596](https://github.com/hyperf/hyperf/pull/4596) Removed `Hyperf\Utils\Context`, please use `Hyperf\Context\Context` instead.
- [#4623](https://github.com/hyperf/hyperf/pull/4623) Removed AliyunOssHook for `hyperf/filesystem`.
- [#4667](https://github.com/hyperf/hyperf/pull/4667) Removed `doctrine/annotations`, please use `PHP8 Attributes`.
- [#5226](https://github.com/hyperf/hyperf/pull/5226) Removed `WARNING` log message when amqp connection restart.

## Deprecated

- `Hyperf\Utils\Contracts\Arrayable` will be deprecated, please use `Hyperf\Contract\Arrayable` instead.
- `Hyperf\AsyncQueue\Message` will be deprecated, please use `Hyperf\AsyncQueue\JobMessage` instead.
- `Hyperf\Di\Container::getDefinitionSource()` will be deprecated.

## Fixed

- [#4549](https://github.com/hyperf/hyperf/pull/4549) Fixed bug that `PhpParser::getExprFromValue()` does not support assoc array.
- [#4835](https://github.com/hyperf/hyperf/pull/4835) Fixed the lost description when using property `$description` and `$signature` for `hyperf/command`.
- [#4851](https://github.com/hyperf/hyperf/pull/4851) Fixed bug that prometheus server will not be closed automatically when using command which enable event dispatcher.
- [#4854](https://github.com/hyperf/hyperf/pull/4854) Fixed bug that the `socket-io` client always reconnect when using coroutine style server.
- [#4885](https://github.com/hyperf/hyperf/pull/4885) Fixed bug that `ProxyTrait::__getParamsMap` can not work when using trait alias.
- [#4892](https://github.com/hyperf/hyperf/pull/4892) [#4895](https://github.com/hyperf/hyperf/pull/4895) Fixed bug that `RedisAdapter::mixSubscribe` cannot work cased by redis prefix when using `socketio-server`.
- [#4910](https://github.com/hyperf/hyperf/pull/4910) Fixed bug that method `ComponentTagCompiler::escapeSingleQuotesOutsideOfPhpBlocks()` cannot work.
- [#4912](https://github.com/hyperf/hyperf/pull/4912) Fixed bug that websocket connection will be closed after 10s when using `Swow`.
- [#4919](https://github.com/hyperf/hyperf/pull/4919) [#4921](https://github.com/hyperf/hyperf/pull/4921) Fixed bug that rpc connections can't refresh themselves after nodes changed when using `rpc-multiplex`.
- [#4920](https://github.com/hyperf/hyperf/pull/4920) Fixed bug that the routing path is wrong (like `//foo`) when the routing prefix is end of '/'.
- [#4940](https://github.com/hyperf/hyperf/pull/4940) Fixed memory leak caused by an exception which occurred in `Parallel`.
- [#5100](https://github.com/hyperf/hyperf/pull/5100) Fixed bug that the tag `continue` cannot work when using `view-engine`.
- [#5121](https://github.com/hyperf/hyperf/pull/5121) Fixed bug that the SQL is not valid but the correct error message cannot be obtained when using `pgsql`.
- [#5132](https://github.com/hyperf/hyperf/pull/5132) Fixed bug that the exit code of command does not work when the exception code isn't int.
- [#5142](https://github.com/hyperf/hyperf/pull/5142) Fixed bug that the method `Request::parseHost` does not work when host is invalid.
- [#5199](https://github.com/hyperf/hyperf/pull/5199) Fixed bug that `RedisSentinel` can't support empty password.
- [#5221](https://github.com/hyperf/hyperf/pull/5221) Fixed bug that `PGSqlSwooleConnection::affectingStatement()` can't work when the `sql` is wrong.
- [#5223](https://github.com/hyperf/hyperf/pull/5223) Fixed bug that `KeepaliveConnection::isTimeout()` can't work when using swow.
- [#5229](https://github.com/hyperf/hyperf/pull/5229) Fixed bug that proxy class will be generated failed when using parameters who allow null in constructor.
- [#5252](https://github.com/hyperf/hyperf/pull/5252) Fixed bug that generate rpc-client failed when the interface has parent interfaces.
- [#5268](https://github.com/hyperf/hyperf/pull/5268) Fixed bug that abstract methods will be written by `di`.
