# Signal Handler

The signal handler automatically registers with the signal manager after the `Worker` process and `Custom` process are started.

## Installation

```
composer require hyperf/signal
```

## Publish Configuration

You can publish the default configuration file to your project using the following command:

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## Adding Handlers

Below, we listen for the `SIGTERM` signal of the `Worker` process. When the signal is received, we print the signal value.

```php
<?php

declare(strict_types=1);

namespace App\Signal;

use Hyperf\Signal\Annotation\Signal;
use Hyperf\Signal\SignalHandlerInterface;

#[Signal]
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

Since the `SIGTERM` signal received by the Worker process cannot be exited normally after being caught, users can directly `Ctrl + C` to exit, or modify the `config/autoload/signal.php` configuration as follows:

> WorkerStopHandler is not suitable for CoroutineServer. If needed, please implement it yourself.

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

After the `WorkerStopHandler` is triggered, the current process will be closed after the [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time) configuration time is exceeded.

## Configuration Example for Coroutine-style Server Listener

> The default listeners above are adapted to asynchronous-style services. If you need to use them in a coroutine-style service, you can follow the custom configuration below:

```php
<?php

declare(strict_types=1);

namespace App\Kernel\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Server\ServerManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;

class CoroutineServerStopHandler implements SignalHandlerInterface
{

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        // There will only be one Worker process in the coroutine style, so we only need to listen to WORKER here.
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // Loop through and shut down started services
            $server->shutdown();
        }
    }
}
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

#[Signal]
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

> WorkerStopHandler 不适配于 CoroutineServer，如有需要请自行实现

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

## 协程风格服务监听器配置示例

> 以上默认的监听器都是适配于异步风格服务的，如果需要在协程风格服务下使用，可以按照以下自定义配置

```php
<?php

declare(strict_types=1);

namespace App\Kernel\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Server\ServerManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;

class CoroutineServerStopHandler implements SignalHandlerInterface
{

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        // 协程风格只会存在一个 Worker 进程，故这里只需要监听 WORKER 即可
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // 循环关闭开启的服务
            $server->shutdown();
        }
    }
}

```
