# Quick Start

## Preface

> [hyperf/database](https://github.com/hyperf/database) is derived from [illuminate/database](https://github.com/illuminate/database). We have made some modifications to it, while keeping most of the functionality the same. We would like to thank the Laravel development team for implementing such a powerful and easy-to-use ORM component.

The [hyperf/database](https://github.com/hyperf/database) component is derived from [illuminate/database](https://github.com/illuminate/database). We have modified it to allow it to be used in other PHP-FPM frameworks or Swoole-based frameworks. In Hyperf, it's worth mentioning the [hyperf/db-connection](https://github.com/hyperf/db-connection) component, which implements a database connection pool based on [hyperf/pool](https://github.com/hyperf/pool) and provides a new abstraction for models. Acting as a bridge, it allows Hyperf to integrate the database component and event component.

## Installation

### Hyperf Framework

```bash
composer require hyperf/db-connection
```

### Other Frameworks

```bash
composer require hyperf/database
```

## Configuration

The default configuration is as follows. Database supports multiple database configurations, with `default` being the default.

| Config Item | Type | Default Value | Remark |
| :---: | :---: | :---: | :---: |
| driver | string | None | Database Engine |
| host | string | None | Database Address |
| database | string | None | Default DB |
| username | string | None | Database Username |
| password | string | null | Database Password |
| charset | string | utf8 | Database Charset |
| collation | string | utf8_unicode_ci | Database Collation |
| prefix | string | '' | Model Prefix |
| timezone | string | null | Database Timezone |
| pool.min_connections | int | 1 | Min Connections |
| pool.max_connections | int | 10 | Max Connections |
| pool.connect_timeout | float | 10.0 | Connect Timeout |
| pool.wait_timeout | float | 3.0 | Wait Timeout |
| pool.heartbeat | int | -1 | Heartbeat |
| pool.max_idle_time | float | 60.0 | Max Idle Time |
| options | array | | PDO Options |

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

Sometimes users need to modify the default PDO configuration, for example, if all fields need to be returned as strings. In this case, you need to modify the PDO option `ATTR_STRINGIFY_FETCHES` to true.

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
            // Framework default configuration
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // If you are using a non-native MySQL database or a DB provided by a cloud vendor (such as a slave instance/analytical instance) that does not support the MySQL prepare protocol, set this to true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### Read/Write Splitting

Sometimes you want `SELECT` statements to use one database connection, and `INSERT`, `UPDATE`, and `DELETE` statements to use another. In `Hyperf`, whether you are using native queries, the query builder, or models, this can be easily achieved.

To understand how read/write splitting is configured, let's look at an example:

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

Note that in the example above, three keys have been added to the configuration array: `read`, `write`, and `sticky`. Both `read` and `write` keys contain an array with a `host` key. Other database configurations for `read` and `write` are shared from the `mysql` array.

If you want to override configurations from the main array, simply modify the `read` and `write` arrays. Therefore, in this example: 192.168.1.1 will be used as the "read" connection host, while 192.168.1.2 will be used as the "write" connection host. Both connections will share the configurations from the `mysql` array, such as database credentials (username/password), prefix, character encoding, etc.

`sticky` is an optional value that can be used to immediately read records that have been written to the database during the current request cycle. If the `sticky` option is enabled, and a "write" operation has been performed during the current request cycle, any "read" operation will use the "write" connection. This ensures that data written during the same request cycle can be read immediately, thereby avoiding data inconsistency issues caused by master-slave replication lag. Whether to enable it, however, depends on the needs of the application.

### Multiple Database Configurations

Multiple database configurations are as follows:

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

When using it, just specify `connection` as `test` to use the configuration from `test`, as follows:

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

In the model, modify the `connection` property to use the corresponding configuration. For example, the following `Model` uses the `test` configuration:

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

## Executing Native SQL Statements

Once the database is configured, you can use `Hyperf\DbConnection\Db` to perform queries.

### Query Class
This mainly includes `Select`, stored procedures with `READS SQL DATA` attribute, functions, and other query statements.

The `select` method will always return an array, and each result in the array is a `StdClass` object.

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]);  // Returns an array

foreach($users as $user){
    echo $user->name;
}
```

### Execute Class
This mainly includes `Insert`, `Update`, `Delete`, and stored procedures with `MODIFIES SQL DATA` attribute and other execution statements.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1, 'Hyperf']); // Returns success bool

$affected = Db::update('UPDATE user set name = ? WHERE id = ?', ['John', 1]); // Returns the number of affected rows (int)

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // Returns the number of affected rows (int)

$result = Db::statement("CALL pro_test(?, '?')", [1, 'your words']);  // Returns bool. CALL pro_test(?, ?) is a stored procedure with attribute MODIFIES SQL DATA
```

### Automatic Database Transaction Management

You can use the `transaction` method of `Db` to run a set of operations within a database transaction. If an exception occurs in the `Closure` of the transaction, the transaction will be rolled back. If the `Closure` of the transaction executes successfully, the transaction will be automatically committed. Once you use `transaction`, you no longer need to worry about manual rollback or commit:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});
```

### Manual Database Transaction Management

If you want to manually start a transaction and have full control over rollback and commit, you can use `beginTransaction`, `commit`, and `rollBack` of `Db`:

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

## Printing the Recently Executed SQL

> The current method can only be used in the development environment. It must be removed before deployment to production, otherwise it will cause serious memory leaks and data confusion.

To log `SQL` in production, please use [Event Listening](/en/db/event)

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// Enable SQL query logging
Db::enableQueryLog();

$book = Book::query()->find(1);

// Print the last SQL related data
var_dump(Arr::last(Db::getQueryLog()));
```

## Driver List

Unlike [illuminate/database](https://github.com/illuminate/database), [hyperf/database](https://github.com/hyperf/database) only provides MySQL driver by default. Currently, it also provides drivers like [PgSQL](https://github.com/hyperf/database-pgsql), [SQLite](https://github.com/hyperf/database-sqlite), and [SQL Server](https://github.com/hyperf/database-sqlserver-incubator).

If the default MySQL driver does not meet your needs, you can install the corresponding driver yourself:

### PgSql Driver

#### Installation

Requires `Swoole >= 5.1.0` and `--enable-swoole-pgsql` enabled during compilation.

```bash
composer require hyperf/database-pgsql
```

#### Configuration File

```php
// config/autoload/databases.php
return [
     // Other configurations
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

### SQLite Driver

#### Installation

Requires `Swoole >= 5.1.0` and `--enable-swoole-sqlite` enabled during compilation.

```bash
composer require hyperf/database-sqlite
```

#### Configuration File

```php
// config/autoload/databases.php
return [
     // Other configurations
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: is an in-memory database, or you can specify the absolute file path
        'database' => env('DB_DATABASE', ':memory:'),
        // other sqlite config
    ]
];
```

### SQL Server Driver

#### Installation

> This is in the incubation stage. We cannot guarantee that all functions work properly at present. Feedback is welcome.

Requires `Swoole >= 5.1.0` and depends on `pdo_odbc`. It requires `--with-swoole-odbc` enabled during compilation.

```bash
composer require hyperf/database-sqlserver-incubator
```

#### Configuration File

```php
// config/autoload/databases.php
return [
     // Other configurations
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
