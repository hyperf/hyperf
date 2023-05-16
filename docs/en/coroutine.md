# Coroutine

## Concept

Hyperf is built on the coroutine of `Swoole 4`, which is one of the big factors that Hyperf can provide for high performance.

### Running Mode of PHP-FPM

Before we talk about what is going on, let's talk about the operation mode of the traditional `PHP-FPM` architecture. `PHP-FPM` is a multi-process `FastCGI` hypervisor, which is used by most PHP applications. Suppose we use `Nginx` to provide the `HTTP` service (it's same when use `Apache`). All client-initiated requests arrive at `Nginx` first, then `Nginx` forwards the request to `PHP-FPM` processing via the `FastCGI` protocol, the `Master Process` of `PHP-FPM` will allocate a `Worker Process` for each request. This processing means the whole process is blocked waiting between waiting for the parsing of the `PHP` script and waiting for the result of the business, and then recycle the child process, which means that how many the number of processes of `PHP-FPM` that you have, how many requests then you could handle. Assuming that `PHP-FPM` has `200` `Worker Process `, a request will take `1` seconds, so simply the entire server can theoretically handle up to 200's, `QPS` is `200/s`, in high-concurrency scenario, such performance is often not enough, although you can use `Nginx` as load balancing with multiple `PHP-FPM` servers to provide services, but due to `PHP-FPM` blocking waiting model, a request will occupy at least one `MySQL` connection, then the multi-node will generate a lot of connections of `MySQL` obviously, and the maximum number of connections of ` MySQL` default value is `100`, although you could modified it, but this apparent that the pattern can not cope with high concurrency scenes properly.

### Asynchronous Non-blocking System

