# WebSocket 服务

Hyperf 提供了对 WebSocket Server 的封装，可基于 [hyperf/websocket-server](https://github.com/hyperf/websocket-server) 组件快速搭建一个 WebSocket 应用。

## 安装

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

> 目前暂时只支持配置文件的模式配置路由，后续会提供注解模式。   

在 `config/routes.php` 文件内增加对应 `ws` 的 Server 的路由配置，这里的 `ws` 值取决于您在 `config/autoload/server.php` 内配置的 WebSocket Server 的 `name` 值。

```php
<?php

Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

## 创建对应控制器

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

接下来启动 Server，便能看到对应启动了一个 WebSocket Server 并监听于 9502 端口，此时您便可以通过各种 WebSocket Client 来进行连接和进行数据传输了。

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```

## 连接上下文

WebSocket 服务的 onOpen, onMessage, onClose 回调并不在同一个协程下触发，因此不能直接使用协程上下文存储状态信息。WebSocket Server 组件提供了 **连接级** 的上下文，API 与协程上下文完全一样。

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
# 多个 ws server
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
    # 转发到多个 ws server
    proxy_pass http://io_nodes;
  }
}
```

## 消息发送器

当我们想在 `HTTP` 服务中，关闭 `WebSocket` 连接时，可以直接使用 `Hyperf\WebSocketServer\Sender`。

`Sender` 会判断 `fd` 是否被当前 `Worker` 所持有，如果是，则会直接发送数据，如果不是，则会通过 `PipeMessage` 发送给除自己外的所有 `Worker`，然后由其他 `Worker` 进行判断，
如果是自己持有的 `fd`，就会发送对应数据到客户端。

`Sender` 支持 `push` 和 `disconnect` 两个 `API`，如下：

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\WebSocketServer\Sender;

/**
 * @AutoController
 */
class ServerController
{
    /**
     * @Inject
     * @var Sender
     */
    protected $sender;

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
