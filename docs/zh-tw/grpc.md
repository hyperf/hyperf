# gRPC 服務

gRPC 官方文件中的 quickstart - php, 很容易給 PHPer 產生誤導, 按照官網的文件, 執行起來 gRPC 服務就很麻煩, 更不用說整套的 RPC 服務了.

推薦閱讀 [tech| 再探 grpc](https://www.jianshu.com/p/f3221df39e6f), 講解了在 PHP 中實現 gRPC 相關基礎知識.

hyperf 對 gRPC 支援做了更進一步的封裝, hyperf-skeleton 專案為例, 詳細講解整個步驟:

- .proto 檔案以及相關配置例項
- gRPC server 示例
- gRPC client 示例

## .proto 檔案以及相關配置例項

- 定義好 proto 檔案 `grpc.proto`

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

- 使用 protoc 生成示例程式碼

```
# 使用 linux 包管理工具安裝 protoc, 下面以 alpine 為例, 也可以參考 hyperf-skeleton 下的 Dockerfile
apk add protobuf

# 使用 protoc 自動生成程式碼
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- 配置 composer.json, 使用 `grpc/` 下程式碼的自動載入. 如果 proto 檔案中使用不同的 `package` 設定, 或者使用了不同的目錄, 進行相應調整即可，新增之後執行 `composer dump-autoload` 使自動載入生效

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

- 安裝元件

```shell
composer require hyperf/grpc-server
```

- gRPC server 伺服器配置

`server.php` 檔案(參考 [配置](zh-tw/config.md)):

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

`routes.php` 檔案(參考 [路由](zh-tw/router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.hi', function () {
        Router::post('/sayHello', 'App\Controller\HiController@sayHello');
    });
});
```

`HiController.php` 檔案中的 `sayHello` 方法:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}

```

.proto 檔案中的定義和 gRPC server 路由的對應關係: `/{package}.{service}/{rpc}`

- 如果想更深入一點

gRPC server 如何對 gRPC 請求進行處理的(`vendor/hyperf/grpc-server/src/CoreMiddleware.php)`: `\Hyperf\GrpcServer\CoreMiddleware::process()` 解析出 `request_uri`, 即得到 `/{package}.{service}/{rpc}` 資訊, 然後呼叫封裝好的 gRPC 編解碼類 `\Hyperf\Grpc\Parser::deserializeMessage`, 就可以獲取到請求的明文資訊

gRPC server 如何進行 gRPC 響應, 相信你可以根據上面的資訊, 自己發現.

## gRPC client 示例

安裝元件

```shell
composer require hyperf/grpc-client
```

示例程式碼可以在 `GrpcController` 中找到:

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

hyperf 已經封裝好了 `\Hyperf\GrpcClient\BaseClient`, 只要根據 .proto 檔案中的定義, 按需擴充套件:

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

gRPC 客戶端還支援 gRPC 的 Streaming 模式。以雙向流為例：

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

> 請注意在 streaming 模式下，您必須手動捕獲連線斷開的異常 (`Hyperf\GrpcClient\Exception\GrpcClientException`) 並根據需要選擇是否重試。

## 寫在後面

如果你是 gRPC 的重度使用者, 歡迎關注 hyperf 的後續開發者工具, 可以根據 .proto 檔案生成全套 gRPC 程式碼.
