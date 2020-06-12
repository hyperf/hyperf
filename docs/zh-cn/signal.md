# 信号处理器

信号处理器会监听 `Worker` 进程和 `自定义` 进程启动后，自动注册到信号管理器中。

## 安装

```
composer require hyperf/signal
```

## 添加处理器

以下我们监听 `Worker` 进程的 `SIGTERM` 信号，当收到信号后，打印出信号值。

```php
<?php

declare(strict_types=1);

namespace App\Signal;

use Hyperf\Signal\Annotation\Signal;
use Hyperf\Signal\SignalHandlerInterface;

/**
 * @Signal
 */
class TermSignalHandler implements SignalHandlerInterface
{
    public function listen(): array
    {
        return [
            [SignalHandlerInterface::WORKER, SIGTERM],
        ];
    }

    public function handle(int $signal): void
    {
        var_dump($signal);
    }
}

```

因为 Worker 进程 SIGTERM 信号被捕获后，无法正常退出，所以用户可以直接 Ctrl+C 退出，或者修改 signal.php 配置

```php
<?php

declare(strict_types=1);

return [
    'handlers' => [
        Hyperf\Signal\Handler\WorkerStopHandler::class => PHP_INT_MIN
    ],
    'timeout' => 5.0,
];

```

`WorkerStopHandler` 触发后，会在 `max_wait_time` 时间后，关掉当前进程。
