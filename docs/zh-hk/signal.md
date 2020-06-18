# 信號處理器

信號處理器會監聽 `Worker` 進程和 `自定義` 進程啟動後，自動註冊到信號管理器中。

## 安裝

```
composer require hyperf/signal
```

## 添加處理器

以下我們監聽 `Worker` 進程的 `SIGTERM` 信號，當收到信號後，打印出信號值。

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

因為 Worker 進程 SIGTERM 信號被捕獲後，無法正常退出，所以用户可以直接 Ctrl+C 退出，或者修改 signal.php 配置

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

`WorkerStopHandler` 觸發後，會在 `max_wait_time` 時間後，關掉當前進程。
