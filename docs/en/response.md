# Response

In Hyperf, you could get the response proxy object by injected `Hyperf\HttpServer\Contract\ResponseInterface` interface, by default, DI container will return an `Hyperf\HttpServer\Response` object, you could directly call all methods of `Psr\Http\Message\ResponseInterface` via this object.

> Note that the standard PSR-7 response object is an immutable object. The return value of all methods starts with `with` is a new object and will not modify the value of the original object.

## Return JSON

You could return a `Json` format content quickly by method `json($data)` of `Hyperf\HttpServer\Contract\ResponseInterface`, and also the `Content-Type` of response object will be set to `application/json`, `$data` accept an array or an object that implemented `Hyperf\Contract\Arrayable` interface.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function json(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        return $response->json($data);
    }
}
```

## Return XML

You could return a `XML` format content quickly by method `xml($data)` of `Hyperf\HttpServer\Contract\ResponseInterface`, and also the `Content-Type` of response object will be set to `application/xml`, `$data` accept an array or an object that implemented `Hyperf\Contract\Xmlable` interface.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function xml(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        return $response->xml($data);
    }
}
```

## Return the raw content

You could return the raw content quickly by method `raw($data)` of `Hyperf\HttpServer\Contract\ResponseInterface`, and also the `Content-Type` of response object will be set to `plain/text`, `$data` accept a string or an object that implemented `__toString()` method.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function raw(ResponseInterface $response): Psr7ResponseInterface
    {
        return $response->raw('Hello Hyperf.');
    }
}
```

## Return view

Please refer to [View](zh-cn/view.md).

## Redirection

`Hyperf\HttpServer\Contract\ResponseInterface` provides `redirect(string $toUrl, int $status = 302, string $schema = 'http')` method to return an `Psr7ResponseInterface` object which has already setup redirection status.

`redirect`:   

|  Arguments  |  Type  | Default Value |                                                      Comment                                                      |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl  | string |   null   | If the argument does not starts with `http://` or `https://`, the corresponding URL will be automatically spliced according to the Host of the current server, and the splicing protocol according to the `$schema` argument |
| status |  int   |  302   |                                                   Status code of Response                                                   |
| schema | string |  http  |                 Effective when `$toUrl` does not starts with `http://` or `https://`, only `http` or `https` are available                |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // redirect() method will return an Psr\Http\Message\ResponseInterface object, needs to return the object.
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Hyperf\HttpMessage\Cookie\Cookie;

class IndexController
{
    public function cookie(ResponseInterface $response): Psr7ResponseInterface
    {
        $cookie = new Cookie('key', 'value');
        return $response->withCookie($cookie)->withContent('Hello Hyperf.');
    }
}
```

## Gzip Compression

## Chunk

## File Download

`Hyperf\HttpServer\Contract\ResponseInterface` provides `download(string $file, string $name = '')` method to return an `Psr7ResponseInterface` object which already setup the file download status.   
If the request contains `if-match` or `if-none-match` header, Hyperf will also compare it with the `ETag` according to the protocol standard, and if they match, it will return a response with a `304` status code.

`download`:   

| Arguments |  Type  | Default Value |                                Comment                                 |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string |   null   | To return to the absolute path of the downloaded file, use the `BASE_PATH` constant to locate the root directory of the project |
| name | string |   null   |         The file name of the client download file, if it is empty, the original name of the downloaded file will be used          |


```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function index(ResponseInterface $response): Psr7ResponseInterface
    {
        return $response->download(BASE_PATH . '/public/file.csv', 'filename.csv');
    }
}
```
