# 重試

網絡通訊天然是不穩定的，因此在分佈式系統中，需要有良好的容錯設計。無差別重試是非常危險的。當通訊出現問題時，每個請求都重試一次，相當於系統 IO 負載增加了 100%，容易誘發雪崩事故。重試還要考慮錯誤的原因，如果是無法通過重試解決的問題，那麼重試只是浪費資源而已。除此之外，如果重試的接口不具備冪等性，還可能造成數據不一致等問題。

本組件提供了豐富的重試機制，可以滿足多種場景的重試需求。


## 安裝

```bash
composer require hyperf/retry
```

## Hello World

在需要重試的方法上加入註解 `#[Retry]`。

```php
/**
 * 異常時重試該方法
 */
#[Retry]
public function foo()
{
    // 發起一次遠程調用
}
```

默認的 Retry 策略可以滿足大部分日常重試需求，且不會過度重試導致雪崩。

## 深度定製

本組件通過組合多種重試策略實現了可插拔性。每個策略關注重試過程中的不同側面，如重試判斷、重試間隔，結果處理等。通過調整註解中使用的策略就可以配置出適配任意場景下的重試切面。

建議根據具體業務需要構造自己的註解別名。下面我們演示如何製作最大嘗試次數為 3 的新註解。

> 在默認的 `Retry` 註解中，您可以通過 `@Retry(maxAttempts=3)` 來控制最大重試次數。為了演示需要，先假裝它不存在。

首先您要新建一個 `註解類` 並繼承 `\Hyperf\Retry\Annotations\AbstractRetry` 。

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

根據您的需要，重寫 `$policies` 屬性。限制重試次數，需要使用 `MaxAttemptsRetryPolicy` 。`MaxAttemptsRetryPolicy` 還需要一個參數，那就是最大嘗試的次數限制，`$maxAttempts` 。把這兩個屬性加入上述的類中。

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

現在 `@MyRetry` 這個註解會導致任何方法都會被循環執行三次，我們還需要加入一個新的策略 `ClassifierRetryPolicy` 來控制什麼樣的錯誤才能被重試。加入 `ClassifierRetryPolicy` 後默認只會在拋出 `Throwable` 後重試。

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

您可以繼續完善該註解，直到該註解滿足您定製化的需求。例如，配置只重試用户自定義的 `TimeoutException` , 並使用重試至少休眠 100 毫秒的變長間歇, 方法如下：

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

只要確保該文件被 Hyperf 掃描，就可以在方法中使用 `@MyRetry` 註解來重試超時錯誤了。

## 默認配置

`@Retry` 的完整註解默認屬性如下：

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

## 可選策略

### 最大嘗試次數策略 `MaxAttemptsRetryPolicy`

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| maxAttempts |  int | 最多嘗試的次數 |


### 錯誤分類策略 `ClassifierRetryPolicy`

通過分類器來判斷錯誤是否可以重試。

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| ignoreThrowables |  array | 無視的 `Throwable` 類名 。優先於 `retryThrowables` |
| retryThrowables | array | 需要重試的 `Throwable` 類名 。優先於 `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | 通過一個函數來判斷 `Throwable` 是否可以重試。如果可以重試，請返回 true, 反之必須返回 false。 |
| retryOnResultPredicate | callable | 通過一個函數來判斷返回值是否可以重試。如果可以重試，請返回 true，反之必須返回 false。 |

### 回退策略 `FallbackRetryPolicy`

重試資源耗盡後執行備選方法。

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| fallback |  callable | fallback 方法 |

`fallback` 除了可以填寫被 `is_callable` 識別的代碼外，還可以填寫形如 `class@method` 的格式，框架會從 `Container` 中拿到對應的 `class`，然後執行其 `method` 方法。

### 睡眠策略 `SleepRetryPolicy`

提供兩種重試間歇策略。等長重試間歇（FlatStrategy）和變長重試間歇（BackoffStrategy）。

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| base |  int | 基礎睡眠時間（毫秒）|
| strategy |  string | 任何實現了 `Hyperf\Retry\SleepStrategyInterface` 的類名，如 `Hyperf\Retry\BackoffStrategy` |

### 超時策略 `TimeoutRetryPolicy`

執行總時長超過時間後退出重試會話。

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| timeout |  float | 超時時間（秒）|

### 熔斷策略 `CircuitBreakerRetryPolicy`

重試失敗退出重試會話後一段時間內直接標記為熔斷，不再進行任何嘗試。

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout |  float | 恢復所需時間（秒）|

### 預算策略 `BudgetRetryPolicy`

每一個 `@Retry` 註解處會生成一個對應的令牌桶，每當註解方法被調用時，就在令牌桶中放入一個具有過期時間(ttl)的令牌。如果發生可重試的錯誤，重試前要消耗掉對應的令牌數量(percentCanRetry)，否則就不會重試（錯誤繼續向下傳遞）。比如，當 percentCanRetry=0.2，則每次重試要消耗 5 個令牌。如此，遇到對端宕機時，最多隻會造成 20% 的額外重試消耗，對於大多數系統都應該可以接受了。

為了照顧某些使用頻率較低的方法，每秒還會生成一定數量的“低保”令牌(minRetriesPerSec)，確保系統穩定。

|    參數    | 類型 | 説明 |
| ---------- | --- | --- |
| retryBudget.ttl |  int | 恢令牌過期時間（秒）|
| retryBudget.minRetriesPerSec |  int | 每秒“低保”最少可以重試的次數|
| retryBudget.percentCanRetry |  float | 重試次數不超過總請求數的百分比 |

> 重試組件的令牌桶在 worker 之間不共享，所以最終的重試次數要乘以 worker 數量。

## 註解別名

因為重試註解配置較為複雜，這裏提供了一些預設的別名便於書寫。

* `@RetryThrowable` 只重試 `Throwable`。和默認的 `@Retry` 相同。

* `@RetryFalsy` 只重試返回值弱等於 false（$result == false)的錯誤，不重試異常。

* `@BackoffRetryThrowable` `@RetryThrowable` 的變長重試間歇版本，重試間歇至少 100 毫秒。

* `@BackoffRetryFalsy` `@RetryFalsy` 的變長重試間歇版本，重試間歇至少 100 毫秒。

## Fluent 鏈式調用

除了註解方法使用本組件外，您還可以通過常規 PHP 函數使用。

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), //默認重試所有Throwable
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) //最多重試5次
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```
為了增強可讀性，還可以使用如下流暢寫法。

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // 當返回false時重試
    ->max(3) // 最多3次
    ->inSeconds(5) // 最長5秒
    ->sleep(1) // 間隔1毫秒
    ->fallback(function(){return true;}) // fallback函數
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
