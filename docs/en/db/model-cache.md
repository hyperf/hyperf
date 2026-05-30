# Model Cache

In high-concurrency business scenarios, we may frequently query the database to obtain business data. Although primary key indexes help, it inevitably puts a tremendous strain on database performance. For this type of key-value (KV) query pattern, we can easily mitigate database pressure by using the `Model Cache`. This component implements automatic caching for Model data, and automatically deletes or updates the corresponding cache when model data is deleted or updated. When performing increment or decrement operations, the cached data is also automatically updated accordingly.

> The model cache currently only supports the `Redis` storage driver. Contributions for other storage engines are welcome.

## Installation

```bash
composer require hyperf/model-cache
```

## Configuration

The configuration for the model cache is stored in `config/autoload/databases.php` by default. The configuration properties are as follows:

| Configuration | Type | Default Value | Remarks |
|:-----------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
| handler | string | Hyperf\ModelCache\Handler\RedisHandler::class | N/A |
| cache_key | string | `mc:%s:m:%s:%s:%s` | `mc:cache_prefix:m:table_name:primary_key:value` |
| prefix | string | db connection name | Cache prefix |
| pool | string | default | Cache pool |
| ttl | int | 3600 | Timeout duration |
| empty_model_ttl | int | 60 | Timeout duration when no data is found |
| load_script | bool | true | Whether to use evalSha instead of eval for Redis engine |
| use_default_value | bool | false | Whether to use database default values |

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

## Usage

Using model cache is very simple. You just need to implement the `Hyperf\ModelCache\CacheableInterface` interface in the corresponding Model. The framework already provides a corresponding implementation; you only need to introduce the `Hyperf\ModelCache\Cacheable` Trait.

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

// Query single cache
/** @var int|string $id */
$model = User::findFromCache($id);

// Batch query cache, returns Hyperf\Database\Model\Collection
/** @var array $ids */
$models = User::findManyFromCache($ids);
```

The corresponding Redis data is as follows. `HF-DATA:DEFAULT` exists as a placeholder in the `HASH`, *so users should not use `HF-DATA` as a database field name*.

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

Another point to note is the cache update mechanism. The framework implements a corresponding `Hyperf\ModelCache\Listener\DeleteCacheListener` listener. Whenever data is modified, the framework will actively delete the corresponding cached data.
If you do not want the framework to automatically delete the corresponding cache, you can proactively override the `deleteCache` method of the Model and implement the corresponding listening logic yourself.

### Batch Modification or Deletion

`Hyperf\ModelCache\Cacheable` will automatically take over the `Model::query` method. As long as you delete data in the following way, the corresponding cached data will be automatically cleaned up.

```php
<?php
// Delete user data from the database, the framework will automatically delete the corresponding cached data
User::query(true)->where('gender', '>', 1)->delete();
```

### Using Default Values

When model cache is used in a production environment, if the corresponding cached data has already been created, but due to logical changes, a new field is added, and the default value is not a data type like `0`, `empty string`, or `null`, this may lead to inconsistencies between the data queried from the cache and the data in the database.

In this case, we can set `use_default_value` to `true` and add `Hyperf\DbConnection\Listener\InitTableCollectorListener` to the `listener.php` configuration. This will cause the Hyperf application to proactively fetch database field information upon startup, compare it when retrieving cached data, and correct the cached data if necessary.

### Controlling Cache Time in Model

In addition to the default `ttl` configured in `database.php`, `Hyperf\ModelCache\Cacheable` supports a finer-grained cache time configuration for models:

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * Cache for 10 minutes. If null is returned, the timeout duration set in the configuration file is used.
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

When we use model relationships, we can solve the `N+1` problem by using `load`, but it still requires a database query. By rewriting `ModelBuilder`, Model Cache allows users to retrieve corresponding models from the cache as much as possible.

> This feature does not support `morphTo` and relationship models that do not only use `whereIn` queries.

Two ways are provided below:

1. Configure `EagerLoadListener` and use the `loadCache` method directly.

Modify the `listeners.php` configuration:

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

Load the corresponding model relationships via the `loadCache` method:

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. Use `EagerLoader`

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

### Cache Adapter

You can implement a cache adapter according to your actual situation by implementing the `Hyperf\ModelCache\Handler\HandlerInterface` interface.

The framework provides two Handlers to choose from:

- `Hyperf\ModelCache\Handler\RedisHandler`

Uses `HASH` to store the cache, which can effectively handle `Model::increment()`. The disadvantage is that because the data type is only `String`, the support for `null` is poor.

- `Hyperf\ModelCache\Handler\RedisStringHandler`

Uses `String` to store the cache. Because it is serialized data, it supports all data types. The disadvantage is that it cannot effectively handle `Model::increment()`. When the model calls increment, it solves the consistency problem by deleting the cache.
