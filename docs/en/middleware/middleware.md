# Middleware

The middleware here refers to the "Middleware Pattern". This functionality is a major feature of the [hyperf/http-server](https://github.com/hyperf/http-server) component, primarily used to weave through the entire process from `Request` to `Response`. This feature is fully implemented based on [PSR-15](https://www.php-fig.org/psr/psr-15/).

## Principle

*Middleware is primarily used to weave through the entire process from `Request` to `Response`*. By organizing multiple middlewares, data flows in a predetermined manner. Middleware is essentially an `Onion Model`. Let's explain it with a diagram:

![middleware](middleware.jpg)

The order in the diagram is organized as `Middleware 1 -> Middleware 2 -> Middleware 3`. We can note that after the middle line passes through the `Kernel`, which is `Middleware 3`, it returns to `Middleware 2`, creating a nested model. The actual order is:
`Request -> Middleware 1 -> Middleware 2 -> Middleware 3 -> Middleware 2 -> Middleware 1 -> Response`
Focus on the `Kernel`, which is `Middleware 3`. It is the dividing point of the onion. The part before the dividing point is all handled based on the `Request`, and when it passes through the dividing point, the `Kernel` produces the `Response` object, which is also the main code target of the `Kernel`. After that, the `Response` is handled. The `Kernel` is usually implemented by the framework, while the rest is up to you to arrange.

## Defining Global Middleware

Global middleware can only be configured via configuration files. The configuration file is located at `config/autoload/middlewares.php` and is configured as follows:

```php
<?php
return [
    // 'http' corresponds to the value of the name attribute of each server in config/autoload/server.php. This configuration only applies to that Server.
    'http' => [
        // Configure your global middleware in the array, the order depends on the order of this array.
        YourMiddleware::class
    ],
];
```
Simply configure your global middleware in this file and within the corresponding `Server Name`, and all requests under that `Server` will apply the configured global middleware.

## Defining Local Middleware

When some middleware is only targeted at certain requests or controllers, it can be defined as local middleware, which can be defined via configuration files or annotations.

### Defining via Configuration Files

When using configuration files to define routes, you can only define corresponding middleware via configuration files. Local middleware configuration will be completed on the route configuration.
The last parameter `$options` of each route definition method in the `Hyperf\HttpServer\Router\Router` class will receive an array. You can define the middleware for that route by passing the key `middleware` and an array value. Let's demonstrate this with a few route definitions:

```php
<?php
use App\Middleware\FooMiddleware;
use Hyperf\HttpServer\Router\Router;

// Each route definition method can receive an $options parameter
Router::get('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::post('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::put('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::patch('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::delete('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::head('/', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);
Router::addRoute(['GET', 'POST', 'HEAD'], '/index', [\App\Controller\IndexController::class, 'index'], ['middleware' => [FooMiddleware::class]]);

// All routes under this Group will apply the configured middleware
Router::addGroup(
    '/v2', function () {
        Router::get('/index', [\App\Controller\IndexController::class, 'index']);
    },
    ['middleware' => [FooMiddleware::class]]
);
```

### Defining via Annotations

When defining routes via annotations, you can only define middleware via annotations. There are two annotations for defining middleware:
  - The `#[Middleware]` annotation is used when defining a single middleware. Only one such annotation can be defined in one place, and it cannot be redefined.
  - The `#[Middlewares]` annotation is used when defining multiple middlewares. Only one such annotation can be defined in one place, and then multiple middlewares can be defined by defining multiple `#[Middleware]` annotations within it.

> Use `use Hyperf\HttpServer\Annotation\Middleware;` namespace when using `#[Middleware]` annotation;
> Use `use Hyperf\HttpServer\Annotation\Middlewares;` namespace when using `#[Middlewares]` annotation;

***Note: Must be used in conjunction with `#[AutoController]` or `#[Controller]`***

Defining single middleware:

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

Defining multiple middlewares via `#[Middlewares]` annotation:

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

Defining multiple middlewares via `#[Middleware]` annotation:

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

#### Defining Method-Level Middleware

When configuring middleware via configuration files, it is simple to define it at the method level. But how to define it at the method level via annotations? You just need to define the annotation directly on the method.
Class-level middleware has priority over method-level middleware. Let's give an example with code:

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

#### Middleware Related Code

Generate middleware:

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
        // Judge logic flow based on specific business, assuming the token carried by the user is valid here
        $isValidToken = true;
        if ($isValidToken) {
            return $handler->handle($request);
        }

        return $this->response->json(
            [
                'code' => -1,
                'data' => [
                    'error' => 'Middleware token verification invalid, block continuing to execute downwards',
                ],
            ]
        );
    }
}
```
The execution order of middleware is `FooMiddleware -> BarMiddleware`.

## Middleware Execution Order

We can understand from the above that there are `3` levels of middleware, namely `Global Middleware`, `Class-level Middleware`, and `Method-level Middleware`. If all these middlewares are defined, the execution order is: `Global Middleware -> Class-level Middleware -> Method-level Middleware`.


In versions `>=3.0.34`, priority configuration has been added. You can change the execution order of middleware when configuring methods or route middleware. The higher the priority, the earlier the execution order.

```php
// Global middleware configuration file middleware.php
return [
    'http' => [
        YourMiddleware::class,
        YourMiddlewareB::class => 3,
    ],
];
```
```php
// Route middleware configuration
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
// Annotation middleware configuration
#[AutoController]
#[Middleware(FooMiddleware::class)]
#[Middleware(FooMiddlewareB::class, 3)]
#[Middlewares([FooMiddlewareC::class => 1, BarMiddlewareD::class => 4])]
class IndexController
{
    
}
```

## Globally Changing Request and Response Objects

First, in the coroutine context, the original PSR-7 `Request Object` and `Response Object` are stored. And according to the `immutability` required for related objects by PSR-7, it means that the `$response` obtained by calling `$response = $response->with***()` is not overwriting the original object, but a new `Clone` object. This also means that the `Request Object` and `Response Object` stored in the coroutine context will not change. So, when some logic in our middleware changes the `Request Object` or `Response Object`, and we want subsequent *non-passing* code to get the changed `Request Object` or `Response Object`, we can set the new object into the context after changing the object, as shown in the code:

```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// $request and $response are modified objects
$request = \Hyperf\Context\Context::set(ServerRequestInterface::class, $request);
$response = \Hyperf\Context\Context::set(ResponseInterface::class, $response);
```

## Customizing CoreMiddleware Behavior

By default, when Hyperf handles route not found or HTTP method not allowed, that is, when the HTTP status code is `404` or `405`, it is directly handled by `CoreMiddleware` and returns the corresponding response object. Thanks to Hyperf's dependency injection design, you can point `CoreMiddleware` to a `CoreMiddleware` implemented by yourself by replacing the object.

For example, if we want to define a `App\Middleware\CoreMiddleware` class to override the default behavior, we can first define an `App\Middleware\CoreMiddleware` class as follows. Here we only take HTTP Server as an example, and other Servers can also use the same method to achieve the same purpose.

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
        // Override the processing logic for route not found
        return $this->response()->withStatus(404);
    }

    /**
     * Handle the response when the routes found but doesn't match any available methods.
     *
     * @return array|Arrayable|mixed|ResponseInterface|string
     */
    protected function handleMethodNotAllowed(array $methods, ServerRequestInterface $request)
    {
        // Override the processing logic for HTTP method not allowed
        return $this->response()->withStatus(405);
    }
}
```

