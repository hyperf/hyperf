# 模型緩存

在高頻場景下，我們會頻繁的查詢數據庫，雖然有主鍵加持，但也會影響到數據庫性能。這種 kv 查詢方式，我們可以很方便的使用 `模型緩存` 來減緩數據庫壓力。本模塊實現了自動緩存，刪除和修改模型時，自動刪除緩存。累加、減操作時，直接操作緩存進行對應累加、減。

> 模型緩存暫支持 `Redis`存儲，其他存儲引擎會慢慢補充。

## 安裝

```
composer require hyperf/model-cache
```

## 配置

模型緩存的配置在 `databases` 中。示例如下

|      配置       |  類型  |                    默認值                     |                  備註                   |
|:---------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
|     handler     | string | Hyperf\ModelCache\Handler\RedisHandler::class |                   無                    |
|    cache_key    | string |              `mc:%s:m:%s:%s:%s`               |  `mc:緩存前綴:m:表名:主鍵 KEY:主鍵值`   |
|     prefix      | string |              db connection name               |                緩存前綴                 |
|       ttl       |  int   |                     3600                      |                超時時間                 |
| empty_model_ttl |  int   |                      60                       |        查詢不到數據時的超時時間         |
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
$model = User::findFromCache($id);

// 批量查詢緩存，返回 Hyperf\Database\Model\Collection
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

另外一點就是，緩存更新機制，框架內實現了對應的 `Hyperf\ModelCache\Listener\DeleteCacheListener` 監聽器，每當數據修改，會主動刪除緩存。
如果用户不想由框架來刪除緩存，可以主動覆寫 `deleteCache` 方法，然後由自己實現對應監聽即可。

### 批量修改或刪除

`Hyperf\ModelCache\Cacheable` 會自動接管 `Model::query` 方法，只需要用户通過以下方式修改數據，就可以自動清理緩存。

```php
<?php
// 刪除用户數據 並自動刪除緩存
User::query(true)->where('gender', '>', 1)->delete();
```
