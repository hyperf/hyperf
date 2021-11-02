# v3.0.0 - TBD

- Upgraded the minimum php version to `^8.0`;

## BC breaks

- 框架移除了 `@Annotation` 的支持，全部使用 `PHP8` 原生注解 `Attribute`，更新前务必检查项目中，是否已经全部替换为 `Attribute`。

> TODO: 提供检测注解的脚本

- 框架为类库增加了更多的类型限制，所以从 `2.2` 更新到 `3.0` 版本时，需要跑一遍静态检测。

```shell
composer analyse
```

## Dependencies Upgrade

- Upgraded `php-amqplib/php-amqplib` to `^3.1`;
- Upgraded `phpstan/phpstan` to `^1.0`;

## Added

- [#4196](https://github.com/hyperf/hyperf/pull/4196) Added `SwooleIOFactory` which used to create amqp io by yourself.
