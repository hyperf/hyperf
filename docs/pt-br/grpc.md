# Serviço gRPC

O quickstart-php da documentação oficial do gRPC é fácil de induzir desenvolvedores PHP ao erro. De acordo com a documentação do site oficial, executar um serviço gRPC é bem complexo, sem falar em todo o conjunto de serviços RPC.

[tech| å†æŽ¢ grpc](https://www.jianshu.com/p/f3221df39e6f) é uma leitura recomendada, pois explica os conhecimentos básicos de implementação de gRPC em PHP.

O Hyperf encapsulou ainda mais o suporte ao gRPC. Usaremos o projeto hyperf-skeleton como exemplo para explicar todas as etapas em detalhes:

- exemplos de arquivo .proto e configurações relacionadas
- exemplo de servidor gRPC
- exemplo de cliente gRPC

## Exemplos de arquivo .proto e configurações relacionadas

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
â”œâ”€â”€ GPBMetadata
â”‚Â Â  â””â”€â”€ Grpc.php
â””â”€â”€ Grpc
    â”œâ”€â”€ HiReply.php
    â””â”€â”€ HiUser.php
```

- Configure o composer.json para usar o carregamento automático do código em `grpc/`. Se forem usados diferentes valores de `package` no arquivo proto, ou um diretório diferente, ajuste conforme necessário. Em seguida, após adicionar, execute `composer dump-autoload after adding` para ativar.

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

## Exemplo de servidor gRPC

- Configuração do servidor gRPC

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

- Configuração de roteamento do servidor gRPC

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

Correspondência entre a definição no arquivo .proto e o roteamento do servidor gRPC: `/{package}.{service}/{rpc}`

- Se você quiser se aprofundar nisso

Como o servidor gRPC processa requisições gRPC (`vendor/hyperf/grpc-server/src/CoreMiddleware.php)`): `\Hyperf\GrpcServer\CoreMiddleware::process()` faz o parse do `request_uri` e obtém as informações `/{package}.{service}/{rpc}`. Em seguida, ele chama a classe de decode gRPC encapsulada `\Hyperf\Grpc\Parser::deserializeMessage`, e você pode obter as informações em texto plano solicitadas.

Como o servidor gRPC responde ao gRPC? Você pode encontrar a resposta nas informações fornecidas acima.

## Exemplo de cliente gRPC

O código de exemplo pode ser encontrado em `GrpcController`:

```php
public function hello()
{
    // Este cliente é coroutine-safe e pode ser reutilizado
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

O Hyperf já encapsulou `\Hyperf\GrpcClient\BaseClient`. Caso necessário, estenda-o conforme a definição no arquivo .proto:

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

O cliente gRPC também suporta o modo Streaming do gRPC. Veja um exemplo de fluxo bidirecional:

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

> Observe que, no modo streaming, você deve capturar manualmente a exceção de desconexão (`Hyperf\GrpcClient\Exception\GrpcClientException`) e decidir se deve tentar novamente ou não.

## Por fim

Se você usa gRPC com alta frequência, fique à vontade para acompanhar as ferramentas de desenvolvedor posteriores do Hyperf, que podem gerar um conjunto completo de código gRPC com base no arquivo .proto.
