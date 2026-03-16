# 訊號處理器

訊號處理器會監聽 `Worker` 程序和 `自定義` 程序啟動後，自動註冊到訊號管理器中。

## 安裝

```
composer require hyperf/signal
```

## 釋出配置

您可以透過下面的命令來發布預設的配置檔案到您的專案中：

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## 新增處理器

以下我們監聽 `Worker` 程序的 `SIGTERM` 訊號，當收到訊號後，打印出訊號值。

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

因為 Worker 程序接收的 SIGTERM 訊號被捕獲後，無法正常退出，所以使用者可以直接 `Ctrl + C` 退出，或者修改 `config/autoload/signal.php` 配置，如下：

> WorkerStopHandler 不適配於 CoroutineServer，如有需要請自行實現

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

`WorkerStopHandler` 觸發後，會在所設定的 [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time) 配置時間後，關閉掉當前程序。

## 協程風格服務監聽器配置示例

> 以上預設的監聽器都是適配於非同步風格服務的，如果需要在協程風格服務下使用，可以按照以下自定義配置

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
        // 協程風格只會存在一個 Worker 程序，故這裡只需要監聽 WORKER 即可
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // 迴圈關閉開啟的服務
            $server->shutdown();
        }
    }
}

```
