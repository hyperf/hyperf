# Task

At present, `Swoole` cannot `hook` all blocking functions, which means that some functions will still cause `process blocking`, thereby affecting coroutine scheduling. In such cases, we can use the `Task` component to simulate coroutine processing to achieve the goal of calling blocking functions without blocking the process. Essentially, it still runs blocking functions in multiple processes, so its performance is significantly lower than that of native coroutines, which depends on the number of `Task Worker` processes.

## Installation

```bash
composer require hyperf/task
```

## Configuration

Since `Task` is not a default component, you need to add `Task` related configurations to `server.php` when using it.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // Other unrelated configuration items are omitted here
    'settings' => [
        // Number of Task Workers, configure an appropriate number according to your server configuration
        'task_worker_num' => 8,
        // Because `Task` mainly handles methods that cannot be coroutinized, it is recommended to set this to `false` to avoid data confusion under coroutines
        'task_enable_coroutine' => false,
    ],
    'callbacks' => [
        // Task callbacks
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];

```

## Usage

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

### Using Annotations

The `active method delivery` is not particularly intuitive. Therefore, we implemented the corresponding `#[Task]` annotation and rewrote method calls using `AOP`. When in a `Worker` process, the task is automatically delivered to the `Task` process, and the coroutine waits for the data to return.

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

> When using the `#[Task]` annotation, ensure you `use Hyperf\Task\Annotation\Task;`

The annotation supports the following parameters:

| Config   | Type  | Default | Description                                              |
| :------: | :---: | :----: | :------------------------------------------------: |
| timeout  |  int  |   10   | Task execution timeout                                  |
| workerId |  int  |   -1   | Specify the Task process ID for delivery (-1 means random delivery to an idle process) |

## Appendix

List of functions that Swoole has not yet coroutinized:

- mysql: uses libmysqlclient internally, not recommended; it is recommended to use pdo_mysql/mysqli which have already been coroutinized.
- mongo: uses mongo-c-client internally.
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> Because `MongoDB` cannot be `hooked`, we can use `Task` to call it. Below is a brief introduction on how to call `MongoDB` using the annotation method.

Here we implement two methods, `insert` and `query`. It is important to note that the `manager` method cannot use `Task`, because `Task` is processed in the corresponding `Task process` and then the data is returned from the `Task process` to the `Worker process`. Therefore, it is best not to carry any `IO` in the input and output parameters of the `Task method`, such as returning an instantiated `Redis` object, etc.

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

Usage is as follows:

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

## Other Schemes

If the Task mechanism cannot meet performance requirements, you can try another open-source project under the Hyperf organization: [GoTask](https://github.com/hyperf/gotask). GoTask uses Swoole's process management function to start a Go process as a Sidecar to the main Swoole process, and uses inter-process communication to deliver tasks to the sidecar for processing and receive the return value. This can be understood as a Go version of Swoole TaskWorker.

