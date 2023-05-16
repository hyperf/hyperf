# 协程

## 概念

Hyperf 是运行于 `Swoole 4` 的协程和 `Swow` 协程之上的，这也是 Hyperf 能提供高性能的其中一个很大的因素。

### PHP-FPM 的运作模式

在聊协程是什么之前，我们先聊聊传统 `PHP-FPM` 架构的运作模式，`PHP-FPM` 是一个多进程的 `FastCGI` 管理程序，是绝大多数 `PHP` 应用所使用的运行模式。假设我们使用 `Nginx` 提供 `HTTP` 服务（`Apache` 同理），所有客户端发起的请求最先抵达的都是 `Nginx`，然后 `Nginx` 通过 `FastCGI` 协议将请求转发给 `PHP-FPM` 处理，`PHP-FPM` 的 `Worker 进程` 会抢占式的获得 CGI 请求进行处理，这个处理指的就是，等待 `PHP` 脚本的解析，等待业务处理的结果返回，完成后回收子进程，这整个的过程是阻塞等待的，也就意味着 `PHP-FPM` 的进程数有多少能处理的请求也就是多少，假设 `PHP-FPM` 有 `200` 个 `Worker 进程`，一个请求将耗费 `1` 秒的时间，那么简单的来说整个服务器理论上最多可以处理的请求也就是 `200` 个，`QPS` 即为 `200/s`，在高并发的场景下，这样的性能往往是不够的，尽管可以利用 `Nginx` 作为负载均衡配合多台 `PHP-FPM` 服务器来提供服务，但由于 `PHP-FPM` 的阻塞等待的工作模型，一个请求会占用至少一个 `MySQL` 连接，多节点高并发下会产生大量的 `MySQL` 连接，而 `MySQL` 的最大连接数默认值为 `100`，尽管可以修改，但显而易见该模式没法很好的应对高并发的场景。

### 异步非阻塞系统

