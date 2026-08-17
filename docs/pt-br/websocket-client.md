# Cliente WebSocket de corrotina

O Hyperf fornece um encapsulamento de WebSocket Client. O WebSocket Server pode ser acessado por meio do componente [hyperf/websocket-client](https://github.com/hyperf/websocket-client).

## Instalação

```bash
composer require hyperf/websocket-client
```

## Uso

O `Hyperf\WebSocketClient\ClientFactory` é fornecido para criar objetos `Hyperf\WebSocketClient\Client`.

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

## Desabilitar o $autoClose

Por padrão, o objeto `Client` criado será fechado via `defer`. Se você não quiser isso, é possível desabilitar o auto-close definindo `$autoClose` como `false` ao instanciar um `Client`.

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
