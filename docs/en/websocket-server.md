# WebSocket Server

Hyperf provides a wrapper for the WebSocket Server, allowing you to quickly build a WebSocket application based on the [hyperf/websocket-server](https://github.com/hyperf/websocket-server) component.

## Installation

```bash
composer require hyperf/websocket-server
```

## Configure Server

Modify `config/autoload/server.php` and add the following configuration:

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

## Configure Routes

> Currently, only the configuration file mode is supported for routing; annotation mode will be provided in the future.

In the `config/routes.php` file, add the routing configuration for the corresponding `ws` Server. The `ws` value here depends on the `name` value of the WebSocket Server you configured in `config/autoload/server.php`.

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## Configure Middleware

In the `config/autoload/middlewares.php` file, add the global middleware configuration for the corresponding `ws` Server. The `ws` value here depends on the `name` value of the WebSocket Server you configured in `config/autoload/server.php`.

```php
<?php

return [
    'ws' => [
        yourMiddleware::class
    ]
];
```

## Create Controller

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
            // If using a coroutine Server, you need to handle it manually and return a PONG frame after identifying a PING frame.
            // For asynchronous style Servers, you can handle it directly via Swoole configuration. For details, please refer to https://wiki.swoole.com/#/websocket_server?id=open_websocket_ping_frame
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

Next, start the Server, and you will see that a WebSocket Server has been started and is listening on port 9502. You can then use various WebSocket Clients to connect and transmit data.

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

!> When we listen to both the 9501 port of the HTTP Server and the 9502 port of the WebSocket Server, the WebSocket Client can connect to the WebSocket Server via both ports, i.e., connecting to `ws://0.0.0.0:9501` and `ws://0.0.0.0:9502` will both succeed.

Because `Swoole\WebSocket\Server` inherits from `Swoole\Http\Server`, you can use HTTP to trigger all WebSocket pushes. For more details, you can view the `onRequest` callback section of the [Swoole documentation](https://wiki.swoole.com/#/websocket_server?id=websocketserver).

If you need to disable it, you can modify the `config/autoload/server.php` file and add the `open_websocket_protocol` configuration item to the `http` service.

```php
<?php
return [
    // Other configurations of this file are omitted
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

The onOpen, onMessage, and onClose callbacks of the WebSocket service are not triggered in the same coroutine, so you cannot directly use the coroutine context to store state information. The WebSocket Server component provides a **connection-level** context, and the API is exactly the same as the coroutine context.

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

## Multiple Server Configuration

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
    # Forward to multiple ws servers
    proxy_pass http://io_nodes;
  }
}
```

## Message Sender

When we want to close a `WebSocket` connection in an `HTTP` service, we can directly use `Hyperf\WebSocketServer\Sender`.

The `Sender` determines whether the `fd` is held by the current `Worker`. If it is, it will send the data directly; if not, it will send it via `PipeMessage` to all `Workers` except itself, and then other `Workers` will make a determination. If it is the `fd` held by itself, it will send the corresponding data to the client.

The `Sender` supports two APIs: `push` and `disconnect`, as follows:

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

## Handling HTTP Requests in WebSocket Service

Besides separating the HTTP service and WebSocket service through ports, we can also listen for HTTP requests in WebSocket.

Because the configuration items in `server.servers.*.callbacks` are singletons, we need to configure a separate instance in `dependencies`.

```php
<?php
return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
];
```

Then modify the `callbacks` configuration in our `WebSocket` service. The irrelevant configuration is hidden below.

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

Finally, we can add `HTTP` routes in `ws`.
