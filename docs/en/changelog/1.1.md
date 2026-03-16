# Changelogs

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
