# 熔断器

## 安装

```
composer require hyperf/circuit-breaker
```

## 为什么要熔断？

分布式系统中经常会出现由于某个基础服务不可用造成整个系统不可用的情况，这种现象被称为服务雪崩效应。为了应对服务雪崩，一种常见的做法是服务降级。而 [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) 组件，就是为了来解决这个问题的。

## 使用熔断器

熔断器的使用十分简单，只需要加入 `Hyperf\CircuitBreaker\Annotation\CircuitBreaker` 注解，就可以根据规定策略，进行熔断。
比如我们需要到另外服务中查询用户列表，用户列表需要关联很多的表，查询效率较低，但平常并发量不高的时候，响应速度还说得过去。一旦并发量激增，就会导致响应速度变慢，并会使对方服务出现慢查。这个时候，我们只需要配置一下熔断超时时间 `timeout` 为 0.05 秒，失败计数 `failCounter` 超过 1 次后熔断，相应 `fallback` 为 `App\Service\UserService` 类的 `searchFallback` 方法。这样当响应超时并触发熔断后，就不会再请求对端的服务了，而是直接将服务降级从当前项目中返回数据，即根据 `fallback` 指定的方法来进行返回。

```php
<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\UserServiceClient;
use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\Di\Annotation\Inject;

class UserService
{
    #[Inject]
    private UserServiceClient $client;

    #[CircuitBreaker(options: ['timeout' => 0.05], failCounter: 1, successCounter: 1, fallback: [UserService::class, 'searchFallback'])]
    public function search($offset, $limit)
    {
        return $this->client->users($offset, $limit);
    }

    public function searchFallback($offset, $limit)
    {
        return [];
    }
}

```

默认熔断策略为 `超时策略` ，如果您想要自己实现熔断策略，只需要自己实现 `Handler` 继承于 `Hyperf\CircuitBreaker\Handler\AbstractHandler` 即可。

```php
<?php
declare(strict_types=1);

namespace Hyperf\CircuitBreaker\Handler;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\Exception\TimeoutException;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class DemoHandler extends AbstractHandler
{
    const DEFAULT_TIMEOUT = 5;

    protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        $result = $proceedingJoinPoint->process();

        if (is_break()) {
            throw new TimeoutException('timeout, use ' . $use . 's', $result);
        }

        return $result;
    }
}

```
