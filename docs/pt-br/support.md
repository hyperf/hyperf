# Utilitários

O Hyperf fornece um grande número de utils convenientes. Alguns dos mais comumente usados e úteis, mas não todos, são listados nesta seção. Para mais detalhes, consulte [hyperf/support](https://github.com/hyperf/support).

## Util de Coroutine

### Hyperf\Coroutine\Coroutine

Este util é usado para auxiliar no julgamento ou operação da Coroutine.

#### id(): int

Obtenha o `coroutine ID` atual usando o método estático `id()`. Se não estiver em um ambiente de Coroutine, retorna `-1`.

#### create(callable $callable): int

O método estático `create(callable $callable)` pode ser usado para criar uma Coroutine. Isso também pode ser feito usando os métodos globais `co(callable $callable)` e `go(callable $callable)`. O método `create(callable $callable)` é um encapsulamento do método de criação no `Swoole`. A diferença é que ele não lançará exceções não capturadas, que serão lançadas por `Hyperf\Contract\StdoutLoggerInterface` como exceções de `warning`.

#### inCoroutine(): bool

`inCoroutine()` é um método estático para determinar se atualmente está em um ambiente de Coroutine.

### Hyperf\Context\Context

O `Context` é usado para lidar com o contexto da Coroutine. É basicamente um encapsulamento de `Swoole\Coroutine::getContext()`. No entanto, o `Hyperf\Context\Context` é compatível com a execução em ambientes sem Coroutine.

### Hyperf\Coordinator\CoordinatorManager

O `CoordinatorManager` é usado para agendar a Coroutine quando eventos ocorrem.

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
