# 自定義進程

[hyperf/process](https://github.com/hyperf/process) 可以添加一個用户自定義的工作進程，此函數通常用於創建一個特殊的工作進程，用於監控、上報或者其他特殊的任務。在 Server 啓動時會自動創建進程，並執行指定的子進程函數，進程意外退出時，Server 會重新拉起進程。

## 創建一個自定義進程

在任意位置實現一個繼承 `Hyperf\Process\AbstractProcess` 的子類，並實現接口方法 `handle(): void`，方法內實現您的邏輯代碼，我們通過代碼來舉例：

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // 您的代碼 ...
    }
}
```

這樣即完成了一個自定義進程類，但該自定義進程類尚未被註冊到 `進程管理器(ProcessManager)` 內，我們可以通過 `配置文件` 或 `註解` 兩種方式的任意一種來完成註冊工作。

### 通過配置文件註冊

只需在 `config/autoload/processes.php` 內加上您的自定義進程類即可：

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### 通過註解註冊

只需在自定義進程類上定義 `#[Process]` 註解，Hyperf 會收集並自動完成註冊工作：

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
        // 您的代碼 ...
    }
}
```

> 使用 `#[Process]` 註解時需 `use Hyperf\Process\Annotation\Process;` 命名空間；   

## 為進程啓動加上條件

有些時候，並不是所有時候都應該啓動一個自定義進程，一個自定義進程的啓動與否可能會根據某些配置或者某些條件來決定，我們可以通過在自定義進程類內重寫 `isEnable(): bool` 方法來實現，默認返回 `true`，即會跟隨服務一同啓動，如方法返回 `false`，則服務啓動時不會啓動該自定義進程。

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
        // 您的代碼 ...
    }
    
    public function isEnable($server): bool
    {
        // 不跟隨服務啓動一同啓動
        return false;   
    }
}
```

## 設置自定義進程

自定義進程存在一些可設置的參數，均可以通過 在子類上重寫參數對應的屬性 或 在 `#[Process]` 註解內定義對應的屬性 兩種方式來進行定義。

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
     * 進程數量
     */
    public int $nums = 1;

    /**
     * 進程名稱
     */
    public string $name = 'user-process';

    /**
     * 重定向自定義進程的標準輸入和輸出
     */
    public bool $redirectStdinStdout = false;

    /**
     * 管道類型
     */
    public int $pipeType = 2;

    /**
     * 是否啓用協程
     */
    public bool $enableCoroutine = true;
}
```

## 使用示例

我們創建一個用於監控失敗隊列數量的子進程，當失敗隊列有數據時，報出警告。

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

如果使用了異步 IO，沒辦法將邏輯寫到循環裏時，可以嘗試以下寫法

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
