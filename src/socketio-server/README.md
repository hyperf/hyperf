Socket.io是一款非常流行的应用层实时通讯协议和框架，可以轻松实现应答、分组、广播。hyperf/socketio-server支持了Socket.io的WebSocket传输协议。

## 安装

```bash
composer require hyperf/socketio-server
```

hyperf/socketio-server 是基于WebSocket实现的，请确保服务端已经添加了WebSocket服务配置。

```php
        [
            'name' => 'socket-io',
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
```


## 快速开始

### 服务端
```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;
use Hyperf\Utils\Codec\Json;

/**
 * @SocketIONamespace("/")
 */
class WebSocketController extends BaseNamespace
{
    /**
     * @Event("event")
     * @param string $data
     */
    public function onEvent(Socket $socket, $data)
    {
        // 应答
        return 'Event Received: ' . $data;
    }

    /**
     * @Event("join-room")
     * @param string $data
     */
    public function onJoinRoom(Socket $socket, $data)
    {
        // 将当前用户加入房间
        $socket->join($data);
        // 向房间内其他用户推送（不含当前用户）
        $socket->to($data)->emit('event', $socket->getSid() . "has joined {$data}");
        // 向房间内所有人广播（含当前用户）
        $this->emit('event', 'There are ' . count($socket->getAdapter()->clients($data)) . " players in {$data}");
    }

    /**
     * @Event("say")
     * @param string $data
     */
    public function onSay(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }
}

```

> 每个 socket 会自动加入以自己 `sid` 命名的房间（`$socket->getSid()`），发送私聊信息就推送到对应 `sid` 即可。

> 框架会触发 `connect` 和 `disconnect` 两个事件。

### 客户端

由于服务端只实现了WebSocket通讯，所以客户端要加上 `{transports:["websocket"]}` 。

```html
<script src="https://cdn.bootcdn.net/ajax/libs/socket.io/4.5.4/socket.io.js"></script>
<script>
    var socket = io('ws://127.0.0.1:9502', { transports: ["websocket"] });
    socket.on('connect', data => {
        socket.emit('event', 'hello, hyperf', console.log);
        socket.emit('join-room', 'room1', console.log);
        setInterval(function () {
            socket.emit('say', '{"room":"room1", "message":"Hello Hyperf."}');
        }, 1000);
    });
    socket.on('event', console.log);
</script>
```

## API 清单

```php
<?php
function onConnect(\Hyperf\SocketIOServer\Socket $socket){

  // sending to the client
  $socket->emit('hello', 'can you hear me?', 1, 2, 'abc');

  // sending to all clients except sender
  $socket->broadcast->emit('broadcast', 'hello friends!');

  // sending to all clients in 'game' room except sender
  $socket->to('game')->emit('nice game', "let's play a game");

  // sending to all clients in 'game1' and/or in 'game2' room, except sender
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // WARNING: `$socket->to($socket->getSid())->emit()` will NOT work, as it will send to everyone in the room
  // named `$socket->getSid()` but the sender. Please use the classic `$socket->emit()` instead.

  // sending with acknowledgement
  $reply = $socket->emit('question', 'do you think so?')->reply();

  // sending without compression
  $socket->compress(false)->emit('uncompressed', "that's rough");

  $io = \Hyperf\Utils\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

  // sending to all clients in 'game' room, including sender
  $io->in('game')->emit('big-announcement', 'the game will start soon');

  // sending to all clients in namespace 'myNamespace', including sender
  $io->of('/myNamespace')->emit('bigger-announcement', 'the tournament will start soon');

  // sending to a specific room in a specific namespace, including sender
  $io->of('/myNamespace')->to('room')->emit('event', 'message');

  // sending to individual socketid (private message)
  $io->to('socketId')->emit('hey', 'I just met you');

  // sending to all clients on this node (when using multiple nodes)
  $io->local->emit('hi', 'my lovely babies');

  // sending to all connected clients
  $io->emit('an event sent to all connected clients');

};
```

## 进阶教程

### 设置 Socket.io 命名空间

Socket.io 通过自定义命名空间实现多路复用。（注意：不是 PHP 的命名空间）

1. 可以通过 `@SocketIONamespace("/xxx")` 将控制器映射为 xxx 的命名空间，

2. 也可通过

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx' , WebSocketController::class);
```

在路由中添加。

### 开启 Session

安装并配置好 hyperf/session 组件及其对应中间件，再通过 `SessionAspect` 切入 SocketIO 来使用 Session 。

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> swoole 4.4.17 及以下版本只能读取 http 创建好的Cookie，4.4.18 及以上版本可以在WebSocket握手时创建Cookie

### 调整房间适配器

默认的房间功能通过 Redis 适配器实现，可以适应多进程乃至分布式场景。

1. 可以替换为内存适配器，只适用于单 worker 场景。
```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. 可以替换为空适配器，不需要房间功能时可以降低消耗。
```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### 调整 SocketID (`sid`)

默认 SocketID 使用 `ServerID#FD` 的格式，可以适应分布式场景。

1. 可以替换为直接使用 Fd 。

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. 也可以替换为 SessionID 。

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### 其他事件分发方法

1. 可以手动注册事件，不使用注解。

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
        $this->on('event', [$this, 'echo']);
    }

    public function echo(Socket $socket, $data)
    {
        $socket->emit('event', $data);
    }
}
```

2. 可以在控制器上添加 `@Event()` 注解，以方法名作为事件名来分发。此时应注意其他公有方法可能会和事件名冲突。

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\SocketIOServer\Annotation\SocketIONamespace;
use Hyperf\SocketIOServer\Annotation\Event;
use Hyperf\SocketIOServer\BaseNamespace;
use Hyperf\SocketIOServer\Socket;

/**
 * @SocketIONamespace("/")
 * @Event()
 */
class WebSocketController extends BaseNamespace
{
    public function echo(Socket $socket, $data)
    {
        $socket->emit('event', $data);
    }
}
```
