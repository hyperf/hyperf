# Task

現階段 `Swoole` 暫時沒有辦法 `hook` 所有的阻塞函式，也就意味著有些函式仍然會導致 `程序阻塞`，從而影響協程的排程，此時我們可以透過使用 `Task` 元件來模擬協程處理，從而達到不阻塞程序呼叫阻塞函式的目的，本質上是仍是是多程序執行阻塞函式，所以效能上會明顯地不如原生協程，具體取決於 `Task Worker` 的數量。

## 安裝

```bash
composer require hyperf/task
```

## 配置

因為 Task 並不是預設元件，所以在使用的時候需要在 `server.php` 增加 `Task` 相關的配置。

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // 這裡省略了其它不相關的配置項
    'settings' => [
        // Task Worker 數量，根據您的伺服器配置而配置適當的數量
        'task_worker_num' => 8,
        // 因為 `Task` 主要處理無法協程化的方法，所以這裡推薦設為 `false`，避免協程下出現資料混淆的情況
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

Task 元件提供了 `主動方法投遞` 和 `註解投遞` 兩種使用方法。

### 主動方法投遞

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
            // task_enable_coroutine 為 false 時返回 -1，反之 返回對應的協程 ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$exec = $container->get(TaskExecutor::class);
$result = $exec->execute(new Task([MethodTask::class, 'handle'], [Coroutine::id()]));

```

### 使用註解

透過 `主動方法投遞` 時，並不是特別直觀，這裡我們實現了對應的 `#[Task]` 註解，並透過 `AOP` 重寫了方法呼叫。當在 `Worker` 程序時，自動投遞到 `Task` 程序，並協程等待 資料返回。

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
            // task_enable_coroutine=false 時返回 -1，反之 返回對應的協程 ID
            'task.cid' => Coroutine::id(),
        ];
    }
}

$container = ApplicationContext::getContainer();
$task = $container->get(AnnotationTask::class);
$result = $task->handle(Coroutine::id());
```

> 使用 `#[Task]` 註解時需 `use Hyperf\Task\Annotation\Task;`

註解支援以下引數

|   配置   | 型別  | 預設值 |                        備註                        |
| :------: | :---: | :----: | :------------------------------------------------: |
| timeout  |  int  |   10   |                  任務執行超時時間                  |
| workerId |  int  |   -1   | 指定投遞的 Task 程序 ID (-1 代表隨機投遞到空閒程序) |

## 附錄

Swoole 暫時沒有協程化的函式列表

- mysql，底層使用 libmysqlclient, 不推薦使用, 推薦使用已經實現協程化的 pdo_mysql/mysqli
- mongo，底層使用 mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> 因為 `MongoDB` 沒有辦法被 `hook`，所以我們可以透過 `Task` 來呼叫，下面就簡單介紹一下如何透過註解方式呼叫 `MongoDB`。

以下我們實現兩個方法 `insert` 和 `query`，其中需要注意的是 `manager` 方法不能使用 `Task`，
因為 `Task` 會在對應的 `Task 程序` 中處理，然後將資料從 `Task 程序` 返回到 `Worker 程序` 。
所以 `Task 方法` 的入參和出參最好不要攜帶任何 `IO`，比如返回一個例項化後的 `Redis` 等等。

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

如果 Task 機制無法滿足效能要求，可以嘗試一下 Hyperf 組織下的另一個開源專案[GoTask](https://github.com/hyperf/gotask)。GoTask 透過 Swoole 程序管理功能啟動 Go 程序作為 Swoole 主程序邊車(Sidecar)，利用程序通訊將任務投遞給邊車處理並接收返回值。可以理解為 Go 版的 Swoole TaskWorker。

