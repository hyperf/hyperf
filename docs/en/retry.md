# Retry

Network communication is inherently unstable, so in a distributed system, a good fault-tolerant design is required. Indiscriminate retry is very dangerous. When there is a problem with communication, each request is retried once, which is equivalent to a 100% increase in system IO load, which is easy to induce avalanche accidents. Retrying also considers the cause of the error. If it is a problem that cannot be solved by retrying, then retrying is just a waste of resources. In addition, if the retrying interface is not idempotent, it may also cause data inconsistency and other problems.

This component provides a rich retry mechanism to meet the retry requirements of various scenarios.


## Install

```bash
composer require hyperf/retry
```

## Hello World

Add the annotation `#[Retry]` to the method that needs to be retried.

```php
/**
 * Retry the method on exception
 */
#[Retry]
public function foo()
{
    // make a remote call
}
```

The default Retry strategy can meet most daily retry needs without excessive retries causing avalanches.

## Deep customization

This component achieves pluggability by combining multiple retry strategies. Each strategy focuses on different aspects of the retry process, such as retry judgment, retry interval, and result processing. By adjusting the strategy used in the annotation, you can configure the retry aspect suitable for any scenario.

It is recommended to construct your own annotation aliases according to specific business needs. Below we demonstrate how to make a new annotation with a maximum number of attempts of 3.

> In the default `Retry` annotation, you can control the maximum number of retries with `@Retry(maxAttempts=3)`. For the sake of demonstration, pretend it doesn't exist.

First you need to create an `annotation class` and inherit `\Hyperf\Retry\Annotations\AbstractRetry`.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
}
```

Override the `$policies` property according to your needs. To limit the number of retries, use `MaxAttemptsRetryPolicy` . `MaxAttemptsRetryPolicy` also needs a parameter, which is the limit of the maximum number of attempts, `$maxAttempts`. Add these two properties to the above class.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
    ];
    public $maxAttempts = 3;
}
```

Now that the `@MyRetry` annotation will cause any method to be executed three times in a loop, we also need to add a new policy `ClassifierRetryPolicy` to control what errors can be retried. Adding `ClassifierRetryPolicy` will only retry after throwing `Throwable` by default.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
        ClassifierRetryPolicy::class,
    ];
    public $maxAttempts = 3;
}
```

You can continue to refine the annotation until it meets your customized needs. For example, configure to retry only user-defined `TimeoutException` , and use retry to sleep at least 100ms of variable length interval, as follows:

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\Retry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
        ClassifierRetryPolicy::class,
        SleepRetryPolicy::class,
    ];
    public $maxAttempts = 3;
    public $base = 100;
    public $strategy = \Hyperf\Retry\BackoffStrategy::class;
    public $retryThrowables = [\App\Exception\TimeoutException::class];
}
```

Just make sure the file is scanned by Hyperf, you can use the `@MyRetry` annotation in the method to retry timeout errors.

## default allocation

The full annotation default properties of `@Retry` are as follows:

```php
/**
 * Array of retry policies. Think of these as stacked middlewares.
 * @var string[]
 */
public $policies = [
    FallbackRetryPolicy::class,
    ClassifierRetryPolicy::class,
    BudgetRetryPolicy::class,
    MaxAttemptsRetryPolicy::class,
    SleepRetryPolicy::class,
];

/**
 * The algorithm for retry intervals.
 */
public string $sleepStrategyClass = SleepStrategyInterface::class;

/**
 * Max Attampts.
 */
public int $maxAttempts = 10;

/**
 * Retry Budget.
 * ttl: Seconds of token lifetime.
 * minRetriesPerSec: Base retry token generation speed.
 * percentCanRetry: Generate new token at this ratio of the request volume.
 *
 * @var array|RetryBudgetInterface
 */
public $retryBudget = [
    'ttl' => 10,
    'minRetriesPerSec' => 1,
    'percentCanRetry' => 0.2,
];

/**
 * Base time inteval (ms) for each try. For backoff strategy this is the interval for the first try
 * while for flat strategy this is the interval for every try.
 */
public int $base = 0;

/**
 * Configures a Predicate which evaluates if an exception should be retried.
 * The Predicate must return true if the exception should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnThrowablePredicate = '';

/**
 * Configures a Predicate which evaluates if an result should be retried.
 * The Predicate must return true if the result should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnResultPredicate = '';

/**
 * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
 * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
 *
 * Ignoring an Throwable has priority over retrying an exception.
 *
 * @var array<string|\Throwable>
 */
public $retryThrowables = [\Throwable::class];

/**
 * Configures a list of error classes that are ignored and thus are not retried.
 * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
 *
 * @var array<string|\Throwable>
 */
public $ignoreThrowables = [];

/**
 * The fallback callable when all attempts exhausted.
 *
 * @var callable|string
 */
public $fallback = '';
```

