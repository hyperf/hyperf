# 模型緩存

在高頻的業務場景下，我們可能會頻繁的查詢數據庫獲取業務數據，雖然有主鍵索引的加持，但也不可避免的對數據庫性能造成了極大的考驗。而對於這種 kv 的查詢方式，我們可以很方便的通過使用 `模型緩存` 來減緩數據庫的壓力。本組件實現了 Model 數據自動緩存的功能，且當刪除和修改模型數據時，自動刪除和修改對應的緩存。執行累加、累減操作時，緩存數據自動進行對應累加、累減變更。

> 模型緩存暫時只支持 `Redis` 存儲驅動，其他存儲引擎歡迎社區提交對應的實現。

## 安裝

```bash
composer require hyperf/model-cache
```

## 配置

模型緩存的配置默認存放在 `config/autoload/databases.php` 中。配置的屬性如下：

|       配置        |  類型  |                    默認值                     |                  備註                   |
|:-----------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
|      handler      | string | Hyperf\ModelCache\Handler\RedisHandler::class |                   無                    |
|     cache_key     | string |              `mc:%s:m:%s:%s:%s`               |  `mc:緩存前綴:m:表名:主鍵 KEY:主鍵值`   |
|      prefix       | string |              db connection name               |                緩存前綴                 |
|       pool        | string |                    default                    |                 緩存池                  |
|        ttl        |  int   |                     3600                      |                超時時間                 |
|  empty_model_ttl  |  int   |                      60                       |        查詢不到數據時的超時時間         |
|    load_script    |  bool  |                     true                      | Redis 引擎下 是否使用 evalSha 代替 eval |
| use_default_value |  bool  |                     false                     |          是否使用數據庫默認值           |

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

模型緩存的使用十分簡單，只需要在對應 Model 中實現 `Hyperf\ModelCache\CacheableInterface` 接口，當然，框架已經提供了對應實現，只需要引入 `Hyperf\ModelCache\Cacheable` Trait 即可。

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

// 查詢單個緩存
/** @var int|string $id */
$model = User::findFromCache($id);

// 批量查詢緩存，返回 Hyperf\Database\Model\Collection
/** @var array $ids */
$models = User::findManyFromCache($ids);

```

對應 Redis 數據如下，其中 `HF-DATA:DEFAULT` 作為佔位符存在於 `HASH` 中，*所以用户不要使用 `HF-DATA` 作為數據庫字段*。

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

另外一點需要注意的就是，緩存的更新機制，框架內實現了對應的 `Hyperf\ModelCache\Listener\DeleteCacheListener` 監聽器，每當數據修改時，框架會主動刪除對應的緩存數據。
如果您不希望由框架來自動刪除對應的緩存，可以通過主動覆寫 Model 的 `deleteCache` 方法，然後自行實現對應監聽即可。

### 批量修改或刪除

`Hyperf\ModelCache\Cacheable` 會自動接管 `Model::query` 方法，只需要用户通過以下方式進行數據的刪除，就可以自動清理對應的緩存數據。

```php
<?php
// 從數據庫刪除用户數據，框架會自動刪除對應的緩存數據
User::query(true)->where('gender', '>', 1)->delete();
```

### 使用默認值

當生產環境使用了模型緩存時，如果已經建立了對應緩存數據，但此時又因為邏輯變更，添加了新的字段，並且默認值不是 `0`、`空字符`、`null` 這類數據時，就會導致在數據查詢時，從緩存中查出來的數據與數據庫中的數據不一致。

對於這種情況，我們可以修改 `use_default_value` 為 `true`，並添加 `Hyperf\DbConnection\Listener\InitTableCollectorListener` 到 `listener.php` 配置中，使 Hyperf 應用在啓動時主動去獲取數據庫的字段信息，並在獲取緩存數據時與之比較並進行緩存數據修正。

### 控制模型中緩存時間

除了 `database.php` 中配置的默認緩存時間 `ttl` 外，`Hyperf\ModelCache\Cacheable` 支持對模型配置更細的緩存時間：

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * 緩存 10 分鐘，返回 null 則使用配置文件中設置的超時時間
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

當我們使用模型關係時，可以通過 `load` 解決 `N+1` 的問題，但仍然需要查一次數據庫。模型緩存通過重寫了 `ModelBuilder`，可以讓用户儘可能的從緩存中拿到對應的模型。

> 本功能不支持 `morphTo` 和不是隻有 `whereIn` 查詢的關係模型。

以下提供兩種方式：

1. 配置 EagerLoadListener，直接使用 `loadCache` 方法。

修改 `listeners.php` 配置

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

通過 `loadCache` 方法，加載對應的模型關係。

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

### 緩存適配器

您可以根據自己的實際情況實現緩存適配器，只需要實現接口 `Hyperf\ModelCache\Handler\HandlerInterface` 即可。

框架提供了兩個 Handler 可供選擇：

- Hyperf\ModelCache\Handler\RedisHandler

使用 `HASH` 存儲緩存，可以有效的處理 `Model::increment()`，不足是因為數據類型只有 `String`，所以對 `null` 支持較差。

- Hyperf\ModelCache\Handler\RedisStringHandler

使用 `String` 存儲緩存，因為是序列化的數據，所以支持所有數據類型，不足是無法有效處理 `Model::increment()`，當模型調用累加時，通過刪除緩存，解決一致性的問題。
