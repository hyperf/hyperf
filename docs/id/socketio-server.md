# Layanan Socket.io

Socket.io adalah protokol dan framework komunikasi real-time pada layer
aplikasi yang sangat populer yang dapat dengan mudah mengimplementasikan
response, pengelompokan (grouping), dan penyiaran (broadcasting). Paket
[hyperf/socketio-server](https://github.com/hyperf/socketio-server) mendukung
protokol transmisi WebSocket milik Socket.io.

## Instalasi

```bash
composer require hyperf/socketio-server
```

Komponen [hyperf/socketio-server](https://github.com/hyperf/socketio-server)
diimplementasikan berdasarkan WebSocket, jadi Anda harus memastikan bahwa
konfigurasi `WebSocket service` telah ditambahkan.

```php
// config/autoload/server.php
[
    'name' =>'socket-io',
    'type' => Server::SERVER_WEBSOCKET,
    'host' => '0.0.0.0',
    'port' => 9502,
    'sock_type' => SWOOLE_SOCK_TCP,
    'callbacks' => [
        Event::ON_HAND_SHAKE => [Hyperf\WebSocketServer\Server::class,'onHandShake'],
        Event::ON_MESSAGE => [Hyperf\WebSocketServer\Server::class,'onMessage'],
        Event::ON_CLOSE => [Hyperf\WebSocketServer\Server::class,'onClose'],
    ],
],
```

## Memulai Cepat

### Server

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
        // response
        return'Event Received: '. $data;
    }

    /**
     * @param string $data
     */
    #[Event("join-room")]
    public function onJoinRoom(Socket $socket, $data)
    {
        // Add the current user to the room
        $socket->join($data);
        // Push to other users in the room (excluding the current user)
        $socket->to($data)->emit('event', $socket->getSid(). "has joined {$data}");
        // Broadcast to everyone in the room (including the current user)
        $this->emit('event','There are '. count($socket->getAdapter()->clients($data)). "players in {$data}");
    }

    /**
     * @param string $data
     */
    #[Event("say")]
    public function onSay(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid(). "say: {$data['message']}");
    }
}

```

> Setiap socket secara otomatis akan bergabung ke dalam room yang dinamai
> sesuai dengan `sid` miliknya sendiri (`$socket->getSid()`), dan mengirimkan
> pesan chat privat ke `sid` yang bersangkutan.

> Framework akan secara otomatis memicu event `connect` dan `disconnect`.

### Client

Karena server hanya mengimplementasikan komunikasi WebSocket, client harus
menambahkan `{transports:["websocket"]}`.

```html
<script src="https://cdn.bootcdn.net/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
<script>
    var socket = io('ws://127.0.0.1:9502', {transports: ["websocket"] });
    socket.on('connect', data => {
        socket.emit('event','hello, hyperf', console.log);
        socket.emit('join-room','room1', console.log);
        setInterval(function () {
            socket.emit('say','{"room":"room1", "message":"Hello Hyperf."}');
        }, 1000);
    });
    socket.on('event', console.log);
</script>
```

## Daftar API

### Socket API

Kirim push ke target Socket melalui SocketAPI, atau berbicara di dalam room
sebagai target Socket. Harus digunakan di dalam callback event.

```php
<?php
#[Event("SomeEvent")]
function onSomeEvent(\Hyperf\SocketIOServer\Socket $socket){

  // sending to the client
  // Push the hello event to the connection
  $socket->emit('hello','can you hear me?', 1, 2,'abc');

  // sending to all clients except sender
  // Push the broadcast event to all connections, but not the current connection.
  $socket->broadcast->emit('broadcast','hello friends!');

  // sending to all clients in'game' room except sender
  // Push the nice game event to all connections in the game room, but not including the current connection.
  $socket->to('game')->emit('nice game', "let's play a game");

  // sending to all clients in'game1' and/or in'game2' room, except sender
  // Take the union and push the nice game event to all the connections in the game1 room and game2 room, but not including the current connection.
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // WARNING: `$socket->to($socket->getSid())->emit()` will NOT work, as it will send to everyone in the room
  // named `$socket->getSid()` but the sender. Please use the classic `$socket->emit()` instead.
  // Note: Do not add to() when you push yourself, because $socket->to() always excludes yourself. Just $socket->emit() directly.

  // sending with acknowledgement
  // Send information, and wait and receive client response.
  $reply = $socket->emit('question','do you think so?')->reply();

  // sending without compression
  // Push without compression
  $socket->compress(false)->emit('uncompressed', "that's rough");
}
```

### Global API

Dapatkan singleton SocketIO secara langsung dari container. Singleton ini
dapat melakukan broadcast ke seluruh dunia atau menentukan room atau komunikasi
personal. Ketika tidak ada namespace yang ditentukan, space '/' digunakan
secara default.

```php
<?php
$io = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

// sending to all clients in'game' room, including sender
// Push the bigger-announcement event to all connections in the game room.
$io->in('game')->emit('big-announcement','the game will start soon');

