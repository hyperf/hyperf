# 辅助类

Hyperf 提供了大量便捷的辅助类，这里会列出一些常用的好用的，不会列举所有，可自行查看 [hyperf/utils](https://github.com/hyperf/utils) 组件的代码获得更多信息。

## 协程辅助类

### Hyperf\Utils\Coroutine

该辅助类用于协助进行协程相关的判断或操作。

#### id(): int

通过静态方法 `id()` 获得当前所处的 `协程 ID`，如当前不处于协程环境下，则返回 `-1`。 

#### create(callable $callable): int

通过静态方法 `create(callable $callable)` 可创建一个协程，还可以通过全局函数 `co(callable $callable)` 或 `go(callable $callable)` 达到同样的目的，该方法是对 `Swoole` 创建协程方法的一个封装，区别在于不会抛出未捕获的异常，未捕获的异常会通过 `Hyperf\Contract\StdoutLoggerInterface` 以 `warning` 等级输出。

#### inCoroutine(): bool

通过静态方法 `inCoroutine()` 判断当前是否处于协程环境下。

### Hyperf\Context\Context

用于处理协程上下文，本质上是对 `Swoole\Coroutine::getContext()` 方法的一个封装，但区别在于这里兼容了非协程环境下的运行。

### Hyperf\Utils\Coordinator\CoordinatorManager

该辅助类用于指挥协程等待事件发生。

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coroutine\Coroutine;

Coroutine::create(function() {
    // 所有OnWorkerStart事件回调完成后唤醒
    CoordinatorManager::until(Constants::WORKER_START)->yield();
    echo 'worker started';
    // 分配资源
    // 所有OnWorkerExit事件回调完成后唤醒
    CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
    echo 'worker exited';
    // 回收资源
});
```
