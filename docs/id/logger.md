# Logger

Komponen `hyperf/logger` diimplementasikan berdasarkan [psr/logger](https://github.com/php-fig/log) dan menggunakan [monolog/monolog](https://github.com/Seldaek/monolog) sebagai driver bawaan. Di dalam project `hyperf-skeleton`, beberapa konfigurasi logger sudah disediakan secara default, menggunakan `Monolog\Handler\StreamHandler`. Karena `Swoole` telah mendukung coroutine untuk fungsi seperti `fopen` dan `fwrite`, maka ini aman digunakan dalam coroutine selama parameter `useLocking` tidak disetel ke `true`.

## Instalasi

```shell
composer require hyperf/logger
```

## Konfigurasi

Di dalam project `hyperf-skeleton`, beberapa konfigurasi logger sudah disediakan secara default. Secara default, file konfigurasi untuk logger adalah `config/autoload/logger.php`, seperti contoh berikut:

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => \Monolog\Level::Debug,
            ],
        ],
        'formatter' => [
            'class' => \Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ]
        ],
    ],
];
```

## Penggunaan

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Hyperf\Logger\LoggerFactory;

class DemoService
{

    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        // Argumen pertama adalah nama log, argumen kedua adalah key di config/autoload/logger.php
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Lakukan sesuatu.
        $this->logger->info("Pesan log Anda.");
    }
}
```

## Pengetahuan Dasar Monolog

Mari kita lihat beberapa konsep dasar `monolog` melalui kode berikut:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Membuat Channel, argumen 'log' adalah nama Channel
$log = new Logger('log');

// Membuat dua Handler, sesuai dengan variabel $stream dan $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// Menentukan format tanggal sebagai "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// Menentukan format log sebagai "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// Membuat Formatter berdasarkan format tanggal dan format log
$formatter = new LineFormatter($output, $dateFormat);

// Mengatur Formatter ke Handler
$stream->setFormatter($formatter);

// Mendorong Handler ke dalam antrian Handler Channel
$log->pushHandler($stream);
$log->pushHandler($fire);

// Meng-clone channel log baru
$log2 = $log->withName('log2');

// Menambahkan record ke log
$log->warning('Foo');

// Menambahkan data tambahan ke record
// 1. log context
$log->error('pengguna baru', ['username' => 'daydaygo']);
// 2. processor
$log->pushProcessor(function ($record) {
    $record['extra']['dummy'] = 'hello';
    return $record;
});
$log->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
$log->alert('czl');
```

- Pertama, instansiasi `Logger` dan berikan nama; nama tersebut sesuai dengan `channel`.
- Anda dapat mengikat beberapa `Handler` ke satu `Logger`. Ketika `Logger` mencatat log, ia mendelegasikan pemrosesan ke `Handler`-handler tersebut.
- `Handler` dapat menentukan **level log** mana yang akan ditangani, misalnya, `Logger::WARNING` hanya akan menangani log dengan level `>= Logger::WARNING`.
- Siapa yang memformat log? `Formatter`. Atur `Formatter` dan ikat ke `Handler` yang sesuai.
- Sebuah log terdiri dari: `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`
- Bedakan antara `context` dan `extra` yang ditambahkan dalam log: `context` adalah tambahan yang ditentukan oleh pengguna saat mencatat log, yang lebih fleksibel; `extra` ditambahkan secara tetap oleh `Processor` yang terikat pada `Logger`, yang lebih cocok untuk mengumpulkan **informasi umum**.

## Penggunaan Lanjutan

### Membungkus Kelas `Log`

Terkadang Anda mungkin ingin mempertahankan kebiasaan pencatatan log yang digunakan di sebagian besar framework. Dalam kasus seperti itu, Anda dapat membuat kelas `Log` di bawah `App` dan menggunakan metode magic `__callStatic` untuk mengimplementasikan panggilan statis guna mengakses `Logger` dan mencatat log di berbagai level. Mari kita demonstrasikan dengan kode:

> Ingat, saat menggunakannya, jangan biarkan $name terikat dengan request. Misalnya, menggunakan $request_id sebagai nama logger akan menyebabkan Factory menyimpan objek logger di tingkat request, yang menyebabkan kebocoran memori yang parah.

```php
namespace App;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Context\ApplicationContext;

