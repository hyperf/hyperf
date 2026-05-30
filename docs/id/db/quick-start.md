# Quick Start

## Pengantar

> [hyperf/database](https://github.com/hyperf/database) diturunkan dari
> [illuminate/database](https://github.com/illuminate/database), kami telah
> melakukan beberapa modifikasi tetapi sebagian besar metodenya tetap sama.
> Terima kasih kepada tim pengembangan Laravel yang telah mengimplementasikan
> komponen ORM yang sangat kuat dan mudah digunakan ini.

Komponen [hyperf/database](https://github.com/hyperf/database) didasarkan pada
komponen yang diturunkan dari [illuminate/database](https://github.com/illuminate/database)
dengan beberapa perubahan agar dapat digunakan baik di framework PHP-FPM
maupun framework berbasis Swoole. Di Hyperf, Anda perlu menggunakan komponen
[hyperf/db-connection](https://github.com/hyperf/db-connection), yang
mengimplementasikan database connection pool berbasis
[hyperf/pool](https://github.com/hyperf/pool). Dengan komponen tersebut sebagai
penghubung, Hyperf dapat mengintegrasikan database connections dan events.

## Instalasi

### Framework Hyperf

```bash
composer require hyperf/db-connection
```

### Framework lainnya

```bash
composer require hyperf/database
```

## Konfigurasi

Konfigurasi default adalah sebagai berikut. Konfigurasi ini mendukung
konfigurasi beberapa database connection sekaligus. Connection default yang
digunakan saat tidak ada connection yang ditentukan dinamakan `default`.

| Nama                 | Tipe   | Nilai default   | Deskripsi                                            |
| :------------------: | :----: | :-------------: | :--------------------------------------------------: |
| driver               | string | tidak ada       | Tipe database                                        |
| host                 | string | tidak ada       | Host database                                        |
| database             | string | tidak ada       | Nama database                                        |
| username             | string | tidak ada       | Username database                                    |
| password             | string | null            | Password database                                    |
| charset              | string | utf8            | Charset string database                              |
| collation            | string | utf8_unicode_ci | Collation string database                            |
| prefix               | string | ''              | Prefix tabel database                                |
| timezone             | string | null            | Zona waktu database                                  |
| pool.min_connections | int    | 1               | Jumlah minimum connection dalam connection pool      |
| pool.max_connections | int    | 10              | Jumlah maksimum connection dalam connection pool      |
| pool.connect_timeout | float  | 10.0            | Timeout menunggu connection                          |
| pool.wait_timeout    | float  | 3.0             | Waktu timeout dalam detik                            |
| pool.heartbeat       | int    | -1              | Heartbeat connection (-1 berarti dinonaktifkan)      |
| pool.max_idle_time   | float  | 60.0            | Waktu idle maksimum connection sebelum ditutup       |
| options              | array  |                 | Opsi konfigurasi PDO                                 |

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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

Terkadang pengguna perlu mengubah konfigurasi default PDO. Misalnya, jika Anda
ingin mengembalikan semua field sebagai string, Anda perlu mengatur item
konfigurasi PDO `ATTR_STRINGIFY_FETCHES` menjadi `true`.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'port' => env('DB_PORT', 3306),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'options' => [
            // Framework default configuration
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            // If you are using a non-native MySQL or a DB provided by a cloud vendor, such as a database/analytic instance that does not support the MySQL prepare protocol, set this to true
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
];
```

### Pemisahan Read dan Write

Terkadang Anda ingin statement `SELECT` menggunakan satu database connection dan
statement `INSERT`, `UPDATE`, dan `DELETE` menggunakan database connection lainnya. Hal
ini sangat mudah diimplementasikan di Hyperf, tidak peduli apakah Anda menggunakan
native query, query builder, atau model.

Untuk memahami bagaimana konfigurasi pemisahan read-write dilakukan, mari kita
lihat contoh berikut terlebih dahulu:

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'read' => [
            'host' => ['192.168.1.1'],
        ],
        'write' => [
            'host' => ['196.168.1.2'],
        ],
        'sticky' => true,
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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

Perhatikan bahwa pada contoh di atas, tiga key telah ditambahkan ke array
konfigurasi, yaitu `read`, `write`, dan `sticky`. Key `read` dan `write` keduanya
berisi array dengan key `host`.

Jika Anda ingin menimpa konfigurasi pada array utama, Anda hanya perlu mengubah
array `read` dan `write`. Jadi, pada contoh ini: 192.168.1.1 akan digunakan
sebagai host connection "read", dan 192.168.1.2 akan digunakan sebagai host
connection "write". Kedua connection ini akan berbagi berbagai konfigurasi dari
array mysql, seperti kredensial database (username/password), prefix, encoding
karakter, dll.

`sticky` adalah nilai opsional yang dapat digunakan untuk langsung membaca
record yang telah ditulis ke database selama siklus request saat ini. Jika opsi
`sticky` diaktifkan dan operasi "write" telah dilakukan dalam siklus request
saat ini, maka operasi "read" apa pun berikutnya akan menggunakan connection "write".
Hal ini memastikan bahwa data yang ditulis dalam siklus request yang sama dapat
langsung dibaca, sehingga menghindari masalah inkonsistensi data yang disebabkan
oleh delay master-slave. Namun, apakah opsi ini harus diaktifkan atau tidak
bergantung pada kebutuhan aplikasi Anda.

### Mengonfigurasi beberapa database connection

Konfigurasi multi-database adalah sebagai berikut.

```php
<?php

return [
    'default' => [
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST','localhost'),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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
        'driver' => env('DB_DRIVER','mysql'),
        'host' => env('DB_HOST2','localhost'),
        'database' => env('DB_DATABASE','hyperf'),
        'username' => env('DB_USERNAME','root'),
        'password' => env('DB_PASSWORD',''),
        'charset' => env('DB_CHARSET','utf8'),
        'collation' => env('DB_COLLATION','utf8_unicode_ci'),
        'prefix' => env('DB_PREFIX',''),
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

Untuk menggunakan connection yang berbeda, Anda hanya perlu menentukan `connection`
melalui query builder:

```php
<?php

use Hyperf\DbConnection\Db;
// default
Db::select('SELECT * FROM user;');
Db::connection('default')->select('SELECT * FROM user;');

// test
Db::connection('test')->select('SELECT * FROM user;');
```

Anda dapat mengubah connection default yang digunakan oleh model tertentu dengan
mengatur nilai `$connection` di dalam class model tersebut:

> Perhatikan bahwa visibilitas properti harus diatur sebagai `protected`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact group@hyperf.io
 * @license https://github.com/hyperf/hyperf/blob/master/LICENSE
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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table ='user';

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection ='test';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id','mobile','realname'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' =>'integer'];
}
```

## Mengeksekusi statement SQL native

Setelah mengonfigurasi database, Anda dapat menggunakan `Hyperf\DbConnection\Db`
untuk melakukan query.

### Melakukan query data

Ini mencakup statement query seperti `select`, stored procedure, dan function
yang membaca data SQL.

Metode `select` akan selalu mengembalikan array, dan setiap hasil di dalam
array tersebut adalah objek `StdClass`.

```php
<?php

use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM `user` WHERE gender = ?',[1]); // return array

foreach($users as $user){
    echo $user->name;
}
```

### Mengubah data

Ini mencakup statement eksekusi seperti `Insert`, `Update`, `Delete`, dan stored
procedure yang memodifikasi data SQL.

```php
<?php

use Hyperf\DbConnection\Db;

$inserted = Db::insert('INSERT INTO user (id, name) VALUES (?, ?)', [1,'Hyperf']); // Returns whether it is successful bool

$affected = Db::update('UPDATE user set name =? WHERE id = ?', ['John', 1]); // Returns the number of affected rows int

$affected = Db::delete('DELETE FROM user WHERE id = ?', [1]); // Returns the number of affected rows int

$result = Db::statement("CALL pro_test(?,'?')", [1,'your words']); // return bool CALL pro_test(?,?) is a stored procedure, the attribute is MODIFIES SQL DATA
```

### Mengelola transaksi database secara otomatis

Anda dapat menggunakan metode `transaction` dari `Db` untuk menjalankan sekumpulan
operasi sebagai satu transaksi database. Jika terjadi exception di dalam closure
transaksi, transaksi tersebut akan di-roll back secara otomatis. Jika closure
transaksi berhasil dieksekusi, transaksi akan di-commit secara otomatis. Ini
berarti Anda tidak perlu khawatir tentang melakukan rollback atau commit secara
manual saat menggunakan metode `transaction`:

```php
<?php
use Hyperf\DbConnection\Db;

Db::transaction(function () {
    Db::table('user')->update(['votes' => 1]);

    Db::table('posts')->delete();
});

```

### Mengelola transaksi database secara manual

Jika Anda ingin memulai transaksi secara manual dan memiliki kontrol penuh atas
rollback dan commit, Anda dapat menggunakan metode `beginTransaction`, `commit`,
dan `rollBack`:

```php
use Hyperf\DbConnection\Db;

Db::beginTransaction();
try{

    // Do something...

    Db::commit();
} catch(\Throwable $ex){
    Db::rollBack();
}
```

## Mencatat (logging) query SQL raw

> Metode ini hanya boleh digunakan di lingkungan development dan harus dihapus
> sebelum deployment ke production/online, jika tidak, hal ini akan menyebabkan
> memory leak yang serius dan masalah konsistensi data.

Anda dapat menggunakan [database event listener](id/db/event) untuk mencatat query SQL:

```php
<?php

use Hyperf\DbConnection\Db;
use Hyperf\Collection\Arr;
use App\Model\Book;

// Enable SQL data logging function
// WARNING: causes a memory leak and data consistency problems in the Swoole CLI environment, local development and debugging only!
Db::enableQueryLog();

$book = Book::query()->find(1);

// Print the last SQL query
var_dump(Arr::last(Db::getQueryLog()));
```

## Daftar driver

Berbeda dengan [illuminate/database](https://github.com/illuminate/database),
[hyperf/database](https://github.com/hyperf/database) hanya menyediakan driver
MySQL secara default. Namun, saat ini juga tersedia driver lain seperti
[PgSQL](https://github.com/hyperf/database-pgsql),
[SQLite](https://github.com/hyperf/database-sqlite), dan
[SQL Server](https://github.com/hyperf/database-sqlserver-incubator).
Jika driver MySQL bawaan tidak memenuhi kebutuhan penggunaan Anda, Anda dapat menginstal
driver yang sesuai secara mandiri.

### Driver PgSql

#### Instalasi

Membutuhkan `Swoole >= 5.1.0` dan opsi `--enable-swoole-pgsql` harus diaktifkan saat kompilasi.

```bash
composer require hyperf/database-pgsql
```

#### File konfigurasi

```php
// config/autoload/databases.php
return [
    // Other configurations
    'pgsql'=> [
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

### Driver SQLite

#### Instalasi

Membutuhkan `Swoole >= 5.1.0` dan opsi `--enable-swoole-sqlite` harus diaktifkan saat kompilasi.

```bash
composer require hyperf/database-sqlite
```

#### File konfigurasi

```php
// config/autoload/databases.php
return [
    // Other configurations
    'sqlite'=>[
        'driver' => env('DB_DRIVER', 'sqlite'),
        'host' => env('DB_HOST', 'localhost'),
        // :memory: For an in-memory database, you can also specify the absolute path to the file.
        'database' => env('DB_DATABASE', ':memory:'),
        // other sqlite config
    ]
];
```

### Driver SQL Server

#### Instalasi

> Masih dalam tahap inkubasi, saat ini kami tidak dapat menjamin bahwa semua
> fungsi akan berjalan dengan normal. Kami sangat menerima feedback Anda.

Membutuhkan `Swoole >= 5.1.0`, bergantung pada pdo_odbc, dan opsi `--with-swoole-odbc` harus diaktifkan saat kompilasi.

```bash
composer require hyperf/database-sqlserver-incubator
```

#### File konfigurasi

```php
// config/autoload/databases.php
return [
    // Other configurations
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
