# 模型缓存

模型缓存暂支持 `Redis`存储，其他存储引擎会慢慢补充。

## 安装

```
composer require hyperf/model-cache
```

## 配置

模型缓存的配置在 `databases` 中。示例如下

|    配置     |  类型  |                         默认值                         |                备注                 |
|:-----------:|:------:|:------------------------------------------------------:|:-----------------------------------:|
|   handler   | string | \Hyperf\DbConnection\Cache\Handler\RedisHandler::class |                 无                  |
|  cache_key  | string |                  `mc:%s:m:%s:%s:%s`                   | `mc:缓存前缀:m:表名:主键KEY:主键值`  |
|   prefix    | string |                   db connection name                   |              缓存前缀               |
|     ttl     |  int   |                          3600                          |              超时时间               |
| load_script |  bool  |                          true                          | Redis引擎下 是否使用evalSha代替eval |

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
            'load_script' => true,
        ]
    ],
];
```

## 使用

模型缓存的使用十分简单，只需要在对应Model中实现 `Hyperf\ModelCache\CacheableInterface` 接口，当然，框架已经提供了对应实现，只需要引入 `Hyperf\ModelCache\Cacheable` Trait 即可。

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

$model = User::findFromCache($id);
$models = User::findManyFromCache($ids);

```

对应Redis数据如下，其中 `HF-DATA:DEFAULT` 作为占位符存在于 `HASH` 中，*所以用户不要使用 `HF-DATA` 作为数据库字段*。
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
