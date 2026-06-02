# Service Monitoring

Kebutuhan inti dari tata kelola microservice adalah observabilitas service. Sebagai seorang pengelola microservice, tidak mudah untuk selalu mengetahui status kesehatan setiap service. Banyak solusi telah muncul di bidang ini di era cloud-native. Komponen ini mengabstraksi pilar-pilar penting observabilitas, telemetry dan monitoring, sehingga memudahkan pengguna untuk mengintegrasikan dengan infrastruktur yang ada dengan cepat, sambil menghindari ketergantungan pada vendor tertentu.

## Instalasi

### Install komponen melalui Composer

```bash
composer require hyperf/metric
```

Metric mendukung [Prometheus](https://prometheus.io/), [StatsD](https://github.com/statsd/statsd), dan [InfluxDB](http://influxdb.com). Anda dapat menjalankan perintah berikut untuk menginstal dependensi yang sesuai:

```bash
# Prometheus
composer require promphp/prometheus_client_php
# StatsD dependencies
composer require domnikl/statsd
# InfluxDB dependencies
composer require influxdb/influxdb-php 
```

### Tambahkan konfigurasi komponen

Jika file belum ada, Anda dapat menjalankan perintah berikut untuk menambahkan file konfigurasi `config/autoload/metric.php`:

```bash
php bin/hyperf.php vendor:publish hyperf/metric
```

## Penggunaan

### Konfigurasi

#### Opsi

`default`: Nilai yang sesuai dengan `default` dalam file konfigurasi adalah nama driver yang digunakan. Konfigurasi spesifik dari driver didefinisikan di bawah item `metric`, menggunakan driver yang sama sebagai `key`.

```php
'default' => env('METRIC_DRIVER', 'prometheus'),
```

* `use_standalone_process`: Apakah akan menggunakan `proses monitoring mandiri`. Disarankan untuk diaktifkan. Jika dinonaktifkan, pengumpulan dan pelaporan metrik akan ditangani di `Worker process`.

```php
'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
```

* `enable_default_metric`: Apakah akan mengumpulkan metrik default. Metrik default mencakup penggunaan memori, beban CPU sistem, serta metrik Swoole Server dan metrik Swoole Coroutine.

```php
'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
```

`default_metric_interval`: Periode push untuk metrik default, dalam detik (berlaku sama untuk di bawah).
```php
'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
```

#### Mengkonfigurasi Prometheus

Saat menggunakan Prometheus, tambahkan konfigurasi spesifik Prometheus ke item `metric` dalam file konfigurasi.

```php
use Hyperf\Metric\Adapter\Prometheus\Constants;

return [
    'default' => env('METRIC_DRIVER', 'prometheus'),
    'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
    'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
    'metric' => [
        'prometheus' => [
            'driver' => Hyperf\Metric\Adapter\Prometheus\MetricFactory::class,
            'mode' => Constants::SCRAPE_MODE,
            'namespace' => env('APP_NAME', 'skeleton'),
            'scrape_host' => env('PROMETHEUS_SCRAPE_HOST', '0.0.0.0'),
            'scrape_port' => env('PROMETHEUS_SCRAPE_PORT', '9502'),
            'scrape_path' => env('PROMETHEUS_SCRAPE_PATH', '/metrics'),
            'push_host' => env('PROMETHEUS_PUSH_HOST', '0.0.0.0'),
            'push_port' => env('PROMETHEUS_PUSH_PORT', '9091'),
            'push_interval' => env('PROMETHEUS_PUSH_INTERVAL', 5),
        ],
    ],
];
```

Prometheus memiliki dua mode operasi: scrape mode dan push mode (melalui Prometheus Pushgateway). Komponen ini mendukung keduanya.

Saat menggunakan scrape mode (direkomendasikan secara resmi oleh Prometheus), Anda perlu mengatur:

```php
'mode' => Constants::SCRAPE_MODE
```

Dan konfigurasikan alamat scraping `scrape_host`, port scraping `scrape_port`, dan path scraping `scrape_path`. Prometheus dapat menarik semua metrik dalam bentuk akses HTTP di bawah konfigurasi yang sesuai.

> Catatan: Dalam gaya asinkron, scrape mode harus mengaktifkan proses independen, yaitu `use_standalone_process = true`.

Saat menggunakan push mode, Anda perlu mengatur:

```php
'mode' => Constants::PUSH_MODE
```

Dan konfigurasikan alamat push `push_host`, port push `push_port`, dan interval push `push_interval`. Push mode hanya direkomendasikan untuk offline task.

Karena perbedaan dalam pengaturan dasar, mode di atas mungkin tidak memenuhi semua kebutuhan. Komponen ini juga mendukung custom mode. Dalam custom mode, komponen hanya bertanggung jawab untuk pengumpulan metrik, dan pelaporan spesifik perlu ditangani oleh pengguna.

```php
'mode' => Constants::CUSTOM_MODE
```

Misalnya, Anda mungkin ingin melaporkan metrik melalui route kustom, atau berharap menyimpan metrik di Redis, dengan service independen lain yang bertanggung jawab untuk pelaporan metrik secara terpusat. Bagian [Custom Reporting](#custom-reporting) berisi contoh yang sesuai.

#### Mengkonfigurasi StatsD

Saat menggunakan StatsD, tambahkan konfigurasi spesifik StatsD ke item `metric` dalam file konfigurasi.

```php
return [
    'default' => env('METRIC_DRIVER', 'statd'),
    'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
    'metric' => [
        'statsd' => [
            'driver' => Hyperf\Metric\Adapter\StatsD\MetricFactory::class,
            'namespace' => env('APP_NAME', 'skeleton'),
            'udp_host' => env('STATSD_UDP_HOST', '127.0.0.1'),
            'udp_port' => env('STATSD_UDP_PORT', '8125'),
            'enable_batch' => env('STATSD_ENABLE_BATCH', true),
            'push_interval' => env('STATSD_PUSH_INTERVAL', 5),
            'sample_rate' => env('STATSD_SAMPLE_RATE', 1.0),
        ],
    ],
];
```

StatsD saat ini hanya mendukung mode UDP. Ini memerlukan konfigurasi alamat UDP `udp_host`, port UDP `udp_port`, apakah akan melakukan batch push `enable_batch` (untuk mengurangi jumlah request), interval batch push `push_interval`, dan sample rate `sample_rate`.

#### Mengkonfigurasi InfluxDB

Saat menggunakan InfluxDB, tambahkan konfigurasi spesifik InfluxDB ke item `metric` dalam file konfigurasi.

```php
return [
    'default' => env('METRIC_DRIVER', 'influxdb'),
    'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
    'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
    'metric' => [
        'influxdb' => [
            'driver' => Hyperf\Metric\Adapter\InfluxDB\MetricFactory::class,
            'namespace' => env('APP_NAME', 'skeleton'),
            'host' => env('INFLUXDB_HOST', '127.0.0.1'),
            'port' => env('INFLUXDB_PORT', '8086'),
            'username' => env('INFLUXDB_USERNAME', ''),
            'password' => env('INFLUXDB_PASSWORD', ''),
            'dbname' => env('INFLUXDB_DBNAME', true),
            'push_interval' => env('INFLUXDB_PUSH_INTERVAL', 5),
        ],
    ],
];
```

InfluxDB menggunakan mode HTTP default. Ini memerlukan konfigurasi alamat `host`, port UDP `port` (Catatan: InfluxDB biasanya menggunakan port HTTP 8086), username `username`, password `password`, tabel `dbname`, dan interval batch push `push_interval`.

### Abstraksi Dasar

Komponen telemetry mengabstraksi tiga tipe data yang umum digunakan untuk memastikan dekopling dari implementasi spesifik.

Ketiga tipe tersebut adalah:

Counter: Digunakan untuk mendeskripsikan metrik yang meningkat secara monoton. Misalnya, jumlah HTTP request.

```php
interface CounterInterface
{
    public function with(string ...$labelValues): self;

    public function add(int $delta);
}
```

Gauge: Digunakan untuk mendeskripsikan metrik yang naik atau turun seiring waktu. Misalnya, jumlah koneksi yang tersedia di connection pool.

```php
interface GaugeInterface
{
    public function with(string ...$labelValues): self;

    public function set(float $value);
    
    public function add(float $delta);
}
```

Histogram: Digunakan untuk mendeskripsikan distribusi statistik yang dihasilkan setelah pengamatan berkelanjutan terhadap suatu event, biasanya direpresentasikan sebagai persentil atau bucket. Misalnya, latensi HTTP request.

```php
interface HistogramInterface
{
    public function with(string ...$labelValues): self;

    public function put(float $sample);
}
```

### Mengkonfigurasi middleware

Setelah mengkonfigurasi driver, cukup konfigurasi middleware untuk mengaktifkan fungsi statistik Histogram untuk request.
Buka file `config/autoload/middlewares.php`, contoh menunjukkan pengaktifan middleware di Server `http`.

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Metric\Middleware\MetricMiddleware::class,
    ],
];
```
> Dimensi statistik di middleware ini mencakup `request_status`, `request_path`, `request_method`. Jika Anda memiliki terlalu banyak `request_path`, disarankan untuk menulis ulang middleware ini dan menghapus dimensi `request_path`, jika tidak, kardinalitas yang berlebihan akan menyebabkan memori overflow.

### Penggunaan Kustom

Telemetry melalui HTTP middleware hanyalah puncak gunung es dari tujuan komponen ini. Anda dapat meng-inject class `Hyperf\Metric\Contract\MetricFactoryInterface` untuk melakukan telemetry data bisnis sendiri. Contoh: jumlah pesanan yang dibuat, jumlah klik iklan, dll.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Order;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Metric\Contract\MetricFactoryInterface;

class IndexController extends AbstractController
{
    #[Inject]
    private MetricFactoryInterface $metricFactory;

    public function create(Order $order)
    {
        $counter = $this->metricFactory->makeCounter('order_created', ['order_type']);
        $counter->with($order->type)->add(1);
        // Logika order...
    }
}
```

