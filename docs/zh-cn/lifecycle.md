# 生命周期

## 框架生命周期

Hyperf 是运行于 [Swoole](http://github.com/swoole/swoole-src) 之上的，想要理解透彻 Hyperf 的生命周期，那么理解 [Swoole](http://github.com/swoole/swoole-src) 的生命周期也至关重要。   
Hyperf 的命令管理默认由 [symfony/console](https://github.com/symfony/console) 提供支持*(如果您希望更换该组件您也可以通过改变 skeleton 的入口文件更换成您希望使用的组件)*，在执行 `php bin/hyperf.php start` 后，将由 `Hyperf\Server\Command\StartServer` 命令类接管，并根据配置文件 `config/autoload/server.php` 内定义的 `Server` 逐个启动。   
关于依赖注入容器的初始化工作，我们并没有由组件来实现，因为一旦交由组件来实现，这个耦合就会非常的明显，所以在默认的情况下，是由入口文件来加载 `config/container.php` 来实现的。

## 请求与协程生命周期

Swoole 在处理每个连接时，会默认创建一个协程去处理，主要体现在 `onRequest`、`onReceive`、`onConnect` 事件，所以可以理解为每个请求都是一个协程，由于创建协程也是个常规操作，所以一个请求协程里面可能会包含很多个协程，同一个进程内协程之间是内存共享的，但调度顺序是非顺序的，且协程间本质上是相互独立的没有父子关系，所以对每个协程的状态处理都需要通过 [协程上下文](zh-cn/coroutine.md#协程上下文) 来管理。   

