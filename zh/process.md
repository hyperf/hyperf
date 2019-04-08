# 子进程

[hyperf/process](https://github.com/hyperf-cloud/process) 可以添加一个用户自定义的工作进程，此函数通常用于创建一个特殊的工作进程，用于监控、上报或者其他特殊的任务。在 Server 启动时会自动创建进程，并执行指定的子进程函数，进程意外退出时，Server 会重新拉起进程。

## 使用

我们创建一个用于监控失败队列数量的子进程，当失败队列有数据时，报出警告。

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Contract\StdoutLoggerInterface;

/**
 * @Process(name="demo_process")
 */
class DemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);

        while (true) {
            $redis = $this->container->get(\Redis::class);
            $count = $redis->llen('queue:failed');

            if ($count > 0) {
                $logger->warning('The num of failed queue is ' . $count);
            }

            sleep(1);
        }
    }

    /**
     * 进程是否启动
     */
    public function isEnable(): bool
    {
        return true;
    }
}

```

当然，除了注解模式，我们还提供了 `processes.php` 配置。

```php
// processes.php
return [
    App\Process\DemoProcess::class,
];
```