# PgSQL driver for Hyperf DB Component

## 安装

> hyperf/db 组件版本必须大于等于 v2.1.8

```
composer require hyperf/db-pgsql-incubator
```

## 配置

修改 `autoload/db.php` 配置

```php

use Hyperf\DB\PgSQL\PgSQLPool;

return [
    'default' => [
        'driver' => PgSQLPool::class,
        'host' => '127.0.0.1',
        'port' => 5432,
        'database' => 'postgres',
        'username' => 'postgres',
        'password' => 'root',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 32,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => 60,
        ],
    ],
];
```

## 使用

具体使用方式与 PDO 一致，只不过需要注意，`pgsql` 中的变量使用 `$1` 而不是 `?` 代表。

例如下述代码

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM USERS WHERE id = $1 AND nickname = $2;', [2, 'Hyperf']);
```

