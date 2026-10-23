# Socket.io service

Socket.io é um protocolo e framework de comunicação em tempo real na camada de aplicação muito popular, que permite implementar facilmente respostas, agrupamento e broadcasting. O pacote [hyperf/socketio-server](https://github.com/hyperf/socketio-server) suporta o protocolo de transmissão WebSocket do Socket.io.

## Instalação

```bash
composer require hyperf/socketio-server
```

O componente [hyperf/socketio-server](https://github.com/hyperf/socketio-server) é implementado com base em WebSocket, então você precisa garantir que a configuração do `serviço WebSocket` já tenha sido adicionada.

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

## Início rápido

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

> Cada socket entra automaticamente na room com o nome do seu próprio `sid` (`$socket->getSid()`), e envia mensagens de chat privado para o `sid` correspondente.

> O framework dispara automaticamente os eventos `connect` e `disconnect`.

### Client

Como o server implementa apenas comunicação via WebSocket, o client precisa adicionar `{transports:["websocket"]}`.

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

## Lista de APIs

### Socket API

Faz push para o Socket de destino através da SocketAPI, ou fala na room como o Socket de destino. Deve ser usada dentro de callbacks de evento.

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
### API Global

Obtenha o singleton do SocketIO diretamente do container. Esse singleton pode fazer broadcast para todo o mundo ou especificar a room ou comunicação pessoal. Quando nenhum namespace é especificado, o namespace'/' é usado por padrão.

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

### API de Namespace

Assim como a API global, exceto que o namespace já foi restringido.
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

## Uso avançado

### Definir o namespace do Socket.io

O Socket.io implementa multiplexação através de namespaces customizados. (Nota: não é um namespace do PHP)

1. O controller pode ser mapeado para o namespace xxx através de `#[SocketIONamespace("/xxx")]`,

2. Também pode ser registrado através do `SocketIORouter`

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx', WebSocketController::class);
```

### Iniciar sessão

Instale e configure o componente [hyperf/session](https://github.com/hyperf/session) e seu middleware correspondente, e então altere para SocketIO através de `SessionAspect` para usar a Session.

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> No Swoole 4.4.17 e versões anteriores só é possível ler cookies criados pelo HTTP; no Swoole 4.4.18 e versões posteriores é possível criar cookies durante o handshake do WebSocket

### Ajustar o adapter da room

A funcionalidade de room padrão é implementada através do adapter Redis, que pode se adaptar a cenários multiprocesso e até distribuídos.

1. Pode ser substituído por um adapter de memória, adequado apenas para cenários de worker único.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. Pode ser substituído por um adapter vazio para reduzir o uso de recursos quando a funcionalidade de room não é necessária.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### Ajustar o SocketID (`sid`)

O SocketID padrão usa o formato `ServerID#FD`, que pode se adaptar a cenários distribuídos.

1. Pode ser substituído diretamente pelo Fd.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. Também pode ser substituído pelo SessionID.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### Outros métodos de distribuição de eventos

1. Registrar eventos manualmente sem usar annotations.

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

2. Você pode adicionar a annotation `#[Event]` no controller, e usar o nome do método como o nome do evento a ser distribuído. Nesse caso, deve-se observar que outros métodos públicos podem conflitar com o nome do evento.

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

### Modificar a configuração básica do `SocketIO`

Parâmetros de configuração padrão:

|      Configuração     | Tipo | Valor padrão |
|:----------------------:|:----:|:-------------:|
|      $pingTimeout      |  int |      100      |
|      $pingInterval     |  int |     10000     |
| $clientCallbackTimeout |  int |     10000     |

Às vezes, devido a uma grande quantidade de mensagens ou a uma rede instável, não é possível retornar um `PONG` dentro de 100ms, o que causará a desconexão. Se isso se tornar um problema, a configuração pode ser ajustada como no exemplo abaixo:

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

Depois adicione o mapeamento correspondente em `dependencies.php`.

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Autenticação Auth

Você pode usar middleware para interceptar o handshake do WebSocket e implementar a função de autenticação da seguinte forma:

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

E configure o middleware acima para o WebSocket Server correspondente.

### Obter o objeto de requisição original

Às vezes é necessário obter informações da requisição, como IP do client e Cookie, após a conexão ter sido estabelecida. O objeto de requisição original é mantido no [connection context](pt-br/websocket-server.md#connection-context) e você pode obtê-lo no callback do evento da seguinte forma:

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Configuração de proxy no Nginx

Há uma pequena diferença entre usar o reverse proxy do `Nginx` para `Socket.io` e para `WebSocket`
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
