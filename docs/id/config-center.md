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

### Unified Access Layer (Lapisan Akses Terpadu) Configuration Center

```bash
composer require hyperf/config-center
```

### Menggunakan Apollo

```bash
composer require hyperf/config-apollo
```

### Menggunakan Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

### Menggunakan Etcd

```bash
composer require hyperf/config-etcd
```

### Menggunakan Nacos

```bash
composer require hyperf/config-nacos
```

#### gRPC Bidirectional Streaming (Aliran Dua Arah)

Nacos v2.0 mendukung gRPC. Anda dapat mengikuti langkah-langkah di bawah ini untuk menggunakannya.

- Pertama, kita menginstal komponen yang diperlukan:

```shell
composer require "hyperf/http2-client:3.1.*"
composer require "hyperf/grpc:3.1.*"
```

- Modifikasi konfigurasi

Ubah `config_center.drivers.nacos.client.grpc.enable` menjadi `true`, spesifikasinya adalah sebagai berikut:

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigApollo\PullMode;
use Hyperf\ConfigCenter\Mode;

return [
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    'driver' => env('CONFIG_CENTER_DRIVER', 'nacos'),
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            'default_key' => 'nacos_config',
            'listener_config' => [
                'nacos_config' => [
                    'tenant' => 'tenant', // sesuai dengan service.namespaceId
                    'data_id' => 'hyperf-service-config',
                    'group' => 'DEFAULT_GROUP',
                ],
            ],
            'client' => [
                // url server nacos seperti https://nacos.hyperf.io, Prioritasnya lebih tinggi daripada host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
                // Hanya mendukung untuk nacos v2.
                'grpc' => [
                    'enable' => true,
                    'heartbeat' => 10,
                ],
            ],
        ],
    ],
];
```

- Selanjutnya, cukup jalankan layanannya.

### Menggunakan Zookeeper

```bash
composer require hyperf/config-zookeeper
```

## Mengakses Configuration Center

### File Konfigurasi

```php
<?php

declare(strict_types=1);

use Hyperf\ConfigCenter\Mode;

return [
    // Apakah akan mengaktifkan configuration center
    'enable' => (bool) env('CONFIG_CENTER_ENABLE', true),
    // Jenis driver yang digunakan, sesuai dengan key di bawah drivers pada level konfigurasi yang sama
    'driver' => env('CONFIG_CENTER_DRIVER', 'apollo'),
    // Mode berjalan configuration center, PROCESS direkomendasikan untuk model multi-proses, COROUTINE direkomendasikan untuk model proses tunggal
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'apollo' => [
            'driver' => Hyperf\ConfigApollo\ApolloDriver::class,
            // Apollo Server
            'server' => 'http://127.0.0.1:9080',
            // AppId Anda
            'appid' => 'test',
            // Cluster di mana aplikasi saat ini berada
            'cluster' => 'default',
            // Namespace yang perlu diakses oleh aplikasi saat ini, bisa dikonfigurasi beberapa sekaligus
            'namespaces' => [
                'application',
            ],
            // Interval pembaruan konfigurasi (detik)
            'interval' => 5,
            // Strict mode. Ketika false, nilai konfigurasi yang ditarik selalu bertipe string, ketika true, nilai konfigurasi akan dikonversi ke tipe data dari nilai konfigurasi asli
            'strict_mode' => false,
            // IP Klien
            'client_ip' => \Hyperf\Support\Network::ip(),
            // Timeout untuk pull (menarik) konfigurasi
            'pullTimeout' => 10,
            // Interval timeout untuk pull konfigurasi
            'interval_timeout' => 1,
        ],
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            // Mode penggabungan konfigurasi, mendukung penimpaan (overwrite) dan penggabungan (merge)
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            // Jika key pemetaan terkait tidak diatur, key default ini akan digunakan
            'default_key' => 'nacos_config',
            'listener_config' => [
                // dataId, group, tenant, type, content
                // KEY konfigurasi yang dipetakan => Konfigurasi aktual di dalam Nacos
                'nacos_config' => [
                    'tenant' => 'tenant', // sesuai dengan service.namespaceId
                    'data_id' => 'hyperf-service-config',
                    'group' => 'DEFAULT_GROUP',
                ],
                'nacos_config.data' => [
                    'data_id' => 'hyperf-service-config-yml',
                    'group' => 'DEFAULT_GROUP',
                    'type' => 'yml',
                ],
            ],
            'client' => [
                // url server nacos seperti https://nacos.hyperf.io, Prioritasnya lebih tinggi daripada host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
            ],
        ],
        'aliyun_acm' => [
            'driver' => Hyperf\ConfigAliyunAcm\AliyunAcmDriver::class,
            // Interval pembaruan konfigurasi (detik)
            'interval' => 5,
            // Alamat endpoint ACM Aliyun, tergantung pada availability zone Anda
            'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
            // Namespace yang perlu diakses oleh aplikasi saat ini
            'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
            // Data ID konfigurasi Anda
            'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
            // Group konfigurasi Anda
            'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
            // Access Key akun Aliyun Anda
            'access_key' => env('ALIYUN_ACM_AK', ''),
            // Secret Key akun Aliyun Anda
            'secret_key' => env('ALIYUN_ACM_SK', ''),
            'ecs_ram_role' => env('ALIYUN_ACM_RAM_ROLE', ''),
        ],
        'etcd' => [
            'driver' => Hyperf\ConfigEtcd\EtcdDriver::class,
            'packer' => Hyperf\Codec\Packer\JsonPacker::class,
            // Awalan (prefix) data yang perlu disinkronkan
            'namespaces' => [
                '/application',
            ],
            // Hubungan pemetaan antara `Etcd` dan `Config`. Jika `key` tidak ada dalam pemetaan, maka tidak akan disinkronkan ke dalam `Config`
            'mapping' => [
                // etcd key => config key
                '/application/test' => 'test',
            ],
            // Interval pembaruan konfigurasi (detik)
            'interval' => 5,
            'client' => [
                # Etcd Client
                'uri' => 'http://127.0.0.1:2379',
                'version' => 'v3beta',
                'options' => [
                    'timeout' => 10,
                ],
            ],
        ],
        'zookeeper' => [
            'driver' => Hyperf\ConfigZookeeper\ZookeeperDriver::class,
            'server' => env('ZOOKEEPER_SERVER', '127.0.0.1:2181'),
            'path' => env('ZOOKEEPER_CONFIG_PATH', '/conf'),
            'interval' => 5,
        ],
    ],
];
```

Jika file konfigurasi tidak ada, Anda dapat menjalankan perintah `php bin/hyperf.php vendor:publish hyperf/config-center` untuk membuatnya.

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
