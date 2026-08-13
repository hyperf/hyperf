# Token Bucket Rate Limiter

## Installation

```bash
composer require hyperf/rate-limit
```

## Configuration

### Publish Configuration

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### Configuration Description

| Configuration | Default | Type | Description |
|:--------------:|:------:|:--------------:|:-------------------:|
| create         | 1      |int| Tokens generated per second |
| consume        | 1      |int| Tokens consumed per request |
| capacity       | 2      |int| Maximum capacity of the token bucket |
| limitCallback  | `[]`   |null\|callable| Callback method when rate limiting is triggered |
| waitTimeout    | 1      |int| Queue timeout in seconds |
| key            | Current request URL |callable\|string| Key for rate limiting |

## Using the Rate Limiter

The component provides the `Hyperf\RateLimit\Annotation\RateLimit` annotation, which can be applied to classes and class methods, overriding the configuration file. For example:

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
        return ["QPS 1, Peak 3"];
    }

    #[RequestMapping(path: "test2")]
    #[RateLimit(create: 2, consume: 2, capacity: 4)]
    public function test2()
    {
        return ["QPS 2, Peak 2"];
    }
}
``` 
Configuration priority: `Method Annotation > Class Annotation > Configuration File > Default Configuration`

## Triggering Rate Limiting
When rate limiting is triggered, it will throw a `Hyperf\RateLimit\Exception\RateLimitException` by default.

This can be handled via [Exception Handler](exception-handler.md) or by configuring `limitCallback`.

For example:
```php
<?php

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
#[RateLimit(limitCallback: [RateLimitController::class, "limitCallback"])]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peak 3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds: Interval for next token generation, in seconds
        // $proceedingJoinPoint: The join point of this request execution
        // You can call `$proceedingJoinPoint->process()` to continue execution, or handle it yourself
        return $proceedingJoinPoint->process();
    }
}
```

## Customizing Token Bucket Rate Limit Key

The default key is based on the current request `url`. When one user triggers rate limiting, other users are also rate-limited when requesting this `url`.

If you need finer-grained rate limiting, such as at a user level, you can rate-limit based on a user `ID`, so that if user A is rate-limited, user B can still request normally:

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
    /**
     * @RateLimit(create=1, capacity=3, key={TestController::class, "getUserId"})
     */
    public function test()
    {
        return ["QPS 1, Peak 3"];
    }

    public static function getUserId(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        // Similarly, you can rate-limit based on different dimensions such as phone number, IP address, etc.
        return $request->input('user_id');
    }
}
```
