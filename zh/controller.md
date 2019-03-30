# 控制器

通过控制器来处理 HTTP 请求，需要通过 `配置文件` 或 `注解` 的形式将路由与控制器方法进行绑定，具体请查阅 [路由](zh/route.md) 章节。   
对于 `请求(Request)` 与 `响应(Response)`，Hyperf 提供了 `Hyperf\HttpServer\Contract\RequestInterface` 和 `Hyperf\HttpServer\Contract\ResponseInterface` 方便您获取入参和返回数据，关于 [请求](zh/request.md) 与 [响应](zh/response.md) 的详细内容请查阅对应的章节。

## 使用

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    // 在 Action 的参数上通过定义 RequestInterface 和 ResponseInterface 来获取相关对象，对象会被依赖注入容器自动注入
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $target = $request->input('target', 'World');
        return 'Hello ' . $target;
    }
}
```

> 我们假设该 `Controller` 已经通过了配置文件的形式定义了路由为 `/`

通过 `cURL` 调用该地址，即可看到返回的内容。

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

这里提醒一个重要的信息，在传统的 PHP-FPM 的框架里，会习惯提供一个 `AbstractController` 或其它命名的 `Controller 抽象父类`，然后定义的 `Controller` 需要基础它用于获取一些请求数据或进行一些返回操作，在 Hyperf 里是 `不能这样做` 的，因为在 Hyperf 内绝大部分的对象包括 `Controller` 都是以单例形式存在的，这也是为了更好的复用对象，而对于与请求相关的数据在协程下也是需要储存到 `协程上下文(Context)` 内的，顾在编写代码时请务必注意不要将单个请求相关的数据储存在类属性内。   
当然如果非要通过类属性来储存请求数据的话，也不是没有办法的，我们可以注意到我们获取 `请求(Request)` 与 `响应(Response)` 对象时是通过注入 `Hyperf\HttpServer\Contract\RequestInterface` 和 `Hyperf\HttpServer\Contract\ResponseInterface` 来获取的，那对应的对象不也是个单例吗？这里是如何做到协程安全的呢？就 `RequestInterface` 来举例，对应的 `Hyperf\HttpServer\Request` 对象内部在获取 `PSR-7 请求对象` 时，都是从 `协程上下文(Context)` 获取的，顾实际使用的类仅仅是一个代理类，实际调用的都是从 `协程上下文(Context)` 中获取的。