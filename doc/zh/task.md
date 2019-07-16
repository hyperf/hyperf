# Task

现阶段 `Swoole` 暂时没有 `hook` 所有的函数，所以有些函数仍然会导致 `协程阻塞`。
所以我们仍需要 `Task` 组件，使用 `Task` 进程处理可能会导致协程阻塞的逻辑。

## 安装

```
composer require hyperf/task
```

## 配置

因为 Task 并不是默认组件，所以在使用的时候需要在 `server.php` 增加 `Task` 相关的配置。

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;

return [
    'mode' => SWOOLE_BASE,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                SwooleEvent::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
    'settings' => [
        'enable_coroutine' => true,
        'worker_num' => 2,
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid',
        'open_tcp_nodelay' => true,
        'max_coroutine' => 100000,
        'open_http2_protocol' => true,
        'max_request' => 100000,
        'socket_buffer_size' => 2 * 1024 * 1024,
        // Task setting
        'task_worker_num' => 4,
        'task_enable_coroutine' => false, // 因为 Task 主要处理无法协程化的方法，所以这里可以设为 false。
    ],
    'callbacks' => [
        SwooleEvent::ON_BEFORE_START => [Hyperf\Framework\Bootstrap\ServerStartCallback::class, 'beforeStart'],
        SwooleEvent::ON_WORKER_START => [Hyperf\Framework\Bootstrap\WorkerStartCallback::class, 'onWorkerStart'],
        SwooleEvent::ON_PIPE_MESSAGE => [Hyperf\Framework\Bootstrap\PipeMessageCallback::class, 'onPipeMessage'],
        // Task setting
        SwooleEvent::ON_TASK => [Hyperf\Framework\Bootstrap\TaskCallback::class, 'onTask'],
        SwooleEvent::ON_FINISH => [Hyperf\Framework\Bootstrap\FinishCallback::class, 'onFinish'],
    ],
];

```
## 使用

Task 组件提供了两种使用方法。

### 主动投递


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
            // task_enable_coroutine=false 时返回 -1，反之 返回对应的协程 ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$exec = $container->get(TaskExecutor::class);
$result = $exec->execute(new Task([MethodTask::class, 'handle'], Coroutine::id()));

```
### 使用注解

通过直接投递时，并不是特别直观，这里我们实现了对应的注解，并通过 AOP 重写了方法调用。当在 Worker 进程时，自动投递到 Task 进程，并协程等待 数据返回。

```php
<?php

use Hyperf\Utils\Coroutine;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Task\Annotation\Task;

class MethodTask
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
/** @var MethodTask $task */
$task = $container->get(MethodTask::class);
$result = $task->handle(Coroutine::id());

```

## 附录

暂时没有协程化的函数列表

- mysql：底层使用libmysqlclient
- curl：底层使用libcurl （即不能使用CURL驱动的Guzzle）
- mongo：底层使用mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

