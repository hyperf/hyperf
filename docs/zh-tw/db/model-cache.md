# 模型快取

在高頻的業務場景下，我們可能會頻繁的查詢資料庫獲取業務資料，雖然有主鍵索引的加持，但也不可避免的對資料庫效能造成了極大的考驗。而對於這種 kv 的查詢方式，我們可以很方便的透過使用 `模型快取` 來減緩資料庫的壓力。本元件實現了 Model 資料自動快取的功能，且當刪除和修改模型資料時，自動刪除和修改對應的快取。執行累加、累減操作時，快取資料自動進行對應累加、累減變更。

> 模型快取暫時只支援 `Redis` 儲存驅動，其他儲存引擎歡迎社群提交對應的實現。

## 安裝

```bash
composer require hyperf/model-cache
```

## 配置

模型快取的配置預設存放在 `config/autoload/databases.php` 中。配置的屬性如下：

|       配置        |  型別  |                    預設值                     |                  備註                   |
|:-----------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
|      handler      | string | Hyperf\ModelCache\Handler\RedisHandler::class |                   無                    |
|     cache_key     | string |              `mc:%s:m:%s:%s:%s`               |  `mc:快取字首:m:表名:主鍵 KEY:主鍵值`   |
|      prefix       | string |              db connection name               |                快取字首                 |
|       pool        | string |                    default                    |                 快取池                  |
|        ttl        |  int   |                     3600                      |                超時時間                 |
|  empty_model_ttl  |  int   |                      60                       |        查詢不到資料時的超時時間         |
|    load_script    |  bool  |                     true                      | Redis 引擎下 是否使用 evalSha 代替 eval |
| use_default_value |  bool  |                     false                     |          是否使用資料庫預設值           |

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
            'max_idle_time' => (float)env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => \Hyperf\ModelCache\Handler\RedisHandler::class,
            'cache_key' => 'mc:%s:m:%s:%s:%s',
            'prefix' => 'default',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 3600,
            'load_script' => true,
            'use_default_value' => false,
        ]
    ],
];
```

## 使用

模型快取的使用十分簡單，只需要在對應 Model 中實現 `Hyperf\ModelCache\CacheableInterface` 介面，當然，框架已經提供了對應實現，只需要引入 `Hyperf\ModelCache\Cacheable` Trait 即可。

```php
<?php
declare(strict_types=1);

namespace App\Models;

use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property $id
 * @property $name
 * @property $gender
 * @property $created_at
 * @property $updated_at
 */
class User extends Model implements CacheableInterface
{
    use Cacheable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'gender' => 'integer'];
}

// 查詢單個快取
/** @var int|string $id */
$model = User::findFromCache($id);

// 批次查詢快取，返回 Hyperf\Database\Model\Collection
/** @var array $ids */
$models = User::findManyFromCache($ids);

```

對應 Redis 資料如下，其中 `HF-DATA:DEFAULT` 作為佔位符存在於 `HASH` 中，*所以使用者不要使用 `HF-DATA` 作為資料庫欄位*。

```
127.0.0.1:6379> hgetall "mc:default:m:user:id:1"
 1) "id"
 2) "1"
 3) "name"
 4) "Hyperf"
 5) "gender"
 6) "1"
 7) "created_at"
 8) "2018-01-01 00:00:00"
 9) "updated_at"
10) "2018-01-01 00:00:00"
11) "HF-DATA"
12) "DEFAULT"
```

另外一點需要注意的就是，快取的更新機制，框架內實現了對應的 `Hyperf\ModelCache\Listener\DeleteCacheListener` 監聽器，每當資料修改時，框架會主動刪除對應的快取資料。
如果您不希望由框架來自動刪除對應的快取，可以透過主動覆寫 Model 的 `deleteCache` 方法，然後自行實現對應監聽即可。

### 批次修改或刪除

`Hyperf\ModelCache\Cacheable` 會自動接管 `Model::query` 方法，只需要使用者透過以下方式進行資料的刪除，就可以自動清理對應的快取資料。

```php
<?php
// 從資料庫刪除使用者資料，框架會自動刪除對應的快取資料
User::query(true)->where('gender', '>', 1)->delete();
```

### 使用預設值

當生產環境使用了模型快取時，如果已經建立了對應快取資料，但此時又因為邏輯變更，添加了新的欄位，並且預設值不是 `0`、`空字元`、`null` 這類資料時，就會導致在資料查詢時，從快取中查出來的資料與資料庫中的資料不一致。

對於這種情況，我們可以修改 `use_default_value` 為 `true`，並新增 `Hyperf\DbConnection\Listener\InitTableCollectorListener` 到 `listener.php` 配置中，使 Hyperf 應用在啟動時主動去獲取資料庫的欄位資訊，並在獲取快取資料時與之比較並進行快取資料修正。

### 控制模型中快取時間

除了 `database.php` 中配置的預設快取時間 `ttl` 外，`Hyperf\ModelCache\Cacheable` 支援對模型配置更細的快取時間：

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * 快取 10 分鐘，返回 null 則使用配置檔案中設定的超時時間
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

當我們使用模型關係時，可以透過 `load` 解決 `N+1` 的問題，但仍然需要查一次資料庫。模型快取透過重寫了 `ModelBuilder`，可以讓使用者儘可能的從快取中拿到對應的模型。

> 本功能不支援 `morphTo` 和不是隻有 `whereIn` 查詢的關係模型。

以下提供兩種方式：

1. 配置 EagerLoadListener，直接使用 `loadCache` 方法。

修改 `listeners.php` 配置

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

透過 `loadCache` 方法，載入對應的模型關係。

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. 使用 EagerLoader

```php
use Hyperf\ModelCache\EagerLoad\EagerLoader;
use Hyperf\Context\ApplicationContext;

$books = Book::findManyFromCache([1,2,3]);
$loader = ApplicationContext::getContainer()->get(EagerLoader::class);
$loader->load($books, ['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

### 快取介面卡

您可以根據自己的實際情況實現快取介面卡，只需要實現介面 `Hyperf\ModelCache\Handler\HandlerInterface` 即可。

框架提供了兩個 Handler 可供選擇：

- Hyperf\ModelCache\Handler\RedisHandler

使用 `HASH` 儲存快取，可以有效的處理 `Model::increment()`，不足是因為資料型別只有 `String`，所以對 `null` 支援較差。

- Hyperf\ModelCache\Handler\RedisStringHandler

使用 `String` 儲存快取，因為是序列化的資料，所以支援所有資料型別，不足是無法有效處理 `Model::increment()`，當模型呼叫累加時，透過刪除快取，解決一致性的問題。
