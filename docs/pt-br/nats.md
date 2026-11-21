# NATS

O NATS é um middleware de mensageria distribuída open source, leve e de alto desempenho, que oferece alta escalabilidade e um elegante modelo de `Publish` / `Subscribe`, desenvolvido em `Golang`. A filosofia do NATS considera que QoS de alta qualidade deve ser construído do lado do cliente, então ele oferece apenas `Request-Reply` e não fornece: 1. Persistência 2. Processamento de transações 3. Modo de entrega aprimorado 4. Fila de nível enterprise.

## Instalação

```bash
composer require hyperf/nats
```

## Uso

### Criar consumer

```
php bin/hyperf.php gen:nats-consumer DemoConsumer
```

Se `queue` estiver definido, o mesmo `subject` será consumido apenas por uma `queue`. Se `queue` não estiver definido, cada consumer receberá a mensagem.

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
    ```php
        public function consume(Message $payload)
        {
            // Faça algo...
        }
    ```

### Enviar mensagem

Use publish para enviar mensagens.

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

Use request para enviar mensagens.

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

Use requestSync para enviar mensagens.

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
