# 定時任務

通常來說，執行定時任務會透過 Linux 的 `crontab` 命令來實現，但現實情況下，並不是所有開發人員都能夠擁有生產環境的伺服器去設定定時任務的，這裡 [hyperf/crontab](https://github.com/hyperf/crontab) 元件為您提供了一個 `秒級` 定時任務功能，只需透過簡單的定義即可完成一個定時任務的定義。 

# 安裝

```bash
composer require hyperf/crontab
```

# 使用

## 啟動任務排程器程序

在使用定時任務元件之前，需要先在 `config/autoload/processes.php` 內註冊一下 `Hyperf\Crontab\Process\CrontabDispatcherProcess` 自定義程序，如下：

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

這樣服務啟動時會啟動一個自定義程序，用於對定時任務的解析和排程分發。   
同時，您還需要將 `config/autoload/crontab.php` 內的 `enable` 配置設定為 `true`，表示開啟定時任務功能，如配置檔案不存在可自行建立，配置如下：

```php
<?php
return [
    // 是否開啟定時任務
    'enable' => true,
];
```

## 定義定時任務

### 透過配置檔案定義

您可於 `config/autoload/crontab.php` 的配置檔案內配置您所有的定時任務，檔案返回一個 `Hyperf\Crontab\Crontab[]` 結構的陣列，如配置檔案不存在可自行建立：

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // 透過配置檔案定義的定時任務
    'crontab' => [
        // Callback型別定時任務（預設）
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('這是一個示例的定時任務'),
        // Command型別定時任務
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (optional) arguments
            'fooArgument' => 'barValue',
            // (optional) options
            '--message-limit' => 1,
            // 記住要加上，否則會導致主程序退出
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure 型別定時任務 (僅在 Coroutine style server 中支援)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

3.1 之後新增了新的配置方式，你可以透過 `config/crontabs.php` 來定義定時任務，如配置檔案不存在可自行建立：

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### 透過註解定義

透過 `#[Crontab]` 註解可以快速完成對一個任務的定義，以下的定義示例與配置檔案定義所達到的目的都是一樣的。定義一個名為 `Foo` 每分鐘執行一次 `App\Task\FooTask::execute()` 的定時任務。

```php
<?php
namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(name: "Foo", rule: "* * * * *", callback: "execute", memo: "這是一個示例的定時任務")]
class FooTask
{
    #[Inject]
    private StdoutLoggerInterface $logger;

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }

    #[Crontab(rule: "* * * * *", memo: "foo")]
    public function foo()
    {
        var_dump('foo');
    }
}
```

### 任務屬性

#### name

定時任務的名稱，可以為任意字串，各個定時任務之間的名稱要唯一。

#### rule

定時任務的執行規則，在分鐘級的定義時，與 Linux 的 `crontab` 命令的規則一致，在秒級的定義時，規則長度從 5 位變成 6 位，在規則的前面增加了對應秒級的節點，也就是 5 位時以分鐘級規則執行，6 位時以秒級規則執行，如 `*/5 * * * * *` 則代表每 5 秒執行一次。注意在註解定義時，規則存在 `\` 符號時，需要進行轉義處理，即填寫 `*\/5 * * * * *`。

#### callback

定時任務的執行回撥，即定時任務實際執行的程式碼，在透過配置檔案定義時，這裡需要傳遞一個 `[$class, $method]` 的陣列，`$class` 為一個類的全稱，`$method` 為 `$class` 內的一個 `public` 方法。當透過註解定義時，只需要提供一個當前類內的 `public` 方法的方法名即可，如果當前類只有一個 `public` 方法，您甚至可以不提供該屬性。

#### singleton

解決任務的併發執行問題，任務永遠只會同時執行 1 個。但是這個沒法保障任務在叢集時重複執行的問題。

#### onOneServer

多例項部署專案時，則只有一個例項會被觸發。

#### mutexPool

互斥鎖使用的 `Redis` 連線池。

#### mutexExpires

互斥鎖超時時間，如果定時任務執行完畢，但解除互斥鎖失敗時，互斥鎖也會在這個時間之後自動解除。

#### memo

定時任務的備註，該屬性為可選屬性，沒有任何邏輯上的意義，僅供開發人員查閱幫助對該定時任務的理解。

#### enable

當前任務是否生效。

> 除了 bool 型別，還支援 string 和 array

如果 `enable` 是 `string`，則會呼叫當前類對應的方法，來判斷此定時任務是否執行

```php
<?php

namespace App\Crontab;

use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: "isEnable", memo: "這是一個示例的定時任務")]
class EchoCrontab
{
    public function execute()
    {
        var_dump(Carbon::now()->toDateTimeString());
    }

