# Quick Start

To help you understand `Hyperf` more quickly, this section will use `Creating an HTTP Server` as an example, implementing a simple `Web` service through the definition of routes and controllers. But `Hyperf` is more than that; complete service governance, `gRPC` services, annotations, `AOP`, and more will be elaborated in specific chapters.

## Defining Access Routes

Hyperf uses [nikic/fast-route](https://github.com/nikic/FastRoute) as the default routing component and provides services. You can easily define your routes in `config/routes.php`.

Not only that, the framework also provides extremely powerful and convenient and flexible `Annotation Routing` functionality. For detailed documentation on routing, please refer to the [Routing](../router.md) section.

### Defining routes via configuration files

The route file is located in the `config/routes.php` of the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project. Below are some commonly used usage examples.

```php
<?php
use Hyperf\HttpServer\Router\Router;

// The code examples here provide three different binding definition methods for each example. In actual configuration, only one should be used and the same route should be defined only once.

// Set a GET request route, binding the access address '/get' to the get method of App\Controller\IndexController
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// Set a POST request route, binding the access address '/post' to the post method of App\Controller\IndexController
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// Set a route that allows GET, POST and HEAD requests, binding the access address '/multi' to the multi method of App\Controller\IndexController
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### Defining routes via annotations

`Hyperf` provides extremely powerful and convenient and flexible [Annotation](../annotation.md) functionality, which undoubtedly provides annotation-based ways to define routes. Hyperf provides two types of annotations, `#[Controller]` and `#[AutoController]`, to define a `Controller`. This is just a simple explanation; for more details, please refer to the [Routing](../router.md) section.

### Defining routes via `#[AutoController]` annotation

`#[AutoController]` provides route binding support for most simple access scenarios. When using `#[AutoController]`, Hyperf will automatically parse all `public` methods of the class and provide both `GET` and `POST` request methods.

> When using the `#[AutoController]` annotation, you need to use the `Hyperf\HttpServer\Annotation\AutoController` namespace;

Controllers with camelCase names will be automatically converted to snake_case routes. Below is an example of the correspondence between the controller and the actual route:

|      Controller      |              Annotation               |    Access Route    |
| :-------------------: | :------------------------------------: | :----------------: |
| MyDataController      |        @AutoController()               | /my_data/index     |
| MydataController      |        @AutoController()               | /mydata/index      |
| MyDataController      | @AutoController(prefix="/data")       |  /data/index       |

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf will automatically generate an /index/index route for this method, allowing requests via GET or POST
    public function index(RequestInterface $request)
    {
        // Get the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Defining routes via `#[Controller]` annotation

`#[Controller]` exists to meet more detailed route definition requirements. Using the `#[Controller]` annotation indicates that the current class is a `Controller class`, and it needs to be used in conjunction with the `#[RequestMapping]` annotation to provide more detailed definitions for request methods and request paths.

We also provide various quick and convenient `Mapping Annotations`, such as `#[GetMapping]`, `#[PostMapping]`, `#[PutMapping]`, `#[PatchMapping]`, and `#[DeleteMapping]`, which are 5 convenient annotations used to indicate different allowed request methods.

> When using the `#[Controller]` annotation, you need to use the `Hyperf\HttpServer\Annotation\Controller` namespace;   
> When using the `#[RequestMapping]` annotation, you need to use the `Hyperf\HttpServer\Annotation\RequestMapping` namespace;   
> When using the `#[GetMapping]` annotation, you need to use the `Hyperf\HttpServer\Annotation\GetMapping` namespace;   
> When using the `#[PostMapping]` annotation, you need to use the `Hyperf\HttpServer\Annotation\PostMapping` namespace;   
> When using the `#[PutMapping]` annotation, you need to use the `Hyperf\HttpServer\Annotation\PutMapping` namespace;   
> When using the `#[PatchMapping]` annotation, you need to use the `Hyperf\HttpServer\Annotation\PatchMapping` namespace;   
> When using the `#[DeleteMapping]` annotation, you need to use the `Hyperf\HttpServer\Annotation\DeleteMapping` namespace;  

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

#[Controller]
class IndexController
{
    // Hyperf will automatically generate an /index/index route for this method, allowing requests via GET or POST
    #[RequestMapping(path: "index", methods: "get,post")]
    public function index(RequestInterface $request)
    {
        // Get the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

## Handling HTTP Requests

`Hyperf` is completely open. Essentially, there is no rule that you must implement request handling based on a certain pattern. You can adopt the traditional `MVC pattern`, or you can adopt the `RequestHandler pattern` for development.

Let's take the `MVC pattern` as an example:
Create a `Controller` folder within the `app` folder and create `IndexController.php` as follows. The `index` method gets the `id` parameter from the request and converts it to a `string` type to return to the client.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    // Hyperf will automatically generate an /index/index route for this method, allowing requests via GET or POST
    public function index(RequestInterface $request)
    {
        // Get the id parameter from the request
        $id = $request->input('id', 1);
        // Convert $id to string format and return the value of $id to the client with plain/text Content-Type
        return (string)$id;
    }
}
```

## Dependency Auto-Injection

Dependency auto-injection is a very powerful feature provided by `Hyperf`, and it is the foundation for maintaining the framework's flexibility.

`Hyperf` provides two injection methods: one is the common constructor injection, and the other is injection via the `#[Inject]` annotation. Below we give an example and show the implementation of injection using both methods;

Suppose we have a `\App\Service\UserService` class, which has a `getInfoById(int $id)` method that takes an `id` and eventually returns a user entity. Since the return value is not what we are focused on here, we will not elaborate much. What we want to focus on is how to obtain `UserService` in any class and call its methods. Generally, the method is to instantiate the service class via `new UserService()`, but in `Hyperf`, we have a better solution.

### Injection via Constructor

Simply declare the type of the parameter in the constructor, and `Hyperf` will automatically inject the corresponding object or value.
```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use App\Service\UserService;

#[AutoController]
class IndexController
{
    private UserService $userService;
    
    // Declare the type of the parameter in the constructor, and Hyperf will automatically inject the corresponding object or value
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```

### Injection via `#[Inject]` annotation

Simply declare the type of the corresponding class property via `@var`, and use the `#[Inject]` annotation to mark the property. `Hyperf` will automatically inject the corresponding object or value.

> When using the `#[Inject]` annotation, you need to use the `Hyperf\Di\Annotation\Inject` namespace;

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Di\Annotation\Inject;
use App\Service\UserService;

#[AutoController]
class IndexController
{

    #[Inject]
    private UserService $userService;
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```
    
From the above examples, it is not difficult to find that `$userService` has been automatically injected as the corresponding class object without instantiation.

However, this case does not truly reflect the benefits and power of dependency auto-injection. Let's assume that `UserService` also has many dependencies, and these dependencies also have many other dependencies. When using the `new` instantiation method, we would need to manually instantiate many objects and adjust the corresponding parameter positions. But in `Hyperf`, we do not need to manually manage these dependencies; we only need to declare the class we ultimately use.

And when `UserService` needs to undergo drastic internal changes, such as being replaced from a local service to an RPC remote service, we only need to adjust the dependency configuration to change the class corresponding to the `UserService` key to the new RPC service class.

## Starting the Hyperf Service

Since `Hyperf` has a built-in coroutine server, it means that `Hyperf` will run in the form of `CLI`. Therefore, after defining the routes and the actual logic code, we need to run `php bin/hyperf.php start` via the command line in the project root to start the service.

When the `Console` interface shows that the service has started, you can normally make requests to the service via `cURL` or a browser. By default, the service provides a home page `http://127.0.0.1:9501/`. For the guided example in this chapter, the corresponding access address is `http://127.0.0.1:9501/index/info?id=1`.

## Reloading Code

Since `Hyperf` is a persistent `CLI` application, it means that once the process is started, the parsed `PHP` code will be persisted in the process. This also means that if you modify the `PHP` code after starting the service, the modified code will not change the already started service. If you want the service to reload your modified code, you need to terminate the service by typing `CTRL + C` in the started `Console`, and then re-execute the start command `php bin/hyperf.php start` to complete the startup and reloading.

> Tips: You can also configure the command to start the Server in your IDE, so that you can quickly complete the `start service` or `restart service` operations through the IDE's `start/stop` operations.
> And during non-view development, you can use [TDD (Test-Driven Development)](https://baike.baidu.com/item/TDD/9064369) to develop. This not only can save the trouble of restarting the service and frequently switching windows, but also can ensure the correctness of the interface data.

> In addition, the [Hot Reload/Hot Update](../awesome-components.md?id=%e7%83%ad%e6%9b%b4%e6%96%b0%e7%83%ad%e9%87%bd%e8%bd%bd) chapter in the documentation provides various solutions supported by community developers. If you still want to use a Hot Reload/Hot Update solution, you can learn more.

## Multi-port Listening

`Hyperf` supports listening to multiple ports, but because the `callbacks` objects are directly obtained from the container, the same `Hyperf\HttpServer\Server::class` will be overwritten in the container. Therefore, we need to redefine the `Server` in the dependency relationship to ensure object isolation.

> The same applies to WebSocket and TCP Servers.

`config/autoload/dependencies.php`

```php
<?php

return [
    'InnerHttp' => Hyperf\HttpServer\Server::class,
];
```

`config/autoload/server.php`

```php
<?php
return [
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
        [
            'name' => 'innerHttp',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => ['InnerHttp', 'onRequest'],
            ],
        ],
    ]
];
```

At the same time, the `route file` or `annotations` also need to specify the corresponding `server`, as follows:

- Route file `config/routes.php`

```php
<?php
Router::addServer('innerHttp', function () {
    Router::get('/', 'App\Controller\IndexController@index');
});
```

- Annotations

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController(server: "innerHttp")]
class IndexController
{
    public function index()
    {
        return 'Hello World.';
    }
}
```


## Events

In addition to the `Event::ON_REQUEST` event mentioned above, the framework also supports other events. The event names are as follows.

|         Event Name          |               Remarks                |
| :---------------------: | :---------------------------------: |
|    Event::ON_REQUEST    |                                   |
|     Event::ON_START     | This event is invalid in `SWOOLE_BASE` mode |
| Event::ON_WORKER_START  |                                   |
|  Event::ON_WORKER_EXIT  |                                   |
| Event::ON_PIPE_MESSAGE  |                                   |
|    Event::ON_RECEIVE    |                                   |
|    Event::ON_CONNECT    |                                   |
|  Event::ON_HAND_SHAKE   |                                   |
|     Event::ON_OPEN      |                                   |
|    Event::ON_MESSAGE    |                                   |
|     Event::ON_CLOSE     |                                   |
|     Event::ON_TASK      |                                   |
|    Event::ON_FINISH     |                                   |
|   Event::ON_SHUTDOWN    |                                   |
|    Event::ON_PACKET     |                                   |
| Event::ON_MANAGER_START |                                   |
| Event::ON_MANAGER_STOP  |                                   |
