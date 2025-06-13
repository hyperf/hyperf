# 自定义进程

[hyperf/process](https://github.com/hyperf/process) 可以添加一个用户自定义的工作进程，此函数通常用于创建一个特殊的工作进程，用于监控、上报或者其他特殊的任务。在 Server 启动时会自动创建进程，并执行指定的子进程函数，进程意外退出时，Server 会重新拉起进程。

## 创建一个自定义进程

在任意位置实现一个继承 `Hyperf\Process\AbstractProcess` 的子类，并实现接口方法 `handle(): void`，方法内实现您的逻辑代码，我们通过代码来举例：

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // 您的代码 ...
    }
}
```

这样即完成了一个自定义进程类，但该自定义进程类尚未被注册到 `进程管理器(ProcessManager)` 内，我们可以通过 `配置文件` 或 `注解` 两种方式的任意一种来完成注册工作。

### 通过配置文件注册

只需在 `config/autoload/processes.php` 内加上您的自定义进程类即可：

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### 通过注解注册

只需在自定义进程类上定义 `#[Process]` 注解，Hyperf 会收集并自动完成注册工作：

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
        // 您的代码 ...
    }
}
```

> 使用 `#[Process]` 注解时需 `use Hyperf\Process\Annotation\Process;` 命名空间；   

## 为进程启动加上条件

有些时候，并不是所有时候都应该启动一个自定义进程，一个自定义进程的启动与否可能会根据某些配置或者某些条件来决定，我们可以通过在自定义进程类内重写 `isEnable(): bool` 方法来实现，默认返回 `true`，即会跟随服务一同启动，如方法返回 `false`，则服务启动时不会启动该自定义进程。

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
        // 您的代码 ...
    }
    
    public function isEnable($server): bool
    {
        // 不跟随服务启动一同启动
        return false;   
    }
}
```

## 设置自定义进程

自定义进程存在一些可设置的参数，均可以通过 在子类上重写参数对应的属性 或 在 `#[Process]` 注解内定义对应的属性 两种方式来进行定义。

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
     * 进程数量
     */
    public int $nums = 1;

    /**
     * 进程名称
     */
    public string $name = 'user-process';

    /**
     * 重定向自定义进程的标准输入和输出
     */
    public bool $redirectStdinStdout = false;

    /**
     * 管道类型
     */
    public int $pipeType = 2;

    /**
     * 是否启用协程
     */
    public bool $enableCoroutine = true;
}
```

## 使用示例

我们创建一个用于监控失败队列数量的子进程，当失败队列有数据时，报出警告。

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

如果使用了异步 IO，没办法将逻辑写到循环里时，可以尝试以下写法

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
