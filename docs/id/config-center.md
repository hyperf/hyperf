# Pendahuluan

Hyperf menyediakan dukungan untuk konfigurasi eksternal dalam sistem terdistribusi, dan secara native mendukung:

- [ctripcorp/apollo](https://github.com/ctripcorp/apollo), proyek open-source oleh Trip.com, didukung oleh komponen [hyperf/config-apollo](https://github.com/hyperf/config-apollo).
- [Application Config Manager (ACM)](https://help.aliyun.com/product/59604.html), layanan configuration center gratis yang disediakan oleh Alibaba Cloud, didukung oleh komponen [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm).
- ETCD
- Nacos
- Zookeeper

## Mengapa Menggunakan Configuration Center?

Seiring perkembangan bisnis dan upgrade arsitektur microservice, jumlah service dan konfigurasi aplikasi semakin bertambah (berbagai microservice, alamat server, parameter). Metode file konfigurasi tradisional dan metode database mungkin sudah tidak memenuhi kebutuhan developer dalam manajemen konfigurasi. Pada saat yang sama, manajemen konfigurasi mungkin melibatkan manajemen permission ACL, manajemen versi konfigurasi dan rollback, validasi format, canary release konfigurasi, isolasi konfigurasi cluster, dan lain-lain, serta:

- Keamanan: Konfigurasi disimpan di version control system bersama source code, yang dapat dengan mudah menyebabkan kebocoran konfigurasi.
- Ketepatan waktu: Mengubah konfigurasi memerlukan modifikasi di setiap server dan setiap aplikasi serta restart service.
- Keterbatasan: Tidak mendukung penyesuaian dinamis, seperti log switch, feature switch, dll.

Oleh karena itu, kita bisa menggunakan configuration center untuk mengelola konfigurasi terkait secara terpusat dengan cara yang lebih ilmiah.

## Instalasi

### Unified Configuration Center Access Layer

```bash
composer require hyperf/config-center
```

### Untuk Apollo

```bash
composer require hyperf/config-apollo
```

### Untuk Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

### Untuk Etcd

```bash
composer require hyperf/config-etcd
```

### Untuk Nacos

```bash
composer require hyperf/config-nacos
```

#### gRPC Bidirectional Stream

Configuration center Nacos tradisional didasarkan pada short polling untuk sinkronisasi konfigurasi, yang menyebabkan service tidak bisa mendapatkan konfigurasi terbaru dalam interval polling. `Nacos V2` menambahkan dukungan untuk gRPC bidirectional streams. Jika Anda ingin Nacos mendorong perubahan konfigurasi ke service terkait secara tepat waktu setelah menemukannya, Anda dapat mengaktifkan fungsi gRPC bidirectional stream dengan langkah-langkah berikut.

- Pertama, install komponen yang diperlukan:

```shell
composer require "hyperf/http2-client:3.1.*"
composer require "hyperf/grpc:3.1.*"
```

- Ubah konfigurasi:

Ubah `config_center.drivers.nacos.client.grpc.enable` menjadi `true`, sebagai berikut:

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
                // url server nacos seperti https://nacos.hyperf.io, prioritas lebih tinggi dari host:port
                // 'uri' => '',
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => null,
                'password' => null,
                'guzzle' => [
                    'config' => null,
                ],
                // Hanya mendukung nacos v2.
                'grpc' => [
                    'enable' => true,
                    'heartbeat' => 10,
                ],
            ],
        ],
    ],
];

