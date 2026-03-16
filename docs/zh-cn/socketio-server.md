# Socket.io 服务

Socket.io 是一款非常流行的应用层实时通讯协议和框架，可以轻松实现应答、分组、广播。hyperf/socketio-server 支持了 Socket.io 的 WebSocket 传输协议。

## 安装

```bash
composer require hyperf/socketio-server
```

hyperf/socketio-server 组件是基于 WebSocket 实现的，请确保服务端已经添加了 `WebSocket 服务` 的配置。

```php
// config/autoload/server.php
[
    'name' => 'socket-io',
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
        // 应答
        return 'Event Received: ' . $data;
    }

    /**
     * @param string $data
     */
    #[Event("join-room")]
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
     * @param string $data
     */
    #[Event("say")]
    public function onSay(Socket $socket, $data)
    {
        $data = Json::decode($data);
        $socket->to($data['room'])->emit('event', $socket->getSid() . " say: {$data['message']}");
    }
}

```

> 每个 socket 会自动加入以自己 `sid` 命名的房间（`$socket->getSid()`），发送私聊信息就推送到对应 `sid` 即可。

> 框架会自动触发 `connect` 和 `disconnect` 两个事件。

### 客户端

由于服务端只实现了 WebSocket 通讯，所以客户端要加上 `{transports:["websocket"]}` 。

```html
<script src="https://cdn.bootcdn.net/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
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

### Socket API

通过 SocketAPI 对目标 Socket 进行推送，或以目标 Socket 的身份在房间内发言。需要在事件回调中使用。

```php
<?php
#[Event("SomeEvent")]
function onSomeEvent(\Hyperf\SocketIOServer\Socket $socket){

  // sending to the client
  // 向连接推送 hello 事件
  $socket->emit('hello', 'can you hear me?', 1, 2, 'abc');

  // sending to all clients except sender
  // 向所有连接推送 broadcast 事件，但是不包括当前连接。
  $socket->broadcast->emit('broadcast', 'hello friends!');

  // sending to all clients in 'game' room except sender
  // 向 game 房间内所有连接推送 nice game 事件，但是不包括当前连接。
  $socket->to('game')->emit('nice game', "let's play a game");

  // sending to all clients in 'game1' and/or in 'game2' room, except sender
  // 向 game1 房间 和 game2 房间内所有连接取并集推送 nice game 事件，但是不包括当前连接。
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // WARNING: `$socket->to($socket->getSid())->emit()` will NOT work, as it will send to everyone in the room
  // named `$socket->getSid()` but the sender. Please use the classic `$socket->emit()` instead.
  // 注意：自己给自己推送的时候不要加to，因为$socket->to()总是排除自己。直接$socket->emit()就好了。

  // sending with acknowledgement
  // 发送信息，并且等待并接收客户端响应。
  $reply = $socket->emit('question', 'do you think so?')->reply();

  // sending without compression
  // 无压缩推送
  $socket->compress(false)->emit('uncompressed', "that's rough");
}
```
### 全局 API

直接从容器中获取 SocketIO 单例。这个单例可向全局广播或指定房间、个人通讯。未指定命名空间时，默认使用 '/' 空间。

```php
<?php
$io = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

// sending to all clients in 'game' room, including sender
// 向 game 房间内的所有连接推送 bigger-announcement 事件。
$io->in('game')->emit('big-announcement', 'the game will start soon');

// sending to all clients in namespace 'myNamespace', including sender
// 向 /myNamespace 命名空间下的所有连接推送 bigger-announcement 事件
$io->of('/myNamespace')->emit('bigger-announcement', 'the tournament will start soon');

// sending to a specific room in a specific namespace, including sender
// 向 /myNamespace 命名空间下的 room 房间所有连接推送 event 事件
$io->of('/myNamespace')->to('room')->emit('event', 'message');

// sending to individual socketid (private message)
// 向 socketId 单点推送
$io->to('socketId')->emit('hey', 'I just met you');

// sending to all clients on this node (when using multiple nodes)
// 向本机所有连接推送
$io->local->emit('hi', 'my lovely babies');

// sending to all connected clients
// 向所有连接推送
$io->emit('an event sent to all connected clients');
```

### 命名空间 API

和全局 API 一样，只不过已经限制了命名空间。
```php
// 以下伪码等价
$foo->emit();
$io->of('/foo')->emit();

/**
 * class内使用也等价
 */
#[SocketIONamespace("/foo")]
class FooNamespace extends BaseNamespace {
    public function onEvent(){
        $this->emit(); 
        $this->io->of('/foo')->emit();
    }
}
```

## 进阶教程

### 设置 Socket.io 命名空间

Socket.io 通过自定义命名空间实现多路复用。（注意：不是 PHP 的命名空间）

1. 可以通过 `#[SocketIONamespace("/xxx")]` 将控制器映射为 xxx 的命名空间，

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

> Swoole 4.4.17 及以下版本只能读取 HTTP 创建好的 Cookie，Swoole 4.4.18 及以上版本可以在 WebSocket 握手时创建 Cookie

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

2. 可以在控制器上添加 `#[Event]` 注解，以方法名作为事件名来分发。此时应注意其他公有方法可能会和事件名冲突。

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

### 修改 `SocketIO` 基础参数

框架默认参数：

|          配置          | 类型  | 默认值 |
| :--------------------: | :---: | :----: |
|      $pingTimeout      |  int  |  100   |
|     $pingInterval      |  int  | 10000  |
| $clientCallbackTimeout |  int  | 10000  |

有时候，由于推送消息比较多或者网络较卡，在 100ms 内，无法及时返回 `PONG`，就会导致连接断开。这时候我们可以通过以下方式，进行重写：

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

        // 重写 pingTimeout 参数
        $io->setPingTimeout(10000);

        return $io;
    }
}

```

然后在 `dependencies.php` 添加对应映射即可。

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Auth 鉴权

您可以通过使用中间件来拦截 WebSocket 握手，实现鉴权功能，如下：

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
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 伪代码，通过 isAuth 方法拦截握手请求并实现权限检查
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }

        return $handler->handle($request);
    }
}
```

并将上面的中间件配置到对应的 WebSocket Server 中去即可。

### 获取原始请求对象

连接建立以后，有时需获取客户端 IP ，Cookie 等请求信息。原始请求对象已经被保留在[连接上下文](websocket-server.md#连接上下文)中，您可以用如下方式在事件回调中获取：

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Nginx 代理配置

使用 `Nginx` 反向代理 `Socket.io` 与 `WebSocket` 有些许区别
```nginx
server {
    location ^~/socket.io/ {
        # 执行代理访问真实服务器
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```