    public function isEnable(): bool
    {
        return true;
    }
}
```

如果 `enable` 是 `array`，則會呼叫 `array[0]` 對應的 `array[1]`，來判斷此定時任務是否執行

```php
<?php

namespace App\Crontab;

class EnableChecker
{
    public function isEnable(): bool
    {
        return false;
    }
}
```

```php
<?php

namespace App\Crontab;

use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: [EnableChecker::class, "isEnable"], memo: "這是一個示例的定時任務")]
class EchoCrontab
{
    public function execute()
    {
        var_dump(Carbon::now()->toDateTimeString());
    }

    public function isEnable(): bool
    {
        return true;
    }
}

```

#### environments

設定定時任務的環境，如果不設定，則會全部環境都生效。支援傳入 array 和 string。

### 排程分發策略

定時任務在設計上允許透過不同的策略來排程分發執行任務，目前僅提供了 `多程序執行策略`、`協程執行策略` 兩種策略，預設為 `多程序執行策略`，後面的迭代會增加更多更強的策略。

> 當使用協程風格服務時，請使用 協程執行策略。

#### 更改排程分發策略

透過在 `config/autoload/dependencies.php` 更改 `Hyperf\Crontab\Strategy\StrategyInterface` 介面類所對應的例項來更改目前所使用的策略，預設情況下使用 `Worker 程序執行策略`，對應的類為 `Hyperf\Crontab\Strategy\WorkerStrategy`，如我們希望更改策略為一個新的策略，比如為 `App\Crontab\Strategy\FooStrategy`，那麼如下：

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Worker 程序執行策略 [預設]

策略類：`Hyperf\Crontab\Strategy\WorkerStrategy`   

預設情況下使用此策略，即為 `CrontabDispatcherProcess` 程序解析定時任務，並透過程序間通訊輪詢傳遞執行任務到各個 `Worker` 程序中，由各個 `Worker` 程序以協程來實際執行執行任務。

##### TaskWorker 程序執行策略

策略類：`Hyperf\Crontab\Strategy\TaskWorkerStrategy`   

此策略為 `CrontabDispatcherProcess` 程序解析定時任務，並透過程序間通訊輪詢傳遞執行任務到各個 `TaskWorker` 程序中，由各個 `TaskWorker` 程序以協程來實際執行執行任務，使用此策略需注意 `TaskWorker` 程序是否配置了支援協程。

##### 多程序執行策略

策略類：`Hyperf\Crontab\Strategy\ProcessStrategy`   

此策略為 `CrontabDispatcherProcess` 程序解析定時任務，並透過程序間通訊輪詢傳遞執行任務到各個 `Worker` 程序和 `TaskWorker` 程序中，由各個程序以協程來實際執行執行任務，使用此策略需注意 `TaskWorker` 程序是否配置了支援協程。

##### 協程執行策略

策略類：`Hyperf\Crontab\Strategy\CoroutineStrategy`   

此策略為 `CrontabDispatcherProcess` 程序解析定時任務，並在程序內為每個執行任務建立一個協程來執行。

## 執行定時任務

當您完成上述的配置後，以及定義了定時任務後，只需要直接啟動 `Server`，定時任務便會一同啟動。   
在您啟動後，即便您定義了足夠短週期的定時任務，定時任務也不會馬上開始執行，所有定時任務都會等到下一個分鐘週期時才會開始執行，比如您啟動的時候是 `10 時 11 分 12 秒`，那麼定時任務會在 `10 時 12 分 00 秒` 才會正式開始執行。

### FailToExecute 事件

當定時任務執行失敗時，會觸發 `FailToExecute` 事件，所以我們可以編寫以下監聽器，拿到對應的 `Crontab` 和 `Throwable`。

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

#[Listener]
class FailToExecuteCrontabListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            FailToExecute::class,
        ];
    }

    /**
     * @param FailToExecute $event
     */
    public function process(object $event)
    {
        var_dump($event->crontab->getName());
        var_dump($event->throwable->getMessage());
    }
}
```
