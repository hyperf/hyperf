# Request Object

`Request Object` diimplementasikan sepenuhnya berdasarkan standar [PSR-7](https://www.php-fig.org/psr/psr-7/), dan implementasinya didukung oleh komponen [hyperf/http-message](https://github.com/hyperf/http-message).

> Catatan: Standar [PSR-7](https://www.php-fig.org/psr/psr-7/) dirancang dengan `immutable mechanism` untuk `Request`. Semua return value dari method yang diawali dengan `with` adalah objek baru dan tidak akan mengubah nilai objek asli.

## Instalasi

Komponen ini sepenuhnya independen dan cocok untuk project framework apapun.

```bash
composer require hyperf/http-message
```

> Kalo dipake di project framework lain, cuma API dari PSR-7 yang didukung. Detailnya liat spesifikasi PSR-7. Pemakaian yang dijelasin di dokumen ini cuma berlaku pas pake Hyperf.

## Mendapatkan Request Object

Anda bisa dapetin `Hyperf\HttpServer\Request` dengan cara inject `Hyperf\HttpServer\Contract\RequestInterface` lewat container. Objek yang di-inject sebenarnya adalah proxy object, yang diproxy adalah `PSR-7 Request Object` untuk tiap request. Artinya, objek ini cuma bisa diakses dalam siklus hidup `onRequest`. Contohnya:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request)
    {
        // ...
    }
}
```

### Dependency Injection dan Parameter

Kalo mau dapetin route parameters lewat parameter method controller, tinggal tulis parameter yang dimau setelah dependencies. Framework bakal otomatis inject parameter yang sesuai. Misalnya, kalo route-nya didefinisikan kayak gini:

```php
// Mode Annotation
#[GetMapping(path: "/user/{id:\d+}")]
// Mode Konfigurasi
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

Anda bisa mendapatkan parameter `Query` `id` dengan mendeklarasikan parameter `$id` di parameter method, seperti yang ditunjukkan di bawah ini:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request, int $id)
    {
        // ...
    }
}
```

Selain lewat dependency injection, route parameters juga bisa diakses pake method `route`:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request)
    {
        // Mengembalikan jika ada, jika tidak mengembalikan nilai default null
        $id = $request->route('id');
        // Mengembalikan jika ada, jika tidak mengembalikan nilai default 0
        $id = $request->route('id', 0);
        // ...
    }
}
```

### Request Path & Method

