# Komponen DB Minimalis

[hyperf/database](https://github.com/hyperf/database) memiliki fungsi yang
sangat kuat, namun tidak dapat dipungkiri bahwa efisiensinya memang sedikit
kurang. Berikut adalah komponen `hyperf/db` yang minimalis.

## Instalasi

```bash
composer require hyperf/db
```

## Publish Konfigurasi Komponen

File konfigurasi untuk komponen ini terletak di `config/autoload/db.php`.
Jika file tersebut tidak ada, Anda dapat mempublikasikan file konfigurasi
ke skeleton dengan perintah berikut:

```bash
php bin/hyperf.php vendor:publish hyperf/db
```

## Konfigurasi Komponen

Konfigurasi default `config/autoload/db.php` adalah sebagai berikut. Database
mendukung konfigurasi multi-database, dengan default adalah `default`.

|   item konfigurasi   |  tipe  |       default      |                          catatan                         |
|:--------------------:|:------:|:------------------:|:--------------------------------------------------------:|
|        driver        | string |        none        |                    Engine database                       |
|         host         | string |    `localhost`     |                      alamat database                     |
|         port         |  int   |        3306        |                      alamat database                     |
|       database       | string |        none        |                    DB default database                   |
|       username       | string |        none        |                     username database                    |
|       password       | string |        null        |                     password database                    |
|       charset        | string |        utf8        |                     charset database                     |
|      collation       | string |  utf8_unicode_ci   |                    collation database                    |
|      fetch_mode      |  int   | `PDO::FETCH_ASSOC` |                Tipe result set query PDO                 |
| pool.min_connections |  int   |         1          | Jumlah koneksi minimum dalam connection pool             |
| pool.max_connections |  int   |         10         | Jumlah koneksi maksimum dalam connection pool             |
| pool.connect_timeout | float  |        10.0        |                  timeout tunggu koneksi                  |
|  pool.wait_timeout   | float  |        3.0         |                      waktu timeout                       |
|    pool.heartbeat    |  int   |         -1         |                        heartbeat                         |
|  pool.max_idle_time  | float  |        60.0        |             waktu menganggur (idle) maksimum             |
|       options        | array  |                    |                     konfigurasi PDO                      |

## Method yang Didukung Komponen

Interface spesifik dapat dilihat pada `Hyperf\DB\ConnectionInterface`.

|    nama method   |   nilai balik  |                                           catatan                                             |
|:----------------:|:--------------:|:---------------------------------------------------------------------------------------------:|
| beginTransaction |     `void`     |                      Membuka transaction (Mendukung transaction nesting)                      |
|      commit      |     `void`     |                      Commit transaction (Mendukung transaction nesting)                       |
|     rollBack     |     `void`     |                     Rollback transaction (Mendukung transaction nesting)                      |
|      insert      |     `int`      | Memasukkan data, mengembalikan ID primary key, primary key non-auto-increment mengembalikan 0 |
|     execute      |     `int`      |               Mengeksekusi SQL untuk mengembalikan jumlah baris yang terpengaruh              |
|      query       |    `array`     |                     Melakukan query SQL, mengembalikan daftar result set                      |
|      fetch       | `array, object`|             Melakukan query SQL untuk mengembalikan baris pertama dari result set             |
|      connection  |     `self`     |                          Menentukan database yang akan dikoneksikan                           |

## Penggunaan

### Menggunakan Instance DB

```php
<?php

use Hyperf\Context\ApplicationContext;
use Hyperf\DB\DB;

$db = ApplicationContext::getContainer()->get(DB::class);

$res = $db->query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Menggunakan Method Statis

```php
<?php

use Hyperf\DB\DB;

$res = DB::query('SELECT * FROM `user` WHERE gender = ?;', [1]);

```

### Method Kustom Menggunakan Anonymous Function

> Method ini memungkinkan pengguna untuk langsung mengoperasikan `PDO` atau
`MySQL` yang mendasarinya, sehingga Anda harus menangani masalah kompatibilitas
sendiri.

Sebagai contoh, jika kita ingin mengeksekusi query tertentu dan menggunakan
`fetch_mode` yang berbeda, kita dapat mengkustomisasi method kita sendiri
dengan cara berikut:

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
