# ReactiveX integration

The [hyperf/reactive-x](https://github.com/hyperf/reactive-x) component provides ReactiveX integration in the Swoole/Hyperf environment.

## History of ReactiveX

ReactiveX is the abbreviation of Reactive Extensions, generally abbreviated as Rx. It was originally an extension of LINQ. It was developed by a team led by Microsoft architect Erik Meijer. It was open sourced in November 2012. Rx is a programming model. The goal is to provide consistent programming Interface to help developers handle asynchronous data streams more easily. Rx library supports .NET, JavaScript and C++. Rx has become more and more popular in recent years, and now it supports almost all popular programming languages. Most of Rx The language library is maintained by the ReactiveX organization, the more popular ones are RxJava/RxJS/Rx.NET, and the community website is [reactivex.io](http://reactivex.io).

## What is ReactiveX

Microsoft's definition is that Rx is a function library that allows developers to write asynchronous and event-based programs using observable sequences and LINQ-style query operators. Using Rx, developers can use Observables to represent asynchronous data streams, and LINQ Operators query asynchronous data streams, and use Schedulers to parameterize concurrent processing of asynchronous data streams. Rx can be defined as follows: Rx = Observables + LINQ + Schedulers.

The definition given by [Reactivex.io](http://reactivex.io) is that Rx is a programming interface for asynchronous programming using observable data streams. ReactiveX combines the essence of observer pattern, iterator pattern and functional programming .

> The above two sections are taken from [RxDocs](https://github.com/mcxiaoke/RxDocs).

## Please consider before using

### front

- By thinking of reactive programming, some complex asynchronous problems can be simplified.

- If you already have reactive programming experience in other languages ​​(such as RxJS/RxJava), this component can help you port this experience to Hyperf.

- Although Swoole recommends writing asynchronous programs like synchronous programs through coroutines, Swoole still contains a large number of events, and handling events is the strength of Rx.

- Rx can also play an important role if your business includes stream processing like WebSocket, gRPC streaming, etc.

### Negative

- The way of thinking of reactive programming is quite different from the traditional object-oriented way of thinking, which requires developers to adapt.

- Rx just provides the way of thinking, no additional magic. Problems that can be solved by reactive programming can be solved by traditional means.

- RxPHP is not the best in the Rx family.

## Install

```bash
composer require hyperf/reactive-x
```

## Package

Let us introduce some encapsulations of this component with examples and demonstrate the powerful capabilities of Rx. All examples can be found in this component under `src/Example`.

### Observable::fromEvent

`Observable::fromEvent` converts PSR standard events into observable sequences.

The event listener for printing SQL statements is provided by default in the hyperf-skeleton skeleton package, and the default location is `app/Listener/DbQueryExecutedListener.php`. Let's make some optimizations to this monitor:

1. Only print SQL queries that take more than 100ms.

2. Each connection can print up to 1 time per second to avoid the hard disk being overrun by the problem program.

Without ReactiveX, question 1 would be fine, but question 2 would require some brainstorming. With ReactiveX, these requirements can be easily solved by means of the following sample code:

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
                function ($event) {
                    return $event->time > 100;
                }
            )
            ->groupBy(
                function ($event) {
                    return $event->connectionName;
                }
            )
            ->flatMap(
                function ($group) {
                    return $group->throttle(1000);
                }
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
                function ($message) {
                    $this->logger->info(sprintf('slow log: [%s] [%s] %s', ...$message));
                }
            );
    }
}
```

### Observable::fromChannel

Turn the Channel in the Swoole coroutine into an observable sequence.

The Channel in the Swoole coroutine is one-to-one read and write. What if we want to do many-to-many subscriptions and publishing through Channels under ReactiveX?

See the example below.

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

Create one or more coroutines and turn the execution results into an observable sequence.

We now let two functions compete in concurrent coroutines, and whichever finishes executing first returns the result. The effect is similar to `Promise.race` in JavaScript.

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

All HTTP requests are actually event-driven. So HTTP request routing can also be taken over with ReactiveX.

> Since we are going to add a route, it must be executed before the Server starts, such as in the `BootApplication` event listener.

Suppose we have an upload route with a lot of traffic, which needs to be buffered in memory and uploaded in batches after ten uploads.

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

After taking over the route, if you need to control the returned Response, you can add a third parameter to fromHttpRoute, which is the same as the normal route, such as

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

At this point, `Observable` acts like middleware. After obtaining the observable sequence of the request object, it will continue to pass the request object to the real `Controller`.

### IpcSubject

Swoole's inter-process communication is also event-driven. This component additionally provides the corresponding cross-process Subject version on the basis of the four [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) provided by RxPHP, which can be used to share information between processes .

For example, we need to make a WebSocket-based chat room, the requirements are as follows:

1. Chat room messages need to be shared between `Worker processes`.

2. The last 5 messages are displayed when the user logs in for the first time.

We do this via a cross-process version of `ReplaySubject`.

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
        // The second parameter is the broadcast mode, the default is the whole process broadcast
        // The third parameter is the channel ID, each channel can only receive messages from the same channel.
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

For convenience, this component uses `IpcSubject` to encapsulate a "message bus" `MessageBusInterface`. Just inject `MessageBusInterface` to send and receive information shared by all processes (including custom processes). Functions such as configuration center can be easily implemented through it.

```php
<?php
$bus = make(Hyperf\ReactiveX\MessageBusInterface::class);
// whole process broadcast information
$bus->onNext('Hello Hyperf');
// subscription info
$bus->subscribe(function($message){
    echo $message;
});
```

> Since ReactiveX needs to use the event loop, please note that the ReactiveX related API must be called after the Swoole Server is started.

## References

* [Rx Chinese Documentation](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Rx documentation in English](http://reactivex.io/)
* [RxPHP repository](https://github.com/ReactiveX/RxPHP)
