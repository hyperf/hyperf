# WebSocket 服務

Hyperf 提供了對 WebSocket Server 的封裝，可基於 [hyperf/websocket-server](https://github.com/hyperf/websocket-server)
組件快速搭建一個 WebSocket 應用。

## 安裝

```bash
composer require hyperf/websocket-server
```

## 配置 Server

修改 `config/autoload/server.php`，增加以下配置。

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

## 配置路由

> 目前暫時只支持配置文件的模式配置路由，後續會提供註解模式。

在 `config/routes.php` 文件內增加對應 `ws` 的 Server 的路由配置，這裏的 `ws` 值取決於您在 `config/autoload/server.php`
內配置的 WebSocket Server 的 `name` 值。

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## 配置中間件

在 `config/autoload/middlewares.php` 文件內增加對應 `ws` 的 Server 的全局中間件配置，這裏的 `ws`
值取決於您在 `config/autoload/server.php` 內配置的 WebSocket Server 的 `name` 值。

```php
<?php

return [
    'ws' => [
        yourMiddleware::class
    ]
];
```

## 創建對應控制器

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
            // 如果使用協程 Server，在判斷是 PING 幀後，需要手動處理，返回 PONG 幀。
            // 異步風格 Server，可以直接通過 Swoole 配置處理，詳情請見 https://wiki.swoole.com/#/websocket_server?id=open_websocket_ping_frame
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

接下來啓動 Server，便能看到對應啓動了一個 WebSocket Server 並監聽於 9502 端口，此時您便可以通過各種 WebSocket Client
來進行連接和數據傳輸了。

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

!> 當我們同時監聽了 HTTP Server 的 9501 端口和 WebSocket Server 的 9502 端口時， WebSocket Client 可以通過 9501 和 9502
兩個端口連接 WebSocket Server，即連接 `ws://0.0.0.0:9501` 和 `ws://0.0.0.0:9502` 都可以成功。

因為 Swoole\WebSocket\Server 繼承自 Swoole\Http\Server，可以使用 HTTP 觸發所有 WebSocket
的推送，瞭解詳情可查看 [Swoole 文檔](https://wiki.swoole.com/#/websocket_server?id=websocketserver) onRequest 回調部分。

如需關閉，可以修改 `config/autoload/server.php` 文件給 `http` 服務中增加 `open_websocket_protocol` 配置項。

```php
<?php
return [
    // 這裏省略了該文件的其它配置
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

## 連接上下文

WebSocket 服務的 onOpen, onMessage, onClose 回調並不在同一個協程下觸發，因此不能直接使用協程上下文存儲狀態信息。WebSocket
Server 組件提供了 **連接級** 的上下文，API 與協程上下文完全一樣。

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

## 多 server 配置

```
# /etc/nginx/conf.d/ng_socketio.conf
# 多個 ws server
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
    # 轉發到多個 ws server
    proxy_pass http://io_nodes;
  }
}
```

## 消息發送器

當我們想在 `HTTP` 服務中，關閉 `WebSocket` 連接時，可以直接使用 `Hyperf\WebSocketServer\Sender`。

`Sender` 會判斷 `fd` 是否被當前 `Worker` 所持有，如果是，則會直接發送數據，如果不是，則會通過 `PipeMessage`
發送給除自己外的所有 `Worker`，然後由其他 `Worker` 進行判斷，
如果是自己持有的 `fd`，就會發送對應數據到客户端。

`Sender` 支持 `push` 和 `disconnect` 兩個 `API`，如下：

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\WebSocketServer\Sender;

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

## 在 WebSocket 服務中處理 HTTP 請求

我們除了可以將 HTTP 服務和 WebSocket 服務通過端口分開，也可以在 WebSocket 中監聽 HTTP 請求。

因為 `server.servers.*.callbacks` 中的配置項，都是單例的，所以我們需要在 `dependencies` 中配置一個單獨的實例。

```php
<?php
return [
    'HttpServer' => Hyperf\HttpServer\Server::class,
];
```

然後修改我們的 `WebSocket` 服務中的 `callbacks` 配置，以下隱藏了不相干的配置

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

最後我們便可以在 `ws` 中，添加 `HTTP` 路由了。
