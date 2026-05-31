# Minimalist DB Component

[hyperf/database](https://github.com/hyperf/database) sangat powerful, tapi harus diakui efisiensinya kurang optimal. Untuk itu, kami menyediakan komponen minimalis `hyperf/db`.

## Installation

```bash
composer require hyperf/db
```

## Publish Component Configuration

File konfigurasi komponen ini berada di `config/autoload/db.php`. Jika belum ada, Anda bisa menerbitkannya ke skeleton melalui perintah berikut:

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## Component Configuration

Konfigurasi default di `config/autoload/db.php` adalah sebagai berikut. Database mendukung konfigurasi multi-database, dengan `default` sebagai koneksi utama.

| Item Konfigurasi | Tipe | Nilai Default | Keterangan |
|:--------------------:|:------:|:------------------:|:--------------------------------:|
| driver | string | N/A | Database engine |
| host | string | `localhost` | Alamat database |
| port | int | 3306 | Port database |
| database | string | N/A | Database default |
| username | string | N/A | Username database |
| password | string | null | Password database |
| charset | string | utf8 | Character set database |
| collation | string | utf8_unicode_ci | Collation database |
| fetch_mode | int | `PDO::FETCH_ASSOC` | Tipe hasil query PDO |
| pool.min_connections | int | 1 | Minimum koneksi dalam connection pool |
| pool.max_connections | int | 10 | Maksimum koneksi dalam connection pool |
| pool.connect_timeout | float | 10.0 | Timeout koneksi |
| pool.wait_timeout | float | 3.0 | Durasi tunggu |
| pool.heartbeat | int | -1 | Detak jantung (heartbeat) |
| pool.max_idle_time | float | 60.0 | Waktu idle maksimum |
| options | array | | Konfigurasi PDO |

## Supported Methods

Untuk detail interface, lihat `Hyperf\DB\ConnectionInterface`.

| Nama Method | Tipe Return | Keterangan |
|:----------------:|:--------------:|:------------------------------------:|
| beginTransaction | `void` | Memulai transaksi, mendukung transaksi bersarang |
| commit | `void` | Menyelesaikan transaksi, mendukung transaksi bersarang |
| rollBack | `void` | Membatalkan transaksi, mendukung transaksi bersarang |
| insert | `int` | Insert data, mengembalikan ID primary key; mengembalikan 0 jika primary key bukan auto-increment |
| execute | `int` | Eksekusi SQL, mengembalikan jumlah baris yang terpengaruh |
| query | `array` | Query SQL, mengembalikan daftar result set |
| fetch | `array, object` | Query SQL, mengembalikan baris pertama dari result set |
| connection | `self` | Menentukan koneksi database |

## Usage

### Menggunakan DB Instance

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);
```

### Menggunakan Static Methods

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);
```

### Menggunakan Anonymous Functions untuk Menyesuaikan Method

> Method ini memungkinkan Anda mengoperasikan `PDO` atau `MySQL` secara langsung, jadi Anda harus menangani sendiri kompatibilitasnya.

Misalnya, jika kita ingin menjalankan query tertentu menggunakan `fetch mode` yang berbeda, kita dapat menyesuaikan method kita sendiri melalui pendekatan berikut:

```php
<?php
use Hyperf\DB\DB;

$sql = 'SELECT * FROM `user` WHERE id = ?;';
$bindings = [2];
$mode = \PDO::FETCH_OBJ;
$res = DB::run(function (\PDO $pdo) use ($sql, $bindings, $mode) {
    $statement = $pdo->prepare($sql);

    $this->bindValues($statement, $bindings);

    $statement->execute();

    return $statement->fetchAll($mode);
});
```
