# Serviço JSON-RPC

JSON-RPC é um padrão de protocolo RPC leve baseado no formato JSON, fácil de usar e de ler. No Hyperf, ele é implementado pelo componente [hyperf/json-rpc](https://github.com/hyperf/json-rpc), que pode ser personalizado para transmissão via protocolo HTTP, ou diretamente via protocolo TCP.

## Instalação

```bash
composer require hyperf/json-rpc
```
  
Este é apenas um componente de processamento de protocolo para JSON-RPC. Em geral, você ainda precisa do componente [hyperf/rpc-server](https://github.com/hyperf/rpc-server) ou [hyperf/rpc-client](https://github.com/hyperf/rpc-client) para atender aos cenários de cliente e servidor. Ambos precisam ser instalados se forem usados ao mesmo tempo:

Para servidor JSON-RPC:

```bash
composer require hyperf/rpc-server
```

Para cliente JSON-RPC:

```bash
composer require hyperf/rpc-client
```

## Instruções de uso

Os serviços têm dois papéis: `ServiceProvider`, que é um serviço que fornece serviços para outros serviços, e `ServiceConsumer`, que é um serviço que depende de outros serviços. Um serviço pode desempenhar os papéis de `ServiceProvider` e `ServiceConsumer` ao mesmo tempo. E ambos podem definir e restringir diretamente a chamada da interface por meio do `Service Contract`. No Hyperf, isso pode ser entendido como uma classe de interface `Interface`. Em geral, essa classe de interface aparece tanto no provider quanto no consumer.

### Definir service provider

Até o momento, apenas a forma de anotações é suportada para definir `ServiceProvider`, e versões futuras adicionarão mais formas de configuração.
Podemos definir diretamente uma classe por meio da anotação `#[RpcService]` e publicar este serviço:

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Note que se você quiser gerenciar o serviço através do service center, você precisa adicionar o atributo publishTo na anotação
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implementa um método de adição; considere simplesmente que os parâmetros são do tipo int
    public function add(int $a, int $b): int
    {
        // A implementação específica do método do serviço
        return $a + $b;
    }
}
```
 
`#[RpcService]` tem `4` parâmetros:  
O atributo `name` é o nome que define o serviço. Basta definir aqui um nome globalmente único. O Hyperf gerará um ID correspondente com base neste atributo e o registrará no service center;
O atributo `protocol` define o protocolo exposto pelo serviço. Atualmente, apenas `jsonrpc-http`, `jsonrpc` e `jsonrpc-tcp-length-check` são suportados, que correspondem ao protocolo HTTP e a dois protocolos sob TCP, respectivamente. O valor padrão é `jsonrpc-http`. O valor aqui corresponde à `key` do protocolo registrado em `Hyperf\Rpc\ProtocolManager`. Eles são essencialmente protocolo JSON-RPC; a diferença está no formato de dados, empacotamento de dados e transmissor de dados.
O atributo `server` é o `Server` carregado pela classe do serviço publicado, e o valor padrão é `jsonrpc-http`. Este atributo corresponde ao `name` em `servers` no arquivo `config/autoload/server.php`, o que também significa que precisamos definir um `Server` correspondente. Vamos detalhar isso no próximo capítulo;
O atributo `publishTo` define o service center para o qual o serviço será publicado. Atualmente suporta apenas `consul` ou null. Quando é null, significa que o serviço não será publicado no service center, o que também significa que você precisará lidar manualmente com a descoberta de serviços. Quando o valor é `consul`, você precisa configurar o componente [hyperf/consul](pt-br/consul.md). Para usar esta função, você precisa instalar o componente [hyperf/service-governance](https://github. com/hyperf/service-governance). Consulte a seção [Registro de serviço](pt-br/service-register.md) para detalhes.

> Para usar a anotação `#[RpcService]`, é necessário o namespace `use Hyperf\RpcServer\Annotation\RpcService;`.

#### Definir servidor JSON-RPC

Servidor HTTP (protocolo `jsonrpc-http`)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // As outras configurações do arquivo são omitidas aqui
    'servers' => [
        [
            'name' => 'jsonrpc-http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9504,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [\Hyperf\JsonRpc\HttpServer::class, 'onRequest'],
            ],
        ],
    ],
];
```

Servidor TCP (protocolo `jsonrpc`)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // As outras configurações do arquivo são omitidas aqui
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true,
                'package_eof' => "\r\n",
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

Servidor TCP (protocolo `jsonrpc-tcp-length-check`)

O protocolo atual é uma extensão do `jsonrpc`, e os usuários podem facilmente ajustar o `settings` correspondente para usar este protocolo. Exemplo:

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // As outras configurações do arquivo são omitidas aqui
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_length_check' => true,
                'package_length_type' => 'N',
                'package_length_offset' => 0,
                'package_body_offset' => 4,
                'package_max_length' => 1024 * 1024 * 2,
            ],
        ],
    ],
];
```

