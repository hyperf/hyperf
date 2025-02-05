# v3.1.51 - TBD

## Added

- [#7250](https://github.com/hyperf/hyperf/pull/7250) Added `Hyperf\AsyncQueue\Driver\Driver::getConfig()`.
- [#7263](https://github.com/hyperf/hyperf/pull/7263) Added `Hyperf\Coroutine\WaitConcurrent`.

# v3.1.50 - 2025-01-09

## Fixed

- [#7231](https://github.com/hyperf/hyperf/pull/7231) Fixed fatal exception "Object of class ... could not be converted to string" when using invalid websocket message handler.

## Added

- [#7224](https://github.com/hyperf/hyperf/pull/7224) Added `Hyperf\Coroutine\Mutex`.
- [#7233](https://github.com/hyperf/hyperf/pull/7233) Added params `connection_name` which used to define amqp connection name.

## Optimized

- [#7223](https://github.com/hyperf/hyperf/pull/7223) Optimized code for reading extra data from `composer.lock`.
- [#7227](https://github.com/hyperf/hyperf/pull/7227) Improves phpstan docs for `Hyperf\Support\SafeCaller`.

# v3.1.49 - 2024-12-17

## Added

- [#7219](https://github.com/hyperf/hyperf/pull/) Added case sensitivity option to `Hyperf\Stringable\Str::is()`.

## Optimized

- [#7206](https://github.com/hyperf/hyperf/pull/7206) Support `swoole` version `6.0`.
- [#7209](https://github.com/hyperf/hyperf/pull/7209) Optimized collection usage in `getUrlRange` method.

# v3.1.48 - 2024-12-12

## Optimized

- [#7194](https://github.com/hyperf/hyperf/pull/7194) Optimized the database exception to implement unique constraint error detection.
- [#7187](https://github.com/hyperf/hyperf/pull/7187) Allow request IP retrieval during tests via server parameters.

## Fixed

- [#7188](https://github.com/hyperf/hyperf/pull/7188) Fixed bug that `Hyperf\HttpMessage\Server\ResponsePlusProxy` cannot support another responses without `getCookies`.

## Added

- [#7192](https://github.com/hyperf/hyperf/pull/7192) Added `Hyperf\Database\Model\Collection::findOrFail()`.

# v3.1.47 - 2024-11-28

## Fixed

- [#7176](https://github.com/hyperf/hyperf/pull/7176) Fixed bug that cookies cannot work when using `swow`.

# v3.1.46 - 2024-11-21

## Added

- [#7148](https://github.com/hyperf/hyperf/pull/7148) Added `exclude` rules for `hyperf/validation`.
- [#7150](https://github.com/hyperf/hyperf/pull/7150) Added missing methods (`accepted_if`, `ascii`, `date_equals`...) to validation messages.
- [#7151](https://github.com/hyperf/hyperf/pull/7151) Added hooks `beforeTestInCoroutine` and `afterTestInCoroutine` for `Hyperf\Testing\Concerns\RunTestsInCoroutine::runTestsInCoroutine()`.
- [#7156](https://github.com/hyperf/hyperf/pull/7156) Added method `Hyperf\Database\Schema\Blueprint::engine()`.

# v3.1.45 - 2024-11-14

## Added

- [#7141](https://github.com/hyperf/hyperf/pull/7141) Added method `Hyperf\Collection\Arr::shuffleAssoc`.
- [#7143](https://github.com/hyperf/hyperf/pull/7143) Added method `Hyperf\Database\Model\Builder::findOr`.
- [#7147](https://github.com/hyperf/hyperf/pull/7147) Added `setVisible` and `setHidden` into `Model\Collection`.
- [#7149](https://github.com/hyperf/hyperf/pull/7149) Support to define `servers` and `info` for `swagger`.

## Fixed

- [#7133](https://github.com/hyperf/hyperf/pull/7133) Fixed bug that the connection of migrations cannot work excepted.

# v3.1.44 - 2024-10-24

## Added

- [#7063](https://github.com/hyperf/hyperf/pull/7063) Added methods `nullableUuidMorphs` `uuidMorphs` and `nullableNumericMorphs` to `Hyperf\Database\Schema\Blueprint`.
- [#7070](https://github.com/hyperf/hyperf/pull/7070) Added `Blueprint::charset()` and `Blueprint::collation()`.
- [#7071](https://github.com/hyperf/hyperf/pull/7071) Added `Hyperf\Database\Schema\Blueprint::tinyText()`.
- [#7110](https://github.com/hyperf/hyperf/pull/7110) Added support for disallowing class morphs.

## Fixed

- [#7124](https://github.com/hyperf/hyperf/pull/7124) Fixed bug that `sortByMany` value is null when using `SORT_NATURAL`.

# v3.1.43 - 2024-10-10

## Fixed

- [#7068](https://github.com/hyperf/hyperf/pull/7068) Fixed bug `Str::trim` cannot support the default rules "\n\r\t\v" for `trim/ltrim/rtim`.
- [#7109](https://github.com/hyperf/hyperf/pull/7109) Fixed bug that `CacheAHead` cannot use the default ttl.

## Optimized

- [#7082](https://github.com/hyperf/hyperf/pull/7082) Optimized the code of `Hyperf\Database\Query\Grammars\Grammar::compileUpdate()`.
- [#7084](https://github.com/hyperf/hyperf/pull/7084) Optimized the code of `Hyperf\Watcher\Ast\RewriteClassNameVisitor::leaveNode()`.
- [#7105](https://github.com/hyperf/hyperf/pull/7105) Removed `env_vars` to keep the child process environment variables consistent with the parent process.

## Added

- [#7025](https://github.com/hyperf/hyperf/pull/7025) Added `Hyperf\Database\Model\Relations\Relation::getMorphAlias()`.

# v3.1.42 - 2024-09-25

## Fixed

- [#7081](https://github.com/hyperf/hyperf/pull/7081) Fixed bug that `data_get` cannot support `int` key. 

## Optimized

- [#7088](https://github.com/hyperf/hyperf/pull/7088) Optimized github actions for all components.

# v3.1.41 - 2024-09-19

## Added

- [#7059](https://github.com/hyperf/hyperf/pull/7059) Added `Hyperf\Database\Schema\Schema::getForeignKeys()`.
- [#7064](https://github.com/hyperf/hyperf/pull/7064) Support db type `enum` for `DoctrineConnection`.
- [#7077](https://github.com/hyperf/hyperf/pull/7077) Added `ModelUpdateVisitor::getReturnType` method.

# v3.1.40 - 2024-09-12

## Fixed

- [#7051](https://github.com/hyperf/hyperf/pull/7051) Fixed bug that `--database` option does not work for `migrate` command.

## Optimized

- [#7053](https://github.com/hyperf/hyperf/pull/7053) Convert `$value` and `$pattern` to string for `Str::is()`.

# v3.1.39 - 2024-09-05

## Fixed

- [#7034](https://github.com/hyperf/hyperf/pull/7034) Fixed bug that the mount code will break the main file with `declare` when using `phar:build`.
- [#7043](https://github.com/hyperf/hyperf/pull/7043) Fixed bug that `jsonrpc-http` cannot support `swow`.

## Optimized

- [#7033](https://github.com/hyperf/hyperf/pull/7033) Improved `ConsoleLogger` to support running in watcher.
- [#7040](https://github.com/hyperf/hyperf/pull/7040) Improved packaging speed for command `phar:build`. 
- [#7044](https://github.com/hyperf/hyperf/pull/7044) Optimized the argument `table` like `database.table` for `gen:model` which can be used to generate another database models.

## Added

- [#7024](https://github.com/hyperf/hyperf/pull/7024) Added methods `whenTableHasColumn` and `whenTableDoesntHaveColumn` to `Hyperf\Database\Schema\Schema`.

# v3.1.38 - 2024-08-29

## Added

- [#7016](https://github.com/hyperf/hyperf/pull/7016) Added reorder method to clear and set sorting for `QueryBuilder`.
- [#7023](https://github.com/hyperf/hyperf/pull/7023) Added `Hyperf\Contract\CanBeEscapedWhenCastToString` and fixed some static detection.
- [#7028](https://github.com/hyperf/hyperf/pull/7028) Added `Hyperf\Framework\Logger\ConsoleLogger`.

# v3.1.37 - 2024-08-22

## Added

- [#7015](https://github.com/hyperf/hyperf/pull/7015) Added methods `whereNone` and `orWhereNone` to `Hyperf\Database\Query\Builder`.

## Optimized

- [#6839](https://github.com/hyperf/hyperf/pull/6839) Use `anonymous classes` to avoid the duplicated class name for database migrations.

# v3.1.36 - 2024-08-15

## Added

- [#6971](https://github.com/hyperf/hyperf/pull/6971) Added partitioned support for Cookie.
- [#6990](https://github.com/hyperf/hyperf/pull/6990) Added support for retrieving the current system time with milliseconds for `Hyperf\Support\Traits\InteractsWithTime`.
- [#6998](https://github.com/hyperf/hyperf/pull/6998) Added default methods for `#[AutoController]`. (You can add method `options` which used to support cors middleware)

# v3.1.35 - 2024-08-08

## Fixed

- [#6987](https://github.com/hyperf/hyperf/pull/6987) Fixed bug that the root path of swagger server cannot work.

# v3.1.34 - 2024-08-01

## Added

- [#6978](https://github.com/hyperf/hyperf/pull/6978) Support static method for `#[AsCommand]`.
- [#6979](https://github.com/hyperf/hyperf/pull/6979) Added command `queue:dynamic-reload`.

# v3.1.33 - 2024-07-25

## Optimized

- [#6962](https://github.com/hyperf/hyperf/pull/6962) Optimized error message of command exceptions.
- [#6963](https://github.com/hyperf/hyperf/pull/6963) Optimized code for `Model/JsonResource::toJson`.

## Fixed

- [#6954](https://github.com/hyperf/hyperf/pull/6954) Fixed bug that the connection cannot reconnect to the server in a situation where there was a failover and exchange of read and write hosts.
- [#6961](https://github.com/hyperf/hyperf/pull/6961) Fixed bug that `websocket sender` not support `Swow`.

# v3.1.32 - 2024-07-18

## Fixed

- [#6949](https://github.com/hyperf/hyperf/pull/6949) Fixed bug that restart failed when don't have `.env`.
- [#6953](https://github.com/hyperf/hyperf/pull/6953) Fixed bug that socketio-server cannot work for swow engine.

## Optimized

- [#6946](https://github.com/hyperf/hyperf/pull/6946) Removed Swoole Atomic dependency and useless restart counter in watcher.

## Added

- [#6950](https://github.com/hyperf/hyperf/pull/6950) Support `where bit functions and operators` for `database`.

# v3.1.31 - 2024-07-11

## Added

- [#6936](https://github.com/hyperf/hyperf/pull/6936) Support to reload `.env` when using `hyperf/watcher`.

# v3.1.30 - 2024-07-05

## Fixed

- [#6925](https://github.com/hyperf/hyperf/pull/6925) Fixed bug that `sortByMany` don't reset indexes for `Collection`. But it will be return the same result like `sortBy` in `v3.2`.

# v3.1.29 - 2024-07-04

## Fixed

- [#6925](https://github.com/hyperf/hyperf/pull/6925) Fixed bug that `sortByMany` cannot support options like `sortBy`.

## Added

- [#6896](https://github.com/hyperf/hyperf/pull/6896) Added `SftpAdapter` for `hyperf/filesystem`.
- [#6917](https://github.com/hyperf/hyperf/pull/6917) Added `Str::chopStart` and `Str::chopEnd`.

# v3.1.28 - 2024-06-27

## Fixed

- [#6900](https://github.com/hyperf/hyperf/pull/6900) Fixed bug that `LengthAwarePaginator::addQuery()` cannot support array `$values`.
- [#6909](https://github.com/hyperf/hyperf/pull/6909) Fixed bug that `Aop` doesn't work on `Trait`.

## Optimized

- [#6903](https://github.com/hyperf/hyperf/pull/6903) Optimized code for `config-nacos`.

## Added

- [#6885](https://github.com/hyperf/hyperf/pull/6885) Added validation rule `prohibiti`.
- [#6891](https://github.com/hyperf/hyperf/pull/6891) Support `cache.*.options.pool` to select redis instance for `hyperf/cache`.
- [#6895](https://github.com/hyperf/hyperf/pull/6895) Support to collect enum annotations.

# v3.1.27 -  2024-06-20

## Added

- [#6864](https://github.com/hyperf/hyperf/pull/6864) Added methods `getViews` and `hasView` into `Hyperf\Database\Schema\Schema`.
- [#6866](https://github.com/hyperf/hyperf/pull/6866) Added method `Hyperf\Database\Concerns\BuildsQueries::lazy`.
- [#6869](https://github.com/hyperf/hyperf/pull/6869) Added methods `before` and `after` into `Collection`.
- [#6876](https://github.com/hyperf/hyperf/pull/6876) Added method `Hyperf\Database\Concerns\Builder::eachById`.
- [#6878](https://github.com/hyperf/hyperf/pull/6878) Added methods `whereMorphRelation` and `orWhereMorphRelation` into `Hyperf\Database\Model\Concerns\QueriesRelationships`.
- [#6883](https://github.com/hyperf/hyperf/pull/6883) Added methods `getIndexes` `hasIndex` and `getIndexListing` into `Hyperf\Database\Schema\Builder`.
- [#6884](https://github.com/hyperf/hyperf/pull/6884) Added method `Hyperf\Database\Model\Model::updateOrFail`.
- [#6897](https://github.com/hyperf/hyperf/pull/6897) [#6899](https://github.com/hyperf/hyperf/pull/6899) Added events `BeforeLongLangConsumerCreated` and `AfterConsumerConfigCreated` into `Hyperf\Kafka\ConsumerManager`.

## Optimized

- [#6829](https://github.com/hyperf/hyperf/pull/6829) Optimized the format of command error logs.
- [#6868](https://github.com/hyperf/hyperf/pull/6868) Support type `Closure|Expression|ModelBuilder|static|string` of `$column` for `QueryBuilder::orderBy()`.
- [#6870](https://github.com/hyperf/hyperf/pull/6870) Updated default path for factories in Model Factory construct method.
- [#6874](https://github.com/hyperf/hyperf/pull/6874) Using `Scanner` instead of hard code for `hyperf/watcher` component.

# v3.1.26 - 2024-06-13

## Fixed

- [#6848](https://github.com/hyperf/hyperf/pull/6848) Fixed bug that `LazyCollection::splitIn()` cannot work caused by type hint.

## Added

- [#6845](https://github.com/hyperf/hyperf/pull/6845) Added method `Hyperf\Database\Schema::getTables()`.
- [#6846](https://github.com/hyperf/hyperf/pull/6846) Added methods `chunkById` and `chunkByIdDesc` into `Hyperf\Database\Concerns\BuildsQueries`.
- [#6851](https://github.com/hyperf/hyperf/pull/6851) Added methods `orDoesntHaveMorph` and `orHasMorph` into `Hyperf\Database\Model\Concerns`.
- [#6858](https://github.com/hyperf/hyperf/pull/6858) Added methods `makeHiddenIf` and `makeVisibleIf` into `Hyperf\Database\Model\Concerns\HidesAttributes`.

## Optimized

- [#6855](https://github.com/hyperf/hyperf/pull/6855) Optimized BuildsQueries to use `Conditionable` instead of `when` and `unless`.
- [#6856](https://github.com/hyperf/hyperf/pull/6856) Optimized `Hyperf\Scout\Builder` to use `Conditionable` instead of `when` and `unless`.
- [#6860](https://github.com/hyperf/hyperf/pull/6860) Use `Hyperf\Collection\Enumerable` instead of `Hyperf\ViewEngine\Contract\Enumerable`.

# v3.1.25.1 - 2024-06-07

## Added

- [#6837](https://github.com/hyperf/hyperf/pull/6837) Added method `Model\Concerns\QueriesRelationships::withWhereHas()`.
- [#6844](https://github.com/hyperf/hyperf/pull/6844) Added methods `whereRelation` and `orWhereRelation` into `Hyperf\Database\Model\Concerns\QueriesRelationships`.

## Optimized

- [#6843](https://github.com/hyperf/hyperf/pull/6843) [#6847](https://github.com/hyperf/hyperf/pull/6847) Updated return type hints in `Collection` and `LazyCollection`.

# v3.1.25 - 2024-06-06

## Added

- [#6809](https://github.com/hyperf/hyperf/pull/6809) Added cursor paginator for `hyperf/database`.
- [#6811](https://github.com/hyperf/hyperf/pull/6811) Added `list` rule for `hyperf/validation`.
- [#6814](https://github.com/hyperf/hyperf/pull/6814) Added method `Model::query()->touch()` which used to update timestamps.
- [#6815](https://github.com/hyperf/hyperf/pull/6815) Added method `Hyperf\Database\Model\Builder::qualifyColumns()`.
- [#6816](https://github.com/hyperf/hyperf/pull/6816) Added methods `Hyperf\Database\Model\Builder::load*`.
- [#6820](https://github.com/hyperf/hyperf/pull/6820) Added method `Hyperf\Database\Model\Builder::valueOrFail()`. 
- [#6821](https://github.com/hyperf/hyperf/pull/6821) Added method `Hyperf\Database\Concerns\BuildsQueries::chunkMap()`.
- [#6822](https://github.com/hyperf/hyperf/pull/6822) Added methods `lazyById` and `lazyByIdDesc` for lazy queries.
- [#6825](https://github.com/hyperf/hyperf/pull/6825) Added methods `createDatabase` and `dropDatabaseIfExists` for `Hyperf\Database\Schmea`.

## Fixed

- [#6813](https://github.com/hyperf/hyperf/pull/6813) Fixed bug that cannot read the messages from non-lower keys for `Hyperf\Constants\Annotation\Message`.
- [#6818](https://github.com/hyperf/hyperf/pull/6818) Fixed bug that `updateOrInsert` cannot work when the input is empty.
- [#6828](https://github.com/hyperf/hyperf/pull/6828) Fixed bug that `AOP` cannot work on `__construct`.
- [#6836](https://github.com/hyperf/hyperf/pull/6836) Fixed bug that `SetCookie::fromString` cannot not work by invalid types.

# v3.1.24 - 2024-05-30

## Fixed

- [#6796](https://github.com/hyperf/hyperf/pull/6796) [#6798](https://github.com/hyperf/hyperf/pull/6798) Fixed bug that the return type of `Collection::mapInto()` is invalid sometimes.

## Added

- [#6792](https://github.com/hyperf/hyperf/pull/6792) Support `IncrementEach` and `DecrementEach` for `Hyperf\Database\Query\Builder`.
- [#6793](https://github.com/hyperf/hyperf/pull/6793) Added the request body and response body to the tracer.
- [#6795](https://github.com/hyperf/hyperf/pull/6795) Added config `rate_limit.storage.options.expired_time` for `rate-limit`.

## Optimized

- [#6778](https://github.com/hyperf/hyperf/pull/6788) Support array for `Hyperf\Amqp\Annotation\Consumer::routingKey`.
- [#6799](https://github.com/hyperf/hyperf/pull/6799) Support `numbers` and `fromBase64` methods for `\Hyperf\Stringable\Str`.
- [#6803](https://github.com/hyperf/hyperf/pull/6803) Cancel AsCommand and ClosureCommand return values.

# v3.1.23 - 2024-05-23

## Added

- [#6757](https://github.com/hyperf/hyperf/pull/6757) Added `Hyperf\Collection\LazyCollection`.
- [#6763](https://github.com/hyperf/hyperf/pull/6763) Added `Premature end of data` into `DetectsLostConnections`.
- [#6767](https://github.com/hyperf/hyperf/pull/6767) Support `whereAll/orWhereAll` `whereAny/orWhereAny` for `Hyperf\Database\Query\Builder`.
- [#6774](https://github.com/hyperf/hyperf/pull/6774) Support Lateral Join for `Hyperf\Database\Query\Builder`.
- [#6781](https://github.com/hyperf/hyperf/pull/6781) Added some methods to `Hyperf\Collection\Arr`.
- [#6782](https://github.com/hyperf/hyperf/pull/6782) Added `whereJsonOverlaps`,`orWhereJsonOverlaps` and `whereJsonDoesntOverlap` to `Hyperf\Database\Query\Builder`.
- [#6783](https://github.com/hyperf/hyperf/pull/6783) Support `insertOrIgnoreUsing` for `Hyperf\Database\Query\Builder`.
- [#6784](https://github.com/hyperf/hyperf/pull/6784) Added `getOrPut` and `getOrSet` into `Hyperf\Collection\Collection`.

## Optimized

- [#6777](https://github.com/hyperf/hyperf/pull/6777) Optimized StdoutLogger to improve log message handling.
- [#6778](https://github.com/hyperf/hyperf/pull/6778) Optimized Collection using EnumeratesValues.

# v3.1.22 - 2024-05-16

## Fixed

- [#6755](https://github.com/hyperf/hyperf/pull/6755) Fixed bug that exception normalizer cannot support symfony 7.

## Added

- [#6734](https://github.com/hyperf/hyperf/pull/6734) Auto complete options for as command and closure command.
- [#6746](https://github.com/hyperf/hyperf/pull/6746) Added `explain()` for `Hyperf\Database\Query\Builder`.
- [#6749](https://github.com/hyperf/hyperf/pull/6749) Added some rules for `hyperf/validation`.
- [#6752](https://github.com/hyperf/hyperf/pull/6752) Added `path` and `paths` methods to `Hyperf\Database\Seeders\Seed`.

# v3.1.21 - 2024-05-09

## Added

- [#6738](https://github.com/hyperf/hyperf/pull/6738) Added `unshift` method to `Hyperf\Collection\Collection`.
- [#6740](https://github.com/hyperf/hyperf/pull/6740) Support `useIndex` `forceIndex` and `ignoreIndex` for `Hyperf\Database\Query\Builder`.

## Optimized

- [#6716](https://github.com/hyperf/hyperf/pull/6716) [#6717](https://github.com/hyperf/hyperf/pull/6717) Optimized exchange declaration for amqp consumer messages.
- [#6721](https://github.com/hyperf/hyperf/pull/6721) Optimized the implementation of `When` Method.
- [#6731](https://github.com/hyperf/hyperf/pull/6731) Updated InteractsWithModelFactory to handle missing dependencies.

## Fixed

- [#6728](https://github.com/hyperf/hyperf/pull/6728) Fixed bug that `hyperf/watch` cannot work when using `hyperf/constants` enum mode.

# v3.1.20 - 2024-04-26

## Added

- [#6709](https://github.com/hyperf/hyperf/pull/6709) Added default `onClose` method for rpc Server.
- [#6712](https://github.com/hyperf/hyperf/pull/6712) Add new methods in `Hyperf\Collection\Collection`.

## Optimized

- [#6700](https://github.com/hyperf/hyperf/pull/6700) Optimized the implementation of `Pluralizer`.

# v3.1.19 - 2024-04-18

## Fixed

- [#6689](https://github.com/hyperf/hyperf/pull/6689) Fixed bug that socket-io cannot parse data with `?` but without `query`.
- [#6697](https://github.com/hyperf/hyperf/pull/6697) Fixed bug that `withoutBody` cannot not work when using `Swow`.

## Added

- [#6680](https://github.com/hyperf/hyperf/pull/6680) Added `Hyperf\Coordinator` helper functions.
- [#6681](https://github.com/hyperf/hyperf/pull/6681) Added option `type` for `gen:constant` which you can be used to generate files with `const` or `enum`.

## Optimized

- [#6686](https://github.com/hyperf/hyperf/pull/6686) Optimized `FswatchDriver` which don't restart server by empty reading.
- [#6698](https://github.com/hyperf/hyperf/pull/6698) Upgrade `hyperf/engine` to `v2.11`.
- [#6696](https://github.com/hyperf/hyperf/pull/6696) Automatic declare exchange when produce message.

# v3.1.18 - 2024-04-12

## Added

- [#6674](https://github.com/hyperf/hyperf/pull/6674) Added getConfig for redisPool.

## Fixed

- [#6664](https://github.com/hyperf/hyperf/pull/6664) Fixed bug that `isset` cannot check `null` in `Hyperf\Collection\Collection`.

## Optimized

- [#6668](https://github.com/hyperf/hyperf/pull/6668) Added error handling when using `callback` in multiplexed RPC.

# v3.1.17 - 2024-04-10

## Added

- [#6652](https://github.com/hyperf/hyperf/pull/6652) Added Str trim methods.
- [#6658](https://github.com/hyperf/hyperf/pull/6658) HEAD requests, attempt fallback to GET in `MiddlewareManager`.
- [#6665](https://github.com/hyperf/hyperf/pull/6665) Added logger for `Websocket`.

# Changed

- [#6661](https://github.com/hyperf/hyperf/pull/6661) Use `PHP_BINARY` instead of `php` as default php binary path for `hyperf/watcher`.

# v3.1.16 - 2024-04-02

## Added

- [#6632](https://github.com/hyperf/hyperf/pull/6632) Support to set headers for `websocket-client`.
- [#6648](https://github.com/hyperf/hyperf/pull/6648) Return result about websocket sender`push``disconnect`.

## Fixed

- [#6633](https://github.com/hyperf/hyperf/pull/6633) Fixed bug that crontab will be skipped sometimes.
- [#6635](https://github.com/hyperf/hyperf/pull/6635) Fixed AMQP `ConsumerMessage::getQueue` return type.

## Optimized

- [#6640](https://github.com/hyperf/hyperf/pull/6640) Support PHP8 Attribute for `hyperf/constants`.

# v3.1.15 - 2024-03-28

## Added

- [#6613](https://github.com/hyperf/hyperf/pull/6613) Added event of release connection for `hyperf/pool`.

## Optimized

- [#6616](https://github.com/hyperf/hyperf/pull/6616) [#6617](https://github.com/hyperf/hyperf/pull/6617) Format code by the latest `cs-fixer`.

## Deprecated

- [#6621](https://github.com/hyperf/hyperf/pull/6621) `WebSocketHandeShakeException` is deprecated, please use `WebSocketHandShakeException` instead.

# v3.1.14 - 2024-03-21

## Fixed

- [#6609](https://github.com/hyperf/hyperf/pull/6609) Fixed bug that the configurations will be cleared when the `scan` configuration does not exist.

## Added

- [#6594](https://github.com/hyperf/hyperf/pull/6594) Added `hyperf/carbon` component.

## Optimized

- [#6600](https://github.com/hyperf/hyperf/pull/6600) Optimized the worker process to no longer output warn information after exiting.
- [#6608](https://github.com/hyperf/hyperf/pull/6608) Optimized `CacheAheadAspect` which store cache in another coroutine instead of blocking current coroutine.

# v3.1.13 - 2024-03-14

## Added

- [#6576](https://github.com/hyperf/hyperf/pull/6576) Added `Hyperf\Stringable\Str::apa()` method.
- [#6577](https://github.com/hyperf/hyperf/pull/6577) Support setup command traits before running.
- [#6579](https://github.com/hyperf/hyperf/pull/6579) Added `now()` and `today()` helper functions.
- [#6590](https://github.com/hyperf/hyperf/pull/6590) Added `--graceful` to  migrateCommand.

## Fixed

- [#6586](https://github.com/hyperf/hyperf/pull/6586) Fixed bug that the command description will cause parse error when contains `--`.
- [#6593](https://github.com/hyperf/hyperf/pull/6593) Fixed the error when register multi `AsCommand`.

# v3.1.12 - 2024-03-07

## Fixed

- [#6569](https://github.com/hyperf/hyperf/pull/6569) Fixed bug that reading messages failed when the channel have been removed from channel group.
- [#6561](https://github.com/hyperf/hyperf/pull/6561) Fixed bug that the relation comments cannot be created by `gen:model`.
- [#6566](https://github.com/hyperf/hyperf/pull/6566) Fixed bug that the numeric keys will be reset when using `$request->all()`.
- [#6567](https://github.com/hyperf/hyperf/pull/6567) Fixed bug that the `CrontabRegisterListener` don't check configuration `crontab.enable`.

# v3.1.11 - 2024-03-01

## Fixed

- [#6555](https://github.com/hyperf/hyperf/pull/6555) Fixed bug that `invalidOperator` cannot work well when using non-string operators.
- [#6563](https://github.com/hyperf/hyperf/pull/6563/files) Fixed cron dispatcher sleep accurate.

## Added

- [#6550](https://github.com/hyperf/hyperf/pull/6550) Added default config of noop driver for `hyperf/opentracing`.
- [#6562](https://github.com/hyperf/hyperf/pull/6562) Added `SqliteDriver` for `hyperf/cache`.

## Optimized

- [#6556](https://github.com/hyperf/hyperf/pull/6556) You can set an expression to model parameters, but it is not standardized.

# v3.1.10 - 2024-02-23

## Added

- [#6542](https://github.com/hyperf/hyperf/pull/6542) Added `MemoryDriver` for `hyperf/cache`.
- [#6533](https://github.com/hyperf/hyperf/pull/6533) Added `database-sqlite` component.

## Optimized

- [#6539](https://github.com/hyperf/hyperf/pull/6539) Optimized `retry` helper function can accept array as first parameter.

# v3.1.9 - 2024-02-18

## Fixed

- [#6482](https://github.com/hyperf/hyperf/pull/6482) Fixed bug that the rule `decimal` cannot work well with `size` for `validation`.

## Added

- [#6518](https://github.com/hyperf/hyperf/pull/6518) Added amqpMessage property to events for amqp.
- [#6526](https://github.com/hyperf/hyperf/pull/6526) Added `Conditionable` trait to Crontab.

## Optimized

- [#6517](https://github.com/hyperf/hyperf/pull/6517) Fixed bug that the older versions of parsers cannot parse new messages for `async-queue`.
- [#6520](https://github.com/hyperf/hyperf/pull/6520) Refactor UdpSocketAspect to improve coroutine handling.

# v3.1.8 - 2024-02-01

## Fixed

- [#6509](https://github.com/hyperf/hyperf/pull/6509) Fixed bug that `Schedule::call()` cannot support `array` when using `crontab`.

## Optimized

- [#6511](https://github.com/hyperf/hyperf/pull/6511) Optimized the serialization of `Hyperf\AsyncQueue\JobMessage`.
- [#6516](https://github.com/hyperf/hyperf/pull/6516) Fixed bug that message will be lost when unpack failed.

## Added

- [#6504](https://github.com/hyperf/hyperf/pull/6504) Added `HostReaderInterface` for `rpc-multiplex`.

# v3.1.7 - 2024-01-26

## Fixed

- [#6491](https://github.com/hyperf/hyperf/pull/6491) Fixed bug that swagger validation collector cannot collect query parameters.
- [#6500](https://github.com/hyperf/hyperf/pull/6500) Fixed bug that inconsistent parsing response in Rpc-Multiplex client.

## Added

- [#6483](https://github.com/hyperf/hyperf/pull/6483) [#6487] (https://github.com/hyperf/hyperf/pull/6487) Added new ways to register crontab.
- [#6488](https://github.com/hyperf/hyperf/pull/6488) Added the default implement for `Psr\Log\LoggerInterface`.
- [#6495](https://github.com/hyperf/hyperf/pull/6495) Added cron support for closure-command.
- [#6501](https://github.com/hyperf/hyperf/pull/6501) Added `Collection::replace()` and `Collection::replaceRecursive()`.

## Optimized

- [#6480](https://github.com/hyperf/hyperf/pull/6480) Optimize log for crontab task skipped.
- [#6489](https://github.com/hyperf/hyperf/pull/6489) Removed the low version conditions of `php` and `swoole`.

# v3.1.6 - 2024-01-18

## Added

- [#6449](https://github.com/hyperf/hyperf/pull/6449) Added method `ReflectionManager::getAllClassesByFinder`.
- [#6468](https://github.com/hyperf/hyperf/pull/6468) Added support for Crontab specified operating environments.
- [#6471](https://github.com/hyperf/hyperf/pull/6471) Added method `Arr::remove`.
- [#6474](https://github.com/hyperf/hyperf/pull/6474) Added `Crontab::setOptions()` and `Crontab::getOptions()`.

## Optimized

- [#6440](https://github.com/hyperf/hyperf/pull/6440) Optimized code of `Hyperf\SocketIOServer\Parser\Decoder::decode()`.
- [#6472](https://github.com/hyperf/hyperf/pull/6472) Optimized code of `DispatcherFactory` which use `require` instead of `require_once` for loading `routes`.
- [#6473](https://github.com/hyperf/hyperf/pull/6473) Auto mkdir folder when using command `gen:swagger-schema`.
- [#6477](https://github.com/hyperf/hyperf/pull/6477) Optimized code about binding `serverMutex` and `taskMutex` for `Crontab`.
- [#6479](https://github.com/hyperf/hyperf/pull/6479) Updated the default mutex expiration time to 60 seconds.

# v3.1.5 - 2024-01-04

## Fixed

- [#6423](https://github.com/hyperf/hyperf/pull/6423) Fixed bug that the timezone of crontab task cannot work.
- [#6436](https://github.com/hyperf/hyperf/pull/6436) Fixed bug that the generator which be used to generate amqp consumer cannot work.

## Added

- [#6431](https://github.com/hyperf/hyperf/pull/6431) Added `UnsetContextInTaskWorkerListener` which can be used to unset connection context when using non-coroutine task worker.

## Optimized

- [#6435](https://github.com/hyperf/hyperf/pull/6435) [#6437](https://github.com/hyperf/hyperf/pull/6437) Optimized model generator which can generate property comments with `use`.

# v3.1.4 - 2023-12-29

## Fixed

- [#6419](https://github.com/hyperf/hyperf/pull/6419) Fixed bug that `prepareHandler` cannot work sometimes for `circuit-breaker`.

## Added

- [#6426](https://github.com/hyperf/hyperf/pull/6426) Added Annotation `RewriteReturnType` which used to rewrite the return type when generating models.

## Optimized

- [#6415](https://github.com/hyperf/hyperf/pull/6415) Throw `InvalidArgumentException` instead of `TypeError` for decoding an empty string when using `Base62::decode`.

# v3.1.3 - 2023-12-21

## Fixed

- [#6389](https://github.com/hyperf/hyperf/pull/6389) Fixed bug that es version cannot be found when the index is null.
- [#6406](https://github.com/hyperf/hyperf/pull/6406) Fixed bug that `Hyperf\Scout\Searchable` don't import namespace of function `config`.

## Added

- [#6398](https://github.com/hyperf/hyperf/pull/6398) Added `timezone` parameter to `hyperf/crontab` component.
- [#6402](https://github.com/hyperf/hyperf/pull/6402) Added `template_suffix` configuration to `twig` engine.

# v3.1.2 - 2023-12-15

## Fixed

- [#6372](https://github.com/hyperf/hyperf/pull/6372) Fixed bug that AOP not working when using variadic parameters.
- [#6374](https://github.com/hyperf/hyperf/pull/6374) Fixed bug that `RateLimitAnnotationAspect::getWeightingAnnotation()` cannot work when using config `rate_limit.storage`.
- [#6384](https://github.com/hyperf/hyperf/pull/6384) Fixed bug that `scout` cannot work when using elasticsearch(which version is less than 7) without index.

## Added

- [#6357](https://github.com/hyperf/hyperf/pull/6357) Support symfony 7.x for some components such as `command` `config` `devtool` `di` and `server`.
- [#6373](https://github.com/hyperf/hyperf/pull/6373) Support `ping` method for `grpc client`.
- [#6379](https://github.com/hyperf/hyperf/pull/6379) Support to read custom attribute for validation when using swagger.
- [#6380](https://github.com/hyperf/hyperf/pull/6380) Support collect swagger validation rules and attribute for mediaType request body.

## Optimized

- [#6376](https://github.com/hyperf/hyperf/pull/6376) Don't need to close swoole short name when don't use swoole or don't require `hyperf/polyfill-coroutine` component.

# v3.1.1 - 2023-12-08

## Fixed

- [#6347](https://github.com/hyperf/hyperf/pull/6347) Fixed bug that the view function may add redundant content-type to header.
- [#6352](https://github.com/hyperf/hyperf/pull/6352) Fixed bug that nacos config center cannot work when using grpc protocol.
- [#6350](https://github.com/hyperf/hyperf/pull/6350) Fixed bug that the recv channel cannot be found, because `GrpcClient::runReceiveCoroutine` will unset streamId before recv method.
- [#6361](https://github.com/hyperf/hyperf/pull/6361) Fixed bug that `Hyperf\SocketIOServer\Emitter\Future` cannot be resolved.
- [#6369](https://github.com/hyperf/hyperf/pull/6369) Fixed bug that the main process did not handle the abnormal exit of the fork process.

## Added

- [#6342](https://github.com/hyperf/hyperf/pull/6342) Added `Coroutine::fork()` method and `Coroutine::pid()` method.
- [#6360](https://github.com/hyperf/hyperf/pull/6360) Added response `content-type` header for swagger server.
- [#6363](https://github.com/hyperf/hyperf/pull/6363) Added callable type support to the fallback property of CircuitBreaker Attribute.

# v3.1.0 - 2023-12-01

## Dependencies Upgrade

- Upgrade the php version to `>=8.1`
- Upgrade the swoole version to `>=5.0`
- Upgrade `hyperf/engine` to `^2.0`
- Upgrade `phpunit/phpunit` to `^10.0`

## Swow Supported

- [#5843](https://github.com/hyperf/hyperf/pull/5843) Support `Swow` for `reactive-x`.
- [#5844](https://github.com/hyperf/hyperf/pull/5844) Support `Swow` for `socketio-server`.

## Added

- [x] Support [Psr7Plus](https://github.com/swow/psr7-plus).
    - [#5828](https://github.com/hyperf/hyperf/pull/5828) Support swow psr7-plus interface for `http-message`.
    - [#5839](https://github.com/hyperf/hyperf/pull/5839) Support swow psr7-plus interface for all components.
- [x] Support [pest](https://github.com/pestphp/pest).
- [x] Added `hyperf/helper` component.
- [x] Added `hyperf/polyfill-coroutine` component.
- [#5815](https://github.com/hyperf/hyperf/pull/5815) Added alias as `mysql` for `pdo` in `hyperf/db`.
- [#5849](https://github.com/hyperf/hyperf/pull/5849) Support for insert update and select using enums.
- [#5894](https://github.com/hyperf/hyperf/pull/5894) [#5897](https://github.com/hyperf/hyperf/pull/5897) Added `model-factory` support for `hyperf/testing`.
- [#5898](https://github.com/hyperf/hyperf/pull/5898) Added `toRawSql()` to Query Builders.
- [#5906](https://github.com/hyperf/hyperf/pull/5906) Added `getRawQueryLog()` to Database Connection.
- [#5915](https://github.com/hyperf/hyperf/pull/5915) Added `data_forget` helper.
- [#5914](https://github.com/hyperf/hyperf/pull/5914) Added `Str::isUrl()` and use it from the validator.
- [#5918](https://github.com/hyperf/hyperf/pull/5918) Added `Arr::isList()` method.
- [#5925](https://github.com/hyperf/hyperf/pull/5925) [#5926](https://github.com/hyperf/hyperf/pull/5926) Allow model attributes to be casted to/from an Enum.
- [#5930](https://github.com/hyperf/hyperf/pull/5930) [#5934](https://github.com/hyperf/hyperf/pull/5934) Added `AsCommand` annotation and `ClosureCommand` support.
- [#5950](https://github.com/hyperf/hyperf/pull/5950) Added `Job::setMaxAttempts` method and `dispatch` helper function for `hyperf/async-queue`.
- [#5967](https://github.com/hyperf/hyperf/pull/5967) Added component `hyperf/migration-generator` which used to generate migrations from databases.
- [#5983](https://github.com/hyperf/hyperf/pull/5983) [#5985](https://github.com/hyperf/hyperf/pull/5985) Added `skipCacheResults` to annotations of `hyperf/cache`.
- [#5994](https://github.com/hyperf/hyperf/pull/5994) Added `events` of `crontab` lifecycle.
- [#6039](https://github.com/hyperf/hyperf/pull/6039) Support semantic crontab rules.
- [#6082](https://github.com/hyperf/hyperf/pull/6082) Added `hyperf/stdlib` component.
- [#6085](https://github.com/hyperf/hyperf/pull/6085) Added an error count to the database connection to ensure that the connection can be reset when occur too many exceptions.
- [#6106](https://github.com/hyperf/hyperf/pull/6106) Support some validation rules.
- [#6124](https://github.com/hyperf/hyperf/pull/6124) Added `Hyperf\AsyncQueue\Job::fail()`.
- [#6259](https://github.com/hyperf/hyperf/pull/6259) Support to use model builder as the column in `Hyperf\Database\Query\Builder\addSelect`.
- [#6301](https://github.com/hyperf/hyperf/pull/6301) Improve storage switcher for rate-limit.
- [#6338](https://github.com/hyperf/hyperf/pull/6338) Added config `processors` for swagger.

## Optimized

- Move Prometheus driver dependency to suggest.
- [#5586](https://github.com/hyperf/hyperf/pull/5586) Support grpc streaming for nacos naming service.
- [#5866](https://github.com/hyperf/hyperf/pull/5866) Use `StrCache` instead of `Str` in special cases.
- [#5872](https://github.com/hyperf/hyperf/pull/5872) Avoid to execute the refresh callback more than once when calling `refresh()` multi times.
- [#5879](https://github.com/hyperf/hyperf/pull/5879) [#5878](https://github.com/hyperf/hyperf/pull/5878) Improve `Command`.
- [#5901](https://github.com/hyperf/hyperf/pull/5901) Optimized code for identifer established by the rpc client that must contain a string,number or null if included.
- [#5905](https://github.com/hyperf/hyperf/pull/5905) Forget with collections.
- [#5917](https://github.com/hyperf/hyperf/pull/5917) Upgrade URL pattern for `Str::isUrl()`.
- [#5920](https://github.com/hyperf/hyperf/pull/5920) add the `\Stringable` interface to classes that have `__toString()` method.
- [#5945](https://github.com/hyperf/hyperf/pull/5945) Don't sync config frequently when listen more than one namespace for apollo config center.
- [#5948](https://github.com/hyperf/hyperf/pull/5948) Optimized `Hyperf\Coroutine\Locker`.
- [#5960](https://github.com/hyperf/hyperf/pull/5960) Allowed set poolName in Annotation.
- [#5972](https://github.com/hyperf/hyperf/pull/5972) `Collection::except()` with null returns all.
- [#5973](https://github.com/hyperf/hyperf/pull/5973) Simplified the handlers definition of logger.
- [#6010](https://github.com/hyperf/hyperf/pull/6010) Throw exception when cast class is not existed.
- [#6030](https://github.com/hyperf/hyperf/pull/6030) Support buffer mechanism in standalone process of metric.
- [#6131](https://github.com/hyperf/hyperf/pull/6131) Throw invalid argument exception when the crontab task is `null`.
- [#6172](https://github.com/hyperf/hyperf/pull/6172) Optimized `ProcessManager` to make the `running` status more clear.
- [#6184](https://github.com/hyperf/hyperf/pull/6184) Set logger when using safe socket in coroutine style tcp server.
- [#6247](https://github.com/hyperf/hyperf/pull/6247) Optimized code that you can get request from `BadRequestHttpException`.

## Removed

- [x] Remove unused codes in `hyperf/utils`.
- [x] Remove redundant `setAccessible` methods.
- [x] Remove deprecated codes.
- [#5813](https://github.com/hyperf/hyperf/pull/5813) Removed support for swoole 4.x
- [#5859](https://github.com/hyperf/hyperf/pull/5859) Removed string cache from `Hyperf\Stringable\Str`
- [#6040](https://github.com/hyperf/hyperf/pull/6040) Removed some deprecated methods from `Hyperf\Di\Annotation\AbstractAnnotation`.
- [#6043](https://github.com/hyperf/hyperf/pull/6043) Removed deprecated `Hyperf\Coroutine\Traits\Container`.
- [#6244](https://github.com/hyperf/hyperf/pull/6244) Removed deprecated component `swoole-tracker`.

## Changed

- [x] Throw exceptions when the redis option key is invalid.
- [#5847](https://github.com/hyperf/hyperf/pull/5847) Changed the default redis key for metric.
- [#5943](https://github.com/hyperf/hyperf/pull/5943) Don't remove the node from load balancer of `json rpc http transporter` when the status code isn't 200.
- [#5961](https://github.com/hyperf/hyperf/pull/5961) Using `enum` instead of `class` for `Hyperf\Amqp\Result` and `Hyperf\Amqp\Message\Type`.
- [#6022](https://github.com/hyperf/hyperf/pull/6022) When using `Base62::decode` to decode the incorrect data, it should be thrown `InvalidArgumentException` instead of `TypeError`.
- [#6128](https://github.com/hyperf/hyperf/pull/6128) When using multi-level directories for `hyperf/config`, you can use `config('a.c')` to get the configurations from `autoload/a/c.php`.

## Fixed

- [#5771](https://github.com/hyperf/hyperf/pull/5771) Fixed bug that the return type of `Model::updateOrInsert` isn't boolean.
- [#6033](https://github.com/hyperf/hyperf/pull/6033) Fixed bug that `RequestContext` and `ResponseContext` cannot get instance from another coroutines.
- [#6056](https://github.com/hyperf/hyperf/pull/6056) Fixed bug that `Hyperf\HttpServer\Request::hasFile()` don't support `Swow`.
- [#6260](https://github.com/hyperf/hyperf/pull/6260) Fixed bug that logger cannot work in `LoadBalancerInterface::refresh()`.

## Deprecated

- `Hyperf\DB\PgSQL\PgSQLConnection::str_replace_once` will be deprecated, please use `Hyperf\DB\PgSQL\PgSQLConnection::strReplaceOnce` instead.
- `Hyperf\Database\PgSQL\PostgreSqlSwooleExtConnection::str_replace_once` will be deprecated, please use `Hyperf\Database\PgSQL\PostgreSqlSwooleExtConnection::strReplaceOnce` instead.
