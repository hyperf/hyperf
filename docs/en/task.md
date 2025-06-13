# Task

At this stage, `Swoole` has no way to `hook` all blocking functions, which means that some functions will still cause `process blocking`, which will affect the scheduling of coroutines. At this time, we can simulate coroutines by using the `Task` component. In order to achieve the purpose of calling blocking functions without blocking the process, in essence, it is still multi-process running blocking functions, so the performance will be obviously inferior to the native coroutine, depending on the number of `Task Worker`.

## Install

```bash
composer require hyperf/task
```

## Configure

Because Task is not the default component, you need to add `Task` related configuration to `server.php` when using it.

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

## use

The Task component provides two usage methods: `active method delivery` and `annotation delivery`.

### Active method delivery

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

### Using annotations

It is not particularly intuitive to use `active method delivery`. Here we implement the corresponding `#[Task]` annotation and rewrite the method call through `AOP`. When in the `Worker` process, it is automatically delivered to the `Task` process, and the coroutine waits for the data to return.

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

> `use Hyperf\Task\Annotation\Task;` is required when using the `#[Task]` annotation

The annotation supports the following parameters

| Configuration | Type | Default | Remarks |
| :------: | :---: | :----: | :-------------------------------------- ----------------------: |
| timeout | int | 10 | Task execution timeout |
| workerId | int | -1 | Specifies the process ID of the task to be delivered (-1 means random delivery to an idle process) |

## Appendix

Swoole does not have a list of coroutine functions for the time being

- mysql, the bottom layer uses libmysqlclient, which is not recommended, it is recommended to use pdo_mysql/mysqli that has already implemented coroutines
- mongo, the bottom layer uses mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> Because `MongoDB` has no way to be `hook`, we can call it through `Task`. The following is a brief introduction to how to call `MongoDB` through annotations.

Below we implement two methods `insert` and `query`. It should be noted that the `manager` method cannot use `Task`,
Because `Task` will be processed in the corresponding `Task process`, and then return the data from the `Task process` to the `Worker process`.
Therefore, the input and output parameters of the `Task method` should not carry any `IO`, such as returning an instantiated `Redis` and so on.

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

Use as follows

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

## Other options

If the Task mechanism cannot meet the performance requirements, you can try another open source project under the Hyperf organization [GoTask](https://github.com/hyperf/gotask). GoTask starts the Go process as the Swoole main process sidecar through the Swoole process management function, and uses the process communication to deliver the task to the sidecar for processing and receive the return value. It can be understood as the Go version of Swoole TaskWorker.