Then define the object relationship in `config/autoload/dependencies.php` to override the CoreMiddleware object:

```php
<?php
return [
    Hyperf\HttpServer\CoreMiddleware::class => App\Middleware\CoreMiddleware::class,
];
```

> This method of directly overriding CoreMiddleware only takes effect in versions 1.1.0+. In version 1.0.x, you still need to override the upper-layer calls of CoreMiddleware through DI, and then replace the passing value of CoreMiddleware with the middleware class you defined.

## Common Middleware

### CORS Middleware

If you need to solve cross-domain issues in the framework, you can implement the following middleware according to your needs

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
            // Headers can be modified according to actual conditions.
            ->withHeader('Access-Control-Allow-Headers', 'DNT,Keep-Alive,User-Agent,Cache-Control,Content-Type,Authorization');

        Context::set(ResponseInterface::class, $response);

        if ($request->getMethod() == 'OPTIONS') {
            return $response;
        }

        return $handler->handle($request);
    }
}
```

In fact, cross-domain configuration can also be directly hung on `Nginx`.

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

### Post-Middleware

Usually, we execute it at the end

```
return $handler->handle($request);
```

So, it is equivalent to pre-middleware. If you want to make the middleware logic post-process, you just need to change the execution order.

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
        // TODO: Pre-operation
        try{
            $result = $handler->handle($request);
        } finally {
            // TODO: Post-operation
        }
        return $result;
    }
}
```
