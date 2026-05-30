# Routing

By default, routing is supported by [nikic/fast-route](https://github.com/nikic/FastRoute) and integrated into `Hyperf` by the [hyperf/http-server](https://github.com/hyperf/http-server) component. RPC routing is handled by the corresponding [hyperf/rpc-server](https://github.com/hyperf/rpc-server) component.

## HTTP Routing

### Defining Routes via Configuration Files

In the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton), all routes are defined in the `config/routes.php` file by default. Of course, if you have many routes, you can extend this file to suit your needs. However, `Hyperf` also supports `Annotation Routing`, which we recommend, especially when you have many routes.

#### Defining Routes via Closures

Building a basic route only requires a URI and a `Closure`. Let's demonstrate this with code:

```php
<?php
use Hyperf\HttpServer\Router\Router;

Router::get('/hello-hyperf', function () {
    return 'Hello Hyperf.';
});
```

You can access this route by requesting `http://host:port/hello-hyperf` via your browser or `cURL` command line.

#### Defining Standard Routes

Standard routes are those handled by a `Controller` and an `Action`. If you use the `Request Handler` pattern, it is similar. Let's demonstrate this with code:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// Any of the following three methods will achieve the same effect
Router::get('/hello-hyperf', 'App\Controller\IndexController::hello');
Router::get('/hello-hyperf', 'App\Controller\IndexController@hello');
Router::get('/hello-hyperf', [App\Controller\IndexController::class, 'hello']);
```

This route definition binds the `/hello-hyperf` path to the `hello` method in `App\Controller\IndexController`.

#### Available Routing Methods

The router provides several methods to help you register routes for any HTTP request:

```php
use Hyperf\HttpServer\Router\Router;

// Register routes for HTTP methods consistent with the method name
Router::get($uri, $callback);
Router::post($uri, $callback);
Router::put($uri, $callback);
Router::patch($uri, $callback);
Router::delete($uri, $callback);
Router::head($uri, $callback);

// Register routes for any HTTP method
Router::addRoute($httpMethod, $uri, $callback);
```

Sometimes you may need to register a route that can respond to multiple HTTP methods simultaneously. This can be defined via the `addRoute` method:

```php
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST','PUT','DELETE'], $uri, $callback);
```

#### Defining Route Groups

The actual route is `group/route`, i.e., `/user/index`, `/user/store`, `/user/update`, `/user/delete`

```php
Router::addGroup('/user/',function (){
    Router::get('index','App\Controller\UserController@index');
    Router::post('store','App\Controller\UserController@store');
    Router::get('update','App\Controller\UserController@update');
    Router::post('delete','App\Controller\UserController@delete');
});
```

### Defining Routes via Annotations

`Hyperf` provides a very convenient [Annotation](annotation.md) routing feature. You can define a route directly on any class by using the `#[Controller]` or `#[AutoController]` annotation.

!> The annotation classes appearing below belong to the `use Hyperf\HttpServer\Annotation\` namespace, such as `Hyperf\HttpServer\Annotation\AutoController`

#### Annotation Parameters

Both `#[Controller]` and `#[AutoController]` provide `prefix` and `server` parameters.

`prefix` represents the route prefix for all methods under the controller. By default, the part after `\Controller\` in the controller class namespace is used as the route prefix in snake_case.

For example, for `App\Controller\Demo\UserController`, the prefix defaults to `demo/user`. If a method's path within the class is `index`, the final route is `/demo/user/index`.

!> Note that `prefix` is not always effective. When a method's path within the class starts with `/`, it indicates that the path is defined from the beginning of the `URI`, meaning the value of `prefix` will be ignored.

`server` indicates which `HTTP Server` the route is defined on. Since Hyperf supports starting multiple `HTTP Servers` simultaneously, this parameter can be used to distinguish which `Server` the route is defined for. It defaults to `http`.

| Controller | Annotation | Access Route |
|:------------------------------------:|:-------------------------------:|:------------------:|
| App\Controller\MyDataController | @AutoController() | /my_data/index |
| App\Controller\MydataController | @AutoController() | /mydata/index |
| App\Controller\MyDataController | @AutoController(prefix="/data") | /data/index |
| App\Controller\Demo\MydataController | @AutoController() | /demo/mydata/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") | /data/index |


| Controller | Annotation | Access Route |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
| App\Controller\MyDataController | @Controller() + @RequestMapping(path: "index", methods: "get,post") | /my_data/index |
| App\Controller\Demo\MyDataController | @Controller() + @RequestMapping(path: "index", methods: "get,post") | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") | /data/index |
| App\Controller\MyDataController | @Controller() + @RequestMapping(path: "/index", methods: "get,post") | /index |

#### `#[AutoController]` Annotation

