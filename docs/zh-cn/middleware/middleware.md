# 中间件

这里的中间件指的是 "中间件模式"，该功能属于 [hyperf/http-server](https://github.com/hyperf/http-server) 组件内的一项主要功能，主要用于编织从 `请求(Request)` 到 `响应(Response)` 的整个流程，该功能完全基于 [PSR-15](https://www.php-fig.org/psr/psr-15/) 实现。

## 原理

*中间件主要用于编织从 `请求(Request)` 到 `响应(Response)` 的整个流程*，通过对多个中间件的组织，使数据的流动按我们预定的方式进行，中间件的本质是一个 `洋葱模型`，我们通过一个图来解释它：

![middleware](middleware.jpg)

图中的顺序为按照 `Middleware 1 -> Middleware 2 -> Middleware 3` 的顺序组织着，我们可以注意到当中间的横线穿过 `内核` 即 `Middleware 3` 后，又回到了 `Middleware 2`，为一个嵌套模型，那么实际的顺序其实就是：   
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`   
重点放在 `核心` 即 `Middleware 3`，它是洋葱的分界点，分界点前面的部分其实都是基于 `请求(Request)` 进行处理，而经过了分界点时，`内核` 就产出了 `响应(Response)` 对象，也是 `内核` 的主要代码目标，在之后便是对 `响应(Response)` 进行处理了，`内核` 通常是由框架负责实现的，而其它的就由您来编排了。

## 定义全局中间件

全局中间件只可通过配置文件的方式来配置，配置文件位于 `config/autoload/middlewares.php` ，配置如下：   
```php
<?php
return [
    // http 对应 config/autoload/server.php 内每个 server 的 name 属性对应的值，该配置仅应用在该 Server 中
    'http' => [
        // 数组内配置您的全局中间件，顺序根据该数组的顺序
        YourMiddleware::class
    ],
];
```
只需将您的全局中间件配置在该文件及对应的 `Server Name` 内，即该 `Server` 下的所有请求都会应用配置的全局中间件。

## 定义局部中间件

当我们有些中间件仅仅面向某些请求或控制器时，即可将其定义为局部中间件，可通过配置文件的方式定义或注解的方式。

### 通过配置文件定义

在使用配置文件定义路由时，您仅可通过配置文件来定义对应的中间件，局部中间件的配置将在路由配置上完成。   
`Hyperf\HttpServer\Router\Router` 类的每个定义路由的方法的最后一个参数 `$options` 都将接收一个数组，可通过传递键值 `middleware` 及一个数组值来定义该路由的中间件，我们通过几个路由定义来演示一下:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// 每个路由定义方法都可接收一个 $options 参数
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);

// 该 Group 下的所有路由都将应用配置的中间件
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [FooMiddleware::class]]
);

```

### 通过注解定义

在通过注解定义路由时，您仅可通过注解的方式来定义中间件，对中间件的定义有两个注解，分别为：   
  - `#[Middleware]` 注解为定义单个中间件时使用，在一个地方仅可定义一个该注解，不可重复定义
  - `#[Middlewares]` 注解为定义多个中间件时使用，在一个地方仅可定义一个该注解，然后通过在该注解内定义多个 `#[Middleware]` 注解实现多个中间件的定义

> 使用 `#[Middleware]` 注解时需 `use Hyperf\HttpServer\Annotation\Middleware;` 命名空间；   
> 使用 `#[Middlewares]` 注解时需 `use Hyperf\HttpServer\Annotation\Middlewares;` 命名空间；

***注意：必须配合 `#[AutoController]` 或者 `#[Controller]` 使用***

定义单个中间件：

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

通过 `#[Middlewares]` 注解定义多个中间件：

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

通过 `#[Middleware]` 注解定义多个中间件：

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

#### 定义方法级别的中间件

在通过配置文件的方式配置中间件时定义到方法级别上很简单，那么要通过注解的形式定义到方法级别呢？您只需将注解直接定义到方法上即可。   
类级别上的中间件会优先于方法级别的中间件，我们通过代码来举例一下：   

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

#### 中间件相关的代码

生成中间件

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
        // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => '中间件验证token无效，阻止继续向下执行',
                ],
            ]
        );
    }
}
```
中间件的执行顺序为 `FooMiddleware -> BarMiddleware`。

## 中间件的执行顺序

我们从上面可以了解到总共有 `3` 种级别的中间件，分别为 `全局中间件`、`类级别中间件`、`方法级别中间件`，如果都定义了这些中间件，执行顺序为：`全局中间件 -> 类级别中间件 -> 方法级别中间件`。


在`>=3.0.34`的版本中，新增了优先级的配置，可以在配置方法、路由中间件的时候改变中间件的执行顺序，优先级越高，执行顺序越靠前。

```php
// 全局中间件配置文件 middleware.php
return [
    'http' => [
        YourMiddleware::class,
        YourMiddlewareB::class => 3,
    ],
];
```
```php
// 路由中间件配置
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
// 注解中间件配置
#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(FooMiddlewareB::class, 3)]
#[Middlewares([FooMiddlewareC::class => 1, BarMiddlewareD::class => 4])]
class IndexController
{
    
}
```

## 全局更改请求和响应对象

首先，在协程上下文内是有存储最原始的 PSR-7 `请求对象` 和 `响应对象` 的，且根据 PSR-7 对相关对象所要求的 `不可变性(immutable)`，也就意味着我们在调用 `$response = $response->with***()` 所调用得到的 `$response`，并非为改写原对象，而是一个 `Clone` 出来的新对象，也就意味着我们储存在协程上下文内的 `请求对象` 和 `响应对象` 是不会改变的，那么当我们在中间件内的某些逻辑改变了 `请求对象` 或 `响应对象`，而且我们希望对后续的 *非传递性的* 代码再获取改变后的 `请求对象` 或 `响应对象`，那么我们便可以在改变对象后，将新的对象设置到上下文中，如代码所示：

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request 和 $response 为修改后的对象
$request = \Hyperf\Context\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Context\Context::set(ResponseInterface::class, $response);
```

