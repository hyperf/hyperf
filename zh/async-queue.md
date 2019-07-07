# 异步队列

异步队列区别于 `RabbitMQ` `Kafka` 等消息队列，它只提供一种 `异步处理` 和 `异步延时处理` 的能力，并不能严格地保证消息的持久化和支持 `ACK 应答机制`。

## 安装

```bash
composer require hyperf/async-queue
```

## 配置

配置文件位于 `config/autoload/async_queue.php`，如文件不存在可自行创建。

> 暂时只支持 `Redis Driver` 驱动。

|     配置      |  类型  |                   默认值                    |        备注        |
|:-------------:|:------:|:-------------------------------------------:|:------------------:|
|    driver     | string | Hyperf\AsyncQueue\Driver\RedisDriver::class |         无         |
|    channel    | string |                    queue                    |      队列前缀      |
| retry_seconds |  int   |                      5                      | 失败后重新尝试间隔 |
|   processes   |  int   |                      1                      |     消费进程数     |

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

## 使用

### 消费消息

组件已经提供了默认子进程，只需要将子进程配置到 `processes.php` 中即可。

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];

```

### 发布消息

首先我们定义一个消息，如下

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use Hyperf\AsyncQueue\Job;

class ExampleJob extends Job
{
    public function handle()
    {
        var_dump('hello world');
    }
}

```

发布消息

```php
<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class DemoService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    public function publish()
    {
        // 发布消息
        // 这里的 ExampleJob 是直接实例化出来的，所以不能在 Job 内使用 @Inject @Value 等注解及注解所对应功能的其它使用方式
        return $this->driver->push(new ExampleJob());
    }

    public function delay()
    {
        // 发布延迟消息
        // 第二个参数 $delay 即为延迟的秒数
        return $this->driver->push(new ExampleJob(), 60);
    }
}

```
