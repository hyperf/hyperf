# 請求物件

`請求物件(Request)` 是完全基於 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準實現的，由 [hyperf/http-message](https://github.com/hyperf/http-message) 元件提供實現支援。

> 注意 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準為 `請求(Request)` 進行了 `immutable 機制` 的設計，所有以 `with` 開頭的方法的返回值都是一個新物件，不會修改原物件的值

## 安裝

該元件完全獨立，適用於任何一個框架專案。

```bash
composer require hyperf/http-message
```

> 如用於其它框架專案則僅支援 PSR-7 提供的 API，具體可直接查閱 PSR-7 的相關規範，該文件所描述的使用方式僅限於使用 Hyperf 時的用法。

## 獲得請求物件

可以透過容器注入 `Hyperf\HttpServer\Contract\RequestInterface` 獲得 對應的 `Hyperf\HttpServer\Request`，實際注入的物件為一個代理物件，代理的物件為每個請求的 `PSR-7 請求物件(Request)`，也就意味著僅可在 `onRequest` 生命週期內可獲得此物件，下面是一個獲取示例：

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

### 依賴注入與引數

如果希望透過控制器方法引數獲取路由引數，可以在依賴項之後列出對應的引數，框架會自動將對應的引數注入到方法引數內，比如您的路由是這樣定義的：

```php
// 註解方式
#[GetMapping(path: "/user/{id:\d+}")]
// 配置方式
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

則可以透過在方法引數上宣告 `$id` 引數獲得 `Query` 引數 `id`，如下所示：

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

除了可以透過依賴注入獲取路由引數，還可以透過 `route` 方法獲取，如下所示：

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
        // 存在則返回，不存在則返回預設值 null
        $id = $request->route('id');
        // 存在則返回，不存在則返回預設值 0
        $id = $request->route('id', 0);
        // ...
    }
}
```

### 請求路徑 & 方法

`Hyperf\HttpServer\Contract\RequestInterface` 除了使用 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準定義的 `APIs` 之外，還提供了多種方法來檢查請求，下面我們提供一些方法的示例：

#### 獲取請求路徑

`path()` 方法返回請求的路徑資訊。也就是說，如果傳入的請求的目標地址是 `http://domain.com/foo/bar?baz=1`，那麼 `path()` 將會返回 `foo/bar`：

```php
$uri = $request->path();
```

`is(...$patterns)` 方法可以驗證傳入的請求路徑和指定規則是否匹配。使用這個方法的時，你也可以傳遞一個 `*` 字元作為萬用字元：

```php
if ($request->is('user/*')) {
    // ...
}
```

#### 獲取請求的 URL

你可以使用 `url()` 或 `fullUrl()` 方法去獲取傳入請求的完整 `URL`。`url()` 方法返回不帶有 `Query 引數` 的 `URL`，而 `fullUrl()` 方法的返回值包含 `Query 引數` ：

```php
// 沒有查詢引數
$url = $request->url();

// 帶上查詢引數
$url = $request->fullUrl();
```

#### 獲取請求方法

`getMethod()` 方法將返回 `HTTP` 的請求方法。你也可以使用 `isMethod(string $method)` 方法去驗證 `HTTP` 的請求方法與指定規則是否匹配：

```php
$method = $request->getMethod();

if ($request->isMethod('post')) {
    // ...
}
```

### PSR-7 請求及方法

[hyperf/http-message](https://github.com/hyperf/http-message) 元件本身是一個實現了 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準的元件，相關方法都可以透過注入的 `請求物件(Request)` 來呼叫。   
如果注入時宣告為 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準的 `Psr\Http\Message\ServerRequestInterface` 介面，則框架會自動轉換為等同於 `Hyperf\HttpServer\Contract\RequestInterface` 的 `Hyperf\HttpServer\Request` 物件。   

> 建議使用 `Hyperf\HttpServer\Contract\RequestInterface` 來注入，這樣可獲得 IDE 對專屬方法的自動完成提醒支援。

## 輸入預處理 & 規範化

### 獲取輸入

#### 獲取所有輸入

您可以使用 `all()` 方法以 `陣列` 形式獲取到所有輸入資料:

```php
$all = $request->all();
```

#### 獲取指定輸入值

透過 `input(string $key, $default = null)` 和 `inputs(array $keys, $default = null): array` 獲取 `一個` 或 `多個` 任意形式的輸入值：

```php
// 存在則返回，不存在則返回 null
$name = $request->input('name');
// 存在則返回，不存在則返回預設值 Hyperf
$name = $request->input('name', 'Hyperf');
```

如果傳輸表單資料中包含「陣列」形式的資料，那麼可以使用「點」語法來獲取陣列：

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```
#### 從查詢字串獲取輸入

使用 `input`, `inputs` 方法可以從整個請求中獲取輸入資料（包括 `Query 引數`），而 `query(?string $key = null, $default = null)` 方法可以只從查詢字串中獲取輸入資料：

```php
// 存在則返回，不存在則返回 null
$name = $request->query('name');
// 存在則返回，不存在則返回預設值 Hyperf
$name = $request->query('name', 'Hyperf');
// 不傳遞引數則以關聯陣列的形式返回所有 Query 引數
$name = $request->query();
```

#### 獲取 `JSON` 輸入資訊

如果請求的 `Body` 資料格式是 `JSON`，則只要 `請求物件(Request)` 的 `Content-Type` `Header 值` 正確設定為 `application/json`，就可以透過  `input(string $key, $default = null)` 方法訪問 `JSON` 資料，你甚至可以使用 「點」語法來讀取 `JSON` 陣列：

```php
// 存在則返回，不存在則返回 null
$name = $request->input('user.name');
// 存在則返回，不存在則返回預設值 Hyperf
$name = $request->input('user.name', 'Hyperf');
// 以陣列形式返回所有 Json 資料
$name = $request->all();
```

#### 確定是否存在輸入值

要判斷請求是否存在某個值，可以使用 `has($keys)` 方法。如果請求中存在該值則返回 `true`，不存在則返回 `false`，`$keys` 可以傳遞一個字串，或傳遞一個數組包含多個字串，只有全部存在才會返回 `true`：

```php
// 僅判斷單個值
if ($request->has('name')) {
    // ...
}
// 同時判斷多個值
if ($request->has(['name', 'email'])) {
    // ...
}
```

### Cookies

#### 從請求中獲取 Cookies

使用 `getCookieParams()` 方法從請求中獲取所有的 `Cookies`，結果會返回一個關聯陣列。

```php
$cookies = $request->getCookieParams();
```

如果希望獲取某一個 `Cookie` 值，可透過 `cookie(string $key, $default = null)` 方法來獲取對應的值：

 ```php
// 存在則返回，不存在則返回 null
$name = $request->cookie('name');
// 存在則返回，不存在則返回預設值 Hyperf
$name = $request->cookie('name', 'Hyperf');
 ```

### 檔案

#### 獲取上傳檔案

你可以使用 `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` 方法從請求中獲取上傳的檔案物件。如果上傳的檔案存在則該方法返回一個 `Hyperf\HttpMessage\Upload\UploadedFile` 類的例項，該類繼承了 `PHP` 的 `SplFileInfo` 類的同時也提供了各種與檔案互動的方法：

```php
// 存在則返回一個 Hyperf\HttpMessage\Upload\UploadedFile 物件，不存在則返回 null
$file = $request->file('photo');
```

#### 檢查檔案是否存在

您可以使用 `hasFile(string $key): bool` 方法確認請求中是否存在檔案：

```php
if ($request->hasFile('photo')) {
    // ...
}
```

#### 驗證成功上傳

除了檢查上傳的檔案是否存在外，您也可以透過 `isValid(): bool` 方法驗證上傳的檔案是否有效：

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

#### 檔案路徑 & 副檔名

`UploadedFile` 類還包含訪問檔案的完整路徑及其副檔名方法。`getExtension()` 方法會根據檔案內容判斷檔案的副檔名。該副檔名可能會和客戶端提供的副檔名不同：

```php
// 該路徑為上傳檔案的臨時路徑
$path = $request->file('photo')->getPath();

// 由於 Swoole 上傳檔案的 tmp_name 並沒有保持檔案原名，所以這個方法已重寫為獲取原檔名的字尾名
$extension = $request->file('photo')->getExtension();
```

#### 儲存上傳檔案

上傳的檔案在未手動儲存之前，都是存在一個臨時位置上的，如果您沒有對該檔案進行儲存處理，則在請求結束後會從臨時位置上移除，所以我們可能需要對檔案進行持久化儲存處理，透過 `moveTo(string $targetPath): void` 將臨時檔案移動到 `$targetPath` 位置持久化儲存，程式碼示例如下：

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// 透過 isMoved(): bool 方法判斷方法是否已移動
if ($file->isMoved()) {
    // ...
}
```


## 相關事件

當我們在服務配置中，開啟 `enable_request_lifecycle`，則每次請求進來，都可以觸發以下三個事件分別是

### 配置例項

> 以下刪除其他不相干程式碼

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

### 事件列表

- Hyperf\HttpServer\Event\RequestReceived

接收到請求時，會觸發此事件

- Hyperf\HttpServer\Event\RequestHandled

請求處理完畢時，會觸發此事件

- Hyperf\HttpServer\Event\RequestTerminated

當前請求的承載協程銷燬時，會觸發此事件
