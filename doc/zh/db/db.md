# 极简的 DB 组件

[hyperf/database](https://github.com/hyperf/database) 功能十分强大，但也不可否认效率上确实些许不足。这里提供一个极简的 `hyperf/db` 组件，支持 `PDO` 和 `Swoole Mysql`。

## 安装

```bash
composer require hyperf/db
```

## 发布组件配置

该组件的配置文件位于 `config/autoload/db.php`，如果文件不存在，可通过下面的命令来将配置文件发布到骨架去： 

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## 组件配置

默认配置 `config/autoload/db.php` 如下，数据库支持多库配置，默认为 `default`。

|        配置项        |  类型  |       默认值       |               备注               |
|:--------------------:|:------:|:------------------:|:--------------------------------:|
|        driver        | string |         无         | 数据库引擎 支持 `pdo` 和 `mysql` |
|         host         | string |    `localhost`     |            数据库地址            |
|         port         |  int   |        3306        |            数据库地址            |
|       database       | string |         无         |          数据库默认 DB           |
|       username       | string |         无         |           数据库用户名           |
|       password       | string |        null        |            数据库密码            |
|       charset        | string |        utf8        |            数据库编码            |
|      collation       | string |  utf8_unicode_ci   |            数据库编码            |
|      fetch_mode      |  int   | `PDO::FETCH_ASSOC` |        PDO 查询结果集类型        |
| pool.min_connections |  int   |         1          |        连接池内最少连接数        |
| pool.max_connections |  int   |         10         |        连接池内最大连接数        |
| pool.connect_timeout | float  |        10.0        |         连接等待超时时间         |
|  pool.wait_timeout   | float  |        3.0         |             超时时间             |
|    pool.heartbeat    |  int   |         -1         |               心跳               |
|  pool.max_idle_time  | float  |        60.0        |           最大闲置时间           |
|       options        | array  |                    |             PDO 配置             |

## 组件支持的方法

具体接口可以查看 `Hyperf\DB\ConnectionInterface`。

|      方法名      |   返回值类型   |                  备注                   |
|:----------------:|:--------------:|:---------------------------------------:|
| beginTransaction |     `void`     |          开启事务 支持事务嵌套          |
|      commit      |     `void`     |          提交事务 支持事务嵌套          |
|     rollBack     |     `void`     |          回滚事务 支持事务嵌套          |
|      insert      |     `int`      | 插入数据，返回主键 ID，非自增主键返回 0 |
|     execute      |     `int`      |       执行 SQL，返回受影响的行数        |
|      query       |    `array`     |        查询 SQL，返回结果集列表         |
|      fetch       | `array,object` |     查询 SQL，返回结果集的首行数据      |
|      connection       | `Hyperf\DB\DB` |     多库配置方式下，指定使用某个库，默认 default      |

## 使用

### 使用 DB 实例

```php
<?php

use Hyperf\Utils\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);
// 使用 默认 default 配置
$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);
// 使用 多库 test 配置
$res = $db->connection('test')->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### 使用静态方法

```php
<?php

use Hyperf\DB\DB;
// 使用 默认 default 配置
$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

// 使用 多库 test 配置
$res = DB::connection('test')->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```
## 读写分离

读写分离配置方式如下：

```php
<?php

return [
    'default' => [
        'driver' => 'mysql',
        'read' => [
            'host' => ['192.168.1.1'],
        ],
        'write' => [
            'host' => ['192.168.1.2'],
        ],
        'sticky'    => true,
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'hyperf'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'fetch_mode' => PDO::FETCH_ASSOC,
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

配置数组中增加了三个键，分别是 read， write 和 sticky。 read 和 write 的键都包含一个键为 host 的数组。而 read 和 write 的其他数据库都在键为 mysql 的数组中。

如果你想重写主数组中的配置，只需要修改 read 和 write 数组即可。所以，这个例子中： 192.168.1.1 将作为 「读」 连接主机，而 192.168.1.2 将作为 「写」 连接主机。这两个连接会共享 mysql 数组的各项配置，如数据库的凭据（用户名 / 密码），前缀，字符编码等。

sticky 是一个 可选值，它可用于立即读取在当前请求周期（当前协程周期）内已写入数据库的记录。若 sticky 选项被启用，并且当前请求周期内执行过 「写」 操作，那么任何 「读」 操作都将使用 「写」 连接。这样可确保同一个请求周期内写入的数据可以被立即读取到，从而避免主从延迟导致数据不一致的问题。不过是否启用它，取决于应用程序的需求。

注意：当前请求周期为长生命周期时（例如自定义进程中 while(true) 内调用），一旦发生过写操作，则后续所有读写都将使用「写」连接。