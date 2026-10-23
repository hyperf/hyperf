# NATS

NATS é um middleware de mensagens distribuído open source, leve e de alto desempenho, que implementa alta escalabilidade e um modelo elegante de `Publish`/`Subscribe`, desenvolvido usando a linguagem `Golang`. A filosofia de desenvolvimento do NATS acredita que uma QoS de alta qualidade deve ser construída no lado do client, então apenas o `Request-Reply` é estabelecido, e ele não fornece 1. Persistência 2. Processamento de transações 3. Modo de entrega aprimorado 4. Fila de nível empresarial.

## Instalação

```bash
composer require hyperf/nats
```

## Uso

### Criar consumer

```
php bin/hyperf.php gen:nats-consumer DemoConsumer
```

Se `queue` for definido, o mesmo `subject` será consumido por apenas uma `queue`. Se `queue` não for definido, cada consumer receberá a mensagem.

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

### Publicar mensagem

Use publish para entregar mensagens.

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

Use request para entregar mensagens.

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

Use requestSync para entregar mensagens.

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
