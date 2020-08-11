# 令牌桶限流器

## Installation

```bash
composer require hyperf/rate-limit
```
## 默认配置

|  配置          | 默认值 |         备注        |
|:--------------:|:------:|:-------------------:|
| create         | 1      | 每秒生成令牌数      |
| consume        | 1      | 每次请求消耗令牌数  |
| capacity       | 2      | 令牌桶最大容量      |
| limitCallback  | NULL   | 触发限流时回调方法  |
| key            | NULL   | 生成令牌桶的key     |
| waitTimeout    | 3      | 排队超时时间        |

```php
<?php

return [
    'create' => 1,
    'consume' => 1,
    'capacity' => 2,
    'limitCallback' => null,
    'key' => null,
    'waitTimeout' => 3,
];
```

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

可以通过[异常处理](en/exception-handler.md)或者配置 `limitCallback` 限流回调处理。

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
 * @RateLimit(limitCallback={RateLimitController::class, 'limitCallback'})
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
        // 可以通过调用 `$proceedingJoinPoint->process()` 继续执行或者自行处理
        return $proceedingJoinPoint->process();
    }
}
```