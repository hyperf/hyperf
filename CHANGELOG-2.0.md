# v2.0.26 - TBD

## Fixed

- [#3047](https://github.com/hyperf/hyperf/pull/3047) Fixed bug that renew sid in all namespaces failed.
- [#3087](https://github.com/hyperf/hyperf/pull/3087) Fixed memory leak when using pipeline sometimes.
- [#3179](https://github.com/hyperf/hyperf/pull/3179) Fixed json-rpc client failed to receive data when the target server restart.
- [#3222](https://github.com/hyperf/hyperf/pull/3222) Fixed memory leak for join queries in `hyperf/database`.

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
- [#2475](https://github.com/hyperf/hyperf/pull/2475) Added `throwbale` to the end of arguments of fallback for `retry` component.

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
- [#2438](https://github.com/hyperf/hyperf/pull/2438) [#2452](https://github.com/hyperf/hyperf/pull/2452) Optimized code for deleting model cache when model deleted or saved in transaction.

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
