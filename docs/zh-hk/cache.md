# Cache

[hyperf/cache](https://github.com/hyperf/cache) 提供了基於 `Aspect` 實現的切面緩存，也提供了實現 `Psr\SimpleCache\CacheInterface` 的緩存類。

## 安裝
```
composer require hyperf/cache
```

## 默認配置

|  配置  |                  默認值                  |         備註          |
|:------:|:----------------------------------------:|:---------------------:|
| driver |  Hyperf\Cache\Driver\RedisDriver  | 緩存驅動，默認為 Redis |
| packer | Hyperf\Codec\Packer\PhpSerializerPacker |        打包器         |
| prefix |                   c:                   |       緩存前綴        |
| skip_cache_results |       []                   |       指定的結果不被緩存   |

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

Simple Cache 也就是 [PSR-16](https://www.php-fig.org/psr/psr-16/) 規範，本組件適配了該規範，如果您希望使用實現 `Psr\SimpleCache\CacheInterface` 緩存類，比如要重寫 `EasyWeChat` 的緩存模塊，可以直接從依賴注入容器中獲取 `Psr\SimpleCache\CacheInterface` 即可，如下所示：

```php

$cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

```

### 註解方式

組件提供 `Hyperf\Cache\Annotation\Cacheable` 註解，作用於類方法，可以配置對應的緩存前綴、失效時間、監聽器和緩存組。
例如，UserService 提供一個 user 方法，可以查詢對應 id 的用户信息。當加上 `Hyperf\Cache\Annotation\Cacheable` 註解後，會自動生成對應的 Redis 緩存，key 值為 `user:id` ，超時時間為 `9000` 秒。首次查詢時，會從數據庫中查，後面查詢時，會從緩存中查。

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

### 清理 `#[Cacheable]` 生成的緩存

我們提供了 `CachePut` 和 `CacheEvict` 兩個註解，來實現更新緩存和清除緩存操作。

當然，我們也可以通過事件來刪除緩存。下面新建一個 Service 提供一方法，來幫我們處理緩存。

> 不過我們更加推薦用户使用註解處理，而非監聽器

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

當我們自定義了 `Cacheable` 的 `value` 時，比如以下情況。

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

則需要對應修改 `DeleteListenerEvent` 構造函數中的 `$arguments` 變量，具體代碼如下。

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

## 註解介紹

### Cacheable

例如以下配置，緩存前綴為 `user`, 超時時間為 `7200`, 刪除事件名為 `USER_CACHE`。生成對應緩存 KEY 為 `c:user:1`。

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

當設置 `value` 後，框架會根據設置的規則，進行緩存 `KEY` 鍵命名。如下實例，當 `$user->id = 1` 時，緩存 `KEY` 為 `c:userBook:_1`

> 此配置也同樣支持下述其他類型緩存註解

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

例如以下配置，緩存前綴為 `user`, 超時時間為 `7200`, 生成對應緩存 KEY 為 `c:user:1`，並且在 7200 - 600 秒的時候，每 10 秒進行一次緩存初始化，直到首次成功。

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

`CachePut` 不同於 `Cacheable`，它每次調用都會執行函數體，然後再對緩存進行重寫。所以當我們想更新緩存時，可以調用相關方法。

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

CacheEvict 更容易理解了，當執行方法體後，會主動清理緩存。

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

## 緩存驅動

### Redis 驅動

`Hyperf\Cache\Driver\RedisDriver` 會把緩存數據存放到 `Redis` 中，需要用户配置相應的 `Redis 配置`。此方式為默認方式。

### 進程內存驅動

如果您需要將數據緩存到內存中，可以嘗試此驅動。

配置如下：

```php
<?php

return [
    'memory' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
    ],
];
```

### 協程內存驅動

如果您需要將數據緩存到 `Context` 中，可以嘗試此驅動。例如以下應用場景 `Demo::get` 會在多個地方調用多次，但是又不想每次都到 `Redis` 中進行查詢。

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

對應配置如下：

```php
<?php

return [
    'co' => [
        'driver' => Hyperf\Cache\Driver\CoroutineMemoryDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
    ],
];
```
