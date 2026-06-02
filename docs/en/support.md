# Helper Classes

Hyperf provides a large number of convenient helper classes. Some commonly used and useful ones are listed here. This is not an exhaustive list; you can check the code of the [hyperf/support](https://github.com/hyperf/support) component yourself for more information.

## Coroutine Helper Classes

### Hyperf\Coroutine\Coroutine

This helper class is used to assist with coroutine-related judgments or operations.

#### id(): int

Use the static method `id()` to obtain the `Coroutine ID` of the current coroutine. If not currently in a coroutine environment, `-1` is returned.

#### create(callable $callable): int

Use the static method `create(callable $callable)` to create a coroutine. You can also achieve the same purpose through the global functions `co(callable $callable)` or `go(callable $callable)`. This method is a wrapper for the `Swoole` coroutine creation method. The difference is that it does not throw uncaught exceptions. Uncaught exceptions will be output at `warning` level via `Hyperf\Contract\StdoutLoggerInterface`.

#### inCoroutine(): bool

Use the static method `inCoroutine()` to determine whether the current environment is a coroutine environment.

### Hyperf\Context\Context

Used to handle the coroutine context. Essentially, it is a wrapper for the `Swoole\Coroutine::getContext()` method, but the difference is that it is compatible with execution in non-coroutine environments.

### Hyperf\Coordinator\CoordinatorManager

This helper class is used to command coroutines to wait for events to occur.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coroutine\Coroutine;

Coroutine::create(function() {
    // Wake up after all OnWorkerStart event callbacks are completed
    CoordinatorManager::until(Constants::WORKER_START)->yield();
    echo 'worker started';
    // Allocate resources
    // Wake up after all OnWorkerExit event callbacks are completed
    CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
    echo 'worker exited';
    // Reclaim resources
});
```
