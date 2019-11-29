# v1.1.9 - TBD

## Fixed

- [#1049](https://github.com/hyperf/hyperf/pull/1049) Fixed `Hyperf\Cache\Driver\RedisDriver::clear` sometimes fails to delete all caches.

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
use Hyperf\Utils\ApplicationContext;

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
