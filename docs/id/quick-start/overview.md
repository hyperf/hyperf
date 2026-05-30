# QuickStart

Sebagai contoh cara menggunakan `Hyperf`, halaman ini akan `membuat HTTP
Server` untuk mengimplementasikan `Web Service` sederhana dengan mendefinisikan
route dan controller. Hyperf dapat melakukan jauh lebih banyak hal, tetapi
fitur seperti service governance, layanan `gRPC`, pemrograman annotations,
`AOP`, dan fitur lainnya akan dijelaskan pada bab-bab khusus.

## Mendefinisikan route

`Hyperf` menggunakan [nikic/fast-route](https://github.com/nikic/FastRoute)
sebagai komponen routing bawaan, sehingga Anda dapat mendefinisikan route
dengan mudah di `config/routes.php`. `Hyperf` juga menyediakan fitur
`Annotation Routing` yang sangat andal dan nyaman.

Untuk informasi lebih lanjut tentang routing di luar contoh yang ditunjukkan di
bawah ini, silakan merujuk ke bab [Router](id/router.md).

### Mendefinisikan route melalui konfigurasi file

File route berada di `config/routes.php` pada proyek
[hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton). Di bawah ini
adalah beberapa contoh penggunaan umum:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// The code example here provides three different binding definitions for each example. In practice, you only need to define one of them.

// Set the route for a GET request, bind the access address '/get' to App\Controller\IndexController::get()
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// Set the route for a POST request, bind the access address '/post' to App\Controller\IndexController::post()
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// Set a route that allows GET, POST, and HEAD requests, bind the access address '/multi' to App\Controller\IndexController::multi()
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### Mendefinisikan route melalui annotations

`Hyperf` menyediakan fitur [Annotations](id/annotation.md) yang membuatnya
cepat dan mudah untuk mendefinisikan route. Hyperf menyediakan annotation
`#[Controller]` and `#[AutoController]` untuk digunakan dalam kelas
`Controller`. Untuk instruksi mendalam, silakan merujuk ke bab
[Routing](id/router.md). Berikut adalah beberapa contoh cepat:

### Mendefinisikan route melalui `#[AutoController]`

`#[AutoController]` menyediakan binding routing otomatis untuk sebagian besar
skenario routing sederhana. Saat menggunakan `#[AutoController]`, `Hyperf`
akan secara otomatis mem-parse semua method `public` dari kelas tersebut dan
menyediakan request `GET` dan `POST` untuk masing-masing method tersebut.

> Annotation `#[AutoController]` memerlukan namespace `use Hyperf\HttpServer\Annotation\AutoController;`

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Mendefinisikan route melalui `#[Controller]`

Untuk definisi routing yang lebih fleksibel, `#[Controller]` dapat digunakan
sebagai pengganti `#[AutoController]`. Menggunakan annotation `#[Controller]`
pada sebuah kelas menjadikannya sebuah `Controller class`, dan annotation
`#[RequestMapping]` dapat digunakan untuk mendefinisikan method dan path
request.

`Hyperf` juga menyediakan berbagai `Mapping annotations` yang cepat dan nyaman,
seperti `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`,
`#[DeleteMapping]`, yang dapat menggantikan `#[RequestMapping]` untuk menghemat
waktu Anda ketika suatu route hanya membutuhkan satu HTTP method saja.

> Annotation `#[Controller]` memerlukan namespace `use Hyperf\HttpServer\Annotation\Controller;`
> Annotation `#[RequestMapping]` memerlukan namespace `use Hyperf\HttpServer\Annotation\RequestMapping;` 
> Annotation `#[GetMapping]` memerlukan namespace `use Hyperf\HttpServer\Annotation\GetMapping;`  
> Annotation `#[PostMapping]` memerlukan namespace `use Hyperf\HttpServer\Annotation\PostMapping;` 
> Annotation `#[PutMapping]` memerlukan namespace `use Hyperf\HttpServer\Annotation\PutMapping;`  
> Annotation `#[PatchMapping]` memerlukan namespace `use Hyperf\HttpServer\Annotation\PatchMapping;`
> Annotation `#[DeleteMapping]` memerlukan namespace `use Hyperf\HttpServer\Annotation\DeleteMapping;`

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
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```


## Menangani HTTP Request

`Hyperf` bersifat tidak kaku (*unopinionated*). Tidak ada keharusan bagi Anda
untuk mengimplementasikan pemrosesan HTTP request menggunakan format tertentu.
Anda dapat menggunakan `MVC mode` tradisional atau `RequestHandler mode` untuk
menangani request. Mari kita ambil contoh menggunakan `MVC mode`:

Buat folder `Controller` di dalam folder `app` lalu buat file
`IndexController.php`. Method `index` akan mengambil parameter `id` dari
request, mengonversinya menjadi tipe `string`, dan mengembalikannya ke client.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        // Transfer $id parameter to a string, and return $id to the client with Content-Type:plain/text
        return (string)$id;
    }
}
```

## Dependency Auto-Injection

Dependency injection adalah fitur sangat andal yang disediakan oleh `Hyperf` dan
merupakan fondasi bagi fleksibilitas framework ini.

`Hyperf` menyediakan dua metode injection, yaitu melalui constructor injection,
dan satunya lagi melalui annotation injection `#[Inject]`. Di bawah ini adalah
contoh untuk kedua metode tersebut:

Misalkan kita memiliki kelas `\App\Service\UserService`. Terdapat method
`getInfoById(int $id)` di dalam kelas tersebut yang menerima argumen `id` dan
mengembalikan sebuah entitas user. Tipe kembalian (*return type*) dan bagian
internal dari kelas tersebut tidak relevan dengan dokumentasi ini, sehingga
kita tidak perlu terlalu memperhatikannya. Yang kita inginkan adalah mendapatkan
`UserService` ke dalam kelas kita dan menggunakan method dari kelas tersebut.
Cara biasa adalah dengan menginstansiasi kelas `UserService` melalui `new
UserService()`, tetapi dengan menggunakan dependency injection pada `Hyperf`,
kita memiliki solusi yang lebih baik.

### Injection melalui constructor

Deklarasikan tipe parameter di dalam argumen constructor, dan `Hyperf` akan
secara otomatis melakukan inject objek atau nilai yang sesuai.

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
    
    // Declare the parameter type within the constructor's arguments, and Hyperf will automatically inject the corresponding object or value.
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

### Injection melalui annotation `#[Inject]`

Deklarasikan tipe parameter di atas properti kelas yang sesuai menggunakan
`@var` dan gunakan annotation `#[Inject]`. `Hyperf` akan secara otomatis
melakukan inject objek atau nilai yang sesuai.

> Annotation `#[Inject]` memerlukan namespace `use Hyperf\Di\Annotation\Inject;`

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
   
Pada contoh di atas, kita dapat melihat dengan jelas bahwa `$userService` tidak
diinstansiasi secara manual, melainkan objek kelas yang sesuai dengan properti
tersebut di-inject secara otomatis oleh `Hyperf`.

Namun, contoh kasus ini belum benar-benar menunjukkan kekuatan sesungguhnya dari
dependency injection. Kita asumsikan bahwa `UserService` memiliki dependency-nya
sendiri, dan dependency tersebut juga memiliki banyak dependency lainnya,
sehingga kelas apa pun yang Anda definisikan harus menginstansiasi banyak objek
secara manual dan mengelola urutan argumen setiap kelas. Di `Hyperf`, kita tidak
perlu mengelola dependency ini secara manual, cukup deklarasikan nama kelas
dari argumen yang kita butuhkan, dan `Hyperf` akan melakukan semua pekerjaan itu
untuk kita.

Ketika `UserService` perlu mengalami perubahan internal yang drastis seperti
mengganti layanan lokal dengan layanan remote RPC, kita hanya perlu menyesuaikan
definisi kelas pada `UserService.php` untuk mengganti layanan lama dengan
layanan RPC baru dalam satu file saja.

## Memulai server

Karena `Hyperf` memiliki coroutine server bawaan, `Hyperf` akan berjalan sebagai
proses `CLI`. Setelah mendefinisikan route dan menulis kode logika aplikasi, kita
dapat memulai server dengan masuk ke direktori utama (*root*) proyek dan
menjalankan perintah `php bin/hyperf.php start`.

Ketika `console` menunjukkan bahwa server telah dimulai, Anda dapat mengakses
server melalui `cURL` atau browser. Secara default, URL untuk contoh dependency
injection di atas adalah `http://127.0.0.1:9501/index/info?id=1`.

## Memuat ulang kode

`Hyperf` adalah aplikasi `CLI` yang persisten. Setelah proses dimulai, kode
`PHP` yang telah di-parse akan tetap tidak berubah selama proses berjalan,
sehingga perubahan pada kode `PHP` setelah server dimulai tidak akan berpengaruh.
Jika Anda ingin server memuat ulang kode Anda, Anda perlu menghentikan proses
dengan menekan `CTRL + C` di `console` lalu menjalankan kembali perintah
`php bin/hyperf.php start`.

> Tip: Anda juga dapat mengonfigurasi perintah untuk mengelola Server di IDE
> Anda, dan Anda dapat dengan cepat menjalankan operasi `Memulai Server` atau
> `Memuat ulang kode` secara langsung melalui tombol `Start/Stop` pada IDE.
