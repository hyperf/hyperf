# Task

现阶段 `Swoole` 暂时没有办法 `hook` 所有的阻塞函数，也就意味着有些函数仍然会导致 `进程阻塞`，从而影响协程的调度，此时我们可以通过使用 `Task` 组件来模拟协程处理，从而达到不阻塞进程调用阻塞函数的目的，本质上是仍是是多进程运行阻塞函数，所以性能上会明显地不如原生协程，具体取决于 `Task Worker` 的数量。

## 安装

```bash
composer require hyperf/task
```

## 配置

因为 Task 并不是默认组件，所以在使用的时候需要在 `server.php` 增加 `Task` 相关的配置。

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // 这里省略了其它不相关的配置项
    'settings' => [
        // Task Worker 数量，根据您的服务器配置而配置适当的数量
        'task_worker_num' => 8,
        // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
        'task_enable_coroutine' => false,
    ],
    'callbacks' => [
        // Task callbacks
        Event::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        Event::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];

```

## 使用

Task 组件提供了 `主动方法投递` 和 `注解投递` 两种使用方法。

### 主动方法投递

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
            // task_enable_coroutine 为 false 时返回 -1，反之 返回对应的协程 ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$exec = $container->get(TaskExecutor::class);
$result = $exec->execute(new Task([MethodTask::class, 'handle'], [Coroutine::id()]));

```

### 使用注解

通过 `主动方法投递` 时，并不是特别直观，这里我们实现了对应的 `#[Task]` 注解，并通过 `AOP` 重写了方法调用。当在 `Worker` 进程时，自动投递到 `Task` 进程，并协程等待 数据返回。

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
            // task_enable_coroutine=false 时返回 -1，反之 返回对应的协程 ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$task = $container->get(AnnotationTask::class);
$result = $task->handle(Coroutine::id());
```

> 使用 `#[Task]` 注解时需 `use Hyperf\Task\Annotation\Task;`

注解支持以下参数

|   配置   | 类型  | 默认值 |                        备注                        |
| :------: | :---: | :----: | :------------------------------------------------: |
| timeout  |  int  |   10   |                  任务执行超时时间                  |
| workerId |  int  |   -1   | 指定投递的 Task 进程 ID (-1 代表随机投递到空闲进程) |

## 附录

Swoole 暂时没有协程化的函数列表

- mysql，底层使用 libmysqlclient, 不推荐使用, 推荐使用已经实现协程化的 pdo_mysql/mysqli
- mongo，底层使用 mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> 因为 `MongoDB` 没有办法被 `hook`，所以我们可以通过 `Task` 来调用，下面就简单介绍一下如何通过注解方式调用 `MongoDB`。

以下我们实现两个方法 `insert` 和 `query`，其中需要注意的是 `manager` 方法不能使用 `Task`，
因为 `Task` 会在对应的 `Task 进程` 中处理，然后将数据从 `Task 进程` 返回到 `Worker 进程` 。
所以 `Task 方法` 的入参和出参最好不要携带任何 `IO`，比如返回一个实例化后的 `Redis` 等等。

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

使用如下

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

## 其他方案

如果 Task 机制无法满足性能要求，可以尝试一下 Hyperf 组织下的另一个开源项目[GoTask](https://github.com/hyperf/gotask)。GoTask 通过 Swoole 进程管理功能启动 Go 进程作为 Swoole 主进程边车(Sidecar)，利用进程通讯将任务投递给边车处理并接收返回值。可以理解为 Go 版的 Swoole TaskWorker。

