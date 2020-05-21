Socket.io是一款非常流行的應用層實時通訊協議和框架，可以輕鬆實現應答、分組、廣播。hyperf/socketio-server支持了Socket.io的WebSocket傳輸協議。

## 安裝

```bash
composer require hyperf/socketio-server
```

hyperf/socketio-server 是基於WebSocket實現的，請確保服務端已經添加了WebSocket服務配置。

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


## 快速開始

### 服務端
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
        // 應答
        return 'Event Received: ' . $data;
    }

    /**
     * @Event("join-room")
     * @param string $data
     */
    public function onJoinRoom(Socket $socket, $data)
    {
        // 將當前用户加入房間
        $socket->join($data);
        // 向房間內其他用户推送（不含當前用户）
        $socket->to($data)->emit('event', $socket->getSid() . "has joined {$data}");
        // 向房間內所有人廣播（含當前用户）
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

> 每個 socket 會自動加入以自己 `sid` 命名的房間（`$socket->getSid()`），發送私聊信息就推送到對應 `sid` 即可。

> 框架會自動觸發 `connect` 和 `disconnect` 兩個事件。

### 客户端

由於服務端只實現了WebSocket通訊，所以客户端要加上 `{transports:["websocket"]}` 。

```html
<script src="https://cdn.bootcss.com/socket.io/2.3.0/socket.io.js"></script>
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

## API 清單

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

## 進階教程

### 設置 Socket.io 命名空間

Socket.io 通過自定義命名空間實現多路複用。（注意：不是 PHP 的命名空間）

1. 可以通過 `@SocketIONamespace("/xxx")` 將控制器映射為 xxx 的命名空間，

2. 也可通過

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx' , WebSocketController::class);
```

在路由中添加。

### 開啟 Session 

安裝並配置好 hyperf/session 組件及其對應中間件，再通過 `SessionAspect` 切入 SocketIO 來使用 Session 。

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> swoole 4.4.17 及以下版本只能讀取 http 創建好的Cookie，4.4.18 及以上版本可以在WebSocket握手時創建Cookie

### 調整房間適配器

默認的房間功能通過 Redis 適配器實現，可以適應多進程乃至分佈式場景。

1. 可以替換為內存適配器，只適用於單 worker 場景。
```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\MemoryAdapter::class,
];
```

2. 可以替換為空適配器，不需要房間功能時可以降低消耗。
```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\Room\AdapterInterface::class => \Hyperf\SocketIOServer\Room\NullAdapter::class,
];
```

### 調整 SocketID (`sid`)

默認 SocketID 使用 `ServerID#FD` 的格式，可以適應分佈式場景。

1. 可以替換為直接使用 Fd 。

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\LocalSidProvider::class,
];
```

2. 也可以替換為 SessionID 。

```php
<?php
// config/autoload/dependencies.php
return [
    \Hyperf\SocketIOServer\SidProvider\SidProviderInterface::class => \Hyperf\SocketIOServer\SidProvider\SessionSidProvider::class,
];
```

### 其他事件分發方法

1. 可以手動註冊事件，不使用註解。

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

2. 可以在控制器上添加 `@Event()` 註解，以方法名作為事件名來分發。此時應注意其他公有方法可能會和事件名衝突。

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
