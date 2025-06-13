# 熔斷器

## 安裝

```
composer require hyperf/circuit-breaker
```

## 為什麼要熔斷？

分散式系統中經常會出現由於某個基礎服務不可用造成整個系統不可用的情況，這種現象被稱為服務雪崩效應。為了應對服務雪崩，一種常見的做法是服務降級。而 [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) 元件，就是為了來解決這個問題的。

## 使用熔斷器

熔斷器的使用十分簡單，只需要加入 `Hyperf\CircuitBreaker\Annotation\CircuitBreaker` 註解，就可以根據規定策略，進行熔斷。
比如我們需要到另外服務中查詢使用者列表，使用者列表需要關聯很多的表，查詢效率較低，但平常併發量不高的時候，響應速度還說得過去。一旦併發量激增，就會導致響應速度變慢，並會使對方服務出現慢查。這個時候，我們只需要配置一下熔斷超時時間 `timeout` 為 0.05 秒，失敗計數 `failCounter` 超過 1 次後熔斷，相應 `fallback` 為 `App\Service\UserService` 類的 `searchFallback` 方法。這樣當響應超時並觸發熔斷後，就不會再請求對端的服務了，而是直接將服務降級從當前專案中返回資料，即根據 `fallback` 指定的方法來進行返回。

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

預設熔斷策略為 `超時策略` ，如果您想要自己實現熔斷策略，只需要自己實現 `Handler` 繼承於 `Hyperf\CircuitBreaker\Handler\AbstractHandler` 即可。

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
