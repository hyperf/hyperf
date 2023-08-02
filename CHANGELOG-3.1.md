# v3.1.0 - TBD

## Dependencies Upgrade

- Upgrade the php version to `>=8.1`
- Upgrade the swoole version to `>=5.0`
- Upgrade `hyperf/engine` to `^2.0`
- Upgrade `phpunit/phpunit` to `^10.0`

## Swow Supported

- [#5843](https://github.com/hyperf/hyperf/pull/5843) Support `Swow` for `reactive-x`.
- [#5844](https://github.com/hyperf/hyperf/pull/5844) Support `Swow` for `socketio-server`.

## Added

- [ ] Support v2 and v3 for socketio-server.
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
- [#5994](https://github.com/hyperf/hyperf/pull/5994) Adds `events` of `crontab` lifecycle.

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

## Removed

- [x] Remove unused codes in `hyperf/utils`.
- [x] Remove redundant `setAccessible` methods.
- [x] Remove deprecated codes.
- [#5813](https://github.com/hyperf/hyperf/pull/5813) Removed support for swoole 4.x
- [#5859](https://github.com/hyperf/hyperf/pull/5859) Removed string cache from `Hyperf\Stringable\Str`

## Changed

- [#5847](https://github.com/hyperf/hyperf/pull/5847) Changed the default redis key for metric.
- [#5943](https://github.com/hyperf/hyperf/pull/5943) Don't remove the node from load balancer of `json rpc http transporter` when the status code isn't 200.
- [#5961](https://github.com/hyperf/hyperf/pull/5961) Using `enum` instead of `class` for `Hyperf\Amqp\Result` and `Hyperf\Amqp\Message\Type`.

## Fixed

- [#5771](https://github.com/hyperf/hyperf/pull/5771) Fixed bug that the return type of `Model::updateOrInsert` isn't boolean.
