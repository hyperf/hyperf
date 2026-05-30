# Async Queue

Async queue dibedakan dari message queue seperti `RabbitMQ` dan `Kafka`.
Komponen ini hanya menyediakan kemampuan 'asynchronous processing' dan
'asynchronous delay processing', serta tidak secara ketat menjamin message
persistence atau mendukung `ACK response mechanism`.

## Instalasi

```bash
composer require hyperf/async-queue
```

## Konfigurasi

File konfigurasi terletak di `config/autoload/async_queue.php`, yang dapat
dibuat jika file tersebut belum ada.

> Hanya `Redis Driver` yang didukung saat ini.

| Konfigurasi | Tipe | Nilai Default | Catatan |
|:-------------:|:------:|:-------------------------------------------:|:------------------:|
| driver | string | Hyperf\AsyncQueue\Driver\RedisDriver::class | Tidak ada |
| channel | string | queue | Prefix dari queue |
| retry_seconds | int | 5 | Interval retry setelah kegagalan |
| processes | int | 1 | Jumlah consumer process |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'retry_seconds' => 5,
        'processes' => 1,
    ],
];

```

## Penggunaan

### Mengonsumsi message

Komponen ini telah menyediakan child process default, cukup konfigurasikan
child process tersebut ke dalam `config/autoload/processes.php`.

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];
```

Tentu saja Anda juga dapat menambahkan `Process` di bawah ini ke dalam skeleton
aplikasi Anda.

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "async-queue")]
class AsyncQueueConsumer extends ConsumerProcess
{
}
```

### Memublikasikan message

Pertama, definisikan sebuah message job sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Job;

use Hyperf\AsyncQueue\Job;

class ExampleJob extends Job
{
    public $params;

    public function __construct($params)
    {
        // Sebaiknya gunakan data biasa di sini. Jangan mengirimkan objek yang membawa IO, seperti objek PDO.
        $this->params = $params;
    }

    public function handle()
    {
        // Proses logika spesifik berdasarkan parameter
        var_dump($this->params);
    }
}
```

Publikasikan message

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\ExampleJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * Publish the message.
     */
    public function push($params, int $delay = 0): bool
    {
        // ExampleJob di sini akan diserialisasi dan disimpan di Redis, sehingga variabel internal dari objek sebaiknya hanya dikirimi data biasa.
        // Demikian pula, jika annotation digunakan secara internal, @Value akan menserialisasikan objek yang sesuai, menyebabkan body message menjadi lebih besar.
        // Sehingga TIDAK disarankan menggunakan metode `make` untuk membuat objek `Job`.
        return $this->driver->push(new ExampleJob($params), $delay);
    }
}
```

Sesuai dengan skenario bisnis yang sebenarnya, untuk mengirimkan message
secara dinamis ke eksekusi async queue, kami mendemonstrasikan pengiriman
message secara dinamis di dalam controller sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\QueueService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class QueueController extends Controller
{
    #[Inject]
    protected QueueService $service;

    public function index()
    {
        $this->service->push([
            'group@hyperf.io',
            'https://doc.hyperf.io',
            'https://www.hyperf.io',
        ]);

        return 'success';
    }
}
```
