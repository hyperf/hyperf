# 异步队列

异步队列区别于 `RabbitMQ` `Kafka` 等消息队列，它只提供一种 `异步处理` 和 `异步延时处理` 的能力，并 **不能** 严格地保证消息的持久化和 **不支持** ACK 应答机制。

## 安装

```bash
composer require hyperf/async-queue
```

## 配置

配置文件位于 `config/autoload/async_queue.php`，如文件不存在可自行创建。

> 暂时只支持 `Redis Driver` 驱动。

|       配置       |   类型    |                   默认值                    |                  备注                   |
|:----------------:|:---------:|:-------------------------------------------:|:---------------------------------------:|
|      driver      |  string   | Hyperf\AsyncQueue\Driver\RedisDriver::class |                   无                    |
|     channel      |  string   |                    queue                    |                队列前缀                 |
|    redis.pool    |  string   |                    default                  |                redis 连接池              |
|     timeout      |    int    |                      2                      |           pop 消息的超时时间            |
|  retry_seconds   | int,array |                      5                      |           失败后重新尝试间隔            |
|  handle_timeout  |    int    |                     10                      |            消息处理超时时间             |
|    processes     |    int    |                      1                      |               消费进程数                |
| concurrent.limit |    int    |                      1                      |             同时处理消息数              |
|   max_messages   |    int    |                      0                      | 进程重启所需最大处理的消息数 默认不重启 |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'redis' => [
            'pool' => 'default'
        ],
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

### 生产消息

#### 传统方式

这种模式会把对象直接序列化然后存到 `Redis` 等队列中，所以为了保证序列化后的体积，尽量不要将 `Container`，`Config` 等设置为成员变量。

比如以下 `Job` 的定义，是 **不可取** 的

```php
<?php

declare(strict_types=1);

namespace App\Job;

use Hyperf\AsyncQueue\Job;
use Psr\Container\ContainerInterface;

class ExampleJob extends Job
{
    public $container;

    public $params;

    public function __construct(ContainerInterface $container, $params)
    {
        $this->container = $container;
        $this->params = $params;
    }

    public function handle()
    {
        // 根据参数处理具体逻辑
        var_dump($this->params);
    }
}

$job = make(ExampleJob::class);
```

正确的 `Job` 应该是只有需要处理的数据，其他相关数据，可以在 `handle` 方法中重新获取，如下。

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
        // 通过具体参数获取模型等
        var_dump($this->params);
    }
}
```

正确定义完 `Job` 后，我们需要写一个专门投递消息的 `Service`，代码如下。

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
     * 生产消息.
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

投递消息

接下来，调用我们的 `QueueService` 投递消息即可。

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
     * 传统模式投递消息
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
}
```

#### 注解方式

框架除了传统方式投递消息，还提供了注解方式。

让我们重写上述 `QueueService`，直接将 `ExampleJob` 的逻辑搬到 `example` 方法中，并加上对应注解 `AsyncQueueMessage`，具体代码如下。

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
        // 需要异步执行的代码逻辑
        var_dump($params);
    }
}

```

投递消息

注解模式投递消息就跟平常调用方法一致，代码如下。

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
     * 注解模式投递消息
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

## 事件

|   事件名称   |        触发时机         |                         备注                         |
|:------------:|:-----------------------:|:----------------------------------------------------:|
| BeforeHandle |     处理消息前触发      |                                                      |
| AfterHandle  |     处理消息后触发      |                                                      |
| FailedHandle |   处理消息失败后触发    |                                                      |
| RetryHandle  |   重试处理消息前触发    |                                                      |
| QueueLength  | 每处理 500 个消息后触发 | 用户可以监听此事件，判断失败或超时队列是否有消息积压 |

### QueueLengthListener

框架自带了一个记录队列长度的监听器，默认不开启，您如果需要，可以自行添加到 `listeners` 配置中。

```php
<?php

declare(strict_types=1);

return [
    Hyperf\AsyncQueue\Listener\QueueLengthListener::class
];
```

## 任务执行流转流程

任务执行流转流程主要包括以下几个队列:

|  队列名  |                   备注                    |
|:--------:|:-----------------------------------------:|
| waiting  |              等待消费的队列               |
| reserved |              正在消费的队列               |
| delayed  |              延迟消费的队列               |
|  failed  |              消费失败的队列               |
| timeout  | 消费超时的队列 (虽然超时，但可能执行成功) |

队列流转顺序如下: 

```mermaid
graph LR;
A[投递延时消息]-->C[delayed队列];
B[投递消息]-->D[waiting队列];
C--到期-->D;
D--消费-->E[reserved队列];
E--成功-->F[删除消息];
E--失败-->G[failed队列];
E--超时-->H[timeout队列];
```

## 配置多个异步队列

当您需要使用多个队列来区分消费高频和低频或其他种类的消息时，可以配置多个队列。

1. 添加配置

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => '{queue}',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 10,
        'processes' => 1,
        'concurrent' => [
            'limit' => 2,
        ],
    ],
    'other' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => '{other.queue}',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 10,
        'processes' => 1,
        'concurrent' => [
            'limit' => 2,
        ],
    ],
];

```

2. 添加消费进程

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

/**
 * @Process()
 */
class OtherConsumerProcess extends ConsumerProcess
{
    /**
     * @var string
     */
    protected $queue = 'other';
}
```

3. 调用

```php
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Utils\ApplicationContext;

$driver = ApplicationContext::getContainer()->get(DriverFactory::class)->get('other');
return $driver->push(new ExampleJob());
```

## 安全关闭

异步队列在终止时，如果正在进行消费逻辑，可能会导致出现错误。框架提供了 `DriverStopHandler` ，可以让异步队列进程安全关闭。

安装信号处理器

```
composer require hyperf/signal
```

添加配置

```php
<?php

declare(strict_types=1);

return [
    'handlers' => [
        Hyperf\AsyncQueue\Signal\DriverStopHandler::class,
    ],
    'timeout' => 5.0,
];

```
