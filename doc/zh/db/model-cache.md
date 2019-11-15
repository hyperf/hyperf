# 模型缓存

在高频场景下，我们会频繁的查询数据库，虽然有主键加持，但也会影响到数据库性能。这种 kv 查询方式，我们可以很方便的使用 `模型缓存` 来减缓数据库压力。本模块实现了自动缓存，删除和修改模型时，自动删除缓存。累加、减操作时，直接操作缓存进行对应累加、减。

> 模型缓存暂支持 `Redis`存储，其他存储引擎会慢慢补充。

## 安装

```
composer require hyperf/model-cache
```

## 配置

模型缓存的配置在 `databases` 中。示例如下

|      配置       |  类型  |                        默认值                         |                备注                 |
|:---------------:|:------:|:-----------------------------------------------------:|:-----------------------------------:|
|     handler     | string | Hyperf\ModelCache\Handler\RedisHandler::class |                 无                  |
|    cache_key    | string |                  `mc:%s:m:%s:%s:%s`                   | `mc:缓存前缀:m:表名:主键 KEY:主键值` |
|     prefix      | string |                  db connection name                   |              缓存前缀               |
|       ttl       |  int   |                         3600                          |              超时时间               |
| empty_model_ttl |  int   |                          60                           |      查询不到数据时的超时时间       |
|   load_script   |  bool  |                         true                          | Redis 引擎下 是否使用 evalSha 代替 eval |

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
$model = User::findFromCache($id);

// 批量查询缓存，返回 Hyperf\Database\Model\Collection
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

另外一点就是，缓存更新机制，框架内实现了对应的 `Hyperf\ModelCache\Listener\DeleteCacheListener` 监听器，每当数据修改，会主动删除缓存。
如果用户不想由框架来删除缓存，可以主动覆写 `deleteCache` 方法，然后由自己实现对应监听即可。

### 批量修改或删除

`Hyperf\ModelCache\Cacheable` 会自动接管 `Model::query` 方法，只需要用户通过以下方式修改数据，就可以自动清理缓存。

```php
<?php
// 删除用户数据 并自动删除缓存
User::query(true)->where('gender', '>', 1)->delete();
```
