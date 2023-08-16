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
|:----------------:|:--------------:|:------------------------------------:|
| beginTransaction |     `void`     |          开启事务 支持事务嵌套          |
|      commit      |     `void`     |          提交事务 支持事务嵌套          |
|     rollBack     |     `void`     |          回滚事务 支持事务嵌套          |
|      insert      |     `int`      | 插入数据，返回主键 ID，非自增主键返回 0   |
|     execute      |     `int`      |       执行 SQL，返回受影响的行数        |
|      query       |    `array`     |        查询 SQL，返回结果集列表         |
|      fetch       | `array, object`|     查询 SQL，返回结果集的首行数据       |
|      connection  |     `self`     |           指定连接的数据库             |

## 使用

### 使用 DB 实例

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### 使用静态方法

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### 使用匿名函数自定义方法

> 此种方式可以允许用户直接操作底层的 `PDO` 或者 `MySQL`，所以需要自己处理兼容问题

比如我们想执行某些查询，使用不同的 `fetch mode`，则可以通过以下方式，自定义自己的方法

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
