# 查询构造器

## 数据库查询

这里只提供一部分常用的教程，具体教程可以到Laravel官网查看。
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

### 查询

```php
use Hyperf\DbConnection\Db;

$users  = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'sex as user_sex')->get();
```

`Db::select()`方法会返回一个array，而`get`方法会返回`Hyperf\Utils\Collection`。其中元素是`stdClass`，所以可以通过以下代码返回各个元素的数据

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### 聚合查询

框架还提供了聚合类方法，例如`count`, `max`, `min`, `avg`, `sum`。

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

### 原始表达式

有时你需要在查询中使用原始表达式，例如实现`COUNT(0) AS count`，这就需要用到`raw`方法。

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('sex', Db::raw('COUNT(0) AS `count`'))->groupBy('sex')->get();
```

