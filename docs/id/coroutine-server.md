# Server Gaya Coroutine

Secara default, Hyperf menggunakan [Swoole asynchronous style](https://wiki.swoole.com/#/server/init),
yang merupakan model multi-process dan proses kustom berjalan dalam proses
terpisah.

> Tipe ini akan berjalan dalam mode single-process ketika menggunakan
> SWOOLE_BASE dan tidak menggunakan proses kustom. Anda dapat memeriksa
> dokumentasi resmi Swoole untuk detailnya.

Hyperf juga menyediakan layanan gaya coroutine (coroutine style), yang
merupakan model single-process, dan semua proses kustom akan berjalan dalam
mode coroutine, tanpa membuat proses terpisah.

Kedua gaya tersebut dapat dipilih sesuai kebutuhan, **tetapi tidak disarankan
untuk beralih ke layanan yang sudah ada tanpa pertimbangan matang**.

## Konfigurasi

Ubah file konfigurasi `autoload/server.php` dan atur `type` menjadi
`Hyperf\Server\CoroutineServer::class` untuk memulai gaya coroutine.

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

1. Karena gaya coroutine dan gaya asynchronous memiliki perbedaan pada callback
masing-masing, maka perlu digunakan sesuai dengan kebutuhan.

Sebagai contoh, pada callback `onReceive`, gaya asynchronous menggunakan
`Swoole\Server`, sedangkan gaya coroutine menggunakan
`Swoole\Coroutine\Server\Connection`.

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

2. Coroutine tempat middleware berada hanya akan berakhir saat `onClose`.

Karena instance database `Hyperf` dikembalikan ke connection pool saat
coroutine dihancurkan, jika `Database` digunakan dalam middleware `WebSocket`,
maka koneksi dalam connection pool tidak akan dikembalikan dengan normal.
