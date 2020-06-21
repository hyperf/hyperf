# v2.0 - TBD

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
