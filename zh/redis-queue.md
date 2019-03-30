# 异步队列

异步队列区别于 `RabbitMQ` `Kafka` 等消息队列，它只提供一种异步处理和异步延时处理的能力。

## 安装

```bash
composer require hyperf/queue
```

## 配置

暂时只支持 `Redis Driver`。

|     配置      |  类型  |                 默认值                 |        备注        |
|:-------------:|:------:|:--------------------------------------:|:------------------:|
|    driver     | string | Hyperf\Queue\Driver\RedisDriver::class |         无         |
|    channel    | string |                 queue                  |      队列前缀      |
| retry_seconds |  int   |                   5                    | 失败后重新尝试间隔 |
|   processes   |  int   |                   1                    |     消费进程数     |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\Queue\Driver\RedisDriver::class,
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
    Hyperf\Queue\Process\ConsumerProcess::class,
];

```

### 发布消息

首先我们定义一个消息，如下

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use Hyperf\Queue\Job;

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
use Hyperf\Queue\Driver\DriverFactory;

class DemoService
{
    protected $driver;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->driver = $container->get(DriverFactory::class)->default;
    }

    public function publish()
    {
        return $this->driver->push(new ExampleJon());
    }
}

```