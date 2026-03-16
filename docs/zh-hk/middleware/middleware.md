# 中間件

這裏的中間件指的是 "中間件模式"，該功能屬於 [hyperf/http-server](https://github.com/hyperf/http-server) 組件內的一項主要功能，主要用於編織從 `請求(Request)` 到 `響應(Response)` 的整個流程，該功能完全基於 [PSR-15](https://www.php-fig.org/psr/psr-15/) 實現。

## 原理

*中間件主要用於編織從 `請求(Request)` 到 `響應(Response)` 的整個流程*，通過對多箇中間件的組織，使數據的流動按我們預定的方式進行，中間件的本質是一個 `洋葱模型`，我們通過一個圖來解釋它：

![middleware](middleware.jpg)

圖中的順序為按照 `Middleware 1 -> Middleware 2 -> Middleware 3` 的順序組織着，我們可以注意到當中間的橫線穿過 `內核` 即 `Middleware 3` 後，又回到了 `Middleware 2`，為一個嵌套模型，那麼實際的順序其實就是：   
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`   
重點放在 `核心` 即 `Middleware 3`，它是洋葱的分界點，分界點前面的部分其實都是基於 `請求(Request)` 進行處理，而經過了分界點時，`內核` 就產出了 `響應(Response)` 對象，也是 `內核` 的主要代碼目標，在之後便是對 `響應(Response)` 進行處理了，`內核` 通常是由框架負責實現的，而其它的就由您來編排了。

## 定義全局中間件

全局中間件只可通過配置文件的方式來配置，配置文件位於 `config/autoload/middlewares.php` ，配置如下：   
```php
<?php
return [
    // http 對應 config/autoload/server.php 內每個 server 的 name 屬性對應的值，該配置僅應用在該 Server 中
    'http' => [
        // 數組內配置您的全局中間件，順序根據該數組的順序
        YourMiddleware::class
    ],
];
```
只需將您的全局中間件配置在該文件及對應的 `Server Name` 內，即該 `Server` 下的所有請求都會應用配置的全局中間件。

## 定義局部中間件

當我們有些中間件僅僅面向某些請求或控制器時，即可將其定義為局部中間件，可通過配置文件的方式定義或註解的方式。

### 通過配置文件定義

在使用配置文件定義路由時，您僅可通過配置文件來定義對應的中間件，局部中間件的配置將在路由配置上完成。   
`Hyperf\HttpServer\Router\Router` 類的每個定義路由的方法的最後一個參數 `$options` 都將接收一個數組，可通過傳遞鍵值 `middleware` 及一個數組值來定義該路由的中間件，我們通過幾個路由定義來演示一下:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// 每個路由定義方法都可接收一個 $options 參數
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);

// 該 Group 下的所有路由都將應用配置的中間件
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [FooMiddleware::class]]
);

```

### 通過註解定義

在通過註解定義路由時，您僅可通過註解的方式來定義中間件，對中間件的定義有兩個註解，分別為：   
  - `#[Middleware]` 註解為定義單箇中間件時使用，在一個地方僅可定義一個該註解，不可重複定義
  - `#[Middlewares]` 註解為定義多箇中間件時使用，在一個地方僅可定義一個該註解，然後通過在該註解內定義多個 `#[Middleware]` 註解實現多箇中間件的定義

> 使用 `#[Middleware]` 註解時需 `use Hyperf\HttpServer\Annotation\Middleware;` 命名空間；   
> 使用 `#[Middlewares]` 註解時需 `use Hyperf\HttpServer\Annotation\Middlewares;` 命名空間；

***注意：必須配合 `#[AutoController]` 或者 `#[Controller]` 使用***

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

通過 `#[Middlewares]` 註解定義多箇中間件：

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

通過 `#[Middleware]` 註解定義多箇中間件：

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

#### 定義方法級別的中間件

在通過配置文件的方式配置中間件時定義到方法級別上很簡單，那麼要通過註解的形式定義到方法級別呢？您只需將註解直接定義到方法上即可。   
類級別上的中間件會優先於方法級別的中間件，我們通過代碼來舉例一下：   

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

#### 中間件相關的代碼

生成中間件

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
        // 根據具體業務判斷邏輯走向，這裏假設用户攜帶的token有效
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => '中間件驗證token無效，阻止繼續向下執行',
                ],
            ]
        );
    }
}
```
中間件的執行順序為 `FooMiddleware -> BarMiddleware`。

## 中間件的執行順序

我們從上面可以瞭解到總共有 `3` 種級別的中間件，分別為 `全局中間件`、`類級別中間件`、`方法級別中間件`，如果都定義了這些中間件，執行順序為：`全局中間件 -> 類級別中間件 -> 方法級別中間件`。


在`>=3.0.34`的版本中，新增了優先級的配置，可以在配置方法、路由中間件的時候改變中間件的執行順序，優先級越高，執行順序越靠前。

```php
// 全局中間件配置文件 middleware.php
return [
    'http' => [
        YourMiddleware::class,
        YourMiddlewareB::class => 3,
    ],
];
```
```php
// 路由中間件配置
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    [
        'middleware' => [
            FooMiddleware::class,
            FooMiddlewareB::class => 3,
        ]
    ]
);
```
```php
// 註解中間件配置
#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(FooMiddlewareB::class, 3)]
#[Middlewares([FooMiddlewareC::class => 1, BarMiddlewareD::class => 4])]
class IndexController
{
    
}
```

## 全局更改請求和響應對象

首先，在協程上下文內是有存儲最原始的 PSR-7 `請求對象` 和 `響應對象` 的，且根據 PSR-7 對相關對象所要求的 `不可變性(immutable)`，也就意味着我們在調用 `$response = $response->with***()` 所調用得到的 `$response`，並非為改寫原對象，而是一個 `Clone` 出來的新對象，也就意味着我們儲存在協程上下文內的 `請求對象` 和 `響應對象` 是不會改變的，那麼當我們在中間件內的某些邏輯改變了 `請求對象` 或 `響應對象`，而且我們希望對後續的 *非傳遞性的* 代碼再獲取改變後的 `請求對象` 或 `響應對象`，那麼我們便可以在改變對象後，將新的對象設置到上下文中，如代碼所示：

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request 和 $response 為修改後的對象
$request = \Hyperf\Context\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Context\Context::set(ResponseInterface::class, $response);
```

