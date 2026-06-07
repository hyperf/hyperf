# Tracing

Dalam skenario microservice, sebuah request bisnis tunggal bisa melintasi minimal 3-4 service, dan maksimal puluhan bahkan lebih. Melakukan debugging masalah dalam arsitektur seperti ini sangatlah sulit. Kita memerlukan sistem call chain tracing untuk menampilkan tautan panggilan service secara dinamis, sehingga kita dapat dengan cepat menemukan titik masalah, dan juga menyesuaikan service berdasarkan informasi tautan tersebut.

Di `Hyperf`, kami menyediakan komponen [hyperf/tracer](https://github.com/hyperf/tracer) untuk melacak dan menganalisis panggilan di seluruh network request. Saat ini, komponen ini terintegrasi dengan sistem [Zipkin](https://zipkin.io/) dan [Jaeger](https://www.jaegertracing.io/) berdasarkan protokol [OpenTracing](https://opentracing.io). Pengguna juga dapat membuat implementasi kustom berdasarkan protokol OpenTracing.

## Instalasi

### Install komponen melalui Composer

```bash
composer require hyperf/tracer
```

Komponen [hyperf/tracer](https://github.com/hyperf/tracer) secara default menginstal dependensi [Zipkin](https://zipkin.io/). Jika Anda ingin menggunakan [Jaeger](https://www.jaegertracing.io/), Anda juga perlu menjalankan perintah berikut untuk menginstal dependensi yang sesuai:

```bash
composer require jonahgeorge/jaeger-client-php
```

### Tambahkan konfigurasi komponen

Jika file belum ada, Anda dapat menjalankan perintah berikut untuk menambahkan file konfigurasi `config/autoload/opentracing.php`:

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## Penggunaan

### Konfigurasi

#### Mengatur switch tracing

Secara default, monitoring atau pemrosesan `AOP` aspect disediakan untuk panggilan `Guzzle HTTP`, panggilan `Redis`, dan panggilan `DB` untuk mengimplementasikan propagasi dan tracing call chain. Secara default, tracing ini tidak diaktifkan. Anda perlu mengaktifkan tracing untuk panggilan remote tertentu dengan mengubah switch di item `enable` dalam file konfigurasi `config/autoload/opentracing.php`.

```php
<?php

return [
    'enable' => [
        // Mengaktifkan atau menonaktifkan tracing untuk panggilan Guzzle HTTP
        'guzzle' => false,
        // Mengaktifkan atau menonaktifkan tracing untuk panggilan Redis
        'redis' => false,
        // Mengaktifkan atau menonaktifkan tracing untuk panggilan DB
        'db' => false,
    ],
];
```

Sebelum memulai tracing, kita juga perlu memilih driver Tracer yang akan digunakan dan mengkonfigurasi Tracer.

#### Memilih driver Tracer

Nilai yang sesuai dengan `default` dalam file konfigurasi adalah nama driver yang digunakan. Konfigurasi spesifik dari driver didefinisikan di bawah item `tracer`, menggunakan driver yang sama sebagai `key`.

```php
<?php

return [
    // Memilih default Tracer driver, nama Tracer yang dipilih sesuai dengan key yang didefinisikan di bawah tracers
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // Konfigurasi lainnya tidak disebutkan di sini
    'enable' => [],

    'tracer' => [
        // Konfigurasi Zipkin
        'staging_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Konfigurasi Zipkin lainnya
        'producton_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Konfigurasi Jaeger
        'jaeger' => [
            'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
        ],
    ]
];
```

Perhatikan, seperti yang ditunjukkan dalam contoh konfigurasi, Anda dapat mengkonfigurasi beberapa driver Zipkin atau driver Jaeger. Meskipun mereka menggunakan sistem dasar yang sama, konfigurasi spesifik mereka bisa berbeda. Skenario umum adalah kita ingin 100% sampling di lingkungan staging, tetapi 1% sampling di lingkungan production. Anda dapat mengkonfigurasi dua driver dan kemudian memilih driver yang berbeda berdasarkan environment variable di item `default`.

#### Mengkonfigurasi Zipkin

Saat menggunakan Zipkin, tambahkan konfigurasi spesifik Zipkin ke item `tracer` dalam file konfigurasi.

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // Memilih default Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // Demonstrasi tidak memperluas konfigurasi di dalam enable
    'enable' => [],

    'tracer' => [
        // Konfigurasi driver Zipkin
        'zipkin' => [
            // Konfigurasi aplikasi saat ini
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // Jika ipv4 dan ipv6 kosong, komponen akan mendeteksinya secara otomatis dari Server
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // URL endpoint service Zipkin
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // Timeout request dalam detik
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // Sampler, default melacak semua request
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
    ],
];
```

#### Mengkonfigurasi Jaeger

Saat menggunakan Jaeger, tambahkan konfigurasi spesifik Jaeger ke item `tracer` dalam file konfigurasi.

```php
<?php
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use const Jaeger\SAMPLER_TYPE_CONST;

return [
    // Memilih default Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // Demonstrasi tidak memperluas konfigurasi di dalam enable
    'enable' => [],

    'tracer' => [
        // Konfigurasi driver Jaeger
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // Nama proyek
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // Sampler, default melacak semua request
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // Reporting agent
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

Untuk konfigurasi lebih lanjut tentang Jaeger, Anda bisa melihatnya [[di sini](https://github.com/jonahgeorge/jaeger-client-php)].

#### Mengkonfigurasi switch tracing JsonRPC

JsonRPC link tracing tidak ada dalam konfigurasi terpadu dan saat ini merupakan fitur versi `Beta`.

Kita hanya perlu mengkonfigurasi `aspects.php` dan menambahkan `Aspect` berikut untuk mengaktifkannya.

> Tip: Jangan lupa untuk menambahkan TraceMiddleware yang sesuai di sisi peer.

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### Mengkonfigurasi switch tracing Coroutine

Coroutine link tracing tidak ada dalam konfigurasi terpadu dan merupakan fitur opsional.

Kita hanya perlu mengkonfigurasi `aspects.php` dan menambahkan `Aspect` berikut untuk mengaktifkannya.

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### Mengkonfigurasi middleware atau listener

Setelah mengkonfigurasi driver, untuk mengumpulkan informasi, Anda juga perlu mengkonfigurasi middleware atau event listener siklus request untuk mengaktifkan fungsi pengumpulan.

- Tambahkan middleware

Buka file `config/autoload/middlewares.php` dan aktifkan middleware di node `http`.

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
];
```

- Atau tambahkan listener

Buka file `config/autoload/listeners.php` dan tambahkan listener.

```php
<?php

declare(strict_types=1);

return [
    \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### Mengkonfigurasi Span tag

Untuk beberapa nama Span Tag yang dikumpulkan secara otomatis oleh Hyperf, Anda dapat mengubah nama yang sesuai dengan mengubah konfigurasi Span Tag. Anda hanya perlu menambahkan konfigurasi `tags` di file konfigurasi `config/autoload/opentracing.php`. Lihat konfigurasi di bawah. Jika item konfigurasi ada, nilai item konfigurasi tersebut yang akan digunakan. Jika item konfigurasi tidak ada, nilai default komponen yang akan digunakan.

```php
return [
    'tags' => [
        // HTTP Client (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis Client
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // Database Client (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### Mengganti Sampler

Sampler default mencatat call chain untuk semua request, yang memiliki dampak tertentu pada performa, terutama penggunaan memori. Oleh karena itu, kita hanya perlu melacak call chain ketika kita menginginkannya. Maka kita perlu mengganti sampler. Menggantinya juga sangat sederhana. Mengambil Zipkin sebagai contoh, cukup ubah nilai yang sesuai dengan item konfigurasi `opentracing.zipkin.sampler` ke instance objek sampler Anda, selama objek sampler Anda mengimplementasikan class interface `Zipkin\Sampler`.

### Mengakses Alibaba Cloud Link Tracing Service

Ketika kita menggunakan layanan link tracing Alibaba Cloud, karena pihak server juga mendukung protokol `Zipkin`, kita bisa langsung mengubah `endpoint_url` di file konfigurasi `config/autoload/opentracing.php` ke alamat `region` Alibaba Cloud Anda yang sesuai. Alamat spesifik dapat diperoleh di layanan link tracing Alibaba Cloud. Untuk detail lebih lanjut, silakan merujuk ke [Dokumen Bantuan Alibaba Cloud Link Tracing Service](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv).

### Menggunakan driver Tracer lainnya

Anda juga dapat menggunakan driver Tracer lain yang sesuai dengan protokol OpenTracing. Di item Driver, isi class apa pun yang mengimplementasikan `Hyperf\Tracer\Contract\NamedFactoryInterface`. Interface ini hanya memiliki satu fungsi make, parameternya adalah nama driver, dan perlu mengembalikan instance yang mengimplementasikan OpenTracing\Tracer.

## Referensi

- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, A Large-Scale Distributed Systems Tracing System](https://bigbully.github.io/Dapper-translation/)
