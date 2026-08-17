# Serviço gRPC

O quickstart-php na documentação oficial do gRPC pode facilmente confundir desenvolvedores PHP. Segundo a documentação do site oficial, executar o serviço gRPC é muito complexo, sem falar em todo o conjunto de serviços RPC.

Recomenda-se a leitura de [tech| 再探 grpc](https://www.jianshu.com/p/f3221df39e6f), que explica o conhecimento básico de implementação do gRPC em PHP.

O Hyperf encapsulou ainda mais o suporte ao gRPC. O projeto hyperf-skeleton é usado como exemplo para explicar todo o processo em detalhes:

- Arquivo .proto e exemplos de configuração relacionados
- Exemplo de server gRPC
- Exemplo de client gRPC

## Arquivo .proto e exemplos de configuração relacionados

- Defina o arquivo proto - `grpc.proto`

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

- Use o protoc para gerar código de exemplo

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

- Configure o composer.json, usando o carregamento automático do código em `grpc/`. Se configurações diferentes de `package` forem usadas no arquivo proto, ou se um diretório diferente for usado, ajuste conforme necessário. E então, execute `composer dump-autoload` após adicionar para que a alteração entre em vigor.

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

## Exemplo de server gRPC

- Configuração do server gRPC

Arquivo `server.php` (consulte [config](pt-br/config.md)):

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

- Configuração de roteamento do server gRPC

Arquivo `routes.php` (consulte [router](pt-br/router.md)):

```php
Router::addServer('grpc', function () {
    Router::addGroup('/grpc.hi', function () {
        Router::post('/sayHello', 'App\Controller\HiController@sayHello');
    });
});
```

Método `sayHello` no arquivo `HiController.php`:

```php
public function sayHello(HiUser $user) 
{
    $message = new HiReply();
    $message->setMessage("Hello World");
    $message->setUser($user);
    return $message;
}

```

Correspondência entre a definição no arquivo .proto e o roteamento do server gRPC: `/{package}.{service}/{rpc}`

- Se você quiser ir mais a fundo nisso

Como o server gRPC processa requisições gRPC (`vendor/hyperf/grpc-server/src/CoreMiddleware.php`): `\Hyperf\GrpcServer\CoreMiddleware::process()` analisa o `request_uri` e obtém as informações de `/{package}.{service}/{rpc}`, e então chama a classe de decodificação gRPC encapsulada `\Hyperf\Grpc\Parser::deserializeMessage`; você pode obter as informações do texto puro requisitado

Como o server gRPC responde ao gRPC? Você pode obter a resposta através das informações fornecidas acima.

## Exemplo de client gRPC

O código de exemplo pode ser encontrado em `GrpcController`:

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

O Hyperf já encapsulou `\Hyperf\GrpcClient\BaseClient`; estenda-o conforme necessário de acordo com a definição no arquivo .proto:

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

O client gRPC também suporta o modo Streaming do gRPC. Tomando o fluxo bidirecional como exemplo:

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

> Note que, no modo streaming, você deve capturar manualmente a exceção de desconexão (`Hyperf\GrpcClient\Exception\GrpcClientException`) e escolher se deseja ou não retentar.

## Para finalizar

Se você é um usuário de alta frequência do gRPC, fique atento às futuras ferramentas de desenvolvimento do hyperf, que poderão gerar um conjunto completo de código gRPC com base no arquivo .proto.