## 自定义 CoreMiddleWare 的行为

默认情况下，Hyperf 在处理路由找不到或 HTTP 方法不允许时，即 HTTP 状态码为 `404`、`405` 的时候，是由 `CoreMiddleware` 直接处理并返回对应的响应对象的，得益于 Hyperf 依赖注入的设计，您可以通过替换对象的方式来把 `CoreMiddleware` 指向由您自己实现的 `CoreMiddleware` 去。

比如我们希望定义一个 `App\Middleware\CoreMiddleware` 类来重写默认的行为，我们可以先定义一个 `App\Middleware\CoreMiddleware` 类如下，这里我们仅以 HTTP Server 为例，其它 Server 也可采用同样的做法来达到同样的目的。

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
        // 重写路由找不到的处理逻辑
        return $this->response()->withStatus(404);
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        // 重写 HTTP 方法不允许的处理逻辑
        return $this->response()->withStatus(405);
    }
}
```

然后再在 `config/autoload/dependencies.php` 定义对象关系重写 CoreMiddleware 对象：

```php
<?php
return [
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
```

> 这里直接重写 CoreMiddleware 的做法需要在 1.1.0+ 版本上才有效，1.0.x 版本仍需要你再将 CoreMiddleware 的上层调用通过 DI 进行重写，然后替换 CoreMiddleware 的传值为您定义的中间件类。

## 常用中间件

### 跨域中间件

如果您需要在框架中解决跨域，则可以按照您的需求实现以下中间件

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
            // Headers 可以根据实际情况进行改写。
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}

```

实际上，跨域配置也可以直接挂在 `Nginx` 上。

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

### 后置中间件

通常情况下，我们都是最后执行

```
return $handler->handle($request);
```

所以，相当于是前置中间件，如果想要让中间件逻辑后置，其实只需要更换一下执行顺序即可。

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
            // TODO: 后置操作
        }
        return $result;
    }
}

```
