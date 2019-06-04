# 异常处理器

在 `Hyperf` 里，业务代码都运行在 `Worker进程` 上，也就意味着一旦任意一个请求的业务存在没有捕获处理的异常的话，都会导致对应的 `Worker进程` 被中断退出，虽然被中断的 `Worker进程` 仍会被重新拉起，但对服务而已也是不能接受的，且捕获异常并输出合理的报错内容给客户端也是更加友好的。   
我们可以通过对各个 `server` 定义不同的 `异常处理器(ExceptionHandler)`，一旦业务流程存在没有捕获的异常，到会被传递到已注册的 `异常处理器(ExceptionHandler)` 去处理。

# 注册异常处理器

目前仅支持配置文件的形式注册 `异常处理器(ExceptionHandler)`，配置文件位于 `config/autoload/exceptions.php`，将您的自定义异常处理器配置在对应的 `server` 下即可：

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // 这里的 http 对应 config/autoload/server.php 内的 server 所对应的 name 值
        'http' => [
            // 这里配置完整的类命名空间地址已完成对该异常处理器的注册
            \App\ExceptionHandler\FooExceptionHandler::class,
        ],    
    ],
];
```

> 每个异常处理器配置数组的顺序决定了异常在处理器间传递的顺序。

# 定义异常处理器

我们可以在任意位置定义一个 `类(Class)` 并基础抽象类 `Hyperf\Framework\ExceptionHandler` 并实现其中的抽象方法，如下：

```php
<?php
namespace App\ExceptionHandler;

use Hyperf\Framework\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class FooExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 判断传入的异常是否是该处理器希望处理的异常
        if ($throwable instanceof FooException) {
            // 传入的异常是我们希望捕获的 FooException，我们格式化为 JSON 格式并输出到用户端
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }
        // 传入的异常不是该处理器希望处理的异常，不处理并交由下一个异常处理器处理
    }
}
```

在上面这个例子，我们先假设 `FooException` 是存在的一个异常，以及假设已经完成了该处理器的配置，那么当业务抛出一个没有被捕获处理的异常时，就会根据配置的顺序依次传递，整一个处理流程可以理解为一个管道，若前一个异常处理器存在返回值则不再往后传递，若最后一个配置的异常处理器仍不对该异常进行捕获处理，那么就会交由 Hyperf 的默认异常处理器处理了。