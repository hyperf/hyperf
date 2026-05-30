# Model Cache

Dalam skenario frekuensi tinggi, kita akan sering melakukan kueri ke database.
Meskipun ada keunggulan primary key, hal tersebut juga akan mempengaruhi performa
database. Dengan metode kueri kv ini, kita dapat dengan mudah menggunakan
`model cache` untuk mengurangi tekanan pada database. Modul ini
mengimplementasikan caching otomatis. Saat menghapus dan mengubah model, cache
akan dihapus secara otomatis. Saat melakukan akumulasi (penjumlahan dan
pengurangan), operasi langsung dilakukan pada cache untuk melakukan akumulasi
yang sesuai.

> Model cache saat ini hanya mendukung penyimpanan `Redis`, mesin penyimpanan
> lain akan ditambahkan secara bertahap.

## Instalasi

```bash
composer require hyperf/model-cache
```

## Konfigurasi

Model caching dikonfigurasi di dalam `databases`. Contohnya adalah sebagai berikut:

| Konfigurasi | Tipe | Default | Keterangan |
|:---:|:---:|:---:|:---:|
| handler | string | Hyperf\DbConnection\Cache\Handler\RedisHandler::class | tidak ada |
| cache_key | string | `mc:%s:m:%s:%s:%s` | `mc:prefix cache:m:nama tabel:KEY primary key:nilai primary key` |
| prefix | string | nama koneksi db | prefix cache |
| pool | string | default | pool cache |
| ttl | int | 3600 | timeout |
| empty_model_ttl | int | 60 | Timeout saat tidak ada data yang ditemukan |
| load_script | bool | true | Apakah menggunakan evalSha alih-alih eval pada engine Redis |
| use_default_value | bool | false | Apakah menggunakan nilai default database |

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
            'empty_model_ttl' => 3600,
            'load_script' => true,
            'use_default_value' => false,
        ]
    ],
];
```

## Penggunaan

Penggunaan model cache sangatlah sederhana. Anda hanya perlu mengimplementasikan
interface `Hyperf\ModelCache\CacheableInterface` pada Model yang bersangkutan.
Tentu saja, framework telah menyediakan implementasinya, Anda hanya perlu
menggunakan Trait `Hyperf\ModelCache\Cacheable`.

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

// Query a single cache
$model = User::findFromCache($id);

// Batch query cache, return Hyperf\Database\Model\Collection
$models = User::findManyFromCache($ids);

```

Data Redis yang sesuai adalah sebagai berikut, di mana `HF-DATA:DEFAULT` ada
sebagai placeholder di dalam `HASH`, *sehingga pengguna tidak boleh menggunakan
`HF-DATA` sebagai nama kolom database*.
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

Hal lain yang perlu diperhatikan adalah mekanisme pembaruan cache
mengimplementasikan listener `Hyperf\ModelCache\Listener\DeleteCacheListener`
yang sesuai di dalam framework. Setiap kali data diubah, cache akan dihapus
secara aktif. Jika pengguna tidak ingin framework menghapus cache, ia dapat
secara aktif melakukan override pada metode `deleteCache`, kemudian
mengimplementasikan monitoring yang sesuai sendiri.

### Mengedit atau menghapus secara massal

`Hyperf\ModelCache\Cacheable` akan secara otomatis mengambil alih metode
`Model::query`. Pengguna hanya perlu menghapus data dengan cara berikut untuk
secara otomatis menghapus data cache yang sesuai.

```php
<?php
// Delete user data from the database and the framework will automatically delete the corresponding cached data.
User::query(true)->where('gender', '>', 1)->delete();
```

### Menggunakan nilai default

Ketika model cache digunakan di lingkungan production, jika data cache yang
sesuai telah dibuat, namun pada saat ini kolom baru ditambahkan karena perubahan
logika, dan nilai default-nya bukan `0`, `string kosong`, `null` atau data sejenis
lainnya, maka ketika data dikueri, data yang diambil dari cache akan tidak
konsisten dengan data yang ada di database.

Untuk mengatasi situasi ini, kita dapat mengubah nilai `use_default_value`
menjadi `true` dan menambahkan `Hyperf\DbConnection\Listener\InitTableCollectorListener`
ke konfigurasi `listener.php` agar aplikasi Hyperf dapat secara aktif
memperoleh informasi kolom dari database saat dijalankan, lalu membandingkannya
dengan data cache saat diambil dan mengoreksi data cache tersebut.

### Mengatur durasi cache pada model

Selain durasi cache default `ttl` yang dikonfigurasi di `database.php`,
`Hyperf\ModelCache\Cacheable` mendukung konfigurasi durasi cache yang lebih
detail untuk model:

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * Cache for 10 minutes. If null is returned, the timeout set in the configuration file will be used.
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

Ketika kita menggunakan relasi model, kita dapat menyelesaikan masalah `N+1`
melalui `load`, tetapi kita masih perlu memeriksa database satu kali. Model
cache menulis ulang `ModelBuilder` untuk memungkinkan pengguna mendapatkan model
yang sesuai dari cache sebanyak mungkin.

> Fitur ini tidak mendapat dukungan untuk relasi `morphTo` dan model relasi yang
> tidak hanya menggunakan kueri `whereIn`.

Dua metode disediakan di bawah ini:

1. Konfigurasikan EagerLoadListener dan gunakan metode `loadCache` secara langsung.

Ubah konfigurasi `listeners.php`

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

Muat relasi model yang sesuai melalui metode `loadCache`.

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. Menggunakan EagerLoader

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

### Adapter cache

Anda dapat mengimplementasikan adapter cache sesuai dengan situasi aktual Anda,
dan Anda hanya perlu mengimplementasikan interface
`Hyperf\ModelCache\Handler\HandlerInterface`.

Framework menyediakan dua Handler yang dapat dipilih:

- Hyperf\ModelCache\Handler\RedisHandler

Menggunakan `HASH` untuk menyimpan cache dapat menangani `Model::increment()`
secara efektif. Kekurangannya adalah karena tipe datanya hanya berupa `String`,
dukungan terhadap `null` kurang baik.

- Hyperf\ModelCache\Handler\RedisStringHandler

Menggunakan `String` untuk menyimpan cache. Karena merupakan data yang
diserialisasi, ia mendukung semua tipe data. Kekurangannya adalah tidak dapat
menangani `Model::increment()` secara efektif. Ketika model memanggil akumulasi,
masalah konsistensi diselesaikan dengan menghapus cache.
