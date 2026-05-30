# Snowflake

## Pengenalan Algoritma

`Snowflake` adalah algoritma pembuatan ID unik global terdistribusi yang
diusulkan oleh Twitter. Hasil dari algoritma pembuat `ID` ini berupa integer
panjang dengan ukuran `64bit`. Pada algoritma standar, strukturnya ditunjukkan
pada gambar di bawah ini:

![snowflake](imgs/snowflake.jpeg)

- `One bit`, tidak digunakan.
  - Bit tertinggi dalam sistem biner adalah bit tanda (sign bit). `ID` yang
    kita hasilkan umumnya berupa integer positif, sehingga bit tertinggi
    diatur tetap menjadi 0.
  
- `41 bits` untuk mencatat timestamp (MS).
  - `41 bits` dapat merepresentasikan `2^41 - 1` angka.
  - Dengan kata lain, `41 bits` dapat merepresentasikan nilai milidetik sebesar
    `2^41 - 1`, dan dalam satuan tahun adalah
    `(2^41 - 1) / (1000 * 60 * 60 * 24 * 365)` atau sekitar `69` tahun.
  
- `10 bits`, digunakan untuk mencatat `ID` mesin pekerja (working machine).
  - Dapat di-deploy di `2^10` node, termasuk `5` bit `DatacenterId` dan `5` bit
    `WorkerId`.
  
- `12 bits`, nomor seri (serial number), digunakan untuk mencatat `id` berbeda
  yang dibuat dalam milidetik yang sama.
  - `12 bits` dapat mewakili angka integer positif maksimum `2^12 - 1` dengan
    total `4095` angka, yang mewakili `4095` nomor seri `ID` yang dibuat oleh
    mesin yang sama dalam interval waktu yang sama (MS).

`Snowflake` dapat menjamin bahwa:

 - Semua `ID` yang dihasilkan bertambah seiring waktu.
 - Tidak ada `ID` duplikat yang akan dibuat di seluruh sistem terdistribusi
   (Karena terdapat perbedaan antara `DatacenterId (5 bits)` dan
   `WorkerId (5 bits)`).
 
Komponen [hyperf/snowflake](https://github.com/hyperf/snowflake) menyediakan
ekstabilitas yang baik dalam desainnya, memungkinkan Anda untuk
mengimplementasikan algoritma varian lain berbasis snowflake dengan ekstensi
yang sederhana.

## Instalasi

```
composer require hyperf/snowflake
```

## Penggunaan

Framework menyediakan `MetaGeneratorInterface` dan `IdGeneratorInterface`.
`MetaGeneratorInterface` menghasilkan file `Meta` dari `ID`, dan
`IdGeneratorInterface` menghasilkan `distributed ID` berdasarkan file `Meta`
yang sesuai.

Secara default, `MetaGeneratorInterface` yang digunakan oleh framework adalah
`generator tingkat milidetik` berbasis `Redis`.
File konfigurasi terletak di `config/autoload/snowflake.php`. Jika file
konfigurasi tidak ada, Anda dapat menjalankan perintah
`php bin/hyperf.php vendor:publish hyperf/snowflake` untuk membuat konfigurasi
default. Isi dari file konfigurasi adalah sebagai berikut:

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
        // To calculate the Key of WorkerId
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
    RedisSecondMetaGenerator::class => [
        // Redis Pool
        'pool' => 'default',
        // To calculate the Key of WorkerId
        'key' => RedisMilliSecondMetaGenerator::DEFAULT_REDIS_KEY
    ],
];

```

Menggunakan `Snowflake` di dalam framework sangatlah mudah. Anda hanya perlu
mengambil objek `IdGeneratorInterface` dari `DI`.

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$id = $generator->generate();
```

Ketika Anda perlu mengembalikan `Meta` dari `ID` yang bersangkutan, Anda hanya
perlu memanggil `degenerate`.

```php
<?php
use Hyperf\Snowflake\IdGeneratorInterface;
use Hyperf\Context\ApplicationContext;

$container = ApplicationContext::getContainer();
$generator = $container->get(IdGeneratorInterface::class);

$meta = $generator->degenerate($id);
```

## Override Generator 'Meta'

Ada banyak cara untuk mengimplementasikan `distributed global unique ID`, dan
ada juga banyak varian berdasarkan algoritma `Snowflake`. Meskipun semuanya
adalah algoritma `Snowflake`, mereka tidaklah sama. Sebagai contoh, seseorang
mungkin membuat `Meta` berdasarkan `UserId` alih-alih `WorkerId`. Selanjutnya,
mari kita buat implementasi `MetaGenerator` sederhana.

Secara singkat, `UserId` pasti akan melebihi '10 bits'. Oleh karena itu,
`DataCenterId` dan `WorkerId` default tidak dapat menampungnya secara langsung.
Dengan demikian, nilai modulo dari `UserId` perlu digunakan.

```php
<?php

declare(strict_types=1);

use Hyperf\Snowflake\IdGenerator;

class UserDefinedIdGenerator
{
    /**
     * @var IdGenerator\SnowflakeIdGenerator
     */
    protected $idGenerator;

    public function __construct(IdGenerator\SnowflakeIdGenerator $idGenerator)
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

## Penerapan Pada Model Database

Setelah mengonfigurasi `Snowflake`, kita dapat membuat model database secara
langsung menggunakan `ID` `Snowflake` sebagai primary key.

```php
<?php

class User extends \Hyperf\Database\Model\Model {
    use \Hyperf\Snowflake\Concern\Snowflake;
}
```

Ketika model user dibuat, algoritma `Snowflake` akan digunakan untuk
menghasilkan primary key secara default.
