# 中介軟體

這裡的中介軟體指的是 "中介軟體模式"，該功能屬於 [hyperf/http-server](https://github.com/hyperf/http-server) 元件內的一項主要功能，主要用於編織從 `請求(Request)` 到 `響應(Response)` 的整個流程，該功能完全基於 [PSR-15](https://www.php-fig.org/psr/psr-15/) 實現。

## 原理

*中介軟體主要用於編織從 `請求(Request)` 到 `響應(Response)` 的整個流程*，透過對多箇中間件的組織，使資料的流動按我們預定的方式進行，中介軟體的本質是一個 `洋蔥模型`，我們透過一個圖來解釋它：

![middleware](middleware.jpg)

圖中的順序為按照 `Middleware 1 -> Middleware 2 -> Middleware 3` 的順序組織著，我們可以注意到當中間的橫線穿過 `核心` 即 `Middleware 3` 後，又回到了 `Middleware 2`，為一個巢狀模型，那麼實際的順序其實就是：   
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`   
重點放在 `核心` 即 `Middleware 3`，它是洋蔥的分界點，分界點前面的部分其實都是基於 `請求(Request)` 進行處理，而經過了分界點時，`核心` 就產出了 `響應(Response)` 物件，也是 `核心` 的主要程式碼目標，在之後便是對 `響應(Response)` 進行處理了，`核心` 通常是由框架負責實現的，而其它的就由您來編排了。

## 定義全域性中介軟體

全域性中介軟體只可透過配置檔案的方式來配置，配置檔案位於 `config/autoload/middlewares.php` ，配置如下：   
```php
<?php
return [
    // http 對應 config/autoload/server.php 內每個 server 的 name 屬性對應的值，該配置僅應用在該 Server 中
    'http' => [
        // 陣列內配置您的全域性中介軟體，順序根據該陣列的順序
        YourMiddleware::class
    ],
];
```
只需將您的全域性中介軟體配置在該檔案及對應的 `Server Name` 內，即該 `Server` 下的所有請求都會應用配置的全域性中介軟體。

## 定義區域性中介軟體

當我們有些中介軟體僅僅面向某些請求或控制器時，即可將其定義為區域性中介軟體，可透過配置檔案的方式定義或註解的方式。

### 透過配置檔案定義

在使用配置檔案定義路由時，您僅可透過配置檔案來定義對應的中介軟體，區域性中介軟體的配置將在路由配置上完成。   
`Hyperf\HttpServer\Router\Router` 類的每個定義路由的方法的最後一個引數 `$options` 都將接收一個數組，可透過傳遞鍵值 `middleware` 及一個數組值來定義該路由的中介軟體，我們通過幾個路由定義來演示一下:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// 每個路由定義方法都可接收一個 $options 引數
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);

// 該 Group 下的所有路由都將應用配置的中介軟體
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [FooMiddleware::class]]
);

```

### 透過註解定義

在透過註解定義路由時，您僅可透過註解的方式來定義中介軟體，對中介軟體的定義有兩個註解，分別為：   
  - `#[Middleware]` 註解為定義單箇中間件時使用，在一個地方僅可定義一個該註解，不可重複定義
  - `#[Middlewares]` 註解為定義多箇中間件時使用，在一個地方僅可定義一個該註解，然後透過在該註解內定義多個 `#[Middleware]` 註解實現多箇中間件的定義

> 使用 `#[Middleware]` 註解時需 `use Hyperf\HttpServer\Annotation\Middleware;` 名稱空間；   
> 使用 `#[Middlewares]` 註解時需 `use Hyperf\HttpServer\Annotation\Middlewares;` 名稱空間；

定義單箇中間件：

```php
<?php
namespace App\Controller;

use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;

#[AutoController]
#[Middleware(FooMiddleware::class)]
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

透過 `#[Middlewares]` 註解定義多箇中間件：

```php
<?php
namespace App\Controller;

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middlewares([FooMiddleware::class, BarMiddleware::class])]
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

透過 `#[Middleware]` 註解定義多箇中間件：

```php
<?php
namespace App\Controller;

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(BarMiddleware::class)]
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

#### 定義方法級別的中介軟體

在透過配置檔案的方式配置中介軟體時定義到方法級別上很簡單，那麼要透過註解的形式定義到方法級別呢？您只需將註解直接定義到方法上即可。   
類級別上的中介軟體會優先於方法級別的中介軟體，我們透過程式碼來舉例一下：   

```php
<?php
namespace App\Controller;

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

#[AutoController]
#[Middlewares([FooMiddleware::class])]
class IndexController
{
    #[Middleware(BarMiddleware::class)]
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

#### 中介軟體相關的程式碼

生成中介軟體

```
php ./bin/hyperf.php gen:middleware Auth/FooMiddleware
```

```php
<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FooMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 根據具體業務判斷邏輯走向，這裡假設使用者攜帶的token有效
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => '中介軟體驗證token無效，阻止繼續向下執行',
                ],
            ]
        );
    }
}
```
中介軟體的執行順序為 `FooMiddleware -> BarMiddleware`。

## 中介軟體的執行順序

我們從上面可以瞭解到總共有 `3` 種級別的中介軟體，分別為 `全域性中介軟體`、`類級別中介軟體`、`方法級別中介軟體`，如果都定義了這些中介軟體，執行順序為：`全域性中介軟體 -> 類級別中介軟體 -> 方法級別中介軟體`。

## 全域性更改請求和響應物件

首先，在協程上下文內是有儲存最原始的 PSR-7 `請求物件` 和 `響應物件` 的，且根據 PSR-7 對相關物件所要求的 `不可變性(immutable)`，也就意味著我們在呼叫 `$response = $response->with***()` 所呼叫得到的 `$response`，並非為改寫原物件，而是一個 `Clone` 出來的新物件，也就意味著我們儲存在協程上下文內的 `請求物件` 和 `響應物件` 是不會改變的，那麼當我們在中介軟體內的某些邏輯改變了 `請求物件` 或 `響應物件`，而且我們希望對後續的 *非傳遞性的* 程式碼再獲取改變後的 `請求物件` 或 `響應物件`，那麼我們便可以在改變物件後，將新的物件設定到上下文中，如程式碼所示：

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request 和 $response 為修改後的物件
$request = \Hyperf\Context\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Context\Context::set(ResponseInterface::class, $response);
```

## 自定義 CoreMiddleWare 的行為

預設情況下，Hyperf 在處理路由找不到或 HTTP 方法不允許時，即 HTTP 狀態碼為 `404`、`405` 的時候，是由 `CoreMiddleware` 直接處理並返回對應的響應物件的，得益於 Hyperf 依賴注入的設計，您可以透過替換物件的方式來把 `CoreMiddleware` 指向由您自己實現的 `CoreMiddleware` 去。

比如我們希望定義一個 `App\Middleware\CoreMiddleware` 類來重寫預設的行為，我們可以先定義一個 `App\Middleware\CoreMiddleware` 類如下，這裡我們僅以 HTTP Server 為例，其它 Server 也可採用同樣的做法來達到同樣的目的。

```php
<?php
declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Contract\Arrayable;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware
{
    /**
     * Handle the response when cannot found any routes.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleNotFound(ServerRequestInterface $request)
    {
        // 重寫路由找不到的處理邏輯
        return $this->response()->withStatus(404);
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        // 重寫 HTTP 方法不允許的處理邏輯
        return $this->response()->withStatus(405);
    }
}
```

然後再在 `config/autoload/dependencies.php` 定義物件關係重寫 CoreMiddleware 物件：

```php
<?php
return [
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
```

> 這裡直接重寫 CoreMiddleware 的做法需要在 1.1.0+ 版本上才有效，1.0.x 版本仍需要你再將 CoreMiddleware 的上層呼叫透過 DI 進行重寫，然後替換 CoreMiddleware 的傳值為您定義的中介軟體類。

## 常用中介軟體

### 跨域中介軟體

如果您需要在框架中解決跨域，則可以按照您的需求實現以下中介軟體

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = Context::get(ResponseInterface::class);
        $response = $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            // Headers 可以根據實際情況進行改寫。
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}

```

實際上，跨域配置也可以直接掛在 `Nginx` 上。

```
location / {
    add_header Access-Control-Allow-Origin *;
    add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
    add_header Access-Control-Allow-Headers 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization';

    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
```
