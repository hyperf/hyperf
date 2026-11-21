# Serviço JSON RPC

JSON RPC é um padrão de protocolo RPC leve baseado no formato JSON, fácil de usar e ler. No Hyperf, ele é implementado pelo componente [hyperf/json-rpc](https://github.com/hyperf/json-rpc), que pode ser personalizado para transmissão baseada no protocolo HTTP, ou diretamente baseado no protocolo TCP para transmissão.

## Instalação

```bash
composer require hyperf/json-rpc
```
  
Este é apenas um componente de processamento de protocolo para JSON RPC. Geralmente, você ainda precisa do componente [hyperf/rpc-server](https://github.com/hyperf/rpc-server) ou [hyperf/rpc-client](https://github.com/hyperf/rpc-client) para atender aos cenários de client e server. Ambos precisam ser instalados se usados ao mesmo tempo:

Para o server JSON RPC:

```bash
composer require hyperf/rpc-server
```

Para o client JSON RPC:

```bash
composer require hyperf/rpc-client
```

## Instruções de uso

Os serviços possuem dois papéis: um é o `ServiceProvider`, que é um serviço que fornece serviços para outros serviços, e o outro é o `ServiceConsumer`, que é um serviço que depende de outros serviços. Um serviço pode desempenhar o papel de `ServiceProvider` e `ServiceConsumer` ao mesmo tempo. E esses dois podem definir e restringir diretamente a chamada da interface através do `Contrato de Serviço` (`Service Contract`). No Hyperf, isso pode ser entendido diretamente como uma classe de interface `Interface`. Em geral, essa classe de interface aparecerá tanto no provedor quanto no consumidor.

### Definindo o provedor de serviço

Até o momento, apenas a forma de Annotation é suportada para definir o `ServiceProvider`, e versões futuras adicionarão outras formas de configuração.
Podemos definir diretamente uma classe através da annotation `#[RpcService]` e publicar esse serviço:

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

/**
 * Note that if you want to manage the service through the service center, you need to add the publishTo attribute in the annotation
 */
#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an addition method, simply consider that the parameters are int type
    public function add(int $a, int $b): int
    {
        // The specific implementation of the service method
        return $a + $b;
    }
}
```
 
`#[RpcService]` possui `4` parâmetros:
O atributo `name` é o nome que define o serviço. Basta definir aqui um nome globalmente único. O Hyperf gerará um ID correspondente com base nesse atributo e o registrará no centro de serviços;
O atributo `protocol` define o protocolo exposto pelo serviço. Atualmente, apenas `jsonrpc-http`, `jsonrpc` e `jsonrpc-tcp-length-check` são suportados, correspondendo ao protocolo HTTP e a dois protocolos sob o protocolo TCP, respectivamente. O valor padrão é `jsonrpc-http`, e o valor aqui corresponde à `key` do protocolo registrado em `Hyperf\Rpc\ProtocolManager`. Eles são essencialmente protocolos JSON RPC, a diferença está na formatação de dados, no empacotamento de dados e no transmissor de dados.
O atributo `server` é o `Server` carregado pela classe de serviço publicada e vinculada; o valor padrão é `jsonrpc-http`. Esse atributo corresponde ao `name` sob `servers` no arquivo `config/autoload/server.php`, o que também significa que precisamos definir um `Server` correspondente; detalharemos como lidar com isso no próximo capítulo;
O atributo `publishTo` define o centro de serviços para o qual será publicado. Atualmente, suporta apenas `consul` ou null. Quando é null, significa que o serviço não será publicado no centro de serviços, o que também significa que você precisa lidar manualmente com a descoberta de serviços. Quando o valor é `consul`, você precisa configurar as configurações relevantes do componente [hyperf/consul](pt-br/consul.md). Para usar essa funcionalidade, você precisa instalar o componente [hyperf/service-governance](https://github.com/hyperf/service-governance); consulte a seção [Registro de Serviço](pt-br/service-register.md) para mais detalhes.

> Para usar a annotation `#[RpcService]`, é necessário o namespace `use Hyperf\RpcServer\Annotation\RpcService;`.

#### Definindo o Server JSON RPC

Server HTTP (adaptado ao protocolo `jsonrpc-http`)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

Server TCP (adaptado ao protocolo `jsonrpc`)

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

Server TCP (adaptado ao protocolo `jsonrpc-tcp-length-check`)

O protocolo atual é um protocolo estendido do `jsonrpc`, e os usuários podem facilmente modificar as `settings` correspondentes para usar esse protocolo. O exemplo é o seguinte:

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // The other configuration of the file is omitted here
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

### Publicando no centro de serviços
   
Atualmente, só é suportada a publicação de serviços no `consul`; outros centros de serviços serão adicionados no futuro.
Publicar serviços no `consul` também é muito fácil no Hyperf. Carregue o componente Consul através de `composer require hyperf/consul` (se já estiver instalado, você pode ignorar esta etapa), e depois configure suas configurações do `Consul` no arquivo de configuração `config/autoload/consul.php`; um exemplo é o seguinte:

```php
<?php

return [
    'uri' => 'http://127.0.0.1:8500',
];
```

Após a configuração ser concluída, quando o serviço for iniciado, o Hyperf registrará automaticamente o serviço, definido com o atributo `publishTo` como `consul` pela annotation `#[RpcService]`, no centro de serviços.

> Atualmente, apenas os protocolos `jsonrpc` e `jsonrpc-http` são suportados para publicação no centro de serviços; outros protocolos ainda não implementaram o registro de serviço

### Definindo consumidores de serviço

Um `ServiceConsumer` pode ser considerado como uma classe cliente. No Hyperf, você não precisa lidar com conexões e coisas relacionadas a requisições; você só precisa realizar algumas configurações de autenticação.

#### Criando automaticamente uma classe de proxy consumidora

Você pode criar automaticamente classes consumidoras através de proxy dinâmico fazendo algumas configurações simples no arquivo de configuração `config/autoload/services.php`.

```php
<?php
return [
    'consumers' => [
        [
            // name must be the same as the name attribute of the service provider
            'name' => 'CalculatorService',
            // Service interface name. It's optional and the default value is equal to the value configured by name. If name is directly defined as an interface class, you can ignore this configuration. If name is a string, you need to configure service to correspond to the interface class
            'service' => \App\JsonRpc\CalculatorServiceInterface::class,
            // Corresponding container object. It's optional and the default value is equal to the value of the service configuration. To define the key of dependency injection.
            'id' => \App\JsonRpc\CalculatorServiceInterface::class,
            // The service agreement of the service provider. It's optional and the default value is jsonrpc-http
            // jsonrpc-http, jsonrpc, and jsonrpc-tcp-length-check are available
            'protocol' => 'jsonrpc-http',
            // Load balancing algorithm, optional, the default value is random
            'load_balancer' => 'random',
            // From which service center the consumer will obtain node information, if it is not configured, the node information will not be obtained from the service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the registry configuration above is not specified, it means to directly consume the specified node. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
            // Configuration, this may affect Packer and Transporter
            'options' => [
                'connect_timeout' => 5.0,
                'recv_timeout' => 5.0,
                'settings' => [
                    // Different protocol, different configuration
                    'open_eof_split' => true,
                    'package_eof' => "\r\n",
                    // 'open_length_check' => true,
                    // 'package_length_type' => 'N',
                    // 'package_length_offset' => 0,
                    // 'package_body_offset' => 4,
                ],
                // Retrie count, the default value is 2, no retry will be performed when the packet is received over time. Only supports JsonRpcPoolTransporter, currently.
                'retry_count' => 2,
                // Retry interval, in milliseconds
                'retry_interval' => 100,
                // The following configuration will be used when using JsonRpcPoolTransporter
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

O objeto de proxy da classe cliente é criado automaticamente quando a aplicação inicia, e o valor do item de configuração `id` é usado no container (se não for definido, o valor do item de configuração `service` será usado no lugar) para adicionar a relação de binding. Assim como a classe cliente escrita manualmente, o client pode ser usado diretamente injetando a interface `CalculatorServiceInterface`.

> Quando o provedor de serviço usa o nome da classe de interface para publicar o nome do serviço, apenas o item de configuração `name` precisa ser definido como o nome da classe de interface no consumidor de serviço, e não é necessário definir novamente os itens de configuração `id` e `service`.

#### Criando manualmente classes consumidoras

Se você tem requisitos maiores para as classes consumidoras, pode criar manualmente uma classe consumidora para realizar isso. Basta definir uma classe e os atributos relacionados.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcClient\AbstractServiceClient;

class CalculatorServiceConsumer extends AbstractServiceClient implements CalculatorServiceInterface
{
    /**
     * Define the service name of the corresponding service provider
     * @var string 
     */
    protected $serviceName = 'CalculatorService';
    
    /**
     * Define the protocol of the corresponding service provider
     * @var string 
     */
    protected $protocol = 'jsonrpc-http';

    public function add(int $a, int $b): int
    {
        return $this->__request(__FUNCTION__, compact('a', 'b'));
    }
}
```

Depois, você precisa definir uma tag no arquivo de configuração para obter as informações do nó a partir de qual centro de serviços. O arquivo está localizado em `config/autoload/services.php` (se não existir, você pode criá-lo)

```php
<?php
return [
    'consumers' => [
        [
            // $serviceName corresponding to the consumer class
            'name' => 'CalculatorService',
            // From which service center the consumer will obtain node information. If it is not configured, the node information will not be obtained from the service center
            'registry' => [
                'protocol' => 'consul',
                'address' => 'http://127.0.0.1:8500',
            ],
            // If the registry configuration above is not specified, it means to directly consume the specified node. Configure the node information of the service provider through the nodes parameter below
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9504],
            ],
        ]
    ],
];
```


Desta forma, podemos usar a classe `CalculatorService` para realizar o consumo do serviço. Para tornar a lógica de relacionamento aqui mais coerente, a relação entre `CalculatorServiceInterface` e `CalculatorServiceConsumer` também deve ser definida em `config/autoload/dependencies.php`. Exemplos a seguir:

```php
return [
    App\JsonRpc\CalculatorServiceInterface::class => App\JsonRpc\CalculatorServiceConsumer::class,
];
```

Desta forma, o client pode ser usado injetando a interface `CalculatorServiceInterface`.

#### Reutilização de configuração

Geralmente, um consumidor de serviço consumirá múltiplos provedores de serviço ao mesmo tempo. Quando descobrimos os provedores de serviço através do centro de serviços, a configuração `registry` no arquivo `config/autoload/services.php` pode ser configurada repetidamente; no entanto, nosso centro de serviços pode ser unificado, o que significa que múltiplos consumidores de serviço são configurados para obter informações de nó do mesmo centro de serviços. Nesse momento, podemos implementar isso através de códigos PHP como `variáveis PHP` ou `loops` para gerar o arquivo de configuração.

##### Gerando configuração por variáveis PHP

```php
<?php
$registry = [
   'protocol' => 'consul',
   'address' => 'http://127.0.0.1:8500',
];
return [
    // The following FooService and BarService are only examples of multi-services, and they do not actually exist in the document examples
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

##### Gerando configuração por loop

```php
<?php
return [
    'consumers' => value(function () {
        $consumers = [];
        // This example automatically creates the configuration form of the proxy consumer class. There are two configuration items - name and service. This is not the only method. Just to explain that the configuration can be generated through PHP code
        // The following FooServiceInterface and BarServiceInterface are only examples of multi-services, and they do not actually exist in the document examples
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

### Retornando objeto PHP

Quando o framework importa `symfony/serializer (^5.0)` e `symfony/property-access (^5.0)`, configure a relação de mapeamento em `dependencies.php`

```php
use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\Serializer;

return [
    Hyperf\Contract\NormalizerInterface::class => new SerializerFactory(Serializer::class),
];
```

`NormalizerInterface` suportará serialização e deserialização de objetos. Este tipo de array de objetos `MathValue[]` não é suportado atualmente.

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

### Usando o JsonRpcPoolTransporter

O framework fornece um `Transporter` baseado em pool de conexões, que pode evitar de forma eficaz o problema de estabelecer conexões demais durante alta concorrência. Aqui você pode usar o `JsonRpcPoolTransporter` para substituir o `JsonRpcTransporter`.

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
