# gRPC 服務

gRPC 官方文檔中的 quickstart - php, 很容易給 PHPer 產生誤導, 按照官網的文檔, 運行起來 gRPC 服務就很麻煩, 更不用説整套的 RPC 服務了.

推薦閲讀 [tech| 再探 grpc](https://www.jianshu.com/p/f3221df39e6f), 講解了在 PHP 中實現 gRPC 相關基礎知識.

hyperf 對 gRPC 支持做了更進一步的封裝, hyperf-skeleton 項目為例, 詳細講解整個步驟:

- .proto 文件以及相關配置實例
- gRPC server 示例
- gRPC client 示例

## .proto 文件以及相關配置實例

- 定義好 proto 文件 `grpc.proto`

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

- 使用 protoc 生成示例代碼

```
# 使用 linux 包管理工具安裝 protoc, 下面以 alpine 為例, 也可以參考 hyperf-skeleton 下的 Dockerfile
apk add protobuf

# 使用 protoc 自動生成代碼
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- 配置 composer.json, 使用 `grpc/` 下代碼的自動加載. 如果 proto 文件中使用不同的 `package` 設置, 或者使用了不同的目錄, 進行相應調整即可，添加之後執行 `composer dump-autoload` 使自動加載生效

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

## gRPC server 示例

- 安裝組件

```shell
composer require hyperf/grpc-server
```

- gRPC server 服務器配置

`server.php` 文件(參考 [配置](zh-hk/config.md)):

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

- gRPC server 路由配置

`routes.php` 文件(參考 [路由](zh-hk/router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.hi', function () {
        Router::post('/sayHello', 'App\Controller\HiController@sayHello');
    });
});
```

`HiController.php` 文件中的 `sayHello` 方法:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}

```

.proto 文件中的定義和 gRPC server 路由的對應關係: `/{package}.{service}/{rpc}`

- 如果想更深入一點

gRPC server 如何對 gRPC 請求進行處理的(`vendor/hyperf/grpc-server/src/CoreMiddleware.php)`: `\Hyperf\GrpcServer\CoreMiddleware::process()` 解析出 `request_uri`, 即得到 `/{package}.{service}/{rpc}` 信息, 然後調用封裝好的 gRPC 編解碼類 `\Hyperf\Grpc\Parser::deserializeMessage`, 就可以獲取到請求的明文信息

gRPC server 如何進行 gRPC 響應, 相信你可以根據上面的信息, 自己發現.

## gRPC client 示例

安裝組件

```shell
composer require hyperf/grpc-client
```

示例代碼可以在 `GrpcController` 中找到:

```php
public function hello()
{
    // 這個client是協程安全的，可以複用
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

hyperf 已經封裝好了 `\Hyperf\GrpcClient\BaseClient`, 只要根據 .proto 文件中的定義, 按需擴展:

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

gRPC 客户端還支持 gRPC 的 Streaming 模式。以雙向流為例：

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

> 請注意在 streaming 模式下，您必須手動捕獲連接斷開的異常 (`Hyperf\GrpcClient\Exception\GrpcClientException`) 並根據需要選擇是否重試。

## 寫在後面

如果你是 gRPC 的重度使用者, 歡迎關注 hyperf 的後續開發者工具, 可以根據 .proto 文件生成全套 gRPC 代碼.