## optional strategies

### Maximum Attempts Policy `MaxAttemptsRetryPolicy`

| Parameters | Type | Description |
| ---------- | --- | --- |
| maxAttempts | int | Maximum number of attempts |


### Error classification policy `ClassifierRetryPolicy`

Pass the classifier to determine if the error can be retried.

| Parameters | Type | Description |
| ---------- | --- | --- |
| ignoreThrowables | array | `Throwable` class names to ignore. takes precedence over `retryThrowables` |
| retryThrowables | array | `Throwable` class names to retry. takes precedence over `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | Pass a function to determine if `Throwable` can be retried. Returns true if retry is possible, false otherwise. |
| retryOnResultPredicate | callable | Use a function to determine whether the return value can be retried. Returns true if it is possible to retry, false otherwise. |

### Fallback policy `FallbackRetryPolicy`

Execute alternate method after retrying resource exhaustion.

| Parameters | Type | Description |
| ---------- | --- | --- |
| fallback | callable | fallback method |

In addition to the code recognized by `is_callable`, `fallback` can also fill in the format of `class@method`, the framework will get the corresponding `class` from `Container`, and then execute its `method` method .

### Sleep policy `SleepRetryPolicy`

Provides two retry intermittent strategies. Equal retry interval (FlatStrategy) and variable retry interval (BackoffStrategy).

| Parameters | Type | Description |
| ---------- | --- | --- |
| base | int | Base sleep time (ms) |
| strategy | string | Any class name that implements `Hyperf\Retry\SleepStrategyInterface`, such as `Hyperf\Retry\BackoffStrategy` |

### Timeout policy `TimeoutRetryPolicy`

Exit the retry session after the total execution time exceeds the time.

| Parameters | Type | Description |
| ---------- | --- | --- |
| timeout | float | timeout (seconds) |

### Circuit breaker policy `CircuitBreakerRetryPolicy`

After the retry fails, the retry session is directly marked as circuit breaker for a period of time, and no more attempts will be made.

| Parameters | Type | Description |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout | float | Time required for recovery (seconds) |

### Budget Policy `BudgetRetryPolicy`

Each `@Retry` annotation will generate a corresponding token bucket, and whenever the annotation method is called, a token with an expiration time (ttl) is placed in the token bucket. If a retryable error occurs, the corresponding number of tokens (percentCanRetry) must be consumed before retrying, otherwise it will not be retried (the error continues to pass down). For example, when percentCanRetry=0.2, each retry consumes 5 tokens. In this way, when the peer is down, at most 20% of the additional retry consumption will be incurred, which should be acceptable for most systems.

To take care of some less frequently used methods, a certain number of "mini-guarantee" tokens (minRetriesPerSec) are also generated per second to ensure system stability.

| Parameters | Type | Description |
| ---------- | --- | --- |
| retryBudget.ttl | int | Recovery token expiration time (seconds) |
| retryBudget.minRetriesPerSec | int | Minimum number of retries per second for "mini-guarantee" |
| retryBudget.percentCanRetry | float | Retry times do not exceed the percentage of total requests |

> The token bucket of the retry component is not shared among workers, so the final number of retries is multiplied by the number of workers.

## Annotation alias

Because the retry annotation configuration is more complicated, some preset aliases are provided here for easy writing.

* `@RetryThrowable` only retry `Throwable`. Same as default `@Retry`.

* `@RetryFalsy` only retry errors whose return value is weakly equal to false ($result == false), not exceptions.

* `@BackoffRetryThrowable` A variable length retry interval version of `@RetryThrowable`, with a retry interval of at least 100ms.

* `@BackoffRetryFalsy` Variable length retry interval version of `@RetryFalsy`, retry interval is at least 100ms.

## Fluent chain call

In addition to using this component with annotated methods, you can also use it with regular PHP functions.

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), // Retry all Throwables by default
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) //Retry up to 5 times
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```
To enhance readability, the following fluent writing can also be used.

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // Retry when false is returned
    ->max(3) // up to 3 times
    ->inSeconds(5) // up to 5 seconds
    ->sleep(1) // 1ms interval
    ->fallback(function(){return true;}) // fallback function
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
