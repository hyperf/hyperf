# Service Monitoring

Salah satu kebutuhan utama dalam tata kelola microservice adalah service
observability. Sebagai pengelola microservice, tidaklah mudah untuk memantau
status kesehatan dari berbagai service. Banyak solusi telah muncul di bidang
ini di era cloud-native. Komponen ini mengabstraksikan telemetri dan pemantauan,
yang merupakan pilar penting dari observability, untuk memungkinkan pengguna
berintegrasi dengan cepat dengan infrastruktur yang ada sambil menghindari
vendor lock-in.

## Instalasi

### Instal Komponen melalui Composer

```bash
composer require hyperf/metric
```

Komponen [hyperf/metric](https://github.com/hyperf/metric) secara default
telah menyertakan dependensi [Prometheus](https://prometheus.io/). Jika Anda
ingin menggunakan [StatsD](https://github.com/statsd/statsd) atau
[InfluxDB](http://influxdb.com), Anda juga perlu menjalankan perintah berikut
untuk menginstal dependensi yang sesuai:

```bash
# StatsD required dependencies
composer require domnikl/statsd
# InfluxDB required dependencies
composer require influxdb/influxdb-php 
```

### Tambahkan Konfigurasi Komponen

Jika file tersebut belum ada, jalankan perintah berikut untuk menambahkan file
konfigurasi `config/autoload/metric.php`:

```bash
php bin/hyperf.php vendor:publish hyperf/metric
```

## Penggunaan

### Konfigurasi

#### Opsi

`default`: Nilai yang sesuai dengan `default` pada file konfigurasi adalah nama
driver yang digunakan. Konfigurasi spesifik dari driver didefinisikan di bawah
`metric`, menggunakan nama driver yang sama sebagai `key`.

```php
'default' => env('METRIC_DRIVER', 'prometheus'),
```

* `use_standalone_process`: Menentukan apakah akan menggunakan `proses
pemantauan mandiri` (standalone monitoring process). Sangat disarankan untuk
mengaktifkannya. Jika dinonaktifkan, pengumpulan dan pelaporan metric akan
ditangani di dalam `Worker process`.

```php
'use_standalone_process' => env('TELEMETRY_USE_STANDALONE_PROCESS', true),
```

* `enable_default_metric`: Menentukan apakah akan menghitung metric default.
Metric default meliputi penggunaan memori, beban CPU sistem, serta metric
Swoole Server dan Swoole Coroutine.

```php
'enable_default_metric' => env('TELEMETRY_ENABLE_DEFAULT_TELEMETRY', true),
```

`default_metric_interval`: Interval pengiriman (push) metric default, dalam
satuan detik (berlaku sama untuk konfigurasi selanjutnya).
```php
'default_metric_interval' => env('DEFAULT_METRIC_INTERVAL', 5),
```

#### Mengonfigurasi Prometheus

Saat menggunakan Prometheus, tambahkan konfigurasi spesifik Prometheus pada
bagian metric di dalam file konfigurasi.

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

Prometheus memiliki dua mode kerja: crawl mode (mode tarik/tarik data) dan push
mode (melalui Prometheus Pushgateway), yang keduanya didukung oleh komponen
ini.

Saat menggunakan crawl mode (rekomendasi resmi dari Prometheus), Anda perlu
mengatur:

```php
'mode' => Constants::SCRAPE_MODE
```

Dan konfigurasikan alamat penarikan `scrape_host`, port penarikan
`scrape_port`, serta path penarikan `scrape_path`. Prometheus dapat menarik
semua metric dalam bentuk akses HTTP berdasarkan konfigurasi tersebut.

> Catatan: Dalam crawl mode, proses mandiri (standalone process) harus
diaktifkan, yaitu `use_standalone_process = true`.

Saat menggunakan push mode, Anda perlu mengatur:

```php
'mode' => Constants::PUSH_MODE
```

Dan konfigurasikan alamat pengiriman `push_host`, port pengiriman `push_port`,
serta interval pengiriman `push_interval`. Push mode hanya direkomendasikan
untuk tugas offline (offline tasks).

Karena perbedaan dalam pengaturan dasar, mode di atas mungkin tidak memenuhi
kebutuhan Anda. Komponen ini juga mendukung custom mode. Dalam custom mode,
komponen hanya bertanggung jawab atas pengumpulan indikator (metric), sedangkan
pelaporan spesifiknya harus ditangani sendiri oleh pengguna.

```php
'mode' => Constants::CUSTOM_MODE
```

Misalnya, Anda mungkin ingin melaporkan metric melalui route kustom, atau
menyimpan metric di Redis, dan service independen lainnya bertanggung jawab atas
pelaporan terpusat dari metric tersebut. Bagian [pelaporan kustom](#pelaporan-kustom)
berisi contoh yang sesuai.

#### Mengonfigurasi StatsD

Saat menggunakan StatsD, tambahkan konfigurasi spesifik StatsD pada bagian
metric di dalam file konfigurasi.

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

StatsD saat ini hanya mendukung mode UDP, Anda perlu mengonfigurasi alamat UDP
`udp_host`, port UDP `udp_port`, apakah akan mengirim secara massal
`enable_batch` (mengurangi jumlah request), interval pengiriman massal
`push_interval`, dan sample rate `sample_rate`.

#### Mengonfigurasi InfluxDB

Saat menggunakan InfluxDB, tambahkan konfigurasi spesifik InfluxDB pada bagian
metric di dalam file konfigurasi.

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

InfluxDB menggunakan mode HTTP default, Anda perlu mengonfigurasi alamat `host`,
port `port`, username `username`, password `password`, nama database `dbname`,
dan interval pengiriman massal `push_interval`.

### Abstraksi Dasar

Komponen telemetri mengabstraksikan tiga tipe data yang umum digunakan untuk
memastikan pemisahan (decoupling) dari implementasi konkretnya.

Ketiga tipe tersebut adalah:

Counter: Indikator yang digunakan untuk menggambarkan peningkatan satu arah
(one-way increments). Seperti jumlah request HTTP.

```php
interface CounterInterface
{
    public function with(string ...$labelValues): self;

    public function add(int $delta);
}
```

Gauge: Indikator yang digunakan untuk menggambarkan kenaikan atau penurunan
seiring waktu. Seperti jumlah koneksi yang tersedia di connection pool.

```php
interface GaugeInterface
{
    public function with(string ...$labelValues): self;

    public function set(float $value);

    public function add(float $delta);
}
```

* Histogram: digunakan untuk menggambarkan distribusi statistik yang dihasilkan
dari observasi berkelanjutan atas suatu event, biasanya dinyatakan dalam
persentil atau bucket. Seperti delay request HTTP.

```php
interface HistogramInterface
{
    public function with(string ...$labelValues): self;

    public function put(float $sample);
}
```

### Mengonfigurasi Middleware

Setelah mengonfigurasi driver, Anda hanya perlu mengonfigurasi middleware
untuk mengaktifkan fungsi statistik Histogram request.
Buka file `config/autoload/middlewares.php`, contoh berikut adalah untuk
mengaktifkan middleware pada HTTP Server.

```php
<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Metric\Middleware\MetricMiddleware::class,
    ],
];
```
> Dimensi statistik dalam middleware ini meliputi `request_status`,
`request_path`, dan `request_method`. Jika `request_path` Anda terlalu besar,
disarankan untuk menulis ulang middleware ini untuk menghapus dimensi
`request_path`, jika tidak, tingginya kardinalitas (high cardinality) dapat
menyebabkan luapan memori (memory overflow).

### Penggunaan Kustom

Telemetri melalui HTTP middleware hanyalah sebagian kecil dari kemampuan
komponen ini. Anda dapat menginjeksi class `Hyperf\Metric\Contract\MetricFactoryInterface`
untuk melakukan telemetri data bisnis Anda sendiri. Contohnya: jumlah pesanan
yang dibuat, jumlah klik pada iklan, dll.

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
        // order logic...
    }

}
```

`MetricFactoryInterface` berisi method factory berikut untuk menghasilkan tiga
tipe statistik dasar yang sesuai.

```php
public function makeCounter($name, $labelNames): CounterInterface;

public function makeGauge($name, $labelNames): GaugeInterface;

public function makeHistogram($name, $labelNames): HistogramInterface;
```

Contoh di atas adalah metric yang dihasilkan dalam cakupan request statistik.
Terkadang indikator yang perlu kita hitung adalah untuk siklus hidup lengkap
(complete life cycle), seperti menghitung panjang antrean asinkron
(asynchronous queue) atau jumlah barang dalam stok. Dalam skenario ini, Anda
dapat mendengarkan (listen) event `MetricFactoryReady`.

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

> Dari sudut pandang engineering, tidak disarankan untuk menanyakan panjang
antrean langsung dari Redis. Panjang antrean sebaiknya diperoleh melalui method
`info()` di bawah interface `DriverInterface` dari driver antrean. Ini hanya
demonstrasi sederhana di sini. Anda dapat menemukan contoh lengkap di folder
`src/Listener` pada source code komponen.

### Anotasi

Anda dapat menggunakan `#[Counter(name="stat_name_here")]` dan
`#[Histogram(name="stat_name_here")]` untuk menghitung waktu pemanggilan dan
menjalankan aspek (aspect).

Untuk penggunaan anotasi, silakan merujuk ke [Bab Anotasi](id/annotation).

### Custom Histogram Bucket

> Bagian ini hanya berlaku untuk driver Prometheus

Ketika Anda menggunakan Histogram dari Prometheus, terkadang ada kebutuhan
untuk kustom Bucket. Sebelum memulai service, Anda dapat menginjeksi
dependensi ke dalam Registry dan mendaftarkan Histogram sendiri, serta
mengatur Bucket yang diperlukan. Ketika Anda menggunakannya nanti,
`MetricFactory` akan menggunakan pendaftaran Histogram dengan nama yang sama
tersebut. Contohnya adalah sebagai berikut:

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
Setelah itu, ketika Anda menggunakan `$metricFactory->makeHistogram('test')`,
Histogram yang dikembalikan adalah Histogram yang telah Anda daftarkan sebelumnya.

### Pelaporan Kustom

> Bagian ini hanya berlaku untuk driver Prometheus

Setelah mengatur mode kerja driver Prometheus komponen ke custom mode
(`Constants::CUSTOM_MODE`), Anda dapat dengan bebas menangani pelaporan
indikator (metric). Di bagian ini, kami menunjukkan cara menyimpan metric di
Redis, kemudian menambahkan HTTP route baru ke Worker yang mengembalikan
metric yang dirender oleh Prometheus.

#### Menyimpan Metric dengan Redis

Media penyimpanan untuk metric ditentukan oleh interface
`Prometheus\Storage\Adapter`. Penyimpanan memori (memory storage) digunakan
secara default. Kita dapat mengubahnya ke penyimpanan Redis di
`config/autoload/dependencies.php`.

```php
<?php

return [
    Prometheus\Storage\Adapter::class => Hyperf\Metric\Adapter\Prometheus\RedisStorageFactory::class,
];
```

#### Menambahkan Route /metrics ke Worker

Tambahkan route Prometheus di `config/routes.php`.

> Catatan bahwa jika Anda ingin mendapatkan metric di bawah Worker, Anda perlu
menangani sendiri pembagian state (state sharing) antar Worker. Salah satu
caranya adalah dengan menyimpan state di Redis seperti yang dijelaskan di atas.

```php
<?php

use Hyperf\HttpServer\Router\Router;

Router::get('/metrics', function(){
    $registry = Hyperf\Context\ApplicationContext::getContainer()->get(Prometheus\CollectorRegistry::class);
    $renderer = new Prometheus\RenderTextFormat();
    return $renderer->render($registry->getMetricFamilySamples());
});
```

## Membuat Dashboard di Grafana

> Bagian ini hanya berlaku untuk driver Prometheus

Jika Anda mengaktifkan metric default, `Hyperf/Metric` menyediakan dashboard
Grafana untuk Anda langsung setelah instalasi (out of the box). Unduh
[file json](https://cdn.jsdelivr.net/gh/hyperf/hyperf/src/metric/grafana.json)
dashboard tersebut, lalu impor ke Grafana dan gunakan.

![grafana](imgs/grafana.png)

## Hal yang Perlu Diperhatikan

- Untuk menggunakan komponen ini dalam mengumpulkan metric pada custom command
  `hyperf/command`, Anda perlu menambahkan parameter command line:
  `--enable-event-dispatcher` saat menjalankan perintah tersebut.
