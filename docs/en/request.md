# Request object

`Request object (Request)` is completely implemented based on the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard and is implemented by [hyperf/http-message](https://github.com/hyperf/http-message).

> Note that the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard `Request (Request)` is designed with `immutable mechanism`, all methods starting with the `with` prefix return a new object and will not modify the value of the original object

## Installation

This component is completely independent and suitable for any framework project.

```bash
composer require hyperf/http-message
```

> If used in other framework projects, only the API provided by PSR-7 is supported. For details, you can refer directly to the relevant specifications of PSR-7. The usage described in this document is limited to usage when using Hyperf.

## Get the request object

You can inject `Hyperf\HttpServer\Contract\RequestInterface` through the container to obtain the corresponding `Hyperf\HttpServer\Request`. The actual injected object is a proxy object implementing `PSR-7 request object (Request)` for each request, which means that this object can only be obtained during the life cycle of `onRequest`. The following is an example of how to obtain the request object:

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

### Dependency injection and parameters

If you want to obtain routing parameters through controller method parameters, you can list the corresponding parameters after the dependencies, and the framework will automatically inject the corresponding parameters into the method parameters. For example, if your route is defined as follows:

```php
// Route definition using annotation method
#[GetMapping(path: "/user/{id:\d+}")]

// Route definition using configuration method
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET','HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

Then you can get the `query` parameter `id` by declaring the `$id` parameter on the method parameter, as shown below:

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

In addition to obtaining route parameters through dependency injection, you can also obtain route parameters through the `route` method of the request object, as shown below:

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
        // Returns the route parameter id if defined or null if the value is missing
        $id = $request->route('id');

        // Returns the route parameter id if defined or 0 if the value is missing
        $id = $request->route('id', 0);
        // ...
    }
}
```

### Request path & method

In addition to using the `APIs` defined by the [PSR-7](https://www.php-fig.org/psr/psr-7/) standard `Hyperf\HttpServer\Contract\RequestInterface`, the request object also provides a variety of methods for accessing request data. Below is a list of some examples of methods:

#### Get the request path

The `path()` method returns the requested path information. In other words, if the destination address of the incoming request is `http://domain.com/foo/bar?baz=1`, then `path()` will return `foo/bar`:

```php
$uri = $request->path();
```

The `is(...$patterns)` method can verify whether the incoming request path matches the specified rule. When using this method, you can also pass a `*` character as a wildcard:

```php
if ($request->is('user/*')) {
    // ...
}
```

#### Get the requested URL

You can use the `url()` or `fullUrl()` method to get the full `URL` of the incoming request. The `url()` method returns the `URL` without the `query parameters`, and the return value of the `fullUrl()` method contains the `query parameters`:

```php
// No query parameters
$url = $request->url();

// With query parameters
$url = $request->fullUrl();
```

#### Get request method

The `getMethod()` method will return the request method of `HTTP`. You can also use the `isMethod(string $method)` method to verify whether the request method of `HTTP` matches the specified rules:

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### PSR-7 request and method

The message component [hyperf/http-message](https://github.com/hyperf/http-message) itself is an implementation of [PSR-7](https://www.php-fig.org/psr/psr-7/) standard components and the interface methods can be called through the injected request object (Request).
If the request is declared as `Psr\Http\Message\ServerRequestInterface` [PSR-7](https://www.php-fig.org/psr/psr-7/) standard  interface during injection, the framework will automatically convert to the equivalent `Hyperf\HttpServer\Request` object that implements `Hyperf\HttpServer\Contract\RequestInterface`.

> It is recommended to use `Hyperf\HttpServer\Contract\RequestInterface` for injection so that you can get the IDE's auto-completion reminder support for exclusive methods.

## Input preprocessing & normalization

## Get input

### Get all input

You can use the `all()` method to get all the input data in the form of an `array`:

```php
$all = $request->all();
```

### Get the specified input value

Use `input(string $key, $default = null)` and `inputs(array $keys, $default = null): array` to obtain `one` or `multiple` input values of any form:

```php
// Returns the input value if it exists or null if it doesn't exist
$name = $request->input('name');

// Return the input value if it exists or the default value of 'Hyperf' if it doesn't exist
$name = $request->input('name','Hyperf');
```

If the transmission form data contains data in the form of an array, you can use the dot syntax to get a naster value from the array:

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```
### Get input from query string

Use the `input` or `inputs` method to get the input data from the entire request (including the `query parameters`), and the `query(?string $key = null, $default = null)` method to get input only from the query string:

```php
// Return the query parameter if it exists, return null if it doesn't exist
$name = $request->query('name');

// Return the query parameter if it exists, return default value of 'Hyperf' if it doesn't exist
$name = $request->query('name','Hyperf');

// If no parameters are passed, all query parameters are returned as an associative array
$name = $request->query();
```

### Get `JSON` input information

If the request `body` data format is `JSON`, as long as the `Content-Type` header value of the `Request object (Request)` is set correctly to `application/json`, you can use the `input(string $key , $default = null)` method to access the `JSON` data and you can even use the dot syntax to read the `JSON` array:

```php
// Return value or null if it does not exist
$name = $request->input('user.name');

// Return value or default value of 'Hyperf' if it does not exist
$name = $request->input('user.name','Hyperf');

// Return all Json data as an array
$name = $request->all();
```

### Determine if input value exists

To determine whether a value exists in the request, you can use the `has($keys)` method. If the value exists in the request, it will return `true`, if it does not exist, it will return `false`. The first parameter can be either a string or an array containing multiple strings. In the latter case, the method will return `true` only if all of the keys exist:

```php
// Only judge a single value
if ($request->has('name')) {
    // ...
}

// Judge multiple values at the same time
if ($request->has(['name','email'])) {
    // ...
}
```

## Cookies

### Get Cookies from the request

Use the `getCookieParams()` method to get all the `Cookies` from the request as an associative array.

```php
$cookies = $request->getCookieParams();
```

You can use the `cookie(string $key, $default = null)` method to get the value of the corresponding cookie:

 ```php
// Return value if the cookie exists or return null if it doesn't exist
$name = $request->cookie('name');

// Return value if the cookie exists or return a default value of 'Hyperf' if it doesn't exist
$name = $request->cookie('name','Hyperf');
 ```

## File

### Get uploaded files

You can use the `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` method to get the uploaded file object from the request. If the uploaded file exists, this method returns an instance of `Hyperf\HttpMessage\Upload\UploadedFile` class, which inherits the `SplFileInfo` class of `PHP` and also provides various methods for interacting with the file:

```php
// Returns a Hyperf\HttpMessage\Upload\UploadedFile object if the file exists, or null if it does not exist
$file = $request->file('photo');
```

### Check if the file exists

You can use the `hasFile(string $key): bool` method to confirm whether there is a file in the request:

```php
if ($request->hasFile('photo')) {
    // ...
}
```

### Verify successful upload

In addition to checking whether the uploaded file exists, you can also verify whether the uploaded file is valid through the `isValid(): bool` method:

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

### File path & extension

The `UploadedFile` class also contains methods for accessing the full path of the file and its extension. The `getExtension()` method will determine the extension of the file based on the content of the file. The extension may be different from the extension provided by the client:

```php
// The path is the temporary path of the uploaded file
$path = $request->file('photo')->getPath();

// Since the tmp_name of the uploaded file by Swoole does not retain the original file name, this method has been rewritten to obtain the suffix of the original file name
$extension = $request->file('photo')->getExtension();
```

### Store uploaded files

The uploaded file is stored in a temporary location before it is manually stored. If you do not store the file, it will be removed from the temporary location after the request is completed. Use `moveTo(string $targetPath): void` to move temporary files to the location of `$targetPath` for persistent storage. The code example is as follows:

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// Determine whether the method has moved through the isMoved(): bool method
if ($file->isMoved()) {
    // ...
}
```
