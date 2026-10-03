# Signal Handler

Signal handler secara otomatis mendaftar ke signal manager setelah process `Worker` dan process `Custom` dimulai.

## Instalasi

```
composer require hyperf/signal
```

## Publikasi Konfigurasi

Anda dapat mempublikasikan file konfigurasi default ke proyek Anda menggunakan perintah berikut:

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## Menambahkan Handler

Di bawah ini, kita mendengarkan sinyal `SIGTERM` dari process `Worker`. Ketika sinyal diterima, kita mencetak nilai sinyal tersebut.

```php
<?php

declare(strict_types=1);

namespace App\Signal;

use Hyperf\Signal\Annotation\Signal;
use Hyperf\Signal\SignalHandlerInterface;

#[Signal]
class TermSignalHandler implements SignalHandlerInterface
{
    public function listen(): array
    {
        return [
            [SignalHandlerInterface::WORKER, SIGTERM],
        ];
    }

    public function handle(int $signal): void
    {
        var_dump($signal);
    }
}
```

Karena sinyal `SIGTERM` yang diterima oleh process Worker tidak dapat keluar secara normal setelah ditangkap, pengguna dapat langsung `Ctrl + C` untuk keluar, atau memodifikasi konfigurasi `config/autoload/signal.php` sebagai berikut:

> WorkerStopHandler tidak cocok untuk CoroutineServer. Jika diperlukan, silakan implementasikan sendiri.

```php
<?php

declare(strict_types=1);

return [
    'handlers' => [
        Hyperf\Signal\Handler\WorkerStopHandler::class => PHP_INT_MIN
    ],
    'timeout' => 5.0,
];
```

Setelah `WorkerStopHandler` terpicu, process saat ini akan ditutup setelah melebihi waktu konfigurasi [max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time).

## Contoh Konfigurasi Listener untuk Coroutine-style Server

> Listener default di atas diadaptasi untuk layanan gaya asynchronous. Jika Anda perlu menggunakannya di layanan gaya coroutine, Anda dapat mengikuti konfigurasi kustom di bawah ini:

```php
<?php

declare(strict_types=1);

namespace App\Kernel\Signal;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Process\ProcessManager;
use Hyperf\Server\ServerManager;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;

class CoroutineServerStopHandler implements SignalHandlerInterface
{

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        // Gaya coroutine hanya akan memiliki satu process Worker, jadi kita hanya perlu mendengarkan WORKER di sini.
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // Loop melalui dan matikan server yang sudah dimulai
            $server->shutdown();
        }
    }
}
```
