# 熔断器

## 安装

```
composer require hyperf/circuit-breaker
```

## 原因

分布式系统中经常会出现某个基础服务不可用造成整个系统不可用的情况, 这种现象被称为服务雪崩效应. 为了应对服务雪崩, 一种常见的做法是手动服务降级. 而 `hyperf/circuit-breaker` 组件，就是为了来解决这个问题的。

## 使用

熔断器的使用十分简单，只需要加入 `Hyperf\CircuitBreaker\Annotation\CircuitBreaker` 注解，就可以根据规定策略，进行熔断。
比如我们需要到另外服务中查询用户列表，用户列表需要关联N多表，查询效率较低，但平常并发量不高的时候，相应速度还说得过去。一旦并发量激增，就会导致响应速度变慢，并会使对方服务出现慢查。这个时候，我们只需要配置一下熔断超时时间 `timeout` 为 0.05 秒，失败计数 `failCounter` 超过 1 次后熔断，相应 `fallback` 为 `App\UserService@searchFallback` 方法。这样，当响应超时，熔断后，就不会在请求对方服务，而是直接从当前项目中返回数据。

```php
<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\Di\Annotation\Inject;
use App\UserServiceClient;

class UserService
{
    /**
     * @Inject
     * @var UserServiceClient
     */
    private $client;

    /**
     * @CircuitBreaker(timeout=0.05, failCounter=1, successCounter=1, fallback="App\UserService@searchFallback")
     */
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

默认熔断策略为超时策略，如果您想要自己实现熔断策略，只需要自己实现 Hanlder 继承于 `Hyperf\CircuitBreaker\Handler\AbstractHandler` 即可。
