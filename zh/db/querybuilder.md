# 查询构造器

## 简介

Hyperf 的数据库查询构造器为创建和运行数据库查询提供了一个方便的接口。它可用于执行应用程序中大部分数据库操作，且可在所有支持的数据库系统上运行。

Hyperf 的查询构造器使用 PDO 参数绑定来保护您的应用程序免受 SQL 注入攻击。因此没有必要清理作为绑定传递的字符串。

这里只提供一部分常用的教程，具体教程可以到Laravel官网查看。
[Laravel Query Builder](https://laravel.com/docs/5.8/queries)

## 获取结果

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM user;');
$users = Db::table('user')->get();
$users = Db::table('user')->select('name', 'gender as user_gender')->get();
```

`Db::select()` 方法会返回一个array，而 `get` 方法会返回 `Hyperf\Utils\Collection`。其中元素是 `stdClass`，所以可以通过以下代码返回各个元素的数据

```php
<?php

foreach ($users as $user) {
    echo $user->name;
}
```

### 获取一列的值

如果你想获取包含单列值的集合，则可以使用 `pluck` 方法。在下面的例子中，我们将获取角色表中标题的集合：

```php
<?php
use Hyperf\DbConnection\Db;

$names = Db::table('user')->pluck('name');

foreach ($names as $name) {
    echo $names;
}

```

你还可以在返回的集合中指定字段的自定义键值：

```php
<?php
use Hyperf\DbConnection\Db;

$roles = Db::table('roles')->pluck('title', 'name');

foreach ($roles as $name => $title) {
    echo $title;
}

```

### 分块结果

如果你需要处理上千条数据库记录，你可以考虑使用 `chunk` 方法。该方法一次获取结果集的一小块，并将其传递给 `闭包` 函数进行处理。该方法在 `Command` 编写数千条处理数据的时候非常有用。例如，我们可以将全部 user 表数据切割成一次处理 100 条记录的一小块：

```php
<?php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {
    foreach ($users as $user) {
        //
    }
});
```

你可以通过在 闭包 中返回 `false` 来终止继续获取分块结果：

```php
use Hyperf\DbConnection\Db;

Db::table('user')->orderBy('id')->chunk(100, function ($users) {

    return false;
});
```

如果要在分块结果时更新数据库记录，则块结果可能会和预计的返回结果不一致。 因此，在分块更新记录时，最好使用 chunkById 方法。 此方法将根据记录的主键自动对结果进行分页：

```php
use Hyperf\DbConnection\Db;

Db::table('user')->where('gender', 1)->chunkById(100, function ($users) {
    foreach ($users as $user) {
        DB::table('user')
            ->where('id', $user->id)
            ->update(['update_time' => time()]);
    }
});
```

> 在块的回调里面更新或删除记录时，对主键或外键的任何更改都可能影响块查询。 这可能会导致记录没有包含在分块结果中。

### 聚合查询

框架还提供了聚合类方法，例如 `count`, `max`, `min`, `avg`, `sum`。

```php
use Hyperf\DbConnection\Db;

$count = Db::table('user')->count();
```

#### 判断记录是否存在

除了通过 `count` 方法可以确定查询条件的结果是否存在之外，还可以使用 `exists` 和 `doesntExist` 方法：

```php
return Db::table('orders')->where('finalized', 1)->exists();

return Db::table('orders')->where('finalized', 1)->doesntExist();
```

## 查询

### 指定一个 Select 语句

当然你可能并不总是希望从数据库表中获取所有列。使用 select 方法，你可以自定义一个 select 查询语句来查询指定的字段：

```php
$users = Db::table('user')->select('name', 'email as user_email')->get();
```

`distinct` 方法会强制让查询返回的结果不重复：

```php
$users = Db::table('user')->distinct()->get();
```

如果你已经有了一个查询构造器实例，并且希望在现有的查询语句中加入一个字段，那么你可以使用 addSelect 方法：

```php
$query = Db::table('users')->select('name');

$users = $query->addSelect('age')->get();
```

## 原始表达式

有时你需要在查询中使用原始表达式，例如实现 `COUNT(0) AS count`，这就需要用到`raw`方法。

```php
use Hyperf\DbConnection\Db;

$res = Db::table('user')->select('gender', Db::raw('COUNT(0) AS `count`'))->groupBy('gender')->get();
```

### 原生方法

可以使用以下方法代替 `Db::raw`，将原生表达式插入查询的各个部分。

`selectRaw` 方法可以代替 `select(Db::raw(...))`。该方法的第二个参数是可选项，值是一个绑定参数的数组：

```php
$orders = Db::table('order')
    ->selectRaw('price * ? as price_with_tax', [1.0825])
    ->get();
```

`whereRaw` 和 `orWhereRaw` 方法将原生的 `where` 注入到你的查询中。这两个方法的第二个参数还是可选项，值还是绑定参数的数组：

```php
$orders = Db::table('order')
    ->whereRaw('price > IF(state = "TX", ?, 100)', [200])
    ->get();
```

`havingRaw` 和 `orHavingRaw` 方法可以用于将原生字符串设置为 `having` 语句的值：

```php
$orders = Db::table('order')
    ->select('department', Db::raw('SUM(price) as total_sales'))
    ->groupBy('department')
    ->havingRaw('SUM(price) > ?', [2500])
    ->get();
```

`orderByRaw` 方法可用于将原生字符串设置为 `order by` 子句的值：

```php
$orders = DB::table('order')
    ->orderByRaw('updated_at - created_at DESC')
    ->get();
```

## 表连接