在高并发的场景下，异步非阻塞就显得优势明显了，直观的优点表现就是 `Worker 进程` 不再同步阻塞的去处理一个请求，而是可以同时处理多个请求，无需 `I/O` 等待，并发能力极强，可以同时发起或维护大量的请求。那么最直观的缺点大家可能也都知道，就是永无止境的回调，业务逻辑必须在对应的回调函数内实现，如果业务逻辑存在多次的 `I/O` 请求，则会存在很多层的回调函数，下面示例一段 `Swoole 1.x` 下的异步回调型的伪代码片段。

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
    // 从 users 表中查询一条数据
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r !== false) {
            // 查询成功后修改一条数据
            $updateSql = 'update users set name="new name" where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                $rows = $db->affected_rows;
                if ($r === true) {
                    return $this->response->end('更新成功');
                }
            });
        }
        $db->close();
    });
});
```

> 注意 `MySQL` 等异步模块已在[4.3.0](https://wiki.swoole.com/#/version/bc?id=430)中移除，并转移到了[swoole_async](https://github.com/swoole/ext-async)。

从上面的代码片段可以看出，每一个操作几乎就需要一个回调函数，在复杂的业务场景中回调的层次感和代码结构绝对会让你崩溃，其实不难看出这样的写法有点类似 `JavaScript` 上的异步方法的写法，而 `JavaScript` 也为此提供了不少的解决方案（当然方案是源于其它编程语言），如 `Promise`，`yield + generator`, `async/await`，`Promise` 则是对回调的一种封装方式，而 `yield + generator` 和 `async/await` 则需要在代码上显性的增加一些代码语法标记，这些相对比回调函数来说，不妨都是一些非常不错的解决方案，但是你需要另花时间来理解它的实现机制和语法。   
`Swoole` 协程也是对异步回调的一种解决方案，在 `PHP` 语言下，`Swoole` 协程与 `yield + generator` 都属于协程的解决方案，协程的解决方案可以使代码以近乎于同步代码的书写方式来书写异步代码，显性的区别则是 `yield + generator` 的协程机制下，每一处 `I/O` 操作的调用代码都需要在前面加上 `yield` 语法实现协程切换，每一层调用都需要加上，否则会出现意料之外的错误，而 `Swoole` 协程的解决方案对比于此就高明多了，在遇到 `I/O` 时底层自动的进行隐式协程切换，无需添加任何的额外语法，无需在代码前加上 `yield`，协程切换的过程无声无息，极大的减轻了维护异步系统的心智负担。

### 协程是什么？

我们已经知道了协程可以很好的解决异步非阻塞系统的开发问题，那么协程本身到底是什么呢？从定义上来说，**协程是一种轻量级的线程，由用户代码来调度和管理，而不是由操作系统内核来进行调度，也就是在用户态进行**。可以直接的理解为就是一个非标准的线程实现，但什么时候切换由用户自己来实现，而不是由操作系统分配 `CPU` 时间决定。具体来说，`Swoole` 的每个 `Worker 进程` 会存在一个协程调度器来调度协程，协程切换的时机就是遇到 `I/O` 操作或代码显性切换时，进程内以单线程的形式运行协程，也就意味着一个进程内同一时间只会有一个协程在运行且切换时机明确，也就无需处理像多线程编程下的各种同步锁的问题。   
单个协程内的代码运行仍是串行的，放在一个 HTTP 协程服务上来理解就是每一个请求是一个协程，举个例子，假设为 `请求 A` 创建了 `协程 A`，为 `请求 B` 创建了 `协程 B`，那么在处理 `协程 A` 的时候代码跑到了查询 `MySQL` 的语句上，这个时候 `协程 A` 则会触发协程切换，`协程 A` 就继续等待 `I/O` 设备返回结果，那么此时就会切换到 `协程 B`，开始处理 `协程 B` 的逻辑，当又遇到了一个 `I/O` 操作便又触发协程切换，再回过来从 `协程 A` 刚才切走的地方继续执行，如此反复，遇到 `I/O` 操作就切换到另一个协程去继续执行而非一直阻塞等待。   
这里可以发现一个问题就是：**在 `协程 A` 中的 `MySQL` 查询操作必须得是一个异步非阻塞的操作，否则会由于阻塞导致协程调度器没法切换到另一个协程继续执行**，这个也是要在协程编程下需要规避的问题之一。

### 协程与普通线程有哪些区别？

都说协程是一个轻量级的线程，协程和线程都适用于多任务的场景下，从这个角度上来说，协程与线程很相似，都有自己的上下文，可以共享全局变量，但不同之处在于，在同一时间可以有多个线程处于运行状态，但对于 `Swoole` 协程来说只能有一个，其它的协程都会处于暂停的状态。此外，普通线程是抢占式的，哪个线程能得到资源由操作系统决定，而协程是协作式的，执行权由用户态自行分配。

## 协程编程注意事项

### 不能存在阻塞代码

协程内代码的阻塞会导致协程调度器无法切换到另一个协程继续执行代码，所以我们绝不能在协程内存在阻塞代码，假设我们启动了 `4` 个 `Worker` 来处理 `HTTP` 请求（通常启动的 `Worker` 数量与 `CPU` 核心数一致或 `2` 倍），如果代码中存在阻塞，暂且理论的认为每个请求都会阻塞 `1` 秒，那么系统的 `QPS` 也将退化为 `4/s` ，这无疑就是退化成了与 `PHP-FPM` 类似的情况，所以我们绝对不能在协程中存在阻塞代码。   

那么到底哪些是阻塞代码呢？我们可以简单的认为绝大多数你所熟知的非 `Swoole` 提供的异步函数的 `MySQL`、`Redis`、`Memcache`、`MongoDB`、`HTTP`、`Socket`等客户端，文件操作、`sleep/usleep` 等均为阻塞函数，这几乎涵盖了所有日常操作，那么要如何解决呢？`Swoole` 提供了 `MySQL`、`PostgreSQL`、`Redis`、`HTTP`、`Socket` 的协程客户端可以使用，同时 `Swoole 4.1` 之后提供了一键协程化的方法 `\Swoole\Runtime::enableCoroutine()`，只需在使用协程前运行这一行代码，`Swoole` 会将 所有使用 `php_stream` 进行 `socket` 操作均变成协程调度的异步 `I/O`，可以理解为除了 `curl` 绝大部分原生的操作都可以适用，关于此部分可查阅 [Swoole 文档](https://wiki.swoole.com/#/runtime) 获得更具体的信息。  

在 `Hyperf` 中我们已经为您处理好了这一切，您只需关注 `\Swoole\Runtime::enableCoroutine()` 仍无法协程化的阻塞代码即可。

### 不能通过全局变量储存状态

在 `Swoole` 的持久化应用下，一个 `Worker` 内的全局变量是 `Worker` 内共享的，而从协程的介绍我们可以知道同一个 `Worker` 内还会存在多个协程并存在协程切换，也就意味着一个 `Worker` 会在一个时间周期内同时处理多个协程（或直接理解为请求）的代码，也就意味着如果使用了全局变量来储存状态可能会被多个协程所使用，也就是说不同的请求之间可能会混淆数据，这里的全局变量指的是 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`等`$_`开头的变量、`global` 变量，以及 `static` 静态属性。    
那么当我们需要使用到这些特性时应该怎么办？   

