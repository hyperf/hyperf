# Pelacakan Call Link

Dalam arsitektur microservice, akan ada banyak service yang dipecah, yang
berarti sebuah request bisnis mungkin melewati setidaknya 3 atau 4 service,
bahkan puluhan atau lebih. Di bawah arsitektur ini, sangat sulit ketika kita
perlu melakukan debugging terhadap masalah tertentu. Oleh karena itu, kita
memerlukan sistem pelacakan call link (call link tracking) untuk membantu kita
menampilkan link panggilan service secara dinamis sehingga kita dapat menemukan
masalah dengan cepat, dan juga mengoptimalkan service berdasarkan informasi
link tersebut.

Di `Hyperf`, kami menyediakan komponen
[hyperf/tracer](https://github.com/hyperf/tracer) untuk melacak dan
menganalisis panggilan dari setiap request lintas jaringan. Saat ini, sistem
[Zipkin](https://zipkin.io/) dan sistem [Jaeger](https://www.jaegertracing.io/)
telah dihubungkan sesuai dengan protokol [OpenTracing](https://opentracing.io).
User juga dapat menyesuaikan hal ini secara mandiri dengan mengikuti protokol
OpenTracing.

## Instalasi

### Melalui Composer

```bash
composer require hyperf/tracer
```

Komponen [hyperf/tracer](https://github.com/hyperf/tracer) telah menginstal
dependensi yang terkait dengan [Zipkin](https://zipkin.io/) secara default. Jika
Anda ingin menggunakan [Jaeger](https://www.jaegertracing.io/), Anda perlu
menjalankan perintah berikut untuk menginstal dependensi yang sesuai:

```bash
composer require jonahgeorge/jaeger-client-php
```

### Tambahkan konfigurasi komponen

Jika file tersebut belum ada, jalankan perintah berikut untuk menambahkan file
konfigurasi `config/autoload/opentracing.php`:

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## Penggunaan

### Konfigurasi

#### Mengaktifkan pelacakan

Secara default, ini menyediakan pemantauan panggilan `Guzzle HTTP`, panggilan
`Redis`, dan panggilan `DB` atau pemrosesan aspect `AOP` untuk mencapai
propagasi dan pelacakan dari call link. Pelacakan ini tidak diaktifkan secara
default. Anda perlu mengubah item `enable` di dalam file konfigurasi
`config/autoload/opentracing.php` untuk mengaktifkan pelacakan dari panggilan
remote tertentu.

```php
<?php

return [
    'enable' => [
        // enable the tracing of Guzzle HTTP calls
        'guzzle' => false,
        // enable the tracing of Redis calls
        'redis' => false,
        // enable the tracing of DB calls
        'db' => false,
    ],
];
```

Sebelum mulai melacak, kita perlu memilih driver Tracer yang akan digunakan
dan mengonfigurasi Tracer tersebut.

#### Memilih driver tracker

Nilai yang sesuai dengan `default` dalam file konfigurasi adalah nama driver
yang digunakan. Konfigurasi spesifik dari driver didefinisikan di bawah item
`tracer`, menggunakan nama driver yang sama sebagai `key`.

```php
<?php

return [
    // Select the default Tracer driver, the selected Tracer name corresponds to the key defined under tracers
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Zipkin config
        'staging_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // another Zipkin config
        'producton_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Jaeger config
        'jaeger' => [
            'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
        ],
    ]
];
```

Perhatikan bahwa seperti yang ditunjukkan pada contoh konfigurasi, Anda dapat
mengonfigurasi beberapa set driver Zipkin atau driver Jaeger. Meskipun sistem
dasar yang digunakan sama, konfigurasi spesifiknya bisa berbeda. Skenario umum
adalah kita menginginkan tingkat sampling (sampling rate) 100% di lingkungan
pengujian (test environment), tetapi 1% di lingkungan produksi (production
environment). Dua set driver dapat dikonfigurasi, dan kemudian driver yang
berbeda dapat dipilih sesuai dengan environment variable di bawah item `default`.

#### Mengonfigurasi Zipkin

Saat menggunakan Zipkin, tambahkan konfigurasi spesifik dari Zipkin ke item
`tracer` di dalam file konfigurasi.

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // default Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Zipkin drive config
        'zipkin' => [
            // current app config
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // If ipv6 and ipv6 are null, the component will automatically detect from the Server
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // the endpoint address of Zipkin service
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // Request timeout (in seconds)
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // Sampler, track all requests by default
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
    ],
];
```

#### Mengonfigurasi Jaeger

Saat menggunakan Jaeger, tambahkan konfigurasi spesifik dari Jaeger ke item
`tracer` di dalam file konfigurasi.

```php
<?php
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use const Jaeger\SAMPLER_TYPE_CONST;

return [
    // default Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Jaeger drive config
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // project name
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // Sampler, track all requests by default
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // the address which should report to
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

Konfigurasi lebih lanjut tentang Jaeger dapat ditemukan di
[sini](https://github.com/jonahgeorge/jaeger-client-php).

#### Mengaktifkan pelacakan JsonRPC

Pelacakan link JsonRPC tidak berada dalam konfigurasi terpadu, dan untuk
sementara termasuk dalam versi `Beta` versi.

Kita hanya perlu mengonfigurasi `aspects.php`, dan menambahkan `Aspect` berikut
untuk mengaktifkannya.

> Tip: Jangan lupa untuk menambahkan TraceMiddleware yang sesuai di sisi lawan.

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### Mengaktifkan pelacakan coroutine

Pelacakan link coroutine tidak disertakan dalam konfigurasi terpadu, ini
merupakan versi opsional dari fungsi tersebut.

Kita hanya perlu mengonfigurasi `aspects.php` dan menambahkan `Aspect` berikut
untuk mengaktifkannya.

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### Mengonfigurasi middleware atau listener

Setelah mengonfigurasi driver, Anda perlu mengonfigurasi middleware atau
listener event siklus request untuk mengumpulkan informasi guna mengaktifkan
fungsi pengumpulan.

- Menambahkan middleware

Buka file `config/autoload/middlewares.php` dan aktifkan middleware pada
node `http`.

```php
<?php

declare(strict_types=1);

return [
     'http' => [
         \Hyperf\Tracer\Middleware\TraceMiddleware::class,
     ],
];
```

- atau menambahkan listener

Buka file `config/autoload/listeners.php` dan tambahkan listener tersebut.

```php
<?php

declare(strict_types=1);

return [
     \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### Mengonfigurasi Span tag

Untuk beberapa nama Span Tag yang informasi pelacakannya dikumpulkan secara
otomatis oleh Hyperf, Anda dapat mengubah nama yang sesuai dengan mengubah
konfigurasi Span Tag. Cukup tambahkan konfigurasi `tags` dalam file konfigurasi
`config/autoload/opentracing.php`. Referensi konfigurasi adalah sebagai berikut.
Jika item konfigurasi ada, nilai dari item konfigurasi tersebut yang akan
berlaku. Jika item konfigurasi tidak ada, nilai default dari komponen yang akan
berlaku.

```php
return [
    'tags' => [
        // HTTP client (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis client
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // database client (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### Mengganti sampler

Sampler default mencatat call link untuk semua request, yang akan memberikan
dampak tertentu pada performa, terutama penggunaan memori. Jadi kita hanya
perlu melacak call link saat kita menginginkannya, sehingga kita perlu mengganti
sampler tersebut. Sangat mudah untuk mengganti sampler, sebagai contoh pada
Zipkin, cukup ubah nilai yang sesuai pada item konfigurasi
`opentracing.zipkin.sampler` ke instance objek sampler Anda, selama objek
sampler Anda mengimplementasikan interface class `Zipkin\Sampler`.

### Mengakses layanan pelacakan link Alibaba Cloud

Saat kita menggunakan layanan pelacakan link Alibaba Cloud, karena sisi lawan
juga mendukung protokol `Zipkin`, Anda dapat langsung mengubah nilai
`endpoint_url` di dalam file konfigurasi `config/autoload/opentracing.php` ke
alamat `region` Aliyun Anda yang sesuai. Alamat spesifik dapat diperoleh di
layanan pelacakan link Alibaba Cloud. Untuk detail lebih lanjut, silakan merujuk
ke [Dokumen Bantuan Layanan Pelacakan Link Alibaba Cloud](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)

### Menggunakan driver Tracer lainnya

Anda juga dapat menggunakan driver Tracer lainnya yang mengikuti protokol
OpenTracing. Pada field Driver, isi dengan kelas apa pun yang
mengimplementasikan `Hyperf\Tracer\Contract\NamedFactoryInterface`. Interface
ini hanya memiliki satu fungsi `make()`, parameternya adalah nama driver, dan
perlu mengembalikan instance yang mengimplementasikan `OpenTracing\Tracer`.

## Referensi
- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, sistem pelacakan untuk sistem terdistribusi skala besar](https://bigbully.github.io/Dapper-translation/)