In a high-concurrency scenario, asynchronous non-blocking has obvious advantages. The intuitive advantage is that the `Worker Process` is no longer synchronously blocking to handle a request, but can handle multiple requests at the same time, without `I/O` Waiting, the ability to concurrency is extremely strong, and a large number of requests can be initiated or maintained at the same time. So the most intuitive shortcomings you may know, is the callback hell, the business logic must be implemented in the corresponding callback function, if the business logic has multiple `I/O` requests, there will be many layers of callback function, the following example of a pseudo-code fragment under `Swoole 1.x`.

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
    // Query a row of data from users table
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r === true) {
            $rows = $db->affected_rows;
            // Modify a row of data after the query is successful
            $updateSql = 'update users set name='new name' where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                if ($r === true) {
                    return $this->response->end('Update Successfully');
                }
            });
        }
        $db->close();
    });
});
```
As you can see from the code snippets above, almost every operation requires a callback function, and the layering and code structure of a callback in a complex business scenario will absolutely break you down It's not hard to see that this approach is similar to writing asynchronous methods on `JavaScript` , and `JavaScript` offers a number of solutions (derived from other programming languages, of course) , such as `Promise` , `yield + generator` , `Async/Await` , while `Promise` is a way to wrap callbacks, `yield + generator` and `Async/Await` need to explicitly add some code syntax tags to the code, which are good alternatives to callbacks But you still need time to understand its implementation and syntax.     
The Swoole coroutine is also a solution for asynchronous callbacks. In PHP, both the Swoole coroutine and the `yield + generator` are coroutine solutions that enable code to write asynchronous code in a nearly synchronous way The obvious difference is that in `yield + generator`s coroutine mechanism, every `I/O` operation needs to be preceded by `yield` syntax to implement the coroutine switch, and every level of call needs to be preceded by `yield` Syntax, otherwise there will be unexpected errors The `Swoole` coroutine solution, by contrast, is a much more elegant one, with `I/O` being switched implicitly at the bottom without any additional syntax or `yield` being added to the code, and the COROUTINE switching is silent Greatly reduces the mental burden of maintaining an asynchronous system.

### What is Coroutine ?

We already know that coroutines can solve the development problem of asynchronous non-blocking system very well, so what is coroutines themselves? By definition, *Coroutines are lightweight threads that are scheduled and managed by user code, rather than by the operating system kernel, that is, in user mode. This can be understood directly as a non standard thread implementation, but it is up to the user to make the switch, not the operating system to allocate `CPU` time. Specifically, each `Worker process` of `Swoole` has a coordinator Scheduler to schedule The coroutines, and the timing of a coroutine switch is when a `I/O` operation or explicit code switch occurs and the process runs the coroutine as a single thread, This means that there is only one coroutine running at the same time in a process and the switching time is clear, so there is no need to deal with the problem of synchronizing locks like multi-threaded programming.    
The code within a single coroutine is still running serially, in an HTTP coroutine server to understand that each request is a coroutine. For example, suppose that `coroutine A` is created for `request A` and `coroutine B` is created for `request B`, then the code runs to the query `MySQL` while processing `coroutine A` , at which point `coroutine A` will trigger the coroutine switch, `coroutine A` will continue to wait for the `I/O` device to return the result, then it will switch to `coroutine B` , start processing the logic of `coroutine B`, then when you encounter another `I/O` operation, trigger the coroutine switch, and then go back and continue from where coroutine a just cut away, and so on When an `I/O` operation is encountered, it switches to another coroutine to continue instead of blocking and waiting.   
The problem here is that the `MySQL` query operation for * `coroutine A` must be an asynchronous non-blocking operation, otherwise the coroutine scheduler can not switch to another coroutine to continue execution * Due to blocking This is one of the issues that needs to be avoided in coprogramming.

### What is the difference between coroutine and ordinary thread ?

As we said that coroutine is a lightweight thread. Coroutines and threads are suitable for multitasking scenarios. From this perspective, coroutines are very similar to threads and have their own contexts, which can share global variables, but the difference is that multiple threads can be running at the same time, but there can only be one for the `Swoole` coroutine, and other coroutines will be paused. In addition, the normal thread is preemptive, which thread can get the resources determined by the operating system, but the coroutine is collaborative, and the execution right is allocated by the user state.

## Coroutine programming considerations

### Cannot exist blocking code

Blocking code in the coroutine will cause the coroutine scheduler cannot switch to another coroutine to continue executing code, so we must prevent the blocking code exist in the coroutine. Assuming we have started `4 Worker` to handle `HTTP` Request (usually the number of `Worker` started is the same as the number of `CPU` cores or `2` times of `CPU` cores). If there is blocking code in the coroutine, theoretically, if each request will block `1` seconds, then the application `QPS ` will also degenerate to `4/s`, which is undoubtedly degenerate into a similar situation to `PHP-FPM`, so we must not allows blocking code exist in the coroutine.

So which ones are blocking code? We can simply think that most of the asynchronous functions provided by non-Swoole are `MySQL`, `Redis`, `Memcache`, `MongoDB`, `HTTP`, `Socket`, file operations. , `sleep/usleep`, etc. are blocking code, which covers almost all daily operations, so how to solve it? `Swoole` provides the MySQL client, `PostgreSQL`, `Redis`, `HTTP`, `Socket` for the coroutine client，in addition to, after `Swoole 4.1`, Swoole provide `\Swoole\Runtime::enableCoroutine()` function to make most of blocking code to coroutined，just execute `\Swoole\Runtime::enableCoroutine()` before create coroutine，`Swoole` will turn all sockets that use php_stream for coroutine scheduling, which can be understood as the most common operations become coroutined, except `curl`. More detailed information can be found in this section of the [Swoole Documentation](https://wiki.swoole.com/#/runtime).

In `Hyperf`, we have handled this for you, you only need to pay attention to the blocking code that  `\Swoole\Runtime::enableCoroutine()` still cannot be coroutined automatically.

### Cannot store status via global variables

Under the persistence application of `Swoole`, a global variable in `Worker` is shared within `Worker`, and from the introduction of coroutine we can know that there will be multiple coroutines exist in the same `Worker`. Coroutine switching means that a `Worker` will process multiple coroutines (or directly understood as requests) in a single time period, which means that if you use global variables to store state, the state data may be used by multiple coroutines, which means that data may be confused between different requests or different coroutines. The global variables here refer to `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` etc. `$_` Variables at the beginning, `global` variables, and `static` properties or variables.   
So what should we do when we need to use these features?

For global variables, it is generated by a `Request`, and Hyperf's Request/Response are made by [hyperf/http-message](https://github) .com/hyperf/http-message) by implementing [PSR-7](https://www.php-fig.org/psr/psr-7/), all global variables can be found in the Request object.

For the `global` variable and the `static` variable, in `PHP-FPM` mode, the essence is to survive in a request life cycle, and in `Hyperf` because of the `CLI` application, there will be a `global cycle ` and `request cycle (coroutine cycle)` two long life cycles.
- Global cycle, we only need to create a static variable for global call. Static variables mean that any coroutine and code logic share the data in this static variable after the service is started, which means that the stored data cannot be special service to a request or a certain coroutine;
- Coroutine cycle, since `Hyperf` will automatically create a coroutine for each request to process, then a coroutine cycle can also be understood here as a request cycle. In the coroutine, all state data should be stored in In the `Hyperf\Context\Context` class, the data of any structure is read and stored by `get`, `set` of the class. Get or Set any data in the `Context (coroutine context)` is limited to the corresponding coroutine that the get or set function executed, and the relevant context data is also automatically destroyed at the end of the coroutine.

### Maximum number of coroutine

Set the `max_coroutine` parameter of `Swoole Server` via the `set` method to configure the maximum number of coroutines that can exist in a `Worker` process. Because the number of coroutines processed by the `Worker` process increases, the corresponding memory usage will also increase. To avoid exceeding the `memory_limit` limit of `PHP`, set the value according to the actual business pressure measurement result. The default value for `Swoole` is `3000`, which is set to `100000` by default in the `hyperf-skeleton` project.

## Usage of coroutine

### Create a coroutine

Use `co(callable $callable)` or `go(callable $callable)` functions or `Hyperf\Coroutine\Coroutine::create(callable $callable)` method to create a coroutine simply, coroutine related methods and clients can be used within the coroutine.

### Is it running in coroutine environment ?

In some cases, we want to determine whether is current running in the coroutine environment, for some compatible coroutine environment and non-coroutine environment code will be used as a basis for judgment, we can use `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` method to get the result.

### Get the coroutine ID

In some cases, we need to do some logic according to the `coroutine ID`, such as `coroutine context`, you can get the current coroutine ID by `Hyperf\Coroutine\Coroutine::id(): int`, if not in the coroutine environment, the method will return `-1`.

### Channel

Similar to the go language `chan`, `Channel` provides support for multi-producer coroutines and multi-consumer coroutine modes. The bottom layer automatically implements the switching and scheduling of the coroutine. `Channel` is similar to PHP's array, it only takes up memory, there are no other additional resources to apply, all operations are memory operations, no `I/O`, the usage is similar to the `SplQueue` queue.
`Channel` is mainly used for inter-coroutines communication. When we want to return some data from one coroutine to another coroutine, we can pass it through `Channel`. 

Main methods:   
- `Channel->push` : When there are other coroutines in the queue waiting for `pop` data, a consumer coroutine is automatically invoked in sequence. Automatically `yield` gives up the control right when the queue is full, waiting for other coroutines to consume data
- `Channel->pop` : Automatic `yield` when the queue is empty, waiting for other coroutine produce data. After the data is consumed, the queue can push new data into it and automatically wake up a producer coroutine in sequence.
                   
The following is a simple example of a communication between coroutines:

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

### Defer

When we want to run some code at the end of the coroutine, we can use a `defer(callable $callable)` function or `Hyperf\Coroutine::defer(callable $callable)` to put a function in the form of a `stack'. Once stored, the functions in the `stack' will be executed one by one at the end of the current coroutine, executed by LIFO (Last in, First out).

