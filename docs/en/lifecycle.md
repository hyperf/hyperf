# Lifecycle

## Framework Lifecycle

Hyperf runs on top of [Swoole](http://github.com/swoole/swoole-src). To fully understand the lifecycle of Hyperf, it is essential to understand the lifecycle of [Swoole](http://github.com/swoole/swoole-src).
Hyperf's command management is supported by [symfony/console](https://github.com/symfony/console) by default (if you wish to change this component, you can also change the skeleton's entry file to use the component you prefer). After executing `php bin/hyperf.php start`, the `Hyperf\Server\Command\StartServer` command class will take over and start each `Server` defined in the configuration file `config/autoload/server.php` one by one.
The initialization of the dependency injection container is not implemented by a component because that would create significant coupling. By default, it is implemented by the entry file loading `config/container.php`.

## Request and Coroutine Lifecycle

Swoole creates a coroutine to handle each connection by default, mainly reflected in the `onRequest`, `onReceive`, and `onConnect` events. Therefore, it can be understood that each request is a coroutine. Since creating coroutines is a common operation, a single request coroutine may contain many other coroutines. Within the same process, coroutines share memory, but their execution order is non-sequential. Coroutines are essentially independent of each other and have no parent-child relationship. Therefore, the state of each coroutine needs to be managed through [Coroutine Context](coroutine.md#coroutine-context).
