# Request Object

The `Request Object` is implemented entirely based on the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard, and its implementation is supported by the [hyperf/http-message](https://github.com/hyperf/http-message) component.

> Note: The [PSR-7](https://www.php-fig.org/psr/psr-7/) standard is designed with an `immutable mechanism` for `Request`. The return values of all methods starting with `with` are new objects and will not modify the value of the original object.

## Installation

This component is completely independent and suitable for any framework project.

```bash
composer require hyperf/http-message
```

> If used in other framework projects, only the APIs provided by PSR-7 are supported. Please refer to the relevant specifications of PSR-7 for details. The usage described in this document is limited to when using Hyperf.

## Obtaining the Request Object

You can obtain the corresponding `Hyperf\HttpServer\Request` by injecting `Hyperf\HttpServer\Contract\RequestInterface` through the container. The actual injected object is a proxy object, and the proxied object is the `PSR-7 Request Object` for each request. This means that this object can only be obtained within the `onRequest` lifecycle. Below is an example of obtaining it:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request)
    {
        // ...
    }
}
```

### Dependency Injection and Parameters

If you wish to obtain route parameters via controller method parameters, you can list the corresponding parameters after the dependencies. The framework will automatically inject the corresponding parameters into the method parameters. For example, if your route is defined as follows:

```php
// Annotation mode
#[GetMapping(path: "/user/{id:\d+}")]
// Configuration mode
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

You can obtain the `Query` parameter `id` by declaring the `$id` parameter in the method parameters, as shown below:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request, int $id)
    {
        // ...
    }
}
```

In addition to obtaining route parameters via dependency injection, you can also obtain them via the `route` method, as shown below:

```php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class IndexController
{
    public function info(RequestInterface $request)
    {
        // Returns if exists, otherwise returns default value null
        $id = $request->route('id');
        // Returns if exists, otherwise returns default value 0
        $id = $request->route('id', 0);
        // ...
    }
}
```

### Request Path & Method

In addition to using the `APIs` defined by the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard, `Hyperf\HttpServer\Contract\RequestInterface` also provides multiple methods to check the request. Below we provide examples of some methods:

#### Obtaining Request Path

The `path()` method returns the path information of the request. In other words, if the target address of the incoming request is `http://domain.com/foo/bar?baz=1`, then `path()` will return `foo/bar`:

```php
$uri = $request->path();
```

The `is(...$patterns)` method can verify if the incoming request path matches the specified rule. When using this method, you can also pass a `*` character as a wildcard:

```php
if ($request->is('user/*')) {
    // ...
}
```

#### Obtaining Request URL

You can use the `url()` or `fullUrl()` method to obtain the complete `URL` of the incoming request. The `url()` method returns the `URL` without `Query Parameters`, while the return value of the `fullUrl()` method contains `Query Parameters`:

```php
// No query parameters
$url = $request->url();

// With query parameters
$url = $request->fullUrl();
```

#### Obtaining Request Method

The `getMethod()` method will return the `HTTP` request method. You can also use the `isMethod(string $method)` method to verify if the `HTTP` request method matches the specified rule:

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### PSR-7 Request and Methods

