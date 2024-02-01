# Task Scheduling

In most cases, the execution of scheduled tasks can be achieved through the `crontab` command of Linux. However, in some cases, configuring crontab in production environment can be inconvenient and comes with the limitation of supporting a minimum of `minute level` scheduling.

The [hyperf/crontab](https://github.com/hyperf/crontab) component provides you with a `second Level` task scheduling and makes it easy to define tasks.

# Installation

```bash
composer require hyperf/crontab
```

# Usage

## Start the scheduler process

Before using the timer task component, you need to register the `Hyperf\Crontab\Process\CrontabDispatcherProcess` in `config/autoload/processes.php`, as follows:

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

In this way, when the service starts, a custom process is started for the analysis and scheduling of tasks. At the same time, you also need to set the `enable` setting in `config/autoload/crontab.php` to `true`, which enables scheduler processing. If the configuration file doesn't exist, you can create it yourself. The configuration is as follows:

```php
<?php
return [
    // Whether to enable timed tasks
    'enable' => true,
];
```

## Define a scheduled task

### Using a configuration file

You can define all your scheduled tasks in `config/autoload/crontab.php` configuration file. The file returns an array of `Hyperf\Crontab\Crontab[]` objects. If the configuration file doesn't exist, you can create it yourself:

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // Timed tasks defined by configuration
    'crontab' => [
        // Callback type timed task (default)
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('This is an example timed task'),
        // Command type timed task
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (optional) arguments
            'fooArgument' => 'barValue',
            // (optional) options
            '--message-limit' => 1,
            // Remember to add it, otherwise it will cause the main process to exit
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure type timed task (Only supported in Coroutine style server)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

Since 3.1 a new configuration method has been added. You can define scheduled tasks through `config/crontabs.php`. If the configuration file doesn't exist, you can create it yourself:

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### Using annotations

The definition of a task can be quickly completed through the `#[Crontab]` annotation. The following definition examples and the configuration file definition achieve the same purpose. Define a timed task named `Foo` to execute `App\Task\FooTask::execute()` every minute.

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

    #[Crontab(rule: "* * * * * *", memo: "foo")]
    public function foo()
    {
        var_dump('foo');
    }
}
```

### Task configuration

#### name

The name of the timed task can be any string, and the name of each timed task must be unique.

#### rule

The execution rules of timed tasks are defined at the minute level, consistent with the rules of the Linux `crontab` command. When defined at the second level, the rule length is changed from 5 digits to 6 digits, and a second-level node is added in front of the rule. This means that it's executed at the minute-level rule for 5 digits and the second-level rule for 6 digits. For example, `*/5 * * * * *` means it will be executed every 5 seconds. Note that forward slashes in the annotation rule definition must be escaped using the backlash `\` symbol: `*\/5 * * * * *`.

#### callback

The callback that is executed by the timed task. When defined by the configuration file, an array of `[$class, $method]` is used where `$class` is the full name of a class and `$method` is a `public` method of that class. When using annotations, you only need to provide the method name of a `public` method in the current class. If the current class has only one `public` method, you don't even need to provide this attribute.

#### singleton

To solve the problem of concurrent execution of tasks, tasks will always run at the same time. But this problem cannot guarantee the repeated execution of tasks in the cluster.

#### onOneServer

When deploying a project with multiple instances, only one instance will execute a given task.

#### mutexPool

The `Redis` connection pool used by the mutex.

#### mutexExpires

The mutex lock timeout period. If the scheduled task is executed but the mutex lock fails to be released, the mutex lock will be automatically released after this time.

#### memo

Notes for the timed task. This attribute is optional and has no syntactical meaning. Its' purpose is to help developers understand the timed task.

#### enable

Whether the current task is effective.

#### environments

The environment variables that need to be set when executing the task. The value of this attribute is an array, and the key is the name of the environment variable, and the value is the value of the environment variable.

### Scheduling distribution strategy

Timed tasks are designed to allow different strategies to be used for scheduling and distributing execution of tasks.

> When using coroutine style services, please use the coroutine execution strategy.

#### Customizing scheduling distribution strategy

You can change the currently used strategy by changing the instance corresponding to the `Hyperf\Crontab\Strategy\StrategyInterface` interface class in `config/autoload/dependencies.php`. By default, the `Worker process execution strategy` is used, and the corresponding class is `Hyperf\Crontab\Strategy\WorkerStrategy`. For example, if we wanted to use `App\Crontab\Strategy\FooStrategy`:

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Worker process execution strategy [default]

Class: `Hyperf\Crontab\Strategy\WorkerStrategy`

By default, this strategy is used. The `CrontabDispatcherProcess` process parses scheduled tasks and passes the execution tasks to each `worker` process through inter-process communication polling. Each `worker` process then uses a coroutine to actually execute the task.

##### TaskWorker execution strategy

Class: `Hyperf\Crontab\Strategy\TaskWorkerStrategy`

This strategy parses the scheduled tasks for the `CrontabDispatcherProcess` process and passes the execution tasks to each `TaskWorker` process through inter-process communication polling. Each `TaskWorker` process then uses the coroutine to actually execute the task. When using this strategy, pay attention to whether the `TaskWorker` process is configured with a supported protocol.

##### Multi-process execution strategy

Class: `Hyperf\Crontab\Strategy\ProcessStrategy`

This strategy parses scheduled tasks for the `CrontabDispatcherProcess` process and transfers the execution tasks to each `Worker` process and `TaskWorker` process through inter-process communication polling. Each process then uses a coroutine to actually execute tasks. Use this strategy to pay attention to whether the `TaskWorker` process is configured to support coroutines.

##### Coroutine execution strategy

Class: `Hyperf\Crontab\Strategy\CoroutineStrategy`

This strategy parses scheduled tasks for the `CrontabDispatcherProcess` process and creates a coroutine to run for each execution task in the process.

## Running timed tasks

After you complete the above configuration and define the scheduled tasks, you only need to directly start the `Server`, and the timed tasks will start together. After you start, even if you define a timed task with a short enough period, the timed task will not start immediately. All the timed tasks will not start until the next minute period. For example, when you start it is `10:11 12 seconds`, then the timed task will officially start execution at `10:12:00`.
