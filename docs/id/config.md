# Konfigurasi

Kalau Anda pake project yang dibuat dari [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton), semua file konfigurasi Hyperf ada di folder `config` di root direktori. Setiap opsi udah ada deskripsi, jadi Anda bisa liat dan pelajari opsi yang tersedia kapan aja.

# Instalasi

```bash
composer require hyperf/config
```

# Struktur File Konfigurasi

Struktur di bawah ini cuma contoh dari konfigurasi default Hyperf-Skeleton. Di prakteknya, isi folder bisa beda-beda tergantung dependensi atau komponen yang dipake.

```
config
├── autoload // File konfigurasi dalam folder ini akan dimuat oleh komponen konfigurasi itu sendiri, menggunakan nama file dalam folder sebagai key pertama
│   ├── amqp.php  // Digunakan untuk mengelola komponen AMQP
│   ├── annotations.php // Digunakan untuk mengelola annotation
│   ├── apollo.php // Digunakan untuk mengelola configuration center berbasis Apollo
│   ├── aspects.php // Digunakan untuk mengelola AOP aspect
│   ├── async_queue.php // Digunakan untuk mengelola layanan antrian sederhana berbasis Redis
│   ├── cache.php // Digunakan untuk mengelola komponen cache
│   ├── commands.php // Digunakan untuk mengelola custom command
│   ├── consul.php // Digunakan untuk mengelola client Consul
│   ├── databases.php // Digunakan untuk mengelola client database
│   ├── dependencies.php // Digunakan untuk mengelola dependensi DI dan pemetaan kelas
│   ├── devtool.php // Digunakan untuk mengelola developer tool
│   ├── exceptions.php // Digunakan untuk mengelola exception handler
│   ├── listeners.php // Digunakan untuk mengelola event listener
│   ├── logger.php // Digunakan untuk mengelola log
│   ├── middlewares.php // Digunakan untuk mengelola middleware
│   ├── opentracing.php // Digunakan untuk mengelola call chain tracing
│   ├── processes.php // Digunakan untuk mengelola custom process
│   ├── redis.php // Digunakan untuk mengelola client Redis
│   └── server.php // Digunakan untuk mengelola layanan Server
├── config.php // Digunakan untuk mengelola konfigurasi pengguna atau framework. Jika konfigurasi relatif independen, dapat juga ditempatkan di folder autoload
├── container.php // Bertanggung jawab untuk inisialisasi container, berjalan sebagai file konfigurasi dan akhirnya mengembalikan objek Psr\Container\ContainerInterface
└── routes.php // Digunakan untuk mengelola route
```

## Deskripsi Konfigurasi `server.php`

Berikut adalah `settings` default yang disediakan oleh `config/autoload/server.php` di Hyperf-Skeleton:

```php
<?php
declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Konfigurasi lain untuk file ini dihilangkan di sini
    'settings' => [
        'enable_coroutine' => true, // Aktifkan built-in coroutine
        'worker_num' => swoole_cpu_num(), // Atur jumlah Worker process yang akan dimulai
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid', // PID dari master process
        'open_tcp_nodelay' => true, // Nonaktifkan algoritma Nagle saat mengirim data melalui koneksi TCP, segera kirim ke koneksi client
        'max_coroutine' => 100000, // Atur jumlah maksimum coroutine untuk worker process saat ini
        'open_http2_protocol' => true, // Aktifkan parsing protokol HTTP2
        'max_request' => 100000, // Atur jumlah maksimum task untuk worker process
        'socket_buffer_size' => 2 * 1024 * 1024, // Konfigurasi panjang buffer untuk koneksi client
    ],
];
```

