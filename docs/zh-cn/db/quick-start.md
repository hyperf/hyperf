# 快速开始

## 前言

> [hyperf/database](https://github.com/hyperf/database) 衍生于 [illuminate/database](https://github.com/illuminate/database)，我们对它进行了一些改造，大部分功能保持了相同。在这里感谢一下 Laravel 开发组，实现了如此强大好用的 ORM 组件。

[hyperf/database](https://github.com/hyperf/database) 组件是基于 [illuminate/database](https://github.com/illuminate/database) 衍生出来的组件，我们对它进行了一些改造，从设计上是允许用于其它 PHP-FPM 框架或基于 Swoole 的框架中的，而在 Hyperf 里就需要提一下 [hyperf/db-connection](https://github.com/hyperf/db-connection) 组件，它基于 [hyperf/pool](https://github.com/hyperf/pool) 实现了数据库连接池并对模型进行了新的抽象，以它作为桥梁，Hyperf 才能把数据库组件及事件组件接入进来。

## 安装

### Hyperf 框架

```bash
composer require hyperf/db-connection
```

### 其它框架

```bash
composer require hyperf/database
```

## 配置

默认配置如下，数据库支持多库配置，默认为 `default`。

|        配置项        |  类型  |     默认值      |        备注        |
| :------------------: | :----: | :-------------: | :----------------: |
|        driver        | string |       无        |     数据库引擎     |
|         host         | string |       无        |     数据库地址     |
|       database       | string |       无        |   数据库默认 DB    |
|       username       | string |       无        |    数据库用户名    |
|       password       | string |      null       |     数据库密码     |
|       charset        | string |      utf8       |     数据库编码     |
|      collation       | string | utf8_unicode_ci |     数据库编码     |
|        prefix        | string |       ''        |   数据库模型前缀   |
|       timezone       | string |      null       |     数据库时区     |
| pool.min_connections |  int   |        1        | 连接池内最少连接数 |
| pool.max_connections |  int   |       10        | 连接池内最大连接数 |
| pool.connect_timeout | float  |      10.0       |  连接等待超时时间  |
|  pool.wait_timeout   | float  |       3.0       |      超时时间      |
|    pool.heartbeat    |  int   |       -1        |        心跳        |
|  pool.max_idle_time  | float  |      60.0       |    最大闲置时间    |
|       options        | array  |                 |      PDO 配置      |

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ]
    ],
];
```

有时候用户需要修改 PDO 默认配置，比如所有字段需要返回为 string。这时候就需要修改 PDO 配置项 `ATTR_STRINGIFY_FETCHES` 为 true。

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            // 框架默认配置
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // 如果使用的为非原生 MySQL 或云厂商提供的 DB 如从库/分析型实例等不支持 MySQL prepare 协议的, 将此项设置为 true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### 读写分离

有时候你希望 `SELECT` 语句使用一个数据库连接，而 `INSERT`，`UPDATE`，和 `DELETE` 语句使用另一个数据库连接。在 `Hyperf` 中，无论你是使用原生查询，查询构造器，或者是模型，都能轻松的实现

为了弄明白读写分离是如何配置的，我们先来看个例子：

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'read' => [
            'host' => ['192.168.1.1'],
        ],
        'write' => [
            'host' => ['196.168.1.2'],
        ],
        'sticky'    => true,
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];

```

注意在以上的例子中，配置数组中增加了三个键，分别是 `read`， `write` 和 `sticky`。 `read` 和 `write` 的键都包含一个键为 `host` 的数组。而 `read` 和 `write` 的其他数据库都在键为 mysql 的数组中。

如果你想重写主数组中的配置，只需要修改 `read` 和 `write` 数组即可。所以，这个例子中： 192.168.1.1 将作为 「读」 连接主机，而 192.168.1.2 将作为 「写」 连接主机。这两个连接会共享 mysql 数组的各项配置，如数据库的凭据（用户名 / 密码），前缀，字符编码等。

`sticky` 是一个 可选值，它可用于立即读取在当前请求周期内已写入数据库的记录。若 `sticky` 选项被启用，并且当前请求周期内执行过 「写」 操作，那么任何 「读」 操作都将使用 「写」 连接。这样可确保同一个请求周期内写入的数据可以被立即读取到，从而避免主从延迟导致数据不一致的问题。不过是否启用它，取决于应用程序的需求。

### 多库配置

多库配置如下。

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
    'test'=>[
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST2', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'collation' => env('DB_COLLATION', 'utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];

```

使用时，只需要规定 `connection` 为 `test`，就可以使用 `test` 中的配置，如下。

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

模型中修改 `connection` 字段，即可使用对应配置，例如一下 `Model` 使用 `test` 配置。

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

/**
 * @property int $id
 * @property string $mobile
 * @property string $realname
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'test';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'mobile', 'realname'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer'];
}

```

