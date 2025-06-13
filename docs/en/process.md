# Custom process

[hyperf/process](https://github.com/hyperf/process) allows you to add user-defined processes. This feature is usually used to create a special process for monitoring, reporting or other special tasks. When the server starts, it will automatically create a process and execute the specified subprocess. If the process exits unexpectedly, the server will automatically restart the process.

## Create a custom process

Implement a subclass that inherits `Hyperf\Process\AbstractProcess` and implement the interface method `handle(): void`, with your logic code in the method. Let's take this code as an example:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }
}
```

This defines a custom process class, but the class has not been registered in the `ProcessManager`. We can register it using one of the two ways: `configuration file` or `annotation`.

### Register via configuration file

Just add your custom process class in `config/autoload/processes.php`:

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### Register via annotation

Just define the #[Process] annotation on the custom process class, and Hyperf will collect and automatically complete the registration work:

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
        // Your code...
    }
}
```

> When using `#[Process]` annotation, `use Hyperf\Process\Annotation\Process;` namespace is required;

## Add conditions for process startup

Sometimes a custom process should not be started at all times. Whether a custom process is started or not may be determined according to certain configurations or conditions by overriding `isEnable(): bool` method in the custom process class. The method is implemented by default with the return value of `true`, which will start with the service. If the method returns `false`, the custom process will not be started when the service starts.

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
        // Your code...
    }

    public function isEnable($server): bool
    {
        // Do not start with service startup
        return false;
    }
}
```

## Configuring a custom process

There are some configurable parameters in the custom process, which can be defined by overriding the attributes corresponding to the parameters on the subclass or defining the corresponding attributes in the `#[Process]` annotation.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process", name: "user-process", redirectStdinStdout: false, pipeType: 2, enableCoroutine: true)]
class FooProcess extends AbstractProcess
{
    /**
     * Number of processes
     * @var int
     */
    public $nums = 1;

    /**
     * Process name
     * @var string
     */
    public $name = 'user-process';

    /**
     * Redirect the standard input and output of a custom process
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * Pipe type
     * @var int
     */
    public $pipeType = 2;

    /**
     * Whether to enable coroutine
     * @var bool
     */
    public $enableCoroutine = true;
}
```

## Usage example

We create a child process to monitor the number of failure queues, and report a warning when there is data in the failure queue.

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
