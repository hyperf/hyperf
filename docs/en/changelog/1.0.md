# Changelogs

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
