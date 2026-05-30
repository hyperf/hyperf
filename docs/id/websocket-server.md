# WebSocket Server

Hyperf menyediakan enkapsulasi untuk WebSocket Server. Aplikasi WebSocket dapat
dibangun dengan cepat berbasis
[hyperf/websocket-server](https://github.com/hyperf/websocket-server).

## Instalasi

```bash
composer require hyperf/websocket-server
```

## Konfigurasi Server

Ubah `config/autoload/server.php` dan tambahkan konfigurasi berikut.

```php
<?php

return [
    'servers' => [
        [
            'name' => 'ws',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_HAND_SHAKE => [Hyperf\WebSocketServer\Server::class, 'onHandShake'],
                Event::ON_MESSAGE => [Hyperf\WebSocketServer\Server::class, 'onMessage'],
                Event::ON_CLOSE => [Hyperf\WebSocketServer\Server::class, 'onClose'],
            ],
        ],
    ],
];
```

## Konfigurasi Router

> Sejauh ini, hanya metode file konfigurasi yang didukung. Metode anotasi akan
> segera hadir.

Dalam file `config/routes.php`, tambahkan konfigurasi router untuk Server `ws`
yang sesuai, di mana `ws` adalah `name` dari WebSocket Server di
`config/autoload/server.php`.

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## Konfigurasi Middleware

Dalam file `config/autoload/middlewares.php`, tambahkan konfigurasi middleware
untuk Server `ws` yang sesuai, di mana `ws` adalah `name` dari WebSocket Server
di `config/autoload/server.php`.

```php
<?php

return [
    'ws' => [
        yourMiddleware::class
    ]
];
```

## Membuat Controller yang Sesuai

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage($server, Frame $frame): void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen($server, Request $request): void
    {
        $server->push($request->fd, 'Opened');
    }
}
```

Jalankan Server, kemudian Anda dapat melihat WebSocket Server telah aktif dan
mendengarkan port 9502. Anda dapat menggunakan WebSocket Client apa pun untuk
berkomunikasi dengan WebSocket Server ini.

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

!> Ketika kita mendengarkan port 9501 untuk HTTP Server dan port 9502 untuk
WebSocket Server pada saat yang sama, WebSocket Client dapat terhubung ke
WebSocket Server melalui kedua port tersebut (9501 dan 9502). Dengan kata lain,
menghubungkan ke `ws://0.0.0.0:9501` dan `ws:/ /0.0.0.0:9502` keduanya akan
berhasil.

Karena `Swoole\WebSocket\Server` mewarisi dari `Swoole\Http\Server`, Anda dapat
menggunakan HTTP untuk melakukan semua push WebSocket. Untuk detail lebih
lanjut, silakan merujuk ke callback `onRequest` pada [Dokumentasi Swoole](https://wiki.swoole.com/#/websocket_server?id=websocketserver)

Jika Anda ingin menonaktifkannya, Anda dapat menambahkan item konfigurasi
`open_websocket_protocol` ke layanan `http` dalam file
`config/autoload/server.php`.

```php
<?php
return [
    // Unrelated configs are ignored
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
            'settings' => [
                'open_websocket_protocol' => false,
            ]
        ],
    ]
];
```

## Connected Context

Callback untuk onOpen, onMessage, dan onClose pada WebSocket tidak dipicu dalam
coroutine yang sama, sehingga tidak dapat langsung menggunakan informasi yang
disimpan di context. **Connected Context** disediakan oleh komponen WebSocket
Server, dan API-nya sama dengan context coroutine.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\WebSocketServer\Context;
use Swoole\Http\Request;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface
{
    public function onMessage($server, Frame $frame): void
    {
        $server->push($frame->fd, 'Username: ' . Context::get('username'));
    }

    public function onOpen($server, Request $request): void
    {
        Context::set('username', $request->cookie['username']);
    }
}
```

## Konfigurasi Multiple Server

```
# /etc/nginx/conf.d/ng_socketio.conf
# multiple ws server
upstream io_nodes {
    server ws1:9502;
    server ws2:9502;
}
server {
  listen 9502;
  # server_name your.socket.io;
  location / {
    proxy_set_header Upgrade "websocket";
    proxy_set_header Connection "upgrade";
    # proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    # proxy_set_header Host $host;
    # proxy_http_version 1.1;
    # Forward to multiple ws server
    proxy_pass http://io_nodes;
  }
}
```

## Sender

Ketika Anda ingin menutup koneksi `WebSocket` di layanan `HTTP`, Anda dapat
menggunakan `Hyperf\WebSocketServer\Sender`.

`Sender` akan memeriksa apakah `fd` dibawa oleh `Worker` saat ini. Jika ya,
pesan akan dikirim langsung. Jika tidak, pesan akan dikirim ke semua `Worker`
lainnya melalui `PipeMessage`. `Worker` lain akan melakukan hal yang sama seperti
yang disebutkan di atas.

`Sender` mendukung `push` dan `disconnect`.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\WebSocketServer\Sender;
use function Hyperf\Coroutine\go;

#[AutoController]
class ServerController
{
    #[Inject]
    protected Sender $sender;

    public function close(int $fd)
    {
        go(function () use ($fd) {
            sleep(1);
            $this->sender->disconnect($fd);
        });

        return '';
    }

    public function send(int $fd)
    {
        $this->sender->push($fd, 'Hello World.');

        return '';
    }
}
```

## Menangani HTTP Request di Websocket Server

Selain memisahkan layanan HTTP dan layanan WebSocket melalui port, kita juga
dapat mendengarkan HTTP request di dalam WebSocket.

Karena item konfigurasi `server.servers.*.callbacks` semuanya adalah singleton,
kita perlu mendefinisikan konfigurasi singleton baru di `dependencies`.

```php
<?php
return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
];
```

Kemudian ubah konfigurasi `callbacks` di layanan `WebSocket` kita. Bagian
berikut menyembunyikan konfigurasi yang tidak relevan.

```php
<?php

declare(strict_types=1);

use Hyperf\Server\Event;
use Hyperf\Server\Server;

return [
    'mode' => SWOOLE_BASE,
    'servers' => [
        [
            'name' => 'ws',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => 9502,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => ['HttpServer', 'onRequest'],
                Event::ON_HAND_SHAKE => [Hyperf\WebSocketServer\Server::class, 'onHandShake'],
                Event::ON_MESSAGE => [Hyperf\WebSocketServer\Server::class, 'onMessage'],
                Event::ON_CLOSE => [Hyperf\WebSocketServer\Server::class, 'onClose'],
            ],
        ],
    ],
];
```

Akhirnya, kita dapat menambahkan routing `HTTP` di `ws`.
