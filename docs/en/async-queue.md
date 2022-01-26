# Async Queue

Async queue are distinguished from message queues such as `RabbitMQ`, `Kafka`. This component only provide an 'asynchronous processing' and 'asynchronous delay processing' capabilities, and do not strictly guarantee message persistence and support `ACK response mechanism`.

## Installation

```bash
composer require hyperf/async-queue
```

## Configuration

The configuration file is located at `config/autoload/async_queue.php`, which can be created if the file does not exist.

> Only the `Redis Driver` is supported currently.

|     Configuration      |  Type  |                   Default Value                    |        Memo        |
|:-------------:|:------:|:-------------------------------------------:|:------------------:|
|    driver     | string | Hyperf\AsyncQueue\Driver\RedisDriver::class |         None         |
|    channel    | string |                    queue                    |      The prefix of the queue      |
| retry_seconds |  int   |                      5                      | Retry the interval after failure |
|   processes   |  int   |                      1                      |     The number of consumer processes     |

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'retry_seconds' => 5,
        'processes' => 1,
    ],
];

```

## Usage

### Consume the message

The component has provided the default child process, just configure the child process into `config/autoload/processes.php`.

```php
<?php

return [
    Hyperf\AsyncQueue\Process\ConsumerProcess::class,
];
```

Of cause you could also adding the `Process` below into your application skeleton.

```php
<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\AsyncQueue\Process\ConsumerProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "async-queue")]
class AsyncQueueConsumer extends ConsumerProcess
{
}
```

### Publish a message

First we define a message job as follows

```php
<?php

declare(strict_types=1);

namespace App\Job;

use Hyperf\AsyncQueue\Job;

class ExampleJob extends Job
{
    public $params;

    public function __construct($params)
    {
        // It's best to use normal data here. Don't pass the objects that carry IO, such as PDO objects.
        $this->params = $params;
    }

    public function handle()
    {
        // Process specific logic based on parameters
        var_dump($this->params);
    }
}
```

Publish the message

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Job\ExampleJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class QueueService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('default');
    }

    /**
     * Publish the message.
     */
    public function push($params, int $delay = 0): bool
    {
        // The `ExampleJob` here will be serialized and stored in Redis, so internal variables of the object are best passed only normal data.
        // Similarly, if the annotation is used internally, @Value will serialize the corresponding object, causing the message body to become larger.
        // So it is NOT recommended to use the `make` method to create a `Job` object.
        return $this->driver->push(new ExampleJob($params), $delay);
    }
}
```

According to the actual business scenario, dynamically post messages to the asynchronous queue execution, we demonstrate the dynamic delivery of messages in the controller, as follows:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\QueueService;
use Hyperf\Di\Annotation\Inject;~~~~~~~~
use Hyperf\HttpServer\Annotation\AutoController;

#[AutoController]
class QueueController extends Controller
{
    #[Inject]
    protected QueueService $service;

    public function index()
    {
        $this->service->push([
            'group@hyperf.io',
            'https://doc.hyperf.io',
            'https://www.hyperf.io',
        ]);

        return 'success';
    }
}
```