File konfigurasi ini digunakan untuk mengelola layanan Server. Opsi `settings` di dalamnya dapat langsung menggunakan opsi yang disediakan oleh `Swoole Server`. Untuk opsi lainnya, silakan merujuk ke [dokumentasi resmi Swoole](https://wiki.swoole.com/#/server/setting).

Kalau perlu daemonization, tinggal tambahin `'daemonize' => true` di `settings`. Abis jalanin `php bin/hyperf.php start`, program bakal jalan di latar belakang sebagai daemon.

Konfigurasi individual Server perlu ditambahkan ke `settings` dari `servers` yang sesuai. Sebagai contoh, untuk konfigurasi TCP Server dari protokol `jsonrpc`, aktifkan EOF automatic packet splitting dan atur string EOF:

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Konfigurasi lain untuk file ini dihilangkan di sini
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true, // Aktifkan EOF automatic packet splitting
                'package_eof' => "\r\n", // Atur string EOF
            ],
        ],
    ],
];
```

## Hubungan Antara `config.php` dan File Konfigurasi di Folder `autoload`

`config.php` dan file di folder `autoload` sama-sama dipindai pas service mulai, lalu diinjeksi ke objek `Hyperf\Contract\ConfigInterface`. Struktur konfigurasinya adalah array key-value gede. Bedanya: nama file di `autoload` jadi Key tingkat pertama, sedangkan `config.php` pake key yang Anda definisikan sebagai tingkat pertama. Biar lebih jelas, liat contoh berikut:
Misalkan ada file `config/autoload/client.php` dengan konten berikut:

```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```

Maka Key yang sesuai dengan nilai `timeout` adalah `client.request.timeout`;

Misalkan kita ingin mendapatkan hasil yang sama dengan Key yang sama, tetapi konfigurasi ditulis dalam file `config/config.php`, konten file harus sebagai berikut:

```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## Menggunakan Hyperf Config Component

