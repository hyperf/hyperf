# Controller

Buat nanganin HTTP request lewat controller, Anda perlu ngikat route ke method controller pake `file konfigurasi` atau `annotation`. Detailnya ada di bagian [Router](id/router.md).
Soal `Request` dan `Response`, Hyperf nyediain `Hyperf\HttpServer\Contract\RequestInterface` dan `Hyperf\HttpServer\Contract\ResponseInterface` buat ngambil input parameters dan balikin data. Buat detail lebih lanjut, liat bagian [Request](id/request.md) dan [Response](id/response.md).

## Menulis Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // Mendefinisikan RequestInterface dan ResponseInterface pada parameter untuk mendapatkan objek terkait,
    // yang akan secara otomatis diinjeksikan oleh dependency injection container
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> Kita anggap `Controller` ini udah define route `/` lewat file konfigurasi, tapi Anda juga bisa pake annotation routing.

Panggil alamat ini melalui `cURL` untuk melihat konten yang dikembalikan.

```bash
$ curl 'http://127.0.0.1:9501/?target=Hyperf'
Hello Hyperf.
```

## Menghindari Kebingungan Data Antar Coroutine

Di framework PHP-FPM tradisional, biasanya ada `AbstractController` atau parent class buat Controller. Controller yang dibuat harus mewarisinya biar bisa dapetin data request atau ngelakuin operasi return. Di Hyperf, Anda **gak bisa ngelakuin ini** karena sebagian besar objek di Hyperf, termasuk `Controller`, berupa `Singleton`, demi reuse objek yang lebih baik. Data yang terkait request harus disimpen di `Coroutine Context`. Makanya, pas nulis kode, pastikan **jangan** nyimpen data spesifik request di class attributes, termasuk non-static attributes.

Sebenernya kalau Anda memang mau nyimpen data request lewat class attributes, ya bisa sih. Coba perhatiin: pas kita dapetin objek `Request` dan `Response`, kita inject `Hyperf\HttpServer\Contract\RequestInterface` dan `Hyperf\HttpServer\Contract\ResponseInterface`. Bukankah objeknya juga singleton? Trus gimana keamanan coroutine-nya? Ambil `RequestInterface` sebagai contoh, objek `Hyperf\HttpServer\Request` di dalemnya ngambil `PSR-7 Request object` dari `Coroutine Context`. Jadi class yang dipake sebenarnya cuma proxy class, dan yang beneran dipanggil itu hasil dari `Coroutine Context`.
