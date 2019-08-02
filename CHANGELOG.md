# v1.0.9 - TBD

## Fixed

- [#300](https://github.com/hyperf-cloud/hyperf/pull/300) Let message queues run in sub-coroutines. Fixed async queue attempts twice to handle message, but only once actually.
- [#305](https://github.com/hyperf-cloud/hyperf/pull/305) Fixed `$key` of method `Arr::set` not support `int` and `null`.
- [#312](https://github.com/hyperf-cloud/hyperf/pull/312) Fixed amqp process collect listener will be handled later than the process boot listener.
- [#318](https://github.com/hyperf-cloud/hyperf/pull/318) Fixed service will register to service center ceaselessly.

# v1.0.8 - 2019-07-31

## Added

- [#276](https://github.com/hyperf-cloud/hyperf/pull/276) Amqp consumer support multi routing_key.
- [#277](https://github.com/hyperf-cloud/hyperf/pull/277) Added etcd client and etcd config center.

## Changed

- [#297](https://github.com/hyperf-cloud/hyperf/pull/297) If register service failed, then sleep 10s and re-register, also hided the useless exception message when register service failed.
- [#298](https://github.com/hyperf-cloud/hyperf/pull/298) [#301](https://github.com/hyperf-cloud/hyperf/pull/301) Adapted openzipkin/zipkin v1.3.3+

## Fixed

- [#271](https://github.com/hyperf-cloud/hyperf/pull/271) Fixed aop only rewrite the first method in classes and method patten is not work.
- [#285](https://github.com/hyperf-cloud/hyperf/pull/285) Fixed anonymous class should not rewrite in proxy class.
- [#286](https://github.com/hyperf-cloud/hyperf/pull/286) Fixed not auto rollback when forgotten to commit or rollback in multi transactions.
- [#292](https://github.com/hyperf-cloud/hyperf/pull/292) Fixed `$default` is not work in method `Request::header`.
- [#293](https://github.com/hyperf-cloud/hyperf/pull/293) Fixed `$key` of method `Arr::get` not support `int` and `null`.

# v1.0.7 - 2019-07-26

## Fixed

- [#266](https://github.com/hyperf-cloud/hyperf/pull/266) Fixed timeout when produce a amqp message.
- [#273](https://github.com/hyperf-cloud/hyperf/pull/273) Fixed all services have been registered to Consul will be deleted by the last register action. 
- [#274](https://github.com/hyperf-cloud/hyperf/pull/274) Fixed the content type of view response.

# v1.0.6 - 2019-07-24

## Added

- [#203](https://github.com/hyperf-cloud/hyperf/pull/203) [#236](https://github.com/hyperf-cloud/hyperf/pull/236) [#247](https://github.com/hyperf-cloud/hyperf/pull/247) [#252](https://github.com/hyperf-cloud/hyperf/pull/252) Added View component, support for Blade engine and Smarty engine. 
- [#203](https://github.com/hyperf-cloud/hyperf/pull/203) Added support for Swoole Task mechanism.
- [#245](https://github.com/hyperf-cloud/hyperf/pull/245) Added TaskWorkerStrategy and WorkerStrategy crontab strategies.
- [#251](https://github.com/hyperf-cloud/hyperf/pull/251) Added coroutine memory driver for cache.
- [#254](https://github.com/hyperf-cloud/hyperf/pull/254) Added support for array value of `RequestMapping::$methods`, `@RequestMapping(methods={"GET"})` and `@RequestMapping(methods={RequestMapping::GET})` are available now.
- [#255](https://github.com/hyperf-cloud/hyperf/pull/255) Transfer `Hyperf\Utils\Contracts\Arrayable` result of Request to Response automatically, and added `text/plain` content-type header for string Response.
- [#256](https://github.com/hyperf-cloud/hyperf/pull/256) If `Hyperf\Contract\IdGeneratorInterface` exist, the `json-rpc` client will generate a Request ID via IdGenerator automatically, and stored in Request attibute. Also added support for service register and health checks of `jsonrpc` TCP protocol.

## Changed

- [#247](https://github.com/hyperf-cloud/hyperf/pull/247) Use `WorkerStrategy` as the default crontab strategy.
- [#256](https://github.com/hyperf-cloud/hyperf/pull/256) Optimized error handling of json-rpc, server will response a standard json-rpc error object when the rpc method does not exist.

## Fixed

- [#235](https://github.com/hyperf-cloud/hyperf/pull/235) Added default exception handler for `grpc-server` and optimized code.
- [#240](https://github.com/hyperf-cloud/hyperf/pull/240) Fixed OnPipeMessage event will be dispatch by another listener.
- [#257](https://github.com/hyperf-cloud/hyperf/pull/257) Fixed cannot get the Internal IP in some special environment.

# v1.0.5 - 2019-07-17

## Added

- [#185](https://github.com/hyperf-cloud/hyperf/pull/185) [#224](https://github.com/hyperf-cloud/hyperf/pull/224) Added support for xml format of response.
- [#202](https://github.com/hyperf-cloud/hyperf/pull/202) Added trace message when throw a uncaptured exception in function `go`.
- [#138](https://github.com/hyperf-cloud/hyperf/pull/138) [#197](https://github.com/hyperf-cloud/hyperf/pull/197) Added crontab component.

## Changed

- [#195](https://github.com/hyperf-cloud/hyperf/pull/195) Changed the behavior of parameter `$times` of `retry()` function, means the retry times of the callable function.
- [#198](https://github.com/hyperf-cloud/hyperf/pull/198) Optimized `has()` method of `Hyperf\Di\Container`, if pass a un-instantiable object (like an interface) to `$container->has($interface)`, the method result is `false` now.
- [#199](https://github.com/hyperf-cloud/hyperf/pull/199) Re-produce one times when the amqp message produce failure.
- [#200](https://github.com/hyperf-cloud/hyperf/pull/200) Make tests directory out of production package.

## Fixed

- [#176](https://github.com/hyperf-cloud/hyperf/pull/176) Fixed TypeError: Return value of LengthAwarePaginator::nextPageUrl() must be of the type string or null, none returned.
- [#188](https://github.com/hyperf-cloud/hyperf/pull/188) Fixed proxy of guzzle client does not work expected.
- [#211](https://github.com/hyperf-cloud/hyperf/pull/211) Fixed rpc client will be replaced by the latest one. 
- [#212](https://github.com/hyperf-cloud/hyperf/pull/212) Fixed config `ssl_key` and `cert` of guzzle client does not work expected.

# v1.0.4 - 2019-07-08

## Added

- [#140](https://github.com/hyperf-cloud/hyperf/pull/140) Support Swoole v4.4.0.
- [#163](https://github.com/hyperf-cloud/hyperf/pull/163) Added custom arguments support to AbstractConstants::__callStatic in `hyperf/constants`.

## Changed

- [#124](https://github.com/hyperf-cloud/hyperf/pull/124) Added `$delay` parameter for `DriverInterface::push`, and marked `DriverInterface::delay` method to deprecated. 
- [#125](https://github.com/hyperf-cloud/hyperf/pull/125) Changed the default value of parameter $default of config() function to null.

## Fixed

- [#110](https://github.com/hyperf-cloud/hyperf/pull/110) [#111](https://github.com/hyperf-cloud/hyperf/pull/111) Fixed Redis::select is not work expected.
- [#131](https://github.com/hyperf-cloud/hyperf/pull/131) Fixed property middlewares not work in `Router::addGroup`.
- [#132](https://github.com/hyperf-cloud/hyperf/pull/132) Fixed request->hasFile does not work expected.
- [#135](https://github.com/hyperf-cloud/hyperf/pull/135) Fixed response->redirect does not work expected.
- [#139](https://github.com/hyperf-cloud/hyperf/pull/139) Fixed the BaseUri of ConsulAgent will be replaced by default BaseUri.
- [#148](https://github.com/hyperf-cloud/hyperf/pull/148) Fixed cannot generate the migration when migrates directory does not exist.
- [#152](https://github.com/hyperf-cloud/hyperf/pull/152) Fixed db connection will not be closed when a low use frequency.
- [#169](https://github.com/hyperf-cloud/hyperf/pull/169) Fixed array parse failed when handle http request.
- [#170](https://github.com/hyperf-cloud/hyperf/pull/170) Fixed websocket server interrupt when request a not exist route.

## Removed

- [#131](https://github.com/hyperf-cloud/hyperf/pull/131) Removed `server` property from Router options.

# v1.0.3 - 2019-07-02

## Added

- [#48](https://github.com/hyperf-cloud/hyperf/pull/48) Added WebSocket Client.
- [#51](https://github.com/hyperf-cloud/hyperf/pull/51) Added property `enableCache` to `DefinitionSource` to enable annotation cache. 
- [#61](https://github.com/hyperf-cloud/hyperf/pull/61) Added property type of `Model` created by command `db:model`.
- [#65](https://github.com/hyperf-cloud/hyperf/pull/65) Added JSON support for model-cache.
- Added WebSocket Server.

## Changed

- [#46](https://github.com/hyperf-cloud/hyperf/pull/46) Removed hyperf/framework requirement of `hyperf/di`, `hyperf/command` and `hyperf/dispatcher`. 

## Fixed

- [#45](https://github.com/hyperf-cloud/hyperf/pull/55) Fixed http server start failed, when the skeleton included `hyperf/websocket-server`. 
- [#55](https://github.com/hyperf-cloud/hyperf/pull/55) Fixed the method level middleware annotation. 
- [#73](https://github.com/hyperf-cloud/hyperf/pull/73) Fixed short name is not work for `db:model`.
- [#88](https://github.com/hyperf-cloud/hyperf/pull/88) Fixed prefix is not right in deep directory.
- [#101](https://github.com/hyperf-cloud/hyperf/pull/101) Fixed constants resolution failed when no message annotation exists.

# v1.0.2 - 2019-06-25

## Added

- [#25](https://github.com/hyperf-cloud/hyperf/pull/25) Added Travis CI.
- [#29](https://github.com/hyperf-cloud/hyperf/pull/29) Added some paramater of `Redis::connect`.

## Fixed

- Fixed http server will be affected of websocket server.
- Fixed proxy class 
- Fixed database pool will be fulled in testing.
- Fixed co-phpunit work not expected.
- Fixed model event `creating`, `updating` ... not work expected.
- Fixed `flushContext` not work expected for testing.
