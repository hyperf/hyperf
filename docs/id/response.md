# Response

Dalam Hyperf, Anda dapat memperoleh objek proxy response dengan menginjeksikan
interface `Hyperf\HttpServer\Contract\ResponseInterface`. Secara default, DI
container akan mengembalikan objek `Hyperf\HttpServer\Response`. Anda dapat
memanggil semua method dari `Psr\Http\Message\ResponseInterface` secara
langsung melalui objek ini.

> Catatan: Objek response PSR-7 standar bersifat immutable. Nilai kembalian
> dari semua method yang diawali dengan `with` adalah objek baru dan tidak akan
> mengubah nilai dari objek aslinya.

## Return JSON

Anda dapat mengembalikan konten berformat `Json` secara cepat menggunakan
method `json($data)` dari `Hyperf\HttpServer\Contract\ResponseInterface`.
`Content-Type` dari objek response juga akan diatur ke `application/json`.
`$data` menerima array atau objek yang mengimplementasikan interface
`Hyperf\Contract\Arrayable`.

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

## Return XML

Anda dapat mengembalikan konten berformat `XML` secara cepat menggunakan
method `xml($data)` dari `Hyperf\HttpServer\Contract\ResponseInterface`.
`Content-Type` dari objek response juga akan diatur ke `application/xml`.
`$data` menerima array atau objek yang mengimplementasikan interface
`Hyperf\Contract\Xmlable`.

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

## Return the raw content

Anda dapat mengembalikan konten mentah (raw) secara cepat menggunakan method
`raw($data)` dari `Hyperf\HttpServer\Contract\ResponseInterface`.
`Content-Type` dari objek response juga akan diatur ke `plain/text`.
`$data` menerima string atau objek yang mengimplementasikan method
`__toString()`.

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

## Return view

Silakan merujuk ke [View](id/view.md).

## Redirection

`Hyperf\HttpServer\Contract\ResponseInterface` menyediakan method
`redirect(string $toUrl, int $status = 302, string $schema = 'http')` untuk
mengembalikan objek `Psr7ResponseInterface` yang telah dikonfigurasi dengan
status redirection.

`redirect`:   

|  Arguments  |  Type  | Default Value |                                                      Comment                                                      |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl  | string |   null   | Jika argumen tidak diawali dengan `http://` atau `https://`, URL yang sesuai akan digabungkan secara otomatis berdasarkan Host dari server saat ini, dan protokol penggabungan berdasarkan argumen `$schema` |
| status |  int   |  302   |                                                   Status code dari Response                                                   |
| schema | string |  http  |                 Berlaku ketika `$toUrl` tidak diawali dengan `http://` atau `https://`, hanya `http` atau `https` yang tersedia                |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // redirect() method will return an Psr\Http\Message\ResponseInterface object, needs to return the object.
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie

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

## Kompresi Gzip

## Chunk

## File Download

`Hyperf\HttpServer\Contract\ResponseInterface` menyediakan method
`download(string $file, string $name = '')` untuk mengembalikan objek
`Psr7ResponseInterface` yang telah dikonfigurasi dengan status download file.
Jika request mengandung header `if-match` atau `if-none-match`, Hyperf juga
akan membandingkannya dengan `ETag` sesuai standar protokol, dan jika cocok,
ia akan mengembalikan response dengan status code `304`.

`download`:   

| Arguments |  Type  | Default Value |                                Comment                                 |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string |   null   | Untuk mengembalikan path absolut dari file yang diunduh, gunakan konstanta `BASE_PATH` untuk menemukan direktori root proyek |
| name | string |   null   |         Nama file yang diunduh oleh klien, jika kosong, nama asli dari file yang diunduh akan digunakan          |


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
