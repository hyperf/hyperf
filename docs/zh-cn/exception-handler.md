# 异常处理器

在 `Hyperf` 里，业务代码都运行在 `Worker 进程` 上，也就意味着一旦任意一个请求的业务存在没有捕获处理的异常的话，都会导致对应的 `Worker 进程` 被中断退出，这对服务而言也是不能接受的，捕获异常并输出合理的报错内容给客户端也是更加友好的。   
我们可以通过对各个 `server` 定义不同的 `异常处理器(ExceptionHandler)`，一旦业务流程存在没有捕获的异常，都会被传递到已注册的 `异常处理器(ExceptionHandler)` 去处理。

## 自定义一个异常处理

### 通过配置文件注册异常处理器

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // 这里的 http 对应 config/autoload/server.php 内的 server 所对应的 name 值
        'http' => [
            // 这里配置完整的类命名空间地址已完成对该异常处理器的注册
            \App\Exception\Handler\FooExceptionHandler::class,
        ],    
    ],
];
```

### 通过[注解](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)注册异常处理器

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterHandler;

// 这里的 http 对应 config/autoload/server.php 内的 server 所对应的 name 值
// priority 为排序
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

> 每个异常处理器配置数组的顺序决定了异常在处理器间传递的顺序。

### 定义异常处理器

我们可以在任意位置定义一个 `类(Class)` 并继承抽象类 ` Hyperf\ExceptionHandler\ExceptionHandler` 并实现其中的抽象方法，如下：

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
        // 判断被捕获到的异常是希望被捕获的异常
        if ($throwable instanceof FooException) {
            // 格式化输出
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);

            // 阻止异常冒泡
            $this->stopPropagation();
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        // 交给下一个异常处理器
        return $response;

        // 或者不做处理直接屏蔽异常
    }

    /**
     * 判断该异常处理器是否要对该异常进行处理
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```

### 定义异常类

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

### 触发异常

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
在上面这个例子，我们先假设 `FooException` 是存在的一个异常，以及假设已经完成了该处理器的配置，那么当业务抛出一个没有被捕获处理的异常时，就会根据配置的顺序依次传递，整一个处理流程可以理解为一个管道，若前一个异常处理器调用 `$this->stopPropagation()` 则不再往后传递，若最后一个配置的异常处理器仍不对该异常进行捕获处理，那么就会交由 Hyperf 的默认异常处理器处理了。

## 集成 Whoops

框架提供了 Whoops 集成。

首先安装 Whoops
```php
composer require --dev filp/whoops
```

然后配置 Whoops 专用异常处理器。

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

效果如图：

![whoops](/imgs/whoops.png)


## Error 监听器

框架提供了 `error_reporting()` 错误级别的监听器 `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`。

### 配置

在 `config/autoload/listeners.php` 中添加监听器

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

则当出现类似以下的代码时会抛出 `\ErrorException` 异常

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

如果不配置监听器则如下，且不会抛出异常。

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```

