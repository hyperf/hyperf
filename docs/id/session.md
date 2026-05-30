# Manajemen Session

HTTP adalah protokol stateless, yang berarti server tidak menyimpan state apa
pun selama transaksi dengan klien. Namun, saat mengembangkan aplikasi web,
sering kali ada kebutuhan untuk berbagi informasi di antara beberapa request,
yang biasanya dilakukan melalui session storage. Anda dapat mengimplementasikan
fungsi session dengan [hyperf/session](https://github.com/hyperf/session).
Komponen session saat ini hanya mengimplementasikan dua storage driver, yaitu
`file` dan `Redis`. Standarnya adalah driver `file`. Di lingkungan produksi,
kami sangat menyarankan Anda menggunakan `Redis` karena memiliki performa yang
jauh lebih baik dibandingkan dengan alternatif `file` dan juga lebih cocok untuk
arsitektur cluster.

# Instalasi

```bash
composer require hyperf/session
```

# Konfigurasi

Konfigurasi komponen session disimpan di dalam file
`config/autoload/session.php`. Jika file tersebut tidak ada, Anda dapat
menggunakan perintah `php bin/hyperf.php vendor:publish hyperf/session` untuk
menerbitkan (publish) file konfigurasi dari komponen session.

## Konfigurasi session middleware

Sebelum menggunakan session, Anda perlu mengonfigurasi middleware
`Hyperf\Session\Middleware\SessionMiddleware` sebagai middleware global dari
HTTP Server agar komponen tersebut dapat mengintersepsi request untuk diproses.
Anda dapat menentukan middleware di file konfigurasi
`config/autoload/middlewares.php`. Contoh konfigurasi:

```php
<?php

return [
    // Here http corresponds to the default server name. If you need to use session on other servers, you need to configure the corresponding global middleware
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## Konfigurasi storage driver

Ubah storage driver session yang berbeda dengan mengganti konfigurasi
`handler` pada file konfigurasi, dan item konfigurasi spesifik dari handler
yang sesuai ditentukan oleh item konfigurasi yang berbeda di dalam `options`.

### Menggunakan file storage driver

> File storage driver adalah driver penyimpanan default, tetapi disarankan
> untuk menggunakan driver Redis di lingkungan produksi

Ketika nilai `handler` adalah `Hyperf\Session\Handler\FileHandler`, ini
menunjukkan bahwa file storage driver digunakan dan semua file data session akan
dihasilkan dan disimpan di folder yang sesuai dengan nilai konfigurasi
`options.path`. Folder konfigurasi default berada di folder `runtime/session`
di bawah direktori root.

### Menggunakan driver Redis

Sebelum menggunakan `Redis` storage driver, Anda perlu menginstal komponen
[hyperf/redis](https://github.com/hyperf/redis). Untuk menggunakan storage
driver ini, atur nilai `handler` ke `Hyperf\Session\Handler\RedisHandler`.
Anda dapat menyesuaikan koneksi `Redis` yang digunakan oleh driver dengan
mengonfigurasi nilai `options.connection`. Koneksi didefinisikan dalam
`config/autoload/redis.php` dari komponen
[hyperf/redis](https://github.com/hyperf/redis).

# Penggunaan

## Mendapatkan objek session

Objek session dapat diakses dengan menginjeksikan
`Hyperf\Contract\SessionInterface`:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\SessionInterface;

class IndexController
{
    #[Inject]
    private SessionInterface $session;

    public function index()
    {
        // Use directly via $this->session
    }
}
```

## Menyimpan data

Saat Anda ingin menyimpan data di dalam session, Anda dapat melakukannya dengan
memanggil metode `set(string $name, $value): void`:

```php
<?php

$this->session->set('foo','bar');
```

## Mengambil data

Saat Anda ingin mendapatkan data dari session, Anda dapat melakukannya dengan
memanggil metode `get(string $name, $default = null)`:

```php
<?php

$this->session->get('foo', $default = null);
```

### Mendapatkan semua data

Anda dapat mengambil semua data yang tersimpan dari session sekaligus dengan
memanggil metode `all(): array`:

```php
<?php

$data = $this->session->all();
```

## Menentukan apakah suatu nilai ada di dalam session

Untuk menentukan apakah suatu nilai ada di dalam session, Anda dapat menggunakan
metode `has(string $name): bool`. Jika nilai tersebut ada dan tidak bernilai
null, metode `has` akan mengembalikan `true`:

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## Mengambil dan menghapus data

Dengan memanggil metode `remove(string $name)`, Anda dapat mengambil dan
menghapus suatu data dari session hanya dengan menggunakan satu metode:

```php
$data = $this->session->remove('foo');
```

## Menghapus satu atau beberapa data

Dengan memanggil metode `forget(string|array $name): void`, satu atau beberapa
data dapat dihapus dari session hanya dengan menggunakan satu metode. Ketika
sebuah string diberikan, itu berarti hanya satu data yang dihapus. Ketika sebuah
array string kunci diberikan, itu berarti menghapus beberapa data sekaligus:

```php
$this->session->forget('foo');
$this->session->forget(['foo','bar']);
```

## Membersihkan data session saat ini

Anda dapat membersihkan semua data di dalam session saat ini dengan memanggil
metode `clear(): void`:

```php
$this->session->clear();
```

## Mendapatkan ID session saat ini

Saat Anda ingin mendapatkan ID session saat ini untuk menangani beberapa logika
sendiri, Anda dapat mengambil ID session saat ini dengan memanggil metode
`getId(): string`:

```php
$sessionId = $this->session->getId();
```
