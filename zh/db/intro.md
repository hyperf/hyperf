# 介绍

> `hyperf/database` 衍生于 `laravel/database`，我们对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 Laravel 开发组，实现了如此强大好用的 ORM 组件。

`hyperf/database` 组件是基于 `laravel/database` 衍生出来的组件，我们对它进行了一些改造，从设计上是允许用于其它 PHP-FPM 框架或基于 Swoole 的框架中的，而在 Hyperf 里就需要提一下 `hyperf/db-connection` 组件，它基于 `hyperf/pool` 实现了数据库连接池并对模型进行了新的抽象，以它作为桥梁，Hyperf 才能把数据库组件及事件组件接入进来。

~~~php
<?php

$user = User::query()->where('id',1)->first();

var_dump($user->toArray());
~~~
