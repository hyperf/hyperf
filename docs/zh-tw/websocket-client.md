# WebSocket 協程客戶端

Hyperf 提供了對 WebSocket Client 的封裝，可基於 [hyperf/websocket-client](https://github.com/hyperf/websocket-client) 元件對 WebSocket Server 進行訪問；

## 安裝

```bash
composer require hyperf/websocket-client
```

## 使用

元件提供了一個 `Hyperf\WebSocketClient\ClientFactory` 來建立客戶端物件 `Hyperf\WebSocketClient\Client`，我們直接通過程式碼來演示一下：

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
        // 對端服務的地址，如沒有提供 ws:// 或 wss:// 字首，則預設補充 ws://
        $host = '127.0.0.1:9502';
        // 通過 ClientFactory 建立 Client 物件，創建出來的物件為短生命週期物件
        $client = $this->clientFactory->create($host);
        // 向 WebSocket 服務端傳送訊息
        $client->push('HttpServer 中使用 WebSocket Client 傳送資料。');
        // 獲取服務端響應的訊息，服務端需要通過 push 向本客戶端的 fd 投遞訊息，才能獲取；以下設定超時時間 2s，接收到的資料型別為 Frame 物件。
        /** @var Frame $msg */
        $msg = $client->recv(2);
        // 獲取文字資料：$res_msg->data
        return $msg->data;
    }
}
```

## 關閉自動關閉

預設情況下，創建出來的 `Client` 物件會通過 `defer` 自動 `close` 連線，如果您希望不自動 `close`，可在建立 `Client` 物件時傳遞第二個引數 `$autoClose` 為 `false`：

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
