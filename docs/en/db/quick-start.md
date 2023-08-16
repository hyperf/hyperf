# Quick start

## Foreword

> [hyperf/database](https://github.com/hyperf/database) is derived from [illuminate/database](https://github.com/illuminate/database), we have made some modifications to it but most methods remain the same. Thanks to the Laravel development team for implementing such a powerful and easy-to-use ORM component.

The [hyperf/database](https://github.com/hyperf/database) component is based on the components derived from [illuminate/database](https://github.com/illuminate/database) with some The changes to allow usage in both PHP-FPM frameworks or Swoole-based frameworks. In Hyperf, you need to use the [hyperf/db-connection](https://github.com/hyperf/db-connection) component, which implements database connection pool based on [hyperf/pool](https://github.com/hyperf/pool). With it as a bridge, Hyperf can integrate database connections and events.

## Installation

### Hyperf framework

```bash
composer require hyperf/db-connection
```

### Other frameworks

```bash
composer require hyperf/database
```

## Configuration

The default configuration is as follows, the configuration supports configuring multiple database connections. The default connection that is used when no connection is specified is called `default`.

| Name                 | Type   | Default value   | Description                                          |
| :------------------: | :----: | :-------------: | :--------------------------------------------------: |
| driver               | string | none            | Database type                                        |
| host                 | string | none            | Database host                                        |
| database             | string | none            | Database name                                        |
| username             | string | none            | Database username                                    |
| password             | string | null            | Database password                                    |
| charset              | string | utf8            | Database string charset                              |
| collation            | string | utf8_unicode_ci | Database string collation                            |
| prefix               | string | ''              | Database table prefix                                |
| timezone             | string | null            | Database time zone                                   |
| pool.min_connections | int    | 1               | Minimum number of connections in the connection pool |
| pool.max_connections | int    | 10              | Maximum number of connections in the connection pool |
| pool.connect_timeout | float  | 10.0            | Connection waiting timeout                           |
| pool.wait_timeout    | float  | 3.0             | Timeout time in seconds                              |
| pool.heartbeat       | int    | -1              | Connection heartbeat (-1 equals disabled)            |
| pool.max_idle_time   | float  | 60.0            | Connection maximum idle time before closing          |
| options              | array  |                 | PDO configuration options                            |

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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

Sometimes users need to modify the default PDO configuration. For example, if you want to return all fields as strings, you need to set the PDO configuration item `ATTR_STRINGIFY_FETCHES` to `true`.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            // Framework default configuration
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // If you are using a non-native MySQL or a DB provided by a cloud vendor, such as a database/analytic instance that does not support the MySQL prepare protocol, set this to true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### Read and write separation

Sometimes you want the `SELECT` statement to use one database connection and the `INSERT`, `UPDATE`, and `DELETE` statements to use another database connection. This is easy to implement in Hyperf, regardless whether you are using a native query, query builder, or model.

In order to understand how the read-write separation is configured, let's first look at an example:

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'read' => [
            'host' => ['192.168.1.1'],
        ],
        'write' => [
            'host' => ['196.168.1.2'],
        ],
        'sticky' => true,
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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

Note that in the above example, three keys have been added to the configuration array, namely `read`, `write` and `sticky`. The keys of `read` and `write` both contain an array with the key `host`.

If you want to rewrite the configuration in the main array, you only need to modify the `read` and `write` arrays. So, in this example: 192.168.1.1 will be used as the "read" connection host, and 192.168.1.2 will be used as the "write" connection host. The two connections will share various configurations of the mysql array, such as database credentials (username/password), prefix, character encoding, etc.

`sticky` is an optional value that can be used to immediately read the records that have been written to the database during the current request cycle. If the `sticky` option is enabled and a "write" operation has been performed in the current request cycle, then any "read" operation will use the "write" connection. This ensures that the data written in the same request cycle can be read immediately, thereby avoiding the problem of data inconsistency caused by master-slave delay. However, whether this option should be enabled depends on the needs of the application.

### Configuring multiple database connections

The multi-database configuration is as follows.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST2','localhost'),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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
To use different connections, you only need to specify `connection` via the query builder:

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

You can change the default connection used by a certain model by setting the value of `$connection` inside the model class:

> Note that the property visibility must be set as `protected`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact group@hyperf.io
 * @license https://github.com/hyperf/hyperf/blob/master/LICENSE
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
    protected $table ='user';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection ='test';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','mobile','realname'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' =>'integer'];
}
```

## Executing native SQL statements

After configuring the database, you can use `Hyperf\DbConnection\Db` to query.

### Querying data

This includes query statements such as `select`, stored procedures and functions that read SQL data.

The `select` method will always return an array, and each result in the array is a `StdClass` object.

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]); // return array

foreach($users as $user){
    echo $user->name;
}
```

### Modifying data

This includes execution statements such as `Insert`, `Update`, `Delete`, and stored procedures that modify SQL data.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1,'Hyperf']); // Returns whether it is successful bool

$affected = Db::update('UPDATE user set name =? WHERE id = ?', ['John', 1]); // Returns the number of affected rows int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // Returns the number of affected rows int

$result = Db::statement("CALL pro_test(?,'?')", [1,'your words']); // return bool CALL pro_test(?,?) is a stored procedure, the attribute is MODIFIES SQL DATA
```

### Automatically manage database transactions

You can use the `transaction` method of `Db` to run a set of operations as a database transaction. If an exception occurs in the transaction closure, the transaction will be rolled back. If the transaction closure is executed successfully, the transaction will be committed automatically. This means that you don't have to worry about rollbacks or commits when using the `transaction` method:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### Manually manage database transactions

If you want to manually start a transaction and have complete control over rollback and commit, you can use `beginTransaction`, `commit`, `rollBack` methods:

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

## Logging the raw SQL queries

> The current method can only be used in the development environment and must be removed before online deployment, otherwise it will cause serious memory leaks and data consistency issues.

You can use the [database event listener](en/db/event) to record the SQL queries:

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// Enable SQL data logging function
// WARNING: causes a memory leak and data consistency problems in the Swoole CLI environment, local development and debugging only!
Db::enableQueryLog();

$book = Book::query()->find(1);

// Print the last SQL query
var_dump(Arr::last(Db::getQueryLog()));
```
