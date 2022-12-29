# Routing

By default, routing uses the [nikic/fast-route](https://github.com/nikic/FastRoute) package. The [hyperf/http-server](https://github.com/hyperf/http-server) component is responsible for connecting to the `Hyperf` server while `RPC` routing is implemented by [hyperf/rpc-server](https://github.com/hyperf/rpc-server) component.

## HTTP routing

### Define routing via configuration file

Under the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) skeleton, all routing definitions are defined in the `config/routes.php` file by default. `Hyperf` also supports `annotation routing`, which is the recommended method, especially when there are a lot of routes.

#### Defining routes using closures

Only a URI and a closure (Closure) are needed to construct a basic route:

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

You can now request `http://host:port/hello-hyperf` through a browser or the `cURL` command line to access the route.

#### Define standard routing

The so-called standard routing refers to the routing handled by the `controllers` and `actions`. This method is quite similar to the closure definition with the obvious difference that business logic can be delegated to respective controller classes:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Any of the following three definitions can achieve the same effect
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

The route is defined as binding the `/hello-hyperf` path to the `hello` method under `App\Controller\IndexController`.

#### Available routing methods

The router provides multiple methods to help you register any HTTP request routing:

```php
use Hyperf\HttpServer\Router\Router;

// Register the route of the HTTP METHOD consistent with the method name
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// Register the route of any HTTP METHOD
Router::addRoute($httpMethod, $uri, $callback);
```

Sometimes you may need to register a route that can correspond to multiple different HTTP methods at the same time. This can be achieved by using the `addRoute` method:

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET','POST','PUT','DELETE'], $uri, $callback);
```

#### How to define route groups

The route group adds the group prefix to each URI. The actual route is `group/route`, namely `/user/index`, `/user/store`, `/user/update`, `/user/delete`

```php
Router::addGroup('/user/', function (){
    Router::get('index', 'App\Controller\UserController@index');
    Router::post('store', 'App\Controller\UserController@store');
    Router::get('update', 'App\Controller\UserController@update');
    Router::post('delete', 'App\Controller\UserController@delete');
});
```

### Define routing via annotations

`Hyperf` provides a very convenient [annotation](en/annotation.md) routing function. You can directly define a route by defining `#[Controller]` or `#[AutoController]` annotations on any class.

#### AutoController annotation

`#[AutoController]` provides routing binding support for most simple access scenarios. When using `#[AutoController]`, `Hyperf` will automatically parse all the `public` methods of the class it is in and provide both `GET` and `POST` Request method.

> When using `#[AutoController]` annotation, `use Hyperf\HttpServer\Annotation\AutoController;` namespace is required.

Pascal case controller names will be converted to snake_case automatically. The following is an example of the correspondence between the controller, annotation and the resulting route:

|    Controller    |             Annotation             |    Route URI   |
|:----------------:|:----------------------------------:|:--------------:|
| MyDataController |         #[AutoController]          | /my_data/index |
| MydataController |         #[AutoController]          | /mydata/index  |
| MyDataController | #[AutoController(prefix: "/data")] | /data/index    |

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf will automatically generate a /user/index route for this method, allowing requests via GET or POST
    public function index(RequestInterface $request)
    {
        // Obtain the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### Controller annotation

`#[Controller]` exists to meet more detailed routing definition requirements. The use of the `#[Controller]` annotation is used to indicate that the current class is a `controller` class, and the `#[RequestMapping]` annotation is required to update the detailed definition of request method and URI.

We also provide a variety of quick and convenient `mapping` annotations, such as `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]` and `#[DeleteMapping]`, each corresponding with a matching request method.

- When using `#[Controller]` annotation, `use Hyperf\HttpServer\Annotation\Controller` namespace is required.
- When using `#[RequestMapping]` annotation, `use Hyperf\HttpServer\Annotation\RequestMapping` namespace is required.
- When using `#[GetMapping]` annotation, `use Hyperf\HttpServer\Annotation\GetMapping` namespace is required.
- When using `#[PostMapping]` annotation, `use Hyperf\HttpServer\Annotation\PostMapping` namespace is required.
- When using `#[PutMapping]` annotation, `use Hyperf\HttpServer\Annotation\PutMapping` namespace is required.
- When using `#[PatchMapping]` annotation, `use Hyperf\HttpServer\Annotation\PatchMapping` namespace is required.
- When using `#[DeleteMapping]` annotation, `use Hyperf\HttpServer\Annotation\DeleteMapping` namespace is required.

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
    // Hyperf will automatically generate a /user/index route for this method, allowing requests via GET or POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Obtain the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### Annotation parameters

Both `#[Controller]` and `#[AutoController]` provide two parameters, `prefix` and `server`.

`prefix` represents the URI prefix for all methods under the controller, the default is the lowercase of the class name. For example, in the case of `UserController`, the `prefix` defaults to `user`, so if the controller method is `index`, then the final route is `/user/index`.
It should be noted that the `prefix` is not always used: when the `path` of a method in a class starts with `/`, it means that the path is defined as an absolute `URI` and the value of `prefix` will be ignored. At the same time, if the `prefix` attribute is not set, then the part after `\\Controller\\` in the controller class namespace will be used as the route prefix in SnakeCase style.

`server` indicates which server the route is defined for. Since `Hyperf` supports starting multiple servers at the same time, there may be multiple HTTP servers running at the same time. Therefore defining the `server` parameter can be used to distinguish which server the route is defined for. The default is `http`.

### Route parameters

> Given route parameters must be consistent with the controller parameter key name and type, otherwise the controller cannot accept the relevant parameters

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```
Access route parameter via controller method injection.

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

Access route parameter via request object.

```php
public function index(RequestInterface $request)
{
    // If it exists, it will return, if it does not exist, it will return the default value null
    $id = $request->route('id');
    // If it exists, it returns, if it doesn't exist, it returns the default value 0
    $id = $request->route('id', 0);
}
```

#### Required parameters

We can define required route parameters using `{}`. For example, `/user/{id}` declares that `id` is a required parameter.

#### Optional parameters

Sometimes you may want a route parameter to be optional. In this case, you can use `[]` to declare the parameter inside the brackets as an optional parameter, such as `/user/[{id}]`.

#### Get routing information

If the devtool component is installed, you can use the `php bin/hyperf.php describe:routes` command to get the routing list information.  You can also provide the path option, which is convenient for obtaining the information of a single route, for example: `php bin/hyperf.php describe:routes --path=/foo/bar`.

## HTTP exceptions

When the route fails to match the route, such as `route not found (404)`, `request method not allowed (405)` and other HTTP exceptions, Hyperf will uniformly throw an exception that inherits the `Hyperf\HttpMessage\Exception\HttpException` class. You need to manage these exceptions through the `ExceptionHandler` mechanism and do the corresponding response processing. By default, you can directly use the `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` provided by the component for exception capture and processing. Not that you need to configure this exception handler in the `config/autoload/exceptions.php` configuration file and ensure that the sequence link between multiple exception handlers is correct.
When you need to customize the response to HTTP exceptions such as `route not found (404)` and `request method not allowed (405)`, you can directly implement your own exception handling based on the code of `HttpExceptionHandler` And configure your own exception handler. For the logic and usage instructions of the exception handler, please refer to [Exception Handling](en/exception-handler.md).
