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
'settings' => [
    // 其他配置
    'max_request' => 0,  // max_request => 2000，此参数表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。 设置为0表示不自动重启。在Worker进程中需要保存连接信息的服务，需要设置为0。
],
```

注：参考[swoole设置运行时参数](https://wiki.swoole.com/wiki/page/13.html)

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
    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $server->push($frame->fd, 'Recv: ' . $frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        var_dump('closed');
    }

    public function onOpen(WebSocketServer $server, Request $request): void
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
