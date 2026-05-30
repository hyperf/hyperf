# Konfigurasi

Ketika Anda menggunakan proyek yang dibuat oleh proyek hyperf/hyperf-skeleton,
semua file konfigurasi Hyperf berada di folder `config` di bawah direktori
root, dan setiap opsi memiliki petunjuk penjelasan, Anda selalu dapat
memeriksa dan membiasakan diri dengan opsi yang tersedia.

# Instalasi

```bash
composer require hyperf/config
```

# Struktur File Konfigurasi

Struktur berikut hanya merupakan struktur dalam kasus konfigurasi default yang
disediakan oleh Hyperf-Skeleton, dan situasi sebenarnya akan bervariasi
tergantung pada komponen yang bergantung atau digunakan.
```
config
├── autoload // File konfigurasi di folder ini akan dimuat oleh komponen konfigurasi itu sendiri, dan nama file di folder ini akan menjadi key tingkat pertama.
│   ├── amqp.php  // Digunakan untuk mengelola komponen AMQP
│   ├── annotations.php // Digunakan untuk mengelola Annotation
│   ├── apollo.php // Digunakan untuk mengelola Configuration Center Apollo
│   ├── aspects.php // Digunakan untuk mengelola Aspect dari AOP
│   ├── async_queue.php // Digunakan untuk mengelola komponen Async-Queue
│   ├── cache.php // Digunakan untuk mengelola komponen Cache
│   ├── commands.php // Digunakan untuk mengelola Custom Command
│   ├── consul.php // Digunakan untuk mengelola Consul Client
│   ├── databases.php // Digunakan untuk mengelola Database
│   ├── dependencies.php // Digunakan untuk mengelola hubungan dependensi dari DI
│   ├── devtool.php // Digunakan untuk mengelola Dev-Tool
│   ├── exceptions.php // Digunakan untuk mengelola Exception Handler
│   ├── listeners.php // Digunakan untuk mengelola Event Listener
│   ├── logger.php // Digunakan untuk mengelola Logger
│   ├── middlewares.php // Digunakan untuk mengelola Middleware
│   ├── opentracing.php // Digunakan untuk mengelola Open-Tracing
│   ├── processes.php // Digunakan untuk mengelola Custom Process
│   ├── redis.php // Digunakan untuk mengelola Redis Client
│   └── server.php // Digunakan untuk mengelola Server
├── config.php // Konfigurasi untuk mengelola pengguna atau framework, seperti konfigurasi yang relatif independen juga dapat ditempatkan di folder autoload
├── container.php // Bertanggung jawab atas inisialisasi container, berjalan sebagai file konfigurasi dan pada akhirnya mengembalikan objek Psr\Container\ContainerInterface
└── routes.php // Digunakan untuk mengelola Routing
```

## Hubungan antara `config.php` dan file konfigurasi di folder `autoload`

File konfigurasi di folder `autoload` dan `config.php` akan dipindai dan
dimasukkan ke dalam objek `Hyperf\Contract\ConfigInterface` yang sesuai saat
server dimulai. Struktur konfigurasi yang terbentuk adalah array besar berisi
pasangan key-value. Perbedaan antara kedua bentuk konfigurasi tersebut adalah:
nama file konfigurasi di `autoload` akan ada sebagai key lapisan pertama,
sedangkan bagian dalam `config.php` akan didefinisikan secara langsung pada
lapisan pertama. Kita akan menggunakan contoh berikut untuk mendemonstrasikannya.

Mari kita asumsikan ada file `config/autoload/client.php` dengan isi berikut:

```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```

Kemudian, jika kita ingin mendapatkan nilai `timeout` yang sesuai, key-nya
adalah `client.request.timeout`;

Kita asumsikan ingin mendapatkan hasil yang sama dengan key yang sama, tetapi
konfigurasinya ditulis dalam file `config/config.php`, maka isi file tersebut
harus terlihat seperti ini:

```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## Menggunakan Komponen Config Hyperf

Komponen ini adalah komponen konfigurasi default resmi yang diimplementasikan
untuk interface `Hyperf\Contract\ConfigInterface`, yang didefinisikan oleh
komponen [hyperf/config](https://github.com/hyperf/config). Objek
`Hyperf\Config\Config` di-bind ke interface tersebut oleh ConfigProvider dari
komponen.

### Menetapkan Nilai Konfigurasi

Konfigurasi dalam file `config/config.php`, `config/autoload/server.php`, dan
folder `autoload` dapat dipindai dan dimasukkan ke dalam objek
`Hyperf\Contract\ConfigInterface` yang sesuai saat server dimulai. Proses ini
dilakukan oleh `Hyperf\Config\ConfigFactory` saat objek Config diinstansiasi.

### Mendapatkan Nilai Konfigurasi

Komponen Config menyediakan tiga cara untuk mendapatkan nilai konfigurasi,
yaitu diperoleh melalui objek `Hyperf\Config\Config`, diperoleh melalui
annotation `#[Value]`, dan diperoleh melalui fungsi `config(string $key, $default)`.

#### Mendapatkan Nilai Konfigurasi melalui Objek Config

