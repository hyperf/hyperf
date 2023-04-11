# 模型缓存

在高频的业务场景下，我们可能会频繁的查询数据库获取业务数据，虽然有主键索引的加持，但也不可避免的对数据库性能造成了极大的考验。而对于这种 kv 的查询方式，我们可以很方便的通过使用 `模型缓存` 来减缓数据库的压力。本组件实现了 Model 数据自动缓存的功能，且当删除和修改模型数据时，自动删除和修改对应的缓存。执行累加、累减操作时，缓存数据自动进行对应累加、累减变更。

> 模型缓存暂时只支持 `Redis` 存储驱动，其他存储引擎欢迎社区提交对应的实现。

## 安装

```bash
composer require hyperf/model-cache
```

## 配置

模型缓存的配置默认存放在 `config/autoload/databases.php` 中。配置的属性如下：

|       配置        |  类型  |                    默认值                     |                  备注                   |
|:-----------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
|      handler      | string | Hyperf\ModelCache\Handler\RedisHandler::class |                   无                    |
|     cache_key     | string |              `mc:%s:m:%s:%s:%s`               |  `mc:缓存前缀:m:表名:主键 KEY:主键值`   |
|      prefix       | string |              db connection name               |                缓存前缀                 |
|       pool        | string |                    default                    |                 缓存池                  |
|        ttl        |  int   |                     3600                      |                超时时间                 |
|  empty_model_ttl  |  int   |                      60                       |        查询不到数据时的超时时间         |
|    load_script    |  bool  |                     true                      | Redis 引擎下 是否使用 evalSha 代替 eval |
| use_default_value |  bool  |                     false                     |          是否使用数据库默认值           |

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

模型缓存的使用十分简单，只需要在对应 Model 中实现 `Hyperf\ModelCache\CacheableInterface` 接口，当然，框架已经提供了对应实现，只需要引入 `Hyperf\ModelCache\Cacheable` Trait 即可。

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

// 查询单个缓存
/** @var int|string $id */
$model = User::findFromCache($id);

// 批量查询缓存，返回 Hyperf\Database\Model\Collection
/** @var array $ids */
$models = User::findManyFromCache($ids);

```

对应 Redis 数据如下，其中 `HF-DATA:DEFAULT` 作为占位符存在于 `HASH` 中，*所以用户不要使用 `HF-DATA` 作为数据库字段*。

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

另外一点需要注意的就是，缓存的更新机制，框架内实现了对应的 `Hyperf\ModelCache\Listener\DeleteCacheListener` 监听器，每当数据修改时，框架会主动删除对应的缓存数据。
如果您不希望由框架来自动删除对应的缓存，可以通过主动覆写 Model 的 `deleteCache` 方法，然后自行实现对应监听即可。

### 批量修改或删除

`Hyperf\ModelCache\Cacheable` 会自动接管 `Model::query` 方法，只需要用户通过以下方式进行数据的删除，就可以自动清理对应的缓存数据。

```php
<?php
// 从数据库删除用户数据，框架会自动删除对应的缓存数据
User::query(true)->where('gender', '>', 1)->delete();
```

### 使用默认值

当生产环境使用了模型缓存时，如果已经建立了对应缓存数据，但此时又因为逻辑变更，添加了新的字段，并且默认值不是 `0`、`空字符`、`null` 这类数据时，就会导致在数据查询时，从缓存中查出来的数据与数据库中的数据不一致。

对于这种情况，我们可以修改 `use_default_value` 为 `true`，并添加 `Hyperf\DbConnection\Listener\InitTableCollectorListener` 到 `listener.php` 配置中，使 Hyperf 应用在启动时主动去获取数据库的字段信息，并在获取缓存数据时与之比较并进行缓存数据修正。

### 控制模型中缓存时间

除了 `database.php` 中配置的默认缓存时间 `ttl` 外，`Hyperf\ModelCache\Cacheable` 支持对模型配置更细的缓存时间：

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * 缓存 10 分钟，返回 null 则使用配置文件中设置的超时时间
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

当我们使用模型关系时，可以通过 `load` 解决 `N+1` 的问题，但仍然需要查一次数据库。模型缓存通过重写了 `ModelBuilder`，可以让用户尽可能的从缓存中拿到对应的模型。

> 本功能不支持 `morphTo` 和不是只有 `whereIn` 查询的关系模型。

以下提供两种方式：

1. 配置 EagerLoadListener，直接使用 `loadCache` 方法。

修改 `listeners.php` 配置

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

通过 `loadCache` 方法，加载对应的模型关系。

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

### 缓存适配器

您可以根据自己的实际情况实现缓存适配器，只需要实现接口 `Hyperf\ModelCache\Handler\HandlerInterface` 即可。

框架提供了两个 Handler 可供选择：

- Hyperf\ModelCache\Handler\RedisHandler

使用 `HASH` 存储缓存，可以有效的处理 `Model::increment()`，不足是因为数据类型只有 `String`，所以对 `null` 支持较差。

- Hyperf\ModelCache\Handler\RedisStringHandler

使用 `String` 存储缓存，因为是序列化的数据，所以支持所有数据类型，不足是无法有效处理 `Model::increment()`，当模型调用累加时，通过删除缓存，解决一致性的问题。
