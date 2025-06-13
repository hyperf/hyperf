# 請求對象

`請求對象(Request)` 是完全基於 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準實現的，由 [hyperf/http-message](https://github.com/hyperf/http-message) 組件提供實現支持。

> 注意 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準為 `請求(Request)` 進行了 `immutable 機制` 的設計，所有以 `with` 開頭的方法的返回值都是一個新對象，不會修改原對象的值

## 安裝

該組件完全獨立，適用於任何一個框架項目。

```bash
composer require hyperf/http-message
```

> 如用於其它框架項目則僅支持 PSR-7 提供的 API，具體可直接查閲 PSR-7 的相關規範，該文檔所描述的使用方式僅限於使用 Hyperf 時的用法。

## 獲得請求對象

可以通過容器注入 `Hyperf\HttpServer\Contract\RequestInterface` 獲得 對應的 `Hyperf\HttpServer\Request`，實際注入的對象為一個代理對象，代理的對象為每個請求的 `PSR-7 請求對象(Request)`，也就意味着僅可在 `onRequest` 生命週期內可獲得此對象，下面是一個獲取示例：

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

### 依賴注入與參數

如果希望通過控制器方法參數獲取路由參數，可以在依賴項之後列出對應的參數，框架會自動將對應的參數注入到方法參數內，比如您的路由是這樣定義的：

```php
// 註解方式
#[GetMapping(path: "/user/{id:\d+}")]
// 配置方式
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'HEAD'], '/user/{id:\d+}', [\App\Controller\IndexController::class, 'user']);
```

則可以通過在方法參數上聲明 `$id` 參數獲得 `Query` 參數 `id`，如下所示：

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

除了可以通過依賴注入獲取路由參數，還可以通過 `route` 方法獲取，如下所示：

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
        // 存在則返回，不存在則返回默認值 null
        $id = $request->route('id');
        // 存在則返回，不存在則返回默認值 0
        $id = $request->route('id', 0);
        // ...
    }
}
```

### 請求路徑 & 方法

`Hyperf\HttpServer\Contract\RequestInterface` 除了使用 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準定義的 `APIs` 之外，還提供了多種方法來檢查請求，下面我們提供一些方法的示例：

#### 獲取請求路徑

`path()` 方法返回請求的路徑信息。也就是説，如果傳入的請求的目標地址是 `http://domain.com/foo/bar?baz=1`，那麼 `path()` 將會返回 `foo/bar`：

```php
$uri = $request->path();
```

`is(...$patterns)` 方法可以驗證傳入的請求路徑和指定規則是否匹配。使用這個方法的時，你也可以傳遞一個 `*` 字符作為通配符：

```php
if ($request->is('user/*')) {
    // ...
}
```

#### 獲取請求的 URL

你可以使用 `url()` 或 `fullUrl()` 方法去獲取傳入請求的完整 `URL`。`url()` 方法返回不帶有 `Query 參數` 的 `URL`，而 `fullUrl()` 方法的返回值包含 `Query 參數` ：

```php
// 沒有查詢參數
$url = $request->url();

// 帶上查詢參數
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

[hyperf/http-message](https://github.com/hyperf/http-message) 組件本身是一個實現了 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準的組件，相關方法都可以通過注入的 `請求對象(Request)` 來調用。   
如果注入時聲明為 [PSR-7](https://www.php-fig.org/psr/psr-7/) 標準的 `Psr\Http\Message\ServerRequestInterface` 接口，則框架會自動轉換為等同於 `Hyperf\HttpServer\Contract\RequestInterface` 的 `Hyperf\HttpServer\Request` 對象。   

> 建議使用 `Hyperf\HttpServer\Contract\RequestInterface` 來注入，這樣可獲得 IDE 對專屬方法的自動完成提醒支持。

## 輸入預處理 & 規範化

### 獲取輸入

#### 獲取所有輸入

您可以使用 `all()` 方法以 `數組` 形式獲取到所有輸入數據:

```php
$all = $request->all();
```

#### 獲取指定輸入值

通過 `input(string $key, $default = null)` 和 `inputs(array $keys, $default = null): array` 獲取 `一個` 或 `多個` 任意形式的輸入值：

```php
// 存在則返回，不存在則返回 null
$name = $request->input('name');
// 存在則返回，不存在則返回默認值 Hyperf
$name = $request->input('name', 'Hyperf');
```

如果傳輸表單數據中包含「數組」形式的數據，那麼可以使用「點」語法來獲取數組：

```php
$name = $request->input('products.0.name');