Cara ini mengharuskan Anda sudah memiliki instansi dari objek `Config`. Objek
default-nya adalah `Hyperf\Config\Config`. Untuk detail tentang instansi
injection, silakan merujuk pada bab [Dependency Injection](id/di.md).

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Get the configuration corresponding to $key by get(string $key, $default): mixed method, the $key value can be positioned to the subordinate array by the . connector, and $default is the default value returned when the corresponding value does not exist.
$config->get($key, $default);
```

#### Mendapatkan Konfigurasi dengan Annotation `#[Value]`

Cara ini mengharuskan objek dibuat oleh komponen
[hyperf/di](https://github.com/hyperf/di). Detail tentang instansi injection
dapat ditemukan di bab [Dependency Injection](id/di.md). Dalam contoh ini,
kita berasumsi bahwa `IndexController` adalah kelas `Controller` yang telah
didefinisikan, dan kelas `Controller` tersebut harus dibuat oleh container `DI`;

String di dalam `#[Value()]` sesuai dengan parameter `$key` di
`$config->get($key)`. Ketika instansi objek dibuat, konfigurasi yang sesuai
akan secara otomatis dimasukkan ke dalam properti kelas yang ditentukan.

```php
<?php
use Hyperf\Config\Annotation\Value;

class IndexController
{
    
    #[Value(key: "config.key")]
    private $configValue;
    
    public function index()
    {
        return $this->configValue;
    }
    
}
```

#### Mendapatkan Konfigurasi dengan fungsi config()

Konfigurasi yang sesuai dapat diperoleh dari fungsi
`config(string $key, $default)` di mana saja, tetapi cara penggunaan ini
berarti aplikasi Anda sangat bergantung pada komponen
[hyperf/config](https://github.com/hyperf/config) dan
[hyperf/support](https://github.com/hyperf/support).

### Menentukan Apakah Konfigurasi Ada

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// The has(): bool method is used to determine whether the corresponding $key value exists in the configuration, and the $key value can be mapped to the subordinate array by the . connector.
$config->has($key);
```

## Variabel Lingkungan (Environment Variable)

Merupakan kebutuhan umum untuk menggunakan konfigurasi yang berbeda untuk
lingkungan operasi yang berbeda. Misalnya, konfigurasi Redis untuk lingkungan
pengujian (test) dan lingkungan produksi (production) berbeda, dan konfigurasi
lingkungan produksi tidak boleh dikirimkan ke sistem manajemen versi kode sumber
(source code version control) untuk menghindari kebocoran informasi.

Di Hyperf kami menyediakan solusi untuk variabel lingkungan, menggunakan
fungsi penguraian variabel lingkungan yang disediakan oleh
[vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) dan fungsi `env()`
untuk mendapatkan lingkungan tersebut. Kebutuhan ini cukup mudah untuk
diselesaikan.

Di aplikasi Hyperf yang baru diinstal, direktori root-nya akan berisi file
`.env.example`. Dalam kasus Hyperf yang diinstal melalui Composer, Composer
akan secara otomatis menyalin file baru berdasarkan `.env.example` dan
menamainya menjadi `.env`. Jika tidak, Anda perlu mengubah nama file tersebut
secara manual.

File `.env` Anda tidak boleh dimasukkan ke dalam sistem manajemen versi kode
sumber aplikasi Anda, karena setiap developer/server yang menggunakan aplikasi
Anda mungkin perlu memiliki konfigurasi lingkungan yang berbeda. Selain itu,
jika penyusup mendapatkan akses ke repositori kode sumber Anda, hal ini dapat
menyebabkan masalah keamanan yang serius, karena data sensitif akan langsung
terlihat jelas.

> Semua variabel di file `.env` dapat ditimpa oleh variabel lingkungan
> eksternal (seperti variabel lingkungan tingkat server, tingkat sistem, atau
> Docker).

### Tipe Variabel Lingkungan

Semua variabel di file `.env` diurai sebagai tipe string, sehingga beberapa
nilai khusus disediakan untuk memungkinkan Anda mendapatkan lebih banyak tipe
variabel dari fungsi `env()`:

| Nilai .env | Nilai env() |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

Jika Anda perlu menggunakan variabel lingkungan yang mengandung spasi, Anda
dapat melakukannya dengan membungkus nilai tersebut dalam tanda kutip ganda,
seperti:

```dotenv
APP_NAME="Hyperf Skeleton"
```

### Mendapatkan Variabel Lingkungan

Kami juga menyebutkan di atas bahwa variabel lingkungan dapat diperoleh dengan
fungsi `env()`. Dalam pengembangan aplikasi, variabel lingkungan hanya boleh
digunakan sebagai nilai dari konfigurasi, dan nilai variabel lingkungan
digunakan untuk menimpa nilai yang dikonfigurasi. **Hanya gunakan konfigurasi**
daripada menggunakan variabel lingkungan secara langsung.

Mari kita berikan contoh yang masuk akal:

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## Configuration Center

Hyperf menyediakan dukungan konfigurasi eksternal untuk sistem terdistribusi.
Secara default, kami menawarkan proyek open source dari Ctrip yaitu
[ctripcorp/apollo](https://github.com/ctripcorp/apollo), yang didukung oleh
komponen [hyperf/config-apollo](https://github.com/hyperf/config-apollo).

Detail tentang penggunaan configuration center dijelaskan dalam bab
[Configuration Center](id/config-center.md).
