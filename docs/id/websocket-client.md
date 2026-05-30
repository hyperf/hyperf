# WebSocket coroutine client

Hyperf menyediakan enkapsulasi untuk WebSocket Client. WebSocket Server dapat
diakses melalui komponen [hyperf/websocket-client](https://github.com/hyperf/websocket-client).

## Instalasi

```bash
composer require hyperf/websocket-client
```

## Penggunaan

`Hyperf\WebSocketClient\ClientFactory` disediakan untuk membuat objek
`Hyperf\WebSocketClient\Client`.

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
        // The address of the peer service. If there is no prefix like ws:// or wss://, then the ws:// would be used as default.
        $host = '127.0.0.1:9502';
        // Create Client object through ClientFactory. Short-lived objects will be created.
        $client = $this->clientFactory->create($host);
        // Send a message to the WebSocket server
        $client->push('Use WebSocket Client to send data in HttpServer.');
        // Get a response from the server. The server should use 'push()' to send messages to fd of the client, only in this way, can the response be received.
        // A Frame object is taken as an example in following with 2 seconds timeout.
        /** @var Frame $msg */
        $msg = $client->recv(2);
        // Get text data: $res_msg->data
        return $msg->data;
    }
}
```

## Menonaktifkan $autoClose

Secara default, objek `Client` yang dibuat akan ditutup dengan `defer`. Jika
hal ini tidak diinginkan, auto-close dapat dinonaktifkan dengan mengatur
`$autoClose` ke `false` saat `Client` sedang diinstansiasi.

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
