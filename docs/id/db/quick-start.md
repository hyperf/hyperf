# Mulai Cepat

## Kata Pengantar

> [hyperf/database](https://github.com/hyperf/database) berasal dari [illuminate/database](https://github.com/illuminate/database). Kami melakukan beberapa modifikasi, sebagian besar fungsionalitas tetap sama. Terima kasih kepada tim Laravel untuk ORM yang powerful dan mudah digunakan ini.

[hyperf/database](https://github.com/hyperf/database) berasal dari [illuminate/database](https://github.com/illuminate/database) dan telah dimodifikasi agar bisa dipakai di framework PHP-FPM lain atau framework berbasis Swoole. Di Hyperf, perlu disebut [hyperf/db-connection](https://github.com/hyperf/db-connection), komponen yang mengimplementasikan database connection pool berbasis [hyperf/pool](https://github.com/hyperf/pool) dan menyediakan abstraksi baru untuk model. Bertindak sebagai jembatan, komponen ini memungkinkan Hyperf mengintegrasikan database component dan event component.

## Instalasi

### Hyperf Framework

```bash
composer require hyperf/db-connection
```

### Framework Lain

```bash
composer require hyperf/database
```

## Konfigurasi

Konfigurasi default-nya sebagai berikut. Database mendukung multi-database, dengan `default` sebagai koneksi utama.

| Config Item | Tipe | Nilai Default | Keterangan |
| :---: | :---: | :---: | :---: |
| driver | string | Tidak ada | Database Engine |
| host | string | Tidak ada | Alamat Database |
| database | string | Tidak ada | DB Default |
| username | string | Tidak ada | Username Database |
| password | string | null | Password Database |
| charset | string | utf8 | Charset Database |
| collation | string | utf8_unicode_ci | Collation Database |
| prefix | string | '' | Model Prefix |
| timezone | string | null | Timezone Database |
| pool.min_connections | int | 1 | Min Connections |
| pool.max_connections | int | 10 | Max Connections |
| pool.connect_timeout | float | 10.0 | Connect Timeout |
| pool.wait_timeout | float | 3.0 | Wait Timeout |
| pool.heartbeat | int | -1 | Heartbeat |
| pool.max_idle_time | float | 60.0 | Max Idle Time |
| options | array | | PDO Options |

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
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
        ]
    ],
];
```

Terkadang Anda perlu mengubah konfigurasi PDO default, misalnya jika semua field harus dikembalikan sebagai string. Ubah PDO option `ATTR_STRINGIFY_FETCHES` menjadi true.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 3306),
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
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            // Konfigurasi default framework
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // Jika Anda menggunakan database non-native MySQL atau DB dari vendor cloud (seperti slave instance/analytical instance) yang tidak mendukung MySQL prepare protocol, set ini ke true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### Read/Write Splitting

Terkadang Anda ingin `SELECT` menggunakan satu koneksi, dan `INSERT`, `UPDATE`, `DELETE` menggunakan koneksi lain. Di `Hyperf`, ini bisa dilakukan dengan mudah, baik pakai native queries, query builder, atau model.

Untuk memahami bagaimana read/write splitting dikonfigurasi, mari lihat contoh berikut:

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'read' => [
            'host' => ['192.168.1.1'],
        ],
        'write' => [
            'host' => ['196.168.1.2'],
        ],
        'sticky'    => true,
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
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];

```

Perhatikan bahwa pada contoh di atas, tiga key telah ditambahkan ke array konfigurasi: `read`, `write`, dan `sticky`. Kedua key `read` dan `write` berisi array dengan key `host`. Konfigurasi database lainnya untuk `read` dan `write` di-share dari array `mysql`.

Jika Anda ingin override konfigurasi dari array utama, cukup modifikasi array `read` dan `write`. Jadi, dalam contoh ini: 192.168.1.1 akan digunakan sebagai host koneksi "read", sementara 192.168.1.2 akan digunakan sebagai host koneksi "write". Kedua koneksi akan berbagi konfigurasi dari array `mysql`, seperti credentials database (username/password), prefix, encoding karakter, dll.

`sticky` adalah nilai opsional untuk langsung membaca record yang baru ditulis dalam request cycle yang sama. Jika `sticky` diaktifkan dan sudah ada operasi "write" dalam request cycle, operasi "read" akan menggunakan koneksi "write". Ini memastikan data yang baru ditulis bisa langsung dibaca, menghindari inkonsistensi akibat master-slave replication lag. Tergantung kebutuhan aplikasi apakah akan mengaktifkannya.

### Multiple Database Configurations

Konfigurasi multiple database adalah sebagai berikut:

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
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
    'test'=>[
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST2', 'localhost'),
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
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
    ],
];
```

Saat menggunakannya, cukup tentukan `connection` sebagai `test` untuk menggunakan konfigurasi dari `test`, seperti berikut:

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

Di dalam model, ubah property `connection` untuk menggunakan konfigurasi yang sesuai. Contohnya, `Model` berikut menggunakan konfigurasi `test`:

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Model;

/**
 * @property int $id
 * @property string $mobile
 * @property string $realname
 */
class User extends Model
{
    /**
     * Tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * Nama koneksi untuk model.
     *
     * @var string
     */
    protected $connection = 'test';

    /**
     * Atribut yang bisa diisi secara massal.
     *
     * @var array
     */
    protected $fillable = ['id', 'mobile', 'realname'];

    /**
     * Atribut yang harus di-cast ke tipe native.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer'];
}
```

