# Rastreamento de cadeia de chamadas

Na arquitetura de microsserviços, há muitos serviços devido à divisão, o que significa que uma requisição de negócio pode passar por pelo menos 3 ou 4 serviços — ou até dezenas. Nessa arquitetura, é extremamente difícil quando precisamos depurar um determinado problema. Então precisamos de um sistema de rastreamento de cadeia de chamadas para nos ajudar a exibir dinamicamente a cadeia de chamadas entre serviços, para que possamos localizar rapidamente o problema e também otimizar o serviço com base nas informações da cadeia.
No `Hyperf`, fornecemos o componente [hyperf/tracer](https://github.com/hyperf/tracer) para rastrear e analisar as chamadas de cada requisição que cruza a rede. Atualmente, os sistemas [Zipkin](https://zipkin.io/) e [Jaeger](https://www.jaegertracing.io/) são integrados conforme o protocolo [OpenTracing](https://opentracing.io). Os usuários também podem personalizar isso seguindo o protocolo OpenTracing.

## Instalação

### Via Composer

```bash
composer require hyperf/tracer
```

O componente [hyperf/tracer](https://github.com/hyperf/tracer) instala por padrão as dependências relacionadas ao [Zipkin](https://zipkin.io/). Se você quiser usar o [Jaeger](https://www.jaegertracing.io/), precisa executar o comando abaixo para instalar as dependências correspondentes:

```bash
composer require jonahgeorge/jaeger-client-php
```

### Adicionar configuração do componente

Se o arquivo não existir, execute o comando a seguir para adicionar o arquivo de configuração `config/autoload/opentracing.php`:

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## Uso

### Configuração

#### Habilitar rastreamento

Por padrão, ele fornece monitoramento de chamadas `Guzzle HTTP`, chamadas `Redis` e chamadas `DB` (ou processamento por aspectos `AOP`) para alcançar a propagação e o rastreamento da cadeia de chamadas. Esses rastreamentos não vêm habilitados por padrão. Você precisa modificar os itens `enable` no arquivo de configuração `config/autoload/opentracing.php` para habilitar o rastreamento de determinadas chamadas remotas.

```php
<?php

return [
    'enable' => [
        // enable the tracing of Guzzle HTTP calls
        'guzzle' => false,
        // enable the tracing of Redis calls
        'redis' => false,
        // enable the tracing of DB calls
        'db' => false,
    ],
];
```

Antes de começar a rastrear, precisamos selecionar o driver de Tracer a ser usado e configurar o Tracer.

#### Selecionar driver do tracer

O valor correspondente a `default` no arquivo de configuração é o nome do driver utilizado. A configuração específica do driver é definida no item `tracer`, usando o próprio driver como `key`.

```php
<?php

return [
    // Select the default Tracer driver, the selected Tracer name corresponds to the key defined under tracers
    'default' => env('TRACER_DRIVER', 'staging_zipkin'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Zipkin config
        'staging_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // another Zipkin config
        'producton_zipkin' => [
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
        ],
        // Jaeger config
        'jaeger' => [
            'driver' => \Hyperf\Tracer\Adapter\JaegerTracerFactory::class,
        ],
    ]
];
```

Observe que, como mostrado no exemplo de configuração, você pode configurar múltiplos conjuntos de drivers Zipkin ou Jaeger. Embora os sistemas subjacentes sejam os mesmos, as configurações específicas podem ser diferentes. Um cenário comum é querer taxa de amostragem de 100% no ambiente de testes, mas 1% no ambiente de produção: você pode configurar dois conjuntos de drivers e então selecionar drivers diferentes conforme variáveis de ambiente no item `default`.

#### Configurar Zipkin

Ao usar o Zipkin, adicione a configuração específica do Zipkin no item `tracer` do arquivo de configuração.

```php
<?php
use Zipkin\Samplers\BinarySampler;

return [
    // default Tracer
    'default' => env('TRACER_DRIVER', 'zipkin'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Zipkin drive config
        'zipkin' => [
            // current app config
            'app' => [
                'name' => env('APP_NAME', 'skeleton'),
                // If ipv6 and ipv6 are null, the component will automatically detect from the Server
                'ipv4' => '127.0.0.1',
                'ipv6' => null,
                'port' => 9501,
            ],
            'driver' => \Hyperf\Tracer\Adapter\ZipkinTracerFactory::class,
            'options' => [
                // the endpoint address of Zipkin service
                'endpoint_url' => env('ZIPKIN_ENDPOINT_URL', 'http://localhost:9411/api/v2/spans'),
                // Request timeout (in seconds)
                'timeout' => env('ZIPKIN_TIMEOUT', 1),
            ],
            // Sampler, track all requests by default
            'sampler' => BinarySampler::createAsAlwaysSample(),
        ],
    ],
];
```

#### Configurar Jaeger

Ao usar o Jaeger, adicione a configuração específica do Jaeger no item `tracer` do arquivo de configuração.

```php
<?php
use Hyperf\Tracer\Adapter\JaegerTracerFactory;
use const Jaeger\SAMPLER_TYPE_CONST;

return [
    // default Tracer
    'default' => env('TRACER_DRIVER', 'jaeger'),

    // Other configurations are omitted here in this example
    'enable' => [],

    'tracer' => [
        // Jaeger drive config
        'jaeger' => [
            'driver' => JaegerTracerFactory::class,
            // project name
            'name' => env('APP_NAME', 'skeleton'),
            'options' => [
                // Sampler, track all requests by default
                'sampler' => [
                    'type' => SAMPLER_TYPE_CONST,
                    'param' => true,
                ],
                // the address which should report to
                'local_agent' => [
                    'reporting_host' => env('JAEGER_REPORTING_HOST', 'localhost'),
                    'reporting_port' => env('JAEGER_REPORTING_PORT', 5775),
                ],
            ],
        ],
    ],
];
```

Mais configurações sobre o Jaeger podem ser encontradas [aqui](https://github.com/jonahgeorge/jaeger-client-php)].

#### Habilitar rastreamento de JsonRPC

O rastreamento de cadeia do JsonRPC não está na configuração unificada e, por enquanto, pertence à versão `Beta`.

Basta configurar `aspects.php` e adicionar o `Aspect` a seguir para habilitar.

> Dica: não se esqueça de adicionar o TraceMiddleware correspondente no lado oposto.

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### Habilitar rastreamento de corrotinas

O rastreamento de cadeia de corrotinas não está incluído na configuração unificada; é uma versão opcional da funcionalidade.

Basta configurar `aspects.php` e adicionar o `Aspect` a seguir para habilitar.

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### Configurar middleware ou listener

Depois de configurar o driver, você precisa configurar o middleware ou o listener de eventos do ciclo de requisição para coletar informações e habilitar a coleta.

- Adicionar middleware

Abra o arquivo `config/autoload/middlewares.php` e habilite o middleware no nó `http`.

```php
<?php

declare(strict_types=1);

return [
     'http' => [
         \Hyperf\Tracer\Middleware\TraceMiddleware::class,
     ],
];
```

- ou adicionar um listener

Abra o arquivo `config/autoload/listeners.php` e adicione o listener.

```php
<?php

declare(strict_types=1);

return [
     \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### Configurar tags de Span

Para alguns nomes de tags de Span que o Hyperf coleta automaticamente para rastreamento, você pode alterar o nome correspondente mudando a configuração das tags de Span. Basta adicionar a configuração `tags` no arquivo `config/autolaod/opentracing.php`. A configuração de referência é a seguinte. Se o item de configuração existir, o valor do item prevalece. Se não existir, prevalece o valor padrão do componente.

```php
return [
    'tags' => [
        // HTTP client (Guzzle)
        'http_client' => [
            'http.url' => 'http.url',
            'http.method' => 'http.method',
            'http.status_code' => 'http.status_code',
        ],
        // Redis client
        'redis' => [
            'arguments' => 'arguments',
            'result' => 'result',
        ],
        // database client (hyperf/database)
        'db' => [
            'db.query' => 'db.query',
            'db.statement' => 'db.statement',
            'db.query_time' => 'db.query_time',
        ],
    ]
];
```

### Substituir o sampler

O sampler padrão registra a cadeia de chamadas de todas as requisições, o que terá algum impacto na performance, especialmente no uso de memória. Então devemos rastrear a cadeia de chamadas apenas quando for necessário — e para isso precisamos substituir o sampler. É simples substituir o sampler: usando o Zipkin como exemplo, basta alterar o valor do item de configuração `opentracing.zipkin.sampler` para a instância do seu sampler, desde que seu sampler implemente a interface `Zipkin\Sampler`.

### Acessar o serviço de rastreamento de links da Alibaba Cloud

Ao usar o serviço de rastreamento de links da Alibaba Cloud, como o lado oposto também suporta o protocolo `Zipkin`, você pode modificar diretamente o valor de `endpoint_url` no arquivo `config/autoload/opentracing.php` para o endereço correspondente da `region` do Aliyun. O endereço específico pode ser obtido no serviço de rastreamento de links da Alibaba Cloud. Para mais detalhes, consulte o [documento de ajuda do Alibaba Cloud Link Tracking Service](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)

### Usar outros drivers de Tracer

Você também pode usar quaisquer outros drivers de Tracer que sigam o protocolo OpenTracing. No campo Driver, preencha qualquer classe que implemente `Hyperf\Tracer\Contract\NamedFactoryInterface`. Essa interface tem apenas a função `make()`: o parâmetro é o nome do driver, e ela deve retornar uma instância que implemente `OpenTracing\Tracer`.

## Referências
- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, tracking system for large-scale distributed systems](https://bigbully.github.io/Dapper-translation/)
