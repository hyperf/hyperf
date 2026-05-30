# Objek Request

`Objek Request (Request)` diimplementasikan sepenuhnya berdasarkan standar
[PSR-7](https://www.php-fig.org/psr/psr-7/) dan diimplementasikan oleh
[hyperf/http-message](https://github.com/hyperf/http-message).

> Perhatikan bahwa standar [PSR-7](https://www.php-fig.org/psr/psr-7/) `Request`
> dirancang dengan `immutable mechanism` (mekanisme tidak dapat diubah), semua
> method yang dimulai dengan awalan `with` mengembalikan objek baru dan tidak
> akan mengubah nilai dari objek aslinya.

## Instalasi

Komponen ini sepenuhnya independen dan cocok untuk proyek framework apa pun.

```bash
composer require hyperf/http-message
```

> Jika digunakan dalam proyek framework lain, hanya API yang disediakan oleh
> PSR-7 yang didukung. Untuk detailnya, Anda dapat merujuk langsung ke
> spesifikasi relevan dari PSR-7. Penggunaan yang dijelaskan dalam dokumen ini
> terbatas pada penggunaan saat menggunakan Hyperf.

## Mendapatkan Objek Request

Anda dapat menginjeksikan `Hyperf\HttpServer\Contract\RequestInterface` melalui
container untuk mendapatkan `Hyperf\HttpServer\Request` yang sesuai. Objek yang
sebenarnya diinjeksikan adalah objek proxy yang mengimplementasikan `PSR-7
request object (Request)` untuk setiap request, yang berarti objek ini hanya
dapat diperoleh selama siklus hidup `onRequest`. Berikut adalah contoh cara
mendapatkan objek request:

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

Jika Anda ingin mendapatkan parameter routing melalui parameter method controller,
Anda dapat mencantumkan parameter yang sesuai setelah dependency, dan framework
akan secara otomatis menginjeksikan parameter tersebut ke dalam parameter
method. Sebagai contoh, jika route Anda didefinisikan sebagai berikut:

```php
// Metode annotation
#[GetMapping(path: "/user/{id:\d+}")]

// Metode konfigurasi
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

Maka Anda dapat mendapatkan parameter `query` `id` dengan mendeklarasikan
parameter `$id` pada parameter method, seperti yang ditunjukkan di bawah ini:

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

Selain mendapatkan parameter route melalui dependency injection, Anda juga dapat
mendapatkan parameter route melalui method `route` dari objek request, seperti
yang ditunjukkan di bawah ini:

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
        // Jika ada, kembalikan nilai; jika tidak ada, kembalikan nilai default null
        $id = $request->route('id');

        // Jika ada, kembalikan nilai; jika tidak ada, kembalikan nilai default 0
        $id = $request->route('id', 0);
        // ...
    }
}
```

### Request Path & Method

Selain menggunakan `API` yang ditentukan oleh standar
[PSR-7](https://www.php-fig.org/psr/psr-7/)
`Hyperf\HttpServer\Contract\RequestInterface`, objek request juga menyediakan
berbagai method untuk mengakses data request. Di bawah ini adalah daftar
beberapa contoh method:

#### Mendapatkan Request Path

Method `path()` mengembalikan informasi path yang di-request. Dengan kata lain,
jika alamat tujuan dari request yang masuk adalah
`http://domain.com/foo/bar?baz=1`, maka `path()` akan mengembalikan `foo/bar`:

```php
$uri = $request->path();
```

Method `is(...$patterns)` dapat memverifikasi apakah path request yang masuk
cocok dengan aturan yang ditentukan. Saat menggunakan method ini, Anda juga
dapat meneruskan karakter `*` sebagai wildcard:

```php
if ($request->is('user/*')) {
    // ...
}
```

#### Mendapatkan Request URL

Anda dapat menggunakan method `url()` atau `fullUrl()` untuk mendapatkan `URL`
lengkap dari request yang masuk. Method `url()` mengembalikan `URL` tanpa
`query parameters`, dan nilai kembalian dari method `fullUrl()` berisi `query
parameters`:

```php
// Tanpa query parameter
$url = $request->url();

// Dengan query parameter
$url = $request->fullUrl();
```

#### Mendapatkan Request Method

Method `getMethod()` akan mengembalikan method request dari `HTTP`. Anda juga
dapat menggunakan method `isMethod(string $method)` untuk memverifikasi apakah
method request dari `HTTP` cocok dengan aturan yang ditentukan:

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### PSR-7 Request dan Method

Komponen message [hyperf/http-message](https://github.com/hyperf/http-message)
sendiri merupakan implementasi dari komponen standar
[PSR-7](https://www.php-fig.org/psr/psr-7/) dan method interface dapat
dipanggil melalui objek request (Request) yang diinjeksikan.
Jika request dideklarasikan sebagai interface standar
[PSR-7](https://www.php-fig.org/psr/psr-7/)
`Psr\Http\Message\ServerRequestInterface` selama injeksi, framework akan
secara otomatis mengonversinya ke objek `Hyperf\HttpServer\Request` yang setara
yang mengimplementasikan `Hyperf\HttpServer\Contract\RequestInterface`.

> Disarankan untuk menggunakan `Hyperf\HttpServer\Contract\RequestInterface`
> untuk injeksi agar Anda mendapatkan dukungan fitur auto-complete dari IDE
> untuk method-method eksklusif.

## Preprocessing & Normalisasi Input

### Mendapatkan Input

#### Mendapatkan Semua Input

Anda dapat menggunakan method `all()` untuk mendapatkan semua data input dalam
bentuk `array`:

```php
$all = $request->all();
```

#### Mendapatkan Nilai Input yang Ditentukan

Gunakan `input(string $key, $default = null)` dan `inputs(array $keys, $default
= null): array` untuk mendapatkan `satu` atau `beberapa` nilai input dari
bentuk apa pun:

```php
// Jika ada, kembalikan nilai; jika tidak ada, kembalikan null
$name = $request->input('name');

// Jika ada, kembalikan nilai; jika tidak ada, kembalikan nilai default Hyperf
$name = $request->input('name', 'Hyperf');
```

Jika data form yang dikirimkan berisi data dalam bentuk array, Anda dapat
menggunakan dot syntax (sintaks titik) untuk mendapatkan nilai bersarang dari
array tersebut:

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```

#### Mendapatkan Input dari Query String

Gunakan method `input` atau `inputs` untuk mendapatkan data input dari seluruh
request (termasuk `query parameters`), dan method `query(?string $key = null,
$default = null)` untuk mendapatkan input hanya dari query string:

```php
// Jika ada, kembalikan nilai; jika tidak ada, kembalikan null
$name = $request->query('name');

// Jika ada, kembalikan nilai; jika tidak ada, kembalikan nilai default Hyperf
$name = $request->query('name', 'Hyperf');

// Jika tidak mengirimkan parameter, semua Query parameter dikembalikan sebagai array asosiatif
$name = $request->query();
```

#### Mendapatkan Informasi Input `JSON`

Jika format data `body` request adalah `JSON`, selama nilai header `Content-Type`
dari `objek Request (Request)` diatur dengan benar ke `application/json`, Anda
dapat menggunakan method `input(string $key, $default = null)` untuk mengakses
data `JSON` dan Anda bahkan dapat menggunakan dot syntax untuk membaca array
`JSON`:

```php
// Jika ada, kembalikan nilai; jika tidak ada, kembalikan null
$name = $request->input('user.name');

// Jika ada, kembalikan nilai; jika tidak ada, kembalikan nilai default Hyperf
$name = $request->input('user.name', 'Hyperf');

// Mengembalikan semua data Json dalam bentuk array
$name = $request->all();
```

#### Menentukan Apakah Nilai Input Ada

Untuk menentukan apakah suatu nilai ada dalam request, Anda dapat menggunakan
method `has($keys)`. Jika nilai tersebut ada dalam request, method akan
mengembalikan `true`, jika tidak ada akan mengembalikan `false`. Parameter
pertama dapat berupa string atau array yang berisi beberapa string. Dalam kasus
terakhir, method akan mengembalikan `true` hanya jika semua key yang ditentukan
ada:

```php
// Hanya memeriksa satu nilai
if ($request->has('name')) {
    // ...
}

// Memeriksa beberapa nilai sekaligus
if ($request->has(['name', 'email'])) {
    // ...
}
```

### Cookies

#### Mendapatkan Cookies dari Request

Gunakan method `getCookieParams()` untuk mendapatkan semua `Cookies` dari
request sebagai array asosiatif.

```php
$cookies = $request->getCookieParams();
```

Anda dapat menggunakan method `cookie(string $key, $default = null)` untuk
mendapatkan nilai dari cookie yang sesuai:

 ```php
// Jika ada, kembalikan nilai; jika tidak ada, kembalikan null
$name = $request->cookie('name');

// Jika ada, kembalikan nilai; jika tidak ada, kembalikan nilai default Hyperf
$name = $request->cookie('name', 'Hyperf');
 ```

### File

#### Mendapatkan File yang Diunggah

Anda dapat menggunakan method `file(string $key, $default):
?Hyperf\HttpMessage\Upload\UploadedFile` untuk mendapatkan objek file yang
diunggah dari request. Jika file yang diunggah ada, method ini mengembalikan
instance dari kelas `Hyperf\HttpMessage\Upload\UploadedFile`, yang mewarisi
kelas `SplFileInfo` dari `PHP` dan juga menyediakan berbagai method untuk
berinteraksi dengan file tersebut:

```php
// Jika ada, kembalikan objek Hyperf\HttpMessage\Upload\UploadedFile; jika tidak ada, kembalikan null
$file = $request->file('photo');
```

#### Memeriksa Apakah File Ada

Anda dapat menggunakan method `hasFile(string $key): bool` untuk mengonfirmasi
apakah ada file dalam request:

```php
if ($request->hasFile('photo')) {
    // ...
}
```

#### Memverifikasi Keberhasilan Unggahan

Selain memeriksa apakah file yang diunggah ada, Anda juga dapat memverifikasi
apakah file yang diunggah valid melalui method `isValid(): bool`:

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

#### Path & Ekstensi File

Kelas `UploadedFile` juga berisi method untuk mengakses path lengkap file dan
ekstensinya. Method `getExtension()` akan menentukan ekstensi file berdasarkan
konten file tersebut. Ekstensi ini mungkin berbeda dari ekstensi yang diberikan
oleh client:

```php
// Path ini adalah path sementara file yang diunggah
$path = $request->file('photo')->getPath();

// Karena tmp_name file upload Swoole tidak mempertahankan nama file asli, method ini telah ditulis ulang untuk mendapatkan suffix nama file asli
$extension = $request->file('photo')->getExtension();
```

#### Menyimpan File yang Diunggah

File yang diunggah disimpan di lokasi sementara sebelum disimpan secara manual.
Jika Anda tidak menyimpan file tersebut, file akan dihapus dari lokasi sementara
setelah request selesai. Gunakan `moveTo(string $targetPath): void` untuk
memindahkan file sementara ke lokasi `$targetPath` untuk penyimpanan persisten.
Contoh kodenya adalah sebagai berikut:

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// Menentukan apakah file sudah dipindahkan melalui method isMoved(): bool
if ($file->isMoved()) {
    // ...
}
```

## Event Terkait

Ketika `enable_request_lifecycle` diaktifkan pada konfigurasi service, setiap
request yang masuk dapat memicu tiga event berikut.

### Contoh Konfigurasi

> Kode lain yang tidak terkait dihapus dari contoh berikut.

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
                // Whether to enable request lifecycle event
                'enable_request_lifecycle' => false,
            ],
        ],
    ],
];

```

### Daftar Event

- Hyperf\HttpServer\Event\RequestReceived

Event ini dipicu saat request diterima.

- Hyperf\HttpServer\Event\RequestHandled

Event ini dipicu saat request selesai diproses.

- Hyperf\HttpServer\Event\RequestTerminated

Event ini dipicu saat coroutine yang membawa request saat ini dihancurkan.
