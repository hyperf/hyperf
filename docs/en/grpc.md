# gRPC Service

The official quickstart for gRPC in PHP is easily misleading for PHP developers. Following the official documentation, running a gRPC service is cumbersome, let alone a complete RPC service.

It is recommended to read [tech| Revisiting gRPC](https://www.jianshu.com/p/f3221df39e6f), which explains the basic knowledge of implementing gRPC in PHP.

Hyperf has further encapsulated gRPC support. Taking the hyperf-skeleton project as an example, the entire process is explained in detail:

- .proto file and related configuration examples
- gRPC server example
- gRPC client example

## .proto File and Related Configuration Examples

- Define the proto file `grpc.proto`

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

- Use protoc to generate example code

```
# Use Linux package management tool to install protoc, alpine is used below as an example. You can also refer to the Dockerfile under hyperf-skeleton
apk add protobuf

# Use protoc to automatically generate code
protoc --php_out=grpc/ grpc.proto

# tree grpc
grpc
├── GPBMetadata
│   └── Grpc.php
└── Grpc
    ├── HiReply.php
    └── HiUser.php
```

- Configure `composer.json` to use the autoloader for code under `grpc/`. If different `package` settings are used in the proto file, or different directories are used, make adjustments accordingly. After adding, execute `composer dump-autoload` to make the autoloader take effect.

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

## gRPC Server Example

- Install component

```shell
composer require hyperf/grpc-server
```

- gRPC server configuration

`server.php` file (refer to [Configuration](config.md)):

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

`routes.php` file (refer to [Routing](router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.Hi', function () {
        Router::post('/SayHello', 'App\Controller\HiController@sayHello');
    });
});
```

`sayHello` method in the `HiController.php` file:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}
```

The correspondence between the definition in the .proto file and the gRPC server routing is: `/{package}.{service}/{rpc}`

- If you want to go deeper

How the gRPC server processes gRPC requests (`vendor/hyperf/grpc-server/src/CoreMiddleware.php`): `\Hyperf\GrpcServer\CoreMiddleware::process()` parses the `request_uri` to get the `/{package}.{service}/{rpc}` information, and then calls the encapsulated gRPC codec class `\Hyperf\Grpc\Parser::deserializeMessage` to obtain the plaintext information of the request.

How the gRPC server responds to gRPC, I believe you can discover it yourself based on the information above.

## gRPC Client Example

Install component

```shell
composer require hyperf/grpc-client
```

Example code can be found in `GrpcController`:

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

Hyperf has encapsulated `\Hyperf\GrpcClient\BaseClient`. Just extend it as needed according to the definition in the .proto file:

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

The gRPC client also supports gRPC's Streaming mode. Taking bidirectional streaming as an example:

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

> Please note that in streaming mode, you must manually catch exceptions of disconnected connections (`Hyperf\GrpcClient\Exception\GrpcClientException`) and choose whether to retry as needed.

## Afterword

If you are a heavy user of gRPC, you are welcome to pay attention to Hyperf's subsequent developer tools, which can generate a full set of gRPC code based on .proto files.