对于全局变量，均是跟随着一个 `请求(Request)` 而产生的，而 `Hyperf` 的 `请求(Request)/响应(Response)` 是由 [hyperf/http-message](https://github.com/hyperf/http-message) 通过实现 [PSR-7](https://www.php-fig.org/psr/psr-7/) 处理的，故所有的全局变量均可以在 `请求(Request)` 对象中得到相关的值；   

对于 `global` 变量和 `static` 变量，在 `PHP-FPM` 模式下，本质都是存活于一个请求生命周期内的，而在 `Hyperf` 内因为是 `CLI` 应用，会存在 `全局周期` 和 `请求周期(协程周期)` 两种长生命周期。   
- 全局周期，我们只需要创建一个静态变量供全局调用即可，静态变量意味着在服务启动后，任意协程和代码逻辑均共享此静态变量内的数据，也就意味着存放的数据不能是特别服务于某一个请求或某一个协程；
- 协程周期，由于 `Hyperf` 会为每个请求自动创建一个协程来处理，那么一个协程周期在此也可以理解为一个请求周期，在协程内，所有的状态数据均应存放于 `Hyperf\Context\Context` 类中，通过该类的 `get`、`set` 来读取和存储任意结构的数据，这个 `Context(协程上下文)` 类在执行任意协程时读取或存储的数据都是仅限对应的协程的，同时在协程结束时也会自动销毁相关的上下文数据。

### 最大协程数限制

对 `Swoole Server` 通过 `set` 方法设置 `max_coroutine` 参数，用于配置一个 `Worker` 进程最多可存在的协程数量。因为随着 `Worker` 进程处理的协程数目的增加，其对应占用的内存也会随之增加，为了避免超出 `PHP` 的 `memory_limit` 限制，请根据实际业务的压测结果设置该值，`Swoole` 的默认值为 `100000`（ `Swoole` 版本小于 `v4.4.0-beta` 时默认值为 `3000` ）, 在 `hyperf-skeleton` 项目中默认设置为 `100000`。

## 使用协程

### 创建一个协程

只需通过 `co(callable $callable)` 或 `go(callable $callable)` 函数或 `Hyperf\Coroutine\Coroutine::create(callable $callable)` 即可创建一个协程，协程内可以使用协程相关的方法和客户端。

### 判断当前是否处于协程环境内

在一些情况下我们希望判断一些当前是否运行于协程环境内，对于一些兼容协程环境与非协程环境的代码来说会作为一个判断的依据，我们可以通过 `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` 方法来得到结果。

### 获得当前协程的 ID

在一些情况下，我们需要根据 `协程 ID` 去做一些逻辑，比如 `协程上下文` 之类的逻辑，可以通过 `Hyperf\Coroutine\Coroutine::id(): int` 获得当前的 `协程 ID`，如不处于协程环境下，会返回 `-1`。

### Channel 通道

类似于 `Go` 语言的 `chan`，`Channel` 可为多生产者协程和多消费者协程模式提供支持。底层自动实现了协程的切换和调度。 `Channel` 与 `PHP` 的数组类似，仅占用内存，没有其他额外的资源申请，所有操作均为内存操作，无 `I/O` 消耗，使用方法与 `SplQueue` 队列类似。   
`Channel` 主要用于协程间通讯，当我们希望从一个协程里返回一些数据到另一个协程时，就可通过 `Channel` 来进行传递。   

主要方法：   
- `Channel->push` ：当队列中有其他协程正在等待 `pop` 数据时，自动按顺序唤醒一个消费者协程。当队列已满时自动 `yield` 让出控制权，等待其他协程消费数据
- `Channel->pop` ：当队列为空时自动 `yield`，等待其他协程生产数据。消费数据后，队列可写入新的数据，自动按顺序唤醒一个生产者协程。

下面是一个协程间通讯的简单例子:

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

### Defer 特性

当我们希望在协程结束时运行一些代码时，可以通过 `defer(callable $callable)` 函数或 `Hyperf\Coroutine::defer(callable $callable)` 将一段函数以 `栈(stack)` 的形式储存起来，`栈(stack)` 内的函数会在当前协程结束时以 `先进后出` 的流程逐个执行。

### WaitGroup 特性

`WaitGroup` 是基于 `Channel` 衍生出来的一个特性，如果接触过 `Go` 语言，我们都会知道 `WaitGroup` 这一特性，在 `Hyperf` 里，`WaitGroup` 的用途是使得主协程一直阻塞等待直到所有相关的子协程都已经完成了任务后再继续运行，这里说到的阻塞等待是仅对于主协程（即当前协程）来说的，并不会阻塞当前进程。      
我们通过一段代码来演示该特性：   

```php
<?php
$wg = new \Hyperf\Utils\WaitGroup();
// 计数器加二
$wg->add(2);
// 创建协程 A
co(function () use ($wg) {
    // some code
    // 计数器减一
    $wg->done();
});
// 创建协程 B
co(function () use ($wg) {
    // some code
    // 计数器减一
    $wg->done();
});
// 等待协程 A 和协程 B 运行完成
$wg->wait();
```

> 注意 `WaitGroup` 本身也需要在协程内才能使用

### Parallel 特性

`Parallel` 特性是 Hyperf 基于 `WaitGroup` 特性抽象出来的一个更便捷的使用方法，我们通过一段代码来演示一下。

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
    // $results 结果为 [1, 2]
   $results = $parallel->wait(); 
} catch(ParallelExecutionException $e){
    // $e->getResults() 获取协程中的返回值。
    // $e->getThrowables() 获取协程中出现的异常。
}
```
> 注意 `Hyperf\Utils\Exception\ParallelExecutionException` 异常仅在 1.1.6 版本和更新的版本下会抛出

通过上面的代码我们可以看到仅花了 `1` 秒就得到了两个不同的协程的 `ID`，在调用 `add(callable $callable)` 的时候 `Parallel` 类会为之自动创建一个协程，并加入到 `WaitGroup` 的调度去。    
不仅如此，我们还可以通过 `parallel(array $callables)` 函数进行更进一步的简化上面的代码，达到同样的目的，下面为简化后的代码。

```php
<?php
use Hyperf\Coroutine\Coroutine;

