# Coroutine

## Concept

Hyperf runs on top of `Swoole 5` coroutines and `Swow` coroutines, which is one of the major factors enabling Hyperf to provide high performance.

### Operating Mode of PHP-FPM

Before discussing what coroutines are, let's look at the operating mode of the traditional `PHP-FPM` architecture. `PHP-FPM` is a multi-process `FastCGI` manager and is the operating mode used by the vast majority of `PHP` applications. Assuming we use `Nginx` to provide `HTTP` services (the same applies to `Apache`), all requests initiated by clients first reach `Nginx`, which then forwards the request to `PHP-FPM` for processing via the `FastCGI` protocol. `PHP-FPM`'s `Worker processes` preemptively acquire CGI requests for processing. This processing means waiting for the parsing of the `PHP` script and waiting for the business processing result to be returned. After completion, the sub-process is reclaimed. The whole process is blocking and waiting, which means that the number of requests `PHP-FPM` can process depends on the number of `Worker processes` it has. Assuming `PHP-FPM` has `200` `Worker processes` and one request takes `1` second, theoretically, the entire server can handle at most `200` requests, and the `QPS` is `200/s`. In high-concurrency scenarios, such performance is often insufficient. Although `Nginx` can be used as a load balancer in combination with multiple `PHP-FPM` servers to provide services, due to the blocking and waiting work model of `PHP-FPM`, one request occupies at least one `MySQL` connection. Under high concurrency across multiple nodes, this will generate a large number of `MySQL` connections, and the default value of the maximum number of `MySQL` connections is `100`. Although it can be modified, it is obvious that this model cannot cope well with high-concurrency scenarios.

### Asynchronous Non-blocking System

In high-concurrency scenarios, asynchronous non-blocking models have obvious advantages. The intuitive advantage is that the `Worker process` no longer synchronously blocks to handle a request, but can handle multiple requests simultaneously, without `I/O` waiting, resulting in extremely high concurrency capabilities and the ability to initiate or maintain a large number of requests simultaneously. The most intuitive disadvantage, as you may already know, is the endless callbacks. Business logic must be implemented within the corresponding callback functions. If the business logic requires multiple `I/O` requests, there will be many layers of callback functions. The following is a snippet of pseudo-code for an asynchronous callback type in `Swoole 1.x`.

```php
$db = new swoole_mysql();
$config = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'test',
    'password' => 'test',
    'database' => 'test',
);

$db->connect($config, function ($db, $r) {
    // Query a record from the users table
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r !== false) {
            // Update a record after a successful query
            $updateSql = 'update users set name="new name" where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                $rows = $db->affected_rows;
                if ($r === true) {
                    return $this->response->end('Update successful');
                }
            });
        }
        $db->close();
    });
});
```