Ini adalah komponen konfigurasi default bawaan Hyperf. Dibangun berdasarkan interface `Hyperf\Contract\ConfigInterface`, dan objek `Hyperf\Config\Config` diikat ke interface tersebut oleh `ConfigProvider` dari komponen [hyperf/config](https://github.com/hyperf/config).

### Mengatur Konfigurasi

Konfigurasi dari `config/config.php`, `config/autoload/server.php`, dan folder `autoload` semuanya otomatis dipindai dan diinjeksi ke objek `Hyperf\Contract\ConfigInterface` pas service mulai. Proses ini ditangani oleh `Hyperf\Config\ConfigFactory` pas objek Config dibuat.

### Mendapatkan Konfigurasi

Config component punya tiga cara buat dapetin konfigurasi: lewat objek `Hyperf\Config\Config`, lewat annotation `#[Value]`, dan lewat fungsi `config(string $key, $default)`.

#### Mendapatkan Konfigurasi melalui Config Object

Metode ini butuh Anda punya instance dari objek `Config` dulu. Objek defaultnya `Hyperf\Config\Config`. Soal injeksi instance, silakan liat bab [Dependency Injection](id/di.md);

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Dapatkan konfigurasi yang sesuai dengan $key melalui metode get(string $key, $default): mixed. Nilai $key dapat diposisikan ke array tingkat lebih rendah melalui konektor ., dan $default adalah nilai default yang dikembalikan ketika nilai yang sesuai tidak ada.
$config->get($key, $default);
```

#### Mendapatkan Konfigurasi melalui Annotation `#[Value]`

Metode ini mengharuskan objek yang pake annotation ini dibuat oleh komponen [hyperf/di](https://github.com/hyperf/di). Soal injeksi instance, silakan liat bab [Dependency Injection](id/di.md). Di contoh ini, kita anggap `IndexController` adalah kelas `Controller` yang udah didefinisikan, dan kelas `Controller` harus dibuat oleh container `DI`;
String di dalam `#[Value]` sesuai dengan parameter `$key` di `$config->get($key)`. Ketika membuat instance dari objek ini, konfigurasi yang sesuai akan secara otomatis diinjeksikan ke dalam properti kelas yang telah didefinisikan.

```php
use Hyperf\Config\Annotation\Value;

class IndexController
{
    #[Value("config.key")]
    private $configValue;

    public function index()
    {
        return $this->configValue;
    }
}
```

#### Mendapatkan melalui Fungsi `config`

Di mana pun, Anda bisa dapetin konfigurasi lewat fungsi `config(string $key, $default)`, tapi cara ini berarti Anda bergantung banget sama komponen [hyperf/config](https://github.com/hyperf/config) dan [hyperf/support](https://github.com/hyperf/support).

### Mengecek Apakah Sebuah Konfigurasi Ada

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Cek apakah nilai $key yang sesuai ada di konfigurasi lewat method has(): bool. Nilai $key bisa ngarah ke array level bawah pake separator .
$config->has($key);
```

## Environment Variables

Menggunakan konfigurasi yang berbeda untuk lingkungan runtime yang berbeda adalah kebutuhan umum, misalnya, konfigurasi Redis untuk lingkungan testing dan production berbeda, dan konfigurasi lingkungan production tidak dapat di-commit ke sistem manajemen versi kode sumber untuk menghindari kebocoran informasi.

Hyperf punya solusi environment variable, pake [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) buat parsing dan fungsi `env()` buat baca nilainya, jadi kebutuhan ini gampang diatasi.

Dalam aplikasi Hyperf yang baru diinstal, direktori root-nya akan berisi file `.env.example`. Jika Hyperf diinstal melalui Composer, file ini akan secara otomatis disalin berdasarkan `.env.example` dan diberi nama `.env`. Jika tidak, Anda perlu mengubah nama file secara manual.

File `.env` Anda tidak boleh di-commit ke sistem manajemen versi kode sumber aplikasi, karena setiap pengembang/server yang menggunakan aplikasi Anda mungkin perlu memiliki konfigurasi lingkungan yang berbeda. Selain itu, ini dapat menyebabkan masalah keamanan yang serius jika seorang penyusup mendapatkan akses ke repositori kode sumber Anda, karena semua data sensitif akan terekspos.

> Semua variabel dalam file `.env` dapat ditimpa oleh environment variable eksternal (seperti variabel tingkat server, sistem, atau lingkungan Docker).

### Tipe Environment Variable

Semua variabel dalam file `.env` akan diparsing sebagai tipe string, jadi beberapa nilai yang dicadangkan disediakan untuk memungkinkan Anda mendapatkan tipe variabel yang lebih banyak dari fungsi `env()`:

| .env value | env() value |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

Jika Anda perlu menggunakan environment variable yang mengandung spasi atau karakter khusus lainnya, Anda dapat melakukannya dengan membungkus nilai dalam tanda kutip ganda, misalnya:

```dotenv
APP_NAME="Hyperf Skeleton"
```

### Membaca Environment Variable

Kami telah menyebutkan di atas bahwa environment variable dapat diperoleh melalui fungsi `env()`. Dalam pengembangan aplikasi, environment variable hanya boleh digunakan sebagai nilai untuk konfigurasi, dan nilai environment variable harus digunakan untuk menimpa nilai konfigurasi. Di lapisan aplikasi, Anda sebaiknya **hanya menggunakan konfigurasi**, daripada langsung menggunakan environment variable.
Berikut adalah contoh penggunaan yang wajar:

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## Menerbitkan Konfigurasi Komponen

Hyperf pake desain berbasis komponen. Abis nambahin komponen ke project skeleton, biasanya kita perlu bikin file konfigurasi buat komponen baru tersebut. Hyperf nyediain `mekanisme publish konfigurasi komponen`, tinggal jalanin perintah `vendor:publish` buat nge-deploy template konfigurasi dari komponen ke project skeleton.
Misalnya, kita mau nambahin komponen `hyperf/foo` (komponen ini gak beneran ada, cuma contoh) beserta file konfigurasinya. Abis `composer require hyperf/foo`, tinggal jalanin `php bin/hyperf.php vendor:publish hyperf/foo` buat nge-publish file konfigurasi ke folder `config/autoload` project skeleton. Isi yang di-publish udah ditentuin sama komponennya.

## Configuration Center

Hyperf nyediain dukungan konfigurasi eksternal buat sistem terdistribusi. Saat ini support `Apollo` (open-source dari Ctrip), Alibaba Cloud ACM, ETCD, Nacos, dan Zookeeper sebagai configuration center.
Soal pemakaian configuration center, bakal dibahas di bab [Configuration Center](id/config-center.md).
