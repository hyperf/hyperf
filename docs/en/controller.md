# Controller

To process HTTP requests by using Controller, you need to bind routing and controller methods in `Config` or `Annotation` way. Check the chapter [Router](en/route.md) for more details.

For the `Request` and `Response`, Hyperf provids `Hyperf\HttpServer\Contract\RequestInterface` and `Hyperf\HttpServer\Contract\ResponseInterface` for you to get parameters and return values. Check the chapters [Request](en/request.md) and [Response](en/response.md) for more details.

## Create a Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // Related objects will be automatically injected by the dependency injection container if you obtain such objects by defining RequestInterface and ResponseInterface on the parameters.
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> Assume this `Controller` has been defined as `/` route through `Config`. (Of course, you can also define it through `Annotation`)

Call this address through `cURL`, and you can see the returned content.

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

## Avoid data confusion between coroutines

In the traditional PHP-FPM framework, an `AbstractController` (or an abstract parent class in other names) would be provided. Then, other defined `Controller` will perform some requests or responses based on the `AbstractController`. However, in Hyperf, **DON'T DO LIKE THAT**. Since most objects, including `Controller`, exist as `Singleton`, which is also for better reuse of objects, and request data are stored at `Context` in coroutine, so **DO NOT** store any request data as a class attribute (non-static properties included).

Of course, it's not impossible if you really want to store request data as class attributes. We have noticed that `Request` and `Response` objects are obtained through injecting `Hyperf\HttpServer\Contract\RequestInterface` and `Hyperf\HttpServer\Contract\ResponseInterface` when we trying to get `Request` and `Response`, so the corresponding object is also a singleton. How is the coroutine safe here? Take the `RequestInterface` as an example, when the corresponding `Hyperf\HttpServer\Request` object gets `PSR-7 request object` from its internal, it is obtained from the `Context`. So the actual class used is only a proxy class, and the actual call is obtained from the `Context`.
