# 路由

默認情況下路由由 [nikic/fast-route](https://github.com/nikic/FastRoute) 提供支持，並由 [hyperf/http-server](https://github.com/hyperf/http-server) 組件負責接入到 `Hyperf` 中，`RPC` 路由由對應的 [hyperf/rpc-server](https://github.com/hyperf/rpc-server) 組件負責。

## HTTP 路由

### 通過配置文件定義路由

在 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 骨架下，默認在 `config/routes.php` 文件內完成所有的路由定義，當然如果您路由眾多，您也可以對該文件進行擴展，以適應您的需求，但 `Hyperf` 還支持 `註解路由`，我們更推薦使用 `註解路由`，特別是在路由眾多的情況下。   

#### 通過閉包定義路由

構建一個最基本的路由只需一個 URI 和一個 `閉包(Closure)`，我們直接通過代碼來演示一下：

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

您可以通過 瀏覽器 或  `cURL` 命令行來請求 `http://host:port/hello-hyperf` 來訪問該路由。

#### 定義標準路由

所謂標準路由指的是由 `控制器(Controller)` 和 `操作(Action)` 來處理的路由，如果您使用 `請求處理器(Request Handler)` 模式也是類似的，我們通過代碼來演示一下：

```php
<?php
use Hyperf\HttpServer\Router\Router;

// 下面三種方式的任意一種都可以達到同樣的效果
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

該路由定義為將 `/hello-hyperf` 路徑綁定到 `App\Controller\IndexController` 下的 `hello` 方法。

#### 可用的路由方法

路由器提供了多種方法幫助您註冊任何的 HTTP 請求的路由：

```php
use Hyperf\HttpServer\Router\Router;

// 註冊與方法名一致的 HTTP METHOD 的路由
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// 註冊任意 HTTP METHOD 的路由
Router::addRoute($httpMethod, $uri, $callback);
```

有時候您可能需要註冊一個可以同時響應多種 HTTP METHOD 請求的路由，可以通過 `addRoute` 方法實現定義：

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST','PUT','DELETE'], $uri, $callback);
```

#### 路由組的定義方式

實際路由為 `group/route`, 即 `/user/index`, `/user/store`, `/user/update`, `/user/delete` 

```php
Router::addGroup('/user/',function (){
    Router::get('index','App\Controller\UserController@index');
    Router::post('store','App\Controller\UserController@store');
    Router::get('update','App\Controller\UserController@update');
    Router::post('delete','App\Controller\UserController@delete');
});

```

### 通過註解定義路由

`Hyperf` 提供了非常便利的 [註解](zh-hk/annotation.md) 路由功能，您可以直接在任意類上通過定義 `#[Controller]` 或 `#[AutoController]` 註解來完成一個路由的定義。

!> 以下出現的註解類都為 `use Hyperf\HttpServer\Annotation\` 命名空間下的類，如 `Hyperf\HttpServer\Annotation\AutoController`

#### 註解參數

`#[Controller]` 和 `#[AutoController]` 都提供了 `prefix` 和 `server` 兩個參數。

`prefix` 表示該控制器下的所有方法路由的前綴，默認為控制器類命名空間中 `\Controller\` 之後的部分會以蛇形命名法(SnakeCase)被用作路由的前綴，

如 `App\Controller\Demo\UserController` 則 prefix 默認為 `demo/user`，如類內某一方法的 path 為 `index`，則最終路由為 `/demo/user/index`。

!> 需要注意的是 `prefix` 並非一直有效，當類內的方法的 path 以 `/` 開頭時，則表明路徑從 `URI` 頭部開始定義，也就意味着會忽略 prefix 的值

`server` 表示該路由是定義在哪個 `HTTP Server` 之上的，由於 Hyperf 支持同時啓動多個 `HTTP Server`，則在定義路由是可以通過該參數來進行區分這個路由是為了哪個 `Server` 定義的，默認為 `http`。

|                 控制器                  |               註解                |        訪問路由        |
|:------------------------------------:|:-------------------------------:|:------------------:|
|   App\Controller\MyDataController    |        @AutoController()        |   /my_data/index   |
|   App\Controller\MydataController    |        @AutoController()        |   /mydata/index    |
|   App\Controller\MyDataController    | @AutoController(prefix="/data") |    /data/index     |
| App\Controller\Demo\MydataController |        @AutoController()        | /demo/mydata/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") |    /data/index     |



|                 控制器                  |                                        註解                                         |        訪問路由         |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        |   /my_data/index    |
| App\Controller\Demo\MyDataController |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") |     /data/index     |
|   App\Controller\MyDataController    |       @Controller() + @RequestMapping(path: "/index", methods: "get,post")        |       /index        |

#### `#[AutoController]` 註解

`#[AutoController]` 為絕大多數簡單的訪問場景提供路由綁定支持，使用 `#[AutoController]` 時則 `Hyperf` 會自動解析所在類的所有 `public` 方法並提供 `GET` 和 `POST` 兩種請求方式。

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf 會自動為此方法生成一個 /user/index 的路由，允許通過 GET 或 POST 方式請求
    public function index(RequestInterface $request)
    {
        // 從請求中獲得 id 參數
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### `#[Controller]` 註解

`#[Controller]` 為滿足更細緻的路由定義需求而存在，使用 `#[Controller]` 註解用於表明當前類為一個 `Controller` 類，同時需配合 `#[RequestMapping]` 註解來對請求方法和請求路徑進行更詳細的定義。   
我們也提供了多種快速便捷的 `Mapping` 註解，如 `#[GetMapping]`、`#[PostMapping]`、`#[PutMapping]`、`#[PatchMapping]`、`#[DeleteMapping]` 5 種便捷的註解用於表明允許不同的請求方法。

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
    // Hyperf 會自動為此方法生成一個 /user/index 的路由，允許通過 GET 或 POST 方式請求
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // 從請求中獲得 id 參數
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### 路由參數

> 本框架定義的路由參數必須和控制器參數鍵名、類型保持一致，否則控制器無法接收到相關參數

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

通過 `route` 方法獲取

```php
public function index(RequestInterface $request)
{
        // 存在則返回，不存在則返回默認值 null
        $id = $request->route('id');
        // 存在則返回，不存在則返回默認值 0
        $id = $request->route('id', 0);
}
```

#### 必填參數

我們可以對 `$uri` 進行一些參數定義，通過 `{}` 來聲明參數，如 `/user/{id}` 則聲明瞭 `id` 值為一個必填參數。

#### 可選參數

有時候您可能會希望這個參數是可選的，您可以通過 `[]` 來聲明中括號內的參數為一個可選參數，如 `/user/[{id}]`。

#### 校驗參數

您也可以使用正則表達式對參數進行校驗，以下是一些例子
```php
use Hyperf\HttpServer\Router\Router;

// 可以匹配 /user/42, 但不能匹配 /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// 可以匹配 /user/foobar, 但不能匹配 /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// 也可以匹配 /user/foo/bar as well
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// 這個路由
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// 等同於以下的兩個路由
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// 多個可選的嵌套也是允許的
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// 這是一條無效的路由, 因為可選部分只能出現在最後
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### 獲取路由信息

如果安裝了 devtool 組件，可使用 `php bin/hyperf.php describe:routes` 命令獲取路由列表信息，
並且提供 path 可選項，方便獲取單個路由信息，對應的命令 `php bin/hyperf.php describe:routes --path=/foo/bar`。

## HTTP 異常

在路由匹配不到路由時，如 `路由找不到(404)`、`請求方法不允許(405)` 等 HTTP 異常，Hyperf 會統一拋出一個 `Hyperf\HttpMessage\Exception\HttpException` 異常類的子類，您需要通過 ExceptionHandler 機制來管理這些異常並做對應的響應處理，默認情況下可以直接使用組件提供的 `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` 來進行異常捕獲處理，注意這個異常處理器需要您自行配置到 `config/autoload/exceptions.php` 配置文件中去，並保障多個異常處理器之間的順序鏈路是正確的。   
當您需要對 `路由找不到(404)`、`請求方法不允許(405)` 等 HTTP 異常情況的響應進行自定義處理時，您可直接根據 `HttpExceptionHandler` 的代碼實現您自己的異常處理器，並配置您自己的異常處理器。關於異常處理器的邏輯和使用説明，可具體查閲 [異常處理](zh-hk/exception-handler.md)