### Publicar no service center
   
Atualmente, só é suportado publicar serviços no `consul`; outros service centers serão adicionados no futuro.
Publicar serviços no `consul` também é bem simples no Hyperf. Instale o componente Consul via `composer require hyperf/consul` (se já estiver instalado, você pode ignorar este passo) e então configure o `Consul` no arquivo `config/autoload/consul.php`. Exemplo:

```php
<?php

return [
    'uri' => 'http://127.0.0.1:8500',
];
```

Depois que a configuração for concluída, quando o serviço iniciar, o Hyperf registrará automaticamente no service center o serviço definido com `publishTo` como `consul` via `#[RpcService]`.

> Atualmente, apenas os protocolos `jsonrpc` e `jsonrpc-http` são suportados para publicação no service center; os outros protocolos ainda não implementaram o registro de serviço

### Definir service consumers

Um `ServiceConsumer` pode ser considerado uma classe de cliente. No Hyperf, você não precisa lidar com conexão e requisições; você só precisa realizar algumas configurações de autenticação.

#### Criar automaticamente classe consumer por proxy

Você pode criar automaticamente classes consumer via proxy dinâmico fazendo uma configuração simples no arquivo `config/autoload/services.php`.

```php
<?php
return [
    'consumers' => [
        [
             // name deve ser o mesmo que o atributo name do service provider
            'name' => 'CalculatorService',
             // Nome da interface do serviço. É opcional e o valor padrão é igual ao valor configurado em name. Se name for definido diretamente como uma classe de interface, você pode ignorar esta configuração. Se name for uma string, você precisa configurar service para corresponder à classe de interface
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
             // Objeto de container correspondente. É opcional e o valor padrão é igual ao valor da configuração de service. Para definir a chave da injeção de dependência.
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
             // O acordo de serviço do service provider. É opcional e o valor padrão é jsonrpc-http
            // jsonrpc-http, jsonrpc e jsonrpc-tcp-length-check estão disponíveis
            'protocol' => 'jsonrpc-http',
             // Algoritmo de balanceamento de carga, opcional, o valor padrão é random
            'load_balancer' => 'random',
             // De qual service center o consumer obterá informações de nó; se não for configurado, as informações não serão obtidas do service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
             // Se a configuração registry acima não for especificada, significa consumir diretamente o nó especificado. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
             // Configurações; isso pode afetar o Packer e o Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                 // Protocolo diferente, configuração diferente
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                 // Contagem de tentativas; o valor padrão é 2; nenhuma tentativa será realizada quando o pacote for recebido fora do tempo. Atualmente suporta apenas JsonRpcPoolTransporter.
                'retry_count' => 2,
                 // Intervalo de tentativa, em milissegundos
                'retry_interval' => 100,
                 // A configuração a seguir será usada ao usar o JsonRpcPoolTransporter
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 32,
                    'connect_timeout' => 10.0,
                    'wait_timeout' => 3.0,
                    'heartbeat' => -1,
                    'max_idle_time' => 60.0,
                ],
            ],
        ]
    ],
];
```

O objeto proxy da classe cliente é criado automaticamente quando a aplicação inicia, e o valor do item de configuração `id` é usado no container (se não for definido, usa-se o valor do item `service`) para adicionar o vínculo. Assim como uma classe cliente escrita manualmente, o cliente pode ser usado diretamente injetando a interface `CalculatorServiceInterface`.

> Quando o service provider usa o nome da classe de interface como nome do serviço publicado, no service consumer você só precisa definir o item `name` como o nome da classe de interface, sem precisar definir repetidamente os itens `id` e `service`.

#### Criar classes consumer manualmente

Se você tiver mais requisitos para classes consumer, você pode criar manualmente uma classe consumer para atender. Basta definir uma classe e atributos relacionados.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * Define o nome do serviço do service provider correspondente
     * @var string 
     */
    protected $serviceName = 'CalculatorService';
    
    /**
     * Define o protocolo do service provider correspondente
     * @var string 
     */
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

