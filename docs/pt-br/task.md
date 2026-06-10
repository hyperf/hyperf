# Tarefas (Task)

Neste momento, o `Swoole` não tem como fazer `hook` de todas as funções bloqueantes, o que significa que algumas funções ainda podem causar `bloqueio do processo`, afetando o agendamento das corrotinas. Nesses casos, podemos simular corrotinas usando o componente `Task`. Para atingir o objetivo de chamar funções bloqueantes sem bloquear o processo, na essência ainda é um modelo multiprocessos executando funções bloqueantes; portanto, o desempenho será claramente inferior ao de corrotinas nativas, dependendo também da quantidade de `Task Worker`.

## Instalação

```bash
composer require hyperf/task
```

## Configurar

Como o Task não é um componente padrão, você precisa adicionar configurações relacionadas a `Task` no `server.php` ao utilizá-lo.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // Other irrelevant configuration items are omitted here
    'settings' => [
        // Number of Task Workers, configure the appropriate number based on your server configuration
        'task_worker_num' => 8,
        // Because `Task` mainly deals with methods that cannot be coroutined, it is recommended to set `false` here to avoid data confusion under coroutines
        'task_enable_coroutine' => false,
    ],
    'callbacks' => [
        // Task callbacks
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];

```

## Uso

O componente Task oferece duas formas de uso: `envio por chamada ativa` e `envio por anotação`.

### Envio por chamada ativa

```php
<?php

use Hyperf\Coroutine\Coroutine;
use Hyperf\Context\ApplicationContext;
use Hyperf\Task\TaskExecutor;
use Hyperf\Task\Task;

class MethodTask
{
    public function handle($cid)
    {
        return [
            'worker.cid' => $cid,
            // Returns -1 when task_enable_coroutine is false, otherwise returns the corresponding coroutine ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$exec = $container->get(TaskExecutor::class);
$result = $exec->execute(new Task([MethodTask::class, 'handle'], [Coroutine::id()]));

```

### Usando anotações

Não é tão intuitivo usar `envio por chamada ativa`. Aqui implementamos a anotação `#[Task]` e reescrevemos a chamada do método via `AOP`. Quando estiver no processo `Worker`, ele é enviado automaticamente ao processo `Task`, e a corrotina aguarda o retorno dos dados.

```php
<?php

use Hyperf\Coroutine\Coroutine;
use Hyperf\Context\ApplicationContext;
use Hyperf\Task\Annotation\Task;

class AnnotationTask
{
    #[Task]
    public function handle($cid)
    {
        return [
            'worker.cid' => $cid,
            // Returns -1 when task_enable_coroutine=false, otherwise returns the corresponding coroutine ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$task = $container->get(AnnotationTask::class);
$result = $task->handle(Coroutine::id());
```

> `use Hyperf\Task\Annotation\Task;` é necessário ao usar a anotação `#[Task]`.

A anotação suporta os seguintes parâmetros:

| Configuração | Tipo | Padrão | Observações |
| :------: | :---: | :----: | :-------------------------------------- ----------------------: |
| timeout | int | 10 | Timeout de execução da task |
| workerId | int | -1 | Especifica o ID do processo task para o qual será enviado (-1 significa envio aleatório para um processo ocioso) |

## Apêndice

No momento, o Swoole não tem uma lista de funções que suportam corrotinas.

- mysql: a camada inferior usa libmysqlclient, o que não é recomendado; recomenda-se usar pdo_mysql/mysqli que já implementam corrotinas
- mongo: a camada inferior usa mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> Como o `MongoDB` não tem como ser `hook`, podemos chamá-lo via `Task`. A seguir está uma breve introdução de como chamar `MongoDB` por anotações.

Abaixo implementamos dois métodos: `insert` e `query`. Observe que o método `manager` não pode usar `Task`,
pois o `Task` será processado no processo `Task` correspondente e então retornará os dados desse processo para o processo `Worker`.
Portanto, os parâmetros de entrada e saída do `Task method` não devem carregar nenhum `IO`, como retornar um `Redis` instanciado etc.

```php
<?php

declare(strict_types=1);

namespace App\Task;

use Hyperf\Task\Annotation\Task;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;

class MongoTask
{
    public Manager $manager;

    #[Task]
    public function insert(string $namespace, array $document)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        $bulk->insert($document);

        $result = $this->manager()->executeBulkWrite($namespace, $bulk, $writeConcern);
        return $result->getUpsertedCount();
    }

    #[Task]
    public function query(string $namespace, array $filter = [], array $options = [])
    {
        $query = new Query($filter, $options);
        $cursor = $this->manager()->executeQuery($namespace, $query);
        return $cursor->toArray();
    }

    protected function manager()
    {
        if ($this->manager instanceof Manager) {
            return $this->manager;
        }
        $uri = 'mongodb://127.0.0.1:27017';
        return $this->manager = new Manager($uri, []);
    }
}

```

Use da seguinte forma:

```php
<?php
use App\Task\MongoTask;
use Hyperf\Context\ApplicationContext;

$client = ApplicationContext::getContainer()->get(MongoTask::class);
$client->insert('hyperf.test', ['id' => rand(0, 99999999)]);

$result = $client->query('hyperf.test', [], [
    'sort' => ['id' => -1],
    'limit' => 5,
]);
```

## Outras opções

Se o mecanismo de Task não atender aos requisitos de desempenho, você pode tentar outro projeto open source sob a organização Hyperf: [GoTask](https://github.com/hyperf/gotask). O GoTask inicia um processo Go como sidecar do processo principal do Swoole por meio do gerenciamento de processos do Swoole e usa comunicação entre processos para enviar a task ao sidecar para processamento e receber o retorno. Ele pode ser entendido como uma versão em Go do Swoole TaskWorker.