`MetricFactoryInterface` berisi method factory berikut untuk menghasilkan tiga tipe statistik dasar yang sesuai.

```php
public function makeCounter($name, $labelNames): CounterInterface;

public function makeGauge($name, $labelNames): GaugeInterface;

public function makeHistogram($name, $labelNames): HistogramInterface;
```

Contoh di atas adalah untuk mengumpulkan metrik yang dihasilkan dalam lingkup sebuah request. Terkadang metrik yang perlu kita kumpulkan berorientasi pada siklus hidup lengkap, seperti menghitung panjang antrean asinkron atau jumlah stok produk. Dalam skenario ini, Anda dapat mendengarkan event `MetricFactoryReady`.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Metric\Event\MetricFactoryReady;
use Psr\Container\ContainerInterface;
use Redis;

class OnMetricFactoryReady implements ListenerInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            MetricFactoryReady::class,
        ];
    }

    public function process(object $event)
    {
        $redis = $this->container->get(Redis::class);
        $gauge = $event
                    ->factory
                    ->makeGauge('queue_length', ['driver'])
                    ->with('redis');
        while (true) {
            $length = $redis->llen('queue');
            $gauge->set($length);
            sleep(1);
        }
    }
}
```

> Dari sudut pandang engineering, tidak terlalu tepat untuk menanyakan panjang antrean langsung dari Redis. Anda harus menggunakan method `info()` di bawah interface `DriverInterface` dari driver antrean untuk mendapatkan panjang antrean. Ini hanya demonstrasi sederhana. Anda dapat menemukan contoh lengkap di folder `src/Listener` dari source code komponen ini.

### Annotation

Anda dapat menggunakan `#[Counter(name="stat_name_here")]` dan `#[Histogram(name="stat_name_here")]` untuk menghitung jumlah pemanggilan dan runtime dari aspect.

