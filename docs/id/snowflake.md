# Snowflake

## Pendahuluan

`Snowflake` adalah algoritma global unique ID generation terdistribusi yang diusulkan oleh Twitter. Hasil yang dihasilkan oleh algoritma ini adalah bilangan bulat panjang `64bit`. Dalam algoritma standar, strukturnya seperti yang ditunjukkan di bawah ini:

![snowflake](imgs/snowflake.jpeg)

- `1 bit`, tidak digunakan.
  - Bit tertinggi dalam biner adalah bit tanda. `ID` yang kita hasilkan umumnya adalah bilangan bulat positif, jadi bit tertinggi ini ditetapkan ke 0.

- `41 bits`, digunakan untuk mencatat timestamp (milidetik).
  - `41 bits` dapat merepresentasikan angka `2^41 - 1`.
  - Artinya, `41 bits` dapat merepresentasikan `2^41 - 1` nilai milidetik. Dikonversi menjadi tahun, yaitu `(2^41 - 1) / (1000 * 60 * 60 * 24 * 365)` sekitar `69` tahun.

- `10 bits`, digunakan untuk mencatat mesin `ID`.
  - Dapat di-deploy pada total `2^10` yaitu `1024` node, termasuk `5 bits` `DatacenterId` dan `5 bits` `WorkerId`.

- `12 bits`, nomor seri, digunakan untuk mencatat `id` yang berbeda yang dihasilkan dalam milidetik yang sama.
  - Bilangan bulat positif maksimum yang dapat direpresentasikan oleh `12 bits` adalah `2^12 - 1`, total `4095` angka, digunakan untuk merepresentasikan `4095` nomor seri `ID` yang dihasilkan pada mesin yang sama pada timestamp (milidetik) yang sama.

`Snowflake` dapat menjamin:

 - Semua `ID` yang dihasilkan bertambah berdasarkan tren waktu.
 - Tidak ada `ID` duplikat yang akan dihasilkan di seluruh sistem terdistribusi (karena `DatacenterId (5 bits)` dan `WorkerId (5 bits)` digunakan untuk pembedaan).
 
Komponen `Hyperf/snowflake` memiliki scalability yang baik dalam desain, memungkinkan Anda untuk mengimplementasikan algoritma varian lain berdasarkan Snowflake melalui ekstensi sederhana.

## Instalasi

```bash
composer require hyperf/snowflake
```

## Penggunaan

Framework menyediakan `MetaGeneratorInterface` dan `IdGeneratorInterface`. `MetaGeneratorInterface` akan menghasilkan file `Meta` untuk `ID`, dan `IdGeneratorInterface` akan menghasilkan `distributed ID` berdasarkan file `Meta` yang sesuai.

`MetaGeneratorInterface` yang digunakan oleh framework secara default adalah `generator level milidetik` berbasis `Redis`.
File konfigurasi terletak di `config/autoload/snowflake.php`. Jika file konfigurasi tidak ada, Anda dapat membuat konfigurasi default dengan menjalankan perintah `php bin/hyperf.php vendor:publish hyperf/snowflake`. Isi file konfigurasi adalah sebagai berikut:

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\MetaGenerator\RedisMilliSecondMetaGenerator;
use Hyperf\Snowflake\MetaGenerator\RedisSecondMetaGenerator;
use Hyperf\Snowflake\MetaGeneratorInterface;

return [
    'begin_second' => MetaGeneratorInterface::DEFAULT_BEGIN_SECOND,
    RedisMilliSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // Key yang digunakan untuk menghitung WorkerId
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
    RedisSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // Key yang digunakan untuk menghitung WorkerId
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
];
```

Menggunakan `Snowflake` di framework sangat sederhana, Anda hanya perlu mengambil objek `IdGeneratorInterface` dari `DI`.

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$id = $generator->generate();
```

Ketika Anda perlu melakukan inferensi balik dari `ID` ke `Meta` yang sesuai, Anda hanya perlu memanggil `degenerate`.

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$meta = $generator->degenerate($id);
```

## Menulis Ulang `Meta` Generator

Ada banyak cara untuk mengimplementasikan `distributed global unique ID`, dan juga ada banyak algoritma varian yang didasarkan pada algoritma `Snowflake`. Meskipun semuanya adalah algoritma `Snowflake`, mereka tidak semuanya sama. Misalnya, beberapa orang mungkin menghasilkan `Meta` berdasarkan `UserId` daripada `WorkerId`. Selanjutnya, mari kita implementasikan `MetaGenerator` sederhana.
Sederhananya, `UserId` pasti akan melebihi `10 bits`, jadi `DataCenterId` dan `WorkerId` default tentu tidak dapat menampungnya, sehingga perlu mengambil modulo dari `UserId`.

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\IdGenerator\SnowflakeIdGenerator;

class UserDefinedIdGenerator
{
    protected SnowflakeIdGenerator $idGenerator;

    public function __construct(SnowflakeIdGenerator $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    public function generate(int $userId)
    {
        $meta = $this->idGenerator->getMetaGenerator()->generate();

        return $this->idGenerator->generate($meta->setWorkerId($userId % 31));
    }

    public function degenerate(int $id)
    {
        return $this->idGenerator->degenerate($id);
    }
}

use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(UserDefinedIdGenerator::class);
$userId = 20190620;

$id = $generator->generate($userId);
```

## Aplikasi di Database Model

Setelah mengkonfigurasi Snowflake, kita dapat membuat model database menggunakan snowflake id sebagai primary key secara langsung.

```php
<?php
use Hyperf\Database\Model\Model;
use Hyperf\Snowflake\Concern\Snowflake;

class User extends Model {
    use Snowflake;
}
```

Model User di atas akan secara default menggunakan algoritma Snowflake untuk menghasilkan primary key ketika dibuat.

Karena Snowflake akan menimpa method `creating`, dan pengguna mungkin perlu mengatur method `creating` mereka sendiri, akan ada masalah dimana `ID` tidak dapat dihasilkan. Di sini, pengguna hanya perlu menanganinya sendiri sebagai berikut:

```php
<?php
use Hyperf\Database\Model\Model;
use Hyperf\Snowflake\Concern\Snowflake;

class User extends Model {
    use Snowflake {
        creating as create;
    }

    public function creating()
    {
        $this->create();
        // Lakukan sesuatu ...
    }
}
```
