# gRPC Service

The quickstart-php in the official gRPC documentation is easy to mislead PHPer. According to the documentation on the official website, it is very complex to run the gRPC service, not to mention the entire set of RPC services.

[tech| 再探 grpc](https://www.jianshu.com/p/f3221df39e6f) is recommended to have a read, which explains the basic knowledge of implementing gRPC in PHP.

Hyperf has further encapsulated gRPC support. The hyperf-skeleton project is taken as an example to explain the entire steps in detail:

- .proto file and related configuration examples
- gRPC server example
- gRPC client example

## .proto file and related configuration examples

- Define proto file - `grpc.proto`

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

- Use protoc to generate sample code

```
# Use the linux package management tool to install protoc. Let's take alpine as an example. You can also refer to the Dockerfile under hyperf-skeleton
apk add protobuf

# Use protoc to automatically generate code
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- Configure composer.json, use the automatic loading of the code under `grpc/`. If different `package` settings are used in the proto file, or a different directory is used, adjust accordingly. And then, execute `composer dump-autoload after adding` make it active.

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

## gRPC server example

- gRPC server configuration

`server.php` file(refer to [config](zh-cn/config.md)):

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

- gRPC server routing configuration

`routes.php` file(refer to [router](zh-cn/router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.hi', function () {
        Router::post('/sayHello', 'App\Controller\HiController@sayHello');
    });
});
```

`sayHello` method in `HiController.php` file:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}

```

Correspondence between the definition in the .proto file and gRPC server routing: `/{package}.{service}/{rpc}`

- If you would like to go further in this

How gRPC server processes gRPC requests(`vendor/hyperf/grpc-server/src/CoreMiddleware.php)`: `\Hyperf\GrpcServer\CoreMiddleware::process()` parse the `request_uri` and get the `/{package}.{service}/{rpc}` information, and then call the encapsulated gRPC decode class `\Hyperf\Grpc\Parser::deserializeMessage`, you can get the requested plaintext information

How gRPC server responds to gRPC? You may get the answer through provided information above.

## gRPC client example

The sample code can be found in `GrpcController`:

```php
public function hello()
{
    // This client is coroutine-safe and can be reused
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

Hyperf has already encapsulated `\Hyperf\GrpcClient\BaseClient`, expand it if needed according to the definition in .proto file:

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

The gRPC client also supports gRPC's Streaming mode. Take two-way flow as an example:

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

> Note that in streaming mode, you must manually catch the disconnected exception (`Hyperf\GrpcClient\Exception\GrpcClientException`) and choose whether to retry or not.

## At the end

If you are a high-frequency user of gRPC, you are welcome to pay attention to the follow-up developer tools of hyperf, which can generate a full set of gRPC code based on the .proto file.
