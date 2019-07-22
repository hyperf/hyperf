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

use Hyperf\Server\SwooleEvent;

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
        SwooleEvent::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        SwooleEvent::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];

```
## 使用

Task 组件提供了 `主动方法投递` 和 `注解投递` 两种使用方法。

### 主动方法投递

```php
<?php

use Hyperf\Utils\Coroutine;
use Hyperf\Utils\ApplicationContext;
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
$result = $exec->execute(new Task([MethodTask::class, 'handle'], Coroutine::id()));

```
### 使用注解

通过 `主动方法投递` 时，并不是特别直观，这里我们实现了对应的 `@Task` 注解，并通过 `AOP` 重写了方法调用。当在 `Worker` 进程时，自动投递到 `Task` 进程，并协程等待 数据返回。

```php
<?php

use Hyperf\Utils\Coroutine;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Task\Annotation\Task;

class AnnotationTask
{
    /**
     * @Task
     */
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

> 使用 `@Task` 注解时需 `use Hyperf\Task\Annotation\Task;`

## 附录

Swoole 暂时没有协程化的函数列表

- mysql：底层使用 libmysqlclient
- curl：底层使用 libcurl（即不能使用CURL驱动的Guzzle）
- mongo：底层使用 mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

