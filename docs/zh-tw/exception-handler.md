# 異常處理器

在 `Hyperf` 裡，業務程式碼都執行在 `Worker 程序` 上，也就意味著一旦任意一個請求的業務存在沒有捕獲處理的異常的話，都會導致對應的 `Worker 程序` 被中斷退出，這對服務而言也是不能接受的，捕獲異常並輸出合理的報錯內容給客戶端也是更加友好的。   
我們可以透過對各個 `server` 定義不同的 `異常處理器(ExceptionHandler)`，一旦業務流程存在沒有捕獲的異常，都會被傳遞到已註冊的 `異常處理器(ExceptionHandler)` 去處理。

## 自定義一個異常處理

### 透過配置檔案註冊異常處理器

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // 這裡的 http 對應 config/autoload/server.php 內的 server 所對應的 name 值
        'http' => [
            // 這裡配置完整的類名稱空間地址已完成對該異常處理器的註冊
            \App\Exception\Handler\FooExceptionHandler::class,
        ],    
    ],
];
```

### 透過[註解](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)註冊異常處理器

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterHandler;

// 這裡的 http 對應 config/autoload/server.php 內的 server 所對應的 name 值
// priority 為排序
#[RegisterHandler(server: 'http')]
class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        return $response->withHeader('Server', 'Hyperf')->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}

```

> 每個異常處理器配置陣列的順序決定了異常在處理器間傳遞的順序。

### 定義異常處理器

我們可以在任意位置定義一個 `類(Class)` 並繼承抽象類 ` Hyperf\ExceptionHandler\ExceptionHandler` 並實現其中的抽象方法，如下：

```php
<?php
namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use App\Exception\FooException;
use Throwable;

class FooExceptionHandler extends  ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 判斷被捕獲到的異常是希望被捕獲的異常
        if ($throwable instanceof FooException) {
            // 格式化輸出
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);

            // 阻止異常冒泡
            $this->stopPropagation();
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        // 交給下一個異常處理器
        return $response;

        // 或者不做處理直接遮蔽異常
    }

    /**
     * 判斷該異常處理器是否要對該異常進行處理
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```

### 定義異常類

```php
<?php
namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class FooException extends ServerException
{
}
```

### 觸發異常

```php

namespace App\Controller;

use App\Exception\FooException;

class IndexController extends AbstractController
{
    public function index()
    {
        throw new FooException('Foo Exception...', 800);
    }
}

```
在上面這個例子，我們先假設 `FooException` 是存在的一個異常，以及假設已經完成了該處理器的配置，那麼當業務丟擲一個沒有被捕獲處理的異常時，就會根據配置的順序依次傳遞，整一個處理流程可以理解為一個管道，若前一個異常處理器呼叫 `$this->stopPropagation()` 則不再往後傳遞，若最後一個配置的異常處理器仍不對該異常進行捕獲處理，那麼就會交由 Hyperf 的預設異常處理器處理了。

## 整合 Whoops

框架提供了 Whoops 整合。

首先安裝 Whoops
```php
composer require --dev filp/whoops
```

然後配置 Whoops 專用異常處理器。

```php
// config/autoload/exceptions.php
return [
    'handler' => [
        'http' => [
            \Hyperf\ExceptionHandler\Handler\WhoopsExceptionHandler::class,
        ],    
    ],
];
```

效果如圖：

![whoops](/imgs/whoops.png)


## Error 監聽器

框架提供了 `error_reporting()` 錯誤級別的監聽器 `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`。

### 配置

在 `config/autoload/listeners.php` 中新增監聽器

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

則當出現類似以下的程式碼時會丟擲 `\ErrorException` 異常

```php
<?php
try {
    $a = [];
    var_dump($a[1]);
} catch (\Throwable $throwable) {
    var_dump(get_class($throwable), $throwable->getMessage());
}

// string(14) "ErrorException"
// string(19) "Undefined offset: 1"
```

如果不配置監聽器則如下，且不會丟擲異常。

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```

