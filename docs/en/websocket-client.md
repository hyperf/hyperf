# WebSocket Coroutine Client

Hyperf provides an encapsulation for the WebSocket Client. You can access the WebSocket Server based on the [hyperf/websocket-client](https://github.com/hyperf/websocket-client) component;

## Installation

```bash
composer require hyperf/websocket-client
```

## Usage

The component provides a `Hyperf\WebSocketClient\ClientFactory` to create a client object `Hyperf\WebSocketClient\Client`. Let's demonstrate it directly through code:

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
        // Address of the peer service. If the ws:// or wss:// prefix is not provided, ws:// is added by default.
        $host = '127.0.0.1:9502';
        // Create a Client object through ClientFactory. The created object is a short-lived object.
        $client = $this->clientFactory->create($host);
        // Send a message to the WebSocket server
        $client->push('Send data using WebSocket Client in HttpServer.');
        // Get the message responded by the server. The server needs to send a message to the fd of this client through push to get it; set the timeout to 2s, and the received data type is Frame object.
        /** @var Frame $msg */
        $msg = $client->recv(2);
        // Get text data: $res_msg->data
        return $msg->data;
    }
}
```

## Turn off automatic closing

By default, the created `Client` object will automatically `close` the connection through `defer`. If you do not want to `close` it automatically, you can pass the second parameter `$autoClose` as `false` when creating the `Client` object:

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
