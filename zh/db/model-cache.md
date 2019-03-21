# 模型缓存

模型缓存暂支持Redis存储，其他存储引擎会慢慢补充。

## 安装
```
composer require hyperf/model-cache
```

## 使用

模型缓存的使用十分简单，只需要在对应Model中实现`Hyperf\ModelCache\CacheableInterface`接口，当然，框架已经提供了对应实现，只需要引入Trait`Hyperf\ModelCache\Cacheable`即可。

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property $id
 * @property $name
 * @property $sex
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
    protected $fillable = ['id', 'name', 'sex', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'sex' => 'integer'];
}

$model = User::findFromCache($id);
$models = User::findManyFromCache($ids);

```

对应Redis数据如下，其中HF-DATA:DEFAULT作为占位符存在于HASH中，*所以用户不要使用HF-DATA作为数据库字段*。
```
127.0.0.1:6379> hgetall "mc:default:m:user:id:1"
 1) "id"
 2) "1"
 3) "name"
 4) "Hyperf"
 5) "sex"
 6) "1"
 7) "created_at"
 8) "2018-01-01 00:00:00"
 9) "updated_at"
10) "2018-01-01 00:00:00"
11) "HF-DATA"
12) "DEFAULT"
```

另外一点就是，缓存更新机制，框架内实现了对应的`Hyperf\ModelCache\Listener\DeleteCacheListener`监听器，每当数据修改，会主动删除缓存。
如果用户不想由框架来删除缓存，可以主动覆写`deleteCache`方法，然后由自己实现对应监听即可。
