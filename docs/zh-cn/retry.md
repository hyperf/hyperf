# 重试

网络通讯天然是不稳定的，因此在分布式系统中，需要有良好的容错设计。无差别重试是非常危险的。当通讯出现问题时，每个请求都重试一次，相当于系统 IO 负载增加了 100%，容易诱发雪崩事故。重试还要考虑错误的原因，如果是无法通过重试解决的问题，那么重试只是浪费资源而已。除此之外，如果重试的接口不具备幂等性，还可能造成数据不一致等问题。

本组件提供了丰富的重试机制，可以满足多种场景的重试需求。


## 安装

```bash
composer require hyperf/retry
```

## Hello World

在需要重试的方法上加入注解 `#[Retry]`。

```php
/**
 * 异常时重试该方法
 */
#[Retry]
public function foo()
{
    // 发起一次远程调用
}
```

默认的 Retry 策略可以满足大部分日常重试需求，且不会过度重试导致雪崩。

## 深度定制

本组件通过组合多种重试策略实现了可插拔性。每个策略关注重试过程中的不同侧面，如重试判断、重试间隔，结果处理等。通过调整注解中使用的策略就可以配置出适配任意场景下的重试切面。

建议根据具体业务需要构造自己的注解别名。下面我们演示如何制作最大尝试次数为 3 的新注解。

> 在默认的 `Retry` 注解中，您可以通过 `@Retry(maxAttempts=3)` 来控制最大重试次数。为了演示需要，先假装它不存在。

首先您要新建一个 `注解类` 并继承 `\Hyperf\Retry\Annotations\AbstractRetry` 。

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

根据您的需要，重写 `$policies` 属性。限制重试次数，需要使用 `MaxAttemptsRetryPolicy` 。`MaxAttemptsRetryPolicy` 还需要一个参数，那就是最大尝试的次数限制，`$maxAttempts` 。把这两个属性加入上述的类中。

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

现在 `@MyRetry` 这个注解会导致任何方法都会被循环执行三次，我们还需要加入一个新的策略 `ClassifierRetryPolicy` 来控制什么样的错误才能被重试。加入 `ClassifierRetryPolicy` 后默认只会在抛出 `Throwable` 后重试。

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

您可以继续完善该注解，直到该注解满足您定制化的需求。例如，配置只重试用户自定义的 `TimeoutException` , 并使用重试至少休眠 100 毫秒的变长间歇, 方法如下：

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

只要确保该文件被 Hyperf 扫描，就可以在方法中使用 `@MyRetry` 注解来重试超时错误了。

## 默认配置

`@Retry` 的完整注解默认属性如下：

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

## 可选策略

### 最大尝试次数策略 `MaxAttemptsRetryPolicy`

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| maxAttempts |  int | 最多尝试的次数 |


### 错误分类策略 `ClassifierRetryPolicy`

通过分类器来判断错误是否可以重试。

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| ignoreThrowables |  array | 无视的 `Throwable` 类名 。优先于 `retryThrowables` |
| retryThrowables | array | 需要重试的 `Throwable` 类名 。优先于 `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | 通过一个函数来判断 `Throwable` 是否可以重试。如果可以重试，请返回 true, 反之必须返回 false。 |
| retryOnResultPredicate | callable | 通过一个函数来判断返回值是否可以重试。如果可以重试，请返回 true，反之必须返回 false。 |

### 回退策略 `FallbackRetryPolicy`

重试资源耗尽后执行备选方法。

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| fallback |  callable | fallback 方法 |

`fallback` 除了可以填写被 `is_callable` 识别的代码外，还可以填写形如 `class@method` 的格式，框架会从 `Container` 中拿到对应的 `class`，然后执行其 `method` 方法。

### 睡眠策略 `SleepRetryPolicy`

提供两种重试间歇策略。等长重试间歇（FlatStrategy）和变长重试间歇（BackoffStrategy）。

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| base |  int | 基础睡眠时间（毫秒）|
| strategy |  string | 任何实现了 `Hyperf\Retry\SleepStrategyInterface` 的类名，如 `Hyperf\Retry\BackoffStrategy` |

### 超时策略 `TimeoutRetryPolicy`

执行总时长超过时间后退出重试会话。

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| timeout |  float | 超时时间（秒）|

### 熔断策略 `CircuitBreakerRetryPolicy`

重试失败退出重试会话后一段时间内直接标记为熔断，不再进行任何尝试。

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout |  float | 恢复所需时间（秒）|

### 预算策略 `BudgetRetryPolicy`

每一个 `@Retry` 注解处会生成一个对应的令牌桶，每当注解方法被调用时，就在令牌桶中放入一个具有过期时间(ttl)的令牌。如果发生可重试的错误，重试前要消耗掉对应的令牌数量(percentCanRetry)，否则就不会重试（错误继续向下传递）。比如，当 percentCanRetry=0.2，则每次重试要消耗 5 个令牌。如此，遇到对端宕机时，最多只会造成 20% 的额外重试消耗，对于大多数系统都应该可以接受了。

为了照顾某些使用频率较低的方法，每秒还会生成一定数量的“低保”令牌(minRetriesPerSec)，确保系统稳定。

|    参数    | 类型 | 说明 |
| ---------- | --- | --- |
| retryBudget.ttl |  int | 恢令牌过期时间（秒）|
| retryBudget.minRetriesPerSec |  int | 每秒“低保”最少可以重试的次数|
| retryBudget.percentCanRetry |  float | 重试次数不超过总请求数的百分比 |

> 重试组件的令牌桶在 worker 之间不共享，所以最终的重试次数要乘以 worker 数量。

## 注解别名

因为重试注解配置较为复杂，这里提供了一些预设的别名便于书写。

* `@RetryThrowable` 只重试 `Throwable`。和默认的 `@Retry` 相同。

* `@RetryFalsy` 只重试返回值弱等于 false（$result == false)的错误，不重试异常。

* `@BackoffRetryThrowable` `@RetryThrowable` 的变长重试间歇版本，重试间歇至少 100 毫秒。

* `@BackoffRetryFalsy` `@RetryFalsy` 的变长重试间歇版本，重试间歇至少 100 毫秒。

## Fluent 链式调用

除了注解方法使用本组件外，您还可以通过常规 PHP 函数使用。

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), //默认重试所有Throwable
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) //最多重试5次
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```
为了增强可读性，还可以使用如下流畅写法。

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // 当返回false时重试
    ->max(3) // 最多3次
    ->inSeconds(5) // 最长5秒
    ->sleep(1) // 间隔1毫秒
    ->fallback(function(){return true;}) // fallback函数
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
