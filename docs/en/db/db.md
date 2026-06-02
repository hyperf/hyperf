# Minimalist DB Component

[hyperf/database](https://github.com/hyperf/database) is very powerful, but it is undeniable that it is slightly lacking in efficiency. Here, a minimalist `hyperf/db` component is provided.

## Installation

```bash
composer require hyperf/db
```

## Publish Component Configuration

The configuration file for this component is located at `config/autoload/db.php`. If the file does not exist, you can publish the configuration file to the skeleton via the following command:

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## Component Configuration

The default configuration in `config/autoload/db.php` is as follows. The database supports multi-database configuration, with `default` as the default connection.

| Configuration Item | Type | Default Value | Remarks |
|:--------------------:|:------:|:------------------:|:--------------------------------:|
| driver | string | N/A | Database engine |
| host | string | `localhost` | Database address |
| port | int | 3306 | Database address |
| database | string | N/A | Default database |
| username | string | N/A | Database username |
| password | string | null | Database password |
| charset | string | utf8 | Database character set |
| collation | string | utf8_unicode_ci | Database collation |
| fetch_mode | int | `PDO::FETCH_ASSOC` | PDO query result set type |
| pool.min_connections | int | 1 | Minimum connections in connection pool |
| pool.max_connections | int | 10 | Maximum connections in connection pool |
| pool.connect_timeout | float | 10.0 | Connection wait timeout |
| pool.wait_timeout | float | 3.0 | Timeout duration |
| pool.heartbeat | int | -1 | Heartbeat |
| pool.max_idle_time | float | 60.0 | Maximum idle time |
| options | array | | PDO configuration |

## Supported Methods

For specific interfaces, please check `Hyperf\DB\ConnectionInterface`.

| Method Name | Return Value Type | Remarks |
|:----------------:|:--------------:|:------------------------------------:|
| beginTransaction | `void` | Start a transaction, supports nested transactions |
| commit | `void` | Commit a transaction, supports nested transactions |
| rollBack | `void` | Rollback a transaction, supports nested transactions |
| insert | `int` | Insert data, returns primary key ID; returns 0 for non-auto-increment primary keys |
| execute | `int` | Execute SQL, returns number of affected rows |
| query | `array` | Query SQL, returns result set list |
| fetch | `array, object` | Query SQL, returns the first row of the result set |
| connection | `self` | Specify the database connection |

## Usage

### Using DB Instance

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);
```

### Using Static Methods

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);
```

### Using Anonymous Functions to Customize Methods

> This method allows users to directly operate the underlying `PDO` or `MySQL`, so you need to handle compatibility issues yourself.

For example, if we want to execute certain queries using different `fetch mode`s, we can customize our own method via the following approach:

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
