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

组件已经提供了默认子进程，只需要将它配置到 `processes.php` 中即可。

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
    protected $params ;

    function __construct($params)
    {
        // 可接受外部参数
        $this->params=$params ;

    }
    public function handle()
    {
        // 根据参数处理业务
        var_dump($this->params);
    }
}

```

发布消息

```php
<?php

declare(strict_types=1);

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

    public function publish($params)
    {
        // 发布消息
        // 这里的 ExampleJob 是直接实例化出来的，所以不能在 Job 内使用 @Inject @Value 等注解及注解所对应功能的其它使用方式
        return $this->driver->push(new ExampleJob($params));
    }

    public function delay($params)
    {
        // 发布延迟消息
        // 第二个参数 $delay 即为延迟的秒数
        return $this->driver->push(new ExampleJob($params), 60);
    }
}

```

### 动态添加

根据实际业务场景，动态投递消息到异步队列执行，我们演示在控制器动态投递消息，如下：

```php
<?php

declare(strict_types=1);

namespace App\Controller\Foo;

use App\Controller\Controller;
use App\Service\DemoService ;

class FooController extends Controller
{
    protected $DemoService ;

    function __construct(DemoService $DemoService)
    {
        $this->DemoService=$DemoService ;

    }
    // 动态添加异步任务到队列，添加后会立即执行。
    function  asyncJobs(){
        
        $params=array(
            'group@hyperf.io', 'https://doc.hyperf.io', 'https://www.hyperf.io'
        );
       $this->DemoService->publish($params);
    }
}

```
