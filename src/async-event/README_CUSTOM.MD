# Hyperf Async Event

## Only some events are asynchronous?

## Custom Configuration manual
```php
//custom configuration
//you need edit below files
$eventForAsync = new \StdClass();
$eventForAsync->is_support_async = false; 
\Hyperf\Utils\ApplicationContext::getContainer()->get(EventDispatcherInterface::class)->dispatch($eventForAsync);
```
### file \App\Dependencies\MyEventDispatcherFactory.php;
```php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @contact  wlfkongl@163.com
 */
namespace App\Dependencies;

use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class  MyEventDispatcherFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $listeners = $container->get(ListenerProviderInterface::class);
        $stdoutLogger = $container->get(StdoutLoggerInterface::class);
        return new MyEventDispatcher($listeners, $stdoutLogger);
    }
}
```
### file \App\Dependencies\MyEventDispatcher.php;
```php
declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
namespace App\Dependencies;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

class MyEventDispatcher implements EventDispatcherInterface
{
    /**
     * @var ListenerProviderInterface
     */
    private $listeners;

    /**
     * @var null|StdoutLoggerInterface
     */
    private $logger;

    public function __construct(
        ListenerProviderInterface $listeners,
        ?StdoutLoggerInterface $logger = null
    ) {
        $this->listeners = $listeners;
        $this->logger = $logger;
    }
    public function dispatch(object $event)
    {
        $is_support_async = $event->is_support_async ?? false;
        $delay = $event->async_delay ?? 0;
        if ($is_support_async) { // only $event->is_support_async == true
            $this->logger->debug(__METHOD__.":async:".get_class($event));
            //do something (Saving data in db)
            $driver_factory = ApplicationContext::getContainer()->get(DriverFactory::class);
            (new \App\AsyncQueue\JobService\AsyncEventDispatchJobService($driver_factory))->push($event, $delay); //推到异步队列中
            return $event;
        } else {
            return $this->dispatchNow($event);
        }
    }

    /**
     * Provide all listeners with an event to process.
     *
     * @param object $event The object to process
     * @return object The Event that was passed, now modified by listeners
     */
    public function dispatchNow(object $event)
    {
        foreach ($this->listeners->getListenersForEvent($event) as $listener) {
            $listener($event);
            $this->dump($listener, $event);
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }

    /**
     * Dump the debug message if $logger property is provided.
     * @param mixed $listener
     */
    private function dump($listener, object $event)
    {
        if (!$this->logger instanceof StdoutLoggerInterface) {
            return;
        }
        $eventName = get_class($event);
        $listenerName = '[ERROR TYPE]';
        if (is_array($listener)) {
            $listenerName = is_string($listener[0]) ? $listener[0] : get_class($listener[0]);
        } elseif (is_string($listener)) {
            $listenerName = $listener;
        } elseif (is_object($listener)) {
            $listenerName = get_class($listener);
        }
        $this->logger->debug(sprintf('Event %s handled by %s listener.', $eventName, $listenerName));
    }
}
```
### file \App\AsyncQueue\JobService\AsyncEventDispatchJobService.php
```php
declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
namespace App\AsyncQueue\JobService;

use App\AsyncQueue\Job\Event\EventDispatchJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Driver\DriverInterface;

class AsyncEventDispatchJobService
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverFactory $driverFactory)
    {
        $this->driver = $driverFactory->get('async_event_queue');
    }

    /**
     * @param $object
     */
    public function push(object $object, int $delay = 0): bool
    {
        $this->driver->push(new EventDispatchJob($object), $delay);
        return true;
    }
}
```

### file \App\AsyncQueue\Job\Event\EventDispatchJob.php
```php
declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
namespace App\AsyncQueue\Job\Event;

use App\Dependencies\MyEventDispatcher;
use Hyperf\AsyncQueue\Job;
use Hyperf\Utils\ApplicationContext;

class EventDispatchJob extends Job
{
    public $object;

    protected $maxAttempts = 0;

    public function __construct(object $object)
    {
        $this->object = $object;
    }

    public function handle()
    {
        //do something (Fetch data from db)
        ApplicationContext::getContainer()->get(MyEventDispatcher::class)->dispatchNow($this->object);
    }
}

```

### file config/autoload/dependencies.php
```php
declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
return [
    \Psr\EventDispatcher\EventDispatcherInterface::class => \App\Dependencies\MyEventDispatcherFactory::class,
];
```

### file config/autoload/processes.php
```php
declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
return [
    \Hyperf\AsyncEvent\AsyncEventDispatchProcess::class,
];
```
