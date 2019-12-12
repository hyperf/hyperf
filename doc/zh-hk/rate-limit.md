# 令牌桶限流器

## 安裝

```bash
composer require hyperf/rate-limit
```
## 默認配置

|  配置          | 默認值 |         備註        |
|:--------------:|:------:|:-------------------:|
| create         | 1      | 每秒生成令牌數      |
| consume        | 1      | 每次請求消耗令牌數  |
| capacity       | 2      | 令牌桶最大容量      |
| limitCallback  | NULL   | 觸發限流時回調方法  |
| key            | NULL   | 生成令牌桶的 key     |
| waitTimeout    | 3      | 排隊超時時間        |

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

組件提供 `Hyperf\RateLimit\Annotation\RateLimit` 註解，作用於類、類方法，可以覆蓋配置文件。 例如，

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
        return ["QPS 1, 峯值3"];
    }

    /**
     * @RequestMapping(path="test2")
     * @RateLimit(create=2, consume=2, capacity=4)
     */
    public function test2()
    {
        return ["QPS 2, 峯值2"];
    }
}
``` 
配置優先級 `方法註解 > 類註解 > 配置文件 > 默認配置`

## 觸發限流
當限流被觸發時, 默認會拋出 `Hyperf\RateLimit\Exception\RateLimitException` 異常

可以通過[異常處理](zh/exception-handler.md)或者配置 `limitCallback` 限流回調處理。

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
        return ["QPS 1, 峯值3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds 下次生成Token 的間隔, 單位為秒
        // $proceedingJoinPoint 此次請求執行的切入點
        // 可以通過調用 `$proceedingJoinPoint->process()` 繼續執行或者自行處理
        return $proceedingJoinPoint->process();
    }
}
```
