# Cache

[hyperf/cache](https://github.com/hyperf-cloud/cache) 提供了基于 `Aspect` 实现的切面缓存，也提供了实现 `Psr\SimpleCache\CacheInterface` 的缓存类。

## 安装
```
composer require hyperf/cache
```

## 默认配置

|  配置  |                  默认值                  |         备注          |
|:------:|:----------------------------------------:|:---------------------:|
| driver |  Hyperf\Cache\Driver\RedisDriver::class  | 缓存驱动，默认为Redis |
| packer | Hyperf\Utils\Packer\PhpSerializer::class |        打包器         |
| prefix |                   'c:'                   |       缓存前缀        |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Utils\Packer\PhpSerializer::class,
        'prefix' => 'c:',
    ],
];
```

## 使用

### 注解方式

组件提供 `Hyperf\Cache\Annotation\Cacheable` 注解，作用于类方法，可以配置对应的缓存前缀、失效时间、监听器和缓存组。
例如，UserService 提供一个 user 方法，可以查询对应id的用户信息。当加上 `Hyperf\Cache\Annotation\Cacheable` 注解后，会自动生成对应的Redis缓存，key值为`user:id`，超时时间为 9000 秒。首次查询时，会从数据库中查，后面查询时，会从缓存中查。

```php
<?php

namespace App\Services;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    /**
     * @Cacheable(prefix="user", ttl=9000, listener="user-update")
     */
    public function user($id)
    {
        $user = User::query()->where('id',$id)->first();

        if($user){
            return $user->toArray();
        }

        return null;
    }
}
```

### 清理注解缓存

当然，如果我们数据库中的数据改变了，如果删除缓存呢？这里就需要用到后面的监听器。下面新建一个 Service 提供一方法，来帮我们处理缓存。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class SystemService
{
    /**
     * @Inject
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function flushCache($userId)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', [$userId]));

        return true;
    }
}
```

### SimpleCache方式

如果您只想使用实现 `Psr\SimpleCache\CacheInterface` 缓存类，比如重写 `EasyWeChat` 缓存模块，可以很方便的从 `Container` 中获取相应对象。

```php

$cache = $container->get(Psr\SimpleCache\CacheInterface::class);

```

## 注解介绍

### Cacheable

例如以下配置，缓存前缀为 user, 超时时间为 7200, 删除事件名为 USER_CACHE。生成对应缓存 KEY 为 `c:user:1`。

```php
use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

/**
 * @Cacheable(prefix="user", ttl=7200, listener="USER_CACHE")
 */
public function user(int $id): array
{
    $user = User::query()->find($id);

    return [
        'user' => $user->toArray(),
        'uuid' => $this->unique(),
    ];
}
```

当设置 value 后，框架会根据设置的规则，进行缓存 KEY 键命名。如下实例，当 $user->id = 1 时，缓存 KEY 为 `c:userBook:_1`

```php
use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

/**
 * @Cacheable(prefix="userBook", ttl=6666, value="_#{user.id}")
 */
public function userBook(User $user): array
{
    return [
        'book' => $user->book->toArray(),
        'uuid' => $this->unique(),
    ];
}
```

### CachePut

CachePut 不同于 Cacheable，它每次调用都会执行函数体，然后再对缓存进行重写。所以当我们想更新缓存时，可以调用相关方法。

```php
use App\Models\User;
use Hyperf\Cache\Annotation\CachePut;

/**
 * @CachePut(prefix="user", ttl=3601)
 */
public function updateUser(int $id)
{
    $user = User::query()->find($id);
    $user->name = 'HyperfDoc';
    $user->save();

    return [
        'user' => $user->toArray(),
        'uuid' => $this->unique(),
    ];
}
```

### CacheEvict

CacheEvict 更容易理解了，当执行方法体后，会主动清理缓存。

```php
use Hyperf\Cache\Annotation\CacheEvict;

/**
 * @CacheEvict(prefix="userBook", value="_#{id}")
 */
public function updateUserBook(int $id)
{
    return true;
}
```

