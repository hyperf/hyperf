# Signal Handler

Signal handler akan mendengarkan proses `Worker` dan proses `custom` serta
mendaftar secara otomatis ke signal manager setelah dimulai.

## Instalasi

```
composer require hyperf/signal
```

## Publikasikan Konfigurasi

Anda dapat mempublikasikan file konfigurasi default ke proyek Anda dengan
perintah berikut:

```bash
php bin/hyperf.php vendor:publish hyperf/signal
```

## Menambahkan Handler

Berikut ini kita mendengarkan signal `SIGTERM` dari proses `Worker`, dan
mencetak nilai signal saat signal tersebut diterima.

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

Karena signal SIGTERM yang diterima oleh proses Worker ditangkap, ia tidak dapat
keluar secara normal. Oleh karena itu, pengguna dapat langsung menekan
`Ctrl + C` untuk keluar, atau mengubah konfigurasi `config/autoload/signal.php`
sebagai berikut:

> WorkerStopHandler tidak cocok untuk CoroutineServer, silakan implementasikan
> sendiri jika diperlukan

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

Setelah `WorkerStopHandler` dipicu, ia akan menutup proses saat ini setelah
waktu konfigurasi
[max_wait_time](https://wiki.swoole.com/#/server/setting?id=max_wait_time) yang
telah ditentukan.

## Contoh Konfigurasi Listener Layanan Bergaya Coroutine

> Listener default di atas semuanya disesuaikan untuk layanan bergaya asinkron.
> Jika Anda perlu menggunakannya dalam layanan bergaya coroutine, Anda dapat
> menyesuaikan konfigurasinya sebagai berikut

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
        // There is only one Worker process in the coroutine style, so you only need to monitor the WORKER here.
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        ProcessManager::setRunning(false);

        foreach (ServerManager::list() as [$type, $server]) {
            // Cyclically close open services
            $server->shutdown();
        }
    }
}

```
