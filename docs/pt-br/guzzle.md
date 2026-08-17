# Guzzle HTTP Client

O componente [hyperf/guzzle](https://github.com/hyperf/guzzle) é baseado no Guzzle para processamento com coroutine, e é substituído dentro do Guzzle através do client HTTP do Swoole como um driver de coroutine para conseguir a coroutinização do client HTTP.

## Instalação

```bash
composer require hyperf/guzzle
```

## Aplicação

Basta definir o `Hyperf\Guzzle\CoroutineHandler` deste componente no client Guzzle como um handler, para convertê-lo em uma operação com coroutine. Para facilitar a criação do objeto Guzzle em coroutine, fornecemos uma classe factory `Hyperf\Guzzle\ClientFactory` para criar o client de forma conveniente. O exemplo é o seguinte:

```php
<?php 
use Hyperf\Guzzle\ClientFactory;

class Foo {
    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;
    
    public function __construct(ClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }
    
    public function bar()
    {
        // $options is equivalent to the $config parameter of the GuzzleHttp\Client constructor
        $options = [];
        // $client is a coroutineized GuzzleHttp\Client object
        $client = $this->clientFactory->create($options);
    }
}
```

### Usando a versão ^7.0

A dependência do componente em relação ao `Guzzle` foi alterada de `^6.3` para `^6.3 | ^7.0`. A versão `^7.0` pode ser instalada por padrão, mas os seguintes componentes entrarão em conflito com `^7.0`:

- hyperf/metric

Você pode executar ativamente as ações a seguir para resolver os conflitos

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

Como essa biblioteca dependente depende de `guzzlehttp/guzzle-services`, que não suporta `^7.0`, isso não pode ser resolvido temporariamente.

## Usando configuração do Swoole

Às vezes queremos modificar diretamente a configuração do `Swoole`, então também fornecemos itens de configuração relacionados. Mas essa configuração não tem efeito no `Curl Guzzle client`, então use com cautela.

> Essa configuração substituirá a configuração original. Por exemplo, o timeout abaixo será substituído por 10.

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Guzzle\CoroutineHandler;
use GuzzleHttp\HandlerStack;

$client = new Client([
    'base_uri' => 'http://127.0.0.1:8080',
    'handler' => HandlerStack::create(new CoroutineHandler()),
    'timeout' => 5,
    'swoole' => [
        'timeout' => 10,
        'socket_buffer_size' => 1024 * 1024 * 2,
    ],
]);

$response = $client->get('/');

```

## Connection Pool

O Hyperf não implementa apenas o `Hyperf\Guzzle\CoroutineHandler`, mas também implementa o `Hyperf\Guzzle\PoolHandler` com base no `Hyperf\Pool\SimplePool`.

### Por quê

Há um limite máximo para o número de conexões TCP por host. Quando a nossa concorrência excede esse limite, a requisição não pode ser estabelecida normalmente. Além disso, há um TIME-WAIT após o encerramento da conexão TCP, então a conexão não pode ser liberada a tempo. Portanto, precisamos de um connection pool para gerenciar esse estágio, minimizar o impacto do TIME-WAIT, e permitir que as conexões TCP sejam reutilizadas.

### Aplicação

```php
<?php
use GuzzleHttp\Client;
use Hyperf\Coroutine\Coroutine;
use GuzzleHttp\HandlerStack;
use Hyperf\Guzzle\PoolHandler;
use Hyperf\Guzzle\RetryMiddleware;

$handler = null;
if (Coroutine::inCoroutine()) {
    $handler = make(PoolHandler::class, [
        'option' => [
            'max_connections' => 50,
        ],
    ]);
}

// Default retry middleware
$retry = make(RetryMiddleware::class, [
    'retries' => 1,
    'delay' => 10,
]);

$stack = HandlerStack::create($handler);
$stack->push($retry->getMiddleware(), 'retry');

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

Além disso, o framework também fornece o `HandlerStackFactory` para criar de forma conveniente o `$stack` acima.

```php
<?php
use Hyperf\Guzzle\HandlerStackFactory;
use GuzzleHttp\Client;

$factory = new HandlerStackFactory();
$stack = $factory->create();

$client = make(Client::class, [
    'config' => [
        'handler' => $stack,
    ],
]);
```

## Usando `ClassMap` para substituir o `GuzzleHttp\Client`

Se o componente de terceiros não fornecer uma interface que possa substituir o `Handler`, também podemos usar o `ClassMap` para substituir diretamente o `Client`, a fim de alcançar o objetivo de coroutinização do client.

> É claro, você também pode usar SWOOLE_HOOK para alcançar o mesmo objetivo.

O exemplo é o seguinte:

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // Omitted other unchanged codes

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // The corresponding Handler can choose CoroutineHandler or PoolHandler as needed
            $config['handler'] = HandlerStack::create($inCoroutine ? new CoroutineHandler() : null);
        } elseif ($inCoroutine && $config['handler'] instanceof HandlerStack) {
            $config['handler']->setHandler(new CoroutineHandler());
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler must be a callable');
        }

        // Convert the base_uri to a UriInterface
        if (isset($config['base_uri'])) {
            $config['base_uri'] = Psr7\uri_for($config['base_uri']);
        }

        $this->configureDefaults($config);
    }
}

```

config/autoload/annotations.php

```php
<?php

declare(strict_types=1);

use GuzzleHttp\Client;

return [
    'scan' => [
        // ...
        'class_map' => [
            Client::class => BASE_PATH . '/class_map/GuzzleHttp/Client.php',
        ],
    ],
];
```
