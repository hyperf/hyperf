# WebSocket

## WebSocket Server

```
composer require hyperf/websocket-server
```

### 配置 Server

修改 `config/autoload/server.php`，增加以下配置。

```
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

### 配置路由

暂时只支持路由模式配置，注解模式后续会提供。

```
Router::addServer('ws', function () {
    Router::get('/', 'App\Controller\WebSocketController');
});
```

### 创建对应控制器

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

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    public function onMessage(Server $server, Frame $frame): void
    {
        var_dump($frame->data);
        $server->push($frame->fd, 'FROM1: ' . $frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump('closed');
        $server->push($fd, 'closed');
    }

    public function onOpen(Server $server, Request $request): void
    {
        var_dump('opened', $server instanceof \Swoole\WebSocket\Server);
        $server->push($request->fd, 'opened');
    }
}
```

接下来启动 Server

```
$ php bin/hyperf.php start

[INFO] Worker#0 started.
[INFO] WebSocket Server listening at 0.0.0.0:9502
[INFO] HTTP Server listening at 0.0.0.0:9501
```