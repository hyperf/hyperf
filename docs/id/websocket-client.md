# WebSocket Coroutine Client

Hyperf menyediakan enkapsulasi untuk WebSocket Client. Anda dapat mengakses WebSocket Server berdasarkan komponen [hyperf/websocket-client](https://github.com/hyperf/websocket-client);

## Instalasi

```bash
composer require hyperf/websocket-client
```

## Penggunaan

Komponen ini menyediakan `Hyperf\WebSocketClient\ClientFactory` untuk membuat objek client `Hyperf\WebSocketClient\Client`. Mari kita demokan langsung melalui kode:

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
        // Alamat dari layanan peer. Jika awalan ws:// atau wss:// tidak diberikan, ws:// akan ditambahkan secara default.
        $host = '127.0.0.1:9502';
        // Buat objek Client melalui ClientFactory. Objek yang dibuat adalah objek short-lived.
        $client = $this->clientFactory->create($host);
        // Kirim pesan ke WebSocket server
        $client->push('Mengirim data menggunakan WebSocket Client di HttpServer.');
        // Dapatkan pesan yang direspon oleh server. Server perlu mengirim pesan ke fd dari client ini melalui push untuk mendapatkannya; set timeout ke 2s, dan tipe data yang diterima adalah objek Frame.
        /** @var Frame $msg */
        $msg = $client->recv(2);
        // Dapatkan data teks: $res_msg->data
        return $msg->data;
    }
}
```

## Menonaktifkan penutupan otomatis

Secara default, objek `Client` yang dibuat akan otomatis menutup koneksi melalui `defer`. Jika tidak ingin otomatis tertutup, lewatkan parameter kedua `$autoClose` sebagai `false` saat membuat objek `Client`:

```php
$autoClose = false;
$client = $clientFactory->create($host, $autoClose);
```
