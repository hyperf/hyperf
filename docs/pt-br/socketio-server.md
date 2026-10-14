# Serviço Socket.io

Socket.io é um protocolo e framework muito popular de comunicação em tempo real na camada de aplicação, que permite implementar facilmente respostas, agrupamento e broadcast. O pacote [hyperf/socketio-server](https://github.com/hyperf/socketio-server) oferece suporte ao protocolo de transporte WebSocket do Socket.io.

## Instalação

```bash
composer require hyperf/socketio-server
```

O componente [hyperf/socketio-server](https://github.com/hyperf/socketio-server) é implementado com base em WebSocket, então você precisa garantir que a configuração do `serviço WebSocket` tenha sido adicionada.

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

### Servidor

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
        // resposta
        return'Event Received: '. $data;
    }

    /**
     * @param string $data
     */
    #[Event("join-room")]
    public function onJoinRoom(Socket $socket, $data)
    {
        // Adiciona o usuário atual à sala
        $socket->join($data);
        // Envia para outros usuários na sala (excluindo o usuário atual)
        $socket->to($data)->emit('event', $socket->getSid(). "has joined {$data}");
        // Faz broadcast para todos na sala (incluindo o usuário atual)
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

> Cada socket entra automaticamente na sala cujo nome é o seu próprio `sid` (`$socket->getSid()`), e envia mensagens privadas para o `sid` correspondente.

> O framework disparará automaticamente os eventos `connect` e `disconnect`.

### Cliente

Como o servidor implementa apenas comunicação WebSocket, o cliente precisa adicionar `{transports:["websocket"]}`.

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

### API do Socket

Envie para o Socket alvo via SocketAPI ou fale na sala como o Socket alvo. Deve ser usado dentro de callbacks de eventos.

```php
<?php
#[Event("SomeEvent")]
function onSomeEvent(\Hyperf\SocketIOServer\Socket $socket){

  // enviando para o cliente
  // Envia o evento hello para a conexão
  $socket->emit('hello','can you hear me?', 1, 2,'abc');

  // enviando para todos os clientes, exceto o remetente
  // Envia o evento broadcast para todas as conexões, exceto a conexão atual.
  $socket->broadcast->emit('broadcast','hello friends!');

  // enviando para todos os clientes na sala 'game', exceto o remetente
  // Envia o evento nice game para todas as conexões na sala game, sem incluir a conexão atual.
  $socket->to('game')->emit('nice game', "let's play a game");

  // enviando para todos os clientes nas salas 'game1' e/ou 'game2', exceto o remetente
  // Faz a união e envia o evento nice game para todas as conexões nas salas game1 e game2, sem incluir a conexão atual.
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // AVISO: `$socket->to($socket->getSid())->emit()` NÃO funciona como esperado, pois enviará para todos na sala
  // chamada `$socket->getSid()`, exceto o remetente. Use o clássico `$socket->emit()` em vez disso.
  // Nota: não adicione to() quando você enviar para si mesmo, porque `$socket->to()` sempre exclui você. Use `$socket->emit()` diretamente.

  // enviando com acknowledgement
  // Envia a informação e espera receber a resposta do cliente.
  $reply = $socket->emit('question','do you think so?')->reply();

  // enviando sem compressão
  // Envia sem compressão
  $socket->compress(false)->emit('uncompressed', "that's rough");
}
```
### API global

Obtenha o singleton SocketIO diretamente do container. Esse singleton pode fazer broadcast para todos, para uma sala específica ou para comunicação privada. Quando nenhum namespace é especificado, o namespace `'/'` é usado por padrão.

```php
<?php
$io = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

// enviando para todos os clientes na sala 'game', incluindo o remetente
// Envia o evento bigger-announcement para todas as conexões na sala game.
$io->in('game')->emit('big-announcement','the game will start soon');

// enviando para todos os clientes no namespace 'myNamespace', incluindo o remetente
// Envia o evento bigger-announcement para todas as conexões no namespace /myNamespace
$io->of('/myNamespace')->emit('bigger-announcement','the tournament will start soon');

// enviando para uma sala específica em um namespace específico, incluindo o remetente
// Envia o evento event para todas as conexões na sala room dentro do namespace /myNamespace
$io->of('/myNamespace')->to('room')->emit('event','message');

// enviando para um socketid individual (mensagem privada)
// Envio individual para socketId
$io->to('socketId')->emit('hey','I just met you');

// enviando para todos os clientes neste nó (ao usar múltiplos nós)
// Envia para todas as conexões desta máquina
$io->local->emit('hi','my lovely babies');

// enviando para todos os clientes conectados
// Envia para todas as conexões
$io->emit('an event sent to all connected clients');
```

### API de namespace

Como a API global, mas com o namespace restrito.
```php
// O pseudocódigo a seguir é equivalente
$foo->emit();
$io->of('/foo')->emit();

/**
 * O uso dentro da classe também é equivalente
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

### Definir namespace do Socket.io

O Socket.io implementa multiplexação através de namespaces customizados. (Nota: não é um namespace PHP.)

1. O controller pode ser mapeado para o namespace xxx via `#[SocketIONamespace(\"/xxx\")]`,

2. Também pode ser registrado via `SocketIORouter`

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx', WebSocketController::class);
```

### Iniciar session

Instale e configure o componente [hyperf/session](https://github.com/hyperf/session) e seu middleware correspondente e, em seguida, faça a integração com o SocketIO via `SessionAspect` para usar session.

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> Swoole 4.4.17 e anteriores só conseguem ler cookies criados via HTTP; no Swoole 4.4.18 e superiores é possível criar cookies durante o handshake do WebSocket

### Ajustar o adapter de salas

Por padrão, a funcionalidade de salas é implementada através do adapter Redis, que funciona em cenários multiprocessos e até distribuídos.

1. Pode ser substituído por um adapter em memória, adequado apenas para cenários com um único worker.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. Pode ser substituído por um adapter vazio para reduzir o uso de recursos quando a funcionalidade de salas não é necessária.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### Ajustar SocketID (`sid`)

O SocketID padrão usa o formato `ServerID#FD`, que se adapta a cenários distribuídos.

1. Pode ser substituído pelo próprio Fd diretamente.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. Também pode ser substituído por SessionID.

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### Outras formas de distribuir eventos

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

2. Você pode adicionar a annotation `#[Event]` no controller e usar o nome do método como nome do evento. Nesse caso, observe que outros métodos públicos podem conflitar com o nome do evento.

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

Parâmetros padrão de configuração:

|      Configuração      | Tipo | Valor padrão |
|:----------------------:|:----:|:-------------:|
|      $pingTimeout      |  int |      100      |
|      $pingInterval     |  int |     10000     |
| $clientCallbackTimeout |  int |     10000     |

Às vezes, devido a um grande volume de mensagens ou por causa de uma rede ruim, não é possível responder com `PONG` em 100ms, o que faz com que a conexão seja encerrada. Se isso virar um problema, você pode configurar como no exemplo abaixo:

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

        // reescreve o parâmetro pingTimeout
        $io->setPingTimeout(10000);

        return $io;
    }
}

```

Depois, adicione o mapeamento correspondente em `dependencies.php`.

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Autenticação (Auth)

Você pode usar um middleware para interceptar o handshake do WebSocket e implementar autenticação da seguinte forma:

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
        // Pseudocódigo: intercepta o handshake via isAuth e implementa verificação de permissão
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }

        return $handler->handle($request);
    }
}
```

E configure o middleware acima no WebSocket Server correspondente.

### Obter o objeto request original

Às vezes é necessário obter informações do request, como IP do cliente e Cookie, depois que a conexão for estabelecida. O objeto request original fica no [contexto da conexão](pt-br/websocket-server.md#connection-context) e você pode obtê-lo no callback do evento da seguinte forma:

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Configuração de proxy do Nginx

Existe uma pequena diferença entre usar proxy reverso `Nginx` para `Socket.io` e para `WebSocket`
```nginx
server {
    location ^~/socket.io/ {
        # Executa proxy para acessar o servidor real
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

