# Middleware

Middleware di sini merujuk pada `middleware mode`, yang merupakan fungsi utama
dalam komponen [hyperf/http-server](https://github.com/hyperf/http-server). Ini
terutama digunakan untuk menenun (weave) seluruh proses dari `Request` ke
`Response`. Diimplementasikan berdasarkan [PSR-15](https://www.php-fig.org/psr/psr-15/).

## Prinsip

*Middleware terutama digunakan untuk menenun seluruh proses dari `Request` ke
`Response`.* Melalui pengaturan beberapa middleware, aliran data dijalankan
sesuai urutan yang kita tentukan. Esensi dari middleware adalah `Onion model`
(model bawang merah). Penjelasannya melalui diagram berikut:

![middleware](middleware.jpg)

Urutan pada gambar diatur dengan urutan `Middleware 1 -> Middleware 2 -> Middleware 3`.
Kita dapat melihat bahwa ketika garis horizontal tengah melewati `kernel`, yaitu
`Middleware 3`, ia kembali ke `Middleware 2`, ini adalah model bersarang (nested
model), sehingga urutan sebenarnya adalah:
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`
Fokusnya adalah pada `kernel`, yaitu `Middleware 3`, yang merupakan titik pembagi
dari bawang merah tersebut. Bagian sebelum titik batas tersebut sebenarnya diproses
berdasarkan `Request`, dan setelah melewati titik batas tersebut, `kernel`
menghasilkan objek `Response`, yang juga merupakan target kode utama dari `kernel`.
Setelah itu, objek `Response` ditangani oleh middleware lainnya. `Kernel` biasanya
diimplementasikan oleh framework, dan sisanya diserahkan kepada Anda.

## Mendefinisikan middleware global

Middleware global HANYA dapat dikonfigurasi melalui file konfigurasi. File
konfigurasi terletak di `config/autoload/middlewares.php` dan konfigurasinya
adalah sebagai berikut:
```php
<?php
return [
    // `http` corresponds to the value corresponding to the name attribute of each server in config/autoload/server.php. This configuration is only applied to the server you configured.
    'http' => [
        // Configure your global middleware in an array, in order according to the order of the array
        YourMiddleware::class
    ],
];
```
Cukup konfigurasi middleware global Anda di dalam file tersebut beserta `Server Name`
yang sesuai, yang berarti semua request di bawah `Server` tersebut akan menerapkan
middleware global yang telah dikonfigurasi.

## Mendefinisikan middleware lokal

Ketika beberapa middleware kita hanya ditujukan untuk request atau controller
tertentu saja, kita dapat mendefinisikannya sebagai middleware lokal, yang dapat
didefinisikan melalui file konfigurasi atau melalui annotation.

### Didefinisikan melalui file konfigurasi

Ketika mendefinisikan route menggunakan file konfigurasi, disarankan untuk
mendefinisikan middleware yang sesuai melalui file konfigurasi tersebut. Konfigurasi
middleware lokal akan diselesaikan pada konfigurasi routing.
Parameter terakhir `$options` dari setiap metode pendefinisian route pada class
`Hyperf\HttpServer\Router\Router` akan menerima sebuah array, yang dapat
didefinisikan dengan meneruskan key `middleware` dan nilai array untuk
mendefinisikan middleware dari route tersebut. Kami mendemonstrasikannya melalui
beberapa definisi route:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// Each route definition method can accept a $options parameter
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);

// All routings under the group will apply the configured middleware
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [ForMiddleware::class]]
);

```

### Didefinisikan melalui annotation

Ketika mendefinisikan route melalui annotation, kami menyarankan untuk mendefinisikan
middleware menggunakan annotation. Terdapat dua annotation untuk pendefinisian
middleware, yaitu:
  - Annotation `#[Middleware]` digunakan saat mendefinisikan satu middleware tunggal.
    Hanya satu annotation yang dapat didefinisikan di satu tempat, dan tidak dapat
    didefinisikan secara berulang.
  - Annotation `#[Middlewares]` digunakan saat mendefinisikan beberapa middleware.
    Hanya satu annotation yang dapat didefinisikan di satu tempat, kemudian beberapa
    definisi middleware dapat diimplementasikan dengan mendefinisikan beberapa
    annotation `#[Middleware]` di dalam annotation tersebut.

> Penggunaan `#[Middleware]` harus menggunakan namespace `use Hyperf\HttpServer\Annotation\Middleware;`;
> Penggunaan `#[Middlewares]` harus menggunakan namespace `use Hyperf\HttpServer\Annotation\Middlewares;`;

*Catatan: Ini harus digunakan bersama dengan `#[AutoController]` atau `#[Controller]`.*

Mendefinisikan satu middleware tunggal:

```php
<?php

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

Mendefinisikan beberapa middleware:

```php
<?php

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

#### Mendefinisikan middleware tingkat metode (method level)

Sangat mudah untuk mendefinisikan tingkat metode ketika mengonfigurasi middleware
melalui file konfigurasi. Bagaimana jika didefinisikan menggunakan annotation?
Anda hanya perlu mendefinisikan annotation secara langsung pada metode tersebut.
Middleware tingkat metode memiliki prioritas lebih tinggi daripada middleware
tingkat class. Mari kita lihat kodenya:

```php
<?php

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middleware(FooMiddleware::class)]
class IndexController
{
    
    #[Middleware(BarMiddleware::class)]
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```
#### Terkait

Membuat middleware menggunakan perintah:

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
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // According to the specific business judgment logic, it is assumed that the token carried by the user is valid here.
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => 'The token is invalid, preventing further execution.',
                ],
            ]
        );
    }
}
```
Urutan eksekusi middleware adalah `FooMiddleware -> BarMiddleware`.

## Urutan Eksekusi Middleware

Kita dapat melihat dari penjelasan di atas bahwa total ada 3 tingkat middleware,
yaitu `global middleware`, `class level middleware`, dan `method level middleware`.
Jika semua middleware ini didefinisikan, urutan eksekusinya adalah:
`Global Middleware -> Method Level Middleware -> Class Level Middleware`.

Pada versi `>=3.0.34`, konfigurasi prioritas baru telah ditambahkan, yang
memungkinkan Anda mengubah urutan eksekusi middleware saat mengonfigurasi metode
dan routing middleware. Semakin tinggi prioritasnya, semakin tinggi urutan eksekusinya.

```php
// middleware.php
return [
    'http' => [
        YourMiddleware::class,
        YourMiddlewareB::class => 3,
    ],
];
```
```php
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
#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(FooMiddlewareB::class, 3)]
#[Middlewares([FooMiddlewareC::class => 1, BarMiddlewareD::class => 4])]
class IndexController
{
    
}
```

## Mengubah objek request dan response secara global

Pertama, terdapat penyimpanan objek `request` dan `response` PSR-7 yang paling
primitif di dalam context coroutine. Sifat `immutable` yang disyaratkan oleh PSR-7
untuk objek terkait berarti bahwa `$response` yang kita panggil dengan
`$response = $response->with***()` bukanlah menulis ulang objek asli, melainkan
objek baru hasil dari `Clone`. Ini berarti objek `request` dan `response` yang
disimpan dalam context coroutine tidak akan berubah. Ketika kita memiliki beberapa
logika di middleware yang mengubah objek `request` atau `response`, dan kita
berharap agar kode berikutnya mendapatkan objek `request` atau `response` yang
telah diubah tersebut, kita dapat mengatur objek baru tersebut ke dalam context
setelah mengubah objeknya, seperti yang ditunjukkan dalam kode:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request and $response are the modified objects
$request = \Hyperf\Context\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Context\Context::set(ResponseInterface::class, $response);
```

