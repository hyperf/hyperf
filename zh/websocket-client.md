# WebSocket 协程客户端

Hyperf 提供了对 WebSocket Client 的封装，可基于 [hyperf/websocket-client](https://github.com/hyperf-cloud/websocket-client) 组件对 WebSocket Server 进行访问；
或许有一天你的业务需求刚好需要在Http和Websocket之间交互数据，可能你为解决方案已经找遍了整个互联网...蓦然回首，发现Hyperf 已经为您提供了完美的解决方案...然而这一切仅仅一小段代码而已！

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

class IndexController extends Controller
{

    /**
     * @Inject()
     * @var \Hyperf\WebSocketClient\ClientFactory
     */
    protected $clientFactory;

    public function index()
    {
        // 对端服务的地址，如没有提供 ws:// 或 wss:// 前缀，则默认补充 ws://
        $host = '127.0.0.1:9502';
        // 通过 ClientFactory 创建 Client 对象，创建出来的对象为短生命周期对象
        $client = $this->clientFactory->create($host);
        //  向websocket服务端发送消息
        $client->push("短连接的Http向Websocket服务端发送消息...");
        // 获取服务端响应的消息，服务端需要通过push向本客户端的 fd 投递消息，才能获取。
        $res_msg=$client->recv(2);  // 接受服务端响应的数据，设置超时时间为 2s ，服务器返回的数据类型为std对象，
        return $res_msg->data  ;   //获取文本数据：$res_msg->data 
        
    }
}
```

## 关闭自动关闭

默认情况下，创建出来的 `Client` 对象会通过 `defer` 自动 `close` 连接，如果您希望不自动 `close`，可在创建 `Client` 对象时传递第二个参数 `$autoClose` 为 `false`：

```php
<?php

$autoClose = false;
$clientFactory->create($host, $autoClose);
```