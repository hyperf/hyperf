# Socket.io Server

Socket.io adalah protokol dan framework komunikasi real-time lapisan aplikasi yang sangat populer, yang dapat dengan mudah mengimplementasikan response, grouping, dan broadcasting. `hyperf/socketio-server` mendukung protokol transport WebSocket dari Socket.io.

## Instalasi

```bash
composer require hyperf/socketio-server
```

Komponen `hyperf/socketio-server` diimplementasikan berdasarkan WebSocket. Pastikan konfigurasi `WebSocket Server` sudah ditambahkan ke server.

```php
// config/autoload/server.php
[
    'name' => 'socket-io',
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
```

## Quick Start

### Server Side

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Codec\Json;

#[SocketIONamespace("/")]
class WebSocketController extends BaseNamespace
{
    /**
     * @param string $data
     */
    #[Event("event")]
    public function onEvent(Socket $socket, $data)
    {
        // Response
        return 'Event Received: ' . $data;
    }

    /**
     * @param string $data
     */
    #[Event("join-room")]
    public function onJoinRoom(Socket $socket, $data)
    {
        // Menggabungkan user saat ini ke room
        $socket->join($data);
        // Push ke user lain di room (tidak termasuk user saat ini)
        $socket->to($data)->emit('event', $socket->getSid() . "has joined {$data}");
        // Broadcast ke semua orang di room (termasuk user saat ini)
        $this->emit('event', 'There are ' . count($socket->getAdapter()->clients($data)) . " players in {$data}");
    }

    /**
     * @param string $data
     */
    #[Event("say")]
    public function onSay(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }
}
```

> Setiap socket secara otomatis bergabung ke room yang dinamai dengan `sid` miliknya sendiri (`$socket->getSid()`). Untuk mengirim pesan pribadi, cukup push ke `sid` yang sesuai.

> Framework secara otomatis memicu dua event: `connect` dan `disconnect`.

### Client Side

Karena server-side hanya mengimplementasikan komunikasi WebSocket, client-side perlu menambahkan `{transports:["websocket"]}`.

```html
<script src="https://cdn.bootcdn.net/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
<script>
    var socket = io('ws://127.0.0.1:9502', { transports: ["websocket"] });
    socket.on('connect', data => {
        socket.emit('event', 'hello, hyperf', console.log);
        socket.emit('join-room', 'room1', console.log);
        setInterval(function () {
            socket.emit('say', '{"room":"room1", "message":"Hello Hyperf."}');
        }, 1000);
    });
    socket.on('event', console.log);
</script>
```

## Daftar API

### Socket API

Gunakan Socket API untuk push ke target Socket, atau berbicara di room sebagai target Socket. Ini perlu digunakan di event callback.

```php
<?php
#[Event("SomeEvent")]
function onSomeEvent(\Hyperf\SocketIOServer\Socket $socket){

  // sending to the client
  // Push event 'hello' ke koneksi
  $socket->emit('hello', 'can you hear me?', 1, 2, 'abc');

  // sending to all clients except sender
  // Push event 'broadcast' ke semua koneksi, tidak termasuk koneksi saat ini.
  $socket->broadcast->emit('broadcast', 'hello friends!');

  // sending to all clients in 'game' room except sender
  // Push event 'nice game' ke semua koneksi di room 'game', tidak termasuk koneksi saat ini.
  $socket->to('game')->emit('nice game', "let's play a game");

  // sending to all clients in 'game1' and/or in 'game2' room, except sender
  // Push event 'nice game' ke semua koneksi di room 'game1' dan room 'game2', tidak termasuk koneksi saat ini.
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // WARNING: `$socket->to($socket->getSid())->emit()` TIDAK akan bekerja, karena akan mengirim ke semua orang di room
  // yang bernama `$socket->getSid()` kecuali pengirim. Gunakan `$socket->emit()` classic sebagai gantinya.
  // Catatan: Jangan menambahkan `to` saat push ke diri sendiri, karena `$socket->to()` selalu mengecualikan diri sendiri. Cukup gunakan `$socket->emit()` secara langsung.

  // sending with acknowledgement
  // Kirim pesan dan tunggu respons client.
  $reply = $socket->emit('question', 'do you think so?')->reply();

  // sending without compression
  // Push tanpa kompresi
  $socket->compress(false)->emit('uncompressed', "that's rough");
}
```

### Global API

Dapatkan singleton SocketIO langsung dari container. Singleton ini dapat digunakan untuk broadcast global atau komunikasi dengan room atau individu tertentu. Ketika namespace tidak ditentukan, ruang '/' digunakan secara default.

```php
<?php
$io = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

// sending to all clients in 'game' room, including sender
// Push event 'big-announcement' ke semua koneksi di room 'game'.
$io->in('game')->emit('big-announcement', 'the game will start soon');

// sending to all clients in namespace 'myNamespace', including sender
// Push event 'bigger-announcement' ke semua koneksi di namespace '/myNamespace'
$io->of('/myNamespace')->emit('bigger-announcement', 'the tournament will start soon');

// sending to a specific room in a specific namespace, including sender
// Push event 'event' ke semua koneksi di 'room' dalam namespace '/myNamespace'
$io->of('/myNamespace')->to('room')->emit('event', 'message');

// sending to individual socketid (private message)
// Push point-to-point ke socketId
$io->to('socketId')->emit('hey', 'I just met you');

// sending to all clients on this node (when using multiple nodes)
// Push ke semua koneksi di node lokal
$io->local->emit('hi', 'my lovely babies');

