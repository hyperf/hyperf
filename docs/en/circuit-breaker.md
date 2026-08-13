# Circuit Breaker

## Installation

```
composer require hyperf/circuit-breaker
```

## Why Do We Need Circuit Breakers?

In distributed systems, the unavailability of a fundamental service often causes the entire system to be unavailable. This phenomenon is known as the service avalanche effect. To cope with service avalanches, a common practice is service degradation. The [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) component is designed to solve this problem.

## Using Circuit Breakers

The use of a circuit breaker is very simple. Just add the `Hyperf\CircuitBreaker\Annotation\CircuitBreaker` annotation, and you can perform circuit breaking according to the specified policy.
For example, we need to query the user list in another service. The user list needs to be associated with many tables, and the query efficiency is relatively low. However, when the concurrent volume is not high, the response speed is passable. Once the concurrent volume surges, it will cause the response speed to slow down and cause slow queries in the peer service. At this time, we only need to configure the circuit breaking timeout `timeout` to 0.05 seconds, the failure counter `failCounter` to more than 1 time for circuit breaking, and the corresponding `fallback` to the `searchFallback` method of the `App\Service\UserService` class. In this way, when the response times out and triggers a circuit breaker, the request will no longer be sent to the peer service, but the service will be degraded, and the data will be returned from the current project, that is, returned according to the method specified by `fallback`.

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

The default circuit breaking strategy is `Timeout Strategy`. If you want to implement your own circuit breaking strategy, you just need to implement a `Handler` that inherits from `Hyperf\CircuitBreaker\Handler\AbstractHandler`.

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
