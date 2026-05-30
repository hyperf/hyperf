# Pool

## Instalasi

```bash
composer require hyperf/pool
```

## Mengapa pool dibutuhkan?

Ketika jumlah konkurensi sangat rendah, koneksi dapat dibuat secara
sementara. Namun, ketika throughput layanan mencapai skala ratusan atau ribuan,
proses `Connect` and `Close` yang sering dapat menjadi bottleneck bagi layanan.
Secara praktis, saat layanan dimulai, beberapa koneksi dapat dibuat dan disimpan
dalam sebuah antrean. Saat dibutuhkan, satu koneksi diambil dari antrean dan
digunakan, lalu dikembalikan ke antrean setelah selesai digunakan. Struktur data
dari antrean ini dikelola oleh connection pool.

## Penggunaan

Untuk komponen-komponen yang disediakan oleh Hyperf, connection pool telah
diadaptasikan. Pengguna tidak perlu menyadarinya saat menggunakan. Hyperf secara
otomatis menyelesaikan pengambilan dan pengembalian koneksi.

## Custom connection pool

Untuk mendefinisikan sebuah connection pool, Anda terlebih dahulu harus
mengimplementasikan subclass yang mewarisi `Hyperf\Pool\Pool` dan
mengimplementasikan abstract method `createConnection`, serta mengembalikan objek
yang mengimplementasikan interface `Hyperf\Contract\ConnectionInterface`. Contoh
demonstrasinya adalah sebagai berikut:

```php
<?php
namespace App\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;

class MyConnectionPool extends Pool
{
    public function createConnection(): ConnectionInterface
    {
        return new MyConnection();
    }
}
```

Dengan cara ini, koneksi dapat diambil dan dikembalikan dengan memanggil method
`get(): ConnectionInterface` dan `release(ConnectionInterface $connection): void`
pada objek instansiasi `MyConnectionPool`.

## SimplePool

Implementasi pool sederhana telah disediakan oleh Hyperf.

```php
<?php

use Hyperf\Pool\SimplePool\PoolFactory;
use Swoole\Coroutine\Http\Client;

$factory = $container->get(PoolFactory::class);

$pool = $factory->get('your pool name', function () use ($host, $port, $ssl) {
    return new Client($host, $port, $ssl);
}, [
    'max_connections' => 50
]);

$connection = $pool->get();

$client = $connection->getConnection(); // The Client which mentioned above.

// Do something.

$connection->release();

```

## Low-frequency Interface

Pool memiliki interface bawaan `LowFrequencyInterface`. Komponen frekuensi rendah
(low-frequency) digunakan secara default, dan menentukan apakah akan melepaskan
koneksi berlebih di dalam pool berdasarkan frekuensi pengambilan koneksi dari
pool.

Jika kita perlu mengganti komponen low-frequency yang sesuai, Anda dapat langsung
menggantinya pada konfigurasi `dependencies`. Mengambil komponen database sebagai
contoh:

```php
<?php

declare(strict_types=1);

namespace App\Pool;

class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * The time interval of the calculated frequency
     * @var int
     */
    protected $time = 10;

    /**
     * Threshold
     * @var int
     */
    protected $lowFrequency = 5;

    /**
     * Minimum time interval for continuous low frequency triggering
     * @var int
     */
    protected $lowFrequencyInterval = 60;
}

```

Ubah pemetaan (mapping) sebagai berikut:

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### Frekuensi konstan (Constant frequency)

Hyperf juga menyediakan komponen low-frequency lainnya, yaitu
`ConstantFrequency`.

Ketika komponen ini diinstansiasi, timer akan dijalankan dan method
`Pool::flushOne(false)` akan dipanggil secara berkala. Method ini akan mengambil
koneksi dari pool dan koneksi tersebut akan dihancurkan jika method tersebut
menilai koneksi telah menganggur (idle) lebih dari jangka waktu tertentu.

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
