# v2.2.0 - TBD

## Dependencies Upgrade

- Upgraded `friendsofphp/php-cs-fixer` to `^3.0`;
- Upgraded `psr/container` to `^1.0|^2.0`;
- Upgraded `egulias/email-validator` to `^3.0`;

## Dependencies Changed

- [#3577](https://github.com/hyperf/hyperf/pull/3577) `domnikl/statsd` is abandoned and no longer maintained. The author suggests using the `slickdeals/statsd` package instead.

## Changed

- [#3580](https://github.com/hyperf/hyperf/pull/3580) Changed the default priority of aspect to 0.
- [#3582](https://github.com/hyperf/hyperf/pull/3582) Changed the consumer tag of amqp to empty string.
- [#3634](https://github.com/hyperf/hyperf/pull/3634) Use Fork Process strategy to replace BetterReflection strategy.
  - [#3649](https://github.com/hyperf/hyperf/pull/3649) Removed `roave/better-reflection` from `hyperf/database` when using `gen:model`.
  - [#3651](https://github.com/hyperf/hyperf/pull/3651) Removed `roave/better-reflection` from LazyLoader.
  - [#3654](https://github.com/hyperf/hyperf/pull/3654) Removed `roave/better-reflection` from other components.
  
## Deprecated

- [#3636](https://github.com/hyperf/hyperf/pull/3636) `Hyperf\Utils\Resource` will be deprecated in v2.3, please use `Hyperf\Utils\ResourceGenerator` instead.

## Added

- [#3589](https://github.com/hyperf/hyperf/pull/3589) Added DAG component.
- [#3606](https://github.com/hyperf/hyperf/pull/3606) Added RPN component.
- [#3629](https://github.com/hyperf/hyperf/pull/3629) Added `Hyperf\Utils\Channel\ChannelManager` which used to manage channels.
- [#3631](https://github.com/hyperf/hyperf/pull/3631) Support multiplexing for AMQP component.
  - [#3639](https://github.com/hyperf/hyperf/pull/3639) Close push channel and socket when worker exited.
  - [#3640](https://github.com/hyperf/hyperf/pull/3640) Optimized log level for SwooleIO.
  - [#3657](https://github.com/hyperf/hyperf/pull/3657) Added `max_idle_channels` to avoid memory exhausted for rabbitmq.
- [#3635](https://github.com/hyperf/hyperf/pull/3635) Added `Hyperf\Utils\CodeGen\PhpParser` which used to generate AST for reflection. 
- [#3648](https://github.com/hyperf/hyperf/pull/3648) Added `Hyperf\Utils\CodeGen\PhpDocReaderManager` to manage `PhpDocReader`.

## Fixed

- [#3650](https://github.com/hyperf/hyperf/pull/3650) Fixed bug that `ReflectionParameter::getClass()` will be deprecated in php8.