### WaitGroup

`WaitGroup` is a feature derived from `Channel`. If you know about the `Go` language, we will know the `WaitGroup` feature. In `Hyperf`, the purpose of `WaitGroup` is to block the main coroutine, wait until all relevant child coroutines have completed the task and then continue to run. The blocking wait mentioned here is only for the main coroutine (ie the current coroutine) and does not block the current process.   
We demonstrate this feature with a piece of code:

```php
<?php
$wg = new \Hyperf\Utils\WaitGroup();
// Counter increase 2
$wg->add(2);
// Create coroutine A
co(function () use ($wg) {
    // some code
    // Counter decrease 1
    $wg->done();
});
// Create coroutine B
co(function () use ($wg) {
    // some code
    // Counter decrease 1
    $wg->done();
});
// Wait for coroutine A and coroutine B finished
$wg->wait();
```

> Note that `WaitGroup` itself also needs to be used in the coroutine.

### Parallel

The `Parallel` feature is an abstraction based on the `WaitGroup` feature provided by Hyperf, more convenient way to use than `WaitGroup`. Let's demonstrate it with a piece of code:

```php
<?php
$parallel = new \Hyperf\Coroutine\Parallel();
$parallel->add(function () {
    \Hyperf\Coroutine\Coroutine::sleep(1);
    return \Hyperf\Coroutine\Coroutine::id();
});
$parallel->add(function () {
    \Hyperf\Coroutine\Coroutine::sleep(1);
    return \Hyperf\Coroutine\Coroutine::id();
});
// $result is [1, 2]
$result = $parallel->wait();
```

From the above code we can see that it took only 1 second to get the ID of two different coroutines. When calling `add(callable $callable)`, the `Parallel` class will automatically create a coroutine for it, and join it to the dispatcher of `WaitGroup`.
Not only that, but we can further simplify the above code by using the `parallel(array $callables)` function to achieve the same purpose. The following is the simplified code.

```php
<?php
use Hyperf\Coroutine\Coroutine;

// The passed array parameters can also use `key of array` to facilitate distinguish the result of coroutine, and the returned result will also return the corresponding result according to key.
$result = parallel([
    function () {
        Coroutine::sleep(1);
        return Coroutine::id();
    },
    function () {
        Coroutine::sleep(1);
        return Coroutine::id();
    }
]);
```

> Note that `Parallel` itself also needs to be used in the coroutine.

### Coroutine Context

Since the coroutines in the same process are shared by memory, the execution/switching of the coroutines is non-sequential, which means that it is difficult to control which one of the current coroutines is * (in fact, it could, but no one would like to do like this) *, so we need to be able to switch the corresponding context at the same time when a coroutine switch occurs.
Implementing context management for coroutines in Hyperf is very simple, based on `set(string $id, $value)`, `get(string $id, $default = null)`, `has(string $id)` static methods of the `Hyperf\Context\Context` can complete the management of context data. The values set and obtained by these methods are limited to the current coroutine. At the end of the coroutine, the corresponding context will be automatically released. No need to manage manually, no need to worry about the risk of memory leaks.
