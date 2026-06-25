# Suporte

O Hyperf fornece uma grande quantidade de utilitários convenientes. Alguns dos mais usados e úteis (mas não todos) estão listados nesta seção. Para mais detalhes, consulte [hyperf/support](https://github.com/hyperf/support).

## Utilitários de corrotina

### Hyperf\Coroutine\Coroutine

Este utilitário é usado para ajudar na verificação ou operação de corrotinas.

#### id(): int

Obtém o `ID da corrotina` atual usando o método estático `id()`. Se não estiver em ambiente de corrotina, retorna `-1`.

#### create(callable $callable): int

O método estático `create(callable $callable)` pode ser usado para criar uma corrotina. Também é possível fazer isso usando os métodos globais `co(callable $callable)` e `go(callable $callable)`. O método `create(callable $callable)` é um encapsulamento do método de criação no `Swoole`. A diferença é que ele não lança exceções não capturadas; em vez disso, elas serão emitidas por `Hyperf\Contract\StdoutLoggerInterface` como warnings.

#### inCoroutine(): bool

`inCoroutine()` é um método estático para determinar se o código está rodando atualmente em um ambiente de corrotina.

### Hyperf\Context\Context

O `Context` é usado para lidar com o contexto de corrotina. Ele é basicamente um encapsulamento de `Swoole\Coroutine::getContext()`. No entanto, `Hyperf\Context\Context` é compatível com execução em ambiente sem corrotina.

### Hyperf\Coordinator\CoordinatorManager

O `CoordinatorManager` é usado para agendar corrotinas quando eventos ocorrerem.

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