## Kustomisasi perilaku CoreMiddleWare

Secara default, ketika Hyperf menangani route yang tidak dapat ditemukan atau HTTP
method tidak diizinkan, yaitu ketika HTTP status code adalah `404` or `405`,
`CoreMiddleware` secara langsung menanganinya dan mengembalikan objek response
yang sesuai. Berkat desain dependency injection Hyperf, Anda dapat mengarahkan
`CoreMiddleware` ke `CoreMiddleware` yang Anda implementasikan sendiri dengan
mengganti pemetaan objeknya.

Sebagai contoh, kita ingin mendefinisikan class `App\Middleware\CoreMiddleware`
untuk menimpa perilaku default. Pertama-tama kita dapat mendefinisikan class
`App\Middleware\CoreMiddleware` sebagai berikut. Di sini kita hanya mengambil HTTP
Server sebagai contoh. Server lain juga dapat menggunakan metode atau praktik
yang sama untuk mencapai tujuan yang sama.

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
     * Handle the response when cannot found any routes.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        // Rewrite the processing logic for route not found
        return $this->response()->withStatus(404);
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        // Rewrite processing logic that is not allowed by HTTP methods
        return $this->response()->withStatus(405);
    }
}
```

Kemudian definisikan hubungan objek di `config/autoload/dependencies.php` dan
tulis ulang objek CoreMiddleware:

```php
<?php
return [
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
```

> Metode penulisan ulang secara langsung CoreMiddleware di sini baru efektif pada
> versi 1.1.0 ke atas. Versi 1.0.x masih mengharuskan Anda untuk menulis ulang
> panggilan tingkat atas dari CoreMiddleware melalui DI, kemudian mengganti nilai
> yang diteruskan oleh CoreMiddleware dengan class middleware yang Anda definisikan.

## Middleware yang Umum Digunakan

### Middleware lintas domain (Cross-domain/CORS)

Jika Anda perlu menyelesaikan masalah lintas domain (cross-origin) di dalam
framework, Anda dapat mengimplementasikan middleware berikut sesuai dengan
kebutuhan Anda:

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
            // Headers can be rewritten according to actual conditions.
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}
```

Faktanya, konfigurasi lintas domain juga dapat langsung dipasang pada `Nginx`.

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

### Post-middleware

Biasanya, kode terakhir yang kita jalankan adalah

```
return $handler->handle($request);
```

Oleh karena itu, ini setara dengan pre-middleware. Jika Anda ingin membuat logika
middleware berjalan setelah proses (post-middleware), Anda hanya perlu mengubah
urutan eksekusinya.

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
        // TODO: pre-operation
        try{
            $result = $handler->handle($request);
        } finally {
            // TODO: post operation
        }
        return $result;
    }
}
```
