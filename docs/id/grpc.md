# gRPC Service

Panduan memulai cepat resmi gRPC untuk PHP cukup mudah menyesatkan bagi developer PHP. Mengikuti dokumentasi resmi, menjalankan layanan gRPC terasa merepotkan, apalagi layanan RPC yang lengkap.

Sangat disarankan untuk membaca [tech| Revisiting gRPC](https://www.jianshu.com/p/f3221df39e6f), yang menjelaskan pengetahuan dasar tentang implementasi gRPC di PHP.

Hyperf telah melakukan enkapsulasi lebih lanjut untuk dukungan gRPC. Dengan mengambil proyek hyperf-skeleton sebagai contoh, seluruh proses dijelaskan secara rinci:

- File `.proto` dan contoh konfigurasi terkait
- Contoh gRPC server
- Contoh gRPC client

## File .proto dan Contoh Konfigurasi Terkait

- Mendefinisikan file proto `grpc.proto`

```proto3
syntax = "proto3";

package grpc;

service Hi {
    rpc SayHello (HiUser) returns (HiReply) {
    }
}

message HiUser {
    string name = 1;
    int32 sex = 2;
}

message HiReply {
    string message = 1;
    HiUser user = 2;
}
```

- Menggunakan protoc untuk menghasilkan contoh kode

```
# Gunakan alat manajemen paket Linux untuk menginstal protoc, alpine digunakan di bawah ini sebagai contoh. Anda juga dapat merujuk ke Dockerfile di hyperf-skeleton
apk add protobuf

# Gunakan protoc untuk menghasilkan kode secara otomatis
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- Konfigurasikan `composer.json` untuk menggunakan autoloader bagi kode di bawah `grpc/`. Jika pengaturan `package` yang berbeda digunakan di file proto, atau direktori yang berbeda digunakan, lakukan penyesuaian yang sesuai. Setelah ditambahkan, jalankan `composer dump-autoload` agar autoloader berlaku.

```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "GPBMetadata\\": "grpc/GPBMetadata",
        "Grpc\\": "grpc/Grpc"
    },
    "files": [
    ]
},
```

## Contoh gRPC Server

- Menginstal komponen

```shell
composer require hyperf/grpc-server
```

- Konfigurasi gRPC server

File `server.php` (lihat [Configuration](id/config.md)):

```php
'servers' => [
    ....
    [
        'name' => 'grpc',
        'type' => Server::SERVER_HTTP,
        'host' => '0.0.0.0',
        'port' => 9503,
        'sock_type' => SWOOLE_SOCK_TCP,
        'callbacks' => [
            Event::ON_REQUEST => [\Hyperf\GrpcServer\Server::class, 'onRequest'],
        ],
    ],
],
```

- Konfigurasi routing gRPC server

File `routes.php` (lihat [Routing](id/router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.Hi', function () {
        Router::post('/SayHello', 'App\Controller\HiController@sayHello');
    });
});
```

Method `sayHello` di file `HiController.php`:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}
```

Korespondensi antara definisi di file .proto dan routing gRPC server adalah: `/{package}.{service}/{rpc}`

- Jika Anda ingin lebih mendalami

Bagaimana gRPC server memproses permintaan gRPC (`vendor/hyperf/grpc-server/src/CoreMiddleware.php`): `\Hyperf\GrpcServer\CoreMiddleware::process()` mengurai `request_uri` untuk mendapatkan informasi `/{package}.{service}/{rpc}`, kemudian memanggil kelas codec gRPC yang sudah dienkapsulasi `\Hyperf\Grpc\Parser::deserializeMessage` untuk mendapatkan informasi teks biasa dari request.

Bagaimana gRPC server merespons gRPC, saya yakin Anda dapat menemukannya sendiri berdasarkan informasi di atas.

## Contoh gRPC Client

Menginstal komponen

```shell
composer require hyperf/grpc-client
```

Contoh kode dapat ditemukan di `GrpcController`:

```php
public function hello()
{
    // Client ini aman untuk coroutine dan dapat digunakan kembali
    $client = new \App\Grpc\HiClient('127.0.0.1:9503', [
        'credentials' => null,
    ]);

    $request = new \Grpc\HiUser();
    $request->setName('hyperf');
    $request->setSex(1);

    /**
     * @var \Grpc\HiReply $reply
     */
    list($reply, $status) = $client->sayHello($request);

    $message = $reply->getMessage();
    $user = $reply->getUser();
    
    var_dump(memory_get_usage(true));
    return $message;
}
```

Hyperf telah mengenkapsulasi `\Hyperf\GrpcClient\BaseClient`. Cukup extend sesuai kebutuhan berdasarkan definisi di file .proto:

```php
class HiClient extends BaseClient
{
    public function sayHello(HiUser $argument)
    {
        return $this->_simpleRequest(
            '/grpc.Hi/SayHello',
            $argument,
            [HiReply::class, 'decode']
        );
    }
}
```

gRPC client juga mendukung mode Streaming gRPC. Mengambil bidirectional streaming sebagai contoh:

```php
public function hello()
{
    $client = new RouteGuideClient('127.0.0.1:50051');

    $note = new RouteNote();

    $call = $client->routeChat();
    $call->push($note);
    $call->push($note);

    /** @var RouteNote $note */
    [$note,] = $call->recv();
    [$note,] = $call->recv();
}
```

> Harap diperhatikan bahwa dalam mode streaming, Anda harus menangkap exception koneksi yang terputus secara manual (`Hyperf\GrpcClient\Exception\GrpcClientException`) dan memilih apakah akan melakukan retry sesuai kebutuhan.

## Penutup

Jika Anda adalah pengguna berat gRPC, Anda dipersilakan untuk mengikuti perkembangan alat developer Hyperf selanjutnya, yang dapat menghasilkan seperangkat kode gRPC lengkap berdasarkan file .proto.
