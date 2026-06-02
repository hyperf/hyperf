# Socket.io Server

Socket.io is a very popular application-layer real-time communication protocol and framework, which can easily implement response, grouping, and broadcasting. `hyperf/socketio-server` supports the WebSocket transport protocol of Socket.io.

## Installation

```bash
composer require hyperf/socketio-server
```

The `hyperf/socketio-server` component is implemented based on WebSocket. Please ensure that the `WebSocket Server` configuration has been added to the server.

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
        // Join the current user to the room
        $socket->join($data);
        // Push to other users in the room (excluding the current user)
        $socket->to($data)->emit('event', $socket->getSid() . "has joined {$data}");
        // Broadcast to everyone in the room (including the current user)
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

> Each socket automatically joins a room named after its own `sid` (`$socket->getSid()`). To send a private message, just push it to the corresponding `sid`.

> The framework automatically triggers two events: `connect` and `disconnect`.

### Client Side

Since the server-side only implements WebSocket communication, the client-side needs to add `{transports:["websocket"]}`.

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

## API List

### Socket API

Use the Socket API to push to the target Socket, or speak in a room as the target Socket. It needs to be used in the event callback.

```php
<?php
#[Event("SomeEvent")]
function onSomeEvent(\Hyperf\SocketIOServer\Socket $socket){

  // sending to the client
  // Push 'hello' event to the connection
  $socket->emit('hello', 'can you hear me?', 1, 2, 'abc');

  // sending to all clients except sender
  // Push 'broadcast' event to all connections, but excluding the current connection.
  $socket->broadcast->emit('broadcast', 'hello friends!');

  // sending to all clients in 'game' room except sender
  // Push 'nice game' event to all connections in the 'game' room, but excluding the current connection.
  $socket->to('game')->emit('nice game', "let's play a game");

  // sending to all clients in 'game1' and/or in 'game2' room, except sender
  // Push 'nice game' event to all connections in 'game1' room and 'game2' room, but excluding the current connection.
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // WARNING: `$socket->to($socket->getSid())->emit()` will NOT work, as it will send to everyone in the room
  // named `$socket->getSid()` but the sender. Please use the classic `$socket->emit()` instead.
  // Note: Do not add `to` when pushing to yourself, because `$socket->to()` always excludes yourself. Just use `$socket->emit()` directly.

  // sending with acknowledgement
  // Send message and wait for client response.
  $reply = $socket->emit('question', 'do you think so?')->reply();

  // sending without compression
  // No compression push
  $socket->compress(false)->emit('uncompressed', "that's rough");
}
```

### Global API

Get the SocketIO singleton directly from the container. The singleton can be used for global broadcasting or communication with a specific room or individual. When no namespace is specified, the '/' space is used by default.

```php
<?php
$io = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

// sending to all clients in 'game' room, including sender
// Push 'big-announcement' event to all connections in 'game' room.
$io->in('game')->emit('big-announcement', 'the game will start soon');

// sending to all clients in namespace 'myNamespace', including sender
// Push 'bigger-announcement' event to all connections in '/myNamespace' namespace
$io->of('/myNamespace')->emit('bigger-announcement', 'the tournament will start soon');

// sending to a specific room in a specific namespace, including sender
// Push 'event' event to all connections in 'room' in '/myNamespace' namespace
$io->of('/myNamespace')->to('room')->emit('event', 'message');

// sending to individual socketid (private message)
// Push point-to-point to socketId
$io->to('socketId')->emit('hey', 'I just met you');

// sending to all clients on this node (when using multiple nodes)
// Push to all connections on the local node
$io->local->emit('hi', 'my lovely babies');

// sending to all connected clients
// Push to all connections
$io->emit('an event sent to all connected clients');
```

### Namespace API

Same as the Global API, but limited to the namespace.
```php
// The following pseudocodes are equivalent
$foo->emit();
$io->of('/foo')->emit();

/**
 * Using inside a class is also equivalent
 */
#[SocketIONamespace("/foo")]
class FooNamespace extends BaseNamespace {
    public function onEvent(){
        $this->emit(); 
        $this->io->of('/foo')->emit();
    }
}
```

## Advanced Tutorials

### Set Socket.io Namespace

Socket.io achieves multiplexing through custom namespaces. (Note: Not PHP namespaces)

1. You can map the controller to the 'xxx' namespace using `#[SocketIONamespace("/xxx")]`.

2. Or add it in the route:

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx' , WebSocketController::class);
```

### Enable Session

Install and configure the `hyperf/session` component and its corresponding middleware, then cut into SocketIO using `SessionAspect` to use Session.

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> Swoole 4.4.17 and below versions can only read cookies created by HTTP. Swoole 4.4.18 and above versions can create cookies during WebSocket handshake.

### Adjust Room Adapter

The default room function is implemented through the Redis adapter, which can adapt to multi-process and even distributed scenarios.

1. It can be replaced with a memory adapter, which is only applicable to single worker scenarios.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. It can be replaced with a null adapter to reduce consumption when room functionality is not needed.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### Adjust SocketID (`sid`)

The default SocketID uses the `ServerID#FD` format, which can adapt to distributed scenarios.

1. It can be replaced to directly use Fd.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. It can also be replaced with SessionID.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### Other Event Dispatching Methods

1. You can manually register events without using annotations.

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

2. You can add the `#[Event]` annotation on the controller and use the method name as the event name to dispatch. At this time, you should note that other public methods may conflict with the event name.

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

### Modify `SocketIO` Basic Parameters

Framework default parameters:

|          Configuration          | Type  | Default Value |
| :--------------------: | :---: | :----: |
|      $pingTimeout      |  int  |  100   |
|     $pingInterval      |  int  | 10000  |
| $clientCallbackTimeout |  int  | 10000  |

Sometimes, due to a large number of pushed messages or network lag, if you cannot return `PONG` in time within 100ms, the connection will be disconnected. In this case, we can rewrite it in the following way:

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

        // Rewrite pingTimeout parameter
        $io->setPingTimeout(10000);

        return $io;
    }
}
```

Then add the corresponding mapping in `dependencies.php`.

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Auth Authentication

You can use middleware to intercept the WebSocket handshake and implement authentication functionality, as follows:

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
        // Pseudocode, intercept the handshake request through the isAuth method and implement permission checks
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }

        return $handler->handle($request);
    }
}
```

And configure the above middleware into the corresponding WebSocket Server.

### Get Raw Request Object

After the connection is established, sometimes you need to get the client IP, Cookie, and other request information. The original request object has been preserved in the [connection context](websocket-server.md#connection-context), and you can obtain it in the event callback in the following way:

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Nginx Proxy Configuration

Using `Nginx` to reverse proxy `Socket.io` is slightly different from `WebSocket`.
```nginx
server {
    location ^~/socket.io/ {
        # Perform proxy access to the real server
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```
