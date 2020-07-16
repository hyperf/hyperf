# WebSocket 服務

Hyperf 提供了對 WebSocket Server 的封裝，可基於 [hyperf/websocket-server](https://github.com/hyperf/websocket-server) 元件快速搭建一個 WebSocket 應用。

## 安裝

```bash
composer require hyperf/websocket-server
```

## 配置 Server

修改 `config/autoload/server.php`，增加以下配置。

```php
<?php

'servers' => [
    [
        'name' => 'ws',
        'type' => Server::SERVER_WEBSOCKET,
        'host' => '0.0.0.0',
        'port' => 9502,
        'sock_type' => SWOOLE_SOCK_TCP,
        'callbacks' => [
            SwooleEvent::ON_HAND_SHAKE => [Hyperf\WebSocketServer\Server::class, 'onHandShake'],
            SwooleEvent::ON_MESSAGE => [Hyperf\WebSocketServer\Server::class, 'onMessage'],
            SwooleEvent::ON_CLOSE => [Hyperf\WebSocketServer\Server::class, 'onClose'],
        ],
    ],
],
```

## 配置路由

> 目前暫時只支援配置檔案的模式配置路由，後續會提供註解模式。   

在 `config/routes.php` 檔案內增加對應 `ws` 的 Server 的路由配置，這裡的 `ws` 值取決於您在 `config/autoload/server.php` 內配置的 WebSocket Server 的 `name` 值。

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## 建立對應控制器

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

接下來啟動 Server，便能看到對應啟動了一個 WebSocket Server 並監聽於 9502 埠，此時您便可以通過各種 WebSocket Client 來進行連線和進行資料傳輸了。

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

## 連線上下文

WebSocket 服務的 onOpen, onMessage, onClose 回撥並不在同一個協程下觸發，因此不能直接使用協程上下文儲存狀態資訊。WebSocket Server 元件提供了 **連線級** 的上下文，API 與協程上下文完全一樣。

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