// sending to all clients in namespace'myNamespace', including sender
// Push the bigger-announcement event to all connections under the /myNamespace namespace
$io->of('/myNamespace')->emit('bigger-announcement','the tournament will start soon');

// sending to a specific room in a specific namespace, including sender
// Push event events to all connections in the room room under the /myNamespace namespace
$io->of('/myNamespace')->to('room')->emit('event','message');

// sending to individual socketid (private message)
// Single push to socketId
$io->to('socketId')->emit('hey','I just met you');

// sending to all clients on this node (when using multiple nodes)
// Push to all connections of this machine
$io->local->emit('hi','my lovely babies');

// sending to all connected clients
// Push to all connections
$io->emit('an event sent to all connected clients');
```

### Namespace API

Sama seperti global API, kecuali bahwa namespace-nya telah dibatasi.

```php
// The following pseudocode is equivalent
$foo->emit();
$io->of('/foo')->emit();

/**
 * Use within the class is also equivalent
 */
#[SocketIONamespace("/foo")]
class FooNamespace extends BaseNamespace {
    public function onEvent(){
        $this->emit();
        $this->io->of('/foo')->emit();
    }
}
```

## Penggunaan Lanjutan

### Mengatur Namespace Socket.io

Socket.io mengimplementasikan multiplexing melalui namespace kustom. (Catatan:
Ini bukan namespace PHP)

1. Controller dapat dipetakan ke namespace xxx melalui `#[SocketIONamespace("/xxx")]`,

2. Dapat juga didaftarkan melalui `SocketIORouter`

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx', WebSocketController::class);
```

### Memulai Session

Instal dan konfigurasi komponen [hyperf/session](https://github.com/hyperf/session)
beserta middleware yang sesuai, lalu beralih ke SocketIO melalui `SessionAspect`
untuk menggunakan Session.

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> Swoole 4.4.17 ke bawah hanya dapat membaca cookie yang dibuat oleh HTTP,
> Swoole 4.4.18 ke atas dapat membuat cookie selama handshake WebSocket.

### Menyesuaikan Adapter Room

Fungsi room default diimplementasikan melalui adapter Redis, yang dapat
beradaptasi dengan skenario multi-proses bahkan terdistribusi.

1. Dapat diganti dengan adapter memori (MemoryAdapter), yang hanya cocok untuk
skenario single worker.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. Dapat diganti dengan adapter kosong (NullAdapter) untuk mengurangi
penggunaan sumber daya ketika fungsi room tidak diperlukan.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### Menyesuaikan SocketID (`sid`)

SocketID default menggunakan format `ServerID#FD`, yang dapat disesuaikan
dengan skenario terdistribusi.

1. Dapat diganti langsung menggunakan Fd.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. Dapat juga diganti menggunakan SessionID.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### Metode Distribusi Event Lainnya

1. Mendaftarkan event secara manual tanpa menggunakan anotasi.

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
        $this->on('event', [$this,'echo']);
    }

    public function echo(Socket $socket, $data)
    {
        $socket->emit('event', $data);
    }
}
```

2. Anda dapat menambahkan anotasi `#[Event]` pada controller, dan menggunakan
nama method sebagai nama event yang akan didistribusikan. Pada saat ini, perlu
diperhatikan bahwa method public lainnya mungkin berbenturan dengan nama event.

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

### Mengubah Konfigurasi Dasar `SocketIO`

Parameter konfigurasi default:

| Konfigurasi | Tipe | Nilai Default |
|:----------------------:|:----:|:-------------:|
| $pingTimeout | int | 100 |
| $pingInterval | int | 10000 |
| $clientCallbackTimeout | int | 10000 |

Terkadang, karena jumlah pesan yang besar atau karena jaringan yang buruk,
tidak mungkin untuk mengembalikan `PONG` dalam waktu 100ms, yang akan
menyebabkan koneksi terputus. Jika hal ini menjadi masalah, pengaturan dapat
dikonfigurasi seperti contoh di bawah ini:

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

        // rewrite the pingTimeout parameter
        $io->setPingTimeout(10000);

        return $io;
    }
}

```

Kemudian tambahkan pemetaan yang sesuai di `dependencies.php`.

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Otentikasi Auth

Anda dapat menggunakan middleware to mengintersepsi handshake WebSocket dan
mengimplementasikan fungsi otentikasi sebagai berikut:

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
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Pseudo code, intercept the handshake request through the isAuth method and implement permission checking
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }

        return $handler->handle($request);
    }
}
```

Dan konfigurasikan middleware di atas ke WebSocket Server yang sesuai.

### Mendapatkan Objek Request Asli

Terkadang diperlukan untuk mendapatkan informasi request seperti IP client dan
Cookie setelah koneksi terjalin. Objek request asli disimpan di dalam
[connection context](id/websocket-server.md#connection-context) dan Anda dapat
mendapatkannya di dalam callback event dengan cara berikut:

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Konfigurasi Proxy Nginx

Ada sedikit perbedaan antara menggunakan reverse proxy `Nginx` untuk `Socket.io`
dan `WebSocket`:

```nginx
server {
    location ^~/socket.io/ {
        # Jalankan proxy untuk mengakses server asli
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```
