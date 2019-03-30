# 响应

## 返回 Json 格式

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function json(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        // $data 需为一个数组或为一个实现了 Hyperf\Utils\Contracts\Arrayable 接口的对象
        return $response->json($data);
    }
}
```

## 返回 Xml 格式

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function xml(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        // $data 需为一个数组或为一个实现了 Hyperf\Utils\Contracts\Xmlable 接口的对象
        return $response->xml($data);
    }
}
```

## 返回 Raw 格式

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;

class IndexController
{
    public function raw(ResponseInterface $response): Psr7ResponseInterface
    {
        $data = [
            'key' => 'value'
        ];
        // $data 需为一个字符串或为一个实现了 __toString() 方法的对象
        return $response->raw($data);
    }
}
```

## 返回视图

Hyperf 暂不支持视图返回，欢迎社区贡献相关的 PR。

## 重定向

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\ResponseInterface;
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
