# Routing

Secara default, routing ditangani oleh [nikic/fast-route](https://github.com/nikic/FastRoute) dan diintegrasikan ke `Hyperf` lewat komponen [hyperf/http-server](https://github.com/hyperf/http-server). RPC routing ditangani oleh komponen [hyperf/rpc-server](https://github.com/hyperf/rpc-server).

## HTTP Routing

### Mendefinisikan Route melalui File Konfigurasi

Di [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton), semua route didefinisikan di `config/routes.php` secara default. Kalo route-nya banyak, Anda bisa perluas file ini. Tapi `Hyperf` juga support `Annotation Routing`, yang kami rekomendasiin, terutama kalo route-nya udah banyak.

#### Mendefinisikan Route melalui Closure

Bikin route dasar cuma butuh URI dan `Closure`. Langsung aja liat kode:

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

Akses route ini lewat `http://host:port/hello-hyperf` di browser atau pake `cURL`.

#### Mendefinisikan Standard Routes

Standard route adalah route yang ditangani oleh `Controller` dan `Action`. Kalo pake pola `Request Handler`, caranya mirip. Liat kode berikut:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Salah satu dari tiga metode berikut akan mencapai efek yang sama
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

Definisi route ini mengikat path `/hello-hyperf` ke method `hello` di `App\Controller\IndexController`.

#### Metode Routing yang Tersedia

Router nyediain beberapa method buat daftarin route berbagai HTTP request:

```php
use Hyperf\HttpServer\Router\Router;

// Mendaftarkan route untuk metode HTTP yang sesuai dengan nama method
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// Mendaftarkan route untuk metode HTTP apapun
Router::addRoute($httpMethod, $uri, $callback);
```

Kadang Anda perlu daftarin route yang bisa ngerespon beberapa metode HTTP sekaligus. Bisa pake method `addRoute`:

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST','PUT','DELETE'], $uri, $callback);
```

#### Mendefinisikan Route Groups

Route aktualnya adalah `group/route`, yaitu `/user/index`, `/user/store`, `/user/update`, `/user/delete`

```php
Router::addGroup('/user/',function (){
    Router::get('index','App\Controller\UserController@index');
    Router::post('store','App\Controller\UserController@store');
    Router::get('update','App\Controller\UserController@update');
    Router::post('delete','App\Controller\UserController@delete');
});
```

### Mendefinisikan Route melalui Annotation

`Hyperf` nyediain fitur routing [Annotation](id/annotation.md) yang praktis. Anda bisa langsung define route di class mana pun pake annotation `#[Controller]` atau `#[AutoController]`.

!> Class annotation yang muncul di bawah ini termasuk dalam namespace `use Hyperf\HttpServer\Annotation\`, seperti `Hyperf\HttpServer\Annotation\AutoController`

#### Parameter Annotation

Baik `#[Controller]` maupun `#[AutoController]` menyediakan parameter `prefix` dan `server`.

`prefix` merepresentasikan prefix route untuk semua method di dalam controller. Secara default, bagian setelah `\Controller\` di namespace controller class digunakan sebagai prefix route dalam format snake_case.

Misalnya, untuk `App\Controller\Demo\UserController`, prefix defaultnya adalah `demo/user`. Jika path suatu method di dalam class adalah `index`, maka route akhirnya adalah `/demo/user/index`.

!> Perhatikan bahwa `prefix` tidak selalu berlaku. Ketika path suatu method di dalam class diawali dengan `/`, itu menandakan bahwa path tersebut didefinisikan dari awal `URI`, yang berarti nilai `prefix` akan diabaikan.

`server` menunjukkan `HTTP Server` mana tempat route tersebut didefinisikan. Karena Hyperf mendukung menjalankan multiple `HTTP Servers` secara bersamaan, parameter ini bisa digunakan untuk membedakan `Server` mana yang menjadi target definisi route. Nilai defaultnya adalah `http`.

| Controller | Annotation | Access Route |
|:------------------------------------:|:-------------------------------:|:------------------:|
| App\Controller\MyDataController | @AutoController() | /my_data/index |
| App\Controller\MydataController | @AutoController() | /mydata/index |
| App\Controller\MyDataController | @AutoController(prefix="/data") | /data/index |
| App\Controller\Demo\MydataController | @AutoController() | /demo/mydata/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") | /data/index |

| Controller | Annotation | Access Route |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
| App\Controller\MyDataController | @Controller() + @RequestMapping(path: "index", methods: "get,post") | /my_data/index |
| App\Controller\Demo\MyDataController | @Controller() + @RequestMapping(path: "index", methods: "get,post") | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") | /data/index |
| App\Controller\MyDataController | @Controller() + @RequestMapping(path: "/index", methods: "get,post") | /index |

#### Annotation `#[AutoController]`

`#[AutoController]` nyediain binding route buat skenario akses sederhana. Pas pake `#[AutoController]`, `Hyperf` otomatis parse semua method `public` di class dan nyediain metode request `GET` dan `POST`.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf akan secara otomatis membuat route /user/index untuk method ini,
    // mengizinkan request melalui GET atau POST
    public function index(RequestInterface $request)
    {
        // Mendapatkan parameter id dari request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### Annotation `#[Controller]`

`#[Controller]` ada buat kebutuhan routing yang lebih detail. Annotation `#[Controller]` nandain bahwa class ini adalah `Controller` class, dan harus dipake bareng `#[RequestMapping]` buat ngatur metode dan path request secara lebih detail.
Kami juga nyediain berbagai `Mapping` annotations yang praktis, kayak `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`, dan `#[DeleteMapping]`, 5 annotation buat nandain metode request yang berbeda.

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
    // Hyperf akan secara otomatis membuat route /user/index untuk method ini,
    // mengizinkan request melalui GET atau POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Mendapatkan parameter id dari request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Route Parameters

> Route parameters yang didefinisikan harus konsisten sama key name dan tipe parameter controller; kalo gak, controller gak bisa nerima parameter yang dimaksud.

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

Mendapatkan melalui method `route`:

```php
public function index(RequestInterface $request)
{
    // Mengembalikan jika ada, jika tidak mengembalikan nilai default null
    $id = $request->route('id');
    // Mengembalikan jika ada, jika tidak mengembalikan nilai default 0
    $id = $request->route('id', 0);
}
```

#### Required Parameters

Kita bisa define parameter buat `$uri` pake `{}`, misal `/user/{id}`, itu nandain `id` sebagai required parameter.

#### Optional Parameters

Kadang Anda pengen parameter ini opsional. Bisa pake `[]` buat optional parameter, kayak `/user/[{id}]`.

#### Validating Parameters

Anda juga bisa menggunakan regular expression untuk memvalidasi parameter. Berikut adalah beberapa contoh:

```php
use Hyperf\HttpServer\Router\Router;

// Cocok dengan /user/42, tetapi tidak bisa mencocokkan /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// Cocok dengan /user/foobar, tetapi tidak bisa mencocokkan /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// Juga bisa mencocokkan /user/foo/bar
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// Route ini
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// Setara dengan dua route berikut
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// Multiple nested brackets opsional juga diizinkan
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// Ini adalah route yang tidak valid karena bagian opsional hanya bisa muncul di akhir
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### Mendapatkan Informasi Route

Kalo komponen devtool terinstal, Anda bisa pake perintah `php bin/hyperf.php describe:routes` buat liat daftar route.
Ada juga opsi `path` buat liat route tertentu: `php bin/hyperf.php describe:routes --path=/foo/bar`.

## HTTP Exceptions

Kalo gak ada route yang cocok, misalnya `Route not found (404)` atau `Method not allowed (405)`, Hyperf bakal lempar subclass dari `Hyperf\HttpMessage\Exception\HttpException`. Exception ini perlu ditangani lewat mekanisme ExceptionHandler. Secara default, Anda bisa pake `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` dari komponen buat nangkep dan nanganin exception. Tapi exception handler ini perlu dikonfigurasi sendiri di `config/autoload/exceptions.php`, dan pastiin urutan multiple exception handlers udah bener.
Kalo pengen ngubah response buat situasi HTTP exception kayak `Route not found (404)` atau `Method not allowed (405)`, Anda bisa implementasiin exception handler sendiri, tinggal turunin dari `HttpExceptionHandler`. Soal logika dan cara pake exception handlers, liat [Exception Handler](id/exception-handler.md)
