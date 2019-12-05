# Middleware

The middleware here refers to the `middleware mode`, which is a main function in the [hyperf/http-server](https://github.com/hyperf/http-server) component. It is mainly used to weave the entire process from `Request` to `Response`. Based on [PSR-15](https://www.php-fig.org/psr/psr-15/) implementation.

## Principle

*The middleware is mainly used to weave the entire process from `Request` to `Response`.* Through the organization of multiple middleware, the flow of data is carried out in the way we order. The essence of middleware is an `Onion model`. Explain it through a diagram:

![middleware](./middleware.jpg)

The order in the figure is organized in the order of `Middleware 1 -> Middleware 2 -> Middleware 3`. We can notice that when the middle horizontal line passes through the `kernel`, ie `Middleware 3`, it returns to `Middleware 2 `, this is a nested model, then the actual order is actually:
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`
The focus is on the `kernal`, ie `Middleware 3`, which is the dividing point of the onion. The part before the demarcation point is actually processed based on the `Request`, and when the demarcation point is passed, the `kernel` generated the `Response` object, it is also the main code target of the `kernel`. After that, is handled the `Response` object by the rest of middlewares. The `kernel` is usually implemented by the framework, and the rest is up to you.

## Define global middleware

The global middleware can ONLY be configured through the configuration file. The configuration file is located in `config/autoload/middlewares.php` and the configuration is as follows:   
```php
<?php
return [
    // `http` corresponds to the value corresponding to the name attribute of each server in config/autoload/server.php. This configuration is only applied to the server you configured.
    'http' => [
        // Configure your global middleware in an array, in order according to the order of the array
        YourMiddleware::class
    ],
];
```
Simply configure your global middleware in the file and the corresponding `Server Name`, it means all requests under the `Server` will apply the configured global middleware.

## Define local middleware

When some of our middleware is only for certain requests or controllers, we can define them as local middleware, which can be defined by configuration file or defined by annotation.

### Defined by configuration file

When defining a route using a configuration file, it is recommended to define the corresponding middleware through the configuration file. The configuration of the local middleware will be completed on the routing configuration.   
The last parameter `$options` of each method defining the route of the `Hyperf\HttpServer\Router\Router` class will receive an array, which can be defined by passing the key value `middleware` and an array value to define the middleware of the route. We demonstrate this through several route definitions:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// Each route definition method can accept a $options parameter
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [ForMiddleware::class]]);

// All routings under the group will apply the configured middleware
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [ForMiddleware::class]]
);

```

### Defined by annotation

When defining routes through annotations, we recommend defining middleware by means of annotations. There are two annotations for the definition of middleware, namely:
  - `@Middleware` annotation are used when defining a single middleware. Only one annotation can be defined in one place, and cannot be defined repeatedly.
  - `@Middlewares` annotation are used when defining multiple middleware. Only one annotation can be defined in one place, and then multiple middleware definitions can be implemented by defining multiple `@Middleware` annotations within the annotation.
  
> Use `@Middleware` should `use Hyperf\HttpServer\Annotation\Middleware;` namespace；   
> Use `@Middlewares` should `use Hyperf\HttpServer\Annotation\Middlewares;` namespace；

Define a single middleware：

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

Define multiple middlewares：

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

#### Define method level middleware

It is very simple to define the method level when configuring the middleware through the configuration file. How about defined by  annotations? You only need to define the annotation directly on the method.
The method level middleware takes precedence over the class level middleware. Let's take a look at the code:

```php
<?php

use App\Middleware\BarMiddleware;
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;

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
#### Related

Generate a middleware by command:

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
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // According to the specific business judgment logic, it is assumed that the token carried by the user is valid here.
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => 'The token is invalid, preventing further execution.',
                ],
            ]
        );
    }
}
```
The order of execution of the middleware is `FooMiddleware -> BarMiddleware`.

## The order of Middleware execution

We can see from the above that there are a total of 3 levels of middleware, namely `global middleware`, `class level middleware`, `method level middleware`. If these middlewares are defined, the order of execution is :`Global Middleware -> Method Level Middleware -> Class Level Middleware`.

## Change request and response objects globally

First, there is a storage of the most primitive PSR-7 `request object` and `response object` within the context of the coroutine, and the `immutable` required by the PSR-7 for the related object means The `$response` we called by calling `$response = $response->with***()` is not a rewrite of the original object, but a new object from `Clone`, which means the `request object` and `response object` which stored in the context of the coroutine will not change, then when we have some logic in the middleware changed the `request object` or `response object`, and we hope for the follow-up * Non-transitive * code to get the changed `request object` or `response object`, then we can set the new object to the context after changing the object, as shown in the code:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request and $response are the modified objects
$request = \Hyperf\Utils\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Utils\Context::set(ResponseInterface::class, $response);
```
