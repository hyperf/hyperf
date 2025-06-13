# Minimalist DB component

[hyperf/database](https://github.com/hyperf/database) The function is very powerful, but it is undeniable that the efficiency is indeed a little insufficient. Here is a minimalist `hyperf/db` component.

## Install

```bash
composer require hyperf/db
```

## Publish component configuration

The configuration file for this component is located at `config/autoload/db.php`. If the file does not exist, you can publish the configuration file to the skeleton with the following command:

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## Component configuration

The default configuration `config/autoload/db.php` is as follows, the database supports multi-database configuration, the default is `default`.

|  configuration item  |  type  |       default      |                          remark                          |
|:--------------------:|:------:|:------------------:|:--------------------------------------------------------:|
|        driver        | string |        none        |                    The database engine                   |
|         host         | string |    `localhost`     |                      database address                    |
|         port         |  int   |        3306        |                      database address                    |
|       database       | string |        none        |                    Database default DB                   |
|       username       | string |        none        |                     database username                    |
|       password       | string |        null        |                     database password                    |
|       charset        | string |        utf8        |                     database charset                     |
|      collation       | string |  utf8_unicode_ci   |                    database collation                    |
|      fetch_mode      |  int   | `PDO::FETCH_ASSOC` |                 PDO Query result set type                |
| pool.min_connections |  int   |         1          | The minimum number of connections in the connection pool |
| pool.max_connections |  int   |         10         | The maximum number of connections in the connection pool |
| pool.connect_timeout | float  |        10.0        |                  connection wait timeout                 |
|  pool.wait_timeout   | float  |        3.0         |                      overtime time                       |
|    pool.heartbeat    |  int   |         -1         |                        heartbeat                         |
|  pool.max_idle_time  | float  |        60.0        |                    maximum idle time                     |
|       options        | array  |                    |                      PDO configure                       |

## Component supported methods

The specific interface can be viewed `Hyperf\DB\ConnectionInterface`.

|    method name   |  return value  |                                       remark                                        |
|:----------------:|:--------------:|:-----------------------------------------------------------------------------------:|
| beginTransaction |     `void`     |                    Open transaction (Support transaction nesting)                   |
|      commit      |     `void`     |                   Commit transaction (Support transaction nesting                   |
|     rollBack     |     `void`     |                  Rollback transaction (Support transaction nesting)                 |
|      insert      |     `int`      | Insert data, return the primary key ID, non-auto-incrementing primary key returns 0 |
|     execute      |     `int`      |                   Execute SQL to return the number of rows affected                 |
|      query       |    `array`     |                       Query SQL, return a list of result sets                       |
|      fetch       | `array, object`|                  Query SQL to return the first row of the result set                |
|      connection  |     `self`     |                          Specify the database to connect to                         |

## Use

### Use DB instance

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Use static methods

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Custom methods using anonymous functions

> This method allows users to directly operate the underlying `PDO` or `MySQL`, so you need to deal with compatibility issues yourself

For example, if we want to execute certain queries and use different `fetch mode`, we can customize our own methods in the following ways

```php
<?php
use Hyperf\DB\DB;

$sql = 'SELECT * FROM `user` WHERE id = ?;';
$bindings = [2];
$mode = \PDO::FETCH_OBJ;
$res = DB::run(function (\PDO $pdo) use ($sql, $bindings, $mode) {
    $statement = $pdo->prepare($sql);

    $this->bindValues($statement, $bindings);

    $statement->execute();

    return $statement->fetchAll($mode);
});
```
