# WebSocket 协程客户端

Hyperf 提供了对 WebSocket Client 的封装，可基于 [hyperf/websocket-client](https://github.com/hyperf/websocket-client) 组件对 WebSocket Server 进行访问；

## 安装

```bash
composer require hyperf/websocket-client
```

## 使用

组件提供了一个 `Hyperf\WebSocketClient\ClientFactory` 来创建客户端对象 `Hyperf\WebSocketClient\Client`，我们直接通过代码来演示一下：

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\WebSocketClient\ClientFactory;
use Hyperf\WebSocketClient\Frame;

class IndexController
{
    #[Inject]
    protected ClientFactory $clientFactory;

    public function index()
    {
        // 对端服务的地址，如没有提供 ws:// 或 wss:// 前缀，则默认补充 ws://
        $host = '127.0.0.1:9502';
        // 通过 ClientFactory 创建 Client 对象，创建出来的对象为短生命周期对象
        $client = $this->clientFactory->create($host);
        // 向 WebSocket 服务端发送消息
        $client->push('HttpServer 中使用 WebSocket Client 发送数据。');
        // 获取服务端响应的消息，服务端需要通过 push 向本客户端的 fd 投递消息，才能获取；以下设置超时时间 2s，接收到的数据类型为 Frame 对象。
        /** @var Frame $msg */
        $msg = $client->recv(2);
        // 获取文本数据：$res_msg->data
        return $msg->data;
    }
}
```

## 关闭自动关闭

默认情况下，创建出来的 `Client` 对象会通过 `defer` 自动 `close` 连接，如果您希望不自动 `close`，可在创建 `Client` 对象时传递第二个参数 `$autoClose` 为 `false`：

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
