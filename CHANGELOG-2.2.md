# v2.2.1 - TBD

## Fixed

- [#3828](https://github.com/hyperf/hyperf/pull/3828) Fixed bug that lazy inject does not work for `Hyperf\Redis\Redis` in `PHP8.0`.
- [#3845](https://github.com/hyperf/hyperf/pull/3845) Fixed bug that `watcher` does not work for `v2.2`.

# v2.2.0 - 2021-07-19

## Dependencies Upgrade

- Upgraded `friendsofphp/php-cs-fixer` to `^3.0`;
- Upgraded `psr/container` to `^1.0|^2.0`;
- Upgraded `egulias/email-validator` to `^3.0`;
- Upgraded `markrogoyski/math-php` to `^2.0`;
- [3783](https://github.com/hyperf/hyperf/pull/3783) Upgraded `league/flysystem` to `^1.0|^2.0`;

## Dependencies Changed

- [#3577](https://github.com/hyperf/hyperf/pull/3577) `domnikl/statsd` is abandoned and no longer maintained. The author suggests using the `slickdeals/statsd` package instead.

## Changed

- [#3334](https://github.com/hyperf/hyperf/pull/3334) Changed the return value of `LengthAwarePaginator::toArray()` to be consistent with that of `Paginator::toArray()`.
- [#3550](https://github.com/hyperf/hyperf/pull/3550) Removed `broker` and `bootstrap_server` from `kafka`, please use `brokers` and `bootstrap_servers` instead.
- [#3580](https://github.com/hyperf/hyperf/pull/3580) Changed the default priority of aspect to 0.
- [#3582](https://github.com/hyperf/hyperf/pull/3582) Changed the consumer tag of amqp to empty string.
- [#3634](https://github.com/hyperf/hyperf/pull/3634) Use Fork Process strategy to replace BetterReflection strategy.
  - [#3649](https://github.com/hyperf/hyperf/pull/3649) Removed `roave/better-reflection` from `hyperf/database` when using `gen:model`.
  - [#3651](https://github.com/hyperf/hyperf/pull/3651) Removed `roave/better-reflection` from LazyLoader.
  - [#3654](https://github.com/hyperf/hyperf/pull/3654) Removed `roave/better-reflection` from other components.
- [#3676](https://github.com/hyperf/hyperf/pull/3676) Use `promphp/prometheus_client_php` instead of `endclothing/prometheus_client_php`.
- [#3694](https://github.com/hyperf/hyperf/pull/3694) Changed `Hyperf\CircuitBreaker\CircuitBreakerInterface` to support php8.
  - Changed `CircuitBreaker::inc*Counter()` to `CircuitBreaker::incr*Counter()`.
  - Changed type hint for method `AbstractHandler::switch()`.
- [#3706](https://github.com/hyperf/hyperf/pull/3706) Changed the style of writing to `#[Middlewares(FooMiddleware::class)]` from `@Middlewares({@Middleware(FooMiddleware::class)})` in PHP8.
- [#3715](https://github.com/hyperf/hyperf/pull/3715) Restructure nacos component, be sure to reread the documents.
- [#3722](https://github.com/hyperf/hyperf/pull/3722) Removed config `config_apollo.php`, please use `config_center.php` instead.
- [#3725](https://github.com/hyperf/hyperf/pull/3725) Removed config `config_etcd.php`, please use `config_center.php` instead.
- [#3730](https://github.com/hyperf/hyperf/pull/3730) Removed config `brokers` and `update_brokers` from kafka.
- [#3733](https://github.com/hyperf/hyperf/pull/3733) Removed config `zookeeper.php`, please use `config_center.php` instead.
- [#3734](https://github.com/hyperf/hyperf/pull/3734) Split `nacos` into `config-nacos` and `service-governance-nacos`.
  - [#3772](https://github.com/hyperf/hyperf/pull/3772) Fixed bug that nacos driver do not work.
- [#3734](https://github.com/hyperf/hyperf/pull/3734) Renamed `nacos-sdk` as `nacos`.
- [#3737](https://github.com/hyperf/hyperf/pull/3737) Refactor config-center and config driver
  - Added `AbstractDriver` and merge the duplicate code into the abstraction class
  - Added `PipeMessageInterface` to uniform the message struct of config fetcher process
- [#3817](https://github.com/hyperf/hyperf/pull/3817) [#3818](https://github.com/hyperf/hyperf/pull/3818) Split `service-governance-consul` from `service-governance`.
- [#3819](https://github.com/hyperf/hyperf/pull/3819) Use their own configuration below `config_center.php` for config center component which using ETCD and Nacos.

## Deprecated

- [#3636](https://github.com/hyperf/hyperf/pull/3636) `Hyperf\Utils\Resource` will be deprecated in v2.3, please use `Hyperf\Utils\ResourceGenerator` instead.

## Added

- [#3589](https://github.com/hyperf/hyperf/pull/3589) Added DAG component.
- [#3606](https://github.com/hyperf/hyperf/pull/3606) Added RPN component.
- [#3629](https://github.com/hyperf/hyperf/pull/3629) Added `Hyperf\Utils\Channel\ChannelManager` which used to manage channels.
- [#3631](https://github.com/hyperf/hyperf/pull/3631) Support multiplexing for AMQP component.
  - [#3639](https://github.com/hyperf/hyperf/pull/3639) Close push channel and socket when worker exited.
  - [#3640](https://github.com/hyperf/hyperf/pull/3640) Optimized log level for SwooleIO.
  - [#3657](https://github.com/hyperf/hyperf/pull/3657) Fixed memory exhausted for rabbitmq caused by confirm channel.
  - [#3659](https://github.com/hyperf/hyperf/pull/3659) Optimized code which be used to close connection friendly.
  - [#3681](https://github.com/hyperf/hyperf/pull/3681) Fixed bug that rpc client does not work for amqp.
- [#3635](https://github.com/hyperf/hyperf/pull/3635) Added `Hyperf\Utils\CodeGen\PhpParser` which used to generate AST for reflection. 
- [#3648](https://github.com/hyperf/hyperf/pull/3648) Added `Hyperf\Utils\CodeGen\PhpDocReaderManager` to manage `PhpDocReader`.
- [#3679](https://github.com/hyperf/hyperf/pull/3679) Added Nacos SDK component.
  - [#3712](https://github.com/hyperf/hyperf/pull/3712) The input parameters of `InstanceProvider::update()` are modified to make it more friendly.
- [#3698](https://github.com/hyperf/hyperf/pull/3698) Support PHP8 Attribute which can replace doctrine annotations.
- [#3714](https://github.com/hyperf/hyperf/pull/3714) Added ide-helper component.
- [#3722](https://github.com/hyperf/hyperf/pull/3722) Added config-center component.
- [#3728](https://github.com/hyperf/hyperf/pull/3728) Added support for `secret` of Apollo.
- [#3743](https://github.com/hyperf/hyperf/pull/3743) Support custom register for service governance.
- [#3753](https://github.com/hyperf/hyperf/pull/3753) Support long pulling mode for Apollo Client.
- [#3759](https://github.com/hyperf/hyperf/pull/3759) Added `rpc-multiplex` component.
- [#3791](https://github.com/hyperf/hyperf/pull/3791) Support setting multiple annotations by inheriting `AbstractMultipleAnnotation`, such as `@Middleware`.
- [#3806](https://github.com/hyperf/hyperf/pull/3806) Added heartbeat for nacos service governance.

## Optimized

- [#3670](https://github.com/hyperf/hyperf/pull/3670) Adapt database component to support php8.
- [#3673](https://github.com/hyperf/hyperf/pull/3673) Adapt all components to support php8.
- [#3730](https://github.com/hyperf/hyperf/pull/3730) Optimized code for kafka component.
  - Support `timeout` for `Producer` to avoid requests not responding.
  - Removed useless code with pool.
  - Throw exceptions when connect kafka failed.
- [#3758](https://github.com/hyperf/hyperf/pull/3758) Optimized code for pool which get connection again when first failed.

## Fixed

- [#3650](https://github.com/hyperf/hyperf/pull/3650) Fixed bug that `ReflectionParameter::getClass()` will be deprecated in php8.
- [#3692](https://github.com/hyperf/hyperf/pull/3692) Fixed bug that class proxies couldn't be included when building phar.
- [#3769](https://github.com/hyperf/hyperf/pull/3769) Fixed bug that `config-center` conflicts with `metrics`.
- [#3770](https://github.com/hyperf/hyperf/pull/3770) Fixed type error when using `Str::slug()`.
- [#3788](https://github.com/hyperf/hyperf/pull/3788) Fixed type error when using `BladeCompiler::getRawPlaceholder()`.
- [#3794](https://github.com/hyperf/hyperf/pull/3794) Fixed bug that `retry_interval` does not work for `rpc-multiplex`.
- [#3798](https://github.com/hyperf/hyperf/pull/3798) Fixed bug that amqp consumer couldn't restart when rabbitmq server stopped.
- [#3814](https://github.com/hyperf/hyperf/pull/3814) Fixed bug that `libxml_disable_entity_loader()` has been deprecated as of PHP 8.0.0.
