# 快速開始

## 前言

> [hyperf/database](https://github.com/hyperf/database) 衍生於 [illuminate/database](https://github.com/illuminate/database)，我們對它進行了一些改造，大部分功能保持了相同。在這裏感謝一下 Laravel 開發組，實現瞭如此強大好用的 ORM 組件。

[hyperf/database](https://github.com/hyperf/database) 組件是基於 [illuminate/database](https://github.com/illuminate/database) 衍生出來的組件，我們對它進行了一些改造，從設計上是允許用於其它 PHP-FPM 框架或基於 Swoole 的框架中的，而在 Hyperf 裏就需要提一下 [hyperf/db-connection](https://github.com/hyperf/db-connection) 組件，它基於 [hyperf/pool](https://github.com/hyperf/pool) 實現了數據庫連接池並對模型進行了新的抽象，以它作為橋樑，Hyperf 才能把數據庫組件及事件組件接入進來。

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

默認配置如下，數據庫支持多庫配置，默認為 `default`。

|        配置項        |  類型  |     默認值      |        備註        |
| :------------------: | :----: | :-------------: | :----------------: |
|        driver        | string |       無        |     數據庫引擎     |
|         host         | string |       無        |     數據庫地址     |
|       database       | string |       無        |   數據庫默認 DB    |
|       username       | string |       無        |    數據庫用户名    |
|       password       | string |      null       |     數據庫密碼     |
|       charset        | string |      utf8       |     數據庫編碼     |
|      collation       | string | utf8_unicode_ci |     數據庫編碼     |
|        prefix        | string |       ''        |   數據庫模型前綴   |
|       timezone       | string |      null       |     數據庫時區     |
| pool.min_connections |  int   |        1        | 連接池內最少連接數 |
| pool.max_connections |  int   |       10        | 連接池內最大連接數 |
| pool.connect_timeout | float  |      10.0       |  連接等待超時時間  |
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

有時候用户需要修改 PDO 默認配置，比如所有字段需要返回為 string。這時候就需要修改 PDO 配置項 `ATTR_STRINGIFY_FETCHES` 為 true。

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
            // 框架默認配置
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // 如果使用的為非原生 MySQL 或雲廠商提供的 DB 如從庫/分析型實例等不支持 MySQL prepare 協議的, 將此項設置為 true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### 讀寫分離

有時候你希望 `SELECT` 語句使用一個數據庫連接，而 `INSERT`，`UPDATE`，和 `DELETE` 語句使用另一個數據庫連接。在 `Hyperf` 中，無論你是使用原生查詢，查詢構造器，或者是模型，都能輕鬆的實現

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

注意在以上的例子中，配置數組中增加了三個鍵，分別是 `read`， `write` 和 `sticky`。 `read` 和 `write` 的鍵都包含一個鍵為 `host` 的數組。而 `read` 和 `write` 的其他數據庫都在鍵為 mysql 的數組中。

如果你想重寫主數組中的配置，只需要修改 `read` 和 `write` 數組即可。所以，這個例子中： 192.168.1.1 將作為 「讀」 連接主機，而 192.168.1.2 將作為 「寫」 連接主機。這兩個連接會共享 mysql 數組的各項配置，如數據庫的憑據（用户名 / 密碼），前綴，字符編碼等。

`sticky` 是一個 可選值，它可用於立即讀取在當前請求週期內已寫入數據庫的記錄。若 `sticky` 選項被啓用，並且當前請求週期內執行過 「寫」 操作，那麼任何 「讀」 操作都將使用 「寫」 連接。這樣可確保同一個請求週期內寫入的數據可以被立即讀取到，從而避免主從延遲導致數據不一致的問題。不過是否啓用它，取決於應用程序的需求。

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

模型中修改 `connection` 字段，即可使用對應配置，例如一下 `Model` 使用 `test` 配置。

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

配置好數據庫後，便可以使用 `Hyperf\DbConnection\Db` 進行查詢。

### Query 查詢類

這裏主要包括 `Select`、屬性為 `READS SQL DATA` 的存儲過程、函數等查詢語句。   

`select` 方法將始終返回一個數組，數組中的每個結果都是一個 `StdClass` 對象

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]);  //  返回array 

foreach($users as $user){
    echo $user->name;
}

```

### Execute 執行類

這裏主要包括 `Insert`、`Update`、`Delete`，屬性為 `MODIFIES SQL DATA` 的存儲過程等執行語句。

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1, 'Hyperf']); // 返回是否成功 bool

$affected = Db::update('UPDATE user set name = ? WHERE id = ?', ['John', 1]); // 返回受影響的行數 int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // 返回受影響的行數 int

$result = Db::statement("CALL pro_test(?, '?')", [1, 'your words']);  // 返回 bool  CALL pro_test(?，?) 為存儲過程，屬性為 MODIFIES SQL DATA
```

### 自動管理數據庫事務

你可以使用 `Db` 的 `transaction` 方法在數據庫事務中運行一組操作。如果事務的閉包 `Closure` 中出現一個異常，事務將會回滾。如果事務閉包 `Closure` 執行成功，事務將自動提交。一旦你使用了 `transaction` ， 就不再需要擔心手動回滾或提交的問題：

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### 手動管理數據庫事務

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

> 當前方法僅能用於開發環境，線上部署前一定要去掉，不然會引起嚴重的內存泄露和數據混淆。

線上記錄 `SQL`，請使用 [事件監聽](/zh-hk/db/event)

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// 啓用 SQL 數據記錄功能
Db::enableQueryLog();

$book = Book::query()->find(1);

// 打印最後一條 SQL 相關數據
var_dump(Arr::last(Db::getQueryLog()));
```