## Mengeksekusi Native SQL Statements

Setelah database dikonfigurasi, Anda bisa menggunakan `Hyperf\DbConnection\Db` untuk melakukan query.

### Query Class
Ini terutama mencakup `Select`, stored procedures dengan atribut `READS SQL DATA`, functions, dan statement query lainnya.

Method `select` akan selalu mengembalikan array, dan setiap hasil dalam array adalah objek `StdClass`.

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]);  // Mengembalikan array

foreach($users as $user){
    echo $user->name;
}
```

### Execute Class
Ini terutama mencakup `Insert`, `Update`, `Delete`, dan stored procedures dengan atribut `MODIFIES SQL DATA` serta statement eksekusi lainnya.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1, 'Hyperf']); // Mengembalikan bool sukses

$affected = Db::update('UPDATE user set name = ? WHERE id = ?', ['John', 1]); // Mengembalikan jumlah baris yang terpengaruh (int)

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // Mengembalikan jumlah baris yang terpengaruh (int)

$result = Db::statement("CALL pro_test(?, '?')", [1, 'your words']);  // Mengembalikan bool. CALL pro_test(?, ?) adalah stored procedure dengan atribut MODIFIES SQL DATA
```

### Manajemen Database Transaction Otomatis

Gunakan method `transaction` dari `Db` untuk menjalankan operasi dalam sebuah transaksi. Jika terjadi exception di dalam `Closure`, transaksi akan di-rollback. Jika sukses, transaksi otomatis di-commit. Tidak perlu khawatir rollback atau commit manual:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});
```

### Manajemen Database Transaction Manual

Jika ingin kontrol penuh, mulai transaksi secara manual dengan `beginTransaction`, `commit`, dan `rollBack` dari `Db`:

```php
use Hyperf\DbConnection\Db;

Db::beginTransaction();
try{

    // Lakukan sesuatu...

    Db::commit();
} catch(\Throwable $ex){
    Db::rollBack();
}
```

## Mencetak SQL yang Baru Dieksekusi

> Method ini hanya untuk development. Harus dihapus sebelum deployment ke production, jika tidak akan menyebabkan memory leak dan data kacau.

Untuk mencatat `SQL` di production, silakan gunakan [Event Listening](id/db/event.md)

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// Aktifkan logging SQL query
Db::enableQueryLog();

$book = Book::query()->find(1);

// Cetak data SQL terakhir
var_dump(Arr::last(Db::getQueryLog()));
```

## Daftar Driver

Berbeda dengan [illuminate/database](https://github.com/illuminate/database), [hyperf/database](https://github.com/hyperf/database) hanya menyediakan MySQL driver secara default. Saat ini, juga menyediakan driver seperti [PgSQL](https://github.com/hyperf/database-pgsql), [SQLite](https://github.com/hyperf/database-sqlite), dan [SQL Server](https://github.com/hyperf/database-sqlserver-incubator).

Jika MySQL driver default tidak memenuhi kebutuhan Anda, Anda bisa menginstall driver yang sesuai sendiri:

### PgSql Driver

#### Instalasi

Memerlukan `Swoole >= 5.1.0` dan opsi `--enable-swoole-pgsql` diaktifkan saat kompilasi.

```bash
composer require hyperf/database-pgsql
```

#### File Konfigurasi

```php
// config/autoload/databases.php
return [
     // Konfigurasi lainnya
    'pgsql'=>[
        'driver' => env('DB_DRIVER', 'pgsql'),
        'host' => env('DB_HOST', 'localhost'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 5432),
        'username' => env('DB_USERNAME', 'postgres'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8'),
    ]
];
```

### SQLite Driver

#### Instalasi

Memerlukan `Swoole >= 5.1.0` dan opsi `--enable-swoole-sqlite` diaktifkan saat kompilasi.

```bash
composer require hyperf/database-sqlite
```

#### File Konfigurasi

```php
// config/autoload/databases.php
return [
     // Konfigurasi lainnya
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: adalah database in-memory, atau Anda bisa menentukan absolute file path
        'database' => env('DB_DATABASE', ':memory:'),
        // konfigurasi sqlite lainnya
    ]
];
```

### SQL Server Driver

#### Instalasi

> Ini masih dalam tahap inkubasi. Kami tidak bisa menjamin semua fungsi berjalan dengan baik saat ini. Masukan sangat diterima.

Memerlukan `Swoole >= 5.1.0` dan bergantung pada `pdo_odbc`. Membutuhkan `--with-swoole-odbc` diaktifkan saat kompilasi.

```bash
composer require hyperf/database-sqlserver-incubator
```

#### File Konfigurasi

```php
// config/autoload/databases.php
return [
     // Konfigurasi lainnya
    'sqlserver' => [
        'driver' => env('DB_DRIVER', 'sqlsrv'),
        'host' => env('DB_HOST', 'mssql'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 1443),
        'username' => env('DB_USERNAME', 'SA'),
        'password' => env('DB_PASSWORD'),
        'odbc_datasource_name' => 'DRIVER={ODBC Driver 18 for SQL Server};SERVER=127.0.0.1,1433;TrustServerCertificate=yes;database=hyperf',
        'odbc'  =>  true,
    ]
];
```
