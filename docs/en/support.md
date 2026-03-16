# Support

Hyperf provides a large number of convenient utils. Some commonly used and useful ones but not all of them are listed in this section. For more details, please refer [hyperf/support](https://github.com/hyperf/support).

## Coroutine Util

### Hyperf\Coroutine\Coroutine

This util is used to assist in the judgment or operation of the coroutine.

#### id(): int

Get current `coroutine ID` by using static method `id()`. If it is not under the coroutine environment return `-1`.

#### create(callable $callable): int

The static method `create(callable $callable)` can be used to create a coroutine. It can also be done by using global method `co(callable $callable)` and `go(callable $callable)`. The `create(callable $callable)` method is an encapsulation of the creation method in `Swoole`. The difference is that it will not throw out uncaught exceptions, which will be thrown by `Hyperf\Contract\StdoutLoggerInterface` as `warning` exceptions.

#### inCoroutine(): bool

`inCoroutine()` is a static method to determine whether it is currently in a coroutine environment.

### Hyperf\Context\Context

The `Context` is used to handle coroutine context. It is basically an encapsulation of `Swoole\Coroutine::getContext()`. However, the `Hyperf\Context\Context` is compatible with running in a non-coroutine environment.

### Hyperf\Coordinator\CoordinatorManager

The `CoordinatorManager` is used to schedule the coroutine when events occurred.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coroutine\Coroutine;

Coroutine::create(function() {
    // Invoked after all OnWorkerStart event callbacks are completed
    CoordinatorManager::until(Constants::WORKER_START)->yield();
    echo 'worker started';
    // Assigning resources
    // Invoked after all OnWorkerStart event callbacks are completed
    CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
    echo 'worker exited';
    // Recycling resources
});
```