class Log
{
    public static function get(string $name = 'app')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
```

Secara default, ia menggunakan `Channel` bernama `app` untuk mencatat log. Anda juga bisa mendapatkan `Logger` untuk `Channel` yang berbeda menggunakan metode `Log::get($name)`. `Container` yang powerful menangani semua ini untuk Anda.

### Logging ke stdout

Secara default, log yang dihasilkan oleh komponen framework didukung oleh kelas implementasi `Hyperf\Framework\Logger\StdoutLogger` dari antarmuka `Hyperf\Contract\StdoutLoggerInterface`. Kelas ini hanya mengeluarkan informasi melalui `print_r()` ke `standard output (stdout)`, yaitu `Terminal` yang menjalankan `Hyperf`, yang berarti `monolog` sebenarnya tidak digunakan. Lalu bagaimana jika kita ingin menggunakan `monolog` untuk menjaga konsistensi?

Ya, tetap melalui `Container` yang powerful.

- Pertama, implementasikan kelas `StdoutLoggerFactory`. Untuk detail lebih lanjut tentang penggunaan `Factory`, silakan lihat bab [Dependency Injection](id/di.md).

```php
<?php
declare(strict_types=1);

namespace App;

use Psr\Container\ContainerInterface;

class StdoutLoggerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return Log::get('sys');
    }
}
```

- Deklarasikan dependency. Di tempat-tempat yang menggunakan `StdoutLoggerInterface`, dependency tersebut akan diselesaikan oleh kelas yang diinstansiasi oleh `StdoutLoggerFactory` yang sebenarnya dijadikan dependency.

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### Format Log Berbeda di Lingkungan Berbeda

Penggunaan di atas hanya berpusat pada `Logger` di monolog. Mari kita lihat `Handler` dan `Formatter`.

```php
// config/autoload/logger.php
$appEnv = env('APP_ENV', 'dev');
if ($appEnv == 'dev') {
    $formatter = [
        'class' => \Monolog\Formatter\LineFormatter::class,
        'constructor' => [
            'format' => "||%datetime%||%channel%||%level_name%||%message%||%context%||%extra%\n",
            'allowInlineLineBreaks' => true,
            'includeStacktraces' => true,
        ],
    ];
} else {
    $formatter = [
        'class' => \Monolog\Formatter\JsonFormatter::class,
        'constructor' => [],
    ];
}

return [
    'default' => [
        'handler' => [
            'class' => \Monolog\Handler\StreamHandler::class,
            'constructor' => [
                'stream' => 'php://stdout',
                'level' => \Monolog\Level::Info,
            ],
        ],
        'formatter' => $formatter,
    ],
];
```

- Sebuah `Handler` bernama `default` dikonfigurasi secara default, yang mencakup informasi tentang `Handler` ini dan `Formatter`-nya.
- Saat mendapatkan `Logger`, jika tidak ada `Handler` yang ditentukan, lapisan bawah secara otomatis akan mengikat `Handler` `default` ke `Logger`.
- Lingkungan `dev` (pengembangan): Log dikeluarkan ke `standard output (stdout)` menggunakan `php://stdout`, dan `allowInlineLineBreaks` disetel di `Formatter` untuk memudahkan melihat log multi-baris.
- Lingkungan non-`dev`: Log diformat sebagai `json` menggunakan `JsonFormatter`, yang memudahkan untuk dikirimkan ke layanan log pihak ketiga.

### Rotasi File Log Berdasarkan Tanggal

Jika Anda ingin file log dirotasi berdasarkan tanggal, Anda dapat menggunakan `Monolog\Handler\RotatingFileHandler` yang sudah disediakan oleh `Monolog`. Konfigurasikan sebagai berikut:

