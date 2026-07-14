# WebSocket server

O Hyperf fornece um encapsulamento do WebSocket Server. Uma aplicação WebSocket pode ser rapidamente construída com base em [hyperf/websocket-server](https://github.com/hyperf/websocket-server).

## Instalação

```bash
composer require hyperf/websocket-server
```

## Configurar o Server

Modifique `config/autoload/server.php` e adicione a seguinte configuração.

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

## Configurar o Router

> Por enquanto, apenas a forma de arquivo de configuração é suportada. A forma via annotation virá em breve.

No arquivo `config/routes.php`, adicione a configuração de rotas do Server correspondente ao `ws`, onde `ws` é o `name` do WebSocket Server em `config/autoload/server.php`.


```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## Configurar Middleware

No arquivo `config/autoload/middlewares.php`, adicione a configuração de middleware do Server correspondente ao `ws`, onde `ws` é o `name` do WebSocket Server em `config/autoload/server.php`.


```php
<?php

return [
    'ws' => [
        yourMiddleware::class
    ]
];
```

## Criar o controller correspondente

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

Inicie o Server, então você poderá ver que um WebSocket Server foi iniciado e está escutando na porta 9502. Você pode então usar qualquer WebSocket Client para se comunicar com esse WebSocket Server.

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

!> Quando escutamos simultaneamente a porta 9501 do HTTP Server e a porta 9502 do WebSocket Server, o WebSocket Client pode se conectar ao WebSocket Server através das duas portas 9501 e 9502, ou seja, conectar-se em `ws://0.0.0.0:9501` e `ws:/ /0.0.0.0:9502` funciona em ambos os casos.

Como o `Swoole\WebSocket\Server` herda de `Swoole\Http\Server`, você pode usar HTTP para realizar todos os pushes de WebSocket. Para mais detalhes, consulte o callback de `onRequest` na [documentação do Swoole](https://wiki.swoole.com/#/websocket_server?id=websocketserver)

Se você precisar desabilitar isso, pode adicionar o item de configuração `open_websocket_protocol` ao serviço `http` no arquivo `config/autoload/server.php`.


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

Os callbacks onOpen, onMessage e onClose do WebSocket não são disparados na mesma coroutine, portanto não é possível usar diretamente as informações armazenadas no context. O **Connected Context** é fornecido pelo componente WebSocket Server, e a API é a mesma do context de coroutine.

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

## Configuração de múltiplos Servers

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

Quando você quiser encerrar uma conexão `WebSocket` dentro de um serviço `HTTP`, você pode usar `Hyperf\WebSocketServer\Sender`.

O `Sender` verifica se o `fd` está sendo mantido pelo `Worker` atual; caso esteja, envia a mensagem diretamente, caso contrário, envia a mensagem para todos os outros `Worker`s através de `PipeMessage`. Os demais `Worker`s farão a mesma verificação mencionada acima.

O `Sender` suporta `push` e `disconnect`.

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


## Tratando requisições Http no WebSocket Server

Além de separar os serviços HTTP e WebSocket através de portas, também podemos escutar requisições HTTP no WebSocket.

Como os itens de configuração `server.servers.*.callbacks` são todos singletons，precisamos definir um novo singleton em `dependencies`.

```php
<?php
return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
];
```

Depois modifique a configuração de `callbacks` no nosso serviço `WebSocket`. A seguir, as configurações irrelevantes foram omitidas

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

Por fim, podemos adicionar rotas `HTTP` no `ws`.
