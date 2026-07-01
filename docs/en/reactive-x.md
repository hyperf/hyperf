# ReactiveX Integration

The [hyperf/reactive-x](https://github.com/hyperf/reactive-x) component provides ReactiveX integration in the Swoole/Hyperf environment.

## History of ReactiveX

ReactiveX is an abbreviation for Reactive Extensions, generally abbreviated as Rx. It was originally an extension of LINQ, developed by a team led by Microsoft architect Erik Meijer, and open-sourced in November 2012. Rx is a programming model that aims to provide a consistent programming interface to help developers handle asynchronous data streams more conveniently. Rx libraries support .NET, JavaScript, and C++. Rx has become increasingly popular in recent years and now supports almost all popular programming languages. Most of the language libraries for Rx are maintained by the ReactiveX organization. Popular ones include RxJava, RxJS, and Rx.NET. The community website is [reactivex.io](http://reactivex.io).

## What is ReactiveX

Microsoft defines Rx as a function library that allows developers to write asynchronous and event-based programs using observable sequences and LINQ-style query operators. Using Rx, developers can represent asynchronous data streams with Observables, query asynchronous data streams with LINQ operators, and parameterize the concurrent processing of asynchronous data streams with Schedulers. Rx can be defined as: Rx = Observables + LINQ + Schedulers.

[Reactivex.io](http://reactivex.io) defines Rx as a programming interface for asynchronous programming using observable data streams. ReactiveX combines the essence of the Observer pattern, the Iterator pattern, and functional programming.

> The above two sections are excerpted from [RxDocs](https://github.com/mcxiaoke/RxDocs).

## Considerations before use

### Pros

- Through the thinking mode of reactive programming, some complex asynchronous problems can be simplified.

- If you have experience with reactive programming in other languages (such as RxJS/RxJava), this component can help you port this experience to Hyperf.

- Although it is recommended in Swoole to write asynchronous programs like synchronous programs through coroutines, Swoole still contains a large number of events, and processing events is exactly the strong point of Rx.

- If your business contains stream processing, such as WebSocket, gRPC streaming, etc., Rx can also play an important role.

### Cons

- The thinking mode of reactive programming is quite different from the traditional object-oriented thinking mode, and developers need time to adapt.

- Rx only provides a thinking mode and has no extra magic. Problems that can be solved through reactive programming can also be solved through traditional methods.

- RxPHP is not the best in the Rx family.

## Installation

```bash
composer require hyperf/reactive-x
```

## Encapsulation

Below, we combine examples to introduce some encapsulations of this component and demonstrate the powerful capabilities of Rx. All examples can be found under `src/Example` of this component.

### Observable::fromEvent

`Observable::fromEvent` converts PSR standard events into observable sequences.

In the hyperf-skeleton skeleton package, an event listener for printing SQL statements is provided by default, located at `app/Listener/DbQueryExecutedListener.php` by default. Below, we perform some optimizations on this listener:

1. Only print SQL queries that exceed 100ms.

2. Print at most once per second for each connection to avoid the hard drive being flooded by problematic programs.

Without ReactiveX, issue 1 is fine, but issue 2 would require some effort. With ReactiveX, you can easily solve these requirements using the example code below:

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ReactiveX\Observable;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;

class SqlListener implements ListenerInterface
{
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        Observable::fromEvent(QueryExecuted::class)
            ->filter(
                fn ($event) => $event->time > 100
            )
            ->groupBy(
                fn ($event) => $event->connectionName
            )
            ->flatMap(
                fn ($group) => $group->throttle(1000)
            )
            ->map(
                function ($event) {
                    $sql = $event->sql;
                    if (! Arr::isAssoc($event->bindings)) {
                        foreach ($event->bindings as $key => $value) {
                            $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                        }
                    }
                    return [$event->connectionName, $event->time, $sql];
                }
            )->subscribe(
                fn ($message) => $this->logger->info(sprintf('slow log: [%s] [%s] %s', ...$message))
            );
    }
}
```

### Observable::fromChannel

Converts a Channel in Swoole coroutines to an observable sequence.

Channels in Swoole coroutines are one-to-one for reading and writing. If we want to implement multi-to-multi subscription and publishing using Channels in ReactiveX, how should we do it?

Please refer to the example below.

```php
<?php

declare(strict_types=1);

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;

$chan = new Channel(1);
$pub = Observable::fromChannel($chan)->publish();

$pub->subscribe(function ($x) {
    echo 'First Subscription:' . $x . PHP_EOL;
});
$pub->subscribe(function ($x) {
    echo 'Second Subscription:' . $x . PHP_EOL;
});
$pub->connect();

$chan->push('hello');
$chan->push('world');

// First Subscription: hello
// Second Subscription: hello
// First Subscription: world
// Second Subscription: world
```

### Observable::fromCoroutine

Creates one or more coroutines and converts the execution results into an observable sequence.

Now we let two functions compete in concurrent coroutines and return the result of whichever finishes first. The effect is similar to `Promise.race` in JavaScript.

```php
<?php

declare(strict_types=1);

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;

$result = new Channel(1);
$o = Observable::fromCoroutine([function () {
    sleep(2);
    return 1;
}, function () {
    sleep(1);
    return 2;
}]);
$o->take(1)->subscribe(
    function ($x) use ($result) {
        $result->push($x);
    }
);
echo $result->pop(); // 2;
```

### Observable::fromHttpRoute

All HTTP requests are actually event-driven. Therefore, HTTP request routing can also be managed by ReactiveX.

> Since we are adding routes, be sure to execute this before the Server starts, such as in the `BootApplication` event listener.

Assume we have an upload route with heavy traffic that needs to be buffered in memory and processed in batches after ten uploads.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ReactiveX\Observable;
use Psr\Http\Message\RequestInterface;

class BatchSaveRoute implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        Observable::fromHttpRoute(['POST', 'PUT'], '/save')
            ->map(
                function (RequestInterface $request) {
                    return $request->getBody();
                }
            )
            ->bufferWithCount(10)
            ->subscribe(
                function (array $bodies) {
                    echo count($bodies); //10
                }
            );
    }
}
```

After taking over the routing, if you need to control the returned Response, you can add a third parameter in `fromHttpRoute`, which is the same as the normal route writing method, such as:

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

At this time, `Observable` acts like a middleware. After obtaining the observable sequence of the request object, it will continue to pass the request object to the real `Controller`.

### IpcSubject

Swoole's inter-process communication is also event-driven. On the basis of the four [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) provided by RxPHP, this component additionally provides corresponding cross-process Subject versions, which can be used to share information between processes.

For example, we need to create a WebSocket-based chat room with the following requirements:

1. Chat room messages need to be shared between `Worker processes`.

2. The latest 5 messages are displayed when the user logs in for the first time.

We implement this through the cross-process version of `ReplaySubject`.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\IpcSubject;
use Rx\Subject\ReplaySubject;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    private IpcSubject $subject;

    private $subscriber = [];

    public function __construct(BroadcasterInterface $broadcaster)
    {
        $relaySubject = make(ReplaySubject::class, ['bufferSize' => 5]);
        // The first parameter is the original RxPHP Subject object.
        // The second parameter is the broadcast method, which defaults to full-process broadcasting
        // The third parameter is the channel ID. Each channel can only receive messages from the same channel.
        $this->subject = new IpcSubject($relaySubject, $broadcaster, 1);
    }

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $this->subject->onNext($frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $this->subscriber[$fd]->dispose();
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $this->subscriber[$request->fd] = $this->subject->subscribe(function ($data) use ($server, $request) {
            $server->push($request->fd, $data);
        });
    }
}
```

For ease of use, this component uses `IpcSubject` to encapsulate a "message bus" `MessageBusInterface`. You only need to inject `MessageBusInterface` to send and receive information shared across all processes (including custom processes). Features such as configuration centers can be easily implemented through it.

```php
<?php
$bus = make(Hyperf\ReactiveX\MessageBusInterface::class);
// Broadcast information across all processes
$bus->onNext('Hello Hyperf');
// Subscribe to information
$bus->subscribe(function($message){
    echo $message;
});
```

> Since ReactiveX needs to use the event loop, please be sure to call ReactiveX-related APIs only after the Swoole Server is started.

## References

* [Rx Chinese Documentation](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Rx English Documentation](http://reactivex.io/)
* [RxPHP Repository](https://github.com/ReactiveX/RxPHP)

