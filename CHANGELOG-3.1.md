# v3.1.0 - TBD

## Dependencies Upgrade

- Upgrade the php version to `>=8.1`
- Upgrade the swoole version to `>=5.0`
- Upgrade `hyperf/engine` to `^2.0`
- Upgrade `phpunit/phpunit` to `^10.0`

## Swow Supported

- [ ] reactive-x
- [ ] socketio-server

## Added

- [ ] Support v2 and v3 for socketio-server.
- [ ] Support [Psr7Plus](https://github.com/swow/psr7-plus).
- [x] Support [pest](https://github.com/pestphp/pest).
- [x] Added `hyperf/helper` component.

## Optimized

- Move Prometheus driver dependency to suggest.

## Removed

- [x] Remove unused codes in `hyperf/utils`.
- [x] Remove redundant `setAccessible` methods.
- [x] Remove deprecated codes.

## Deprecated

- [x] Drop support swoole for 4.x.
