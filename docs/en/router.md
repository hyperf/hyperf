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

! > The annotation classes that appear below are classes under the `use Hyperf\HttpServer\Annotation\` namespace, such as `Hyperf\HttpServer\Annotation\AutoController`.

#### Annotation parameters

Both `#[Controller]` and `#[AutoController]` provide two parameters, `prefix` and `server`.

`prefix` indicates the prefix of all the method routes under the controller, by default, the part after \Controller\` in the controller class namespace will be used as the prefix of the route with SnakeCase nomenclature, e.g. \App\Controller\Demo\UserController` then prefix will be \demo/user` by default.

For example, if `App\Controller\Demo\UserController`, the prefix will be `demo/user` by default, and if the path of a method in the class is `index`, the final route will be `/demo/user/index`.

! > Note that `prefix` is not always valid, when the path of a method within a class starts with `/`, the path is defined from the `URI` header, which means that the prefix value is ignored.

`server` indicates which `HTTP Server` the route is defined on. Since Hyperf supports multiple `HTTP Servers` at the same time, this parameter can be used to distinguish which `Server` the route is defined for, the default is `http`.

|              Controller              |           Annotation            |      Route URI      |
|:------------------------------------:|:-------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @AutoController()        |   /my_data/index    |
|   App\Controller\MydataController    |        @AutoController()        |    /mydata/index    |
|   App\Controller\MyDataController    | @AutoController(prefix="/data") |     /data/index     |
| App\Controller\Demo\MyDataController |        @AutoController()        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @AutoController(prefix="/data") |     /data/index     |



|              Controller              |                                    Annotation                                     |      Route URI      |
|:------------------------------------:|:---------------------------------------------------------------------------------:|:-------------------:|
|   App\Controller\MyDataController    |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        |   /my_data/index    |
| App\Controller\Demo\MyDataController |        @Controller() + @RequestMapping(path: "index", methods: "get,post")        | /demo/my_data/index |
| App\Controller\Demo\MyDataController | @Controller(prefix="/data") + @RequestMapping(path: "index", methods: "get,post") |     /data/index     |
|   App\Controller\MyDataController    |       @Controller() + @RequestMapping(path: "/index", methods: "get,post")        |       /index        |

#### AutoController annotation

`#[AutoController]` provides routing binding support for most simple access scenarios. When using `#[AutoController]`, `Hyperf` will automatically parse all the `public` methods of the class it is in and provide both `GET` and `POST` Request method.

> When using `#[AutoController]` annotation, `use Hyperf\HttpServer\Annotation\AutoController;` namespace is required.

Pascal case controller names will be converted to snake_case automatically. The following is an example of the correspondence between the controller, annotation and the resulting route:


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

#### Validate parameters

You can also use regular expression to validate parameters. Here are some examples
```php
use Hyperf\HttpServer\Router\Router;

// Matches /user/42, but not /user/xyz
Router::addRoute('GET', '/user/{id:\d+}', 'handler');

// Matches /user/foobar, but not /user/foo/bar
Router::addRoute('GET', '/user/{name}', 'handler');

// Matches /user/foo/bar as well
Router::addRoute('GET', '/user/{name:.+}', 'handler');

// This route
Router::addRoute('GET', '/user/{id:\d+}[/{name}]', 'handler');
// Is equivalent to these two routes
Router::addRoute('GET', '/user/{id:\d+}', 'handler');
Router::addRoute('GET', '/user/{id:\d+}/{name}', 'handler');

// Multiple nested optional parts are possible as well
Router::addRoute('GET', '/user[/{id:\d+}[/{name}]]', 'handler');

// This route is NOT valid, because optional parts can only occur at the end
Router::addRoute('GET', '/user[/{id:\d+}]/{name}', 'handler');
```

#### Get routing information

If the devtool component is installed, you can use the `php bin/hyperf.php describe:routes` command to get the routing list information.  You can also provide the path option, which is convenient for obtaining the information of a single route, for example: `php bin/hyperf.php describe:routes --path=/foo/bar`.

## HTTP exceptions

When the route fails to match the route, such as `route not found (404)`, `request method not allowed (405)` and other HTTP exceptions, Hyperf will uniformly throw an exception that inherits the `Hyperf\HttpMessage\Exception\HttpException` class. You need to manage these exceptions through the `ExceptionHandler` mechanism and do the corresponding response processing. By default, you can directly use the `Hyperf\HttpServer\Exception\Handler\HttpExceptionHandler` provided by the component for exception capture and processing. Not that you need to configure this exception handler in the `config/autoload/exceptions.php` configuration file and ensure that the sequence link between multiple exception handlers is correct.
When you need to customize the response to HTTP exceptions such as `route not found (404)` and `request method not allowed (405)`, you can directly implement your own exception handling based on the code of `HttpExceptionHandler` And configure your own exception handler. For the logic and usage instructions of the exception handler, please refer to [Exception Handling](en/exception-handler.md).
