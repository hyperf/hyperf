# 介绍

> `hyperf/database` 借鉴于 `laravel/database`，其中功能大多相同，但某些细节进行调整。在这里感谢laravel开发组，实现了这么好用的ORM组件。

database组件原设计是在fpm模式下运行，所以hyperf不能直接使用。这里就需要提一下`hyperf/db-connection`组件，他基于`hyperf/pool`实现了数据库连接池，以它作为桥梁，hyperf才能把数据库组件接入进来。

~~~php
<?php

$user = User::query()->where('id',1)->first();

var_dump($user->toArray());
~~~