The [hyperf/http-message](https://github.com/hyperf/http-message) component itself is a component that implements the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard. Related methods can be called via the injected `Request Object`.
If it is declared as the `Psr\Http\Message\ServerRequestInterface` interface of the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard during injection, the framework will automatically convert it to the `Hyperf\HttpServer\Request` object, which is equivalent to `Hyperf\HttpServer\Contract\RequestInterface`.

> It is recommended to use `Hyperf\HttpServer\Contract\RequestInterface` for injection, so that you can get the IDE's auto-completion reminder support for dedicated methods.

## Input Pre-processing & Normalization

### Obtaining Input

#### Obtaining All Inputs

You can use the `all()` method to obtain all input data in the form of an `array`:

```php
$all = $request->all();
```

#### Obtaining Specified Input Value

Obtain `one` or `more` input values in any form via `input(string $key, $default = null)` and `inputs(array $keys, $default = null): array`:

```php
// Returns if exists, otherwise returns null
$name = $request->input('name');
// Returns if exists, otherwise returns default value Hyperf
$name = $request->input('name', 'Hyperf');
```

If the transmitted form data contains data in the form of an「array」, then you can use「dot」syntax to obtain the array:

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```

#### Obtaining Input from Query String

Using the `input`, `inputs` methods can obtain input data (including `Query Parameters`) from the entire request, while the `query(?string $key = null, $default = null)` method can only obtain input data from the query string:

```php
// Returns if exists, otherwise returns null
$name = $request->query('name');
// Returns if exists, otherwise returns default value Hyperf
$name = $request->query('name', 'Hyperf');
// Returns all Query parameters in the form of an associative array if no parameters are passed
$name = $request->query();
```

#### Obtaining `JSON` Input Information

If the data format of the request `Body` is `JSON`, as long as the `Content-Type` `Header Value` of the `Request Object` is correctly set to `application/json`, you can access `JSON` data through the `input(string $key, $default = null)` method, and you can even use「dot」syntax to read `JSON` arrays:

```php
// Returns if exists, otherwise returns null
$name = $request->input('user.name');
// Returns if exists, otherwise returns default value Hyperf
$name = $request->input('user.name', 'Hyperf');
// Returns all Json data in the form of an array
$name = $request->all();
```

#### Determining Existence of Input Value

To determine whether a value exists in the request, you can use the `has($keys)` method. If the value exists in the request, it returns `true`, otherwise it returns `false`. `$keys` can be a string, or an array containing multiple strings. It will only return `true` if all of them exist:

```php
// Only judge a single value
if ($request->has('name')) {
    // ...
}
// Judge multiple values at the same time
if ($request->has(['name', 'email'])) {
    // ...
}
```

### Cookies

#### Obtaining Cookies from Request

Use the `getCookieParams()` method to obtain all `Cookies` from the request, which will return an associative array.

```php
$cookies = $request->getCookieParams();
```

If you wish to obtain a certain `Cookie` value, you can obtain the corresponding value through the `cookie(string $key, $default = null)` method:

 ```php
// Returns if exists, otherwise returns null
$name = $request->cookie('name');
// Returns if exists, otherwise returns default value Hyperf
$name = $request->cookie('name', 'Hyperf');
 ```

### Files

#### Obtaining Uploaded Files

You can use the `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` method to obtain the uploaded file object from the request. If the uploaded file exists, this method returns an instance of the `Hyperf\HttpMessage\Upload\UploadedFile` class. This class inherits `PHP`'s `SplFileInfo` class and also provides various methods to interact with the file:

```php
// Returns a Hyperf\HttpMessage\Upload\UploadedFile object if it exists, otherwise returns null
$file = $request->file('photo');
```

#### Checking if File Exists

You can use the `hasFile(string $key): bool` method to confirm whether a file exists in the request:

```php
if ($request->hasFile('photo')) {
    // ...
}
```

#### Verifying Successful Upload

In addition to checking whether the uploaded file exists, you can also verify whether the uploaded file is valid through the `isValid(): bool` method:

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

#### File Path & Extension

The `UploadedFile` class also contains methods for accessing the full path and extension of the file. The `getExtension()` method determines the extension of the file based on the file content. This extension may be different from the extension provided by the client:

```php
// This path is the temporary path of the uploaded file
$path = $request->file('photo')->getPath();

// Since Swoole's uploaded file tmp_name does not keep the original file name, this method has been rewritten to get the suffix of the original file name
$extension = $request->file('photo')->getExtension();
```

#### Storing Uploaded Files

Uploaded files exist in a temporary location before they are manually stored. If you do not perform storage processing on the file, it will be removed from the temporary location after the request ends. Therefore, we may need to perform persistent storage processing on the file. Persistently store the temporary file to the `$targetPath` location via `moveTo(string $targetPath): void`. The code example is as follows:

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// Determine whether the method has been moved via the isMoved(): bool method
if ($file->isMoved()) {
    // ...
}
```


## Related Events

When we turn on `enable_request_lifecycle` in the service configuration, each request that comes in can trigger the following three events, respectively:

### Configuration Instance

> The following deletes other irrelevant code

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;
use Hyperf\Server\ServerInterface;

return [
    'servers' => [
        [
            'name' => 'http',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
            'options' => [
                // Whether to enable request lifecycle event
                'enable_request_lifecycle' => false,
            ],
        ],
    ],
];
```

### Event List

- Hyperf\HttpServer\Event\RequestReceived

This event is triggered when a request is received.

- Hyperf\HttpServer\Event\RequestHandled

This event is triggered when the request is processed.

- Hyperf\HttpServer\Event\RequestTerminated

This event is triggered when the carrying coroutine of the current request is destroyed.
