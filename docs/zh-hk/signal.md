# 信號處理器

信號處理器會監聽 `Worker` 進程和 `自定義` 進程啓動後，自動註冊到信號管理器中。

## 安裝

```
composer require hyperf/signal
```

## 發佈配置

您可以通過下面的命令來發布默認的配置文件到您的項目中：

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## 添加處理器

以下我們監聽 `Worker` 進程的 `SIGTERM` 信號，當收到信號後，打印出信號值。

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

因為 Worker 進程接收的 SIGTERM 信號被捕獲後，無法正常退出，所以用户可以直接 `Ctrl + C` 退出，或者修改 `config/autoload/signal.php` 配置，如下：

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

`WorkerStopHandler` 觸發後，會在所設置的 [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time) 配置時間後，關閉掉當前進程。

## 協程風格服務監聽器配置示例

> 以上默認的監聽器都是適配於異步風格服務的，如果需要在協程風格服務下使用，可以按照以下自定義配置

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
        // 協程風格只會存在一個 Worker 進程，故這裏只需要監聽 WORKER 即可
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // 循環關閉開啓的服務
            $server->shutdown();
        }
    }
}

```
