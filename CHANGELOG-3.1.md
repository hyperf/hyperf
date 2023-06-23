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
- [#5855](https://github.com/hyperf/hyperf/pull/5855) Added `hyperf/closure-command` component.

## Optimized

- Move Prometheus driver dependency to suggest.
- [#5866](https://github.com/hyperf/hyperf/pull/5866) Use `StrCache` instead of `Str` in special cases.
- [#5872](https://github.com/hyperf/hyperf/pull/5872) Avoid to execute the refresh callback more than once when calling `refresh()` multi times.

## Removed

- [x] Remove unused codes in `hyperf/utils`.
- [x] Remove redundant `setAccessible` methods.
- [x] Remove deprecated codes.
- [#5813](https://github.com/hyperf/hyperf/pull/5813) Removed support for swoole 4.x
- [#5859](https://github.com/hyperf/hyperf/pull/5859) Removed string cache from `Hyperf\Stringable\Str`

## Changed

- [#5847](https://github.com/hyperf/hyperf/pull/5847) Changed the default redis key for metric.

## Fixed

- [#5771](https://github.com/hyperf/hyperf/pull/5771) Fixed bug that the return type of `Model::updateOrInsert` isn't boolean.
