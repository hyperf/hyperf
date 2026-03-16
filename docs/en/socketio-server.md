# Socket.io service

Socket.io is a very popular application layer real-time communication protocol and framework which can easily implement response, grouping, and broadcasting. The [hyperf/socketio-server](https://github.com/hyperf/socketio-server) package supports Socket.io's WebSocket transmission protocol.

## Installation

```bash
composer require hyperf/socketio-server
```

The [hyperf/socketio-server](https://github.com/hyperf/socketio-server) component is implemented based on WebSocket, so you need to make sure that the `WebSocket service` configuration has been added.

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

## Quick start

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

> Each socket will automatically join the room named after its own `sid` (`$socket->getSid()`), and send private chat messages to the corresponding `sid`.

> The framework will automatically trigger the `connect` and `disconnect` events.

### Client

Since the server only implements WebSocket communication, the client must add `{transports:["websocket"]}`.

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

## API list

### Socket API

Push the target Socket through SocketAPI, or speak in the room as the target Socket. Must be used inside event callbacks.

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

Obtain the SocketIO singleton directly from the container. This singleton can broadcast to the whole world or specify the room or personal communication. When no namespace is specified, the'/' space is used by default.

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

Like the global API, except that the namespace has been restricted.
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

## Advanced usage

### Set Socket.io namespace

Socket.io implements multiplexing through custom namespaces. (Note: It is not a PHP namespace)

1. The controller can be mapped to the xxx namespace through `#[SocketIONamespace("/xxx")]`,

2. Can also be registered through the `SocketIORouter`

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx', WebSocketController::class);
```

### Start session

Install and configure the [hyperf/session](https://github.com/hyperf/session) component and its corresponding middleware, and then switch to SocketIO through `SessionAspect` to use Session.

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> Swoole 4.4.17 and below can only read cookies created by HTTP, Swoole 4.4.18 and above can create cookies during WebSocket handshake

### Adjust the room adapter

The default room function is implemented through the Redis adapter, which can adapt to multi-process and even distributed scenarios.

1. It can be replaced with a memory adapter, which is only suitable for single worker scenarios.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. It can be replaced with an empty adapter to reduce resource usage when the room function is not needed.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### Adjust SocketID (`sid`)

The default SocketID uses the format of `ServerID#FD`, which can be adapted to distributed scenarios.

1. It can be replaced with Fd directly.

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

### Other event distribution methods

1. Manually register events without using annotations.

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

2. You can add `#[Event]` annotation on the controller, and use the method name as the event name to distribute. At this time, it should be noted that other public methods may conflict with the event name.

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

### Modify the basic configuration of `SocketIO`

Default configuration parameters:

|      Configuration     | Type | Default Value |
|:----------------------:|:----:|:-------------:|
|      $pingTimeout      |  int |      100      |
|      $pingInterval     |  int |     10000     |
| $clientCallbackTimeout |  int |     10000     |

Sometimes, due to a large number of messages or because of a poor network, it is impossible to return to `PONG` within 100ms, which will cause the connection to be disconnected. If this becomes an issue, the setting can be configured as shown in the example below:

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

Then add the corresponding mapping in `dependencies.php`.

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Auth authentication

You can use middleware to intercept the WebSocket handshake and implement the authentication function as follows:

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

And configure the above middleware to the corresponding WebSocket Server.

### Get the original request object

It is sometimes necessary to obtain the request information such as client IP and Cookie after to connection has been established. The original request object is kept in [connection context](en/websocket-server.md#connection-context) and you can get it in the event callback in the following way:

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Nginx proxy configuration

There is a slight difference between using `Nginx` reverse proxy for `Socket.io` and `WebSocket`
```nginx
server {
    location ^~/socket.io/ {
        # Execute proxy to access real server
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```