## 自定義 CoreMiddleWare 的行為

默認情況下，Hyperf 在處理路由找不到或 HTTP 方法不允許時，即 HTTP 狀態碼為 `404`、`405` 的時候，是由 `CoreMiddleware` 直接處理並返回對應的響應對象的，得益於 Hyperf 依賴注入的設計，您可以通過替換對象的方式來把 `CoreMiddleware` 指向由您自己實現的 `CoreMiddleware` 去。

比如我們希望定義一個 `App\Middleware\CoreMiddleware` 類來重寫默認的行為，我們可以先定義一個 `App\Middleware\CoreMiddleware` 類如下，這裏我們僅以 HTTP Server 為例，其它 Server 也可採用同樣的做法來達到同樣的目的。

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

然後再在 `config/autoload/dependencies.php` 定義對象關係重寫 CoreMiddleware 對象：

```php
<?php
return [
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
```

> 這裏直接重寫 CoreMiddleware 的做法需要在 1.1.0+ 版本上才有效，1.0.x 版本仍需要你再將 CoreMiddleware 的上層調用通過 DI 進行重寫，然後替換 CoreMiddleware 的傳值為您定義的中間件類。

## 常用中間件

### 跨域中間件

如果您需要在框架中解決跨域，則可以按照您的需求實現以下中間件

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

### 後置中間件

通常情況下，我們都是最後執行

```
return $handler->handle($request);
```

所以，相當於是前置中間件，如果想要讓中間件邏輯後置，其實只需要更換一下執行順序即可。

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OpenApiMiddleware implements MiddlewareInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // TODO: 前置操作
        try{
            $result = $handler->handle($request);
        } finally {
            // TODO: 後置操作
        }
        return $result;
    }
}

```
