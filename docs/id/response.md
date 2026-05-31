# Response

Di Hyperf, Anda bisa inject proxy object `Response` buat nanganin response lewat interface `Hyperf\HttpServer\Contract\ResponseInterface`. Secara default, ia ngembaliin objek `Hyperf\HttpServer\Response`, dan objek ini bisa langsung panggil semua method dari `Psr\Http\Message\ResponseInterface`.

> Catatan: Standar PSR-7 dirancang dengan `immutable mechanism` untuk Response. Semua return value dari method yang diawali dengan `with` adalah objek baru dan tidak akan mengubah nilai objek asli.

## Mengembalikan Format Json

`Hyperf\HttpServer\Contract\ResponseInterface` nyediain method `json($data)` buat balikin response ke format `Json` dan set `Content-Type` ke `application/json`. `$data` bisa array atau objek yang implementasiin interface `Hyperf\Contract\Arrayable`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function json(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        return $response->json($data);
    }
}
```

## Mengembalikan Format Xml

`Hyperf\HttpServer\Contract\ResponseInterface` nyediain method `xml($data)` buat balikin response ke format `XML` dan set `Content-Type` ke `application/xml`. `$data` bisa array atau objek yang implementasiin interface `Hyperf\Contract\Xmlable`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function xml(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        return $response->xml($data);
    }
}
```

## Mengembalikan Format Raw

`Hyperf\HttpServer\Contract\ResponseInterface` nyediain method `raw($data)` buat balikin response ke format `raw` dan set `Content-Type` ke `plain/text`. `$data` bisa string atau objek yang implementasiin `__toString()`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function raw(ResponseInterface $response): Psr7ResponseInterface
    {
        return $response->raw('Hello Hyperf.');
    }
}
```

## Mengembalikan View

Silakan merujuk ke bagian [View](id/view.md) dari dokumentasi.

## Redirect

`Hyperf\HttpServer\Contract\ResponseInterface` nyediain `redirect(string $toUrl, int $status = 302, string $schema = 'http')` buat balikin objek `Psr7ResponseInterface` dengan status redirect.

Method `redirect`:

| Parameter | Tipe | Nilai Default | Keterangan |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl | string | Tidak ada | Kalo parameter gak ada `http://` atau `https://`, bakal otomatis bikin URL berdasarkan Host service saat ini, plus protokol sesuai `$schema` |
| status | int | 302 | Response status code |
| schema | string | http | Berlaku ketika `toUrl` tidak mengandung `http://` atau `https://`, hanya `http` atau `https` yang bisa diberikan |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // Method redirect() mengembalikan objek Psr\Http\Message\ResponseInterface, yang perlu dikembalikan lagi
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie Setting

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Hyperf\HttpMessage\Cookie\Cookie;

class IndexController
{
    public function cookie(ResponseInterface $response): Psr7ResponseInterface
    {
        $cookie = new Cookie('key', 'value');
        return $response->withCookie($cookie)->withContent('Hello Hyperf.');
    }
}
```

## Chunked Transfer Encoding

`Hyperf\HttpServer\Contract\ResponseInterface` nyediain `write(string $data)` buat ngirim response ke browser secara bertahap dan set `Transfer-Encoding` ke `chunked`. `$data` bisa string atau objek yang implementasiin `__toString()`.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Swoole\Coroutine;
use Hyperf\Engine\Http\EventStream;

class IndexController
{
    public function index(ResponseInterface $response)
    {
       $response
            ->withStatus(200)
            ->withHeader('X-Event-Mode', 'Enabled') // ⭐ Custom Header
            ->withHeader('X-Stream-Time', '5s');
        $streamer = new EventStream($this->response->getConnection(), $response);
        $startTime = time();
        $totalSteps = 5;
        $streamer->write("data: --- 🚀 EventStream started (total {$totalSteps} steps) ---\n\n");
        for ($i = 1; $i <= $totalSteps; ++$i) {
            Coroutine::sleep(1);
            $elapsed = time() - $startTime;
            $message = "data: 【{$i} second】data block sent. Time elapsed: {$elapsed} seconds\n\n";
            $streamer->write($message);
        }
        $streamer->write("data: --- ✅ EventStream ended ---\n\n");
        $streamer->end();

        return 'Hello Hyperf';
    }
}
```

!> Catatan: Abis panggil `write` buat ngirim data bertahap, kalo Anda pake `return` buat balikin data lagi, data tersebut gak bakal tampil. Di contoh di atas, `Hello Hyperf` gak bakal muncul, cuma `data: 【{$i} second】data block sent. Time elapsed: {$elapsed} seconds\n\n`.

## File Download

`Hyperf\HttpServer\Contract\ResponseInterface` menyediakan `download(string $file, string $name = '')` untuk mengembalikan objek `Psr7ResponseInterface` dengan status file download yang telah ditentukan.

Jika request mengandung header `if-match` atau `if-none-match`, Hyperf juga akan membandingkannya dengan `ETag` sesuai dengan standar protokol. Jika cocok, response dengan status code `304` akan dikembalikan.

Method `download`:

| Parameter | Tipe | Nilai Default | Keterangan |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string | Tidak ada | Path absolut ke file yang akan dikembalikan untuk di-download. Gunakan konstanta BASE_PATH untuk menemukan direktori root project |
| name | string | Tidak ada | Nama file untuk client download. Jika kosong, nama asli file yang di-download akan digunakan |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function index(ResponseInterface $response): Psr7ResponseInterface
    {
        return $response->download(BASE_PATH . '/public/file.csv', 'filename.csv');
    }
}
```
