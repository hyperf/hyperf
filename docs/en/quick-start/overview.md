# QuickStart

As an example of how to use `Hyperf`, this page will `create an HTTP Server` to implement a simple `Web Service` by defining routes and controllers. Hyperf can do much more, but features like service governance, `gRPC` services, annotations programming, `AOP`, and other features will be explained in specific chapters.

## Defining a route

`Hyperf` uses [nikic/fast-route](https://github.com/nikic/FastRoute) as the default routing component, so you can easily define your routes in `config/routes.php`. `Hyperf` also provides an extremely powerful and convenient `Annotation Routing` feature.

For more information on routing outside of the examples shown below, please refer to the [Router](en/router.md) chapter.

### Define routes via file configuration

The routes file is located in `config/routes.php` in the [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project. Below are some common usage examples:

```php
<?php
use Hyperf\HttpServer\Router\Router;

// The code example here provides three different binding definitions for each example. In practice, you only need to define one of them.

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

### Define routes via annotations

`Hyperf` provides an [Annotations](en/annotation.md) feature which makes it fast and easy to define routes. Hyperf provides `@Controller` and `@AutoController` annotations for use in a `Controller` class. For in-depth instructions, please refer to the [Routing](en/router.md) chapter. Here are some quick examples:

### Define routes via `@AutoController`

`@AutoController` provides automatic routing bindings for most simple routing scenarios. When using `@AutoController`, `Hyperf` will automatically parse all `public` methods of the class and provide `GET` and `POST` requests for each of those methods.

> `@AutoController` annotations require the namespace `use Hyperf\HttpServer\Annotation\AutoController;`

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
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        return (string)$id;
    }
}
```

### Define routes via `@Controller`

For more flexible routing definitions, `@Controller` can be used instead of `@AutoController`. Using a `@Controller` annotation in a class makes it a `Controller class`, and the `@RequestMapping` annotation can be used to define the request methods and paths.

`Hyperf` also provides a variety of quick and convenient `Mapping annotations`, such as `@GetMapping`, `@PostMapping`, `@PutMapping`, `@PatchMapping`, `@DeleteMapping`, which can replace `@RequestMapping` to save you time when a route only needs a single HTTP method.

> `@Controller` annotations require the namespace `use Hyperf\HttpServer\Annotation\Controller;`
> `@RequestMapping` annotations require the namespace `use Hyperf\HttpServer\Annotation\RequestMapping;` 
> `@GetMapping` annotations require the namespace `use Hyperf\HttpServer\Annotation\GetMapping;`  
> `@PostMapping` annotations require the namespace `use Hyperf\HttpServer\Annotation\PostMapping;` 
> `@PutMapping` annotations require the namespace `use Hyperf\HttpServer\Annotation\PutMapping;`  
> `@PatchMapping` annotations require the namespace `use Hyperf\HttpServer\Annotation\PatchMapping;`
> `@DeleteMapping` annotations require the namespace `use Hyperf\HttpServer\Annotation\DeleteMapping;`

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
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
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


## Handle HTTP Requests

`Hyperf` is unopinionated. There is no requirement for you to implement HTTP request processing using any specific format. You can use the traditional `MVC mode` or the `RequestHandler mode` to handle requests. Let's take `MVC mode` as an example:

Create a `Controller` folder in the `app` folder and create a `IndexController.php` file. The `index` method gets the `id` parameter from the request, converts it to a `string` type and returns it to the client.

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
    // Hyperf will automatically generate a `/index/index` route for this method, allowing GET or POST requests
    public function index(RequestInterface $request)
    {
        // Retrieve the id parameter from the request
        $id = $request->input('id', 1);
        // Transfer $id parameter to a string, and return $id to the client with Content-Type:plain/text
        return (string)$id;
    }
}
```

## Dependency Auto-Injection

Dependency injection is a very powerful feature provided by `Hyperf` and is the foundation for the flexibility of the framework.

`Hyperf` provides two methods of injection, one is through constructor injection, the other is through the `@Inject` annotation injection, below are examples for both methods:

Suppose we have an `\App\Service\UserService` class. There is a `getInfoById(int $id)` method in the class that takes an `id` argument and returns a user entity. The return type and internals aren't relevant to this documentation, so we won't pay them too much attention, what we want is to get `UserService` in our class and to use the methods of that class. The normal method is to instantiate the `UserService` class through `new UserService()`, but in `Hyperf` using dependency injection, we have a better solution.

### Injection via constructor

Declare the parameter type within the constructor's arguments, and `Hyperf` will automatically inject the corresponding object or value.

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
    
    // Declare the parameter type within the constructor's arguments, and Hyperf will automatically inject the corresponding object or value.
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

### Injection via `@Inject` annotation

Declare the parameter type above the corresponding class property via `@var` and use the `@Inject` annotation. `Hyperf` will automatically inject the corresponding object or value.

> `@Inject` annotations require the namespace `use Hyperf\Di\Annotation\Inject;`

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
   
In the above example, we can easily see that `$userService` is not instantiated manually, but the class object corresponding to the property is automatically injected by `Hyperf`.

However, this case does not really show the real power of dependency injection. We assume that `UserService` has its own depencencies, and that those dependencies have many other dependencies as well, so that any class you define needs to instantiate many objects manually and manage the order of each class's arguments. In `Hyperf`, we don't need to manually manage these dependencies, just declare the class name of the arguments we need, and `Hyperf` does all the work for us.

When `UserService` needs to undergo a drastic internal change such as replacing a local service with an RPC remote service, we only needs to adjust the class definition of `UserService.php` to replace the old service with the new RPC service in a single file.

## Start the server

Since `Hyperf` has a built-in coroutine server, `Hyperf` will run as a `CLI` process. After defining our routes and writing the application logic code, we can start the server by entering the root directory of the project and executing the command  `php bin/hyperf.php start`.

When the `console` shows that the server has started, you can access the server through `cURL` or via the browser. By default, the url of the above dependency injection examples is  `http://127.0.0.1:9501/index/info?id= 1`.

## Reload the code

`Hyperf` is a persistent `CLI` application. Once the process starts, the parsed `PHP` code will remain unchanged while the process is running, so changes to the `PHP` code after the server starts will have no effect. If you want the server to reload your code, you need to terminate the process by typing `CTRL + C` in the `console` and then re-execute the command `php bin/hyperf.php start`.

> Tip: You can also configure the commands to manage the Server in your IDE, and you can quickly execute the `Start the Server` or `Reload the code` operations directly via the IDE's `Start/Stop` buttons.
