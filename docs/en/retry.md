# Retry

Network communication is inherently unstable, so in distributed systems, good fault-tolerant design is necessary. Indiscriminate retries are very dangerous. When a communication problem occurs, if every request is retried once, it is equivalent to an increase of 100% in system IO load, which can easily induce an avalanche. Retries must also consider the cause of the error. If it is a problem that cannot be solved by retrying, retrying is just a waste of resources. In addition, if the retry interface is not idempotent, it may also cause problems such as data inconsistency.

This component provides a rich set of retry mechanisms to meet retry requirements in various scenarios.


## Installation

```bash
composer require hyperf/retry
```

## Hello World

Add the `#[Retry]` annotation to the method that needs retrying.

```php
/**
 * Retry the method when an exception occurs
 */
#[Retry]
public function foo()
{
    // Initiate a remote call
}
```

The default Retry strategy can meet most daily retry needs and will not cause an avalanche due to excessive retries.

## Deep Customization

This component achieves pluggability by combining multiple retry strategies. Each strategy focuses on different aspects of the retry process, such as retry judgment, retry interval, result processing, etc. By adjusting the strategies used in the annotation, you can configure retry aspects adapted to any scenario.

It is recommended to construct your own annotation aliases according to specific business needs. Below we demonstrate how to create a new annotation with a maximum number of attempts of 3.

> In the default `Retry` annotation, you can control the maximum number of retries via `#[Retry(maxAttempts=3)]`. For demonstration purposes, pretend it does not exist.

First, you need to create a new `annotation class` and inherit `\Hyperf\Retry\Annotations\AbstractRetry`.

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

According to your needs, override the `$policies` property. To limit the number of retries, you need to use `MaxAttemptsRetryPolicy`. `MaxAttemptsRetryPolicy` also requires a parameter, which is the maximum attempt limit, `$maxAttempts`. Add these two properties to the above class.

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

Now, the `#[MyRetry]` annotation will cause any method to be looped three times. We also need to add a new strategy, `ClassifierRetryPolicy`, to control what kind of errors can be retried. After adding `ClassifierRetryPolicy`, it will default to retrying only after throwing a `Throwable`.

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

You can continue to refine this annotation until it meets your customized needs. For example, configure it to retry only user-defined `TimeoutException`, and use a variable-length interval where the retry sleeps for at least 100 milliseconds. The method is as follows:

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

As long as you ensure that the file is scanned by Hyperf, you can use the `#[MyRetry]` annotation in the method to retry timeout errors.

## Default Configuration

The complete default properties of the `#[Retry]` annotation are as follows:

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
 * Max Attempts.
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
 * Base time interval (ms) for each try. For backoff strategy this is the interval for the first try
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
 * Configures a Predicate which evaluates if a result should be retried.
 * The Predicate must return true if the result should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnResultPredicate = '';

/**
 * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
 * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
 *
 * Ignoring a Throwable has priority over retrying an exception.
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

## Optional Strategies

### Max Attempts Policy `MaxAttemptsRetryPolicy`

| Parameter | Type | Description |
| ---------- | --- | --- |
| maxAttempts | int | Maximum number of attempts |


### Error Classifier Policy `ClassifierRetryPolicy`

Determine whether an error can be retried through a classifier.

| Parameter | Type | Description |
| ---------- | --- | --- |
| ignoreThrowables | array | Ignored `Throwable` class names. Takes priority over `retryThrowables` |
| retryThrowables | array | `Throwable` class names that need to be retried. Takes priority over `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | Determine whether `Throwable` can be retried through a function. If it can be retried, return true, otherwise return false. |
| retryOnResultPredicate | callable | Determine whether the return value can be retried through a function. If it can be retried, return true, otherwise return false. |

### Fallback Policy `FallbackRetryPolicy`

Execute alternative methods after retry resources are exhausted.

| Parameter | Type | Description |
| ---------- | --- | --- |
| fallback | callable | fallback method |

In addition to code that can be identified by `is_callable`, `fallback` can also be filled in the format of `class@method`. The framework will get the corresponding `class` from the `Container` and then execute its `method` method.

### Sleep Policy `SleepRetryPolicy`

Provides two retry interval strategies: Flat Retry Interval (FlatStrategy) and Variable-Length Retry Interval (BackoffStrategy).

| Parameter | Type | Description |
| ---------- | --- | --- |
| base | int | Base sleep time (milliseconds) |
| strategy | string | Any class name that implements `Hyperf\Retry\SleepStrategyInterface`, such as `Hyperf\Retry\BackoffStrategy` |

### Timeout Policy `TimeoutRetryPolicy`

Exit the retry session after the total execution time exceeds the time.

| Parameter | Type | Description |
| ---------- | --- | --- |
| timeout | float | Timeout time (seconds) |

### Circuit Breaker Policy `CircuitBreakerRetryPolicy`

After retrying fails and exiting the retry session, it is directly marked as fused for a period of time, and no further attempts are made.

| Parameter | Type | Description |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout | float | Time required for recovery (seconds) |

### Budget Policy `BudgetRetryPolicy`

Each `#[Retry]` annotation generates a corresponding token bucket. Whenever the annotated method is called, a token with an expiration time (ttl) is placed in the token bucket. If a retryable error occurs, the corresponding number of tokens (percentCanRetry) must be consumed before retrying, otherwise it will not retry (the error continues to be passed down). For example, when percentCanRetry=0.2, 5 tokens are consumed for each retry. Thus, when the peer crashes, it will only cause a maximum of 20% extra retry consumption, which should be acceptable for most systems.

To accommodate some methods with lower usage frequency, a certain number of "minimum" tokens (minRetriesPerSec) are generated every second to ensure system stability.

| Parameter | Type | Description |
| ---------- | --- | --- |
| retryBudget.ttl | int | Token expiration time (seconds) |
| retryBudget.minRetriesPerSec | int | Minimum number of retries guaranteed per second |
| retryBudget.percentCanRetry | float | Percentage of retries not exceeding the total number of requests |

> The token bucket of the retry component is not shared between workers, so the final number of retries must be multiplied by the number of workers.

## Annotation Aliases

Because retry annotation configuration is relatively complex, some preset aliases are provided here for ease of writing.

* `#[RetryThrowable]` retries only `Throwable`. Same as the default `#[Retry]`.

* `#[RetryFalsy]` retries only errors where the return value is loosely equal to false ($result == false), and does not retry exceptions.

* `#[BackoffRetryThrowable]` The variable-length retry interval version of `#[RetryThrowable]`, with a retry interval of at least 100 milliseconds.

* `#[BackoffRetryFalsy]` The variable-length retry interval version of `#[RetryFalsy]`, with a retry interval of at least 100 milliseconds.

## Fluent Chained Calls

In addition to using this component with annotation methods, you can also use it through regular PHP functions.

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), // Default retry all Throwable
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) // Retry up to 5 times
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```
To enhance readability, you can also use the following fluent syntax.

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // Retry when returning false
    ->max(3) // Up to 3 times
    ->inSeconds(5) // Maximum 5 seconds
    ->sleep(1) // Interval 1 millisecond
    ->fallback(function(){return true;}) // fallback function
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
