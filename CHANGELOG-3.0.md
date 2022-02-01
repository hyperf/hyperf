# v3.0.0 - TBD

- [#4238](https://github.com/hyperf/hyperf/issues/4238) Upgraded the minimum php version to `^8.0` for all components (43/89);

## BC breaks

- 框架移除了 `@Annotation` 的支持，全部使用 `PHP8` 原生注解 `Attribute`，更新前务必检查项目中，是否已经全部替换为 `Attribute`。

> TODO: 提供检测注解的脚本

- 框架为类库增加了更多的类型限制，所以从 `2.2` 更新到 `3.0` 版本时，需要跑一遍静态检测。

```shell
composer analyse
```

升级模型脚本

> 因为模型基类增加了成员变量的类型支持，所以需要使用以下脚本，将其升级为新版本。

```shell
composer require hyperf/code-generator
php vendor/bin/regenerate-models.php $PWD/app/Model
```

## Dependencies Upgrade

- Upgraded `php-amqplib/php-amqplib` to `^3.1`;
- Upgraded `phpstan/phpstan` to `^1.0`;
- Upgraded `mix/redis-subscribe` to `mix/redis-subscriber:^3.0`
- Upgraded `psr/simple-cache` to `^1.0|^2.0|^3.0`

## Added

- [#4196](https://github.com/hyperf/hyperf/pull/4196) Added `Hyperf\Amqp\IO\IOFactory` which used to create amqp io by yourself.
- [#4304](https://github.com/hyperf/hyperf/pull/4304) Support `$suffix` for trait `Hyperf\Utils\Traits\StaticInstance`.
- [#4400](https://github.com/hyperf/hyperf/pull/4400) Added `$description` which used to set command description easily for `Hyperf\Command\Command`.
- [#4277](https://github.com/hyperf/hyperf/pull/4277) Added `Hyperf\Utils\IPReader` to get local IP.
- [#4497](https://github.com/hyperf/hyperf/pull/4497) Added `Hyperf\Coordinator\Timer` which can be stopped safely.

## Optimized

- [#4147](https://github.com/hyperf/hyperf/pull/4147) Optimized code for nacos which you can use `http://xxx.com/yyy/` instead of `http://xxx.com:8848/` to connect `nacos`.
- [#4367](https://github.com/hyperf/hyperf/pull/4367) Optimized `DataFormatterInterface` which uses object instead of array as inputs.

## Changed

- [#4199](https://github.com/hyperf/hyperf/pull/4199) Changed the `public` property `$message` to `protected` for `Hyperf\AsyncQueue\Event\Event`.
- [#4214](https://github.com/hyperf/hyperf/pull/4214) Renamed `$circularDependences` to `$checkCircularDependencies` for `Dag`.
- [#4225](https://github.com/hyperf/hyperf/pull/4225) Split `hyperf/coordinator` from `hyperf/utils`.
- [#4269](https://github.com/hyperf/hyperf/pull/4269) Changed the default priority of listener to `0` from `1`.
- [#4345](https://github.com/hyperf/hyperf/pull/4345) Renamed `Hyperf\Kafka\Exception\ConnectionCLosedException` to `Hyperf\Kafka\Exception\ConnectionClosedException`.
- [#4434](https://github.com/hyperf/hyperf/pull/4434) The method `Hyperf\Database\Model\Builder::insertOrIgnore` will be return affected count.
- [#4495](https://github.com/hyperf/hyperf/pull/4495) Changed the default value to `null` for `Hyperf\DbConnection\Db::__connection()`.

## Removed

- [#4199](https://github.com/hyperf/hyperf/pull/4199) Removed deprecated handler `Hyperf\AsyncQueue\Signal\DriverStopHandler`.
- [#4482](https://github.com/hyperf/hyperf/pull/4482) Removed deprecated `Hyperf\Utils\Resource`.
- [#4487](https://github.com/hyperf/hyperf/pull/4487) Removed log warning from cache component when the key is greater than 64 characters.

## Deprecated

- `Hyperf\Utils\Contracts\Arrayable` will be deprecated, please use `Hyperf\Contract\Arrayable` instead.
- `Hyperf\AsyncQueue\Message` will be deprecated, please use `Hyperf\AsyncQueue\JobMessage` instead.
