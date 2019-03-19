# 协程编程注意事项

## 不能存在阻塞代码

协程内代码的阻塞会导致协程调度器无法切换到另一个协程继续执行代码，所以我们绝不能在协程内存在阻塞代码，假设我们启动了4个 Worker 来处理 HTTP 请求（通常启动的 Worker 数量与 CPU 核心数一致或2倍），如果代码中存在阻塞，暂且理论的认为每个请求都会阻塞1秒，那么系统的 QPS 也将退化为 4/s，这无疑就是退化成了与 PHP-FPM 类似的情况，所以我们绝对不能在协程中存在阻塞代码。   

那么到底哪些是阻塞代码呢？我们可以简单的认为绝大多数你所熟知的非 Swoole 提供的异步函数的 `MySQL`、`Redis`、`Memcache`、`MongoDB`、`HTTP`、`Socket`等客户端，文件操作、`sleep/usleep` 等均为阻塞函数，这几乎涵盖了所有日常操作，那么要如何解决呢？Swoole 提供了 MySQL、PostgreSQL、Redis、HTTP、Socket 的协程客户端可以使用，同时 Swoole 4.1 之后提供了一键协程化的方法 `\Swoole\Coroutine::enableCoroutine()`，只需在使用协程前运行这一行代码，Swoole 会将 所有使用 `php_stream` 进行 `socket` 操作均变成协程调度的异步 I/O，可以理解为除了 `curl` 绝大部分原生的操作都可以适用，关于此部分可查阅 [Swoole 文档](https://wiki.swoole.com/wiki/page/965.html) 获得更具体的信息。  

在 Hyperf 中我们已经为您处理好了这一切，您只需关注 `\Swoole\Coroutine::enableCoroutine()` 仍无法协程化的阻塞代码即可。

## 不能通过全局变量储存状态

在 Swoole 的持久化应用下，一个 Worker 内的全局变量是 Worker 内共享的，而从协程的介绍我们可以知道同一个 Worker 内还会存在多个协程并存在协程切换，也就意味着一个 Worker 会在一个时间周期内同时处理多个协程（或直接理解为请求）的代码，也就意味着如果使用了全局变量来储存状态可能会被多个协程所使用，也就是说不同的请求之间可能会混淆数据，这里的全局变量指的是 `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`等`$_`开头的变量、`global` 变量，以及 `static` 静态属性。    
那么当我们需要使用到这些特性时应该怎么办？   

对于全局变量，均是跟随着一个 `请求(Request)` 而产生的，而 Hyperf 的 `请求(Request)/响应(Response)` 是由 [hyperf-cloud/http-message](https://github.com/hyperf-cloud/http-message) 通过实现 [PSR-7](https://www.php-fig.org/psr/psr-7/) 处理的，顾所有的全局变量均可以在 请求(Request) 对象中得到相关的值；   

对于 `global` 变量和 `static` 变量，在 PHP-FPM 模式下，本质都是存活于一个请求声明周期内的，而在 Hyperf 内因为是 CLI 应用，会存在 `全局周期` 和 `请求周期(协程周期)` 两种长生命周期。   
- 全局周期，我们只需要创建一个静态变量供全局调用即可，静态变量意味着在服务启动后，任意协程和代码逻辑均共享此静态变量内的数据，也就意味着存放的数据不能是特别服务于某一个请求或某一个协程；
- 协程周期，由于 Hyperf 会为每个请求自动创建一个协程来处理，那么一个协程周期在此也可以理解为一个请求周期，在协程内，所有的状态数据均应存放于 `Hyperf\Utils\Context` 类中，通过该类的 `get`、`set` 来读取和存储任意结构的数据，这个 Context(协程上下文) 类在执行任意协程时读取或存储的数据都是仅限对应的协程的，同时在协程结束时也会自动销毁相关的上下文数据。