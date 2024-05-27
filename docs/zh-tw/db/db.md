# 極簡的 DB 元件

[hyperf/database](https://github.com/hyperf/database) 功能十分強大，但也不可否認效率上確實些許不足。這裡提供一個極簡的 `hyperf/db` 元件。

## 安裝

```bash
composer require hyperf/db
```

## 釋出元件配置

該元件的配置檔案位於 `config/autoload/db.php`，如果檔案不存在，可透過下面的命令來將配置檔案釋出到骨架去：

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## 元件配置

預設配置 `config/autoload/db.php` 如下，資料庫支援多庫配置，預設為 `default`。

|        配置項        |  型別  |       預設值       |               備註               |
|:--------------------:|:------:|:------------------:|:--------------------------------:|
|        driver        | string |         無         | 資料庫引擎  |
|         host         | string |    `localhost`     |            資料庫地址            |
|         port         |  int   |        3306        |            資料庫地址            |
|       database       | string |         無         |          資料庫預設 DB           |
|       username       | string |         無         |           資料庫使用者名稱           |
|       password       | string |        null        |            資料庫密碼            |
|       charset        | string |        utf8        |            資料庫編碼            |
|      collation       | string |  utf8_unicode_ci   |            資料庫編碼            |
|      fetch_mode      |  int   | `PDO::FETCH_ASSOC` |        PDO 查詢結果集型別        |
| pool.min_connections |  int   |         1          |        連線池內最少連線數        |
| pool.max_connections |  int   |         10         |        連線池內最大連線數        |
| pool.connect_timeout | float  |        10.0        |         連線等待超時時間         |
|  pool.wait_timeout   | float  |        3.0         |             超時時間             |
|    pool.heartbeat    |  int   |         -1         |               心跳               |
|  pool.max_idle_time  | float  |        60.0        |           最大閒置時間           |
|       options        | array  |                    |             PDO 配置             |

## 元件支援的方法

具體介面可以檢視 `Hyperf\DB\ConnectionInterface`。

|      方法名      |   返回值型別   |                  備註                   |
|:----------------:|:--------------:|:------------------------------------:|
| beginTransaction |     `void`     |          開啟事務 支援事務巢狀          |
|      commit      |     `void`     |          提交事務 支援事務巢狀          |
|     rollBack     |     `void`     |          回滾事務 支援事務巢狀          |
|      insert      |     `int`      | 插入資料，返回主鍵 ID，非自增主鍵返回 0   |
|     execute      |     `int`      |       執行 SQL，返回受影響的行數        |
|      query       |    `array`     |        查詢 SQL，返回結果集列表         |
|      fetch       | `array, object`|     查詢 SQL，返回結果集的首行資料       |
|      connection  |     `self`     |           指定連線的資料庫             |

## 使用

### 使用 DB 例項

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

### 使用匿名函式自定義方法

> 此種方式可以允許使用者直接操作底層的 `PDO` 或者 `MySQL`，所以需要自己處理相容問題

比如我們想執行某些查詢，使用不同的 `fetch mode`，則可以透過以下方式，自定義自己的方法

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