```

- Selanjutnya, jalankan service.

### Untuk Zookeeper

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
    // Tipe driver yang digunakan, sesuai dengan key di bawah konfigurasi drivers pada level yang sama
    'driver' => env('CONFIG_CENTER_DRIVER', 'apollo'),
    // Mode operasi configuration center, mode PROCESS direkomendasikan untuk model multi-process, mode COROUTINE direkomendasikan untuk model single-process
    'mode' => env('CONFIG_CENTER_MODE', Mode::PROCESS),
    'drivers' => [
        'apollo' => [
            'driver' => Hyperf\ConfigApollo\ApolloDriver::class,
            // Apollo Server
            'server' => 'http://127.0.0.1:9080',
            // AppId Anda
            'appid' => 'test',
            // Cluster tempat aplikasi saat ini berada
            'cluster' => 'default',
            // Namespace yang perlu diakses oleh aplikasi saat ini, bisa dikonfigurasi banyak
            'namespaces' => [
                'application',
            ],
            // Interval pembaruan konfigurasi (detik)
            'interval' => 5,
            // Mode ketat, ketika false nilai konfigurasi yang ditarik semuanya bertipe string; ketika true nilai konfigurasi yang ditarik akan dikonversi ke tipe data asli
            'strict_mode' => false,
            // IP Klien
            'client_ip' => \Hyperf\Support\Network::ip(),
            // Timeout tarik konfigurasi
            'pullTimeout' => 10,
            // Interval tarik konfigurasi
            'interval_timeout' => 1,
        ],
        'nacos' => [
            'driver' => Hyperf\ConfigNacos\NacosDriver::class,
            // Metode penggabungan konfigurasi, mendukung overwrite dan merge
            'merge_mode' => Hyperf\ConfigNacos\Constants::CONFIG_MERGE_OVERWRITE,
            'interval' => 3,
            // Jika key pemetaan yang sesuai tidak diatur, gunakan default key
            'default_key' => 'nacos_config',
            'listener_config' => [
                // dataId, group, tenant, type, content
                // KEY konfigurasi yang dipetakan => Konfigurasi aktual di Nacos
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
                // url server nacos seperti https://nacos.hyperf.io, prioritas lebih tinggi dari host:port
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
            // Endpoint Aliyun ACM, tergantung pada availability zone Anda
            'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
            // Namespace yang perlu diakses oleh aplikasi saat ini
            'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
            // Data ID sesuai dengan konfigurasi Anda
            'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
            // Group sesuai dengan konfigurasi Anda
            'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
            // Access Key untuk akun Aliyun Anda
            'access_key' => env('ALIYUN_ACM_AK', ''),
            // Secret Key untuk akun Aliyun Anda
            'secret_key' => env('ALIYUN_ACM_SK', ''),
            'ecs_ram_role' => env('ALIYUN_ACM_RAM_ROLE', ''),
        ],
        'etcd' => [
            'driver' => Hyperf\ConfigEtcd\EtcdDriver::class,
            'packer' => Hyperf\Codec\Packer\JsonPacker::class,
            // Prefix dari data yang akan disinkronkan
            'namespaces' => [
                '/application',
            ],
            // Hubungan pemetaan antara `Etcd` dan `Config`. Key yang tidak ada dalam pemetaan tidak akan disinkronkan ke `Config`
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

Jika file konfigurasi tidak ada, Anda bisa menjalankan perintah `php bin/hyperf.php vendor:publish hyperf/config-center` untuk membuatnya.

## Cakupan Pembaruan Konfigurasi

Dalam implementasi default, sebuah proses `ConfigFetcherProcess` menarik konfigurasi dari `namespace` yang sesuai dari Configuration Center Server berdasarkan `interval` yang dikonfigurasi, dan mengirimkan konfigurasi baru ke setiap Worker melalui komunikasi IPC, memperbaruinya ke dalam objek yang sesuai di dalam `Hyperf\Contract\ConfigInterface`.

Perlu dicatat bahwa konfigurasi yang diperbarui hanya akan memperbarui objek `Config`, sehingga ini terbatas pada konfigurasi level aplikasi atau level bisnis. Ini tidak melibatkan perubahan konfigurasi di level framework karena perubahan konfigurasi framework memerlukan restart service. Jika Anda memiliki kebutuhan seperti itu, Anda juga dapat mencapainya dengan mengimplementasikan `ConfigFetcherProcess` sendiri.

## Event Pembaruan Konfigurasi

Selama operasi configuration center, ketika konfigurasi berubah, ia akan memicu event `Hyperf\ConfigCenter\Event\ConfigChanged`. Anda dapat mendengarkan event ini untuk memenuhi kebutuhan Anda.

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
