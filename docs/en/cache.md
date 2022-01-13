# Cache

[https://github.com/hyperf/cache](https://github.com/hyperf/cache)

## Config

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\Cache\Driver\RedisDriver::class,
        'packer' => Hyperf\Cache\Packer\PhpSerializer::class,
    ],
];
```

## How to use

Components provide Cacheable annotation to configure cache prefix, expiration times, listeners, and cache groups.
For example, UserService provides a user method to query user information. When the Cacheable annotation is added, the Redis cache is automatically generated with the key value of `user:id` and the timeout time of 9000 seconds. When querying for the first time, it will be fetched from DB, and when querying later, it will be fetched from Cache.

```php
<?php

namespace App\Services;

use App\Models\User;
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    #[Cacheable(key: "user", ttl: 9000, listener: "user-update")]
    public function user($id)
    {
        $user =  User::query()->where('id',$id)->first();

        if($user){
            return $user->toArray();
        }

        return null;
    }
}
```

## Clear Cache

Of course, if the data changes, how to delete the cache? Here we need to use the listener. Next, a new Service provides a way to help us deal with it.

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
