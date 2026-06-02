# Memulai dengan Cepat

Biar lebih cepet paham `Hyperf`, bagian ini bakal pake studi kasus `Membuat HTTP Server`, implementasi `Web Service` sederhana lewat definisi route dan controller. Tapi `Hyperf` lebih dari sekedar itu; service governance, layanan `gRPC`, annotation, `AOP`, dan lainnya bakal dibahas di bab-bab terpisah.

## Mendefinisikan Access Routes

Hyperf pake [nikic/fast-route](https://github.com/nikic/FastRoute) sebagai default routing component. Anda bisa dengan gampang define route di `config/routes.php`.

Gak cuma itu, framework juga nyediain `Annotation Routing` yang powerful, praktis, dan fleksibel. Detailnya liat di bagian [Routing](id/router.md).

### Mendefinisikan Route melalui File Konfigurasi

File route terletak di `config/routes.php` dari project [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton). Berikut adalah beberapa contoh penggunaan yang umum.

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Contoh kode di sini menyediakan tiga metode binding yang berbeda untuk setiap contoh. Dalam konfigurasi aktual, hanya satu yang harus digunakan dan route yang sama hanya boleh didefinisikan sekali.

// Menyetel GET request route, mengikat alamat akses '/get' ke method get dari App\Controller\IndexController
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// Menyetel POST request route, mengikat alamat akses '/post' ke method post dari App\Controller\IndexController
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// Menyetel route yang mengizinkan request GET, POST dan HEAD, mengikat alamat akses '/multi' ke method multi dari App\Controller\IndexController
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### Mendefinisikan Route melalui Annotation

`Hyperf` nyediain [Annotation](id/annotation.md) yang powerful, praktis, dan fleksibel, yang jelas nyediain cara berbasis annotation buat define route. Hyperf punya dua jenis annotation: `#[Controller]` dan `#[AutoController]` buat mendefinisikan `Controller`. Ini cuma pengantar; detail lebih lanjut ada di bagian [Routing](id/router.md).

### Mendefinisikan Route melalui Annotation `#[AutoController]`

`#[AutoController]` nyediain binding route buat skenario akses sederhana. Pas pake `#[AutoController]`, Hyperf bakal otomatis parse semua method `public` dari class dan nyediain metode request `GET` dan `POST`.

> Saat menggunakan annotation `#[AutoController]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\AutoController`;

Controller dengan nama camelCase akan secara otomatis dikonversi menjadi route snake_case. Berikut adalah contoh korespondensi antara controller dan route aktual:

|      Controller      |              Annotation               |    Access Route    |
| :-------------------: | :------------------------------------: | :----------------: |
| MyDataController      |        @AutoController()               | /my_data/index     |
| MydataController      |        @AutoController()               | /mydata/index      |
| MyDataController      | @AutoController(prefix="/data")       |  /data/index       |

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf akan secara otomatis membuat route /index/index untuk method ini, mengizinkan request melalui GET atau POST
    public function index(RequestInterface $request)
    {
        // Mendapatkan parameter id dari request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Mendefinisikan Route melalui Annotation `#[Controller]`

`#[Controller]` ada untuk memenuhi kebutuhan definisi route yang lebih detail. Menggunakan annotation `#[Controller]` menandakan bahwa class saat ini adalah sebuah `Controller class`, dan perlu digunakan bersama dengan annotation `#[RequestMapping]` untuk memberikan definisi yang lebih detail untuk metode request dan path request.

Kami juga menyediakan berbagai `Mapping Annotations` yang cepat dan nyaman, seperti `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`, dan `#[DeleteMapping]`, yaitu 5 annotation praktis yang digunakan untuk menandakan metode request yang berbeda yang diizinkan.

> Saat menggunakan annotation `#[Controller]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\Controller`;   
> Saat menggunakan annotation `#[RequestMapping]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\RequestMapping`;   
> Saat menggunakan annotation `#[GetMapping]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\GetMapping`;   
> Saat menggunakan annotation `#[PostMapping]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\PostMapping`;   
> Saat menggunakan annotation `#[PutMapping]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\PutMapping`;   
> Saat menggunakan annotation `#[PatchMapping]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\PatchMapping`;   
> Saat menggunakan annotation `#[DeleteMapping]`, Anda perlu menggunakan namespace `Hyperf\HttpServer\Annotation\DeleteMapping`;  

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class IndexController
{
    // Hyperf akan secara otomatis membuat route /index/index untuk method ini, mengizinkan request melalui GET atau POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Mendapatkan parameter id dari request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

## Menangani HTTP Requests

`Hyperf` sepenuhnya terbuka. Intinya, gak ada aturan baku soal pola penanganan request. Anda bisa pake pola `MVC` tradisional atau pola `RequestHandler`.

Mari kita ambil pola `MVC` sebagai contoh:
Buat folder `Controller` di dalam folder `app` dan buat `IndexController.php` sebagai berikut. Method `index` mendapatkan parameter `id` dari request dan mengonversinya menjadi tipe `string` untuk dikembalikan ke client.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf akan secara otomatis membuat route /index/index untuk method ini, mengizinkan request melalui GET atau POST
    public function index(RequestInterface $request)
    {
        // Mendapatkan parameter id dari request
        $id = $request->input('id', 1);
        // Mengonversi $id ke format string dan mengembalikan nilai $id ke client dengan Content-Type plain/text
        return (string)$id;
    }
}
```

## Dependency Auto-Injection

Dependency auto-injection adalah fitur super powerful dari `Hyperf` dan jadi fondasi fleksibilitas framework.

`Hyperf` punya dua cara injection: pertama lewat constructor injection (standar), kedua lewat annotation `#[Inject]`. Di bawah ini contoh implementasi keduanya:

Misalnya kita punya class `\App\Service\UserService` dengan method `getInfoById(int $id)` yang nerima `id` dan balikin user entity. Return value-nya bukan fokus kita di sini. Yang pengen kita bahas adalah gimana cara dapetin `UserService` di class mana pun dan panggil method-nya. Biasanya sih pake `new UserService()`, tapi di `Hyperf` ada solusi yang lebih oke.

### Injection melalui Constructor

Cukup deklarasikan tipe parameter di constructor, dan `Hyperf` akan secara otomatis menginjeksikan objek atau nilai yang sesuai.
```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use App\Service\UserService;

#[AutoController]
class IndexController
{
    private UserService $userService;
    
    // Deklarasikan tipe parameter di constructor, dan Hyperf akan secara otomatis menginjeksikan objek atau nilai yang sesuai
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```

### Injection melalui Annotation `#[Inject]`

Cukup deklarasikan tipe properti class yang sesuai melalui `@var`, dan gunakan annotation `#[Inject]` untuk menandai properti tersebut. `Hyperf` akan secara otomatis menginjeksikan objek atau nilai yang sesuai.

> Saat menggunakan annotation `#[Inject]`, Anda perlu menggunakan namespace `Hyperf\Di\Annotation\Inject`;

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Di\Annotation\Inject;
use App\Service\UserService;

#[AutoController]
class IndexController
{

    #[Inject]
    private UserService $userService;
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```

Dari contoh di atas, keliatan kan bahwa `$userService` udah otomatis di-inject sebagai objek class tanpa perlu instansiasi manual.

Tapi contoh ini belum sepenuhnya nunjukin manfaat dependency auto-injection. Bayangin `UserService` punya banyak dependencies, dan dependencies itu juga punya dependencies lagi. Kalo pake `new`, kita harus instansiasi banyak objek manual dan atur posisi parameter. Tapi di `Hyperf`, kita gak perlu ngatur semua itu, tinggal deklarasiin class yang mau dipake.

Dan pas `UserService` butuh perubahan gede, misalnya dari local service ke RPC remote service, kita tinggal sesuaikan dependency configuration buat ngubah class yang terikat di key `UserService` ke class RPC service yang baru.

## Menjalankan Hyperf Service

Karena `Hyperf` punya built-in coroutine server, `Hyperf` bakal jalan sebagai `CLI`. Makanya, abis define route dan kode logika, kita perlu jalanin `php bin/hyperf.php start` dari command line di root project buat mulai service.

Ketika antarmuka `Console` menunjukkan bahwa service sudah berjalan, Anda bisa membuat request ke service tersebut secara normal melalui `cURL` atau browser. Secara default, service menyediakan halaman utama `http://127.0.0.1:9501/`. Untuk contoh yang dipandu dalam bab ini, alamat akses yang sesuai adalah `http://127.0.0.1:9501/index/info?id=1`.

## Memuat Ulang Kode

Karena `Hyperf` adalah aplikasi `CLI` yang persisten, begitu proses jalan, kode `PHP` yang udah diparsing bakal nempel di proses. Artinya, kalo Anda ubah kode `PHP` abis service mulai, perubahan gak bakal ngefek ke service yang udah jalan. Mau reload kode yang udah diubah? Hentikan service pake `CTRL + C` di `Console`, lalu jalanin ulang `php bin/hyperf.php start`.

> Tips: Anda juga bisa konfigurasi perintah start Server di IDE, biar tinggal pake tombol `start/stop` buat `start service` atau `restart service`.
> Selain itu, pas development non-view, Anda bisa pake [TDD (Test-Driven Development)](https://baike.baidu.com/item/TDD/9064369). Ini gak cuma ngilangin repot restart service dan pindah-pindah jendela, tapi juga ngejamin kebenaran data interface.

> Selain itu, bab [Hot Reload/Hot Update](id/awesome-components.md?id=%e7%83%ad%e6%9b%b4%e6%96%b0%e7%83%ad%e9%87%bd%e8%bd%bd) dalam dokumentasi menyediakan berbagai solusi yang didukung oleh developer komunitas. Jika Anda masih ingin menggunakan solusi Hot Reload/Hot Update, Anda bisa mempelajarinya lebih lanjut.

## Multi-port Listening

`Hyperf` bisa dengerin multiple ports, tapi karena objek `callbacks` diambil langsung dari container, `Hyperf\HttpServer\Server::class` yang sama bakal ketimpa di container. Makanya, kita perlu definisi ulang `Server` di dependency relationship biar objeknya terisolasi.

> Hal yang sama berlaku untuk WebSocket dan TCP Servers.

`config/autoload/dependencies.php`

```php
<?php

return [
    'InnerHttp' => Hyperf\HttpServer\Server::class,
];
```

`config/autoload/server.php`

```php
<?php
return [
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
        [
            'name' => 'innerHttp',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => ['InnerHttp', 'onRequest'],
            ],
        ],
    ]
];
```

Pada saat yang sama, `route file` atau `annotation` juga perlu menentukan `server` yang sesuai, sebagai berikut:

- Route file `config/routes.php`

```php
<?php
Router::addServer('innerHttp', function () {
    Router::get('/', 'App\Controller\IndexController@index');
});
```

- Annotation

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController(server: "innerHttp")]
class IndexController
{
    public function index()
    {
        return 'Hello World.';
    }
}
```

## Events

Selain event `Event::ON_REQUEST` yang disebutkan di atas, framework juga mendukung event lainnya. Nama-nama event tersebut adalah sebagai berikut:

|         Nama Event          |               Keterangan                |
| :---------------------: | :---------------------------------: |
|    Event::ON_REQUEST    |                                   |
|     Event::ON_START     | Event ini tidak valid di mode `SWOOLE_BASE` |
| Event::ON_WORKER_START  |                                   |
|  Event::ON_WORKER_EXIT  |                                   |
| Event::ON_PIPE_MESSAGE  |                                   |
|    Event::ON_RECEIVE    |                                   |
|    Event::ON_CONNECT    |                                   |
|  Event::ON_HAND_SHAKE   |                                   |
|     Event::ON_OPEN      |                                   |
|    Event::ON_MESSAGE    |                                   |
|     Event::ON_CLOSE     |                                   |
|     Event::ON_TASK      |                                   |
|    Event::ON_FINISH     |                                   |
|   Event::ON_SHUTDOWN    |                                   |
|    Event::ON_PACKET     |                                   |
| Event::ON_MANAGER_START |                                   |
| Event::ON_MANAGER_STOP  |                                   |
