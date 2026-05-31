# Coroutine-style Service

Hyperf secara default menggunakan [Swoole asynchronous style](https://wiki.swoole.com/#/server/init). Tipe ini adalah model multi-proses, dan custom process berjalan sebagai proses terpisah.

> Tipe ini akan berjalan sebagai model single-process saat menggunakan SWOOLE_BASE dan tidak menggunakan custom process. Untuk detailnya, silakan lihat dokumentasi resmi Swoole.

Hyperf juga menyediakan service bergaya coroutine. Tipe ini adalah model single-process. Semua custom process akan berjalan dalam mode coroutine, dan tidak ada proses terpisah yang dibuat.

Kedua gaya ini dapat dipilih sesuai kebutuhan, **tetapi tidak disarankan untuk sembarangan mengganti service yang sudah berjalan normal**.

## Konfigurasi

Ubah file konfigurasi `autoload/server.php` dan set `type` menjadi `Hyperf\Server\CoroutineServer::class` untuk memulai dalam gaya coroutine.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;

return [
    'type' => Hyperf\Server\CoroutineServer::class,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
        ],
    ],
];
```

## WebSocket

1. Karena ada perbedaan dalam callback yang sesuai antara gaya coroutine dan gaya asynchronous, Anda perlu menggunakannya sesuai kebutuhan.

Sebagai contoh, untuk callback `onReceive`, gaya asynchronous menggunakan `Swoole\Server`, sedangkan gaya coroutine menggunakan `Swoole\Coroutine\Server\Connection`.

```php
<?php

declare(strict_types=1);

namespace Hyperf\Contract;

use Swoole\Coroutine\Server\Connection;
use Swoole\Server as SwooleServer;

interface OnReceiveInterface
{
    /**
     * @param Connection|SwooleServer $server
     */
    public function onReceive($server, int $fd, int $reactorId, string $data): void;
}
```

2. Coroutine tempat middleware berada hanya berakhir saat `onClose`.

Karena instance database `Hyperf` dikembalikan ke connection pool ketika coroutine dihancurkan, menggunakan `Database` di middleware `WebSocket` akan mengakibatkan koneksi di connection pool tidak dikembalikan secara normal.
