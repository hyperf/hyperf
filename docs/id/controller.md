# Controller

Untuk memproses HTTP request dengan menggunakan Controller, Anda perlu
menghubungkan routing dan method controller dengan cara `Config` atau
`Annotation`. Silakan baca bab [Router](id/route.md) untuk detail lebih lanjut.

Untuk `Request` dan `Response`, Hyperf menyediakan
`Hyperf\HttpServer\Contract\RequestInterface` dan
`Hyperf\HttpServer\Contract\ResponseInterface` bagi Anda untuk mendapatkan
parameter dan mengembalikan nilai. Silakan baca bab [Request](id/request.md)
dan [Response](id/response.md) untuk detail lebih lanjut.

## Membuat Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // Related objects will be automatically injected by the dependency injection container if you obtain such objects by defining RequestInterface and ResponseInterface on the parameters.
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> Diasumsikan `Controller` ini telah didefinisikan sebagai route `/` melalui
> `Config`. (Tentu saja, Anda juga dapat mendefinisikannya melalui `Annotation`)

Panggil alamat ini melalui `cURL`, dan Anda dapat melihat konten yang
dikembalikan.

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

## Menghindari kebingungan data (data confusion) antar coroutine

Dalam framework PHP-FPM tradisional, sebuah `AbstractController` (atau kelas
induk abstrak dengan nama lain) biasanya disediakan. Kemudian, `Controller`
lain yang didefinisikan akan melakukan beberapa request atau response
berdasarkan `AbstractController` tersebut. Namun, di Hyperf, **JANGAN LAKUKAN
HAL SEPERTI ITU**. Karena sebagian besar objek, termasuk `Controller`, ada
sebagai `Singleton` (yang juga bertujuan untuk penggunaan kembali objek yang
lebih baik), dan data request disimpan di `Context` dalam coroutine, maka
**JANGAN** menyimpan data request apa pun sebagai attribute kelas (termasuk
properti non-statis).

Tentu saja, bukan hal yang tidak mungkin jika Anda benar-benar ingin menyimpan
data request sebagai attribute kelas. Kami memperhatikan bahwa objek `Request`
dan `Response` didapatkan dengan menyuntikkan
`Hyperf\HttpServer\Contract\RequestInterface` dan
`Hyperf\HttpServer\Contract\ResponseInterface` saat kita mencoba mendapatkan
`Request` dan `Response`, sehingga objek yang bersangkutan juga merupakan
sebuah singleton. Bagaimana keamanan coroutine (coroutine safety) terjamin
di sini? Mengambil `RequestInterface` sebagai contoh, ketika objek
`Hyperf\HttpServer\Request` yang sesuai mendapatkan `PSR-7 request object` dari
bagian internalnya, objek tersebut diambil dari `Context`. Jadi kelas aktual
yang digunakan hanyalah proxy class, dan pemanggilan aktualnya didapatkan dari
`Context`.
