# v1.0.4 - TBD

## Added

- [#140](https://github.com/hyperf-cloud/hyperf/pull/140) Support Swoole v4.4.0.
- [#163](https://github.com/hyperf-cloud/hyperf/pull/163) Added custom arguments support to AbstractConstants::__callStatic in `hyperf/constants`.

# Changed

- [#124](https://github.com/hyperf-cloud/hyperf/pull/124) Added `$delay` parameter for `DriverInterface::push`, and marked `DriverInterface::delay` method to deprecated. 
- [#125](https://github.com/hyperf-cloud/hyperf/pull/125) Changed the default value of parameter $default of config() function to null.

# Fixed

- [#110](https://github.com/hyperf-cloud/hyperf/pull/110) [#111](https://github.com/hyperf-cloud/hyperf/pull/111) Fixed Redis::select is not work expected.
- [#131](https://github.com/hyperf-cloud/hyperf/pull/131) Fixed property middlewares not work in `Router::addGroup`.
- [#132](https://github.com/hyperf-cloud/hyperf/pull/132) Fixed request->hasFile does not work expected.
- [#135](https://github.com/hyperf-cloud/hyperf/pull/135) Fixed response->redirect does not work expected.
- [#139](https://github.com/hyperf-cloud/hyperf/pull/139) Fixed the BaseUri of ConsulAgent will be replaced by default BaseUri.
- [#148](https://github.com/hyperf-cloud/hyperf/pull/148) Fixed cannot generate the migration when migrates directory does not exist.
- [#152](https://github.com/hyperf-cloud/hyperf/pull/152) Fixed db connection will not be closed when a low use frequency.

# Removed

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
