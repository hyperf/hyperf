# master

## Added

- Nothing.

## Changed

- Removed framework dependency of `hyperf/di`, `hyperf/command` and `hyperf/dispatcher`.

## Removed

- Nothing.

## Fixed

- Fixed http server start failed, when include `hyperf/websocket-server`.

# v1.0.2 - 2019-06-25

## Added

- Added Travis Ci. [#25](https://github.com/hyperf-cloud/hyperf/pull/25)
- Added some paramater of `Redis::connect`. [#29](https://github.com/hyperf-cloud/hyperf/pull/29)

## Fixed

- Fixed http server will be affected of websocket server.
- Fixed proxy class 
- Fixed database pool will be fulled in testing.
- Fixed co-phpunit work not expected.
- Fixed model event `creating`, `updating` ... not work expected.
- Fixed `flushContext` not work expected for testing.
