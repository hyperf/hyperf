# Rastreamento de cadeia de chamadas

Na arquitetura de microsserviços, haverá muitos serviços resultantes da divisão, o que significa que uma requisição de negócio pode passar por pelo menos 3 ou 4 serviços, podendo chegar a dezenas ou mais. Sob essa arquitetura, é extremamente difícil quando precisamos depurar um determinado problema. Precisamos, então, de um sistema de rastreamento de cadeia de chamadas para nos ajudar a exibir dinamicamente a cadeia de chamadas de serviço, para que possamos localizar rapidamente o problema, e também otimizar o serviço com base nas informações da cadeia.
No `Hyperf`, fornecemos o componente [hyperf/tracer](https://github.com/hyperf/tracer) para rastrear e analisar a chamada de cada requisição entre redes. Atualmente, o sistema [Zipkin](https://zipkin.io/) e o sistema [Jaeger](https://www.jaegertracing.io/) são integrados de acordo com o protocolo [OpenTracing](https://opentracing.io). Os usuários também podem personalizar isso seguindo o protocolo OpenTracing.

## Instalação

### Via Composer

```bash
composer require hyperf/tracer
```

O componente [hyperf/tracer](https://github.com/hyperf/tracer) já instala as dependências relacionadas ao [Zipkin](https://zipkin.io/) por padrão. Se você quiser usar o [Jaeger](https://www.jaegertracing.io/), você precisa executar o seguinte comando para instalar as dependências correspondentes:

```bash
composer require jonahgeorge/jaeger-client-php
```

### Adicionar configuração do componente

Se o arquivo não existir, execute o seguinte comando para adicionar o arquivo de configuração `config/autoload/opentracing.php`:

```bash
php bin/hyperf.php vendor:publish hyperf/tracer
```

## Uso

### Config

#### Habilitar rastreamento

Por padrão, ele fornece monitoramento de chamadas `Guzzle HTTP`, chamadas `Redis` e chamadas `DB`, ou processamento de aspecto `AOP` para viabilizar a propagação e o rastreamento da cadeia de chamadas. Esses rastreamentos não são habilitados por padrão. Você precisa modificar os itens `enable` no arquivo de configuração `config/autoload/opentracing.php` para habilitar o rastreamento de determinadas chamadas remotas.

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

Antes de começar a rastrear, precisamos selecionar o driver do Tracer a ser usado e configurar o Tracer.

#### Selecionar o driver do rastreador

O valor correspondente a `default` no arquivo de configuração é o nome do driver usado. A configuração específica do driver é definida no item `tracer`, usando o mesmo driver como `key`.

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

Note que, como mostrado no exemplo de configuração, você pode configurar múltiplos conjuntos de drivers Zipkin ou drivers Jaeger. Embora os sistemas subjacentes usados sejam os mesmos, suas configurações específicas podem ser diferentes. Um cenário comum é quando queremos uma taxa de amostragem de 100% no ambiente de teste, mas com uma taxa de amostragem de 1% no ambiente de produção; dois conjuntos de drivers podem ser configurados, e então diferentes drivers podem ser selecionados de acordo com as variáveis de ambiente no item `default`.

#### Configurando o Zipkin

Ao usar o Zipkin, adicione a configuração específica do Zipkin ao item `tracer` no arquivo de configuração.

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

#### Configurando o Jaeger

Ao usar o Jaeger, adicione a configuração específica do Jaeger ao item `tracer` no arquivo de configuração.

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

#### Configurando a habilitação de rastreamento do JsonRPC

O rastreamento de cadeia do JsonRPC não está na configuração unificada, e pertence temporariamente à versão `Beta`.

Só precisamos configurar `aspects.php` e adicionar o seguinte `Aspect` para habilitá-lo.

> Dica: Não se esqueça de adicionar o TraceMiddleware correspondente no lado oposto.

```php
<?php

return [
    Hyperf\Tracer\Aspect\JsonRpcAspect::class,
];
```

#### Configurando a habilitação de rastreamento de Coroutine

O rastreamento de cadeia de Coroutine não está incluído na configuração unificada; é uma versão opcional da funcionalidade.

Só precisamos configurar `aspects.php` e adicionar o seguinte `Aspect` para habilitá-lo.

```php
<?php

return [
    Hyperf\Tracer\Aspect\CoroutineAspect::class,
];
```

### Configurar middleware ou listener

Após configurar o driver, você precisa configurar o middleware ou o listener de evento de ciclo de requisição para coletar informações e habilitar a função de coleta.

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

- ou adicione um listener

Abra o arquivo `config/autoload/listeners.php` e adicione o listener.

```php
<?php

declare(strict_types=1);

return [
     \Hyperf\Tracer\Listener\RequestTraceListener::class,
];
```

### Configurar Span Tag

Para alguns nomes de Span Tag que o Hyperf coleta automaticamente para informações de rastreamento, você pode alterar o nome correspondente alterando a configuração de Span Tag. Basta adicionar a configuração `tags` no arquivo de configuração `config/autolaod/opentracing.php`. A configuração de referência é a seguinte. Se o item de configuração existir, o valor do item de configuração prevalecerá. Se o item de configuração não existir, prevalecerá o valor padrão do componente.

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

### Substituindo o sampler

O sampler padrão registra a cadeia de chamadas para todas as requisições, o que terá certo impacto no desempenho, especialmente no uso de memória. Então, só precisamos rastrear a cadeia de chamadas quando quisermos, e para isso precisamos substituir o sampler. É fácil substituir o sampler; tomando o Zipkin como exemplo, basta alterar o valor correspondente do item de configuração `opentracing.zipkin.sampler` para a instância do objeto do seu sampler, desde que o objeto do seu sampler implemente a classe de interface `Zipkin\Sampler`.

### Acesso ao serviço de rastreamento de cadeia da Alibaba Cloud

Quando estamos usando o serviço de rastreamento de cadeia da Alibaba Cloud, como o lado oposto também suporta o protocolo `Zipkin`, você pode modificar diretamente o valor de `endpoint_url` no arquivo de configuração `config/autoload/opentracing.php` para o endereço correspondente à sua `region` da Aliyun. O endereço específico pode ser obtido no serviço de rastreamento de cadeia da Alibaba Cloud. Para mais detalhes, consulte o [Documento de Ajuda do Serviço de Rastreamento de Cadeia da Alibaba Cloud](https://help.aliyun.com/document_detail/100031.html?spm=a2c4g.11186623.6.547.68f974dcZlg4Mv)

### Usando outros drivers de Tracer

Você também pode usar qualquer outro driver de Tracer que siga o protocolo OpenTracing. No campo Driver, preencha qualquer classe que implemente `Hyperf\Tracer\Contract\NamedFactoryInterface`. Essa interface possui apenas uma função `make()`, cujo parâmetro é o nome do driver, e precisa retornar uma instância que implemente `OpenTracing\Tracer`.

## Referência
- [Opentracing](https://opentracing.io)
- [Zipkin](https://zipkin.io/)
- [Jaeger](https://www.jaegertracing.io/)
- [Dapper, sistema de rastreamento para sistemas distribuídos de grande escala](https://bigbully.github.io/Dapper-translation/)
