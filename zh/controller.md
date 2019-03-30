# 控制器

Hyperf 提供了 `Hyperf\HttpServer\Contract\RequestInterface` 和 `Hyperf\HttpServer\Contract\ResponseInterface` 方便您获取入参和返回数据。

## 使用

```php
<?php

declare(strict_types=1);

namespace App\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class IndexController
{
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $name = $request->input('target', 'World');
        return 'Hello ' . $name;
    }
}
```

> 我们假设该 `Controller` 已经通过了配置文件的形式定义了路由为 `/`

调用接口，即可看到返回。

```bash
$ curl http://127.0.0.1:9501/\?target\=Hyperf
Hello Hyperf.
```

到这里，有人会问，如果我想使用 `URL重定向` 和 `设置 Cookies` 时怎么办？*下面提供一种暂时解决方案，后面可能会调整。*

```php
<?php

declare(strict_types=1);

use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Swoft\Http\Message\Cookie\Cookie;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
    }

    public function redirect($url, $status = 302)
    {
        return $this->response()
            ->withAddedHeader('Location', (string) $url)
            ->withStatus($status);
    }

    public function cookie(Cookie $cookie)
    {
        $response = $this->response()->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    /**
     * @return \Swoft\Http\Message\Server\Response
     */
    public function response()
    {
        return Context::get(PsrResponseInterface::class);
    }
}

```
