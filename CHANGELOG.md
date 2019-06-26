# v1.0.3 TBD

## Added

- [#48](https://github.com/hyperf-cloud/hyperf/pull/48) Added WebSocket Client.
- [#51](https://github.com/hyperf-cloud/hyperf/pull/51) Added property `enableCache` to `DefinitionSource` to enable annotation cache. 

## Changed

- [#46](https://github.com/hyperf-cloud/hyperf/pull/46) Removed hyperf/framework requirement of `hyperf/di`, `hyperf/command` and `hyperf/dispatcher`. 

## Removed

- Nothing.

## Fixed

- [#45](https://github.com/hyperf-cloud/hyperf/pull/55) Fixed http server start failed, when the skeleton included `hyperf/websocket-server`. 
- [#55](https://github.com/hyperf-cloud/hyperf/pull/55) Fixed the method level middleware annotation. 

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
