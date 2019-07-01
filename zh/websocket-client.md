# WebSocket 协程客户端

Hyperf 提供了对 WebSocket Client 的封装，可基于 [hyperf/websocket-client](https://github.com/hyperf-cloud/websocket-client) 组件对 WebSocket Server 进行访问；

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