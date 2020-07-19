# v2.0.3 - TBD

## Added

- [#2082](https://github.com/hyperf/hyperf/pull/2082) Added `SIGINT` listened by `Hyperf\Signal\Handler\WorkerStopHandler`.
- [#2097](https://github.com/hyperf/hyperf/pull/2097) Added TencentCloud COS for `hyperf/filesystem`.
- [#2122](https://github.com/hyperf/hyperf/pull/2122) Added `\Hyperf\Snowflake\Concern\HasSnowflake` Trait to integrate `hyperf/snowflake` and database models.

## Fixed

- [#2017](https://github.com/hyperf/hyperf/pull/2017) Fixed when prometheus using the redis record, an error is reported during the rendering of data due to the change in the number of label.
- [#2117](https://github.com/hyperf/hyperf/pull/2117) Fixed `@Inject` will be useless sometimes when using `server:watch`.
- [#2123](https://github.com/hyperf/hyperf/pull/2123) Fixed bug that `redis::call` will be recorded twice.

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
