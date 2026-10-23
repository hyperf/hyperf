# Task

Na fase atual, o `Swoole` não tem como fazer `hook` de todas as funções bloqueantes, o que significa que algumas funções ainda causarão `bloqueio de processo`, o que afetará o agendamento das Coroutines. Nesse caso, podemos simular Coroutines usando o componente `Task`. Para atingir o objetivo de chamar funções bloqueantes sem bloquear o processo, na essência, ainda é a execução de funções bloqueantes em múltiplos processos, então o desempenho será visivelmente inferior à Coroutine nativa, dependendo do número de `Task Worker`.

## Instalação

```bash
composer require hyperf/task
```

## Configuração

Como o Task não é um componente padrão, você precisa adicionar a configuração relacionada ao `Task` em `server.php` ao usá-lo.

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

O componente Task fornece dois métodos de uso: `entrega por método ativo` e `entrega por annotation`.

### Entrega por método ativo

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

### Usando annotations

Não é particularmente intuitivo usar a `entrega por método ativo`. Aqui implementamos a annotation correspondente `#[Task]` e reescrevemos a chamada do método através de `AOP`. Quando estiver no processo `Worker`, ele é automaticamente entregue ao processo `Task`, e a Coroutine espera o retorno dos dados.

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

> `use Hyperf\Task\Annotation\Task;` é obrigatório ao usar a annotation `#[Task]`

A annotation suporta os seguintes parâmetros

| Configuração | Tipo | Padrão | Observações |
| :------: | :---: | :----: | :-------------------------------------- ----------------------: |
| timeout | int | 10 | Tempo limite de execução da tarefa |
| workerId | int | -1 | Especifica o ID do processo para o qual a tarefa deve ser entregue (-1 significa entrega aleatória a um processo livre) |

## Apêndice

O Swoole ainda não possui uma lista de funções com suporte a Coroutine para os seguintes casos

- mysql, a camada inferior usa libmysqlclient, que não é recomendado; é recomendado usar pdo_mysql/mysqli, que já implementaram Coroutines
- mongo, a camada inferior usa mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> Como o `MongoDB` não tem como ser `hooked`, podemos chamá-lo através do `Task`. A seguir está uma breve introdução sobre como chamar o `MongoDB` através de annotations.

Abaixo implementamos dois métodos, `insert` e `query`. Deve-se notar que o método `manager` não pode usar `Task`,
porque o `Task` será processado no `processo Task` correspondente, e então retornará os dados do `processo Task` para o `processo Worker`.
Portanto, os parâmetros de entrada e saída do `método Task` não devem carregar nenhum `IO`, como retornar um `Redis` instanciado e assim por diante.

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

Uso conforme a seguir

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

Se o mecanismo Task não atender aos requisitos de desempenho, você pode tentar outro projeto open source da organização Hyperf, o [GoTask](https://github.com/hyperf/gotask). O GoTask inicia o processo Go como um sidecar do processo principal do Swoole através da função de gerenciamento de processos do Swoole, e usa a comunicação entre processos para entregar a tarefa ao sidecar para processamento e receber o valor de retorno. Pode ser entendido como a versão Go do Swoole TaskWorker.

