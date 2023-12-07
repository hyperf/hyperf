# Signal handler

The signal handler will listen to the `Worker` process and the `custom` process and automatically register with the signal manager after it starts.

## Install

```
composer require hyperf/signal
```

## Publish configuration

You can publish the default configuration file to your project with the following command:

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## Add handler

Below we listen to the `SIGTERM` signal of the `Worker` process, and print the signal value when the signal is received.

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

Because the SIGTERM signal received by the Worker process is captured, it cannot exit normally, so the user can directly `Ctrl + C` to exit, or modify the `config/autoload/signal.php` configuration as follows:

> WorkerStopHandler is not suitable for CoroutineServer, please implement it yourself if necessary

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

After the `WorkerStopHandler` is triggered, it will close the current process after the set [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time) configuration time.

## Coroutine style service listener configuration example

> The above default listeners are all adapted to asynchronous style services. If you need to use them in coroutine style services, you can customize the configuration as follows

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
        // There is only one Worker process in the coroutine style, so you only need to monitor the WORKER here.
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // Cyclically close open services
            $server->shutdown();
        }
    }
}

```