Depois, você precisa definir um item no arquivo de configuração para obter informações de nós a partir de qual service center. O arquivo fica em `config/autoload/services.php` (se não existir, você pode criá-lo)

```php
<?php
return [
    'consumers' => [
        [
            // $serviceName correspondente à classe consumer
            'name' => 'CalculatorService',
            // De qual service center o consumer obterá as informações de nó. Se não for configurado, as informações não serão obtidas do service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // Se a configuração registry acima não for especificada, significa consumir diretamente o nó especificado. Configure as informações de nó do service provider por meio do parâmetro nodes abaixo
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```


Dessa forma, podemos usar a classe `CalculatorService` para consumir o serviço. Para tornar essa lógica de relacionamento mais razoável, a relação entre `CalculatorServiceInterface` e `CalculatorServiceConsumer` também deve ser definida em `config/autoload/dependencies.php`. Exemplos:

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

Dessa forma, o cliente pode ser usado injetando a interface `CalculatorServiceInterface`.

#### Reutilização de configuração

Em geral, um service consumer consumirá vários service providers ao mesmo tempo. Quando descobrimos service providers via service center, a configuração `registry` no arquivo `config/autoload/services.php` pode acabar sendo repetida. Porém, nosso service center pode ser único — ou seja, múltiplos service consumers são configurados para obter informações de nós do mesmo service center. Nesse caso, podemos usar código PHP (como `variáveis` ou `loops`) para gerar o arquivo de configuração.

##### Gerar configuração por variáveis PHP

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // O FooService e o BarService a seguir são apenas exemplos de múltiplos serviços e não existem de fato nos exemplos do documento
    'consumers' => [
        [
            'name' => 'FooService',
            'registry' => $registry,
        ],
        [
            'name' => 'BarService',
            'registry' => $registry,
        ]
    ],
];
```

##### Gerar configuração por loop

```php
<?php
return [
    'consumers' => value(function () {
        $consumers = [];
        // Este exemplo cria automaticamente a forma de configuração da classe consumer proxy. Existem dois itens de configuração - name e service. Este não é o único método. Apenas para explicar que a configuração pode ser gerada via código PHP
        // O FooServiceInterface e o BarServiceInterface a seguir são apenas exemplos de múltiplos serviços e não existem de fato nos exemplos do documento
        $services = [
            'FooService' => App\JsonRpc\FooServiceInterface::class,
            'BarService' => App\JsonRpc\BarServiceInterface::class,
        ];
        foreach ($services as $name => $interface) {
            $consumers[] = [
                'name' => $name,
                'service' => $interface,
                'registry' => [
                   'protocol' => 'consul',
                   'address' => 'http://127.0.0.1:8500',
                ]
            ];
        }
        return $consumers;
    }),
];
```

### Retornar objeto PHP

Quando o framework inclui `symfony/serializer (^5.0)` e `symfony/property-access (^5.0)`, configure o mapeamento em `dependencies.php`

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` dará suporte à serialização e desserialização de objetos. Atualmente, este tipo de array de objetos `MathValue[]` não é suportado.

Defina o objeto de retorno

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

class MathValue
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
```

Reescreva o arquivo de interface

```php
<?php

declare(strict_types=1);

namespace App\JsonRpc;

interface CalculatorServiceInterface
{
    public function sum(MathValue $v1, MathValue $v2): MathValue;
}
```

Chame no controller

```php
<?php

use Hyperf\Context\ApplicationContext;
use App\JsonRpc\CalculatorServiceInterface;
use App\JsonRpc\MathValue;

$client = ApplicationContext::getContainer()->get(CalculatorServiceInterface::class);

/** @var MathValue $result */
$result = $client->sum(new MathValue(1), new MathValue(2));

var_dump($result->value);
```

### Usar JsonRpcPoolTransporter

O framework fornece um `Transporter` baseado em pool de conexões, que pode evitar efetivamente o problema de estabelecer conexões demais em alta concorrência. Aqui você pode usar `JsonRpcPoolTransporter` para substituir `JsonRpcTransporter`.

Modifique o arquivo `dependencies.php`

```php
<?php

declare(strict_types=1);

use Hyperf\JsonRpc\JsonRpcPoolTransporter;
use Hyperf\JsonRpc\JsonRpcTransporter;

return [
    JsonRpcTransporter::class => JsonRpcPoolTransporter::class,
];

```
