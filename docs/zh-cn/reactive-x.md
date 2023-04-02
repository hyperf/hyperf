# ReactiveX 集成

[hyperf/reactive-x](https://github.com/hyperf/reactive-x) 组件提供了 Swoole/Hyperf 环境下的 ReactiveX 集成。

## ReactiveX 的历史

ReactiveX 是 Reactive Extensions 的缩写，一般简写为 Rx，最初是 LINQ 的一个扩展，由微软的架构师 Erik Meijer 领导的团队开发，在 2012 年 11 月开源，Rx 是一个编程模型，目标是提供一致的编程接口，帮助开发者更方便的处理异步数据流，Rx 库支持.NET、JavaScript 和 C ++，Rx 近几年越来越流行了，现在已经支持几乎全部的流行编程语言了，Rx 的大部分语言库由 ReactiveX 这个组织负责维护，比较流行的有 RxJava/RxJS/Rx.NET，社区网站是 [reactivex.io](http://reactivex.io)。

## 什么是 ReactiveX

微软给的定义是，Rx 是一个函数库，让开发者可以利用可观察序列和 LINQ 风格查询操作符来编写异步和基于事件的程序，使用 Rx，开发者可以用 Observables 表示异步数据流，用 LINQ 操作符查询异步数据流， 用 Schedulers 参数化异步数据流的并发处理，Rx 可以这样定义：Rx = Observables + LINQ + Schedulers。

[Reactivex.io](http://reactivex.io) 给的定义是，Rx 是一个使用可观察数据流进行异步编程的编程接口，ReactiveX 结合了观察者模式、迭代器模式和函数式编程的精华。

> 以上两节摘自[RxDocs](https://github.com/mcxiaoke/RxDocs)。

## 使用前请考虑

### 正面

- 通过响应式编程的思考方式，可以将一些复杂异步问题化繁为简。

- 如果您已经在其他语言有过响应式编程经验(如 RxJS/RxJava)，本组件可以帮助您将这种经验移植到 Hyperf 上。

- 尽管 Swoole 中推荐通过协程像编写同步程序一样编写异步程序，但 Swoole 中仍然包含了大量事件，而处理事件正是 Rx 的强项。

- 如果您业务中包含流处理，如 WebSocket，gRPC streaming 等，Rx 也可以发挥重要作用。

### 负面

- 响应式编程的思维方式和传统面向对象思维方式差异较大，需要开发者适应。

- Rx 只是提供了思维方式，并没有额外的魔法。通过响应式编程能够解决的问题通过传统方式一样能够解决。

- RxPHP 并不是 Rx 家族中的佼佼者。

## 安装

```bash
composer require hyperf/reactive-x
```

## 封装

下面我们结合示例来介绍本组件的一些封装，并展示 Rx 的强大能力。全部示例可以在本组件 `src/Example` 下找到。

### Observable::fromEvent

`Observable::fromEvent` 将 PSR 标准事件转为可观察序列。

在 hyperf-skeleton 骨架包中默认提供了打印 SQL 语句的事件监听，默认位置于 `app/Listener/DbQueryExecutedListener.php`。下面我们对这个监听做一些优化：

1. 只打印超过 100ms 的 SQL 查询。

2. 每个连接最多 1 秒打印 1 次，避免硬盘被问题程序刷爆。

如果没有 ReactiveX，问题 1 还好说，而问题 2 应该就需要动一番脑筋了。而通过 ReactiveX，则可以通过下面的示例代码的方式轻松解决这些需求：

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
use Hyperf\Utils\Str;
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

将 Swoole 协程中的 Channel 转为可观察序列。

Swoole 协程中的 Channel 是读写一对一的。如果我们希望通过 Channel 来做多对多订阅和发布在 ReactiveX 下该怎么做呢？

请参阅下面这个例子。

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

创建一个或多个协程并将执行结果转为可观察序列。

我们现在让两个函数在并发协程中竞争，哪个先执行完毕的就返回哪个的结果。效果类似 JavaScript 中的 `Promise.race`。

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

所有的 HTTP 请求其实也是事件驱动的。所以 HTTP 请求路由也可以用 ReactiveX 来接管。

> 由于我们要添加路由，所以务必要在 Server 启动前执行，如在 `BootApplication` 事件监听中。

假设我们有一个上传路由，流量很大，需要在内存中缓冲，上传十次以后再批量入库。

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

接管路由后如果需要控制返回的 Response，可以在 fromHttpRoute 中增加第三个参数，与正常路由写法相同，如

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

此时 `Observable` 作用类似于中间件，获取请求对象可观察序列后会继续传递请求对象给真正的 `Controller`。

### IpcSubject

Swoole 的进程间通讯也是事件驱动的。本组件在 RxPHP 提供的四种 [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) 基础上额外提供了对应的跨进程 Subject 版本，可以用于在进程间共享信息。

例如，我们需要制作一个基于 WebSocket 的聊天室，需求如下：

1. 聊天室的消息需要在 `Worker 进程` 之间共享。

2. 用户第一次登录时显示最新的 5 条消息。

我们通过 `ReplaySubject` 的跨进程版本来实现。

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
        // 第一个参数为原 RxPHP Subject 对象。
        // 第二个参数为广播方式，默认为全进程广播
        // 第三个参数为频道 ID, 每个频道只能收到相同频道的消息。
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

为了方便使用，本组件利用 `IpcSubject` 封装了一条 “消息总线” `MessageBusInterface`。只需要注入 `MessageBusInterface` 就可以收发全进程共享信息（包括自定义进程）。诸如配置中心一类的功能可以通过它来轻松实现。

```php
<?php
$bus = make(Hyperf\ReactiveX\MessageBusInterface::class);
// 全进程广播信息
$bus->onNext('Hello Hyperf');
// 订阅信息
$bus->subscribe(function($message){
    echo $message;
});
```

> 由于 ReactiveX 需要使用事件循环，请注意一定要在 Swoole Server 启动之后再调用 ReactiveX 相关 API 。

## 参考资料

* [Rx 中文文档](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Rx 英文文档](http://reactivex.io/)
* [RxPHP 仓库](https://github.com/ReactiveX/RxPHP)
