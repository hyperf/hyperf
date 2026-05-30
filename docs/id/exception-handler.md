# Exception Handler

Dalam `Hyperf`, semua kode bisnis dieksekusi pada `Worker Process`. Dalam hal
ini, ketika terjadi exception yang tidak ditangkap pada suatu request, `Worker
Process` yang bersangkutan akan terinterupsi dan keluar (exit), yang mana hal
ini tidak dapat diterima oleh suatu layanan. Menangkap exception dan
menampilkan konten error yang wajar juga lebih ramah bagi klien. Kita dapat
mendefinisikan `ExceptionHandler` yang berbeda untuk setiap `server`, dan
begitu ada exception yang tidak ditangkap di dalam proses, exception tersebut
akan diteruskan ke `ExceptionHandler` yang terdaftar untuk diproses.

## Kustomisasi Penanganan Exception

### Mendaftarkan Exception Handler

Saat ini, pendaftaran `ExceptionHandler` hanya didukung melalui file
konfigurasi. File konfigurasi terletak di `config/autoload/exceptions.php`.
Konfigurasikan exception handler kustom Anda di bawah `server` yang sesuai:

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // http di sini sesuai dengan nilai name untuk server di config/autoload/server.php
        'http' => [
            // Pendaftaran exception handler dilakukan dengan mengonfigurasi namespace class lengkap di sini
            \App\Exception\Handler\FooExceptionHandler::class,
        ],    
    ],
];
```

### Mendaftarkan exception handler melalui [annotation](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterHandler;

// http di sini sesuai dengan nilai name untuk server di config/autoload/server.php
// priority digunakan untuk pengurutan
#[RegisterHandler(server: 'http')]
class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        return $response->withHeader('Server', 'Hyperf')->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}

```

> Urutan setiap array konfigurasi exception handler menentukan urutan
penyampaian exception antar handler.

### Mendefinisikan Exception Handler

Kita dapat mendefinisikan sebuah `class` di mana saja dan mewarisi abstract
class `Hyperf\ExceptionHandler\ExceptionHandler` serta mengimplementasikan
method abstract di dalamnya. Seperti contoh di bawah ini:

```php
<?php
namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use App\Exception\FooException;
use Throwable;

class FooExceptionHandler extends  ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // Menentukan apakah exception yang ditangkap adalah exception yang diinginkan
        if ($throwable instanceof FooException) {
            // Output terformat
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);

            // Mencegah penggelembungan (bubbling)
            $this->stopPropagation();
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        // Serahkan ke exception handler berikutnya
        return $response;

        // Atau langsung menyembunyikan exception tanpa pemrosesan
    }

    /**
     * Tentukan apakah exception handler perlu menangani exception atau tidak
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```

### Mendefinisikan Exception Class

```php
<?php
namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class FooException extends ServerException
{
}
```

### Memicu Exception

```php
namespace App\Controller;

use App\Exception\FooException;

class IndexController extends AbstractController
{
    public function index()
    {
        throw new FooException('Foo Exception...', 800);
    }
}

```
Pada contoh di atas, kita mengasumsikan bahwa `FooException` adalah exception
yang dilemparkan, dan exception handler telah dikonfigurasi. Ketika exception
yang tidak ditangkap dilemparkan, exception tersebut akan diteruskan sesuai
dengan urutan pendaftaran handler. Bayangkan proses ini seperti sebuah pipa
(pipe), exception tidak akan diteruskan lagi setelah ada handler yang memanggil
`$this->stopPropagation()`. Handler default Hyperf akan menjadi yang terakhir
menangkap exception jika tidak ada handler lain yang menangkap exception
tersebut.

## Integrasi Whoops

Framework menyediakan integrasi Whoops.

Instal Whoops terlebih dahulu:
```php
composer require --dev filp/whoops
```

Kemudian konfigurasikan exception handler khusus untuk Whoops.

```php
// config/autoload/exceptions.php
return [
    'handler' => [
        'http' => [
            \Hyperf\ExceptionHandler\Handler\WhoopsExceptionHandler::class,
        ],    
    ],
];
```

Seperti yang ditunjukkan pada gambar:

![whoops](/imgs/whoops.png)


## Listener Error

Framework menyediakan listener level error `error_reporting()` yaitu
`Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.

### Konfigurasi

Tambahkan listener di `config/autoload/listeners.php`

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

Ketika kode yang mirip seperti berikut muncul, `\ErrorException` akan
dilemparkan:

```php
<?php
try {
    $a = [];
    var_dump($a[1]);
} catch (\Throwable $throwable) {
    var_dump(get_class($throwable), $throwable->getMessage());
}

// string(14) "ErrorException"
// string(19) "Undefined offset: 1"
```

Jika tidak ada listener yang dikonfigurasi, exception tidak akan dilemparkan.

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```
