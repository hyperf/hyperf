# Connection Pool

## Instalasi

```bash
composer require hyperf/pool
```

## Mengapa kita membutuhkan pool?

Saat konkurensi rendah, koneksi bisa dibuat sesuai kebutuhan. Namun, ketika throughput layanan mencapai ratusan atau ribuan permintaan, operasi `Connect` dan `Close` yang sering bisa jadi bottleneck. Dengan membuat sekumpulan koneksi saat layanan dimulai dan menyimpannya dalam antrean, Anda bisa mengambil koneksi saat dibutuhkan, memakainya, lalu mengembalikannya. Mengelola antrean ini adalah tugas pool.

## Menggunakan pool

Untuk komponen Hyperf resmi, pool sudah terintegrasi. Anda tidak perlu mengelolanya secara eksplisit, framework secara otomatis menangani pengambilan dan pelepasan koneksi.

## Custom pool

Untuk mendefinisikan pool, Anda perlu membuat subclass yang meng-extend `Hyperf\Pool\Pool` dan mengimplementasikan method abstrak `createConnection`. Method ini harus mengembalikan objek yang mengimplementasikan interface `Hyperf\Contract\ConnectionInterface`. Setelah itu, custom pool Anda siap digunakan. Lihat contoh di bawah ini:

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

Setelah instansiasi, Anda dapat menggunakan method `get(): ConnectionInterface` dan `release(ConnectionInterface $connection): void` dari objek `MyConnectionPool` untuk mengambil dan melepaskan koneksi.

## SimplePool

Framework menyediakan implementasi pool yang sangat sederhana.

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

$client = $connection->getConnection(); // Instance Client seperti yang disebutkan di atas.

// Lakukan sesuatu.

$connection->release();

```

## Low-frequency component

Pool dilengkapi dengan `LowFrequencyInterface` bawaan untuk menangani koneksi frekuensi rendah. Secara default, komponen ini digunakan untuk menentukan apakah akan melepaskan koneksi berlebih dari pool berdasarkan frekuensi pengambilan koneksi.

Jika Anda perlu mengganti low-frequency component, Anda bisa langsung menggantinya di konfigurasi `dependencies`. Berikut adalah contoh menggunakan komponen database:

```php
<?php

declare(strict_types=1);

namespace App\Pool;

class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * Interval waktu untuk perhitungan frekuensi
     */
    protected int $time = 10;

    /**
     * Frekuensi untuk memicu low-frequency handler
     */
    protected int $lowFrequency = 5;

    /**
     * Interval waktu minimum antara pemicuan low-frequency yang berurutan
     */
    protected int $lowFrequencyInterval = 60;
}

```

Ubah hubungan mapping sebagai berikut:

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### Constant Frequency

Framework juga menyediakan low-frequency component lain yang disebut `ConstantFrequency`.

Setelah komponen ini diinstansiasi, ia akan memulai timer yang memanggil method `Pool::flushOne(false)` pada interval tetap. Method ini mengambil satu koneksi dari pool dan menghancurkannya jika sudah melebihi batas waktu idle.

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
