# Task

現階段 `Swoole` 暫時沒有辦法 `hook` 所有的阻塞函數，也就意味着有些函數仍然會導致 `進程阻塞`，從而影響協程的調度，此時我們可以通過使用 `Task` 組件來模擬協程處理，從而達到不阻塞進程調用阻塞函數的目的，本質上是仍是是多進程運行阻塞函數，所以性能上會明顯地不如原生協程，具體取決於 `Task Worker` 的數量。

## 安裝

```bash
composer require hyperf/task
```

## 配置

因為 Task 並不是默認組件，所以在使用的時候需要在 `server.php` 增加 `Task` 相關的配置。

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;

return [
    // 這裏省略了其它不相關的配置項
    'settings' => [
        // Task Worker 數量，根據您的服務器配置而配置適當的數量
        'task_worker_num' => 8,
        // 因為 `Task` 主要處理無法協程化的方法，所以這裏推薦設為 `false`，避免協程下出現數據混淆的情況
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

Task 組件提供了 `主動方法投遞` 和 `註解投遞` 兩種使用方法。

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

通過 `主動方法投遞` 時，並不是特別直觀，這裏我們實現了對應的 `#[Task]` 註解，並通過 `AOP` 重寫了方法調用。當在 `Worker` 進程時，自動投遞到 `Task` 進程，並協程等待 數據返回。

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

註解支持以下參數

|   配置   | 類型  | 默認值 |                        備註                        |
| :------: | :---: | :----: | :------------------------------------------------: |
| timeout  |  int  |   10   |                  任務執行超時時間                  |
| workerId |  int  |   -1   | 指定投遞的 Task 進程 ID (-1 代表隨機投遞到空閒進程) |

## 附錄

Swoole 暫時沒有協程化的函數列表

- mysql，底層使用 libmysqlclient, 不推薦使用, 推薦使用已經實現協程化的 pdo_mysql/mysqli
- mongo，底層使用 mongo-c-client
- pdo_pgsql
- pdo_ori
- pdo_odbc
- pdo_firebird

### MongoDB

> 因為 `MongoDB` 沒有辦法被 `hook`，所以我們可以通過 `Task` 來調用，下面就簡單介紹一下如何通過註解方式調用 `MongoDB`。

以下我們實現兩個方法 `insert` 和 `query`，其中需要注意的是 `manager` 方法不能使用 `Task`，
因為 `Task` 會在對應的 `Task 進程` 中處理，然後將數據從 `Task 進程` 返回到 `Worker 進程` 。
所以 `Task 方法` 的入參和出參最好不要攜帶任何 `IO`，比如返回一個實例化後的 `Redis` 等等。

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

如果 Task 機制無法滿足性能要求，可以嘗試一下 Hyperf 組織下的另一個開源項目[GoTask](https://github.com/hyperf/gotask)。GoTask 通過 Swoole 進程管理功能啓動 Go 進程作為 Swoole 主進程邊車(Sidecar)，利用進程通訊將任務投遞給邊車處理並接收返回值。可以理解為 Go 版的 Swoole TaskWorker。

