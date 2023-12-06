# Circuit Breaker

## Installation

```
composer require hyperf/circuit-breaker
```

## Why you need a Circuit Breaker ?

In distributed systems, it is often the case that the entire system is unavailable due to the unavailability of a basic service. This phenomenon is called the service avalanche effect. In response to service avalanches, a common practice is to downgrade services. The [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) component is designed to solve this problem.

## Usage

## Why you need a Circuit Breaker ?

In distributed systems, it is often the case that the entire system is unavailable due to the unavailability of a basic service. This phenomenon is called the service avalanche effect. In response to service avalanches, a common practice is to downgrade services. The [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) component is designed to solve this problem.

## Using Circuit Breaker

The usage of the Circuit Breaker is very simple, just add the `Hyperf\CircuitBreaker\Annotation\CircuitBreaker` annotation, you can circuit break according to the specified strategy.
For example, we need to query the user list in another service. The user list needs to be associated with a lots of tables. The query efficiency is low, but when the concurrent amount is normal, the corresponding speed is still reasonable. Once the amount of concurrency increases, it will slow down the response and cause the other service to slow down. At this time, we only need to configure the circuit break timeout period `timeout` to be 0.05 seconds, the failure count `failCounter` to be blown after more than 1 time, and the corresponding `fallback` is the `searchFallback` method of the `App\UserService` class. In this way, when the response times out and triggers the circuit break, it will not request the peer's service any more. Instead, it will directly downgrade the service from the current application, that is, return according to the method specified by `fallback`.

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\UserServiceClient;
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

The default circuit break policy is `Timeout Policy`. If you want to implement the circuit break policy yourself, you only need to implement `Handler` inherited by `Hyperf\CircuitBreaker\Handler\AbstractHandler`.

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
