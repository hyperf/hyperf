# 連線池

## 安裝

```bash
composer require hyperf/pool
```

## 為什麼需要連線池？

當併發量很低的時候，連線可以臨時建立，但當服務吞吐達到幾百、幾千的時候，頻繁 `建立連線 Connect` 和 `銷燬連線 Close` 就有可能會成為服務的一個瓶頸，那麼當服務啟動的時候，先建立好若干個連線並存放於一個佇列中，當需要使用時從佇列中取出一個並使用，使用完後再反還到佇列去，而對這個佇列資料結構進行維護的，就是連線池。

## 使用連線池

對於 Hyperf 官方提供的元件，都是已經對接好連線池的，在使用上無任何的感知，底層自動完成連線的取用和歸還。

## 自定義連線池

定義一個連線池首先需要實現一個繼承了 `Hyperf\Pool\Pool` 的子類並實現抽象方法 `createConnection`，並返回一個實現了 `Hyperf\Contract\ConnectionInterface` 介面的物件，這樣您建立的連線池物件就已經完成了，如下示例：
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
這樣便可以通過對例項化後的 `MyConnectionPool` 物件呼叫 `get(): ConnectionInterface` 和 `release(ConnectionInterface $connection): void` 方法執行連線的取用和歸還了。   

## SimplePool

這裡框架提供了一個非常簡單的連線池實現。

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

## 低頻元件

連線池內建 `LowFrequencyInterface` 介面，預設使用的低頻元件，會根據從連線池中獲取連線的頻率，來決定是否釋放連線池中多餘的連線。

如果我們需要替換對應的低頻元件，可以直接在 `dependencies` 配置中替換，以下以資料庫元件為例。

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

修改對映關係如下

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => App\Pool\Frequency::class,
];
```

### 恆定頻率

當然，框架還提供了另外一種低頻元件 `ConstantFrequency`。

當此元件例項化後，會啟動定時器，在固定間隔呼叫 `Pool::flushOne(false)` 方法，此方法會拿出連線池中的一個連線，並判斷超過閒置時長後，銷燬連線。

```php
<?php
return [
    Hyperf\DbConnection\Frequency::class => Hyperf\Pool\ConstantFrequency::class,
];
```
