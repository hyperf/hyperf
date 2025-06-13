# WebSocket 協程客户端

Hyperf 提供了對 WebSocket Client 的封裝，可基於 [hyperf/websocket-client](https://github.com/hyperf/websocket-client) 組件對 WebSocket Server 進行訪問；

## 安裝

```bash
composer require hyperf/websocket-client
```

## 使用

組件提供了一個 `Hyperf\WebSocketClient\ClientFactory` 來創建客户端對象 `Hyperf\WebSocketClient\Client`，我們直接通過代碼來演示一下：

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
        // 對端服務的地址，如沒有提供 ws:// 或 wss:// 前綴，則默認補充 ws://
        $host = '127.0.0.1:9502';
        // 通過 ClientFactory 創建 Client 對象，創建出來的對象為短生命週期對象
        $client = $this->clientFactory->create($host);
        // 向 WebSocket 服務端發送消息
        $client->push('HttpServer 中使用 WebSocket Client 發送數據。');
        // 獲取服務端響應的消息，服務端需要通過 push 向本客户端的 fd 投遞消息，才能獲取；以下設置超時時間 2s，接收到的數據類型為 Frame 對象。
        /** @var Frame $msg */
        $msg = $client->recv(2);
        // 獲取文本數據：$res_msg->data
        return $msg->data;
    }
}
```

## 關閉自動關閉

默認情況下，創建出來的 `Client` 對象會通過 `defer` 自動 `close` 連接，如果您希望不自動 `close`，可在創建 `Client` 對象時傳遞第二個參數 `$autoClose` 為 `false`：

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
