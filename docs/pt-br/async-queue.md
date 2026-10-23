# Async Queue

As Async Queue se diferenciam das message queues como `RabbitMQ` e `Kafka`. Este componente fornece apenas capacidades de 'processamento assíncrono' e 'processamento assíncrono com atraso', e não garante estritamente a persistência de mensagens nem suporta o mecanismo de resposta `ACK`.

## Instalação

```bash
composer require hyperf/async-queue
```

## Configuração

O arquivo de configuração está localizado em `config/autoload/async_queue.php`, que pode ser criado se o arquivo não existir.

> Atualmente, apenas o `Driver Redis` é suportado.

|     Configuração      |  Tipo  |                   Valor Padrão                    |        Observação        |
|:-------------:|:------:|:-------------------------------------------:|:------------------:|
|    enable     |  bool  |                    false                    | Habilita a criação automática de processos consumidores |
|    driver     | string | Hyperf\AsyncQueue\Driver\RedisDriver::class |         Nenhuma         |
|    channel    | string |                    queue                    |      O prefixo da fila      |
| retry_seconds |  int   |                      5                      | Intervalo de retentativa após falha |
|   processes   |  int   |                      1                      |     O número de processos consumidores     |

```php
<?php

return [
    'default' => [
        'enable' => true,
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'retry_seconds' => 5,
        'processes' => 1,
    ],
];

```

## Uso

### Consumir a mensagem

O componente oferece duas formas de configurar os consumidores da async queue: `configuração por processo` e `configuração por parâmetro`.

#### 1. Configuração por parâmetro

Com base no parâmetro `enable` no arquivo de configuração `config/autoload/async_queue.php` mencionado acima, os processos consumidores são criados automaticamente.

#### 2. Configuração por processo

O componente já fornece o processo filho (Process) padrão; basta configurar o processo filho em `config/autoload/processes.php`.

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];
```

Claro, você também pode adicionar o `Process` abaixo ao esqueleto da sua aplicação.

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "async-queue")]
class AsyncQueueConsumer extends ConsumerProcess
{
}
```

### Publicar uma mensagem

Primeiro, definimos um message job da seguinte forma

```php
<?php

declare(strict_types=1);

namespace App\Job;

use Hyperf\AsyncQueue\Job;

class ExampleJob extends Job
{
    public $params;

    public function __construct($params)
    {
        // It's best to use normal data here. Don't pass the objects that carry IO, such as PDO objects.
        $this->params = $params;
    }

    public function handle()
    {
        // Process specific logic based on parameters
        var_dump($this->params);
    }
}
```

Publica a mensagem

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\ExampleJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * Publish the message.
     */
    public function push($params, int $delay = 0): bool
    {
        // The `ExampleJob` here will be serialized and stored in Redis, so internal variables of the object are best passed only normal data.
        // Similarly, if the annotation is used internally, @Value will serialize the corresponding object, causing the message body to become larger.
        // So it is NOT recommended to use the `make` method to create a `Job` object.
        return $this->driver->push(new ExampleJob($params), $delay);
    }
}
```

De acordo com o cenário de negócio real, para publicar mensagens dinamicamente na execução da async queue, demonstramos a entrega dinâmica de mensagens no controller, como a seguir:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\QueueService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class QueueController extends Controller
{
    #[Inject]
    protected QueueService $service;

    public function index()
    {
        $this->service->push([
            'group@hyperf.io',
            'https://doc.hyperf.io',
            'https://www.hyperf.io',
        ]);

        return 'success';
    }
}
```
