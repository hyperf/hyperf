# Helper Classes

Hyperf menyediakan banyak kelas pembantu (helper) yang memudahkan pekerjaan. Beberapa yang umum dan berguna dicantumkan di sini. Ini bukan daftar lengkap; Anda bisa memeriksa langsung kode komponen [hyperf/support](https://github.com/hyperf/support) untuk informasi lebih lanjut.

## Coroutine Helper Classes

### Hyperf\Coroutine\Coroutine

Kelas pembantu ini digunakan untuk membantu operasi atau pengecekan terkait coroutine.

#### id(): int

Gunakan static method `id()` untuk mendapatkan `Coroutine ID` dari coroutine saat ini. Jika tidak sedang dalam lingkungan coroutine, maka `-1` akan dikembalikan.

#### create(callable $callable): int

Gunakan static method `create(callable $callable)` untuk membuat sebuah coroutine. Anda juga bisa menggunakan global functions `co(callable $callable)` atau `go(callable $callable)` untuk tujuan yang sama. Method ini merupakan wrapper untuk method pembuatan coroutine `Swoole`. Perbedaannya adalah method ini tidak akan melemparkan uncaught exception. Exception yang tidak tertangkap akan dikeluarkan di level `warning` melalui `Hyperf\Contract\StdoutLoggerInterface`.

#### inCoroutine(): bool

Gunakan static method `inCoroutine()` untuk menentukan apakah lingkungan saat ini adalah lingkungan coroutine.

### Hyperf\Context\Context

Digunakan untuk menangani konteks coroutine. Pada dasarnya, ini adalah wrapper untuk method `Swoole\Coroutine::getContext()`, tetapi perbedaannya adalah ini kompatibel dengan eksekusi di lingkungan non-coroutine.

### Hyperf\Coordinator\CoordinatorManager

Kelas pembantu ini digunakan untuk memerintahkan coroutine menunggu hingga suatu event terjadi.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coroutine\Coroutine;

Coroutine::create(function() {
    // Bangunkan setelah semua callback event OnWorkerStart selesai
    CoordinatorManager::until(Constants::WORKER_START)->yield();
    echo 'worker started';
    // Alokasikan resource
    // Bangunkan setelah semua callback event OnWorkerExit selesai
    CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
    echo 'worker exited';
    // Bebaskan resource
});
```
