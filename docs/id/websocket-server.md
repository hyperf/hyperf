# WebSocket Server

Hyperf menyediakan wrapper untuk WebSocket Server, sehingga Anda dapat dengan cepat membangun aplikasi WebSocket berbasis komponen [hyperf/websocket-server](https://github.com/hyperf/websocket-server).

## Instalasi

```bash
composer require hyperf/websocket-server
```

## Konfigurasi Server

Ubah `config/autoload/server.php` dan tambahkan konfigurasi berikut:

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

## Konfigurasi Routes

> Saat ini, hanya mode file konfigurasi yang didukung untuk routing. Mode annotation akan tersedia di masa mendatang.

Di file `config/routes.php`, tambahkan konfigurasi routing untuk Server `ws` yang sesuai. Nilai `ws` di sini tergantung pada nilai `name` dari WebSocket Server yang Anda konfigurasi di `config/autoload/server.php`.

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## Konfigurasi Middleware

Di file `config/autoload/middlewares.php`, tambahkan konfigurasi global middleware untuk Server `ws` yang sesuai. Nilai `ws` di sini tergantung pada nilai `name` dari WebSocket Server yang Anda konfigurasi di `config/autoload/server.php`.

```php
<?php

return [
    'ws' => [
        yourMiddleware::class
    ]
];
```

## Membuat Controller

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Engine\WebSocket\Frame;
use Hyperf\Engine\WebSocket\Response;
use Hyperf\WebSocketServer\Constant\Opcode;
use Swoole\Server;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage($server, $frame): void
    {
        $response = (new Response($server))->init($frame);
        if($frame->opcode == Opcode::PING) {
            // Jika menggunakan Coroutine Server, Anda perlu menanganinya secara manual dan mengembalikan frame PONG setelah mengidentifikasi frame PING.
            // Untuk asynchronous style Server, bisa ditangani langsung melalui konfigurasi Swoole. Detailnya lihat https://wiki.swoole.com/#/websocket_server?id=open_websocket_ping_frame
            $response->push(new Frame(opcode: Opcode::PONG));
            return;
        }
        $response->push(new Frame(payloadData: 'Recv: ' . $frame->data));
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen($server, $request): void
    {
        $response = (new Response($server))->init($request);
        $response->push(new Frame(payloadData: 'Opened'));
    }
}
```

Selanjutnya, jalankan Server, dan Anda akan melihat bahwa WebSocket Server telah berjalan dan mendengarkan di port 9502. Anda kemudian dapat menggunakan berbagai WebSocket Client untuk terhubung dan mentransmisikan data.

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

!> Saat kita mendengarkan port 9501 dari HTTP Server dan port 9502 dari WebSocket Server secara bersamaan, WebSocket Client dapat terhubung ke WebSocket Server melalui kedua port tersebut, yaitu koneksi ke `ws://0.0.0.0:9501` dan `ws://0.0.0.0:9502` akan berhasil.

Karena `Swoole\WebSocket\Server` mewarisi dari `Swoole\Http\Server`, Anda dapat menggunakan HTTP untuk memicu semua push WebSocket. Untuk detail lebih lanjut, Anda dapat melihat bagian callback `onRequest` di [dokumentasi Swoole](https://wiki.swoole.com/#/websocket_server?id=websocketserver).

Jika Anda perlu menonaktifkannya, Anda dapat mengubah file `config/autoload/server.php` dan menambahkan item konfigurasi `open_websocket_protocol` ke service `http`.

```php
<?php
return [
    // Konfigurasi lain dari file ini dihapus
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

## Connection Context

Callback onOpen, onMessage, dan onClose pada WebSocket service tidak dipicu dalam coroutine yang sama, sehingga Anda tidak bisa langsung menggunakan coroutine context untuk menyimpan informasi state. WebSocket Server component menyediakan context **level koneksi**, dan API-nya persis sama dengan coroutine context.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Engine\WebSocket\Frame;
use Hyperf\Engine\WebSocket\Response;
use Hyperf\WebSocketServer\Context;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface
{
    public function onMessage($server, $frame): void
    {
        $response = (new Response($server))->init($frame);
        $response->push(new Frame(payloadData: 'Username: ' . Context::get('username')));
    }

    public function onOpen($server, $request): void
    {
        Context::set('username', $request->cookie['username']);
    }
}
```

## Konfigurasi Multiple Server

```
# /etc/nginx/conf.d/ng_socketio.conf
# Multiple ws servers
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
    # Forward ke multiple ws servers
    proxy_pass http://io_nodes;
  }
}
```

## Message Sender

Ketika kita ingin menutup koneksi `WebSocket` di service `HTTP`, kita bisa langsung menggunakan `Hyperf\WebSocketServer\Sender`.

`Sender` akan menentukan apakah `fd` dimiliki oleh `Worker` saat ini. Jika iya, maka akan mengirim data secara langsung; jika tidak, akan mengirim melalui `PipeMessage` ke semua `Worker` kecuali dirinya sendiri, kemudian `Worker` lain akan menentukannya. Jika `fd` tersebut dimiliki oleh `Worker` tersebut, maka akan mengirim data yang sesuai ke client.

`Sender` mendukung dua API: `push` dan `disconnect`, sebagai berikut:

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

## Menangani HTTP Request di WebSocket Service

Selain memisahkan HTTP service dan WebSocket service melalui port, kita juga dapat mendengarkan HTTP request di WebSocket.

Karena item konfigurasi di `server.servers.*.callbacks` bersifat singleton, kita perlu mengkonfigurasi instance terpisah di `dependencies`.

```php
<?php
return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
];
```

Kemudian ubah konfigurasi `callbacks` di service `WebSocket` kita. Konfigurasi yang tidak relevan disembunyikan di bawah.

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

Terakhir, kita dapat menambahkan route `HTTP` di `ws`.