Untuk penggunaan annotation, silakan merujuk ke [Bab Annotation](id/annotation.md).

### Custom Histogram Bucket

> Bagian ini hanya berlaku untuk driver Prometheus

Ketika Anda menggunakan Histogram di Prometheus, terkadang ada kebutuhan untuk Bucket kustom. Anda dapat mengandalkan injeksi Registry dan mendaftarkan Histogram sendiri sebelum service dimulai, dan mengatur Bucket yang diperlukan. Nantinya, saat menggunakan `MetricFactory`, ia akan memanggil Histogram dengan nama yang sama yang Anda daftarkan. Contohnya sebagai berikut:

```php
<?php

namespace App\Listener;

use Hyperf\Config\Annotation\Value;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeMainServerStart;
use Prometheus\CollectorRegistry;

class OnMainServerStart implements ListenerInterface
{
    protected $registry;

    public function __construct(CollectorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function listen(): array
    {
        return [
            BeforeMainServerStart::class,
        ];
    }

    public function process(object $event)
    {
        $this->registry->registerHistogram(
            config("metric.metric.prometheus.namespace"), 
            'test',
            'help_message', 
            ['labelName'], 
            [0.1, 1, 2, 3.5]
        );
    }
}
```
Setelah itu, ketika Anda menggunakan `$metricFactory->makeHistogram('test')`, ia akan mengembalikan Histogram yang Anda daftarkan sebelumnya.

