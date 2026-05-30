# NATS

NATS adalah middleware distributed messaging open-source, ringan, dan
berperforma tinggi yang mengimplementasikan skalabilitas tinggi serta model
`Publish` / `Subscribe` yang elegan, dikembangkan menggunakan bahasa `Golang`.
Filosofi pengembangan NATS meyakini bahwa QoS berkualitas tinggi harus dibangun
di sisi klien, sehingga hanya `Request-Reply` yang disediakan, dan NATS tidak
menyediakan 1. Persistence 2. Pemrosesan transaksi 3. Mode pengiriman yang
ditingkatkan 4. Antrean tingkat enterprise.

## Instalasi

```bash
composer require hyperf/nats
```

## Penggunaan

### Membuat Consumer

```
php bin/hyperf.php gen:nats-consumer DemoConsumer
```

Jika `queue` diatur, `subject` yang sama hanya akan dikonsumsi oleh satu
`queue`. Jika `queue` tidak diatur, setiap consumer akan menerima pesan.

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

### Mengirim Pesan

Gunakan publish untuk mengirimkan pesan.

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

Gunakan request untuk mengirimkan pesan.

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

Gunakan requestSync untuk mengirimkan pesan.

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
