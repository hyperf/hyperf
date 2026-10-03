# Middleware

Middleware di sini maksudnya "Middleware Pattern", fitur utama dari komponen [hyperf/http-server](https://github.com/hyperf/http-server) yang gunanya buat nyusun alur dari `Request` sampai `Response`. Fitur ini sepenuhnya berdasarkan [PSR-15](https://www.php-fig.org/psr/psr-15/).

## Prinsip

*Middleware gunanya buat nyusun alur dari `Request` hingga `Response`*. Dengan ngatur multiple middlewares, data bakal ngikutin alur yang udah ditentuin. Middleware pada dasarnya adalah `Onion Model`. Biar lebih jelas, liat diagram ini:

![middleware](middleware.jpg)

Diagram di atas ngatur urutan `Middleware 1 -> Middleware 2 -> Middleware 3`. Setelah lewat `Kernel` (Middleware 3), alurnya balik lagi ke `Middleware 2`, jadinya model bersarang. Urutan aslinya:
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`
Fokus di `Kernel`, yaitu `Middleware 3`. Ini titik potong onion-nya. Bagian sebelum titik potong semuanya ditangani berdasarkan `Request`. Pas lewat titik potong, `Kernel` menghasilkan objek `Response`, ini juga target kode utama `Kernel`. Setelah itu, `Response` ditangani. `Kernel` biasanya diimplementasiin oleh framework, sisanya terserah Anda.

## Mendefinisikan Global Middleware

Global middleware cuma bisa dikonfigurasi lewat file konfigurasi. Filenya ada di `config/autoload/middlewares.php`:

```php
<?php
return [
    // 'http' sesuai dengan nilai atribut name dari setiap server di config/autoload/server.php. Konfigurasi ini hanya berlaku untuk Server tersebut.
    'http' => [
        // Konfigurasikan global middleware Anda di dalam array, urutannya tergantung pada urutan array ini.
        YourMiddleware::class
    ],
];
```
Tinggal konfigurasi global middleware di file ini sesuai `Server Name` yang dimaksud, semua request di `Server` itu bakal pake middleware tersebut.

## Mendefinisikan Local Middleware

Kalo middleware cuma ditargetin ke request atau controller tertentu, bisa didefinisikan sebagai local middleware, lewat file konfigurasi atau annotation.

### Mendefinisikan melalui File Konfigurasi

Kalo pake file konfigurasi buat define route, Anda cuma bisa define middleware lewat file konfigurasi juga. Konfigurasi local middleware ditaruh di konfigurasi route.
Parameter terakhir `$options` dari tiap method definisi route di class `Hyperf\HttpServer\Router\Router` nerima array. Anda bisa define middleware buat route tersebut dengan ngasih key `middleware` dan nilai array. Langsung liat contoh:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// Setiap metode definisi route dapat menerima parameter $options
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);

// Semua route di bawah Group ini akan menerapkan middleware yang telah dikonfigurasi
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [FooMiddleware::class]]
);
```

### Mendefinisikan melalui Annotation

Kalo define route lewat annotation, Anda cuma bisa define middleware lewat annotation juga. Ada dua annotation:
  - `#[Middleware]`, buat satu middleware. Cuma satu annotation ini yang bisa dipasang di satu tempat, dan gak bisa didefinisikan ulang.
  - `#[Middlewares]`, buat multiple middlewares. Cuma satu annotation ini di satu tempat, lalu isi beberapa `#[Middleware]` di dalemnya.

> Gunakan namespace `use Hyperf\HttpServer\Annotation\Middleware;` saat menggunakan annotation `#[Middleware]`;
> Gunakan namespace `use Hyperf\HttpServer\Annotation\Middlewares;` saat menggunakan annotation `#[Middlewares]`;

***Catatan: Harus digunakan bersama dengan `#[AutoController]` atau `#[Controller]`***

Mendefinisikan single middleware:

```php
<?php
namespace App\Controller;

use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;

#[AutoController]
#[Middleware(FooMiddleware::class)]
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

Mendefinisikan multiple middlewares melalui annotation `#[Middlewares]`:

```php
<?php
namespace App\Controller;

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middlewares([FooMiddleware::class, BarMiddleware::class])]
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

Mendefinisikan multiple middlewares melalui annotation `#[Middleware]`:

```php
<?php
namespace App\Controller;

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(BarMiddleware::class)]
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

#### Mendefinisikan Method-Level Middleware

Pas konfigurasi lewat file, gampang aja nentuin middleware di level method. Tapi gimana kalo lewat annotation? Tinggal definisiin annotation langsung di method-nya.
Class-level middleware prioritasnya di atas method-level middleware. Liat contoh:

```php
<?php
namespace App\Controller;

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middlewares([FooMiddleware::class])]
class IndexController
{
    #[Middleware(BarMiddleware::class)]
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

#### Kode Terkait Middleware

Generate middleware:

```
php ./bin/hyperf.php gen:middleware Auth/FooMiddleware
```

```php
<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FooMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Menentukan alur logika berdasarkan bisnis spesifik, asumsikan token yang dibawa user valid di sini
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => 'Middleware token verification invalid, block continuing to execute downwards',
                ],
            ]
        );
    }
}
```
Urutan eksekusi middleware-nya: `FooMiddleware -> BarMiddleware`.

## Urutan Eksekusi Middleware

Dari penjelasan di atas, ada `3` level middleware: `Global Middleware`, `Class-level Middleware`, dan `Method-level Middleware`. Kalo semuanya didefinisikan, urutan eksekusinya: `Global Middleware -> Class-level Middleware -> Method-level Middleware`.

Di versi `>=3.0.34`, ada fitur prioritas. Anda bisa ngatur urutan middleware pas konfigurasi method atau route middleware. Makin tinggi prioritas, makin awal dieksekusi.

```php
// File konfigurasi global middleware middleware.php
return [
    'http' => [
        YourMiddleware::class,
        YourMiddlewareB::class => 3,
    ],
];
```
```php
// Konfigurasi route middleware
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    [
        'middleware' => [
            FooMiddleware::class,
            FooMiddlewareB::class => 3,
        ]
    ]
);
```
```php
// Konfigurasi annotation middleware
#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(FooMiddlewareB::class, 3)]
#[Middlewares([FooMiddlewareC::class => 1, BarMiddlewareD::class => 4])]
class IndexController
{
    
}
```

## Mengubah Request dan Response Objects Secara Global

Pertama, di coroutine context, PSR-7 `Request Object` dan `Response Object` asli udah tersimpan. Sesuai prinsip `immutability` dari PSR-7, `$response` yang didapet dari `$response = $response->with***()` bukan menimpa objek asli, melainkan objek `Clone` baru. Artinya, `Request Object` dan `Response Object` yang tersimpan di coroutine context gak bakal berubah. Nah, kalo middleware kita ngubah `Request Object` atau `Response Object`, dan kita pengen kode *non-passing* berikutnya dapetin objek yang udah diubah, kita bisa set objek baru ke dalam context abis ngubahnya:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request dan $response adalah objek yang telah dimodifikasi
$request = \Hyperf\Context\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Context\Context::set(ResponseInterface::class, $response);
```

## Menyesuaikan Perilaku CoreMiddleware

Secara default, pas Hyperf nemu route not found atau HTTP method not allowed (status code `404` atau `405`), langsung ditangani `CoreMiddleware` yang bakal balikin response object. Berkat desain dependency injection Hyperf, Anda bisa ganti `CoreMiddleware` dengan implementasi sendiri.

Misalnya, kita mau define class `App\Middleware\CoreMiddleware` buat ganti perilaku default. Pertama, define class-nya kayak gini. Di sini kita pake HTTP Server sebagai contoh, Server lain juga bisa pake cara yang sama.

```php
<?php
declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Contract\Arrayable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    /**
     * Menangani response ketika tidak ada route yang ditemukan.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        // Menimpa logika pemrosesan untuk route not found
        return $this->response()->withStatus(404);
    }

    /**
     * Menangani response ketika route ditemukan tetapi tidak cocok dengan metode yang tersedia.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        // Menimpa logika pemrosesan untuk HTTP method not allowed
        return $this->response()->withStatus(405);
    }
}
```

Kemudian definisikan object relationship di `config/autoload/dependencies.php` untuk mengganti objek CoreMiddleware:

```php
<?php
return [
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
```

> Cara langsung ganti CoreMiddleware ini cuma berlaku di v1.1.0+. Di v1.0.x, Anda masih perlu ganti panggilan level atas CoreMiddleware lewat DI, baru ganti nilai CoreMiddleware-nya.

## Middleware Umum

### CORS Middleware

Kalo perlu nanganin cross-domain di framework, tinggal implementasiin middleware kayak gini:

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            // Header bisa dimodifikasi sesuai kondisi aktual.
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}
```

Sebenarnya, konfigurasi cross-domain juga bisa langsung dipasang di `Nginx`.

```
location / {
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
    add_header Access-Control-Allow-Headers 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization';

    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```

### Post-Middleware

Biasanya, kita jalanin di akhir:

```
return $handler->handle($request);
```

Jadi ini sama dengan pre-middleware. Biar middleware jalan setelah proses (post-process), tinggal ubah urutan eksekusinya.

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OpenApiMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: Pre-operation
        try{
            $result = $handler->handle($request);
        } finally {
            // TODO: Post-operation
        }
        return $result;
    }
}
```
