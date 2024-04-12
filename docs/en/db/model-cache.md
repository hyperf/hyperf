# Model cache

In high-frequency scenarios, we will frequently query the database. Although there is a primary key blessing, it will also affect the database performance. With this kv query method, we can easily use `model cache` to reduce database pressure. This module implements automatic caching. When deleting and modifying the model, the cache is automatically deleted. When accumulating and subtracting, directly operate the cache to perform the corresponding accumulation and subtraction.

> Model cache temporarily supports `Redis` storage, other storage engines will be added gradually.

## Installation

```bash
composer require hyperf/model-cache
```

## configure

Model caching is configured in `databases`. Examples are as follows

|   Configuration   |  Type  |                         Default                        |                Remarks                                           |
|:-----------------:|:------:|:------------------------------------------------------:|:----------------------------------------------------------------:|
| handler           | string | Hyperf\DbConnection\Cache\Handler\RedisHandler::class  |                               none                               |
| cache_key         | string |                   `mc:%s:m:%s:%s:%s`                   | `mc:cache prefix:m:table name:primary key KEY:primary key value` |
| prefix            | string |                   db connection name                   |                           cache prefix                           |
| pool              | string |                        default                         |                           cache pool                             |
| ttl               |  int   |                          3600                          |                              timeout                             |
| empty_model_ttl   |  int   |                           60                           |                  Timeout when no data is queried                 |
| load_script       |  bool  |                          true                          |   Whether to use evalSha instead of eval under the Redis engine  |
| use_default_value |  bool  |                          false                         |              Whether to use database default values              |

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
            'use_default_value' => false,
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

### Edit or delete in bulk

`Hyperf\ModelCache\Cacheable` will automatically take over the `Model::query` method. The user only needs to delete the data in the following ways to automatically clear the corresponding cached data.

```php
<?php
// Delete user data from the database and the framework will automatically delete the corresponding cached data.
User::query(true)->where('gender', '>', 1)->delete();
```

### Use default value

When the model cache is used in the production environment, if the corresponding cache data has been established, but at this time new fields are added due to logical changes, and the default values ​​are not `0`, `null character`, `null` and other such data When the data is queried, the data retrieved from the cache will be inconsistent with the data in the database.

For this situation, we can modify `use_default_value` to `true` and add `Hyperf\DbConnection\Listener\InitTableCollectorListener` to the `listener.php` configuration so that the Hyperf application can actively obtain the field information of the database when it starts. And compare it with the cached data when obtaining it and correct the cached data.

### Control cache time in models

In addition to the default cache time `ttl` configured in `database.php`, `Hyperf\ModelCache\Cacheable` supports configuring more detailed cache time for the model:

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * Cache for 10 minutes. If null is returned, the timeout set in the configuration file will be used.
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

When we use model relationships, we can solve the `N+1` problem through `load`, but we still need to check the database once. The model cache rewrites `ModelBuilder` to allow users to get the corresponding model from the cache as much as possible.

> This feature does not support `morphTo` and relational models that do not have only `whereIn` queries.

Two methods are provided below:

1. Configure EagerLoadListener and use the `loadCache` method directly.

Modify `listeners.php` configuration

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

Load the corresponding model relationship through the `loadCache` method.

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. Use EagerLoader

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

### Cache adapter

You can implement the cache adapter according to your actual situation, and you only need to implement the interface `Hyperf\ModelCache\Handler\HandlerInterface`.

The framework provides two Handlers to choose from:

- Hyperf\ModelCache\Handler\RedisHandler

Using `HASH` to store cache can effectively handle `Model::increment()`. The disadvantage is that because the data type is only `String`, it has poor support for `null`.

- Hyperf\ModelCache\Handler\RedisStringHandler

Use `String` to store the cache. Because it is serialized data, it supports all data types. The disadvantage is that it cannot effectively handle `Model::increment()`. When the model calls accumulation, the consistency problem is solved by deleting the cache.