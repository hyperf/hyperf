# Logger

Komponen `hyperf/logger` diimplementasikan berdasarkan
[psr/logger](https://github.com/php-fig/log), dan
[monolog/monolog](https://github.com/Seldaek/monolog) digunakan secara default
sebagai driver. Beberapa konfigurasi log disediakan secara default dalam proyek
`hyperf-skeleton`, dan `Monolog\Handler\StreamHandler` digunakan secara default.
Karena `Swoole` telah meng-coroutine-kan fungsi-fungsi seperti `fopen`,
`fwrite`, selama parameter `useLocking` tidak disetel ke `true`, coroutine
tersebut aman (coroutine-safe).

## Instalasi

```shell
composer require hyperf/logger
```

## Konfigurasi

Beberapa konfigurasi log disediakan secara default dalam proyek
`hyperf-skeleton`. Secara default, file konfigurasi log adalah
`config/autoload/logger.php`. Contohnya adalah sebagai berikut:

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

## Petunjuk Penggunaan

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
        // The first parameter corresponds to the name of the log, and the second parameter corresponds to the key in config/autoload/logger.php
        $this->logger = $loggerFactory->get('log', 'default');
    }

    public function method()
    {
        // Do something.
        $this->logger->info("Your log message.");
    }
}
```

## Pengetahuan Dasar tentang Monolog

Mari kita lihat beberapa konsep dasar yang terlibat dalam monolog dengan kode
berikut:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

// Create a Channel. The parameter log is the name of the Channel
$log = new Logger('log');

// Create two Handlers, corresponding to variables $stream and $fire
$stream = new StreamHandler('test.log', Logger::WARNING);
$fire = new FirePHPHandler();

// Define the time format as "Y-m-d H:i:s"
$dateFormat = "Y n j, g:i a";
// Define the log format as "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
$output = "%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n";
// Create a Formatter based on the time format and log format
$formatter = new LineFormatter($output, $dateFormat);

// Set Formatter to Handler
$stream->setFormatter($formatter);

// Push the Handler into the Handler queue of the Channel
$log->pushHandler($stream);
$log->pushHandler($fire);

// Clone new log channel
$log2 = $log->withName('log2');

// Add records to the log
$log->warning('Foo');

// Add extra data to record
// 1. log context
$log->error('a new user', ['username' => 'daydaygo']);
// 2. processor
$log->pushProcessor(function ($record) {
    $record['extra']['dummy'] = 'hello';
    return $record;
});
$log->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor());
$log->alert('czl');
```

- Pertama, instansiasi `Logger` dan tentukan nama yang sesuai dengan `channel`.
- Anda dapat mengikat (bind) beberapa `Handler` ke `Logger`. `Logger` melakukan
  pencatatan log, lalu menyerahkannya ke `Handler` untuk diproses.
- `Handler` dapat menentukan **log level** mana yang perlu diproses, seperti
  `Logger::WARNING` atau hanya memproses log dengan tingkat log
  `>=Logger::WARNING`.
- Siapa yang akan memformat log? `Formatter` yang akan melakukannya. Cukup setel
  Formatter dan ikat ke `Handler` yang sesuai.
- Bagian log yang disertakan:
  `"%datetime%||%channel||%level_name%||%message%||%context%||%extra%\n"`.
- Bedakan informasi tambahan yang ditambahkan dalam log `context` dan `extra`:
  `context` ditentukan secara tambahan oleh pengguna saat mencatat log, yang
  bersifat lebih fleksibel; sedangkan `extra` ditambahkan secara tetap oleh
  `Processor` yang terikat pada `Logger`, yang lebih cocok untuk mengumpulkan
  beberapa **informasi umum**.

## Penggunaan Lebih Lanjut

### Mengenkapsulasi Kelas `Log`

Terkadang, Anda mungkin ingin mempertahankan kebiasaan logging seperti pada
kebanyakan framework. Maka Anda dapat membuat kelas `Log` di bawah `App`, dan
memanggil magic static method `__callStatic` untuk mengakses `Logger` dan setiap
level logging. Mari kita demonstrasikan melalui kode:

> Ingatlah untuk tidak mengaitkan nama logger dengan request, seperti
> menghubungkan $request_id sebagai nama logger. Hal ini dapat menyebabkan
> objek log tingkat request disimpan di dalam factory, yang mengakibatkan
> memory leak yang serius.

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

Secara default, `Channel` bernama `app` digunakan untuk mencatat log. Anda juga
dapat menggunakan metode `Log::get($name)` untuk mendapatkan `Logger` dari
`Channel` yang berbeda. `Container` yang andal dapat membantu Anda menyelesaikan
semua itu.

