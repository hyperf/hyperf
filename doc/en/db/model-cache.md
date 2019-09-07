# Model cache

In the high-frequency scenario, we will frequently query the database. Although there is a primary key blessing, it will also affect the database performance. This kind of kv query method, we can easily use `model cache` to slow down database pressure. This module implements automatic caching, automatically deleting the cache when deleting and modifying the model. When accumulating or subtracting operations, directly operate the cache to perform corresponding accumulation and subtraction.

> The model cache temporarily supports `Redis` storage, and other storage engines will slowly add it.

## Install

```
composer require hyperf/model-cache
```

## Configuration

The model cache is configured in `databases`. Examples are as follows

|     config      |  type  |                     default value                     |                            remark                            |
| :-------------: | :----: | :---------------------------------------------------: | :----------------------------------------------------------: |
|     handler     | string | Hyperf\DbConnection\Cache\Handler\RedisHandler::class |                             null                             |
|    cache_key    | string |                  `mc:%s:m:%s:%s:%s`                   | `mc:cache prefix:m:table name:primary key:primary key value` |
|     prefix      | string |                  db connection name                   |                         Cache prefix                         |
|       ttl       |  int   |                         3600                          |                         expire date                          |
| empty_model_ttl |  int   |                          60                           |               Timeout when no data is queried                |
|   load_script   |  bool  |                         true                          |     Under the Redis engine, use evalSha instead of eval.     |

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
            'handler' => \Hyperf\DbConnection\Cache\Handler\RedisHandler::class,
            'cache_key' => 'mc:%s:m:%s:%s:%s',
            'prefix' => 'default',
            'ttl' => 3600 * 24,
            'empty_model_ttl' => 3600,
            'load_script' => true,
        ]
    ],
];
```

## Use

The use of the model cache is very simple, you only need to implement the `Hyperf\ModelCache\CacheableInterface` interface in the corresponding Model. Of course, the framework already provides the corresponding implementation, just introduce `Hyperf\ModelCache\Cacheable` Trait.

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

// Query a single cache
$model = User::findFromCache($id);

// Batch query cache, return Hyperf\Database\Model\Collection
$models = User::findManyFromCache($ids);

```

The corresponding Redis data is as follows, where `HF-DATA:DEFAULT` exists as a placeholder in `HASH`, so users should not use `HF-DATA` as the database field*.
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

Another point is that the cache update mechanism, the corresponding `Hyperf\ModelCache\Listener\DeleteCacheListener` listener is implemented in the framework, and the cache is actively deleted whenever the data is modified.
If the user does not want to delete the cache by the framework, you can override the `deleteCache` method and then implement the corresponding listener.
