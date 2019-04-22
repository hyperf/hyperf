# 中间件

这里的中间件指的是"中间件模式"，该功能属于 [hyperf/http-server](https://github.com/hyperf-cloud/http-server) 组件内的一项主要功能，主要用于编织从 `请求(Request)` 到 `响应(Response)` 的整个流程，该功能完成基于 [PSR-15]() 实现。

## 原理

*中间件主要用于编织从 `请求(Request)` 到 `响应(Response)` 的整个流程*，通过对多个中间件的组织，使数据的流动按我们预定的方式进行，中间件的本质是一个 `洋葱模型`，我们通过一个图来解释它：

![middleware](./middleware.jpg)

图中的顺序为按照 `Middleware 1 -> Middleware 2 -> Middleware 3` 的顺序组织着，我们可以注意到当中间的横线穿过 `内核` 即 `Middleware 3` 后，又回到了 `Middleware 2`，为一个嵌套模型，那么实际的顺序其实就是：   
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`   
重点放在 `核心` 即 `Middleware 3`，它是洋葱的分界点，分界点前面的部分其实都是基于 `请求(Request)` 进行处理，而经过了分界点时，`内核` 就产出了 `响应(Response)` 对象，也是 `内核` 的主要代码目标，在之后便是对 `响应(Response)` 进行处理了，`内核` 通常是由框架负责实现的，而其它的就由你来编排了。

## 定义全局中间件

全局中间件只可通过配置文件的方式来配置，配置文件位于 `config/autoload/middlewares.php` ，配置如下：   
```php
<?php
return [
    // http 对应 config/server.php 内每个 server 的 name 属性对应的值，该配置仅应用在该 Server 中
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

在使用配置文件定义路由时，推荐通过配置文件来定义对应的中间件，局部中间件的配置将在路由配置上完成。   
`Hyperf\HttpServer\Router\Router` 类的每个定义路由的方法的最后一个参数 `$options` 都将接收一个数组，可通过传递键值 `middleware` 及一个数组值来定义该路由的中间件，我们通过几个路由定义来演示一下:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// 每个路由定义方法都可接收一个 $options 参数
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);

// 该 Group 下的所有路由都将应用配置的中间件
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [ForMiddleware::class]]
);

```

### 通过注解定义

在通过注解定义路由时，我们推荐通过注解的方式来定义中间件，对中间件的定义有两个注解，分别为：   
  - `@Middleware` 注解为定义单个中间件时使用，在一个地方仅可定义一个该注解，不可重复定义
  - `@Middlewares` 注解为定义多个中间件时使用，在一个地方仅可定义一个该注解，然后通过在该注解内定义多个 `@Middleware` 注解实现多个中间件的定义
  
> 使用 `@Middleware` 注解时需 `use Hyperf\HttpServer\Annotation\Middleware;` 命名空间；
> 使用 `@Middlewares` 注解时需 `use Hyperf\HttpServer\Annotation\Middlewares;` 命名空间；

定义单个中间件：

```php
<?php

use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @AutoController()
 * @Middleware(FooMiddleware::class)
 */
class IndexController
{
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

定义多个中间件：

```php
<?php

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @AutoController()
 * @Middlewares({
 *     @Middleware(FooMiddleware::class),
 *     @Middleware(BarMiddleware::class)
 * })
 */
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
方法级别上的中间件会优先于类级别的中间件，我们通过代码来举例一下：   

```php
<?php

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;

/**
 * @AutoController()
 * @Middlewares({
 *     @Middleware(FooMiddleware::class)
 * })
 */
class IndexController
{
    
    /**
     * @AutoController()
     * @Middlewares({
     *     @Middleware(BarMiddleware::class)
     * })
     */
    public function index()
    {
        return 'Hello Hyperf.';
    }
}
```

中间件的执行顺序为 `BarMiddleware -> FooMiddleware`。

## 中间件的执行顺序

我们从上面可以了解到总共有 `3` 种级别的中间件，分别为 `全局中间件`、`类级别中间件`、`方法级别中间件`，如果都定义了这些中间件，执行顺序为：`全局中间件 -> 类级别中间件 -> 方法级别中间件`。

