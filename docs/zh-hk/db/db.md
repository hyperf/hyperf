# 極簡的 DB 組件

[hyperf/database](https://github.com/hyperf/database) 功能十分強大，但也不可否認效率上確實些許不足。這裏提供一個極簡的 `hyperf/db` 組件，支持 `PDO` 和 `Swoole Mysql`。

## 安裝

```bash
composer require hyperf/db
```

## 發佈組件配置

該組件的配置文件位於 `config/autoload/db.php`，如果文件不存在，可通過下面的命令來將配置文件發佈到骨架去：

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## 組件配置

默認配置 `config/autoload/db.php` 如下，數據庫支持多庫配置，默認為 `default`。

|        配置項        |  類型  |       默認值       |               備註               |
|:--------------------:|:------:|:------------------:|:--------------------------------:|
|        driver        | string |         無         | 數據庫引擎 支持 `pdo` 和 `mysql` |
|         host         | string |    `localhost`     |            數據庫地址            |
|         port         |  int   |        3306        |            數據庫地址            |
|       database       | string |         無         |          數據庫默認 DB           |
|       username       | string |         無         |           數據庫用户名           |
|       password       | string |        null        |            數據庫密碼            |
|       charset        | string |        utf8        |            數據庫編碼            |
|      collation       | string |  utf8_unicode_ci   |            數據庫編碼            |
|      fetch_mode      |  int   | `PDO::FETCH_ASSOC` |        PDO 查詢結果集類型        |
| pool.min_connections |  int   |         1          |        連接池內最少連接數        |
| pool.max_connections |  int   |         10         |        連接池內最大連接數        |
| pool.connect_timeout | float  |        10.0        |         連接等待超時時間         |
|  pool.wait_timeout   | float  |        3.0         |             超時時間             |
|    pool.heartbeat    |  int   |         -1         |               心跳               |
|  pool.max_idle_time  | float  |        60.0        |           最大閒置時間           |
|       options        | array  |                    |             PDO 配置             |

## 組件支持的方法

具體接口可以查看 `Hyperf\DB\ConnectionInterface`。

|      方法名      |   返回值類型   |                  備註                   |
|:----------------:|:--------------:|:------------------------------------:|
| beginTransaction |     `void`     |          開啓事務 支持事務嵌套          |
|      commit      |     `void`     |          提交事務 支持事務嵌套          |
|     rollBack     |     `void`     |          回滾事務 支持事務嵌套          |
|      insert      |     `int`      | 插入數據，返回主鍵 ID，非自增主鍵返回 0   |
|     execute      |     `int`      |       執行 SQL，返回受影響的行數        |
|      query       |    `array`     |        查詢 SQL，返回結果集列表         |
|      fetch       | `array, object`|     查詢 SQL，返回結果集的首行數據       |
|      connection  |     `self`     |           指定連接的數據庫             |

## 使用

### 使用 DB 實例

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### 使用靜態方法

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### 使用匿名函數自定義方法

> 此種方式可以允許用户直接操作底層的 `PDO` 或者 `MySQL`，所以需要自己處理兼容問題

比如我們想執行某些查詢，使用不同的 `fetch mode`，則可以通過以下方式，自定義自己的方法

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
