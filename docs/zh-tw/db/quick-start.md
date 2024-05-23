# 快速開始

## 前言

> [hyperf/database](https://github.com/hyperf/database) 衍生於 [illuminate/database](https://github.com/illuminate/database)，我們對它進行了一些改造，大部分功能保持了相同。在這裡感謝一下 Laravel 開發組，實現瞭如此強大好用的 ORM 元件。

[hyperf/database](https://github.com/hyperf/database) 元件是基於 [illuminate/database](https://github.com/illuminate/database) 衍生出來的元件，我們對它進行了一些改造，從設計上是允許用於其它 PHP-FPM 框架或基於 Swoole 的框架中的，而在 Hyperf 裡就需要提一下 [hyperf/db-connection](https://github.com/hyperf/db-connection) 元件，它基於 [hyperf/pool](https://github.com/hyperf/pool) 實現了資料庫連線池並對模型進行了新的抽象，以它作為橋樑，Hyperf 才能把資料庫元件及事件元件接入進來。

## 安裝

### Hyperf 框架

```bash
composer require hyperf/db-connection
```

### 其它框架

```bash
composer require hyperf/database
```

## 配置

預設配置如下，資料庫支援多庫配置，預設為 `default`。

|        配置項        |  型別  |     預設值      |        備註        |
| :------------------: | :----: | :-------------: | :----------------: |
|        driver        | string |       無        |     資料庫引擎     |
|         host         | string |       無        |     資料庫地址     |
|       database       | string |       無        |   資料庫預設 DB    |
|       username       | string |       無        |    資料庫使用者名稱    |
|       password       | string |      null       |     資料庫密碼     |
|       charset        | string |      utf8       |     資料庫編碼     |
|      collation       | string | utf8_unicode_ci |     資料庫編碼     |
|        prefix        | string |       ''        |   資料庫模型字首   |
|       timezone       | string |      null       |     資料庫時區     |
| pool.min_connections |  int   |        1        | 連線池內最少連線數 |
| pool.max_connections |  int   |       10        | 連線池內最大連線數 |
| pool.connect_timeout | float  |      10.0       |  連線等待超時時間  |
|  pool.wait_timeout   | float  |       3.0       |      超時時間      |
|    pool.heartbeat    |  int   |       -1        |        心跳        |
|  pool.max_idle_time  | float  |      60.0       |    最大閒置時間    |
|       options        | array  |                 |      PDO 配置      |

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

有時候使用者需要修改 PDO 預設配置，比如所有欄位需要返回為 string。這時候就需要修改 PDO 配置項 `ATTR_STRINGIFY_FETCHES` 為 true。

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
            // 框架預設配置
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // 如果使用的為非原生 MySQL 或雲廠商提供的 DB 如從庫/分析型例項等不支援 MySQL prepare 協議的, 將此項設定為 true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### 讀寫分離

有時候你希望 `SELECT` 語句使用一個數據庫連線，而 `INSERT`，`UPDATE`，和 `DELETE` 語句使用另一個數據庫連線。在 `Hyperf` 中，無論你是使用原生查詢，查詢構造器，或者是模型，都能輕鬆的實現

為了弄明白讀寫分離是如何配置的，我們先來看個例子：

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

注意在以上的例子中，配置陣列中增加了三個鍵，分別是 `read`， `write` 和 `sticky`。 `read` 和 `write` 的鍵都包含一個鍵為 `host` 的陣列。而 `read` 和 `write` 的其他資料庫都在鍵為 mysql 的陣列中。

如果你想重寫主陣列中的配置，只需要修改 `read` 和 `write` 陣列即可。所以，這個例子中： 192.168.1.1 將作為 「讀」 連線主機，而 192.168.1.2 將作為 「寫」 連線主機。這兩個連線會共享 mysql 陣列的各項配置，如資料庫的憑據（使用者名稱 / 密碼），字首，字元編碼等。

`sticky` 是一個 可選值，它可用於立即讀取在當前請求週期內已寫入資料庫的記錄。若 `sticky` 選項被啟用，並且當前請求週期內執行過 「寫」 操作，那麼任何 「讀」 操作都將使用 「寫」 連線。這樣可確保同一個請求週期內寫入的資料可以被立即讀取到，從而避免主從延遲導致資料不一致的問題。不過是否啟用它，取決於應用程式的需求。

### 多庫配置

多庫配置如下。

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

使用時，只需要規定 `connection` 為 `test`，就可以使用 `test` 中的配置，如下。

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

模型中修改 `connection` 欄位，即可使用對應配置，例如一下 `Model` 使用 `test` 配置。

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

## 執行原生 SQL 語句

配置好資料庫後，便可以使用 `Hyperf\DbConnection\Db` 進行查詢。

### Query 查詢類

這裡主要包括 `Select`、屬性為 `READS SQL DATA` 的儲存過程、函式等查詢語句。   

`select` 方法將始終返回一個數組，陣列中的每個結果都是一個 `StdClass` 物件

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]);  //  返回array 

