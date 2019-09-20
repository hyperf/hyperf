# 异步队列

异步队列区别于 `RabbitMQ` `Kafka` 等消息队列，它只提供一种 `异步处理` 和 `异步延时处理` 的能力，并不能严格地保证消息的持久化和支持 `ACK 应答机制`。

## 安装

```bash
composer require hyperf/async-queue
```

## 配置

配置文件位于 `config/autoload/async_queue.php`，如文件不存在可自行创建。

> 暂时只支持 `Redis Driver` 驱动。

|     配置      |   类型    |                   默认值                    |        备注        |
|:-------------:|:---------:|:-------------------------------------------:|:------------------:|
|    driver     |  string   | Hyperf\AsyncQueue\Driver\RedisDriver::class |         无         |
|    channel    |  string   |                    queue                    |      队列前缀      |
| retry_seconds | int,array |                      5                      | 失败后重新尝试间隔 |
|   processes   |    int    |                      1                      |     消费进程数     |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'retry_seconds' => 5,
        'processes' => 1,
    ],
];

```

`retry_seconds` 也可以传入数组，根据重试次数相应修改重试时间，例如

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

### 消费消息

组件已经提供了默认子进程，只需要将它配置到 `config/autoload/processes.php` 中即可。

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];

```

当然，您也可以将以下 `Process` 添加到自己的项目中。

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

### 发布消息

首先我们定义一个消息，如下

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
        // 这里最好是普通数据，不要使用携带 IO 的对象，比如 PDO 对象
        $this->params = $params;
    }

    public function handle()
    {
        // 根据参数处理具体逻辑
        var_dump($this->params);
    }
}
```

发布消息

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
     * 投递消息.
     * @param $params 数据
     * @param int $delay 延时时间 单位秒
     */
    public function push($params, int $delay = 0): bool
    {
        // 这里的 `ExampleJob` 会被序列化存到 Redis 中，所以内部变量最好只传入普通数据
        // 同理，如果内部使用了注解 @Value 会把对应对象一起序列化，导致消息体变大。
        // 所以这里也不推荐使用 `make` 方法来创建 `Job` 对象。
        return $this->driver->push(new ExampleJob($params), $delay);
    }
}
```

根据实际业务场景，动态投递消息到异步队列执行，我们演示在控制器动态投递消息，如下：

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

    public function index()
    {
        $this->service->push([
            'group@hyperf.io',
            'https://doc.hyperf.io',
            'https://www.hyperf.io',
        ]);

        return 'success';
    }
}
```