// 传递的数组参数您也可以带上 key 便于区分子协程，返回的结果也会根据 key 返回对应的结果
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

> 注意 `Parallel` 本身也需要在协程内才能使用

#### 限制 Parallel 最大同时运行的协程数

当我们添加到 `Parallel` 里的任务有很多时，假设都是一些请求任务，那么一瞬间发出全部请求很有可能会导致对端服务因为一瞬间接收到了大量的请求而处理不过来，有宕机的风险，所以需要对对端进行适当的保护，但我们又希望可以通过 `Parallel` 机制来加速这些请求的耗时，那么可以通过在实例化 `Parallel` 对象时传递第一个参数，来设置最大运行的协程数，比如我们希望最大设置的协程数为 `5` ，也就意味着 `Parallel` 里最多只会有 `5` 个协程在运行，只有当 `5` 个里有协程完成结束后，后续的协程才会继续启动，直至所有协程完成任务，示例代码如下：

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
    // $e->getResults() 获取协程中的返回值。
    // $e->getThrowables() 获取协程中出现的异常。
}
```

### Concurrent 协程运行控制

`Hyperf\Coroutine\Concurrent` 基于 `Swoole\Coroutine\Channel` 实现，用来控制一个代码块内同时运行的最大协程数量的特性。

以下样例，当同时执行 `10` 个子协程时，会在循环中阻塞，但只会阻塞当前协程，直到释放出一个位置后，循环继续执行下一个子协程。

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

### 协程上下文

由于同一个进程内协程间是内存共享的，但协程的执行/切换是非顺序的，也就意味着我们很难掌控当前的协程是哪一个**（事实上可以，但通常没人这么干）**，所以我们需要在发生协程切换时能够同时切换对应的上下文。
在 `Hyperf` 里实现协程的上下文管理将非常简单，基于 `Hyperf\Context\Context` 类的 `set(string $id, $value)`、`get(string $id, $default = null)`、`has(string $id)`、`override(string $id, \Closure $closure)` 静态方法即可完成上下文数据的管理，通过这些方法设置和获取的值，都仅限于当前的协程，在协程结束时，对应的上下文也会自动跟随释放掉，无需手动管理，无需担忧内存泄漏的风险。

#### Hyperf\Context\Context::set()

通过调用 `set(string $id, $value)` 方法储存一个值到当前协程的上下文中，如下：

```php
<?php
use Hyperf\Context\Context;