foreach($users as $user){
    echo $user->name;
}

```

### Execute 執行類

這裡主要包括 `Insert`、`Update`、`Delete`，屬性為 `MODIFIES SQL DATA` 的儲存過程等執行語句。

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1, 'Hyperf']); // 返回是否成功 bool

$affected = Db::update('UPDATE user set name = ? WHERE id = ?', ['John', 1]); // 返回受影響的行數 int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // 返回受影響的行數 int

$result = Db::statement("CALL pro_test(?, '?')", [1, 'your words']);  // 返回 bool  CALL pro_test(?，?) 為儲存過程，屬性為 MODIFIES SQL DATA
```

### 自動管理資料庫事務

你可以使用 `Db` 的 `transaction` 方法在資料庫事務中執行一組操作。如果事務的閉包 `Closure` 中出現一個異常，事務將會回滾。如果事務閉包 `Closure` 執行成功，事務將自動提交。一旦你使用了 `transaction` ， 就不再需要擔心手動回滾或提交的問題：

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### 手動管理資料庫事務

如果你想要手動開始一個事務，並且對回滾和提交能夠完全控制，那麼你可以使用 `Db` 的 `beginTransaction`, `commit`, `rollBack`:

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

## 輸出剛剛執行的 SQL

> 當前方法僅能用於開發環境，線上部署前一定要去掉，不然會引起嚴重的記憶體洩露和資料混淆。

線上記錄 `SQL`，請使用 [事件監聽](/zh-tw/db/event)

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// 啟用 SQL 資料記錄功能
Db::enableQueryLog();

$book = Book::query()->find(1);

// 列印最後一條 SQL 相關資料
var_dump(Arr::last(Db::getQueryLog()));
```

## 驅動列表

和 [illuminate/database](https://github.com/illuminate/database) 不同，[hyperf/database](https://github.com/hyperf/database) 預設只提供了 MySQL 驅動，目前還提供了 [PgSQL](https://github.com/hyperf/database-pgsql)、[SQLite](https://github.com/hyperf/database-sqlite)和[SQL Server](https://github.com/hyperf/database-sqlserver-incubator) 等驅動。

如果預設的 MySQL 驅動滿足不了使用需求，可以自行安裝對應的驅動：

### PgSql 驅動

#### 安裝

要求 `Swoole >= 5.1.0` 並且編譯時開啟 `--enable-swoole-pgsql`

```bash
composer require hyperf/database-pgsql
```

#### 配置檔案

```php
// config/autoload/databases.php
return [
     // 其他配置
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

### SQLite 驅動

#### 安裝

要求 `Swoole >= 5.1.0` 並且編譯時開啟 `--enable-swoole-sqlite`

```bash
composer require hyperf/database-sqlite
```

#### 配置檔案

```php
// config/autoload/databases.php
return [
     // 其他配置
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: 為記憶體資料庫 也可以指定檔案絕對路徑
        'database' => env('DB_DATABASE', ':memory:'),
        // other sqlite config
    ]
];
```

### SQL Server 驅動

#### 安裝

> 孵化階段，目前並不能保證所有功能正常，歡迎反饋問題。

要求 `Swoole >= 5.1.0` 依賴 pdo_odbc，需要編譯時開啟 `--with-swoole-odbc`

```bash
composer require hyperf/database-sqlserver-incubator
```

#### 配置檔案

```php
// config/autoload/databases.php
return [
     // 其他配置
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
