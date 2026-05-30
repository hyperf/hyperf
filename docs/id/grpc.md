# gRPC Service

Panduan quickstart-php di dokumentasi resmi gRPC mudah menyesatkan PHPer.
Berdasarkan dokumentasi di situs web resmi, menjalankan layanan gRPC sangat
rumit, belum lagi seluruh rangkaian layanan RPC.

Direkomendasikan untuk membaca [tech| 再探 grpc](https://www.jianshu.com/p/f3221df39e6f)
yang menjelaskan pengetahuan dasar penerapan gRPC di PHP.

Hyperf telah mengenkapsulasi dukungan gRPC lebih lanjut. Proyek
hyperf-skeleton diambil sebagai contoh untuk menjelaskan seluruh langkah secara
mendetail:

- File .proto dan contoh konfigurasi terkait
- Contoh gRPC server
- Contoh gRPC client

## File .proto dan contoh konfigurasi terkait

- Definisikan file proto - `grpc.proto`

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

- Gunakan protoc untuk menghasilkan kode sampel

```
# Gunakan alat manajemen paket linux untuk menginstal protoc. Mari kita ambil alpine sebagai contoh. Anda juga dapat merujuk ke Dockerfile di bawah hyperf-skeleton
apk add protobuf

# Gunakan protoc untuk menghasilkan kode secara otomatis
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- Konfigurasikan composer.json, gunakan pemuatan otomatis (autoloading) kode
  di bawah `grpc/`. Jika pengaturan `package` yang berbeda digunakan dalam file
  proto, atau direktori lain digunakan, sesuaikan. Dan kemudian, jalankan
  `composer dump-autoload` setelah menambahkan agar menjadi aktif.

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

## Contoh gRPC server

- Konfigurasi gRPC server

File `server.php` (merujuk pada [config](id/config.md)):

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

File `routes.php` (merujuk pada [router](id/router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.hi', function () {
        Router::post('/sayHello', 'App\Controller\HiController@sayHello');
    });
});
```

Metode `sayHello` di file `HiController.php`:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}

```

Hubungan antara definisi dalam file .proto dan routing gRPC server: `/{package}.{service}/{rpc}`

- Jika Anda ingin mempelajarinya lebih lanjut

Bagaimana gRPC server memproses request gRPC
(`vendor/hyperf/grpc-server/src/CoreMiddleware.php`):
`\Hyperf\GrpcServer\CoreMiddleware::process()` menganalisis `request_uri` dan
mendapatkan informasi `/{package}.{service}/{rpc}`, lalu memanggil kelas decode
gRPC yang terenkapsulasi `\Hyperf\Grpc\Parser::deserializeMessage`, Anda dapat
mendapatkan informasi plaintext yang diminta.

Bagaimana gRPC server merespons gRPC? Anda mungkin bisa mendapatkan jawabannya
melalui informasi yang disediakan di atas.

## Contoh gRPC client

Kode sampel dapat ditemukan di `GrpcController`:

```php
public function hello()
{
    // Client ini coroutine-safe dan dapat digunakan kembali
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

Hyperf telah mengenkapsulasi `\Hyperf\GrpcClient\BaseClient`, kembangkan jika
diperlukan sesuai dengan definisi di file .proto:

```php
class HiClient extends BaseClient
{
    public function sayHello(HiUser $argument)
    {
        return $this->_simpleRequest(
            '/grpc.hi/sayHello',
            $argument,
            [HiReply::class, 'decode']
        );
    }
}
```

gRPC client juga mendukung mode Streaming dari gRPC. Ambil contoh aliran dua arah
(two-way flow):

```php
<?
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

> Harap dicatat bahwa dalam mode streaming, Anda harus menangkap exception
> terputus (`Hyperf\GrpcClient\Exception\GrpcClientException`) secara manual
> dan memilih apakah akan mencoba lagi (retry) atau tidak.

## Di akhir kata

Jika Anda adalah pengguna frekuensi tinggi gRPC, Anda dipersilakan untuk
memperhatikan alat pengembang (developer tools) dari hyperf berikutnya, yang
dapat menghasilkan seluruh rangkaian kode gRPC berdasarkan file .proto.
