# 定时任务

通常来说，执行定时任务会通过 Linux 的 `crontab` 命令来实现，但现实情况下，并不是所有开发人员都能够拥有生产环境的服务器去设置定时任务的，这里 [hyperf/crontab](https://github.com/hyperf/crontab) 组件为您提供了一个 `秒级` 定时任务功能，只需通过简单的定义即可完成一个定时任务的定义。 

# 安装

```bash
composer require hyperf/crontab
```

# 使用

## 启动任务调度器进程

在使用定时任务组件之前，需要先在 `config/autoload/processes.php` 内注册一下 `Hyperf\Crontab\Process\CrontabDispatcherProcess` 自定义进程，如下：

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

这样服务启动时会启动一个自定义进程，用于对定时任务的解析和调度分发。   
同时，您还需要将 `config/autoload/crontab.php` 内的 `enable` 配置设置为 `true`，表示开启定时任务功能，如配置文件不存在可自行创建，配置如下：

```php
<?php
return [
    // 是否开启定时任务
    'enable' => true,
];
```

## 定义定时任务

### 通过配置文件定义

您可于 `config/autoload/crontab.php` 的配置文件内配置您所有的定时任务，文件返回一个 `Hyperf\Crontab\Crontab[]` 结构的数组，如配置文件不存在可自行创建：

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // 通过配置文件定义的定时任务
    'crontab' => [
        // Callback类型定时任务（默认）
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('这是一个示例的定时任务'),
        // Command类型定时任务
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (optional) arguments
            'fooArgument' => 'barValue',
            // (optional) options
            '--message-limit' => 1,
            // 记住要加上，否则会导致主进程退出
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure 类型定时任务 (仅在 Coroutine style server 中支持)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

3.1 之后新增了新的配置方式，你可以通过 `config/crontabs.php` 来定义定时任务，如配置文件不存在可自行创建：

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### 通过注解定义

通过 `#[Crontab]` 注解可以快速完成对一个任务的定义，以下的定义示例与配置文件定义所达到的目的都是一样的。定义一个名为 `Foo` 每分钟执行一次 `App\Task\FooTask::execute()` 的定时任务。

```php
<?php
namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(name: "Foo", rule: "* * * * *", callback: "execute", memo: "这是一个示例的定时任务")]
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

### 任务属性

#### name

定时任务的名称，可以为任意字符串，各个定时任务之间的名称要唯一。

#### rule

定时任务的执行规则，在分钟级的定义时，与 Linux 的 `crontab` 命令的规则一致，在秒级的定义时，规则长度从 5 位变成 6 位，在规则的前面增加了对应秒级的节点，也就是 5 位时以分钟级规则执行，6 位时以秒级规则执行，如 `*/5 * * * * *` 则代表每 5 秒执行一次。注意在注解定义时，规则存在 `\` 符号时，需要进行转义处理，即填写 `*\/5 * * * * *`。

#### callback

定时任务的执行回调，即定时任务实际执行的代码，在通过配置文件定义时，这里需要传递一个 `[$class, $method]` 的数组，`$class` 为一个类的全称，`$method` 为 `$class` 内的一个 `public` 方法。当通过注解定义时，只需要提供一个当前类内的 `public` 方法的方法名即可，如果当前类只有一个 `public` 方法，您甚至可以不提供该属性。

#### singleton

解决任务的并发执行问题，任务永远只会同时运行 1 个。但是这个没法保障任务在集群时重复执行的问题。

#### onOneServer

多实例部署项目时，则只有一个实例会被触发。

#### mutexPool

互斥锁使用的 `Redis` 连接池。

#### mutexExpires

互斥锁超时时间，如果定时任务执行完毕，但解除互斥锁失败时，互斥锁也会在这个时间之后自动解除。

#### memo

定时任务的备注，该属性为可选属性，没有任何逻辑上的意义，仅供开发人员查阅帮助对该定时任务的理解。

#### enable

当前任务是否生效。

> 除了 bool 类型，还支持 string 和 array

如果 `enable` 是 `string`，则会调用当前类对应的方法，来判断此定时任务是否运行

```php
<?php

namespace App\Crontab;

use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: "isEnable", memo: "这是一个示例的定时任务")]
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

如果 `enable` 是 `array`，则会调用 `array[0]` 对应的 `array[1]`，来判断此定时任务是否运行

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

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: [EnableChecker::class, "isEnable"], memo: "这是一个示例的定时任务")]
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

设置定时任务的环境，如果不设置，则会全部环境都生效。支持传入 array 和 string。

### 调度分发策略

定时任务在设计上允许通过不同的策略来调度分发执行任务，目前仅提供了 `多进程执行策略`、`协程执行策略` 两种策略，默认为 `多进程执行策略`，后面的迭代会增加更多更强的策略。

> 当使用协程风格服务时，请使用 协程执行策略。

#### 更改调度分发策略

通过在 `config/autoload/dependencies.php` 更改 `Hyperf\Crontab\Strategy\StrategyInterface` 接口类所对应的实例来更改目前所使用的策略，默认情况下使用 `Worker 进程执行策略`，对应的类为 `Hyperf\Crontab\Strategy\WorkerStrategy`，如我们希望更改策略为一个新的策略，比如为 `App\Crontab\Strategy\FooStrategy`，那么如下：

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Worker 进程执行策略 [默认]

策略类：`Hyperf\Crontab\Strategy\WorkerStrategy`   

默认情况下使用此策略，即为 `CrontabDispatcherProcess` 进程解析定时任务，并通过进程间通讯轮询传递执行任务到各个 `Worker` 进程中，由各个 `Worker` 进程以协程来实际运行执行任务。

##### TaskWorker 进程执行策略

策略类：`Hyperf\Crontab\Strategy\TaskWorkerStrategy`   

此策略为 `CrontabDispatcherProcess` 进程解析定时任务，并通过进程间通讯轮询传递执行任务到各个 `TaskWorker` 进程中，由各个 `TaskWorker` 进程以协程来实际运行执行任务，使用此策略需注意 `TaskWorker` 进程是否配置了支持协程。

##### 多进程执行策略

策略类：`Hyperf\Crontab\Strategy\ProcessStrategy`   

此策略为 `CrontabDispatcherProcess` 进程解析定时任务，并通过进程间通讯轮询传递执行任务到各个 `Worker` 进程和 `TaskWorker` 进程中，由各个进程以协程来实际运行执行任务，使用此策略需注意 `TaskWorker` 进程是否配置了支持协程。

##### 协程执行策略

策略类：`Hyperf\Crontab\Strategy\CoroutineStrategy`   

此策略为 `CrontabDispatcherProcess` 进程解析定时任务，并在进程内为每个执行任务创建一个协程来运行。

## 运行定时任务

当您完成上述的配置后，以及定义了定时任务后，只需要直接启动 `Server`，定时任务便会一同启动。   
在您启动后，即便您定义了足够短周期的定时任务，定时任务也不会马上开始执行，所有定时任务都会等到下一个分钟周期时才会开始执行，比如您启动的时候是 `10 时 11 分 12 秒`，那么定时任务会在 `10 时 12 分 00 秒` 才会正式开始执行。

### FailToExecute 事件

当定时任务执行失败时，会触发 `FailToExecute` 事件，所以我们可以编写以下监听器，拿到对应的 `Crontab` 和 `Throwable`。

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