$names = $request->input('products.*.name');
```
#### 從查詢字符串獲取輸入

使用 `input`, `inputs` 方法可以從整個請求中獲取輸入數據（包括 `Query 參數`），而 `query(?string $key = null, $default = null)` 方法可以只從查詢字符串中獲取輸入數據：

```php
// 存在則返回，不存在則返回 null
$name = $request->query('name');
// 存在則返回，不存在則返回默認值 Hyperf
$name = $request->query('name', 'Hyperf');
// 不傳遞參數則以關聯數組的形式返回所有 Query 參數
$name = $request->query();
```

#### 獲取 `JSON` 輸入信息

如果請求的 `Body` 數據格式是 `JSON`，則只要 `請求對象(Request)` 的 `Content-Type` `Header 值` 正確設置為 `application/json`，就可以通過  `input(string $key, $default = null)` 方法訪問 `JSON` 數據，你甚至可以使用 「點」語法來讀取 `JSON` 數組：

```php
// 存在則返回，不存在則返回 null
$name = $request->input('user.name');
// 存在則返回，不存在則返回默認值 Hyperf
$name = $request->input('user.name', 'Hyperf');
// 以數組形式返回所有 Json 數據
$name = $request->all();
```

#### 確定是否存在輸入值

要判斷請求是否存在某個值，可以使用 `has($keys)` 方法。如果請求中存在該值則返回 `true`，不存在則返回 `false`，`$keys` 可以傳遞一個字符串，或傳遞一個數組包含多個字符串，只有全部存在才會返回 `true`：

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

使用 `getCookieParams()` 方法從請求中獲取所有的 `Cookies`，結果會返回一個關聯數組。

```php
$cookies = $request->getCookieParams();
```

如果希望獲取某一個 `Cookie` 值，可通過 `cookie(string $key, $default = null)` 方法來獲取對應的值：

 ```php
// 存在則返回，不存在則返回 null
$name = $request->cookie('name');
// 存在則返回，不存在則返回默認值 Hyperf
$name = $request->cookie('name', 'Hyperf');
 ```

### 文件

#### 獲取上傳文件

你可以使用 `file(string $key, $default): ?Hyperf\HttpMessage\Upload\UploadedFile` 方法從請求中獲取上傳的文件對象。如果上傳的文件存在則該方法返回一個 `Hyperf\HttpMessage\Upload\UploadedFile` 類的實例，該類繼承了 `PHP` 的 `SplFileInfo` 類的同時也提供了各種與文件交互的方法：

```php
// 存在則返回一個 Hyperf\HttpMessage\Upload\UploadedFile 對象，不存在則返回 null
$file = $request->file('photo');
```

#### 檢查文件是否存在

您可以使用 `hasFile(string $key): bool` 方法確認請求中是否存在文件：

```php
if ($request->hasFile('photo')) {
    // ...
}
```

#### 驗證成功上傳

除了檢查上傳的文件是否存在外，您也可以通過 `isValid(): bool` 方法驗證上傳的文件是否有效：

```php
if ($request->file('photo')->isValid()) {
    // ...
}
```

#### 文件路徑 & 擴展名

`UploadedFile` 類還包含訪問文件的完整路徑及其擴展名方法。`getExtension()` 方法會根據文件內容判斷文件的擴展名。該擴展名可能會和客户端提供的擴展名不同：

```php
// 該路徑為上傳文件的臨時路徑
$path = $request->file('photo')->getPath();

// 由於 Swoole 上傳文件的 tmp_name 並沒有保持文件原名，所以這個方法已重寫為獲取原文件名的後綴名
$extension = $request->file('photo')->getExtension();
```

#### 存儲上傳文件

上傳的文件在未手動儲存之前，都是存在一個臨時位置上的，如果您沒有對該文件進行儲存處理，則在請求結束後會從臨時位置上移除，所以我們可能需要對文件進行持久化儲存處理，通過 `moveTo(string $targetPath): void` 將臨時文件移動到 `$targetPath` 位置持久化儲存，代碼示例如下：

```php
$file = $request->file('photo');
$file->moveTo('/foo/bar.jpg');

// 通過 isMoved(): bool 方法判斷方法是否已移動
if ($file->isMoved()) {
    // ...
}
```


## 相關事件

當我們在服務配置中，打開 `enable_request_lifecycle`，則每次請求進來，都可以觸發以下三個事件分別是

### 配置實例

> 以下刪除其他不相干代碼

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
