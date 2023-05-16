# NATS

NATS is an open source, lightweight, high-performance distributed messaging middleware that implements high scalability and an elegant `Publish` / `Subscribe` model, developed using the `Golang` language. The development philosophy of NATS believes that high-quality QoS should be built on the client side, so only `Request-Reply` is established, and it does not provide 1. Persistence 2. Transaction processing 3. Enhanced delivery mode 4. Enterprise-level queue.

## Install

```bash
composer require hyperf/nats
```

## use

### create consumer

```
php bin/hyperf.php gen:nats-consumer DemoConsumer
```

If `queue` is set, the same `subject` will only be consumed by one `queue`. If `queue` is not set, each consumer will receive the message.

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

### Post message

Use publish to deliver messages.

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

Use request to deliver messages.

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

Use requestSync to deliver messages.

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
