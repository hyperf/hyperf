# Life Cycle

## Life Cycle of Framework

Hyperf is based on [Swoole](http://github.com/swoole/swoole-src). To understand the life cycle of Hyperf, then understand the life cycle of [Swoole](http://github.com/swoole/swoole-src) is also crucial.   
 
Hyperf's command management is supported by [symfony/console](https://github.com/symfony/console) by default * (if you wish to replace this component you can also change the entry file of skeleton to the component that you wish to use) *, after executing `php bin/hyperf.php start`, it will be taken over by the `Hyperf\Server\Command\StartServer` command class and started one by one according to the `Server` defined in the configuration file `config/autoload/server.php`.   
 
Regarding the initialization of the dependency injection container, we are not implemented by any component, because once it is implemented by some component, the coupling will be very obvious, so by default, the configuration file `config/container.php` is loaded by the entry file to initialize the container.

## Life Cycle of Request and Coroutine

When Swoole handles each connection, it will create a coroutine to handle by default, mainly in the `onRequest`, `onReceive`, `onConnect` events, so it can be understood that each request is a coroutine. Because of creates coroutines is also a normal operation, so a request coroutine may contain many coroutines, and the same intra-process coroutine is memory shared, but the scheduling order is non-sequential, and the coroutines are essentially independent of each other, no parent-child relationship, so the state processing for each coroutine needs to be managed by [Coroutine Context](en/coroutine.md#coroutine context).

