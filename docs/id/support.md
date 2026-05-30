# Support

Hyperf menyediakan banyak utilitas (utils) yang memudahkan. Beberapa yang
sering digunakan dan berguna, tetapi tidak semuanya, dicantumkan dalam bagian
ini. Untuk detail selengkapnya, silakan merujuk ke
[hyperf/support](https://github.com/hyperf/support).

## Coroutine Util

### Hyperf\Coroutine\Coroutine

Util ini digunakan untuk membantu penilaian atau pengoperasian coroutine.

#### id(): int

Dapatkan `coroutine ID` saat ini dengan menggunakan metode statis `id()`. Jika
tidak berada di bawah lingkungan coroutine, metode ini mengembalikan `-1`.

#### create(callable $callable): int

Metode statis `create(callable $callable)` dapat digunakan untuk membuat sebuah
coroutine. Hal ini juga dapat dilakukan dengan menggunakan metode global
`co(callable $callable)` dan `go(callable $callable)`. Metode
`create(callable $callable)` merupakan enkapsulasi dari metode pembuatan di
`Swoole`. Perbedaannya adalah metode ini tidak akan melempar exception yang
tidak ditangkap, yang mana exception tersebut akan dilempar oleh
`Hyperf\Contract\StdoutLoggerInterface` sebagai exception `warning`.

#### inCoroutine(): bool

`inCoroutine()` adalah metode statis untuk menentukan apakah saat ini sedang
berada dalam lingkungan coroutine.

### Hyperf\Context\Context

`Context` digunakan untuk menangani context coroutine. Pada dasarnya ini adalah
enkapsulasi dari `Swoole\Coroutine::getContext()`. Namun,
`Hyperf\Context\Context` kompatibel untuk dijalankan di lingkungan
non-coroutine.

### Hyperf\Coordinator\CoordinatorManager

`CoordinatorManager` digunakan untuk menjadwalkan coroutine ketika event
terjadi.

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
