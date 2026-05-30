# Routing

Secara default, routing menggunakan paket
[nikic/fast-route](https://github.com/nikic/FastRoute). Komponen
[hyperf/http-server](https://github.com/hyperf/http-server) bertanggung jawab
untuk menghubungkan ke server `Hyperf`, sedangkan routing `RPC` diimplementasikan
oleh komponen [hyperf/rpc-server](https://github.com/hyperf/rpc-server).

## HTTP routing

### Mendefinisikan routing melalui file konfigurasi

Di dalam skeleton [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton),
semua definisi routing secara default didefinisikan di file `config/routes.php`.
`Hyperf` juga mendukung `annotation routing`, yang merupakan metode yang
direkomendasikan, terutama ketika ada banyak route.

#### Mendefinisikan route menggunakan closure

Hanya dibutuhkan sebuah URI dan sebuah closure (Closure) untuk membuat route
dasar:

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

Sekarang Anda dapat mengakses route tersebut dengan mengirim request ke
`http://host:port/hello-hyperf` melalui browser atau command line `cURL`.

#### Mendefinisikan routing standar

Routing standar mengacu pada routing yang ditangani oleh `controller` dan
`action`. Metode ini sangat mirip dengan definisi closure, dengan perbedaan
jelas bahwa logika bisnis dapat didelegasikan ke kelas controller masing-masing:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Any of the following three definitions can achieve the same effect
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

Route tersebut didefinisikan untuk menghubungkan path `/hello-hyperf` ke method
`hello` di bawah `App\Controller\IndexController`.

#### Method routing yang tersedia

Router menyediakan beberapa method untuk membantu Anda meregistrasikan HTTP
request routing apa pun:

```php
use Hyperf\HttpServer\Router\Router;

// Register the route of the HTTP METHOD consistent with the method name
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// Register the route of any HTTP METHOD
Router::addRoute($httpMethod, $uri, $callback);
```

Kadang-kadang Anda mungkin perlu meregistrasikan route yang dapat merespons
beberapa HTTP method yang berbeda sekaligus. Hal ini dapat dicapai dengan
menggunakan method `addRoute`:

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST','PUT','DELETE'], $uri, $callback);
```

#### Cara mendefinisikan route groups

Route group menambahkan prefix group ke setiap URI. Route sebenarnya adalah
`group/route`, yaitu `/user/index`, `/user/store`, `/user/update`,
`/user/delete`

```php
Router::addGroup('/user/',function (){
    Router::get('index','App\Controller\UserController@index');
    Router::post('store','App\Controller\UserController@store');
    Router::get('update','App\Controller\UserController@update');
    Router::post('delete','App\Controller\UserController@delete');
});
```

### Mendefinisikan routing via annotations

`Hyperf` menyediakan fungsi routing [annotation](id/annotation.md) yang sangat
praktis. Anda dapat langsung mendefinisikan route dengan menentukan annotation
`#[Controller]` atau `#[AutoController]` pada kelas mana saja.

! > Kelas annotation yang muncul di bawah adalah kelas di bawah namespace
`use Hyperf\HttpServer\Annotation\`, seperti
`Hyperf\HttpServer\Annotation\AutoController`.

#### Parameter annotation

Baik `#[Controller]` maupun `#[AutoController]` menyediakan dua parameter,
yaitu `prefix` dan `server`.

`prefix` menunjukkan prefix untuk semua route method di bawah controller. Secara
default, bagian setelah `\Controller\` pada namespace kelas controller akan
digunakan sebagai prefix route dengan nomenklatur SnakeCase, misal
`\App\Controller\Demo\UserController` maka secara default prefix-nya adalah
`demo/user`.

Sebagai contoh, jika `App\Controller\Demo\UserController`, prefix-nya secara
default akan menjadi `demo/user`, dan jika path dari suatu method di dalam kelas
tersebut adalah `index`, route akhirnya akan menjadi `/demo/user/index`.

! > Perlu dicatat bahwa `prefix` tidak selalu berlaku. Ketika path dari sebuah
method di dalam kelas dimulai dengan `/`, path tersebut didefinisikan dari
bagian awal `URI`, yang berarti nilai prefix akan diabaikan.

`server` menunjukkan pada `HTTP Server` mana route tersebut didefinisikan. Karena
Hyperf mendukung beberapa `HTTP Server` secara bersamaan, parameter ini dapat
digunakan untuk membedakan route didefinisikan untuk `Server` yang mana, dengan
nilai default `http`.

|              Controller              |           Annotation            |      Route URI      |
|:------------------------------------:|:-------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @AutoController()        |   /my_data/index    |
|   App\Controller\MydataController    |        @AutoController()        |    /mydata/index    |
|   App\Controller\MyDataController    | @AutoController(prefix="/data") |     /data/index     |
| App\Controller\Demo\MyDataController |        @AutoController()        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") |     /data/index     |



|              Controller              |                                    Annotation                                     |      Route URI      |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        |   /my_data/index    |
| App\Controller\Demo\MyDataController |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") |     /data/index     |
|   App\Controller\MyDataController    |       @Controller() + @RequestMapping(path: "/index", methods: "get,post")        |       /index        |

#### Annotation AutoController

`#[AutoController]` menyediakan dukungan routing binding untuk sebagian besar
skenario akses sederhana. Saat menggunakan `#[AutoController]`, `Hyperf` akan
secara otomatis mem-parsing semua method `public` dari kelas tersebut dan
menyediakan method request `GET` dan `POST`.

> Saat menggunakan annotation `#[AutoController]`, diperlukan namespace
`use Hyperf\HttpServer\Annotation\AutoController;`.

Nama controller berformat Pascal case akan dikonversi ke snake_case secara
otomatis. Berikut adalah contoh korespondensi antara controller, annotation,
dan route yang dihasilkan:


```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf will automatically generate a /user/index route for this method, allowing requests via GET or POST
    public function index(RequestInterface $request)
    {
        // Obtain the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### Annotation Controller

`#[Controller]` ada untuk memenuhi persyaratan definisi routing yang lebih
mendetail. Penggunaan annotation `#[Controller]` digunakan untuk menunjukkan
bahwa kelas saat ini adalah kelas `controller`, dan annotation
`#[RequestMapping]` diperlukan untuk memperbarui definisi detail dari method
request dan URI.

Kami juga menyediakan berbagai annotation `mapping` yang cepat dan praktis,
seperti `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`,
`#[PatchMapping]`, dan `#[DeleteMapping]`, masing-masing sesuai dengan HTTP
method yang cocok.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class UserController
{
    // Hyperf will automatically generate a /user/index route for this method, allowing requests via GET or POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Obtain the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Route parameter

> Parameter route yang diberikan harus konsisten dengan nama key dan tipe
parameter controller, jika tidak, controller tidak dapat menerima parameter
tersebut.

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```
Akses parameter route melalui injection pada method controller.

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

Akses parameter route melalui objek request.

```php
public function index(RequestInterface $request)
{
    // Jika ada maka akan dikembalikan, jika tidak ada maka akan mengembalikan nilai default null
    $id = $request->route('id');
    // Jika ada maka akan dikembalikan, jika tidak ada maka akan mengembalikan nilai default 0
    $id = $request->route('id', 0);
}
```

#### Parameter wajib (Required parameters)

Kita dapat mendefinisikan parameter route wajib menggunakan `{}`. Misalnya,
`/user/{id}` menyatakan bahwa `id` adalah parameter wajib.

#### Parameter opsional (Optional parameters)

Terkadang Anda ingin parameter route bersifat opsional. Dalam hal ini, Anda dapat
menggunakan `[]` untuk menyatakan parameter di dalam tanda kurung siku sebagai
parameter opsional, seperti `/user/[{id}]`.

#### Validasi parameter

Anda juga dapat menggunakan regular expression untuk memvalidasi parameter.
Berikut adalah beberapa contoh:
```php
use Hyperf\HttpServer\Router\Router;

// Matches /user/42, but not /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// Matches /user/foobar, but not /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// Matches /user/foo/bar as well
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// This route
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// Is equivalent to these two routes
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// Multiple nested optional parts are possible as well
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// This route is NOT valid, because optional parts can only occur at the end
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### Mendapatkan informasi routing

Jika komponen devtool terinstal, Anda dapat menggunakan perintah
`php bin/hyperf.php describe:routes` untuk mendapatkan daftar informasi routing.
Anda juga dapat memberikan opsi path, yang memudahkan untuk mendapatkan
informasi tentang satu route saja, contoh:
`php bin/hyperf.php describe:routes --path=/foo/bar`.

## HTTP exception

Ketika route gagal dicocokkan, seperti `route not found (404)`, `request method
not allowed (405)`, dan exception HTTP lainnya, Hyperf secara seragam akan
melempar exception yang mewarisi kelas
`Hyperf\HttpMessage\Exception\HttpException`. Anda perlu mengelola exception ini
melalui mekanisme `ExceptionHandler` dan melakukan pemrosesan respons yang
sesuai. Secara default, Anda dapat langsung menggunakan
`Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` yang disediakan oleh
komponen untuk menangkap dan memproses exception. Perlu dicatat bahwa Anda harus
mengonfigurasi exception handler ini di file konfigurasi
`config/autoload/exceptions.php` dan memastikan urutan rantai antara beberapa
exception handler sudah benar.

Ketika Anda perlu menyesuaikan respons untuk HTTP exception seperti `route not
found (404)` dan `request method not allowed (405)`, Anda dapat langsung
mengimplementasikan penanganan exception Anda sendiri berdasarkan kode
`HttpExceptionHandler` dan mengonfigurasi exception handler Anda sendiri. Untuk
logika dan petunjuk penggunaan exception handler, silakan merujuk ke
[Exception Handling](id/exception-handler.md).
