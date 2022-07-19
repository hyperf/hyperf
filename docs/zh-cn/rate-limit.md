# 令牌桶限流器

## 安装

```bash
composer require hyperf/rate-limit
```

## 配置

### 发布配置

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### 配置说明

|  配置          | 默认值 | 类型 |       备注        |
|:--------------:|:------:|:--------------:|:-------------------:|
| create         | 1      |int| 每秒生成令牌数      |
| consume        | 1      |int| 每次请求消耗令牌数  |
| capacity       | 2      |int| 令牌桶最大容量      |
| limitCallback  | `[]`   |null\|callable| 触发限流时回调方法  |
| waitTimeout    | 1      |int| 排队超时时间        |
| key            | 当前请求 url 地址     |callable\|string| 限流的 key        |

## 使用限流器

组件提供 `Hyperf\RateLimit\Annotation\RateLimit` 注解，作用于类、类方法，可以覆盖配置文件。 例如，

```php
<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

/**
 * @Controller(prefix="rate-limit")
 */
class RateLimitController
{
    /**
     * @RequestMapping(path="test")
     * @RateLimit(create=1, capacity=3)
     */
    public function test()
    {
        return ["QPS 1, 峰值3"];
    }

    /**
     * @RequestMapping(path="test2")
     * @RateLimit(create=2, consume=2, capacity=4)
     */
    public function test2()
    {
        return ["QPS 2, 峰值2"];
    }
}
``` 
配置优先级 `方法注解 > 类注解 > 配置文件 > 默认配置`

## 触发限流
当限流被触发时, 默认会抛出 `Hyperf\RateLimit\Exception\RateLimitException` 异常

可以通过[异常处理](zh-cn/exception-handler.md)或者配置 `limitCallback` 限流回调处理。

例如:
```php
<?php

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

/**
 * @Controller(prefix="rate-limit")
 * @RateLimit(limitCallback={RateLimitController::class, "limitCallback"})
 */
class RateLimitController
{
    /**
     * @RequestMapping(path="test")
     * @RateLimit(create=1, capacity=3)
     */
    public function test()
    {
        return ["QPS 1, 峰值3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds 下次生成Token 的间隔, 单位为秒
        // $proceedingJoinPoint 此次请求执行的切入点
        // 可以通过调用 `$proceedingJoinPoint->process()` 继续完成执行，或者自行处理
        return $proceedingJoinPoint->process();
    }
}
```

## 自定义令牌桶限流 key

默认的 key 是获取当前请求的地址，当需要针对用户 ID 进行限流，可以使用用户 ID 作为自定义 key 进行限流，可以参考以下示例实现

```php
<?php

declare(strict_types=1);


namespace App\Controller;

use App\Service\TestService;
use App\Request\TestRequest;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Utils\ApplicationContext;


class TestController
{
    /**
     * @var TestService
     */
    private $TestService;

    public function __construct(TestService $TestService)
    {
        $this->TestService = $TestService;
    }

    /**
     * 1 小时 6 次
     * @RateLimit(create=1, capacity=3600, consume=600, key={TestController::class, "rateLimit"})
     */
    public function test(TestRequest $request)
    {
        $params = $request->validated();

        return $this->TestService->getToken($params);
    }

    public function rateLimit(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(TestRequest::class);
        
        $params = $request->validated();

        return $params['id'];
    }

}

```