Selain API dari standar [PSR-7](https://www.php-fig.org/psr/psr-7/), `Hyperf\HttpServer\Contract\RequestInterface` juga nyediain berbagai method buat ngeliat request. Berikut contoh beberapa method:

#### Mendapatkan Request Path

Method `path()` ngembaliin informasi path dari request. Maksudnya, kalo alamat request-nya `http://domain.com/foo/bar?baz=1`, maka `path()` bakal ngembaliin `foo/bar`:

```php
$uri = $request->path();
```

Method `is(...$patterns)` buat ngecek apakah path request cocok sama aturan tertentu. Pake karakter `*` sebagai wildcard:

```php
if ($request->is('user/*')) {
    // ...
}
```

#### Mendapatkan Request URL

Pake method `url()` atau `fullUrl()` buat dapetin `URL` lengkap dari request. `url()` ngembaliin `URL` tanpa `Query Parameters`, sedangkan `fullUrl()` nyertain `Query Parameters`:

```php
// Tanpa query parameters
$url = $request->url();

// Dengan query parameters
$url = $request->fullUrl();
```

#### Mendapatkan Request Method

Method `getMethod()` akan mengembalikan metode `HTTP` request. Anda juga bisa menggunakan method `isMethod(string $method)` untuk memverifikasi apakah metode `HTTP` request cocok dengan aturan yang ditentukan:

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### PSR-7 Request dan Methods

Komponen [hyperf/http-message](https://github.com/hyperf/http-message) itu sendiri adalah komponen yang mengimplementasikan standar [PSR-7](https://www.php-fig.org/psr/psr-7/). Method terkait bisa dipanggil melalui `Request Object` yang diinjeksikan.
Jika dideklarasikan sebagai interface `Psr\Http\Message\ServerRequestInterface` dari standar [PSR-7](https://www.php-fig.org/psr/psr-7/) saat injeksi, framework akan secara otomatis mengonversinya menjadi objek `Hyperf\HttpServer\Request`, yang setara dengan `Hyperf\HttpServer\Contract\RequestInterface`.

> Disarankan pake `Hyperf\HttpServer\Contract\RequestInterface` buat inject, biar dapet auto-completion IDE buat method khusus.

## Input Pre-processing & Normalization

### Mendapatkan Input

#### Mendapatkan Semua Input

Pake method `all()` buat dapetin semua data input dalam bentuk `array`:

```php
$all = $request->all();
```

#### Mendapatkan Nilai Input Tertentu

Mendapatkan `satu` atau `lebih` nilai input dalam bentuk apapun melalui `input(string $key, $default = null)` dan `inputs(array $keys, $default = null): array`:

```php
// Mengembalikan jika ada, jika tidak mengembalikan null
$name = $request->input('name');
// Mengembalikan jika ada, jika tidak mengembalikan nilai default Hyperf
$name = $request->input('name', 'Hyperf');
```

Kalo data form yang dikirim bentuknya `array`, pake sintaks `dot` buat ngaksesnya:

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```

#### Mendapatkan Input dari Query String

Menggunakan method `input`, `inputs` bisa mendapatkan data input (termasuk `Query Parameters`) dari seluruh request, sementara method `query(?string $key = null, $default = null)` hanya bisa mendapatkan data input dari query string:

```php
// Mengembalikan jika ada, jika tidak mengembalikan null
$name = $request->query('name');
// Mengembalikan jika ada, jika tidak mengembalikan nilai default Hyperf
$name = $request->query('name', 'Hyperf');
// Mengembalikan semua Query parameters dalam bentuk associative array jika tidak ada parameter yang diberikan
$name = $request->query();
```

#### Mendapatkan Informasi Input `JSON`

Jika format data dari `Body` request adalah `JSON`, selama `Content-Type` `Header Value` dari `Request Object` telah diatur dengan benar ke `application/json`, Anda bisa mengakses data `JSON` melalui method `input(string $key, $default = null)`, dan Anda bahkan bisa menggunakan sintaks `dot` untuk membaca array `JSON`:

```php
// Mengembalikan jika ada, jika tidak mengembalikan null
$name = $request->input('user.name');
// Mengembalikan jika ada, jika tidak mengembalikan nilai default Hyperf
$name = $request->input('user.name', 'Hyperf');
// Mengembalikan semua data Json dalam bentuk array
$name = $request->all();
```

#### Menentukan Keberadaan Nilai Input

Untuk menentukan apakah suatu nilai ada di request, Anda bisa menggunakan method `has($keys)`. Jika nilai tersebut ada di request, maka mengembalikan `true`, jika tidak mengembalikan `false`. `$keys` bisa berupa string, atau array yang berisi multiple strings. Nilai `true` hanya akan dikembalikan jika semuanya ada:

```php
// Hanya mengecek satu nilai
if ($request->has('name')) {
    // ...
}
// Mengecek beberapa nilai sekaligus
if ($request->has(['name', 'email'])) {
    // ...
}
```

### Cookies

#### Mendapatkan Cookies dari Request

Gunakan method `getCookieParams()` untuk mendapatkan semua `Cookies` dari request, yang akan mengembalikan associative array.

```php
$cookies = $request->getCookieParams();
```

Jika Anda ingin mendapatkan nilai `Cookie` tertentu, Anda bisa mendapatkan nilai yang sesuai melalui method `cookie(string $key, $default = null)`:

 ```php
// Mengembalikan jika ada, jika tidak mengembalikan null
$name = $request->cookie('name');
// Mengembalikan jika ada, jika tidak mengembalikan nilai default Hyperf
$name = $request->cookie('name', 'Hyperf');
 ```

### Files

#### Mendapatkan Uploaded Files

Anda bisa menggunakan method `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` untuk mendapatkan uploaded file object dari request. Jika uploaded file ada, method ini mengembalikan instance dari class `Hyperf\HttpMessage\Upload\UploadedFile`. Class ini mewarisi class `SplFileInfo` dari `PHP` dan juga menyediakan berbagai method untuk berinteraksi dengan file:

```php
// Mengembalikan objek Hyperf\HttpMessage\Upload\UploadedFile jika ada, jika tidak mengembalikan null
$file = $request->file('photo');
```

#### Memeriksa Apakah File Ada

Anda bisa menggunakan method `hasFile(string $key): bool` untuk mengonfirmasi apakah suatu file ada di request:

```php
if ($request->hasFile('photo')) {
    // ...
}
```

#### Memverifikasi Keberhasilan Upload

Selain memeriksa apakah uploaded file ada, Anda juga bisa memverifikasi apakah uploaded file valid melalui method `isValid(): bool`:

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

#### File Path & Extension

Class `UploadedFile` juga berisi method untuk mengakses path lengkap dan extension dari file. Method `getExtension()` menentukan extension file berdasarkan konten file. Extension ini mungkin berbeda dengan extension yang disediakan oleh client:

```php
// Path ini adalah temporary path dari uploaded file
$path = $request->file('photo')->getPath();

// Karena tmp_name uploaded file Swoole tidak mempertahankan nama file asli, method ini telah ditulis ulang untuk mendapatkan suffix dari nama file asli
$extension = $request->file('photo')->getExtension();
```

#### Menyimpan Uploaded Files

File upload nyimpan di lokasi sementara sebelum Anda simpen secara permanen. Kalo gak disimpen, file bakal dihapus setelah request selesai. Makanya kita perlu nyimpen file secara permanen. Pake `moveTo(string $targetPath): void` buat mindahin temporary file ke `$targetPath`. Contohnya:

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// Menentukan apakah method telah dipindahkan melalui method isMoved(): bool
if ($file->isMoved()) {
    // ...
}
```

## Event Terkait

Kalo `enable_request_lifecycle` diaktifin di konfigurasi service, setiap request bakal memicu tiga event berikut:

### Contoh Konfigurasi

> Berikut menghapus kode lain yang tidak relevan

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Hyperf\Server\ServerInterface;

return [
    'servers' => [
        [
            'name' => 'http',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
            'options' => [
                // Apakah akan mengaktifkan request lifecycle event
                'enable_request_lifecycle' => false,
            ],
        ],
    ],
];
```

### Daftar Event

- Hyperf\HttpServer\Event\RequestReceived

Event ini dipicu ketika sebuah request diterima.

- Hyperf\HttpServer\Event\RequestHandled

Event ini dipicu ketika request telah diproses.

- Hyperf\HttpServer\Event\RequestTerminated

Event ini dipicu ketika coroutine yang membawa request saat ini dihancurkan.
