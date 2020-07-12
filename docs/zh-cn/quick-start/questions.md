# 常见问题

## Swoole 短名未关闭

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

您需要在您的 php.ini 配置文件增加 `swoole.use_shortname = 'Off'` 配置项

> 注意该配置必须于 php.ini 内配置，无法通过 ini_set() 函数来重写

当然，也可以通过以下的命令来启动服务，在执行 PHP 命令时关闭掉 Swoole 短名功能

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## 异步队列消息丢失

如果在使用 `async-queue` 组件时，发现 `handle` 中的方法没有执行，请先检查以下几种情况：

1. `Redis` 是否与其他人共用，消息被其他人消费走
2. 本地进程是否存在残余，被其他进程消费掉

以下提供万无一失的解决办法：

1. killall php
2. 修改 `async-queue` 配置 `channel`

## 1.1.24 - 1.1.26 版本 SymfonyEventDispatcher 报错

因为 `symfony/console` 默认使用的 `^4.2` 版本，而 `symfony/event-dispatcher` 的 `^4.3` 版本与 `<4.3` 版本不兼容。

`hyperf/framework` 默认推荐使用 `^4.3` 版本的 `symfony/event-dispatcher`，就有一定概率导致实现上的冲突。

如果有类似的情况出现，可以尝试以下操作

```
rm -rf vendor
rm -rf composer.lock
composer require "symfony/event-dispatcher:^4.3"
```

1.1.27 版本中，会在 `composer.json` 中添加以下配置，来处理这个问题。

```
    "conflict": {
        "symfony/event-dispatcher": "<4.3"
    },
```

## 使用 AMQP 组件报 `Swoole\Error: API must be called in the coroutine` 错误

可以在 `config/autoload/amqp.php` 配置文件中将 `close_on_destruct` 改为 `false` 即可。

## 使用 Swoole 4.5 版本和 view 组件时访问接口出现 404

使用 Swoole 4.5 版本和 view 组件如果出现接口 404 的问题，可以尝试删除 `config/autoload/server.php` 文件中的 `static_handler_locations` 配置项。

此配置下的路径都会被认为是静态文件路由，所以如果配置了`/`，就会导致所有接口都会被认为是文件路径，导致接口 404。
