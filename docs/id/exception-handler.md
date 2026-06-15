# Exception Handler

Di `Hyperf`, kode bisnis jalan di `Worker processes`. Artinya, kalo ada exception yang gak tertangkap di request mana pun, `Worker process` yang bersangkutan bakal mati. Ini gak bisa ditolerir, lebih baik exception ditangkap dan pesan error yang jelas dikasih ke client.
Kita bisa define `ExceptionHandlers` yang beda buat tiap `server`. Kalo ada exception yang gak tertangkap di alur bisnis, exception bakal diterusin ke `ExceptionHandler` yang udah terdaftar buat diproses.

## Menyesuaikan Exception Handler

### Mendaftarkan Exception Handler melalui File Konfigurasi

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // 'http' di sini sesuai dengan nilai atribut name dari server di config/autoload/server.php
        'http' => [
            // Konfigurasikan alamat namespace class yang lengkap di sini untuk menyelesaikan pendaftaran exception handler ini
            \App\Exception\Handler\FooExceptionHandler::class,
        ],    
    ],
];
```

### Mendaftarkan Exception Handler melalui [Annotation](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterHandler;

// 'http' di sini sesuai dengan nilai atribut name dari server di config/autoload/server.php
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

> Urutan di array konfigurasi exception handler nentuin urutan penyebaran exception antar handler.

### Mendefinisikan Exception Handler

Kita bisa define `Class` di mana aja, warisi abstract class `Hyperf\ExceptionHandler\ExceptionHandler`, lalu implementasiin abstract method-nya:

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
        // Menentukan apakah exception yang ditangkap adalah exception yang ingin Anda tangkap
        if ($throwable instanceof FooException) {
            // Output yang diformat
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);

            // Menghentikan propagasi exception
            $this->stopPropagation();
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        // Menyerahkan ke exception handler berikutnya
        return $response;

        // Atau tidak menanganinya dan langsung menutup exception
    }

    /**
     * Menentukan apakah exception handler ini harus menangani exception ini
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
Di contoh di atas, anggap `FooException` udah ada dan handler-nya udah dikonfigurasi. Nah, pas business logic lempar exception yang gak tertangkap, exception bakal diterusin secara berurutan sesuai konfigurasi. Prosesnya mirip pipeline. Kalo exception handler sebelumnya manggil `$this->stopPropagation()`, exception gak bakal diterusin. Kalo handler terakhir yang dikonfigurasi juga gak nangkep dan nanganin exception, maka exception bakal diserahin ke default exception handler Hyperf.

## Mengintegrasikan Whoops

Framework punya integrasi Whoops.

Pertama, install Whoops
```php
composer require --dev filp/whoops
```

Kemudian konfigurasikan exception handler Whoops khusus.

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

Efeknya seperti yang ditunjukkan pada gambar:

![whoops](id/imgs/whoops.png)

## Error Listener

Framework nyediain listener buat level error `error_reporting()` yaitu `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.

### Konfigurasi

Tambahkan listener ke `config/autoload/listeners.php`

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

Kalo ada kode kayak gini, exception `\ErrorException` bakal dilempar:

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

Kalo listener gak dikonfigurasi, hasilnya bakal kayak gini, gak ada exception yang dilempar:

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```
