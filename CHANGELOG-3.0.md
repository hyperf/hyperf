# v3.0.0 - TBD

- [#4238](https://github.com/hyperf/hyperf/issues/4238) Upgraded the minimum php version to `^8.0` for all components;

## BC breaks

- 框架移除了 `@Annotation` 的支持，全部使用 `PHP8` 原生注解 `Attribute`，更新前务必检查项目中，是否已经全部替换为 `Attribute`。

可以执行以下脚本，将 `Doctrine Annotations` 转化为 `PHP8 Attributes`.

**注意: 这个脚本只能在 2.2 版本下执行**

```shell
composer require hyperf/code-generator
php bin/hyperf.php code:generate -D app
```

- 升级模型脚本

> 因为模型基类增加了成员变量的类型支持，所以需要使用以下脚本，将其升级为新版本。

```shell
composer require hyperf/code-generator
php vendor/bin/regenerate-models.php $PWD/app/Model
```

- 框架为类库增加了更多的类型限制，所以从 `2.2` 更新到 `3.0` 版本时，需要跑一遍静态检测。

```shell
composer analyse
```

- 框架根据 `GRPC` 规范修改了 `GRPC Server` 返回的 `Http status` 固定为为 200， `GRPC Server` 返回对应的 `status code`,更新前如果有使用 `GRPC`,请务必将相关的服务升级到 3.x 版本

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
- [#5199](https://github.com/hyperf/hyperf/pull/5199) Fixed bug that `RedisSentinel` can't support empty password.
- [#5221](https://github.com/hyperf/hyperf/pull/5221) Fixed bug that `PGSqlSwooleConnection::affectingStatement()` can't work when the `sql` is wrong.
- [#5223](https://github.com/hyperf/hyperf/pull/5223) Fixed bug that `KeepaliveConnection::isTimeout()` can't work when using swow.
- [#5228](https://github.com/hyperf/hyperf/issues/5228) Fixed bug that proxy class will be generated failed when using parameters who allow null in constructor.
