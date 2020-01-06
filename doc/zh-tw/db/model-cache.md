# 模型快取

在高頻場景下，我們會頻繁的查詢資料庫，雖然有主鍵加持，但也會影響到資料庫效能。這種 kv 查詢方式，我們可以很方便的使用 `模型快取` 來減緩資料庫壓力。本模組實現了自動快取，刪除和修改模型時，自動刪除快取。累加、減操作時，直接操作快取進行對應累加、減。

> 模型快取暫支援 `Redis`儲存，其他儲存引擎會慢慢補充。

## 安裝

```
composer require hyperf/model-cache
```

## 配置

模型快取的配置在 `databases` 中。示例如下

|      配置       |  型別  |                    預設值                     |                  備註                   |
|:---------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
|     handler     | string | Hyperf\ModelCache\Handler\RedisHandler::class |                   無                    |
|    cache_key    | string |              `mc:%s:m:%s:%s:%s`               |  `mc:快取字首:m:表名:主鍵 KEY:主鍵值`   |
|     prefix      | string |              db connection name               |                快取字首                 |
|       ttl       |  int   |                     3600                      |                超時時間                 |
| empty_model_ttl |  int   |                      60                       |        查詢不到資料時的超時時間         |
|   load_script   |  bool  |                     true                      | Redis 引擎下 是否使用 evalSha 代替 eval |

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
$model = User::findFromCache($id);

// 批量查詢快取，返回 Hyperf\Database\Model\Collection
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

另外一點就是，快取更新機制，框架內實現了對應的 `Hyperf\ModelCache\Listener\DeleteCacheListener` 監聽器，每當資料修改，會主動刪除快取。
如果使用者不想由框架來刪除快取，可以主動覆寫 `deleteCache` 方法，然後由自己實現對應監聽即可。

### 批量修改或刪除

`Hyperf\ModelCache\Cacheable` 會自動接管 `Model::query` 方法，只需要使用者通過以下方式修改資料，就可以自動清理快取。

```php
<?php
// 刪除使用者資料 並自動刪除快取
User::query(true)->where('gender', '>', 1)->delete();
```
