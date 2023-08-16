# Model cache

In high-frequency scenarios, we will frequently query the database. Although there is a primary key blessing, it will also affect the database performance. With this kv query method, we can easily use `model cache` to reduce database pressure. This module implements automatic caching. When deleting and modifying the model, the cache is automatically deleted. When accumulating and subtracting, directly operate the cache to perform the corresponding accumulation and subtraction.

> Model cache temporarily supports `Redis` storage, other storage engines will be added gradually.

## Installation

```
composer require hyperf/model-cache
```

## configure

Model caching is configured in `databases`. Examples are as follows

|  Configuration  |  Type  |                         Default                        |                Remarks                                           |
|:---------------:|:------:|:------------------------------------------------------:|:----------------------------------------------------------------:|
| handler         | string | Hyperf\DbConnection\Cache\Handler\RedisHandler::class  |                               none                               |
| cache_key       | string |                   `mc:%s:m:%s:%s:%s`                   | `mc:cache prefix:m:table name:primary key KEY:primary key value` |
| prefix          | string |                   db connection name                   |                           cache prefix                           |
| ttl             |  int   |                          3600                          |                              timeout                             |
| empty_model_ttl |  int   |                           60                           |                  Timeout when no data is queried                 |
| load_script     |  bool  |                          true                          |   Whether to use evalSha instead of eval under the Redis engine  |

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

## use

The use of the model cache is very simple. You only need to implement the `Hyperf\ModelCache\CacheableInterface` interface in the corresponding Model. Of course, the framework has already provided the corresponding implementation, you only need to introduce the `Hyperf\ModelCache\Cacheable` Trait.

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

The corresponding Redis data is as follows, where `HF-DATA:DEFAULT` exists as a placeholder in `HASH`, *so users do not use `HF-DATA` as a database field*.
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

Another point is that the cache update mechanism implements the corresponding `Hyperf\ModelCache\Listener\DeleteCacheListener` listener in the framework. Whenever the data is modified, the cache will be actively deleted.
If the user does not want the framework to delete the cache, he can actively override the `deleteCache` method, and then implement the corresponding monitoring by yourself.