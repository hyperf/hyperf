# Response

In Hyperf, you can inject the `Response` proxy object to handle responses through the `Hyperf\HttpServer\Contract\ResponseInterface` interface class. By default, it returns a `Hyperf\HttpServer\Response` object, and this object can directly call all methods of `Psr\Http\Message\ResponseInterface`.

> Note: The PSR-7 standard is designed with an `immutable mechanism` for Response. The return values of all methods starting with `with` are new objects and will not modify the value of the original object.

## Returning Json Format

The `Hyperf\HttpServer\Contract\ResponseInterface` provides the `json($data)` method for quickly returning in `Json` format and setting `Content-Type` to `application/json`. `$data` accepts an array or an object that implements the `Hyperf\Contract\Arrayable` interface.

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

## Returning Xml Format

The `Hyperf\HttpServer\Contract\ResponseInterface` provides the `xml($data)` method for quickly returning in `XML` format and setting `Content-Type` to `application/xml`. `$data` accepts an array or an object that implements the `Hyperf\Contract\Xmlable` interface.

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

## Returning Raw Format

The `Hyperf\HttpServer\Contract\ResponseInterface` provides the `raw($data)` method for quickly returning in `raw` format and setting `Content-Type` to `plain/text`. `$data` accepts a string or an object that implements the `__toString()` method.

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

## Returning View

Please refer to the [View](view.md) section of the documentation.

## Redirect

The `Hyperf\HttpServer\Contract\ResponseInterface` provides `redirect(string $toUrl, int $status = 302, string $schema = 'http')` to return a `Psr7ResponseInterface` object with the redirect status set.

`redirect` method:

| Parameter | Type | Default Value | Remarks |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl | string | None | If the parameter does not contain `http://` or `https://`, it will automatically splice the corresponding URL according to the Host of the current service, and splice the protocol according to the `$schema` parameter |
| status | int | 302 | Response status code |
| schema | string | http | Takes effect when `toUrl` does not contain `http://` or `https://`, only `http` or `https` can be passed |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // The redirect() method returns a Psr\Http\Message\ResponseInterface object, which needs to be returned again
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie Setting

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

## Chunked Transfer Encoding

The `Hyperf\HttpServer\Contract\ResponseInterface` provides `write(string $data)` for sending response content to the browser in segments and setting `Transfer-Encoding` to `chunked`. `$data` accepts a string or an object that implements the `__toString()` method.

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Swoole\Coroutine;
use Hyperf\Engine\Http\EventStream;

class IndexController
{
    public function index(ResponseInterface $response)
    {
       $response
            ->withStatus(200)
            ->withHeader('X-Event-Mode', 'Enabled') // ⭐ Custom Header
            ->withHeader('X-Stream-Time', '5s');
        $streamer = new EventStream($this->response->getConnection(), $response);
        $startTime = time();
        $totalSteps = 5;
        $streamer->write("data: --- 🚀 EventStream started (total {$totalSteps} steps) ---\n\n");
        for ($i = 1; $i <= $totalSteps; ++$i) {
            Coroutine::sleep(1);
            $elapsed = time() - $startTime;
            $message = "data: 【{$i} second】data block sent. Time elapsed: {$elapsed} seconds\n\n";
            $streamer->write($message);
        }
        $streamer->write("data: --- ✅ EventStream ended ---\n\n");
        $streamer->end();

        return 'Hello Hyperf';
    }
}
```

!> Note: After calling `write` to send data in segments, if you use `return` to return data again, the data will not be returned normally. That is, in the example above, `Hello Hyperf` will not be output, only `data: 【{$i} second】data block sent. Time elapsed: {$elapsed} seconds\n\n` will be output.

## File Download

The `Hyperf\HttpServer\Contract\ResponseInterface` provides `download(string $file, string $name = '')` to return a `Psr7ResponseInterface` object with the file download status set.

If the request contains the `if-match` or `if-none-match` request header, Hyperf will also compare it with `ETag` according to the protocol standard. If they match, a `304` status code response will be returned.

`download` method:

| Parameter | Type | Default Value | Remarks |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string | None | The absolute path to the file to be returned for download. Use the BASE_PATH constant to locate the project's root directory |
| name | string | None | The file name for the client to download. If empty, the original name of the downloaded file will be used |


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
