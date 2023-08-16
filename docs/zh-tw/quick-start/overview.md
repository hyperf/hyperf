# 快速入門

為了讓您更快的瞭解 `Hyperf` 的使用，本章節將以 `建立一個 HTTP Server` 為例，透過對路由、控制器的定義實現一個簡單的 `Web` 服務，但 `Hyperf` 不止於此，完善的服務治理、`gRPC` 服務、註解、`AOP` 等功能將由具體的章節闡述。

## 定義訪問路由

Hyperf 使用 [nikic/fast-route](https://github.com/nikic/FastRoute) 作為預設的路由元件並提供服務，您可以很方便的在 `config/routes.php` 中定義您的路由。   
不僅如此，框架還提供了極其強大和方便靈活的 `註解路由` 功能，關於路由的詳情文件請查閱 [路由](zh-tw/router.md) 章節

### 透過配置檔案定義路由

路由的檔案位於 [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) 專案的 `config/routes.php` ，下面是一些常用的用法示例。

```php
<?php
use Hyperf\HttpServer\Router\Router;

// 此處程式碼示例為每個示例都提供了三種不同的繫結定義方式，實際配置時僅可採用一種且僅定義一次相同的路由

// 設定一個 GET 請求的路由，繫結訪問地址 '/get' 到 App\Controller\IndexController 的 get 方法
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// 設定一個 POST 請求的路由，繫結訪問地址 '/post' 到 App\Controller\IndexController 的 post 方法
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// 設定一個允許 GET、POST 和 HEAD 請求的路由，繫結訪問地址 '/multi' 到 App\Controller\IndexController 的 multi 方法
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### 透過註解來定義路由

`Hyperf` 提供了極其強大和方便靈活的 [註解](zh-tw/annotation.md) 功能，在路由的定義上也毫無疑問地提供了註解定義的方式，Hyperf 提供了 `#[Controller]` 和 `#[AutoController]` 兩種註解來定義一個 `Controller`，此處僅做簡單的說明，更多細節請查閱 [路由](zh-tw/router.md) 章節。

### 透過 `#[AutoController]` 註解定義路由

`#[AutoController]` 為絕大多數簡單的訪問場景提供路由繫結支援，使用 `#[AutoController]` 時則 Hyperf 會自動解析所在類的所有 `public` 方法並提供 `GET` 和 `POST` 兩種請求方式。

> 使用 `#[AutoController]` 註解時需 `use Hyperf\HttpServer\Annotation\AutoController;` 名稱空間；

駝峰命名的控制器，會自動轉化為蛇形路由，以下為控制器與實際路由的對應關係示例：

|      控制器      |              註解               |    訪問路由    |
| :--------------: | :-----------------------------: | :------------: |
| MyDataController |        @AutoController()        | /my_data/index |
| MydataController |        @AutoController()        | /mydata/index  |
| MyDataController | @AutoController(prefix="/data") |  /data/index   |

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf 會自動為此方法生成一個 /index/index 的路由，允許透過 GET 或 POST 方式請求
    public function index(RequestInterface $request)
    {
        // 從請求中獲得 id 引數
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### 透過 `#[Controller]` 註解定義路由
`#[Controller]` 為滿足更細緻的路由定義需求而存在，使用 `#[Controller]` 註解用於表明當前類為一個 `Controller 類`，同時需配合 `#[RequestMapping]` 註解來對請求方法和請求路徑進行更詳細的定義。   
我們也提供了多種快速便捷的 `Mapping 註解`，如 `#[GetMapping]`、`#[PostMapping]`、`#[PutMapping]`、`#[PatchMapping]`、`#[DeleteMapping]` 5 種便捷的註解用於表明允許不同的請求方法。

> 使用 `#[Controller]` 註解時需 `use Hyperf\HttpServer\Annotation\Controller;` 名稱空間；   
> 使用 `#[RequestMapping]` 註解時需 `use Hyperf\HttpServer\Annotation\RequestMapping;` 名稱空間；   
> 使用 `#[GetMapping]` 註解時需 `use Hyperf\HttpServer\Annotation\GetMapping;` 名稱空間；   
> 使用 `#[PostMapping]` 註解時需 `use Hyperf\HttpServer\Annotation\PostMapping;` 名稱空間；   
> 使用 `#[PutMapping]` 註解時需 `use Hyperf\HttpServer\Annotation\PutMapping;` 名稱空間；   
> 使用 `#[PatchMapping]` 註解時需 `use Hyperf\HttpServer\Annotation\PatchMapping;` 名稱空間；   
> 使用 `#[DeleteMapping]` 註解時需 `use Hyperf\HttpServer\Annotation\DeleteMapping;` 名稱空間；  

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
    // Hyperf 會自動為此方法生成一個 /index/index 的路由，允許透過 GET 或 POST 方式請求
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // 從請求中獲得 id 引數
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

## 處理 HTTP 請求

`Hyperf` 是完全開放的，本質上沒有規定您必須基於某種模式下去實現請求的處理，您可以採用傳統的 `MVC 模式`，亦可以採用 `RequestHandler 模式` 來進行開發。   
我們以 `MVC 模式` 來舉個例子：   
在 `app` 資料夾內建立一個 `Controller` 資料夾並建立 `IndexController.php` 如下，`index` 方法內從請求中獲取了 `id` 引數，並轉換為 `字串` 型別返回到客戶端。

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf 會自動為此方法生成一個 /index/index 的路由，允許透過 GET 或 POST 方式請求
    public function index(RequestInterface $request)
    {
        // 從請求中獲得 id 引數
        $id = $request->input('id', 1);
        // 轉換 $id 為字串格式並以 plain/text 的 Content-Type 返回 $id 的值給客戶端
        return (string)$id;
    }
}
```

## 依賴自動注入

依賴自動注入是 `Hyperf` 提供的一個非常強大的功能，也是保持框架靈活性的根基。   
`Hyperf` 提供了兩種注入方式，一種是大家常見的透過建構函式注入，另一種是透過 `#[Inject]` 註解注入，下面我們舉個例子並分別以兩種方式展示注入的實現；   
假設我們存在一個 `\App\Service\UserService` 類，類中存在一個 `getInfoById(int $id)` 方法透過傳遞一個 `id` 並最終返回一個使用者實體，由於返回值並不是我們這裡所需要關注的，所以不做過多闡述，我們要關注的是在任意的類中獲取 `UserService` 並呼叫裡面的方法，一般的方法是透過 `new UserService()` 來例項化該服務類，但在 `Hyperf` 下，我們有更優的解決方法。

### 透過建構函式注入
只需在建構函式的引數內宣告引數的型別，`Hyperf` 會自動注入對應的物件或值。
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
    
    // 在建構函式宣告引數的型別，Hyperf 會自動注入對應的物件或值
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

### 透過 `#[Inject]` 註解注入

只需對對應的類屬性透過 `@var` 宣告引數的型別，並使用 `#[Inject]` 註解標記屬性 ，`Hyperf` 會自動注入對應的物件或值。

> 使用 `#[Inject]` 註解時需 `use Hyperf\Di\Annotation\Inject;` 名稱空間；

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
   
透過上面的示例我們不難發現 `$userService` 在沒有例項化的情況下， 屬性對應的類物件被自動注入了。   
不過這裡的案例並未真正體現出依賴自動注入的好處及其強大之處，我們假設一下 `UserService` 也存在很多的依賴，而這些依賴同時又存在很多其它的依賴時，`new` 例項化的方式就需要手動例項化很多的物件並調整好對應的引數位，而在 `Hyperf` 裡我們就無須手動管理這些依賴，只需要宣告一下最終使用的類即可。   
而當 `UserService` 需要發生替換等劇烈的內部變化時，比如從一個本地服務替換成了一個 RPC 遠端服務，也只需要透過配置調整依賴中 `UserService` 這個鍵值對應的類為新的 RPC 服務類即可。

## 啟動 Hyperf 服務

由於 `Hyperf` 內建了協程伺服器，也就意味著 `Hyperf` 將以 `CLI` 的形式去執行，所以在定義好路由及實際的邏輯程式碼之後，我們需要在專案根目錄並透過命令列執行 `php bin/hyperf.php start` 來啟動服務。   
當 `Console` 介面顯示服務啟動後便可透過 `cURL` 或 瀏覽器對服務正常發起訪問了，預設服務會提供一個首頁 `http://127.0.0.1:9501/`，對於本章示例引導的情況下，也就是上面的例子所對應的訪問地址為 `http://127.0.0.1:9501/index/info?id=1`。

## 重新載入程式碼

由於 `Hyperf` 是持久化的 `CLI` 應用，也就意味著一旦程序啟動，已解析的 `PHP` 程式碼會持久化在程序中，也就意味著啟動服務後您再修改的 `PHP` 程式碼不會改變已啟動的服務，如您希望服務重新載入您修改後的程式碼，您需要透過在啟動的 `Console` 中鍵入 `CTRL + C` 終止服務，再重新執行啟動命令 `php bin/hyperf.php start` 完成啟動和重新載入。

> Tips: 您也可以將啟動 Server 的命令配置在 IDE 上，便可直接透過 IDE 的 `啟動/停止` 操作快捷的完成 `啟動服務` 或 `重啟服務` 的操作。
> 且非檢視開發時可以採用 [TDD(Test-Driven Development)](https://baike.baidu.com/item/TDD/9064369) 測試驅動開發來進行開發，這樣不僅可以省略掉服務重啟和頻繁切換視窗的麻煩，還可保證介面資料的正確性。

> 另外，在文件 [協程元件庫](zh-tw/awesome-components?id=%e7%83%ad%e6%9b%b4%e6%96%b0%e7%83%ad%e9%87%8d%e8%bd%bd) 一章中提供了多種由社群開發者支援的 熱更新/熱過載 的解決方案，如仍希望採用 熱更新/熱過載 方案可再深入瞭解。

## 多埠監聽

`Hyperf` 支援監聽多個埠，但因為 `callbacks` 中的物件直接從容器中獲取，所以相同的 `Hyperf\HttpServer\Server::class` 會在容器中被覆蓋。所以我們需要在依賴關係中，重新定義 `Server`，確保物件隔離。

> WebSocket 和 TCP 等 Server 同理。

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

同時 `路由檔案`，或者 `註解` 也需要指定對應的 `server`，如下：

- 路由檔案 `config/routes.php`

```php
<?php
Router::addServer('innerHttp', function () {
    Router::get('/', 'App\Controller\IndexController@index');
});
```

- 註解

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


## 事件

除上述提到的 `Event::ON_REQUEST` 事件，框架還支援其他事件，所有事件名如下。

|         事件名          |               備註                |
| :---------------------: | :-------------------------------: |
|    Event::ON_REQUEST    |                                   |
|     Event::ON_START     | 該事件在 `SWOOLE_BASE` 模式下無效 |
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
