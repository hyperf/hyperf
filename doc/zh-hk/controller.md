# 控制器

通過控制器來處理 HTTP 請求，需要通過 `配置文件` 或 `註解` 的形式將路由與控制器方法進行綁定，具體請查閲 [路由](zh-hk/router.md) 章節。   
對於 `請求(Request)` 與 `響應(Response)`，Hyperf 提供了 `Hyperf\HttpServer\Contract\RequestInterface` 和 `Hyperf\HttpServer\Contract\ResponseInterface` 方便您獲取入參和返回數據，關於 [請求](zh-hk/request.md) 與 [響應](zh-hk/response.md) 的詳細內容請查閲對應的章節。

## 編寫控制器

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // 在參數上通過定義 RequestInterface 和 ResponseInterface 來獲取相關對象，對象會被依賴注入容器自動注入
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> 我們假設該 `Controller` 已經通過了配置文件的形式定義了路由為 `/`，當然您也可以使用註解路由

通過 `cURL` 調用該地址，即可看到返回的內容。

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

## 避免協程間數據混淆

在傳統的 PHP-FPM 的框架裏，會習慣提供一個 `AbstractController` 或其它命名的 `Controller 抽象父類`，然後定義的 `Controller` 需要繼承它用於獲取一些請求數據或進行一些返回操作，在 Hyperf 裏是 **不能這樣做** 的，因為在 Hyperf 內絕大部分的對象包括 `Controller` 都是以 `單例(Singleton)` 形式存在的，這也是為了更好的複用對象，而對於與請求相關的數據在協程下也是需要儲存到 `協程上下文(Context)` 內的，所以在編寫代碼時請務必注意 **不要** 將單個請求相關的數據儲存在類屬性內，包括非靜態屬性。   

當然如果非要通過類屬性來儲存請求數據的話，也不是沒有辦法的，我們可以注意到我們獲取 `請求(Request)` 與 `響應(Response)` 對象時是通過注入 `Hyperf\HttpServer\Contract\RequestInterface` 和 `Hyperf\HttpServer\Contract\ResponseInterface` 來獲取的，那對應的對象不也是個單例嗎？這裏是如何做到協程安全的呢？就 `RequestInterface` 來舉例，對應的 `Hyperf\HttpServer\Request` 對象內部在獲取 `PSR-7 請求對象` 時，都是從 `協程上下文(Context)` 獲取的，所以實際使用的類僅僅是一個代理類，實際調用的都是從 `協程上下文(Context)` 中獲取的。
