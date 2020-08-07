# 信号处理器

信号处理器会监听 `Worker` 进程和 `自定义` 进程启动后，自动注册到信号管理器中。

## 安装

```
composer require hyperf/signal
```

## 发布配置

您可以通过下面的命令来发布默认的配置文件到您的项目中：

```bash
php bin/hyperf.php vendor:publish hyperf/signal
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

因为 Worker 进程接收的 SIGTERM 信号被捕获后，无法正常退出，所以用户可以直接 `Ctrl + C` 退出，或者修改 `config/autoload/signal.php` 配置，如下：

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

`WorkerStopHandler` 触发后，会在所设置的 [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time) 配置时间后，关闭掉当前进程。
