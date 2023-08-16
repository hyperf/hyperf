# 響應

在 Hyperf 裡可透過 `Hyperf\HttpServer\Contract\ResponseInterface` 介面類來注入 `Response` 代理物件對響應進行處理，預設返回 `Hyperf\HttpServer\Response` 物件，該物件可直接呼叫所有 `Psr\Http\Message\ResponseInterface` 的方法。

> 注意 PSR-7 標準為 響應(Response) 進行了 immutable 機制 的設計，所有以 with 開頭的方法的返回值都是一個新物件，不會修改原物件的值

## 返回 Json 格式

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `json($data)` 方法用於快速返回 `Json` 格式，並設定 `Content-Type` 為 `application/json`，`$data` 接受一個數組或為一個實現了 `Hyperf\Contract\Arrayable` 介面的物件。

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

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `xml($data)` 方法用於快速返回 `XML` 格式，並設定 `Content-Type` 為 `application/xml`，`$data` 接受一個數組或為一個實現了 `Hyperf\Contract\Xmlable` 介面的物件。

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

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `raw($data)` 方法用於快速返回 `raw` 格式，並設定 `Content-Type` 為 `plain/text`，`$data` 接受一個字串或一個實現了 `__toString()` 方法的物件。

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

## 返回檢視

請參考 [檢視](zh-tw/view.md) 部分文件

## 重定向

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `redirect(string $toUrl, int $status = 302, string $schema = 'http')` 返回一個已設定重定向狀態的 `Psr7ResponseInterface` 物件。

`redirect` 方法：   

|  引數  |  型別  | 預設值 |                                                      備註                                                      |
|:------:|:------:|:------:|:--------------------------------------------------------------------------------------------------------------:|
| toUrl  | string |   無   | 如果引數不存在 `http://` 或 `https://` 則根據當前服務的 Host 自動拼接對應的 URL，且根據 `$schema` 引數拼接協議 |
| status |  int   |  302   |                                                   響應狀態碼                                                   |
| schema | string |  http  |                 當 `$toUrl` 不存在 `http://` 或 `https://` 時生效，僅可傳遞 `http` 或 `https`                  |

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function redirect(ResponseInterface $response): Psr7ResponseInterface
    {
        // redirect() 方法返回的是一個 Psr\Http\Message\ResponseInterface 物件，需再 return 回去
        return $response->redirect('/anotherUrl');
    }
}
```

## Cookie 設定

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

## 分塊傳輸編碼 Chunk

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `write(string $data)` 用於分段向瀏覽器傳送相應內容，並設定 `Transfer-Encoding` 為 `chunked`，`$data` 接受一個字串或一個實現了 `__toString()` 方法的物件。

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    public function index(ResponseInterface $response)
    {
        for ($i=0; $i<10; $i++) {
            $response->write((string) $i);
        }

        return 'Hello Hyperf';
    }
}
```

!> 注意：在呼叫 `write` 分段傳送資料後，如果再次使用 `return` 返回資料，此時的資料不會正常返回。即上文的例子中不會輸出 `Hello Hyperf`，只會輸出 `0123456789`。

## 檔案下載

`Hyperf\HttpServer\Contract\ResponseInterface` 提供了 `download(string $file, string $name = '')` 返回一個已設定下載檔案狀態的 `Psr7ResponseInterface` 物件。

如果請求中帶有 `if-match` 或 `if-none-match` 的請求頭，Hyperf 也會根據協議標準與 `ETag` 進行比較，如果一致則會返回一個 `304` 狀態碼的響應。

`download` 方法：   

| 引數 |  型別  | 預設值 |                                備註                                 |
|:----:|:------:|:------:|:-------------------------------------------------------------------:|
| file | string |   無   | 要返回下載檔案的絕對路徑，同透過 BASE_PATH 常量來定位到專案的根目錄 |
| name | string |   無   |         客戶端下載檔案的檔名，為空則會使用下載檔案的原名          |


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
