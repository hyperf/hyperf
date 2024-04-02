# Cache

[hyperf/cache](https://github.com/hyperf/cache) 提供了基于 `Aspect` 实现的切面缓存，也提供了实现 `Psr\SimpleCache\CacheInterface` 的缓存类。

## 安装
```
composer require hyperf/cache
```

## 默认配置

|  配置  |                  默认值                  |         备注          |
|:------:|:----------------------------------------:|:---------------------:|
| driver |  Hyperf\Cache\Driver\RedisDriver  | 缓存驱动，默认为 Redis |
| packer | Hyperf\Codec\Packer\PhpSerializerPacker |        打包器         |
| prefix |                   c:                   |       缓存前缀        |
| skip_cache_results |       []                   |       指定的结果不被缓存   |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
        'prefix' => 'c:',
        'skip_cache_results' => [],
    ],
];
```

## 使用

### Simple Cache 方式

Simple Cache 也就是 [PSR-16](https://www.php-fig.org/psr/psr-16/) 规范，本组件适配了该规范，如果您希望使用实现 `Psr\SimpleCache\CacheInterface` 缓存类，比如要重写 `EasyWeChat` 的缓存模块，可以直接从依赖注入容器中获取 `Psr\SimpleCache\CacheInterface` 即可，如下所示：

```php

$cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

```

### 注解方式

组件提供 `Hyperf\Cache\Annotation\Cacheable` 注解，作用于类方法，可以配置对应的缓存前缀、失效时间、监听器和缓存组。
例如，UserService 提供一个 user 方法，可以查询对应 id 的用户信息。当加上 `Hyperf\Cache\Annotation\Cacheable` 注解后，会自动生成对应的 Redis 缓存，key 值为 `user:id` ，超时时间为 `9000` 秒。首次查询时，会从数据库中查，后面查询时，会从缓存中查。

```php
<?php

namespace App\Services;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    #[Cacheable(prefix: "user", ttl: 9000, listener: "user-update")]
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

### 清理 `#[Cacheable]` 生成的缓存

我们提供了 `CachePut` 和 `CacheEvict` 两个注解，来实现更新缓存和清除缓存操作。

当然，我们也可以通过事件来删除缓存。下面新建一个 Service 提供一方法，来帮我们处理缓存。

> 不过我们更加推荐用户使用注解处理，而非监听器

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class SystemService
{
    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    public function flushCache($userId)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', [$userId]));

        return true;
    }
}
```

当我们自定义了 `Cacheable` 的 `value` 时，比如以下情况。

```php
<?php

declare(strict_types=1);

namespace App\Service\Cache;

use Hyperf\Cache\Annotation\Cacheable;

class DemoService
{

    #[Cacheable(prefix: "cache", value: "_#{id}", listener: "user-update")]
    public function getCache(int $id)
    {
        return $id . '_' . uniqid();
    }
}
```

则需要对应修改 `DeleteListenerEvent` 构造函数中的 `$arguments` 变量，具体代码如下。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Cache\Listener\DeleteListenerEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

class SystemService
{
    #[Inject]
    protected EventDispatcherInterface $dispatcher;

    public function flushCache($userId)
    {
        $this->dispatcher->dispatch(new DeleteListenerEvent('user-update', ['id' => $userId]));

        return true;
    }
}
```

## 注解介绍

### Cacheable

例如以下配置，缓存前缀为 `user`, 超时时间为 `7200`, 删除事件名为 `USER_CACHE`。生成对应缓存 KEY 为 `c:user:1`。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    #[Cacheable(prefix: "user", ttl: 7200, listener: "USER_CACHE")]
    public function user(int $id): array
    {
        $user = User::query()->find($id);

        return [
            'user' => $user->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

当设置 `value` 后，框架会根据设置的规则，进行缓存 `KEY` 键命名。如下实例，当 `$user->id = 1` 时，缓存 `KEY` 为 `c:userBook:_1`

> 此配置也同样支持下述其他类型缓存注解

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserBookService
{
    #[Cacheable(prefix: "userBook", ttl: 6666, value: "_#{user.id}")]
    public function userBook(User $user): array
    {
        return [
            'book' => $user->book->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

### CacheAhead

例如以下配置，缓存前缀为 `user`, 超时时间为 `7200`, 生成对应缓存 KEY 为 `c:user:1`，并且在 7200 - 600 秒的时候，每 10 秒进行一次缓存初始化，直到首次成功。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\CacheAhead;

class UserService
{
    #[CacheAhead(prefix: "user", ttl: 7200, aheadSeconds: 600, lockSeconds: 10)]
    public function user(int $id): array
    {
        $user = User::query()->find($id);

        return [
            'user' => $user->toArray(),
            'uuid' => $this->unique(),
        ];
    }
}
```

### CachePut

`CachePut` 不同于 `Cacheable`，它每次调用都会执行函数体，然后再对缓存进行重写。所以当我们想更新缓存时，可以调用相关方法。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\User;
use Hyperf\Cache\Annotation\CachePut;

class UserService
{
    #[CachePut(prefix: "user", ttl: 3601)]
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
}
```

### CacheEvict

CacheEvict 更容易理解了，当执行方法体后，会主动清理缓存。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\Cache\Annotation\CacheEvict;

class UserBookService
{
    #[CacheEvict(prefix: "userBook", value: "_#{id}")]
    public function updateUserBook(int $id)
    {
        return true;
    }
}
```

## 缓存驱动

### Redis 驱动

`Hyperf\Cache\Driver\RedisDriver` 会把缓存数据存放到 `Redis` 中，需要用户配置相应的 `Redis 配置`。此方式为默认方式。

### 进程内存驱动

如果您需要将数据缓存到内存中，可以尝试此驱动。

配置如下：

```php
<?php

return [
    'memory' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
    ],
];
```

### 协程内存驱动

如果您需要将数据缓存到 `Context` 中，可以尝试此驱动。例如以下应用场景 `Demo::get` 会在多个地方调用多次，但是又不想每次都到 `Redis` 中进行查询。

```php
<?php
use Hyperf\Cache\Annotation\Cacheable;

class Demo
{    
    public function get($userId, $id)
    {
        return $this->getArray($userId)[$id] ?? 0;
    }

    #[Cacheable(prefix: "test", group: "co")]
    public function getArray(int $userId): array
    {
        return $this->redis->hGetAll($userId);
    }
}
```

对应配置如下：

```php
<?php

return [
    'co' => [
        'driver' => Hyperf\Cache\Driver\CoroutineMemoryDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
    ],
];
```
