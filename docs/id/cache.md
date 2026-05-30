# Cache

[hyperf/cache](https://github.com/hyperf/cache) menyediakan aspect cache berdasarkan implementasi `Aspect`, dan juga menyediakan class cache yang mengimplementasikan `Psr\SimpleCache\CacheInterface`.

## Instalasi

```
composer require hyperf/cache
```

## Konfigurasi Default

| Konfigurasi | Nilai Default | Keterangan |
|:------:|:----------------------------------------:|:---------------------:|
| driver | Hyperf\Cache\Driver\RedisDriver | Cache driver, default adalah Redis |
| packer | Hyperf\Codec\Packer\PhpSerializerPacker | Packager |
| prefix | c: | Cache prefix |
| skip_cache_results | [] | Hasil tertentu tidak disimpan di cache |

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

### Metode Simple Cache

Simple Cache adalah spesifikasi [PSR-16](https://www.php-fig.org/psr/psr-16/).
Komponen ini menyesuaikan dengan spesifikasi tersebut. Jika Anda ingin
menggunakan class cache `Psr\SimpleCache\CacheInterface`, misalnya jika Anda
ingin menulis ulang modul cache milik `EasyWeChat`, Anda dapat mendapatkan
`Psr\SimpleCache\CacheInterface` secara langsung dari dependency injection
container, seperti yang ditunjukkan di bawah ini:

```php

$cache = $container->get(\Psr\SimpleCache\CacheInterface::class);

```

### Metode Annotation

Komponen ini menyediakan annotation `Hyperf\Cache\Annotation\Cacheable`, yang
bekerja pada method class dan dapat mengonfigurasi cache prefix, expiration
time, listener, serta cache group yang sesuai.

Sebagai contoh, UserService menyediakan method user yang dapat melakukan query
informasi user berdasarkan id. Ketika annotation
`Hyperf\Cache\Annotation\Cacheable` ditambahkan, Redis cache yang sesuai akan
secara otomatis dibuat. Key cache tersebut bernilai `user:id` dan timeout
sebesar `9000` detik. Saat melakukan query untuk pertama kalinya, data akan
diambil dari database, dan untuk query berikutnya, data akan diambil dari
cache.

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

### Membersihkan cache yang dibuat oleh `#[Cacheable]`

Kami menyediakan dua annotation, `CachePut` dan `CacheEvict`, untuk
mengimplementasikan operasi update cache dan pembersihan cache.

Tentu saja, kita juga dapat menghapus cache melalui event. Mari buat sebuah
Service baru untuk menyediakan method yang membantu kita menangani caching.

> Namun, kami menyarankan pengguna untuk menggunakan pemrosesan annotation
> alih-alih listener.

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

Ketika kita melakukan kustomisasi pada `value` dari `Cacheable`, seperti situasi
berikut.

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

Anda perlu menyesuaikan variabel `$arguments` di dalam constructor
`DeleteListenerEvent`. Kode lengkapnya adalah sebagai berikut.

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

Sebagai contoh, pada konfigurasi di bawah ini, cache prefix adalah `user`,
timeout adalah `7200`, dan nama event penghapusan adalah `USER_CACHE`. Cache
KEY yang sesuai akan dibuat sebagai `c:user:1`.

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

Ketika `value` diatur, framework akan memberi nama key `KEY` cache sesuai
dengan aturan yang telah ditentukan. Pada contoh berikut, ketika `$user->id = 1`,
`KEY` cache-nya adalah `c:userBook:_1`

> Konfigurasi ini juga mendukung jenis annotation cache lainnya yang
> dijelaskan di bawah ini

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

Sebagai contoh, pada konfigurasi di bawah ini, cache prefix adalah `user`,
timeout adalah `7200`, cache KEY yang dihasilkan adalah `c:user:1`, dan cache
akan diinisialisasi setiap 10 detik dari detik ke-7200 hingga detik ke-600
sebelum kedaluwarsa hingga berhasil untuk pertama kalinya.

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

`CachePut` berbeda dengan `Cacheable` karena ia mengeksekusi body method
setiap kali dipanggil dan kemudian menulis ulang cache. Jadi, ketika kita
ingin memperbarui cache, kita dapat memanggil method yang bersangkutan.

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

`CacheEvict` lebih mudah dipahami. Ketika body method dijalankan, cache akan
dibersihkan secara aktif.

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

## Cache driver

### Redis driver

`Hyperf\Cache\Driver\RedisDriver` akan menyimpan data cache di `Redis`, dan
pengguna perlu mengonfigurasi `Redis configuration` yang sesuai. Mode ini
merupakan mode default.

### Process memory driver

Jika Anda perlu menyimpan data cache ke dalam memori, Anda dapat mencoba
driver ini. Konfigurasinya adalah sebagai berikut:

```php
<?php

return [
    'memory' => [
        'driver' => Hyperf\Cache\Driver\MemoryDriver::class,
    ],
];
```

### Coroutine memory driver

Jika Anda perlu menyimpan data cache ke dalam `Context`, Anda dapat mencoba
driver ini. Sebagai contoh, pada skenario aplikasi di bawah ini, `Demo::get`
akan dipanggil berkali-kali di beberapa tempat, tetapi Anda tidak ingin
melakukan query ke `Redis` setiap saat.

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
