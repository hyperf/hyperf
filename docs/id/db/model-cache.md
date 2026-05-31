# Model Cache

Dalam skenario bisnis dengan konkurensi tinggi, query ke database untuk mengambil data bisnis sering terjadi. Walaupun primary key index membantu, tekanan pada performa database tetaplah besar. Untuk pola query key-value (KV) seperti ini, `Model Cache` bisa meringankan beban database. Komponen ini mengimplementasikan caching otomatis untuk data Model, cache akan otomatis dihapus atau diperbarui saat data model berubah. Operasi increment/decrement juga otomatis memperbarui cache.

> Model cache saat ini hanya mendukung storage driver `Redis`. Kontribusi untuk engine lain sangat diterima.

## Installation

```bash
composer require hyperf/model-cache
```

## Configuration

Konfigurasi model cache disimpan di `config/autoload/databases.php`. Propertinya sebagai berikut:

| Konfigurasi | Type | Nilai Default | Keterangan |
|:-----------------:|:------:|:---------------------------------------------:|:---------------------------------------:|
| handler | string | Hyperf\ModelCache\Handler\RedisHandler::class | N/A |
| cache_key | string | `mc:%s:m:%s:%s:%s` | `mc:prefix:m:nama_tabel:primary_key:nilai` |
| prefix | string | nama koneksi db | Cache prefix |
| pool | string | default | Cache pool |
| ttl | int | 3600 | Durasi timeout |
| empty_model_ttl | int | 60 | Durasi timeout saat tidak ada data ditemukan |
| load_script | bool | true | Gunakan evalSha sebagai pengganti eval untuk Redis |
| use_default_value | bool | false | Gunakan nilai default dari database |

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
            'use_default_value' => false,
        ]
    ],
];
```

## Usage

Menggunakan model cache sangat mudah. Tinggal implementasikan interface `Hyperf\ModelCache\CacheableInterface` di Model. Framework sudah menyediakan implementasinya, Anda cukup gunakan Trait `Hyperf\ModelCache\Cacheable`.

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
     * Tabel yang terkait dengan model ini.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'gender', 'created_at', 'updated_at'];

    protected $casts = ['id' => 'integer', 'gender' => 'integer'];
}

// Query cache tunggal
/** @var int|string $id */
$model = User::findFromCache($id);

// Batch query cache, mengembalikan Hyperf\Database\Model\Collection
/** @var array $ids */
$models = User::findManyFromCache($ids);
```

Data Redis yang sesuai adalah sebagai berikut. `HF-DATA:DEFAULT` ada sebagai placeholder di `HASH`, *jadi pengguna tidak boleh menggunakan `HF-DATA` sebagai nama field database*.

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

Perlu diperhatikan mekanisme pembaruan cache-nya. Framework menyediakan listener `Hyperf\ModelCache\Listener\DeleteCacheListener`. Setiap kali data dimodifikasi, framework otomatis menghapus cache yang sesuai.
Jika tidak ingin otomatis, Anda bisa override method `deleteCache` di Model dan implementasi logic sendiri.

### Batch Modification atau Deletion

`Hyperf\ModelCache\Cacheable` otomatis mengambil alih method `Model::query`. Jika Anda menghapus data seperti berikut, cache akan ikut terhapus.

```php
<?php
// Hapus data user dari database, framework akan secara otomatis menghapus data cache yang sesuai
User::query(true)->where('gender', '>', 1)->delete();
```

### Menggunakan Default Values

Di production, jika cache sudah ada tapi ada field baru ditambahkan karena perubahan logic, dan nilai default-nya bukan `0`, `empty string`, atau `null`, ini bisa menyebabkan inkonsistensi antara data cache dan database.

Untuk mengatasinya, set `use_default_value` ke `true` dan tambahkan `Hyperf\DbConnection\Listener\InitTableCollectorListener` ke `listener.php`. Hyperf akan mengambil informasi field database saat startup, membandingkannya saat mengambil cache, dan memperbaiki cache jika diperlukan.

### Mengontrol Cache Time di Model

Selain `ttl` default di `database.php`, `Hyperf\ModelCache\Cacheable` mendukung konfigurasi waktu cache per model yang lebih granular:

```php
class User extends Model implements CacheableInterface
{
    use Cacheable;
    
    /**
     * Cache selama 10 menit. Jika null, akan pakai ttl dari konfigurasi.
     * @return int|null
     */
    public function getCacheTTL(): ?int
    {
        return 600;
    }
}
```

### EagerLoad

Saat menggunakan model relationships, `load` bisa menyelesaikan masalah `N+1`, tapi tetap butuh query database. Dengan rewrite `ModelBuilder`, Model Cache memungkinkan pengambilan model dari cache sebanyak mungkin.

> Fitur ini tidak mendukung `morphTo` dan relationship models yang tidak hanya menggunakan query `whereIn`.

Dua cara tersedia:

1. Konfigurasi `EagerLoadListener` dan gunakan method `loadCache`.

Ubah konfigurasi `listeners.php`:

```php
return [
    Hyperf\ModelCache\Listener\EagerLoadListener::class,
];
```

Muat model relationships yang sesuai melalui method `loadCache`:

```php
$books = Book::findManyFromCache([1,2,3]);
$books->loadCache(['user']);

foreach ($books as $book){
    var_dump($book->user);
}
```

2. Gunakan `EagerLoader`

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

### Cache Adapter

Anda bisa membuat cache adapter sendiri dengan mengimplementasikan interface `Hyperf\ModelCache\Handler\HandlerInterface`.

Framework menyediakan dua Handler untuk dipilih:

- `Hyperf\ModelCache\Handler\RedisHandler`

Menggunakan `HASH` untuk menyimpan cache, yang dapat menangani `Model::increment()` secara efektif. Kekurangannya adalah karena tipe data hanya `String`, dukungan terhadap `null` kurang baik.

- `Hyperf\ModelCache\Handler\RedisStringHandler`

Menggunakan `String` untuk menyimpan cache. Karena berupa data serialized, ini mendukung semua tipe data. Kekurangannya adalah tidak dapat menangani `Model::increment()` secara efektif. Ketika model memanggil increment, masalah konsistensi diselesaikan dengan menghapus cache.
