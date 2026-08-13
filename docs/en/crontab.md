# Scheduled Tasks (Crontab)

Generally, scheduled tasks are implemented using the Linux `crontab` command. However, in reality, not all developers have access to production servers to set up these tasks. The [hyperf/crontab](https://github.com/hyperf/crontab) component provides a `second-level` scheduled task function that allows you to define a scheduled task with simple configuration.

# Installation

```bash
composer require hyperf/crontab
```

# Usage

## Start the task scheduler process

Before using the scheduled task component, you need to register the `Hyperf\Crontab\Process\CrontabDispatcherProcess` custom process in `config/autoload/processes.php`:

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

When the service starts, this will launch a custom process used for parsing, scheduling, and distributing scheduled tasks. Additionally, you need to set the `enable` configuration in `config/autoload/crontab.php` to `true` to enable the scheduled task function. If this file does not exist, you can create it yourself:

```php
<?php
return [
    // Whether to enable scheduled tasks
    'enable' => true,
];
```

## Defining scheduled tasks

### Definition via configuration file

You can configure all your scheduled tasks in the `config/autoload/crontab.php` configuration file. The file should return an array of `Hyperf\Crontab\Crontab` objects. If this file does not exist, you can create it yourself:

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // Scheduled tasks defined via configuration file
    'crontab' => [
        // Callback type scheduled task (default)
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('This is an example scheduled task'),
        // Command type scheduled task
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (optional) arguments
            'fooArgument' => 'barValue',
            // (optional) options
            '--message-limit' => 1,
            // Remember to include this, otherwise the main process might exit
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure type scheduled task (only supported in Coroutine style server)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

Since version 3.1, a new configuration method has been added. You can define scheduled tasks via `config/crontabs.php`. If this file does not exist, you can create it yourself:

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### Definition via Annotation

You can quickly define a task using the `#[Crontab]` annotation. The following definition example achieves the same goal as the configuration file definition. This defines a scheduled task named `Foo` that executes `App\Task\FooTask::execute()` every minute.

```php
<?php
namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(name: "Foo", rule: "* * * * *", callback: "execute", memo: "This is an example scheduled task")]
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

### Task Attributes

#### name
The name of the scheduled task, which can be any string. The names of all scheduled tasks must be unique.

#### rule
The execution rule of the scheduled task. When defined at the minute level, it is consistent with the rules of the Linux `crontab` command. When defined at the second level, the rule length changes from 5 to 6 digits, adding a corresponding second-level node at the beginning of the rule. That is, a 5-digit rule executes at the minute level, and a 6-digit rule executes at the second-level, such as `*/5 * * * * *`, which means it executes every 5 seconds. Note that when defining via annotations, you need to escape the `\` symbol in the rule, i.e., write `*\/5 * * * * *`.

#### callback
The execution callback of the scheduled task, which is the actual code to be executed. When defined through a configuration file, you need to pass an array `[$class, $method]`, where `$class` is the fully qualified class name, and `$method` is a `public` method within that class. When defined via annotation, you only need to provide the method name of a `public` method within the current class. If the current class only has one `public` method, you can even omit this attribute.

#### singleton
Solves the problem of concurrent execution of tasks; at most one instance of the task will run at any given time. However, this does not guarantee against repeated execution of tasks in a cluster.

#### onOneServer
When deploying the project in multiple instances, only one instance will be triggered.

#### mutexPool
The `Redis` connection pool used for the mutex lock.

#### mutexExpires
The timeout for the mutex lock. If the scheduled task completes execution but fails to release the mutex lock, the lock will automatically be released after this time.

#### memo
A note for the scheduled task. This attribute is optional and has no logical significance; it is only for developers to read to help understand the task.

#### enable
Whether the current task is enabled.

> Besides boolean, string and array types are also supported.

If `enable` is a `string`, the method corresponding to the current class will be called to determine whether this scheduled task runs:

```php
<?php

namespace App\Crontab;

use Carbon\Carbon;
use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: "isEnable", memo: "This is an example scheduled task")]
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

If `enable` is an `array`, the method defined in `array[1]` of the class defined in `array[0]` will be called to determine whether this scheduled task runs:

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

#[Crontab(name: "Echo", rule: "* * * * *", callback: "execute", enable: [EnableChecker::class, "isEnable"], memo: "This is an example scheduled task")]
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
Set the environment for the scheduled task. If not set, it will be effective in all environments. Supports array and string inputs.

### Scheduling and Distribution Strategy
The scheduled task is designed to allow tasks to be scheduled and distributed using different strategies. Currently, only `Multi-process Execution Strategy` and `Coroutine Execution Strategy` are provided. The default is `Multi-process Execution Strategy`, and more powerful strategies will be added in later iterations.

> When using a Coroutine style server, please use the Coroutine Execution Strategy.

#### Changing the Scheduling and Distribution Strategy
You can change the strategy currently in use by changing the instance corresponding to the `Hyperf\Crontab\Strategy\StrategyInterface` interface class in `config/autoload/dependencies.php`. By default, the `Worker Process Execution Strategy` is used, corresponding to the class `Hyperf\Crontab\Strategy\WorkerStrategy`. If we want to change the strategy to a new one, for example, `App\Crontab\Strategy\FooStrategy`, you can do the following:

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Worker Process Execution Strategy [Default]
Strategy Class: `Hyperf\Crontab\Strategy\WorkerStrategy`

This strategy is used by default. The `CrontabDispatcherProcess` process parses scheduled tasks and polls them to pass the tasks to each `Worker` process for execution via inter-process communication. Each `Worker` process then executes the tasks as coroutines.

##### TaskWorker Process Execution Strategy
Strategy Class: `Hyperf\Crontab\Strategy\TaskWorkerStrategy`

In this strategy, the `CrontabDispatcherProcess` process parses scheduled tasks and polls them to pass the tasks to each `TaskWorker` process for execution via inter-process communication. Each `TaskWorker` process then executes the tasks as coroutines. Note whether the `TaskWorker` process is configured to support coroutines when using this strategy.

##### Multi-process Execution Strategy
Strategy Class: `Hyperf\Crontab\Strategy\ProcessStrategy`

In this strategy, the `CrontabDispatcherProcess` process parses scheduled tasks and polls them to pass the tasks to each `Worker` process and `TaskWorker` process for execution via inter-process communication. Each process then executes the tasks as coroutines. Note whether the `TaskWorker` process is configured to support coroutines when using this strategy.

##### Coroutine Execution Strategy
Strategy Class: `Hyperf\Crontab\Strategy\CoroutineStrategy`

In this strategy, the `CrontabDispatcherProcess` process parses scheduled tasks and creates a coroutine within the process to execute each task.

## Running Scheduled Tasks

Once you have completed the configuration above and defined the scheduled tasks, you only need to start the `Server`, and the scheduled tasks will start simultaneously.

After starting, even if you have defined a scheduled task with a short cycle, it will not start execution immediately. All scheduled tasks will wait until the next minute cycle to start execution. For example, if you start at `10:11:12`, the scheduled tasks will formally start execution at `10:12:00`.

### FailToExecute Event
When a scheduled task fails to execute, a `FailToExecute` event is triggered. We can write the following listener to receive the corresponding `Crontab` and `Throwable`.

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
