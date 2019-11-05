# 令牌桶重试器

网络通讯天然是不稳定的，因此在分布式系统中，需要有良好的容错设计。失败重试人人都会，但不是人人都能把重试做好。无差别重试是非常危险的。当通讯出现问题时，每个请求都重试一次，相当于系统负载增加了100%，容易诱发雪崩事故。重试还要考虑错误的原因，如果是无法通过重试解决的问题，那么重试只是浪费资源而已。除此之外，如果重试的接口不具备幂等性，还可能造成数据不一致等问题。

本组件受到[Finagle](https://twitter.github.io/finagle/)启发，提供了令牌桶重试机制，避免雪崩事故发生，同时提供了丰富的重试过滤规则。


## 安装

```bash
composer require hyperf/retry
```

## Hello World

在需要重试的方法上加入注解 `@Retry`。

```php
/**
 * 异常时重试该方法
 * @Retry
 */
public function foo()
{
    // 发起一次远程调用
}
```

## 原理

每一个 `@Retry` 注解处会生成一个对应的令牌桶，每当注解方法被调用时，就在令牌桶中放入一个具有过期时间(ttl)的令牌。如果发生可重试的错误，重试前要消耗掉对应的令牌数量(percentCanRetry)，否则就不会重试（错误继续向下传递）。比如，当percentCanRetry=0.2，则每次重试要消耗5个令牌。如此，遇到对端宕机时，最多只会造成20%的额外重试消耗，对于大多数系统都应该可以接受了。

为了照顾某些使用频率较低的方法，每秒还会生成一定数量的“低保”令牌(minRetriesPerSec)，确保系统稳定。

## 注解配置

Retry的完整注解默认值如下：

```php
class Retry extends AbstractAnnotation
{
    /**
     * The algorithm for retry intervals.
     * @var string
     */
    public $strategy = StrategyInterface::class;

    /**
     * Max Attampts.
     * @var float|int
     */
    public $maxAttempts = INF;

    /**
     * Retry Budget. 
     * ttl: Seconds of token lifetime.
     * minRetriesPerSec: Base retry token generation speed.
     * percentCanRetry: Generate new token at this ratio of the request volume.
     * 
     * @var array
     */
    public $retryBudget = [
        'ttl' => 10,
        'minRetriesPerSec' => 10,
        'percentCanRetry' => 0.2,
    ];

    /**
     * Base time inteval (ms) for each try. For backoff strategy this is the interval for the first try
     * while for flat strategy this is the interval for every try.
     * @var int
     */
    public $base = 0;

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

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, self::class, $this);
    }
}
```

### strategy

提供两种重试间歇策略。等长重试间歇（FlatStrategy）和变长重试间歇（BackoffStrategy）。默认为等长重试间歇。

### retryBudget

```php
public $retryBudget = [
    'ttl' => 10,
    'minRetriesPerSec' => 10,
    'percentCanRetry' => 0.2,
];
```

* `ttl` 令牌过期时间。
* `minRetriesPerSec` 每秒“低保”最少可以重试的次数。
* `percentCanRetry` 重试次数不超过总请求数的百分比。

> 重试组件的令牌桶在worker之间不共享，所以最终的重试次数要乘以worker数量。

### base

等长重试间歇中的间歇时间，变长重试间歇中的第一次重试间歇时间。

### maxAttempts

类型：float|int。单次请求最大重试次数。

### ignoreThrowables

类型：array<string|\Throwable>。无视的 `Throwable` 。优先于 `retryThrowables`。

### retryThrowables

类型：array<string|\Throwable>。需要重试的 `Throwable` 。优先于 `retryOnThrowablePredicate`。

### retryOnThrowablePredicate

类型：callable|string。通过一个函数来判断 `Throwable` 是否可以重试。如果可以重试，请返回true,反之必须返回false。

### retryOnResultPredicate

类型：callable|string。 通过一个函数来判断返回值是否可以重试。如果可以重试，请返回true,反之必须返回false。

## 注解别名

因为重试注解配置较为复杂，这里提供了一些预设的别名便于书写。

* `@RetryThrowable` 只重试 `Throwable`。和默认的Retry相同。

* `@RetryFalsy` 只重试返回值弱等于false（$result == false)的错误，不重试异常。

* `@BackoffRetryThrowable` `@RetryThrowable`的变长重试间歇版本，初次重试间歇100毫秒。

* `@BackoffRetryFalsy` `@RetryFalsy`的变长重试间歇版本，初次重试间歇100毫秒。

建议根据具体业务需要构造自己的注解别名。例如，配置只重试用户自定义的 `TimeoutException` , 并使用变长间歇, 方法如下：

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RetryTimeout extends Hyperf\Retry\Annotation\Retry
{
    public $base = 100;
    public $strategy = \Hyperf\Retry\BackoffStrategy::class;
    public $retryThrowables = [\App\Exception\TimeoutException::class];
}
```

只要确保该文件被Hyperf扫描，就可以在方法中使用 `@RetryTimeout` 注解来重试超时错误了。