### Log stdout

Secara default, output log dari komponen framework didukung oleh kelas
implementasi dari interface `Hyperf\Contract\StdoutLoggerInterface`, yaitu
`Hyperf\Framework\Logger\StdoutLogger`. Kelas implementasi ini hanya untuk
mengeluarkan informasi relevan pada `stdout` melalui `print_r()`, yang merupakan
`terminal` tempat memulai `Hyperf`. Dalam hal ini, `monolog` sebenarnya tidak
digunakan. Bagaimana jika Anda ingin menggunakan `monolog` agar konsisten?

Tentu saja, hal ini dapat dilakukan melalui `Container` yang andal.

- Pertama, implementasikan kelas `StdoutLoggerFactory`. Penggunaan `Factory`
  dapat dijelaskan lebih rinci dalam bab [Dependency Injection](id/di.md).

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

- Deklarasikan dependensi, pekerjaan `StdoutLoggerInterface` dilakukan oleh
  kelas yang diinstansiasi oleh `StdoutLoggerFactory` sebagai dependensi aktual.

```php
// config/autoload/dependencies.php
return [
    \Hyperf\Contract\StdoutLoggerInterface::class => \App\StdoutLoggerFactory::class,
];
```

### Output format log yang berbeda di lingkungan (environment) yang berbeda

Banyak penggunaan di atas hanya untuk `Logger` di monolog. Mari kita lihat
`Handler` dan `Formatter`.

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
]
```

- `Handler` bernama `default` dikonfigurasi secara default, dan berisi informasi
  tentang `Handler` ini serta `Formatter`-nya.
- Saat mendapatkan `Logger`, jika `Handler` tidak ditentukan, lapisan bawah
  (underlying) secara otomatis akan mengikat `default(Handler)` ke `Logger`.
- Lingkungan dev (development): Menggunakan `php://stdout` untuk mengeluarkan
  log ke `stdout`, dan menyetel `allowInlineLineBreaks` pada `Formatter`, yang
  memudahkan untuk melihat log multi-baris.
- Lingkungan non-dev: Log menggunakan `JsonFormatter`, yang akan diformat sebagai
  `json` dan memudahkan pengiriman ke layanan log pihak ketiga.

### Rotasi file log berdasarkan tanggal

Jika Anda ingin file log dirotasi sesuai dengan tanggal, Anda dapat menggunakan
`Monolog\Handler\RotatingFileHandler` yang disediakan oleh `Monolog`.
Konfigurasinya adalah sebagai berikut:

Ubah file konfigurasi `config/autoload/logger.php`, ganti `Handler` menjadi
`Monolog\Handler\RotatingFileHandler::class` dan ubah field `stream` menjadi
`filename`.

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

Jika Anda ingin melakukan pemotongan log (log cutting) yang lebih detail, Anda
juga dapat memperluas (extend) kelas `Monolog\Handler\RotatingFileHandler` dan
mengimplementasikan kembali metode `rotate()`.

### Mengonfigurasi beberapa `Handler`

Pengguna dapat memodifikasi `handlers` sehingga grup log terkait dapat mendukung
beberapa `handlers`. Sebagai contoh, pada konfigurasi berikut, ketika pengguna
mengirimkan log dengan level lebih tinggi dari `INFO`, log tersebut akan ditulis
di `hyperf.log` and `hyperf-debug.log`. Ketika pengguna mengirimkan log `DEBUG`,
log tersebut hanya akan ditulis di `hyperf-debug.log`.

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

Hasilnya adalah sebagai berikut

```shell
==> runtime/logs/hyperf.log <==
[2019-11-08 11:11:35] hyperf.INFO: 5dc4dce791690 [] []

==> runtime/logs/hyperf-debug.log <==
{"message":"5dc4dce791690","context":[],"level":200,"level_name":"INFO","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597153","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
{"message":"xxxx","context":[],"level":100,"level_name":"DEBUG","channel":"hyperf","datetime":{"date":"2019-11-08 11:11:35.597635","timezone_type":3,"timezone":"Asia/Shanghai"},"extra":[]}
```

### Log Terpadu Tingkat Request

Terkadang kita perlu menghubungkan log yang berasal dari request yang sama. Untuk
itu, kita dapat mengimplementasikan sebuah Processor.

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

Kemudian konfigurasikan processor tersebut ke konfigurasi `logger.php`.

```php
<?php

declare(strict_types=1);

use App\Kernel\Log;

return [
    'default' => [
        // Konfigurasi lain dihapus
        'processors' => [
            [
                'class' => Log\AppendRequestIdProcessor::class,
            ],
        ],
    ],
];

```