// 将 bar 字符串以 foo 为 key 储存到当前协程上下文中
$foo = Context::set('foo', 'bar');
// set 方法会再将 value 作为方法的返回值返回回来，所以 $foo 的值为 bar
```

#### Hyperf\Context\Context::get()

通过调用 `get(string $id, $default = null)` 方法可从当前协程的上下文中取出一个以 `$id` 为 `key` 储存的值，如不存在则返回 `$default` ，如下：

```php
<?php
use Hyperf\Context\Context;

// 从当前协程上下文中取出 key 为 foo 的值，如不存在则返回 bar 字符串
$foo = Context::get('foo', 'bar');
```

#### Hyperf\Context\Context::has()

通过调用 `has(string $id)` 方法可判断当前协程的上下文中是否存在以 `$id` 为 `key` 储存的值，如存在则返回 `true`，不存在则返回 `false`，如下：

```php
<?php
use Hyperf\Context\Context;

// 从当前协程上下文中判断 key 为 foo 的值是否存在
$foo = Context::has('foo');
```

#### Hyperf\Context\Context::override()

当我们需要做一些复杂的上下文处理，比如先判断一个 `key` 是否存在，如果存在则取出 `value` 来再对 `value` 进行某些修改，然后再将 `value` 设置回上下文容器中，此时会有比较繁杂的判断条件，可直接通过调用 `override` 方法来实现这个逻辑，如下：

```php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Context\Context;

// 从协程上下文取出 $request 对象并设置 key 为 foo 的 Header，然后再保存到协程上下文中
$request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) {
    return $request->withAddedHeader('foo', 'bar');
});
```

### Swoole Runtime Hook Level

框架在入口函数中提供了 `SWOOLE_HOOK_FLAGS` 常量，如果您需要修改整个项目的 `Runtime Hook` 等级，比如想要支持 `CURL 协程` 并且 `Swoole` 版本为 `v4.5.4` 之前的版本，可以修改这里的代码，如下。

```php
<?php
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL);
``` 

!> 如果 Swoole 版本 >= `v4.5.4`，不需要做任何修改。
