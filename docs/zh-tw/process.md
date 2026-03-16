# 自定義程序

[hyperf/process](https://github.com/hyperf/process) 可以新增一個使用者自定義的工作程序，此函式通常用於建立一個特殊的工作程序，用於監控、上報或者其他特殊的任務。在 Server 啟動時會自動建立程序，並執行指定的子程序函式，程序意外退出時，Server 會重新拉起程序。

## 建立一個自定義程序

在任意位置實現一個繼承 `Hyperf\Process\AbstractProcess` 的子類，並實現介面方法 `handle(): void`，方法內實現您的邏輯程式碼，我們透過程式碼來舉例：

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // 您的程式碼 ...
    }
}
```

這樣即完成了一個自定義程序類，但該自定義程序類尚未被註冊到 `程序管理器(ProcessManager)` 內，我們可以透過 `配置檔案` 或 `註解` 兩種方式的任意一種來完成註冊工作。

### 透過配置檔案註冊

只需在 `config/autoload/processes.php` 內加上您的自定義程序類即可：

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### 透過註解註冊

只需在自定義程序類上定義 `#[Process]` 註解，Hyperf 會收集並自動完成註冊工作：

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process")]
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // 您的程式碼 ...
    }
}
```

> 使用 `#[Process]` 註解時需 `use Hyperf\Process\Annotation\Process;` 名稱空間；   

## 為程序啟動加上條件

有些時候，並不是所有時候都應該啟動一個自定義程序，一個自定義程序的啟動與否可能會根據某些配置或者某些條件來決定，我們可以透過在自定義程序類內重寫 `isEnable(): bool` 方法來實現，預設返回 `true`，即會跟隨服務一同啟動，如方法返回 `false`，則服務啟動時不會啟動該自定義程序。

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process")]
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // 您的程式碼 ...
    }
    
    public function isEnable($server): bool
    {
        // 不跟隨服務啟動一同啟動
        return false;   
    }
}
```

## 設定自定義程序

自定義程序存在一些可設定的引數，均可以透過 在子類上重寫引數對應的屬性 或 在 `#[Process]` 註解內定義對應的屬性 兩種方式來進行定義。

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "user-process", redirectStdinStdout: false, pipeType: 2, enableCoroutine: true)]
class FooProcess extends AbstractProcess
{
    /**
     * 程序數量
     */
    public int $nums = 1;

    /**
     * 程序名稱
     */
    public string $name = 'user-process';

    /**
     * 重定向自定義程序的標準輸入和輸出
     */
    public bool $redirectStdinStdout = false;

    /**
     * 管道型別
     */
    public int $pipeType = 2;

    /**
     * 是否啟用協程
     */
    public bool $enableCoroutine = true;
}
```

## 使用示例

我們建立一個用於監控失敗佇列數量的子程序，當失敗佇列有資料時，報出警告。

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Contract\StdoutLoggerInterface;

#[Process(name: "demo_process")]
class DemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);

        while (true) {
            $redis = $this->container->get(\Redis::class);
            $count = $redis->llen('queue:failed');

            if ($count > 0) {
                $logger->warning('The num of failed queue is ' . $count);
            }

            sleep(1);
        }
    }
}
```

如果使用了非同步 IO，沒辦法將邏輯寫到迴圈裡時，可以嘗試以下寫法

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Swoole\Timer;

#[Process(name: "demo_process")]
class DemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        Timer::tick(1000, function(){
            var_dump(1);
            // Do something...
        });

        while (true) {
            sleep(1);
        }
    }
}
```
