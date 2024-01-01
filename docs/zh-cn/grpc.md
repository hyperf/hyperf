# gRPC 服务

gRPC 官方文档中的 quickstart - php, 很容易给 PHPer 产生误导, 按照官网的文档, 运行起来 gRPC 服务就很麻烦, 更不用说整套的 RPC 服务了.

推荐阅读 [tech| 再探 grpc](https://www.jianshu.com/p/f3221df39e6f), 讲解了在 PHP 中实现 gRPC 相关基础知识.

hyperf 对 gRPC 支持做了更进一步的封装, hyperf-skeleton 项目为例, 详细讲解整个步骤:

- .proto 文件以及相关配置实例
- gRPC server 示例
- gRPC client 示例

## .proto 文件以及相关配置实例

- 定义好 proto 文件 `grpc.proto`

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

- 使用 protoc 生成示例代码

```
# 使用 linux 包管理工具安装 protoc, 下面以 alpine 为例, 也可以参考 hyperf-skeleton 下的 Dockerfile
apk add protobuf

# 使用 protoc 自动生成代码
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- 配置 composer.json, 使用 `grpc/` 下代码的自动加载. 如果 proto 文件中使用不同的 `package` 设置, 或者使用了不同的目录, 进行相应调整即可，添加之后执行 `composer dump-autoload` 使自动加载生效

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

- 安装组件

```shell
composer require hyperf/grpc-server
```

- gRPC server 服务器配置

`server.php` 文件(参考 [配置](zh-cn/config.md)):

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

`routes.php` 文件(参考 [路由](zh-cn/router.md)):

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

.proto 文件中的定义和 gRPC server 路由的对应关系: `/{package}.{service}/{rpc}`

- 如果想更深入一点

gRPC server 如何对 gRPC 请求进行处理的(`vendor/hyperf/grpc-server/src/CoreMiddleware.php)`: `\Hyperf\GrpcServer\CoreMiddleware::process()` 解析出 `request_uri`, 即得到 `/{package}.{service}/{rpc}` 信息, 然后调用封装好的 gRPC 编解码类 `\Hyperf\Grpc\Parser::deserializeMessage`, 就可以获取到请求的明文信息

gRPC server 如何进行 gRPC 响应, 相信你可以根据上面的信息, 自己发现.

## gRPC client 示例

安装组件

```shell
composer require hyperf/grpc-client
```

示例代码可以在 `GrpcController` 中找到:

```php
public function hello()
{
    // 这个client是协程安全的，可以复用
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

hyperf 已经封装好了 `\Hyperf\GrpcClient\BaseClient`, 只要根据 .proto 文件中的定义, 按需扩展:

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

gRPC 客户端还支持 gRPC 的 Streaming 模式。以双向流为例：

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

> 请注意在 streaming 模式下，您必须手动捕获连接断开的异常 (`Hyperf\GrpcClient\Exception\GrpcClientException`) 并根据需要选择是否重试。

## 写在后面

如果你是 gRPC 的重度使用者, 欢迎关注 hyperf 的后续开发者工具, 可以根据 .proto 文件生成全套 gRPC 代码.
