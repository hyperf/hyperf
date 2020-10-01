# QuickStart

In order to let you know more about the use of `Hyperf`, this chapter will introduce `Create an HTTP Server` as an example to implement a simple `Web Service` by defining routes and controllers. Not only that, The features of Hyperf like service governance, `gRPC` services, annotations programming, `AOP` and other features will be explained by specific chapters.

## Define a route

Hyperf use [nikic/fast-route] (https://github.com/nikic/FastRoute) as the default routing component, so you can easily define your route in `config/routes.php`.   

Not only that，Hyperf also provides an extremely powerful and convenient "Annotation Routing" feature, for more information on routing, please refer to the [Router] (en/router.md) section.

### Define routes by file configuration

The routes file is located in `config/routes.php` of the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project . Below are some common usage examples.

```php
<?php
use Hyperf\HttpServer\Router\Router;

// The code example here provides three different binding definitions for each example. In practice, you just need to define one of them.

// Set the route for a GET request, bind the access address '/get' to App\Controller\IndexController::get()
Router::get('/get', 'App\Controller\IndexController::get');
Router::get('/get', 'App\Controller\IndexController@get');
Router::get('/get', [\App\Controller\IndexController::class, 'get']);

// Set the route for a POST request, bind the access address '/post' to App\Controller\IndexController::post()
Router::post('/post', 'App\Controller\IndexController::post');
Router::post('/post', 'App\Controller\IndexController@post');
Router::post('/post', [\App\Controller\IndexController::class, 'post']);

// Set a route that allows GET, POST, and HEAD requests, bind the access address '/multi' to App\Controller\IndexController::multi()
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController::multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', 'App\Controller\IndexController@multi');
Router::addRoute(['GET', 'POST', 'HEAD'], '/multi', [\App\Controller\IndexController::class, 'multi']);
```

### Define routes by annotations

`Hyperf` provides an extremely powerful and convenient [annotation] (en/annotation.md) feature, and there is no doubt that the definition of the route also provides a way to define by annotation. Hyperf provides `@Controller` and `@ AutoController` annotations to define a `Controller`. Here is a brief description. For more details, please refer to the [Routing] (en/router.md) section.

### Define routes by `@AutoController`
s
`@AutoController` provides routing binding support for most simple access scenarios. When using `@AutoController`, Hyperf will automatically parse all `public` methods of the class and provide `GET` and `POST` requests at the same time.

> Using `@AutoController` annotation should `use Hyperf\HttpServer\Annotation\AutoController;` namespace；

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 */
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allows GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Define routes by `@Controller`

`@Controller` using for more flexiable routing definition requirements, using `@Controller` annotation for current class is means it's a `Controller class`, and with the `@RequestMapping` annotation to define the request method and the request path.
We also provide a variety of quick and convenient `Mapping annotations`, such as `@GetMapping`, `@PostMapping`, `@PutMapping`, `@PatchMapping`, `@DeleteMapping` 5 convenient annotations to indicate that different allowed request method.

> Using `@Controller` annotation should `use Hyperf\HttpServer\Annotation\Controller;` namespace；   
> Using `@RequestMapping` annotation should `use Hyperf\HttpServer\Annotation\RequestMapping;` namespace；   
> Using `@GetMapping` annotation should `use Hyperf\HttpServer\Annotation\GetMapping;` namespace；   
> Using `@PostMapping` annotation should `use Hyperf\HttpServer\Annotation\PostMapping;` namespace；   
> Using `@PutMapping` annotation should `use Hyperf\HttpServer\Annotation\PutMapping;` namespace；   
> Using `@PatchMapping` annotation should `use Hyperf\HttpServer\Annotation\PatchMapping;` namespace；   
> Using `@DeleteMapping` annotation should `use Hyperf\HttpServer\Annotation\DeleteMapping;` namespace；  

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

/**
 * @Controller()
 */
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allows GET or POST requests
    /**
     * @RequestMapping(path="index", methods="get,post")
     */
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```


## Handle HTTP Request

`Hyperf` is totally open. There is essentially no requirement that you must implement request processing based on some kind of certain mode. You could use the traditional `MVC mode` or the `RequestHandler mode` to handle the request.
Let's take the example of `MVC mode`:
Create a `Controller` folder in the `app` folder and create `IndexController.php` as follows. The `index` method gets the `id` parameter from the request and converts it to a `string` type and returns it to the client. .

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController()
 */
class IndexController
{
    // Hyperf will automatically generate a `/index/index` route for this method, allows GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        // Transfer $id parameter fo a string, and return $id to client with Content-Type:plain/text
        return (string)$id;
    }
}
```

## Dependencies Auto-injection

This is a very powerful feature provided by `Hyperf` and is the foundation for maintaining the flexibility of the framework.
`Hyperf` provides two injection ways, one is common through the constructor injection, the other is through the `@Inject` annotation injection, below are the examples of the injection in two ways;
Suppose we have a `\App\Service\UserService` class. There is a `getInfoById(int $id)` method in the class by passing an `id` and eventually returning a user entity. Since the return value is not what we need to pay attention to here. , so do not elaborate too much, we should pay attention to get `UserService` in any class and call the methods of class, the general method is to instantiate the service class through `new UserService()`, but in Hyperf`, we have a better solution.

### Injection by constructor

Simply declare the type of the parameter within the constructor's argument, and `Hyperf` will automatically inject the corresponding object or value.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use App\Service\UserService;

/**
 * @AutoController()
 */
class IndexController
{
    /**
     * @var UserService
     */
    private $userService;
    
    // Simply declare the type of the parameter within the constructor's argument, and `Hyperf` will automatically inject the corresponding object or value.
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

### Injection by `@Inject` annotation

Simply declare the type of the parameter with the corresponding class property via `@var` and use the `@Inject` annotation to mark the property. `Hyperf` will automatically inject the corresponding object or value.

> using `@Inject` annotation should `use Hyperf\Di\Annotation\Inject;` namespace；  

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Di\Annotation\Inject;
use App\Service\UserService;

/**
 * @AutoController()
 */
class IndexController
{
    /**
     * @Inject()
     * @var UserService
     */
    private $userService;
    
    // /index/info
    public function info(RequestInterface $request)
    {
        $id = $request->input('id', 1);
        return $this->userService->getInfoById((int)$id);
    }
}
```
   
Through the above example, we could easily see that `$userService` is not instantiated manutally, but the class object corresponding to the property is automatically injected.
However, this case here does not really shows the benefits of dependencies Auto-injection and its power. We assume that there are many dependencies on `UserService`, and that these dependencies have many other dependencies at the same time, so that the class needs to instantiate many objects manually and adjust the corresponding the order of arguments. But in `Hyperf`, we don't need to manually manage these dependencies, just declare the class name of the arguments, we will finished the other works.
When `UserService` needs to undergo a drastic internal change such as replacement, such as replacing a local service with an RPC remote service, it only needs to adjust the class corresponding to the key value of `UserService` in the dependency to the new RPC service.

## Start server

Since `Hyperf` has a built-in coroutine server, it means that `Hyperf` will run as `CLI`, so after defining the routing and writing the actual logic code, we need to run it in root directory of the project and execute the command line `php bin/hyperf.php start` to start the server.
When the `Console` shows that the server is started, you can initiate access to the server through `cURL` or the browser. By default, the url of above example is  `http://127.0.0.1:9501/index/info?id= 1`.

## Reload the code

Since `Hyperf` is a persistent `CLI` application, it means that once the process is started, the parsed `PHP` code will be persisted in the process, which means that you cannot modify the `PHP` code after the server started. The code will not be changed in the started server. If you want the server to reload your code, you need to terminate the server by typing `CTRL + C` in the `Console`, and then re-execute the start command to reload.

> Tips: You can also configure the command to start the Server on the IDE, and you can quickly complete the `Start Server` or `Restart Server` operations directly via the IDE's `Start/Stop` buttons.
