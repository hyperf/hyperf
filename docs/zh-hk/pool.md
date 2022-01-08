# 連接池

## 安裝

```bash
composer require hyperf/pool
```

## 為什麼需要連接池？

當併發量很低的時候，連接可以臨時建立，但當服務吞吐達到幾百、幾千的時候，頻繁 `建立連接 Connect` 和 `銷燬連接 Close` 就有可能會成為服務的一個瓶頸，那麼當服務啟動的時候，先建立好若干個連接並存放於一個隊列中，當需要使用時從隊列中取出一個並使用，使用完後再反還到隊列去，而對這個隊列數據結構進行維護的，就是連接池。

## 使用連接池

對於 Hyperf 官方提供的組件，都是已經對接好連接池的，在使用上無任何的感知，底層自動完成連接的取用和歸還。

## 自定義連接池

定義一個連接池首先需要實現一個繼承了 `Hyperf\Pool\Pool` 的子類並實現抽象方法 `createConnection`，並返回一個實現了 `Hyperf\Contract\ConnectionInterface` 接口的對象，這樣您創建的連接池對象就已經完成了，如下示例：
```php
<?php
namespace App\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;

class MyConnectionPool extends Pool
{
    public function createConnection(): ConnectionInterface
    {
        return new MyConnection();
    }
}
``` 
這樣便可以通過對實例化後的 `MyConnectionPool` 對象調用 `get(): ConnectionInterface` 和 `release(ConnectionInterface $connection): void` 方法執行連接的取用和歸還了。   

## SimplePool

這裏框架提供了一個非常簡單的連接池實現。

```php
<?php

use Hyperf\Pool\SimplePool\PoolFactory;
use Swoole\Coroutine\Http\Client;

$factory = $container->get(PoolFactory::class);

$pool = $factory->get('your pool name', function () use ($host, $port, $ssl) {
    return new Client($host, $port, $ssl);
}, [
    'max_connections' => 50
]);

$connection = $pool->get();

$client = $connection->getConnection(); // 即上述 Client.

// Do something.

$connection->release();

```

## 低頻組件

連接池內置 `LowFrequencyInterface` 接口，默認使用的低頻組件，會根據從連接池中獲取連接的頻率，來決定是否釋放連接池中多餘的連接。

如果我們需要替換對應的低頻組件，可以直接在 `dependencies` 配置中替換，以下以數據庫組件為例。

```php
<?php

declare(strict_types=1);

namespace App\Pool;

class Frequency extends \Hyperf\Pool\Frequency
{
    /**
     * 被計算頻率的時間間隔
     */
    protected int $time = 10;

    /**
     * 觸發低頻的頻率
     */
    protected int $lowFrequency = 5;

    /**
     * 連續觸發低頻的最小時間間隔
     */
    protected int $lowFrequencyInterval = 60;
}

```

修改映射關係如下

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### 恆定頻率

當然，框架還提供了另外一種低頻組件 `ConstantFrequency`。

當此組件實例化後，會啟動定時器，在固定間隔調用 `Pool::flushOne(false)` 方法，此方法會拿出連接池中的一個連接，並判斷超過閒置時長後，銷燬連接。

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
