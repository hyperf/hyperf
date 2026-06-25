# Servidor WebSocket

O Hyperf fornece um encapsulamento para WebSocket Server. Uma aplicação WebSocket pode ser construída rapidamente com base no [hyperf/websocket-server](https://github.com/hyperf/websocket-server).

## Instalação

```bash
composer require hyperf/websocket-server
```

## Configurar servidor

Modifique `config/autoload/server.php` e adicione a configuração a seguir.

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

## Configurar roteamento

> Até o momento, apenas o modo via arquivo de configuração é suportado. O modo via anotações chegará em breve.

No arquivo `config/routes.php`, adicione a configuração de rotas do server correspondente a `ws`, onde `ws` é o `name` do WebSocket Server em `config/autoload/server.php`.

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## Configurar middleware

No arquivo `config/autoload/middlewares.php`, adicione a configuração de middleware do server correspondente a `ws`, onde `ws` é o `name` do WebSocket Server em `config/autoload/server.php`.

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

Inicie o server e você verá um WebSocket Server iniciado, escutando a porta 9502. Então você pode usar qualquer WebSocket Client para se comunicar com este WebSocket Server.

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

!> Quando escutamos a 9501 do HTTP Server e a 9502 do WebSocket Server ao mesmo tempo, o WebSocket Client pode se conectar ao WebSocket Server pelas duas portas 9501 e 9502; isto é: conectar em `ws://0.0.0.0:9501` e `ws:/ /0.0.0.0:9502` funciona em ambos os casos.

Como `Swoole\WebSocket\Server` herda de `Swoole\Http\Server`, você pode usar HTTP para realizar push em WebSocket. Para mais detalhes, consulte o callback `onRequest` na [Swoole Doc](https://wiki.swoole.com/#/websocket_server?id=websocketserver).

Se você precisar desativar isso, pode adicionar o item de configuração `open_websocket_protocol` no serviço `http` no arquivo `config/autoload/server.php`.

```php
<?php
return [
    // Configurações não relacionadas são ignoradas
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

Os callbacks `onOpen`, `onMessage` e `onClose` do WebSocket não são disparados na mesma coroutine, portanto não podem usar diretamente as informações armazenadas no contexto. O componente WebSocket Server fornece um **Connected Context**, e a API é a mesma do contexto de coroutine.

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

## Configuração de múltiplos servidores

```
# /etc/nginx/conf.d/ng_socketio.conf
# múltiplos servidores ws
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
    # Encaminhar para múltiplos servidores ws
    proxy_pass http://io_nodes;
  }
}
```

## Sender

Quando você quiser fechar uma conexão `WebSocket` no serviço `HTTP`, você pode usar `Hyperf\WebSocketServer\Sender`.

O `Sender` verifica se o `fd` está associado ao `Worker` atual; se estiver, ele envia a mensagem diretamente. Caso contrário, ele envia a mensagem para os demais `Worker`s via `PipeMessage`. Os outros `Worker`s farão o mesmo conforme mencionado acima.

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

## Tratar requisições HTTP no Websocket Server

Além de separar serviços HTTP e WebSocket por portas, também podemos escutar requisições HTTP no WebSocket.

Como os itens de configuração `server.servers.*.callbacks` são todos singleton，precisamos definir uma nova config singleton em `dependencies`.

```php
<?php
return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
];
```

Então modifique a configuração de `callbacks` no nosso serviço `WebSocket`. A seguir omitimos configurações irrelevantes.

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

Por fim, podemos adicionar roteamento `HTTP` em `ws`.