### Custom Reporting

> Bagian ini hanya berlaku untuk driver Prometheus

Setelah mengatur mode operasi driver Prometheus komponen ke custom mode (`Constants::CUSTOM_MODE`), Anda dapat dengan bebas menangani pelaporan metrik. Di bagian ini, kami menunjukkan cara menyimpan metrik di Redis, dan kemudian menambahkan route HTTP baru di Worker untuk mengembalikan metrik yang dirender oleh Prometheus.

#### Menggunakan Redis untuk menyimpan metrik

Media penyimpanan untuk metrik didefinisikan oleh interface `Prometheus\Storage\Adapter`. Penyimpanan in-memory digunakan secara default. Kita dapat mengubahnya ke penyimpanan Redis di `config/autoload/dependencies.php`.

```php
<?php

return [
    Prometheus\Storage\Adapter::class => Hyperf\Metric\Adapter\Prometheus\RedisStorageFactory::class,
];
```

#### Menambahkan route /metrics di Worker

Tambahkan route Prometheus di config/routes.php.

> Catatan bahwa jika Anda ingin mendapatkan metrik di Worker, Anda perlu menangani masalah state sharing antar Worker sendiri. Salah satu caranya adalah menyimpan state di Redis seperti yang dijelaskan di atas.

```php
<?php

use Hyperf\HttpServer\Router\Router;

Router::get('/metrics', function(){
    $registry = Hyperf\Context\ApplicationContext::getContainer()->get(Prometheus\CollectorRegistry::class);
    $renderer = new Prometheus\RenderTextFormat();
    return $renderer->render($registry->getMetricFamilySamples());
});
```

## Membuat Console di Grafana

> Bagian ini hanya berlaku untuk driver Prometheus

Jika Anda mengaktifkan default metrics, `Hyperf/Metric` telah menyiapkan console Grafana yang siap pakai untuk Anda. Download file json console [di sini](https://cdn.jsdelivr.net/gh/hyperf/hyperf/src/metric/grafana.json) dan impor ke Grafana untuk menggunakannya.

![grafana](imgs/grafana.png)

## Perhatian

- Jika Anda perlu menggunakan komponen ini untuk mengumpulkan metrik di perintah kustom `hyperf/command`, Anda perlu menambahkan argumen baris perintah saat memulai perintah: `--enable-event-dispatcher`.
