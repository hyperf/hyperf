# Quick start

## Foreword

> [hyperf/database](https://github.com/hyperf-cloud/database) is derived from [illuminate/database](https://github.com/illuminate/database), we have made some modifications to it, big Some features remain the same. Thanks to the Laravel development team for implementing such a powerful ORM component.

[hyperf/database](https://github.com/hyperf-cloud/database) The component is a component derived from [illuminate/database](https://github.com/illuminate/database), we do it Some modifications have been designed to be used in other PHP-FPM frameworks or Swoole-based frameworks, and in Hyperf you need to mention [hyperf/db-connection](https://github.com/hyperf- The cloud/db-connection) component, which implements the database connection pool and a new abstraction of the model based on [hyperf/pool](https://github.com/hyperf-cloud/pool), using it as a bridge, Hyperf In order to access the database components and event components.

## Install

### Hyperf framework

```bash
composer require hyperf/db-connection
```

### Other framework

```bash
composer require hyperf/database
```

## Configuration

The default configuration is as follows, the database supports multi-library configuration, the default is `default`.

|     Config item      |  type  |  default value  |                   remark                    |
| :------------------: | :----: | :-------------: | :-----------------------------------------: |
|        driver        | string |      null       |               Database engine               |
|         host         | string |      null       |              Database address               |
|       database       | string |      null       |             Database default DB             |
|       username       | string |      null       |              Database username              |
|       password       | string |      null       |              Database password              |
|       charset        | string |      utf8       |              Database encoding              |
|      collation       | string | utf8_unicode_ci |              Database encoding              |
|        prefix        | string |       ''        |            Database model prefix            |
| pool.min_connections |  int   |        1        | Connection pool least Number of connections |
| pool.max_connections |  int   |       10        |  Connection pool max Number of connections  |
| pool.connect_timeout | float  |      10.0       |           Connection wait timeout           |
|  pool.wait_timeout   | float  |       3.0       |                 max timeout                 |
|    pool.heartbeat    |  int   |       -1        |                  Heartbeat                  |
|  pool.max_idle_time  | float  |      60.0       |              Maximum idle time              |
|       options        | array  |                 |              PDO configuration              |

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

Sometimes users need to modify the PDO default configuration, such as all fields need to be returned as string. At this time, you need to modify the PDO configuration item `ATTR_STRINGIFY_FETCHES` to true.

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
            // Frame default configuration
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];

```

### Read and write separation

Sometimes you want the `SELECT` statement to use a database connection, and the `INSERT`, `UPDATE`, and `DELETE` statements use another database connection. In `Hyperf`, whether you use native queries, query constructors, or models, you can easily implement

To understand how read-write separation is configured, let's look at an example:

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

Note that in the above example, three keys have been added to the configuration array, namely `read`, `write` and `sticky`. The `read` and `write` keys all contain an array of keys `host`. The other databases for `read` and `write` are in arrays with keys mysql.

If you want to override the configuration in the main array, you only need to modify the `read` and `write` arrays. So, in this example: 192.168.1.1 will be the "read" connection host, and 192.168.1.2 will be the "write" connection host. These two connections share the configuration of the mysql array, such as the database credentials (username / password), prefix, character encoding, and so on.

`sticky` is an optional value that can be used to immediately read records that have been written to the database during the current request period. If the `sticky` option is enabled and a "write" operation has been performed during the current request cycle, then any "read" operation will use the "write" connection. This ensures that data written in the same request cycle can be read immediately, thus avoiding the problem of data inconsistency caused by master-slave delay. But whether to enable it depends on the needs of the application.

### Multiple database configuration

Multiple database configuration example

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
When using, you only need to specify `connection` to `test`, you can use the configuration in `test`, as follows.

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

Modify the `connection` field in the model to use the corresponding configuration. For example, `Model` uses the `test` configuration.

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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

## Execute a native SQL statement

Once the database is configured, you can use `Hyperf\DbConnection\Db` for queries.

### Query query class

This mainly includes `Select`, stored procedures such as `READS SQL DATA`, functions and other query statements.

The `select` method will always return an array, and each result in the array is a `StdClass` object.

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]);  //  return array 

foreach($users as $user){
    echo $user->name;
}

```

### Execute execution class

This mainly includes `Insert`, `Update`, `Delete`, and the execution statement of the stored procedure of `MODIFIES SQL DATA`.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1, 'Hyperf']); // return success bool

$affected = Db::update('UPDATE user set name = ? WHERE id = ?', ['John', 1]); // return the number of rows affected int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // return the number of rows affected int

$result = Db::statement("CALL pro_test(?, '?')", [1, 'your words']);  // return bool  CALL pro_test(?，?) Is a stored procedure，Attribute is MODIFIES SQL DATA
```

### Automatic management of database transactions

You can run a set of operations in a database transaction using `Db`'s `transaction` method. If an exception occurs in the closure of the transaction `Closure`, the transaction will be rolled back. If the transaction closure `Closure` is executed successfully, the transaction will be committed automatically. Once you've used `transaction` , you no longer need to worry about manual rollbacks or commits:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### Manually manage database transactions

If you want to start a transaction manually and have full control over rollback and commit, then you can use `Db`'s `beginTransaction`, `commit`, `rollBack`:

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
