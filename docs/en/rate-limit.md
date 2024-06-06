# Token bucket rate limiter

## Installation

```bash
composer require hyperf/rate-limit
```
## Configuration

### Publish config

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### Config description

|  config item   | default |         remark        |
|:--------------:|:-------:|:---------------------:|
| create         | 1       | Number of tokens generated per second            |
| consume        | 1       | Number of tokens consumed per request            |
| capacity       | 2       | Maximum capacity of token bucket                 |
| limitCallback  | `[]`    | Callback method when current limit is triggered  |
| waitTimeout    | 1       | timeout in wait queue                            |

## Usage

The component provides `Hyperf\RateLimit\Annotation\RateLimit` annotation, which acts on classes and class methods, and can override configuration files. For example:

```php
<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peek3"];
    }

    #[RequestMapping(path: "test2")]
    #[RateLimit(create: 2, consume: 2, capacity: 4)]
    public function test2()
    {
        return ["QPS 2, Peek2"];
    }
}
``` 
Configuration priority `Method Annotation > Class Annotation > Configuration File > Default Configuration`

## Trigger current limit
When the current limit is triggered, the `Hyperf\RateLimit\Exception\RateLimitException` will be thrown by default.

You can use [Exception Handler](en/exception-handler.md) or configure `limitCallback` to handle the current limit callback.

For example:
```php
<?php

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
#[RateLimit(limitCallback: {RateLimitController::class, "limitCallback"})]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peek3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds Token generation time interval, in seconds
        // $proceedingJoinPoint The entry point for the execution of this request
        // You can handle it by yourself, or continue its execution by calling `$proceedingJoinPoint->process()`
        return $proceedingJoinPoint->process();
    }
}
```

## Customized token bucket current limiting key

The default key is based on the `url` of the current request. When a user triggers current limiting, other users will also be restricted to request this `url`;

If current limiting at different granularities is required, such as current limiting at user latitude, current limiting can be carried out based on user `ID`, so that user A is restricted and user B can request normally:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;

class TestController
{
    #[RateLimit(create: 1, capacity: 3, key: {TestController::class, "getUserId"})]
    public function test()
    {
        return ["QPS 1, 峰值3"];
    }

    public static function getUserId(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        // In the same way, traffic can be limited based on different dimensions such as mobile phone number and IP address.
        return $request->input('user_id');
    }
}
```