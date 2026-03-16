# 响应

在 Hyperf 里可通过 `Hyperf\HttpServer\Contract\ResponseInterface` 接口类来注入 `Response` 代理对象对响应进行处理，默认返回 `Hyperf\HttpServer\Response` 对象，该对象可直接调用所有 `Psr\Http\Message\ResponseInterface` 的方法。

> 注意 PSR-7 标准为 响应(Response) 进行了 immutable 机制 的设计，所有以 with 开头的方法的返回值都是一个新对象，不会修改原对象的值

## 返回 Json 格式

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `json($data)` 方法用于快速返回 `Json` 格式，并设置 `Content-Type` 为 `application/json`，`$data` 接受一个数组或为一个实现了 `Hyperf\Contract\Arrayable` 接口的对象。

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

## 返回 Xml 格式

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `xml($data)` 方法用于快速返回 `XML` 格式，并设置 `Content-Type` 为 `application/xml`，`$data` 接受一个数组或为一个实现了 `Hyperf\Contract\Xmlable` 接口的对象。

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

## 返回 Raw 格式

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `raw($data)` 方法用于快速返回 `raw` 格式，并设置 `Content-Type` 为 `plain/text`，`$data` 接受一个字符串或一个实现了 `__toString()` 方法的对象。

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

## 返回视图

请参考 [视图](zh-cn/view.md) 部分文档

## 重定向

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `redirect(string $toUrl, int $status = 302, string $schema = 'http')` 返回一个已设置重定向状态的 `Psr7ResponseInterface` 对象。

`redirect` 方法：   

|  参数  |  类型  | 默认值 |                                                      备注                                                      |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl  | string |   无   | 如果参数不存在 `http://` 或 `https://` 则根据当前服务的 Host 自动拼接对应的 URL，且根据 `$schema` 参数拼接协议 |
| status |  int   |  302   |                                                   响应状态码                                                   |
| schema | string |  http  |                 当 `$toUrl` 不存在 `http://` 或 `https://` 时生效，仅可传递 `http` 或 `https`                  |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // redirect() 方法返回的是一个 Psr\Http\Message\ResponseInterface 对象，需再 return 回去
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie 设置

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

## 分块传输编码 Chunk

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `write(string $data)` 用于分段向浏览器发送相应内容，并设置 `Transfer-Encoding` 为 `chunked`，`$data` 接受一个字符串或一个实现了 `__toString()` 方法的对象。

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
            ->withHeader('X-Event-Mode', 'Enabled') // ⭐ 自定义 Header
            ->withHeader('X-Stream-Time', '5s');
        $streamer = new EventStream($this->response->getConnection(), $response);
        $startTime = time();
        $totalSteps = 5;
        $streamer->write("data: --- 🚀 EventStream 开始 (共 {$totalSteps} 步) ---\n\n");
        for ($i = 1; $i <= $totalSteps; ++$i) {
            Coroutine::sleep(1);
            $elapsed = time() - $startTime;
            $message = "data: 【第 {$i} 秒】数据块发送完成。已耗时: {$elapsed} 秒\n\n";
            $streamer->write($message);
        }
        $streamer->write("data: --- ✅ EventStream 结束 ---\n\n");
        $streamer->end();

        return 'Hello Hyperf';
    }
}
```

!> 注意：在调用 `write` 分段发送数据后，如果再次使用 `return` 返回数据，此时的数据不会正常返回。即上文的例子中不会输出 `Hello Hyperf`，只会输出 `data: 【第 {$i} 秒】数据块发送完成。已耗时: {$elapsed} 秒\n\n`。

## 文件下载

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `download(string $file, string $name = '')` 返回一个已设置下载文件状态的 `Psr7ResponseInterface` 对象。

如果请求中带有 `if-match` 或 `if-none-match` 的请求头，Hyperf 也会根据协议标准与 `ETag` 进行比较，如果一致则会返回一个 `304` 状态码的响应。

`download` 方法：   

| 参数 |  类型  | 默认值 |                                备注                                 |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string |   无   | 要返回下载文件的绝对路径，同通过 BASE_PATH 常量来定位到项目的根目录 |
| name | string |   无   |         客户端下载文件的文件名，为空则会使用下载文件的原名          |


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