// sending to all connected clients
// Push ke semua koneksi
$io->emit('an event sent to all connected clients');
```

### Namespace API

Sama seperti Global API, tetapi terbatas pada namespace tersebut.
```php
// Pseudocode berikut setara
$foo->emit();
$io->of('/foo')->emit();

/**
 * Penggunaan di dalam class juga setara
 */
#[SocketIONamespace("/foo")]
class FooNamespace extends BaseNamespace {
    public function onEvent(){
        $this->emit(); 
        $this->io->of('/foo')->emit();
    }
}
```

## Tutorial Lanjutan

### Mengatur Socket.io Namespace

Socket.io mencapai multiplexing melalui custom namespace. (Catatan: Bukan PHP namespace)

1. Anda dapat memetakan controller ke namespace 'xxx' menggunakan `#[SocketIONamespace("/xxx")]`.

2. Atau tambahkan di route:

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx' , WebSocketController::class);
```

### Mengaktifkan Session

Install dan konfigurasi komponen `hyperf/session` beserta middleware yang sesuai, kemudian potong ke SocketIO menggunakan `SessionAspect` untuk menggunakan Session.

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> Swoole versi 4.4.17 dan di bawahnya hanya bisa membaca cookie yang dibuat oleh HTTP. Swoole versi 4.4.18 ke atas dapat membuat cookie selama WebSocket handshake.

### Menyesuaikan Room Adapter

Fungsi room default diimplementasikan melalui Redis adapter, yang dapat beradaptasi dengan skenario multi-proses dan bahkan terdistribusi.

1. Dapat diganti dengan memory adapter, yang hanya berlaku untuk skenario single worker.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. Dapat diganti dengan null adapter untuk mengurangi konsumsi saat fungsi room tidak diperlukan.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### Menyesuaikan SocketID (`sid`)

SocketID default menggunakan format `ServerID#FD`, yang dapat beradaptasi dengan skenario terdistribusi.

1. Dapat diganti untuk langsung menggunakan Fd.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. Dapat juga diganti dengan SessionID.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### Metode Event Dispatching Lainnya

1. Anda dapat mendaftarkan event secara manual tanpa menggunakan annotation.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\Socket;
use Hyperf\WebSocketServer\Sender;

class WebSocketController extends BaseNamespace
{
    public function __construct(Sender $sender, SidProviderInterface $sidProvider) {
        parent::__construct($sender,$sidProvider);
        $this->on('event', [$this, 'echo']);
    }

    public function echo(Socket $socket, $data)
    {
        $socket->emit('event', $data);
    }
}
```

2. Anda dapat menambahkan annotation `#[Event]` pada controller dan menggunakan nama method sebagai nama event untuk dispatch. Pada saat ini, perlu diperhatikan bahwa method public lainnya mungkin konflik dengan nama event.

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

#[SocketIONamespace("/")]
#[Event]
class WebSocketController extends BaseNamespace
{
    public function echo(Socket $socket, $data)
    {
        $socket->emit('event', $data);
    }
}
```

### Mengubah Parameter Dasar `SocketIO`

Parameter default framework:

|         Konfigurasi         | Tipe  | Nilai Default |
| :--------------------: | :---: | :----: |
|      $pingTimeout      |  int  |  100   |
|     $pingInterval      |  int  | 10000  |
| $clientCallbackTimeout |  int  | 10000  |

Terkadang, karena banyaknya pesan yang di-push atau lag jaringan, jika Anda tidak bisa mengembalikan `PONG` tepat waktu dalam 100ms, koneksi akan terputus. Dalam kasus ini, kita dapat menulis ulang dengan cara berikut:

```php
<?php

declare(strict_types=1);

namespace App\Kernel;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\SocketIOServer\Parser\Decoder;
use Hyperf\SocketIOServer\Parser\Encoder;
use Hyperf\SocketIOServer\SidProvider\SidProviderInterface;
use Hyperf\SocketIOServer\SocketIO;
use Hyperf\WebSocketServer\Sender;
use Psr\Container\ContainerInterface;

class SocketIOFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $io = new SocketIO(
            $container->get(StdoutLoggerInterface::class),
            $container->get(Sender::class),
            $container->get(Decoder::class),
            $container->get(Encoder::class),
            $container->get(SidProviderInterface::class)
        );

        // Menimpa parameter pingTimeout
        $io->setPingTimeout(10000);

        return $io;
    }
}
```

Kemudian tambahkan mapping yang sesuai di `dependencies.php`.

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Auth Authentication

Anda dapat menggunakan middleware untuk mencegat WebSocket handshake dan mengimplementasikan fungsionalitas autentikasi, sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WebSocketAuthMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Pseudocode, mencegat handshake request melalui method isAuth dan implementasi pengecekan izin
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }

        return $handler->handle($request);
    }
}
```

Dan konfigurasikan middleware di atas ke WebSocket Server yang sesuai.

### Mendapatkan Raw Request Object

Setelah koneksi terbentuk, terkadang Anda perlu mendapatkan informasi client IP, Cookie, dan informasi request lainnya. Objek request asli telah disimpan di [connection context](id/websocket-server.md#connection-context), dan Anda bisa mendapatkannya di event callback dengan cara berikut:

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Konfigurasi Nginx Proxy

Menggunakan `Nginx` untuk reverse proxy `Socket.io` sedikit berbeda dengan `WebSocket`.
```nginx
server {
    location ^~/socket.io/ {
        # Melakukan proxy akses ke server nyata
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```
