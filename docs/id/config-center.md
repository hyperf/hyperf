Hyperf menyediakan dukungan konfigurasi eksternal untuk sistem terdistribusi,
yang diadaptasi secara default:

- [ctripcorp/apollo](https://github.com/ctripcorp/apollo) Proyek open source oleh
  Ctrip, didukung oleh komponen [hyperf/config-apollo](https://github.com/hyperf/config-apollo).
- Aliyun menyediakan layanan configuration center gratis [ACM (Application Config Manager)](https://help.aliyun.com/product/59604.html)
  yang didukung oleh komponen [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm).

## Mengapa menggunakan Configuration Center?

Seiring berkembangnya layanan dan peningkatan arsitektur microservice, jumlah
layanan serta konfigurasi aplikasi (berbagai microservice, alamat server,
parameter) yang bertambah membuat metode file konfigurasi tradisional atau
database tidak lagi memadai. Kebutuhan developer akan manajemen konfigurasi
juga dapat mencakup manajemen hak akses ACL, manajemen versi konfigurasi dan
rollback, verifikasi format, grayscale publishing konfigurasi, isolasi
konfigurasi cluster, dan lain-lain, serta:

- Security: Konfigurasi disimpan bersama dengan source code dalam sistem
  manajemen versi, sehingga berisiko menyebabkan kebocoran konfigurasi.
- Timeliness: Saat mengubah konfigurasi, setiap server perlu memperbarui dan
  merestart layanan untuk setiap aplikasi.
- Limitations: Penyesuaian dinamis tidak didukung, misalnya switch log, switch
  fitur, dll.

Oleh karena itu, kita dapat mengelola konfigurasi terkait secara ilmiah
menggunakan configuration center.

## Instalasi

### Apollo

```bash
composer require hyperf/config-apollo
```

### Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

## Menggunakan Apollo

Jika Anda belum mengganti komponen konfigurasi default dan masih menggunakan
komponen [hyperf/config](https://github.com/hyperf/config), mengintegrasikan
Apollo Configuration Center akan sangat mudah.
- Pasang komponen [hyperf/config-apollo](https://github.com/hyperf/config-apollo)
  melalui Composer dengan menjalankan perintah `composer require hyperf/config-apollo`.
- Tambahkan file konfigurasi `apollo.php` ke direktori `config/autoload`.
  Konfigurasinya adalah sebagai berikut:

```php
<?php
return [
    // Whether to enable the process of the configuration center. When true, a ConfigFetcherProcess process is automatically started to update the configuration
    'enable' => true,
    // Apollo Server
    'server' => 'http://127.0.0.1:8080',
    // Your AppId
    'appid' => 'test',
    // The cluster where the current application is located
    'cluster' => 'default',
    // Namespace that the current application needs to access, can be configured multiple namespcaes
    'namespaces' => [
        'application',
    ],
    // Strict mode. When the value is false, the configuration value that pulled from Apollo will always is string type, when the value is true, the configuration value will transfer to the suitable type according to the original value type on config container.
    'strict_mode' => false,
    // The interval of update configuration (seconds)
    'interval' => 5,
];
```

## Menggunakan Aliyun ACM

Mengakses Aliyun ACM Configuration Center semudah Apollo, cukup dua langkah.
- Jalankan perintah `composer require hyperf/config-aliyun-acm` menggunakan
  Composer untuk menginstal [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm).
- Tambahkan file konfigurasi `aliyun_acm.php` ke direktori `config/autoload`.
  Konfigurasinya adalah sebagai berikut:

```php
<?php
return [
    // Whether to enable the process of the configuration center. When true, a ConfigFetcherProcess process is automatically started to update the configuration
    'enable' => true,
    // The interval of update configuration (seconds)
    'interval' => 5,
    // ACM endpoint address, depending on your Availability Zone
    'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    // Namespace that the current application needs to access
    'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
    // The Data ID of your configuration
    'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
    // The Group of your configuration
    'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
    // Your Access Key of aliyun account
    'access_key' => env('ALIYUN_ACM_AK', ''),
    // Your Secret Key of aliyun account
    'secret_key' => env('ALIYUN_ACM_SK', ''),
];
```

## Ruang Lingkup Pembaruan Konfigurasi

Pada implementasi fitur default, sebuah process `ConfigFetcherProcess` menarik
konfigurasi `namespace` yang sesuai dari Configuration Center berdasarkan
`interval` yang dikonfigurasi, lalu meneruskan konfigurasi baru tersebut ke
setiap worker melalui IPC communication, kemudian memperbarui objek yang sesuai
dengan `Hyperf\Contract\ConfigInterface`.

Perlu dicatat bahwa konfigurasi yang diperbarui hanya akan mengupdate objek
`Config`, sehingga ini hanya berlaku untuk konfigurasi application layer atau
business layer. Ini tidak melibatkan perubahan konfigurasi framework layer
karena perubahan konfigurasi pada framework layer memerlukan restart layanan.
Jika Anda memiliki kebutuhan seperti itu, hal ini dapat dicapai dengan
mengimplementasikan `ConfigFetcherProcess` secara mandiri.

## Event Pembaruan Konfigurasi

Selama configuration center berjalan, jika terjadi perubahan konfigurasi,
event `Hyperf\ConfigCenter\Event\ConfigChanged` akan dipicu secara
berdampingan. Anda dapat memantau event ini sesuai dengan kebutuhan Anda.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\ConfigCenter\Event\ConfigChanged;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ConfigChanged::class,
        ];
    }

    public function process(object $event)
    {
        var_dump($event);
    }
}
```
