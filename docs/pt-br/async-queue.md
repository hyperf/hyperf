# Fila assíncrona

A fila assíncrona se diferencia de message queues como `RabbitMQ` e `Kafka`. Este componente fornece apenas capacidades de "processamento assíncrono" e "processamento assíncrono com delay" e não garante estritamente a persistência de mensagens nem oferece suporte ao `ACK response mechanism`.

## Instalação

```bash
composer require hyperf/async-queue
```

## Configuração

O arquivo de configuração fica em `config/autoload/async_queue.php` e pode ser criado caso não exista.

> Atualmente, apenas o `Redis Driver` é suportado.

|     Configuração      |  Tipo  |                    Valor padrão                    |        Observação        |
|:-------------:|:------:|:-------------------------------------------:|:------------------:|
|    driver     | string | Hyperf\AsyncQueue\Driver\RedisDriver::class |         Nenhuma          |
|    channel    | string |                    queue                    |      Prefixo da fila      |
| retry_seconds |  int   |                      5                      | Intervalo de retry após falha |
|   processes   |  int   |                      1                      |     Quantidade de processos consumidores     |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'retry_seconds' => 5,
        'processes' => 1,
    ],
];
```

## Uso

### Consumir mensagens

O componente já fornece um processo filho padrão; basta configurar o processo em `config/autoload/processes.php`.

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];
```

Claro, você também pode adicionar o `Process` abaixo no seu skeleton da aplicação.

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

Primeiro definimos um job de mensagem como segue:

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
        // É recomendável usar dados comuns aqui. Não passe objetos que carreguem conexões de IO, como objetos PDO.
        $this->params = $params;
    }

    public function handle()
    {
        // Processa a lógica específica com base nos parâmetros
        var_dump($this->params);
    }
}
```

Publicar a mensagem:

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
     * Publica a mensagem.
     */
    public function push($params, int $delay = 0): bool
    {
        // O `ExampleJob` aqui será serializado e armazenado no Redis, portanto, é melhor passar apenas dados comuns para as variáveis internas do objeto.
        // Da mesma forma, se anotações forem usadas internamente, o @Value serializará o objeto correspondente, fazendo com que o corpo da mensagem fique maior.
        // Por isso, NÃO é recomendado usar o método `make` para criar um objeto `Job`.
        return $this->driver->push(new ExampleJob($params), $delay);
    }
}
```

De acordo com o cenário real de negócio, você pode postar mensagens dinamicamente para execução na fila assíncrona. Demonstramos o envio dinâmico de mensagens no controller, como segue:

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