`#[AutoController]` provides route binding support for most simple access scenarios. When using `#[AutoController]`, `Hyperf` automatically parses all `public` methods in the class and provides both `GET` and `POST` request methods.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class UserController
{
    // Hyperf will automatically generate a /user/index route for this method, 
    // allowing requests via GET or POST
    public function index(RequestInterface $request)
    {
        // Get the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

#### `#[Controller]` Annotation

`#[Controller]` exists to meet more detailed routing definition requirements. The `#[Controller]` annotation is used to indicate that the current class is a `Controller` class, and it needs to be used in conjunction with the `#[RequestMapping]` annotation to define the request method and request path in more detail.
We also provide various quick and convenient `Mapping` annotations, such as `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`, and `#[DeleteMapping]`, which are 5 convenient annotations used to indicate that different request methods are allowed.

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
    // Hyperf will automatically generate a /user/index route for this method, 
    // allowing requests via GET or POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Get the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Route Parameters

> The route parameters defined by this framework must be consistent with the controller parameter key names and types; otherwise, the controller cannot receive the relevant parameters.

```php
Router::get('/user/{id}', 'App\Controller\UserController::info');
```

```php
public function info(int $id)
{
    $user = User::find($id);
    return $user->toArray();
}
```

Get via `route` method:

```php
public function index(RequestInterface $request)
{
    // Returns if exists, otherwise returns default value null
    $id = $request->route('id');
    // Returns if exists, otherwise returns default value 0
    $id = $request->route('id', 0);
}
```

#### Required Parameters

We can define some parameters for `$uri` by declaring parameters via `{}`, such as `/user/{id}`, which declares `id` as a required parameter.

#### Optional Parameters

Sometimes you may want this parameter to be optional. You can declare the parameter within brackets as an optional parameter via `[]`, such as `/user/[{id}]`.

#### Validating Parameters

You can also use regular expressions to validate parameters. Here are some examples:

```php
use Hyperf\HttpServer\Router\Router;

// Matches /user/42, but cannot match /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// Matches /user/foobar, but cannot match /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// Can also match /user/foo/bar as well
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// This route
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// Equivalent to the following two routes
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// Multiple optional nested brackets are also allowed
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// This is an invalid route because optional parts can only appear at the end
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### Getting Route Information

If the devtool component is installed, you can use the `php bin/hyperf.php describe:routes` command to get the route list information.
It also provides a `path` option, which is convenient for getting single route information. The corresponding command is `php bin/hyperf.php describe:routes --path=/foo/bar`.

## HTTP Exceptions

When no route is matched, such as `Route not found (404)` or `Method not allowed (405)` HTTP exceptions, Hyperf will uniformly throw a subclass of the `Hyperf\HttpMessage\Exception\HttpException` exception class. You need to manage these exceptions through the ExceptionHandler mechanism and perform corresponding response processing. By default, you can directly use the `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` provided by the component to capture and handle exceptions. Note that this exception handler needs to be configured by yourself in the `config/autoload/exceptions.php` configuration file, and ensure that the order of multiple exception handlers is correct.
When you need to customize the response for HTTP exception situations such as `Route not found (404)` or `Method not allowed (405)`, you can directly implement your own exception handler based on the code of `HttpExceptionHandler`, and configure your own exception handler. For the logic and usage instructions of exception handlers, please refer to [Exception Handler](exception-handler.md)