> Note that asynchronous modules such as `MySQL` have been removed in [4.3.0](https://wiki.swoole.com/#/version/bc?id=430) and moved to [swoole_async](https://github.com/swoole/ext-async).

As can be seen from the code snippet above, almost every operation requires a callback function. In complex business scenarios, the layering of callbacks and the code structure will definitely make you miserable. In fact, it is not difficult to see that this way of writing is somewhat similar to the asynchronous method writing in `JavaScript`, and `JavaScript` has provided many solutions for this (of course, the solutions originate from other programming languages), such as `Promise`, `yield + generator`, `async/await`. `Promise` is a way to encapsulate callbacks, while `yield + generator` and `async/await` require explicit code syntax markers to be added to the code. Compared to callback functions, these are all very good solutions, but you need to spend extra time understanding their implementation mechanisms and syntax.
`Swoole` coroutines are also a solution for asynchronous callbacks. In the `PHP` language, both `Swoole` coroutines and `yield + generator` belong to coroutine solutions. Coroutine solutions allow code to be written in a way that is almost synchronous while being asynchronous. The explicit difference is that under the `yield + generator` coroutine mechanism, every `I/O` operation call code needs to have the `yield` syntax added in front to implement coroutine switching, and every layer of calls needs to be added, otherwise unexpected errors will occur. The `Swoole` coroutine solution is much smarter than this. When encountering `I/O`, the underlying layer automatically performs implicit coroutine switching, without adding any extra syntax, without adding `yield` before the code, and the coroutine switching process is silent, which greatly reduces the mental burden of maintaining asynchronous systems.

### What is a Coroutine?

We already know that coroutines can well solve the development problems of asynchronous non-blocking systems, but what exactly is a coroutine itself? By definition, **a coroutine is a lightweight thread that is scheduled and managed by user code, rather than by the operating system kernel, meaning it runs in user space**. It can be directly understood as a non-standard thread implementation, but when to switch is implemented by the user, not determined by the operating system allocating `CPU` time. Specifically, each `Worker process` in `Swoole` has a coroutine scheduler to schedule coroutines. The timing for coroutine switching is when an `I/O` operation is encountered or when the code explicitly switches. Coroutines run in a single-threaded manner within the process, which means that only one coroutine will be running in a process at the same time, and the switching timing is clear, so there is no need to deal with the various synchronization lock problems as in multi-threaded programming.

The code running within a single coroutine is still serial. To understand it in terms of an HTTP coroutine service, each request is a coroutine. For example, suppose `Coroutine A` is created for `Request A`, and `Coroutine B` is created for `Request B`. When processing `Coroutine A`, the code reaches a `MySQL` query statement. At this time, `Coroutine A` will trigger a coroutine switch, and `Coroutine A` will continue to wait for the `I/O` device to return the result. At this time, it will switch to `Coroutine B`, and start processing `Coroutine B`'s logic. When another `I/O` operation is encountered, it triggers a coroutine switch, and then it switches back to continue execution from where `Coroutine A` left off. This process repeats: when an `I/O` operation is encountered, it switches to another coroutine to continue execution instead of blocking and waiting.

A problem can be found here: **The `MySQL` query operation in `Coroutine A` must be an asynchronous non-blocking operation, otherwise, blocking will cause the coroutine scheduler to be unable to switch to another coroutine to continue execution**. This is also one of the problems to be avoided in coroutine programming.

### What are the Differences between Coroutines and Ordinary Threads?

It is often said that a coroutine is a lightweight thread. Both coroutines and threads are suitable for multi-tasking scenarios. From this perspective, coroutines and threads are very similar, both have their own context and can share global variables. But the difference is that multiple threads can be in a running state at the same time, but for `Swoole` coroutines, only one can be in a running state, and other coroutines will be in a paused state. In addition, ordinary threads are preemptive, and the operating system decides which thread gets resources, while coroutines are cooperative, and the execution power is allocated by the user space itself.

## Precautions for Coroutine Programming

### No Blocking Code Allowed

Blocking code in a coroutine will cause the coroutine scheduler to be unable to switch to another coroutine to continue executing code, so we absolutely must not have blocking code in a coroutine. Suppose we start `4` `Workers` to handle `HTTP` requests (usually the number of `Workers` started is the same as the number of `CPU` cores or `2` times), if there is blocking in the code, and we theoretically assume that each request will block for `1` second, then the system's `QPS` will also degenerate to `4/s`, which is undoubtedly a degeneration into a situation similar to `PHP-FPM`, so we absolutely must not have blocking code in a coroutine.

So what exactly is blocking code? We can simply assume that most of the `MySQL`, `Redis`, `Memcache`, `MongoDB`, `HTTP`, `Socket`, etc., clients you know that are not provided by `Swoole` as asynchronous functions, as well as file operations, `sleep/usleep`, etc., are blocking functions. This covers almost all daily operations. So how to solve it? `Swoole` provides coroutine clients for `MySQL`, `PostgreSQL`, `Redis`, `HTTP`, `Socket` that can be used. At the same time, after `Swoole 4.1`, a one-click coroutine method `\Swoole\Runtime::enableCoroutine()` is provided. You only need to run this line of code before using coroutines. `Swoole` will turn all socket operations using `php_stream` into asynchronously `I/O` scheduled by coroutines. It can be understood that except for `curl`, most native operations are applicable. For more information about this part, please refer to the [Swoole Documentation](https://wiki.swoole.com/#/runtime).

In `Hyperf`, we have already handled all of this for you. You only need to pay attention to blocking code that `\Swoole\Runtime::enableCoroutine()` still cannot make coroutine-friendly.

### Cannot Store State via Global Variables

In `Swoole`'s persistent application, a global variable within a `Worker` is shared within the `Worker`. From the introduction of coroutines, we know that there will be multiple coroutines within the same `Worker` and coroutine switching will occur. This means that a `Worker` will process code for multiple coroutines (or simply understood as requests) at the same time in a time cycle, which means that if global variables are used to store state, they may be used by multiple coroutines, that is, data may be mixed up between different requests. The global variables here refer to variables starting with `$_`, such as `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`, `global` variables, and `static` properties.

So what should we do when we need to use these features?

For global variables, they are all generated following a `Request`. The `Request/Response` of `Hyperf` is handled by [hyperf/http-message](https://github.com/hyperf/http-message) by implementing [PSR-7](https://www.php-fig.org/psr/psr-7/), so all global variables can get relevant values in the `Request` object;

For `global` variables and `static` variables, in `PHP-FPM` mode, they essentially live within a request lifecycle, while in `Hyperf`, because it is a `CLI` application, there are two long lifecycles: `global cycle` and `request cycle (coroutine cycle)`.
- Global cycle: We only need to create a static variable for global invocation. Static variables mean that after the service starts, any coroutine and code logic share the data within this static variable, which means the data stored cannot be specifically served for a certain request or a certain coroutine;
- Coroutine cycle: Since `Hyperf` automatically creates a coroutine for each request to process it, a coroutine cycle here can also be understood as a request cycle. Within a coroutine, all state data should be stored in the `Hyperf\Context\Context` class. You can read and store data of any structure through the `get` and `set` methods of this class. The data read or stored by this `Context (coroutine context)` class when executing any coroutine is limited to the corresponding coroutine, and the related context data will be automatically destroyed when the coroutine ends.

### Maximum Coroutine Number Limit

Set the `max_coroutine` parameter through the `set` method on the `Swoole Server` to configure the maximum number of coroutines that can exist in a `Worker` process. Because as the number of coroutines processed by a `Worker` process increases, the corresponding memory occupation will also increase. To avoid exceeding `PHP`'s `memory_limit` limit, please set this value according to the stress test results of the actual business. The default value of `Swoole` is `100000` (the default value is `3000` when the `Swoole` version is less than `v4.4.0-beta`). In the `hyperf-skeleton` project, it is set to `100000` by default.

## Using Coroutines

### Creating a Coroutine

Simply create a coroutine via the `Hyperf\Coroutine\co(callable $callable)`, `Hyperf\Coroutine\go(callable $callable)` functions, or `Hyperf\Coroutine\Coroutine::create(callable $callable)`. Within the coroutine, you can use coroutine-related methods and clients.

### Judging Whether the Current Environment is a Coroutine Environment

In some cases, we want to judge whether we are currently running in a coroutine environment. For some code that is compatible with both coroutine and non-coroutine environments, this can serve as a basis for judgment. We can get the result through the `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` method.

### Getting the Current Coroutine ID

In some cases, we need to do some logic based on the `Coroutine ID`, such as logic for `Coroutine Context`. You can get the current `Coroutine ID` through `Hyperf\Coroutine\Coroutine::id(): int`. If not in a coroutine environment, `-1` will be returned.

### Channel

Similar to `chan` in the `Go` language, `Channel` provides support for multi-producer coroutine and multi-consumer coroutine modes. The underlying layer automatically implements coroutine switching and scheduling. `Channel` is similar to `PHP` arrays, occupying only memory, with no other extra resource application. All operations are memory operations, with no `I/O` consumption. Its usage is similar to `SplQueue` queues.
`Channel` is mainly used for communication between coroutines. When we want to return some data from one coroutine to another, we can pass it through `Channel`.

Main methods:
- `Channel->push`: When other coroutines are waiting to `pop` data in the queue, automatically wake up a consumer coroutine in order. When the queue is full, automatically `yield` to give up control, and wait for other coroutines to consume data.
- `Channel->pop`: When the queue is empty, automatically `yield`, and wait for other coroutines to produce data. After consuming data, the queue can write new data, and automatically wake up a producer coroutine in order.

The following is a simple example of communication between coroutines:

```php
<?php
co(function () {
    $channel = new \Swoole\Coroutine\Channel();
    co(function () use ($channel) {
        $channel->push('data');
    });
    $data = $channel->pop();
});
```

### Defer Feature

When we want to run some code when a coroutine ends, we can use the `defer(callable $callable)` function or `Hyperf\Coroutine::defer(callable $callable)` to store a function in the form of a `stack`. The functions in the `stack` will be executed one by one in a `first-in-last-out` process when the current coroutine ends.

### WaitGroup Feature

`WaitGroup` is a feature derived from `Channel`. If you have been exposed to the `Go` language, we all know the `WaitGroup` feature. In `Hyperf`, the purpose of `WaitGroup` is to allow the main coroutine to block and wait until all related sub-coroutines have completed their tasks before continuing to run. The blocking and waiting mentioned here only applies to the main coroutine (i.e., the current coroutine) and will not block the current process.
We use a piece of code to demonstrate this feature:

```php
<?php
$wg = new \Hyperf\Coroutine\WaitGroup();
// Increment the counter by two
$wg->add(2);
// Create Coroutine A
co(function () use ($wg) {
    // some code
    // Decrement the counter by one
    $wg->done();
});
// Create Coroutine B
co(function () use ($wg) {
    // some code
    // Decrement the counter by one
    $wg->done();
});
// Wait for Coroutine A and Coroutine B to finish running
$wg->wait();
```

> Note that `WaitGroup` itself needs to be used within a coroutine as well.

### Parallel Feature

The `Parallel` feature is a more convenient usage method abstracted by Hyperf based on the `WaitGroup` feature. Let's demonstrate it with a piece of code.

```php
<?php
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

$parallel = new Parallel();
$parallel->add(function () {
    sleep(1);
    return Coroutine::id();
});
$parallel->add(function () {
    sleep(1);
    return Coroutine::id();
});

try{
    // $results will be [1, 2]
    $results = $parallel->wait();
} catch(ParallelExecutionException $e){
    // $e->getResults() to get return values in the coroutine.
    // $e->getThrowables() to get exceptions that occurred in the coroutine.
}
```

> Note that the `Hyperf\Coroutine\Exception\ParallelExecutionException` exception will only be thrown in version 1.1.6 and newer versions.

Through the above code, we can see that it only took `1` second to get the `IDs` of two different coroutines. When calling `add(callable $callable)`, the `Parallel` class will automatically create a coroutine for it and add it to the scheduling of `WaitGroup`.
Not only that, we can also further simplify the above code through the `parallel(array $callables)` function to achieve the same purpose. The following is the simplified code.

```php
<?php
use Hyperf\Coroutine\Coroutine;

// You can also add keys to the passed array parameters to facilitate distinguishing sub-coroutines, and the returned results will also return the corresponding results according to the keys.
$result = parallel([
    function () {
        sleep(1);
        return Coroutine::id();
    },
    function () {
        sleep(1);
        return Coroutine::id();
    }
]);
```

> Note that `Parallel` itself needs to be used within a coroutine as well.

#### Limiting the Maximum Number of Simultaneously Running Coroutines in Parallel

When there are many tasks added to `Parallel`, assuming they are all request tasks, sending all requests at once is very likely to cause the peer service to be unable to handle them because it receives a large number of requests at once, which carries the risk of downtime. Therefore, it is necessary to properly protect the peer side, but we also hope to speed up these requests through the `Parallel` mechanism. In this case, you can set the maximum number of running coroutines by passing the first parameter when instantiating the `Parallel` object. For example, if we want to set the maximum number of coroutines to `5`, it means that at most `5` coroutines will be running in `Parallel` at the same time. Only when a coroutine in the `5` finishes, will the subsequent coroutines continue to start until all coroutines have completed their tasks. The example code is as follows:

```php
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

$parallel = new Parallel(5);
for ($i = 0; $i < 20; $i++) {
    $parallel->add(function () {
        sleep(1);
        return Coroutine::id();
    });
}

try{
   $results = $parallel->wait();
} catch(ParallelExecutionException $e){
    // $e->getResults() to get return values in the coroutine.
    // $e->getThrowables() to get exceptions that occurred in the coroutine.
}
```

### Concurrent Coroutine Execution Control

`Hyperf\Coroutine\Concurrent` is implemented based on `Swoole\Coroutine\Channel` and is used to control the maximum number of coroutines running simultaneously within a code block.

In the following example, when `10` sub-coroutines are executed at the same time, it will block in the loop, but it will only block the current coroutine until a position is released, and then the loop will continue to execute the next sub-coroutine.

```php
<?php

use Hyperf\Coroutine\Concurrent;

$concurrent = new Concurrent(10);

for ($i = 0; $i < 15; ++$i) {
    $concurrent->create(function () {
        // Do something...
    });
}
```

### Coroutine Context

Since memory is shared between coroutines in the same process, but the execution/switching of coroutines is non-sequential, which means it is difficult for us to control which coroutine is currently running **(in fact, it is possible, but usually nobody does this)**, so we need to be able to switch the corresponding context at the same time when a coroutine switch occurs.
In `Hyperf`, implementing coroutine context management is very simple. Based on the `set(string $id, $value)`, `get(string $id, $default = null)`, `has(string $id)`, and `override(string $id, \Closure $closure)` static methods of the `Hyperf\Context\Context` class, you can complete the management of context data. The values set and obtained through these methods are limited to the current coroutine. When the coroutine ends, the corresponding context will also be automatically released. There is no need to manually manage it, and there is no need to worry about the risk of memory leaks.

#### Hyperf\Context\Context::set()

Store a value in the context of the current coroutine by calling the `set(string $id, $value)` method, as follows:

```php
<?php
use Hyperf\Context\Context;

// Store the bar string into the current coroutine context with foo as the key
$foo = Context::set('foo', 'bar');
// The set method will return the value as the return value of the method, so the value of $foo is bar
```

#### Hyperf\Context\Context::get()

Take out a value stored with `$id` as `key` from the context of the current coroutine by calling the `get(string $id, $default = null)` method. If it does not exist, return `$default`, as follows:

```php
<?php
use Hyperf\Context\Context;

// Take out the value with key foo from the current coroutine context. If it does not exist, return the bar string
$foo = Context::get('foo', 'bar');
```

#### Hyperf\Context\Context::has()

Determine whether a value stored with `$id` as `key` exists in the context of the current coroutine by calling the `has(string $id)` method. If it exists, return `true`, otherwise return `false`, as follows:

```php
<?php
use Hyperf\Context\Context;

// Determine whether the value with key foo exists in the current coroutine context
$foo = Context::has('foo');
```

#### Hyperf\Context\Context::override()

When we need to do some complex context processing, such as first judging whether a `key` exists, if it exists, take out the `value` and then make some modifications to the `value`, and then set the `value` back to the context container, there will be relatively complicated judgment conditions at this time. You can directly call the `override` method to implement this logic, as follows:

```php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Context\Context;

// Take out the $request object from the coroutine context and set the Header with key as foo, and then save it back to the coroutine context
$request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) {
    return $request->withAddedHeader('foo', 'bar');
});
```

### Swoole Runtime Hook Level

The framework provides the `SWOOLE_HOOK_FLAGS` constant in the entry function. If you need to modify the `Runtime Hook` level of the entire project, for example, if you want to support `CURL coroutines` and the `Swoole` version is earlier than `v4.5.4`, you can modify the code here, as follows.

```php
<?php
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL);
```

!> If the Swoole version is >= `v4.5.4`, no modifications are needed.
