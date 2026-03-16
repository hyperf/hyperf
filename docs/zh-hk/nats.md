# NATS

NATS 是一個開源、輕量級、高性能的分佈式消息中間件，實現了高可伸縮性和優雅的 `Publish` / `Subscribe` 模型，使用 `Golang` 語言開發。NATS 的開發哲學認為高質量的 QoS 應該在客户端構建，故只建立了 `Request-Reply`，不提供 1. 持久化 2. 事務處理 3. 增強的交付模式 4. 企業級隊列。

## 安裝

```bash
composer require hyperf/nats
```

## 使用

### 創建消費者

```
php bin/hyperf.php gen:nats-consumer DemoConsumer
```

如果設置了 `queue`，則相同的 `subject` 只會被一個 `queue` 消費。若不設置 `queue`，則每個消費者都會收到消息。

```php
<?php

declare(strict_types=1);

namespace App\Nats\Consumer;

use Hyperf\Nats\AbstractConsumer;
use Hyperf\Nats\Annotation\Consumer;
use Hyperf\Nats\Message;

#[Consumer(subject: 'hyperf.demo', queue: 'hyperf.demo', name: 'DemoConsumer', nums: 1)]
class DemoConsumer extends AbstractConsumer
{
    public function consume(Message $payload)
    {
        // Do something...
    }
}
```

### 投遞消息

使用 publish 投遞消息。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Nats\Driver\DriverInterface;

#[AutoController(prefix: "nats")]
class NatsController extends AbstractController
{
    #[Inject]
    protected DriverInterface $nats;

    public function publish()
    {
        $res = $this->nats->publish('hyperf.demo', [
            'id' => 'Hyperf',
        ]);

        return $this->response->success($res);
    }
}

```

使用 request 投遞消息。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Nats\Driver\DriverInterface;
use Hyperf\Nats\Message;

#[AutoController(prefix: "nats")]
class NatsController extends AbstractController
{
    #[Inject]
    protected DriverInterface $nats;

    public function request()
    {
        $res = $this->nats->request('hyperf.reply', [
            'id' => 'limx',
        ], function (Message $payload) {
            var_dump($payload->getBody());
        });

        return $this->response->success($res);
    }
}

```

使用 requestSync 投遞消息。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Nats\Driver\DriverInterface;
use Hyperf\Nats\Message;

#[AutoController(prefix: "nats")]
class NatsController extends AbstractController
{
    #[Inject]
    protected DriverInterface $nats;

    public function sync()
    {
        /** @var Message $message */
        $message = $this->nats->requestSync('hyperf.reply', [
            'id' => 'limx',
        ]);

        return $this->response->success($message->getBody());
    }
}

```