Ubah file konfigurasi `config/autoload/logger.php`, ganti `Handler` menjadi `Monolog\Handler\RotatingFileHandler::class`, dan ubah field `stream` menjadi `filename`.

```php
<?php

return [
    'default' => [
        'handler' => [
            'class' => Monolog\Handler\RotatingFileHandler::class,
            'constructor' => [
                'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => Monolog\Level::Debug,
            ],
        ],
        'formatter' => [
            'class' => Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],
];
```

Jika Anda ingin melakukan pemisahan log yang lebih terperinci, Anda juga dapat memperluas kelas `Monolog\Handler\RotatingFileHandler` dan mengimplementasikan ulang metode `rotate()`.

### Mengonfigurasi Beberapa `Handler`

Pengguna dapat memodifikasi `handlers` agar grup log yang sesuai mendukung beberapa `handler`. Misalnya, dalam konfigurasi berikut, ketika pengguna mengirimkan log dengan level `INFO` atau lebih tinggi, log akan ditulis ke `hyperf.log` dan `hyperf-debug.log`.
Ketika pengguna mengirimkan log level `DEBUG`, log hanya akan ditulis ke `hyperf-debug.log`.

```php
<?php

declare(strict_types=1);

use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Level;

return [
    'default' => [
        'handlers' => [
            [
                'class' => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Level::Info,
                ],
                'formatter' => [
                    'class' => Formatter\LineFormatter::class,
                    'constructor' => [
                        'format' => null,
                        'dateFormat' => null,
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
            [
                'class' => Handler\StreamHandler::class,
                'constructor' => [
                    'stream' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
                    'level' => Level::Info,
                ],
                'formatter' => [
                    'class' => Formatter\JsonFormatter::class,
                    'constructor' => [
                        'batchMode' => Formatter\JsonFormatter::BATCH_MODE_JSON,
                        'appendNewline' => true,
                    ],
                ],
            ],
        ],
    ],
];
```

Atau

```php

declare(strict_types=1);

use Monolog\Handler;
use Monolog\Formatter;
use Monolog\Level;

return [
    'default' => [
        'handlers' => ['single', 'daily'],
    ],

    'single' => [
        'handler' => [
            'class' => Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf.log',
                'level' => Level::Info,
            ],
        ],
        'formatter' => [
            'class' => Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ],
        ],
    ],

    'daily' => [
        'handler' => [
            'class' => Handler\StreamHandler::class,
            'constructor' => [
                'stream' => BASE_PATH . '/runtime/logs/hyperf-debug.log',
                'level' => Level::Info,
            ],
        ],
        'formatter' => [
            'class' => Formatter\JsonFormatter::class,
            'constructor' => [
                'batchMode' => Formatter\JsonFormatter::BATCH_MODE_JSON,
                'appendNewline' => true,
            ],
        ],
    ],
];

```

Hasilnya adalah sebagai berikut:

```shell
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```


### Logging Terpadu di Tingkat Request

Terkadang, kita perlu mengaitkan log untuk request yang sama. Oleh karena itu, kita dapat mengimplementasikan sebuah `Processor`.

```php
<?php

declare(strict_types=1);

namespace App\Kernel\Log;

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class AppendRequestIdProcessor implements ProcessorInterface
{
    public const REQUEST_ID = 'log.request.id';

    public function __invoke(array|LogRecord $record)
    {
        $record['extra']['request_id'] = Context::getOrSet(self::REQUEST_ID, uniqid());
        $record['extra']['coroutine_id'] = Coroutine::id();
        return $record;
    }

}
```

Kemudian konfigurasikan ke dalam konfigurasi `logger.php` kita:

```php
<?php

declare(strict_types=1);

use App\Kernel\Log;

return [
    'default' => [
        // Hapus konfigurasi lainnya
        'processors' => [
            [
                'class' => Log\AppendRequestIdProcessor::class,
            ],
        ],
    ],
];
```
