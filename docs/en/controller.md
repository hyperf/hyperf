# Controller

To handle HTTP requests via a controller, you need to bind routes to controller methods using `configuration files` or `annotations`. Please refer to the [Router](router.md) section for details.
For `Request` and `Response`, Hyperf provides `Hyperf\HttpServer\Contract\RequestInterface` and `Hyperf\HttpServer\Contract\ResponseInterface` to facilitate obtaining input parameters and returning data. For detailed content on [Request](request.md) and [Response](response.md), please refer to the corresponding sections.

## Writing a Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // Define RequestInterface and ResponseInterface on parameters to obtain relevant objects, 
    // which will be automatically injected by the dependency injection container
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> We assume that this `Controller` has already defined the route as `/` via the configuration file, but you can also use annotation routing.

Call this address via `cURL` to see the returned content.

```bash
$ curl 'http://127.0.0.1:9501/?target=Hyperf'
Hello Hyperf.
```

## Avoiding Data Confusion Between Coroutines

In traditional PHP-FPM frameworks, there is a habit of providing an `AbstractController` or other similarly named `Controller abstract parent class`, and the defined `Controller` needs to inherit from it to obtain some request data or perform some return operations. In Hyperf, you **cannot do this** because most objects in Hyperf, including `Controller`, exist in the form of `Singleton`, which is also for better object reuse. For data related to the request, it needs to be stored in the `Coroutine Context` under the coroutine. Therefore, when writing code, please be sure **not** to store data related to a single request in class attributes, including non-static attributes.

Of course, if you really want to store request data via class attributes, it is not impossible. We can notice that when we obtain `Request` and `Response` objects, we do so by injecting `Hyperf\HttpServer\Contract\RequestInterface` and `Hyperf\HttpServer\Contract\ResponseInterface`. Isn't the corresponding object also a singleton? How is coroutine safety achieved here? Take `RequestInterface` as an example, the corresponding `Hyperf\HttpServer\Request` object internally obtains the `PSR-7 Request object` from the `Coroutine Context`. So the actual class used is just a proxy class, and what is actually called is obtained from the `Coroutine Context`.