## 执行原生 SQL 语句

配置好数据库后，便可以使用 `Hyperf\DbConnection\Db` 进行查询。

### Query 查询类

这里主要包括 `Select`、属性为 `READS SQL DATA` 的存储过程、函数等查询语句。   

`select` 方法将始终返回一个数组，数组中的每个结果都是一个 `StdClass` 对象

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]);  //  返回array 

foreach($users as $user){
    echo $user->name;
}

```

### Execute 执行类

这里主要包括 `Insert`、`Update`、`Delete`，属性为 `MODIFIES SQL DATA` 的存储过程等执行语句。

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1, 'Hyperf']); // 返回是否成功 bool

$affected = Db::update('UPDATE user set name = ? WHERE id = ?', ['John', 1]); // 返回受影响的行数 int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // 返回受影响的行数 int

$result = Db::statement("CALL pro_test(?, '?')", [1, 'your words']);  // 返回 bool  CALL pro_test(?，?) 为存储过程，属性为 MODIFIES SQL DATA
```

### 自动管理数据库事务

你可以使用 `Db` 的 `transaction` 方法在数据库事务中运行一组操作。如果事务的闭包 `Closure` 中出现一个异常，事务将会回滚。如果事务闭包 `Closure` 执行成功，事务将自动提交。一旦你使用了 `transaction` ， 就不再需要担心手动回滚或提交的问题：

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### 手动管理数据库事务

如果你想要手动开始一个事务，并且对回滚和提交能够完全控制，那么你可以使用 `Db` 的 `beginTransaction`, `commit`, `rollBack`:

```php
use Hyperf\DbConnection\Db;

Db::beginTransaction();
try{

    // Do something...

    Db::commit();
} catch(\Throwable $ex){
    Db::rollBack();
}
```

## 输出刚刚执行的 SQL

> 当前方法仅能用于开发环境，线上部署前一定要去掉，不然会引起严重的内存泄露和数据混淆。

线上记录 `SQL`，请使用 [事件监听](/zh-cn/db/event)

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// 启用 SQL 数据记录功能
Db::enableQueryLog();

$book = Book::query()->find(1);

// 打印最后一条 SQL 相关数据
var_dump(Arr::last(Db::getQueryLog()));
```

## 驱动列表

和 [illuminate/database](https://github.com/illuminate/database) 不同，[hyperf/database](https://github.com/hyperf/database) 默认只提供了 MySQL 驱动，目前还提供了 [PgSQL](https://github.com/hyperf/database-pgsql)、[SQLite](https://github.com/hyperf/database-sqlite)和[SQL Server](https://github.com/hyperf/database-sqlserver-incubator) 等驱动。

如果默认的 MySQL 驱动满足不了使用需求，可以自行安装对应的驱动：

### PgSql 驱动

#### 安装

要求 `Swoole >= 5.1.0` 并且编译时开启 `--enable-swoole-pgsql`

```bash
composer require hyperf/database-pgsql
```

#### 配置文件

```php
// config/autoload/databases.php
return [
     // 其他配置
    'pgsql'=>[
        'driver' => env('DB_DRIVER', 'pgsql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 5432),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8'),
    ]
];
```

### SQLite 驱动

#### 安装

要求 `Swoole >= 5.1.0` 并且编译时开启 `--enable-swoole-sqlite`

```bash
composer require hyperf/database-sqlite
```

#### 配置文件

```php
// config/autoload/databases.php
return [
     // 其他配置
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: 为内存数据库 也可以指定文件绝对路径
        'database' => env('DB_DATABASE', ':memory:'),
        // other sqlite config
    ]
];
```

### SQL Server 驱动

#### 安装

> 孵化阶段，目前并不能保证所有功能正常，欢迎反馈问题。

要求 `Swoole >= 5.1.0` 依赖 pdo_odbc，需要编译时开启 `--with-swoole-odbc`

```bash
composer require hyperf/database-sqlserver-incubator
```

#### 配置文件

```php
// config/autoload/databases.php
return [
     // 其他配置
    'sqlserver' => [
        'driver' => env('DB_DRIVER', 'sqlsrv'),
        'host' => env('DB_HOST', 'mssql'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 1443),
        'username' => env('DB_USERNAME', 'SA'),
        'password' => env('DB_PASSWORD'),
        'odbc_datasource_name' => 'DRIVER={ODBC Driver 18 for SQL Server};SERVER=127.0.0.1,1433;TrustServerCertificate=yes;database=hyperf',
        'odbc'  =>  true,
    ]
];
```
