# 非同步佇列

非同步佇列區別於 `RabbitMQ` `Kafka` 等訊息佇列，它只提供一種 `非同步處理` 和 `非同步延時處理` 的能力，並 **不能** 嚴格地保證訊息的持久化和 **不支援** ACK 應答機制。

## 安裝

```bash
composer require hyperf/async-queue
```

## 配置

配置檔案位於 `config/autoload/async_queue.php`，如檔案不存在可自行建立。

> 暫時只支援 `Redis Driver` 驅動。

|       配置       |   型別    |                   預設值                    |                  備註                   |
|:----------------:|:---------:|:-------------------------------------------:|:---------------------------------------:|
|      driver      |  string   | Hyperf\AsyncQueue\Driver\RedisDriver::class |                   無                    |
|     channel      |  string   |                    queue                    |                佇列字首                 |
|     timeout      |    int    |                      2                      |            pop 訊息的超時時間            |
|  retry_seconds   | int,array |                      5                      |           失敗後重新嘗試間隔            |
|  handle_timeout  |    int    |                     10                      |            訊息處理超時時間             |
|    processes     |    int    |                      1                      |               消費程序數                |
| concurrent.limit |    int    |                      1                      |             同時處理訊息數              |
|   max_messages   |    int    |                      0                      | 程序重啟所需最大處理的訊息數 預設不重啟 |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 10,
        'processes' => 1,
        'concurrent' => [
            'limit' => 5,
        ],
    ],
];

```

`retry_seconds` 也可以傳入陣列，根據重試次數相應修改重試時間，例如

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'retry_seconds' => [1, 5, 10, 20],
        'processes' => 1,
    ],
];

```

## 使用

### 消費訊息

元件已經提供了預設子程序，只需要將它配置到 `config/autoload/processes.php` 中即可。

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];

```

當然，您也可以將以下 `Process` 新增到自己的專案中。

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

/**
 * @Process(name="async-queue")
 */
class AsyncQueueConsumer extends ConsumerProcess
{
}
```

### 生產訊息

#### 傳統方式

首先我們定義一個訊息類，如下

```php
<?php

declare(strict_types=1);

namespace App\Job;

use Hyperf\AsyncQueue\Job;

class ExampleJob extends Job
{
    public $params;

    public function __construct($params)
    {
        // 這裡最好是普通資料，不要使用攜帶 IO 的物件，比如 PDO 物件
        $this->params = $params;
    }

    public function handle()
    {
        // 根據引數處理具體邏輯
        var_dump($this->params);
    }
}
```

生產訊息

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\ExampleJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * 生產訊息.
     * @param $params 資料
     * @param int $delay 延時時間 單位秒
     */
    public function push($params, int $delay = 0): bool
    {
        // 這裡的 `ExampleJob` 會被序列化存到 Redis 中，所以內部變數最好只傳入普通資料
        // 同理，如果內部使用了註解 @Value 會把對應物件一起序列化，導致訊息體變大。
        // 所以這裡也不推薦使用 `make` 方法來建立 `Job` 物件。
        return $this->driver->push(new ExampleJob($params), $delay);
    }
}
```

#### 註解方式

框架除了傳統方式投遞訊息，還提供了註解方式。

讓我們重寫上述 `QueueService`，直接將 `ExampleJob` 的邏輯搬到 `example` 方法中，具體程式碼如下。

```php
<?php

declare(strict_types=1);

namespace App\Service;

use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

class QueueService
{
    /**
     * @AsyncQueueMessage
     */
    public function example($params)
    {
        // 需要非同步執行的程式碼邏輯
        var_dump($params);
    }
}

```

#### 投遞訊息

根據實際業務場景，動態投遞訊息到非同步佇列執行，我們演示在控制器動態投遞訊息，如下：

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\QueueService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * @AutoController
 */
class QueueController extends Controller
{
    /**
     * @Inject
     * @var QueueService
     */
    protected $service;

    /**
     * 傳統模式投遞訊息
     */
    public function index()
    {
        $this->service->push([
            'group@hyperf.io',
            'https://doc.hyperf.io',
            'https://www.hyperf.io',
        ]);

        return 'success';
    }

    /**
     * 註解模式投遞訊息
     */
    public function example()
    {
        $this->service->example([
            'group@hyperf.io',
            'https://doc.hyperf.io',
            'https://www.hyperf.io',
        ]);

        return 'success';
    }
}
```
