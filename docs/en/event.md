# Event Mechanism

## Foreword

The event pattern must be implemented based on [PSR-14](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-14-event-dispatcher.md).
Hyperf's event manager is implemented by [hyperf/event](https://github.com/hyperf/event) by default. This component can also be used in other frameworks or applications; you only need to introduce this component via Composer.

```bash
composer require hyperf/event
```

## Concept

The event pattern is a well-tested and reliable mechanism that is very suitable for decoupling. There are the following 3 roles:

- `Event` is the communication object passed between the application code and the `Listener`
- `Listener` is the listening object used to listen for the occurrence of the `Event`
- `EventDispatcher` is the manager object used to trigger the `Event` and manage the relationship between the `Listener` and the `Event`

To use a common and easy-to-understand example, suppose we have a `UserService::register()` method used to register an account. After the account is successfully registered, we can trigger the `UserRegistered` event through the event dispatcher. The listener listens for the occurrence of this event and performs certain operations when triggered, such as sending a success SMS message for user registration. As the business develops, we may want to do more things after the user registration is successful, such as sending a success email for user registration. At this time, we can just add another listener to listen for the `UserRegistered` event, without having to add irrelevant code inside the `UserService::register()` method.

## Using the Event Manager

> Next, we will introduce listeners through two methods: configuration and annotation. In actual use, you only need to use one of them. If both annotations and configuration exist, the listener will be triggered multiple times.

### Defining an Event

An event is actually an ordinary class used to manage state data. When triggered, application data is passed into the event, and then the listener operates on the event class. One event can be listened to by multiple listeners.

```php
<?php
namespace App\Event;

class UserRegistered
{
    // It is recommended to define public properties here so that listeners can directly use these properties, or you can provide Getters for these properties
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;    
    }
}
```

### Defining a Listener

Listeners need to implement the constraint method of the `Hyperf\Event\Contract\ListenerInterface` interface. An example is as follows.

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Contract\ListenerInterface;

class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // Return an array of events that this listener wants to listen to, can listen to multiple events at the same time
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // The code that the listener wants to execute after the event is triggered is written here, such as sending a success SMS message for user registration in this example
        // Directly access the user property of $event to obtain the parameter value passed when the event is triggered
        // $event->user;
        
    }
}
```

#### Registering Listeners via Configuration File

After defining the listener, we need to make it discoverable by the `EventDispatcher`. You can add this listener in the `config/autoload/listeners.php` configuration file *(create one if it does not exist)*. The triggering order of listeners depends on the configuration order of this configuration file:

```php
<?php
return [
    \App\Listener\UserRegisteredListener::class,
];
```

### Registering Listeners via Annotations

Hyperf also provides a more convenient way to register listeners, which is to register via the `#[Listener]` annotation. As long as this annotation is defined on the listener class and the listener class is within the `Hyperf annotation scan domain`, the registration can be automatically completed. The code example is as follows:

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // Return an array of events that this listener wants to listen to, can listen to multiple events at the same time
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // The code that the listener wants to execute after the event is triggered is written here, such as sending a success SMS message for user registration in this example
        // Directly access the user property of $event to obtain the parameter value passed when the event is triggered
        // $event->user;
    }
}
```

When registering a listener via annotation, we can define the order of the current listener by setting the `priority` property, such as `#[Listener(priority=1)]`. The underlying layer uses the `SplPriorityQueue` structure to store them, and the larger the `priority` number, the higher the priority.

> When using the `#[Listener]` annotation, you need to `use Hyperf\Event\Annotation\Listener;` namespace;

### Triggering Events

Events can only be heard by the `Listener` after being dispatched by the `EventDispatcher`. We use a piece of code to demonstrate how to trigger an event:

```php
<?php
namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\UserRegistered; 

class UserService
{
    #[Inject]
    private EventDispatcherInterface $eventDispatcher;
    
    public function register()
    {
        // We assume an User entity exists
        $user = new User();
        $result = $user->save();
        // Complete account registration logic
        // Here dispatch(object $event) will run listeners listening to this event one by one
        $this->eventDispatcher->dispatch(new UserRegistered($user));
        return $result;
    }
}
```

## Hyperf Lifecycle Events

![](imgs/hyperf-events.svg)

## Hyperf Coroutine-style Lifecycle Events

![](https://raw.githubusercontent.com/hyperf/raw-storage/main/hyperf/svg/hyperf-coroutine-events.svg)

## Precautions

### Do Not Inject `EventDispatcherInterface` in `Listener`

Because `EventDispatcherInterface` depends on `ListenerProviderInterface`, and `ListenerProviderInterface` will collect all `Listeners` when it is initialized.

If `Listener` also depends on `EventDispatcherInterface`, it will cause a circular dependency, which will lead to memory overflow.

### It Is Best to Only Inject `ContainerInterface` in `Listener`

It is best to only inject `ContainerInterface` in `Listener`, while other components are obtained through `container` in `process`. When the framework starts, `EventDispatcherInterface` will be instantiated. At this time, it is not yet a coroutine environment. If a class that may trigger coroutine switching is injected into `Listener`, it will cause the framework to fail to start.
