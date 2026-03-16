# Socket.io 服務

Socket.io 是一款非常流行的應用層實時通訊協議和框架，可以輕鬆實現應答、分組、廣播。hyperf/socketio-server 支持了 Socket.io 的 WebSocket 傳輸協議。

## 安裝

```bash
composer require hyperf/socketio-server
```

hyperf/socketio-server 組件是基於 WebSocket 實現的，請確保服務端已經添加了 `WebSocket 服務` 的配置。

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
        // 應答
        return 'Event Received: ' . $data;
    }

    /**
     * @param string $data
     */
    #[Event("join-room")]
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

> 每個 socket 會自動加入以自己 `sid` 命名的房間（`$socket->getSid()`），發送私聊信息就推送到對應 `sid` 即可。

> 框架會自動觸發 `connect` 和 `disconnect` 兩個事件。

### 客户端

由於服務端只實現了 WebSocket 通訊，所以客户端要加上 `{transports:["websocket"]}` 。

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

## API 清單

### Socket API

通過 SocketAPI 對目標 Socket 進行推送，或以目標 Socket 的身份在房間內發言。需要在事件回調中使用。

```php
<?php
#[Event("SomeEvent")]
function onSomeEvent(\Hyperf\SocketIOServer\Socket $socket){

  // sending to the client
  // 向連接推送 hello 事件
  $socket->emit('hello', 'can you hear me?', 1, 2, 'abc');

  // sending to all clients except sender
  // 向所有連接推送 broadcast 事件，但是不包括當前連接。
  $socket->broadcast->emit('broadcast', 'hello friends!');

  // sending to all clients in 'game' room except sender
  // 向 game 房間內所有連接推送 nice game 事件，但是不包括當前連接。
  $socket->to('game')->emit('nice game', "let's play a game");

  // sending to all clients in 'game1' and/or in 'game2' room, except sender
  // 向 game1 房間 和 game2 房間內所有連接取並集推送 nice game 事件，但是不包括當前連接。
  $socket->to('game1')->to('game2')->emit('nice game', "let's play a game (too)");

  // WARNING: `$socket->to($socket->getSid())->emit()` will NOT work, as it will send to everyone in the room
  // named `$socket->getSid()` but the sender. Please use the classic `$socket->emit()` instead.
  // 注意：自己給自己推送的時候不要加to，因為$socket->to()總是排除自己。直接$socket->emit()就好了。

  // sending with acknowledgement
  // 發送信息，並且等待並接收客户端響應。
  $reply = $socket->emit('question', 'do you think so?')->reply();

  // sending without compression
  // 無壓縮推送
  $socket->compress(false)->emit('uncompressed', "that's rough");
}
```
### 全局 API

直接從容器中獲取 SocketIO 單例。這個單例可向全局廣播或指定房間、個人通訊。未指定命名空間時，默認使用 '/' 空間。

```php
<?php
$io = \Hyperf\Context\ApplicationContext::getContainer()->get(\Hyperf\SocketIOServer\SocketIO::class);

// sending to all clients in 'game' room, including sender
// 向 game 房間內的所有連接推送 bigger-announcement 事件。
$io->in('game')->emit('big-announcement', 'the game will start soon');

// sending to all clients in namespace 'myNamespace', including sender
// 向 /myNamespace 命名空間下的所有連接推送 bigger-announcement 事件
$io->of('/myNamespace')->emit('bigger-announcement', 'the tournament will start soon');

// sending to a specific room in a specific namespace, including sender
// 向 /myNamespace 命名空間下的 room 房間所有連接推送 event 事件
$io->of('/myNamespace')->to('room')->emit('event', 'message');

// sending to individual socketid (private message)
// 向 socketId 單點推送
$io->to('socketId')->emit('hey', 'I just met you');

// sending to all clients on this node (when using multiple nodes)
// 向本機所有連接推送
$io->local->emit('hi', 'my lovely babies');

// sending to all connected clients
// 向所有連接推送
$io->emit('an event sent to all connected clients');
```

### 命名空間 API

和全局 API 一樣，只不過已經限制了命名空間。
```php
// 以下偽碼等價
$foo->emit();
$io->of('/foo')->emit();

/**
 * class內使用也等價
 */
#[SocketIONamespace("/foo")]
class FooNamespace extends BaseNamespace {
    public function onEvent(){
        $this->emit(); 
        $this->io->of('/foo')->emit();
    }
}
```

## 進階教程

### 設置 Socket.io 命名空間

Socket.io 通過自定義命名空間實現多路複用。（注意：不是 PHP 的命名空間）

1. 可以通過 `#[SocketIONamespace("/xxx")]` 將控制器映射為 xxx 的命名空間，

2. 也可通過

```php
<?php
use Hyperf\SocketIOServer\Collector\SocketIORouter;
use App\Controller\WebSocketController;
SocketIORouter::addNamespace('/xxx' , WebSocketController::class);
```

在路由中添加。

### 開啓 Session 

安裝並配置好 hyperf/session 組件及其對應中間件，再通過 `SessionAspect` 切入 SocketIO 來使用 Session 。

```php
<?php
// config/autoload/aspect.php
return [
    \Hyperf\SocketIOServer\Aspect\SessionAspect::class,
];
```

> Swoole 4.4.17 及以下版本只能讀取 HTTP 創建好的 Cookie，Swoole 4.4.18 及以上版本可以在 WebSocket 握手時創建 Cookie

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

2. 可以在控制器上添加 `#[Event]` 註解，以方法名作為事件名來分發。此時應注意其他公有方法可能會和事件名衝突。

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

### 修改 `SocketIO` 基礎參數

框架默認參數：

|          配置          | 類型  | 默認值 |
| :--------------------: | :---: | :----: |
|      $pingTimeout      |  int  |  100   |
|     $pingInterval      |  int  | 10000  |
| $clientCallbackTimeout |  int  | 10000  |

有時候，由於推送消息比較多或者網絡較卡，在 100ms 內，無法及時返回 `PONG`，就會導致連接斷開。這時候我們可以通過以下方式，進行重寫：

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

        // 重寫 pingTimeout 參數
        $io->setPingTimeout(10000);

        return $io;
    }
}

```

然後在 `dependencies.php` 添加對應映射即可。

```php
return [
    Hyperf\SocketIOServer\SocketIO::class => App\Kernel\SocketIOFactory::class,
];
```

### Auth 鑑權

您可以通過使用中間件來攔截 WebSocket 握手，實現鑑權功能，如下：

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
        // 偽代碼，通過 isAuth 方法攔截握手請求並實現權限檢查
        if (! $this->isAuth($request)) {
            return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class)->raw('Forbidden');
        }

        return $handler->handle($request);
    }
}
```

並將上面的中間件配置到對應的 WebSocket Server 中去即可。

### 獲取原始請求對象

連接建立以後，有時需獲取客户端 IP ，Cookie 等請求信息。原始請求對象已經被保留在[連接上下文](websocket-server.md#連接上下文)中，您可以用如下方式在事件回調中獲取：

```php
public function onEvent($socket, $data)
{
    $request = Hyperf\WebSocketServer\Context::get(
        Psr\Http\Message\ServerRequestInterface::class
    );
}
```

### Nginx 代理配置

使用 `Nginx` 反向代理 `Socket.io` 與 `WebSocket` 有些許區別
```nginx
server {
    location ^~/socket.io/ {
        # 執行代理訪問真實服務器
        proxy_pass http://hyperf;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```
