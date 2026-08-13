# Custom Process

[hyperf/process](https://github.com/hyperf/process) allows you to add user-defined worker processes. This is typically used to create special worker processes for monitoring, reporting, or other specific tasks. The process is automatically created when the Server starts, and the specified child process function is executed. If the process exits unexpectedly, the Server will restart it.

## Creating a custom process

To create a custom process, implement a subclass that extends `Hyperf\Process\AbstractProcess` and implement the `handle(): void` interface method, where you can put your logic. Here is an example:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code ...
    }
}
```

This completes the custom process class, but it has not yet been registered with the `ProcessManager`. You can register it using either a `configuration file` or an `annotation`.

### Registering via configuration file

Simply add your custom process class to `config/autoload/processes.php`:

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### Registering via Annotation

Simply define the `#[Process]` annotation on your custom process class, and Hyperf will collect and register it automatically:

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
        // Your code ...
    }
}
```

> When using the `#[Process]` annotation, ensure you `use Hyperf\Process\Annotation\Process;`.

## Setting launch conditions

Sometimes, you may not want to start a custom process every time. Whether or not to start a custom process might depend on certain configurations or conditions. You can achieve this by overriding the `isEnable(): bool` method within your custom process class. It returns `true` by default, meaning it will start with the service. If it returns `false`, the custom process will not start when the service starts.

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
        // Your code ...
    }
    
    public function isEnable($server): bool
    {
        // Do not follow the service startup
        return false;   
    }
}
```

## Configuring a custom process

A custom process has several configurable parameters. These can be defined either by overriding the corresponding properties in the subclass or by defining the corresponding attributes within the `#[Process]` annotation.

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
     * Process count
     */
    public int $nums = 1;

    /**
     * Process name
     */
    public string $name = 'user-process';

    /**
     * Redirect standard input and output of the custom process
     */
    public bool $redirectStdinStdout = false;

    /**
     * Pipe type
     */
    public int $pipeType = 2;

    /**
     * Whether to enable coroutines
     */
    public bool $enableCoroutine = true;
}
```

## Usage Example

We will create a child process that monitors the number of failed tasks in a queue and reports a warning when there is data in the failed queue.

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

If you are using asynchronous I/O and cannot put the logic directly into a loop, you can try the following approach:

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
