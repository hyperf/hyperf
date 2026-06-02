# Cache

[hyperf/cache](https://github.com/hyperf/cache) nyediain caching berbasis aspek yang diimplementasiin pake `Aspect`, dan juga nyediain cache class yang implementasiin `Psr\SimpleCache\CacheInterface`.

## Instalasi

```
composer require hyperf/cache
```

## Konfigurasi Default

| Konfigurasi | Nilai Default | Keterangan |
|:------:|:----------------------------------------:|:---------------------:|
| driver | Hyperf\Cache\Driver\RedisDriver | Cache driver, defaultnya Redis |
| packer | Hyperf\Codec\Packer\PhpSerializerPacker | Packer |
| prefix | c: | Cache prefix |
| skip_cache_results | [] | Hasil yang ditentukan tidak di-cache |

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

## Penggunaan

### Simple Cache Mode

Simple Cache ngacu ke spesifikasi [PSR-16](https://www.php-fig.org/psr/psr-16/). Komponen ini ngikutin spesifikasi itu. Kalo mau pake cache class yang implementasiin `Psr\SimpleCache\CacheInterface`, misalnya buat ganti modul cache `EasyWeChat`, tinggal ambil `Psr\SimpleCache\CacheInterface` dari dependency injection container:

```php

$cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

```

### Mode Annotation

Komponen nyediain annotation `Hyperf\Cache\Annotation\Cacheable` yang dipasang di method class, bisa ngatur prefix cache, waktu kedaluwarsa, listener, dan cache group.
Misalnya, `UserService` punya method `user` buat nanyain info user berdasarkan `id`. Abis ditambahin annotation `Hyperf\Cache\Annotation\Cacheable`, cache Redis bakal otomatis tergenerate, dengan `key` `user:id` dan timeout `9000` detik. Pertama kali query, ambil dari database; query selanjutnya, ambil dari cache.

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

### Menghapus Cache yang Dihasilkan oleh `#[Cacheable]`

Ada dua annotation: `CachePut` buat update cache, dan `CacheEvict` buat hapus cache.

Tentu aja, kita juga bisa hapus cache lewat events. Di bawah ini, bikin `Service` baru dan sediah method buat nanganin cache.

> Tapi kami saranin pake annotation daripada listener.

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

Ketika kita menyesuaikan nilai `value` dari `Cacheable`, misalnya dalam situasi berikut.

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

Maka Anda perlu mengubah variabel `$arguments` di constructor `DeleteListenerEvent` sesuai dengan itu. Kode spesifiknya adalah sebagai berikut.

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

## Pengenalan Annotation

### Cacheable

Misalnya, dalam konfigurasi berikut, cache prefix adalah `user`, timeout adalah `7200`, dan nama deletion event adalah `USER_CACHE`. Cache `KEY` yang dihasilkan adalah `c:user:1`.

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

Setelah menyetel `value`, framework akan menamai cache `KEY` sesuai dengan aturan yang ditetapkan. Seperti dalam contoh berikut, ketika `$user->id = 1`, cache `KEY` adalah `c:userBook:_1`

> Konfigurasi ini juga mendukung tipe cache annotation lain yang disebutkan di bawah ini.

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

Misalnya, dalam konfigurasi berikut, cache prefix adalah `user`, timeout adalah `7200`, dan cache `KEY` yang dihasilkan adalah `c:user:1`. Dan antara 7200 - 600 detik, inisialisasi cache dilakukan setiap 10 detik sampai berhasil untuk pertama kalinya.

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

`CachePut` beda sama `Cacheable`, ia bakal ngejalanin badan fungsi setiap kali dipanggil, lalu numpuk cache. Jadi kalo mau update cache, tinggal panggil method yang relevan.

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

CacheEvict lebih simpel: abis ngejalanin badan method, cache langsung dihapus.

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

## Cache Driver

### Redis Driver

`Hyperf\Cache\Driver\RedisDriver` nyimpen data cache di `Redis`, dan Anda perlu konfigurasi `Redis` dulu. Ini mode default.

### Process Memory Driver

Kalo perlu nyimpen data di memory, bisa pake driver ini.

Konfigurasinya adalah sebagai berikut:

```php
<?php

return [
    'memory' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
    ],
];
```

### Coroutine Memory Driver

Kalo perlu nyimpen data di `Context`, bisa pake driver ini. Misalnya, di skenario berikut, `Demo::get` bakal dipanggil berkali-kali di banyak tempat, tapi Anda gak mau query ke `Redis` tiap kali.

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

Konfigurasi yang sesuai adalah sebagai berikut:

```php
<?php

return [
    'co' => [
        'driver' => Hyperf\Cache\Driver\CoroutineMemoryDriver::class,
        'packer' => Hyperf\Codec\Packer\PhpSerializerPacker::class,
    ],
];
```
