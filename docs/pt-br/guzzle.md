# Cliente HTTP Guzzle

O componente [hyperf/guzzle](https://github.com/hyperf/guzzle) é baseado no Guzzle para processamento com corrotinas, substituindo o driver do Guzzle pelo cliente HTTP do Swoole como driver de corrotina para tornar o cliente HTTP compatível com corrotinas.

## Instalação

```bash
composer require hyperf/guzzle
```

## Uso

Basta definir o `Hyperf\Guzzle\CoroutineHandler` deste componente no cliente do Guzzle como handler para transformá-lo em uma operação com corrotina. Para facilitar a criação do objeto Guzzle com corrotina, disponibilizamos uma factory `Hyperf\Guzzle\ClientFactory` para criar o cliente de forma conveniente. Exemplo:

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

### Usar a versão ^7.0

A dependência do componente em `Guzzle` foi alterada de `^6.3` para `^6.3 | ^7.0`. A versão `^7.0` pode ser instalada por padrão, mas os seguintes componentes entrarão em conflito com `^7.0`:

- hyperf/metric

Você pode realizar a ação abaixo para resolver conflitos

```
composer require "promphp/prometheus_client_php:2.2.1"
```

- overtrue/flysystem-cos

Como a biblioteca dependente depende de `guzzlehttp/guzzle-services`, e ela não suporta `^7.0`, isso não pode ser resolvido temporariamente.

## Usar configuração do Swoole

Às vezes queremos modificar diretamente as configurações do `Swoole`, então também fornecemos itens de configuração relacionados. Porém, essa configuração não entra em vigor no `Curl Guzzle client`, então use com cuidado.

> Esta configuração substituirá a configuração original. Por exemplo, o timeout abaixo será substituído por 10.

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

## Pool de conexões

O Hyperf não apenas implementa `Hyperf\Guzzle\CoroutineHandler`, como também implementa `Hyperf\Guzzle\PoolHandler` com base em `Hyperf\Pool\SimplePool`.

### Por quê

Existe um limite máximo para a quantidade de conexões TCP por host. Quando nossa concorrência ultrapassa esse limite, a requisição não consegue ser estabelecida normalmente. Além disso, após o encerramento de uma conexão TCP há um TIME-WAIT, então a conexão não pode ser liberada a tempo. Por isso, precisamos de um pool de conexões para manter essa etapa, reduzir o impacto do TIME-WAIT e permitir o reuso de conexões TCP.

### Uso

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

Além disso, o framework também fornece `HandlerStackFactory` para criar o `$stack` acima de forma conveniente.

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

## Usar `ClassMap` para substituir `GuzzleHttp\Client`

Se o componente de terceiros não fornecer uma interface para substituir o `Handler`, também podemos usar o `ClassMap` para substituir diretamente o `Client` e alcançar o objetivo de tornar o cliente compatível com corrotinas.

> Claro, você também pode usar o SWOOLE_HOOK para alcançar o mesmo objetivo.

Exemplo:

class_map/GuzzleHttp/Client.php

```php
<?php
namespace GuzzleHttp;

use GuzzleHttp\Psr7;
use Hyperf\Guzzle\CoroutineHandler;
use Hyperf\Coroutine\Coroutine;

class Client implements ClientInterface
{
    // Omitidos outros códigos inalterados

    public function __construct(array $config = [])
    {
        $inCoroutine = Coroutine::inCoroutine();
        if (!isset($config['handler'])) {
            // O Handler correspondente pode escolher CoroutineHandler ou PoolHandler conforme necessário
            $config['handler'] = HandlerStack::create($inCoroutine ? new CoroutineHandler() : null);
        } elseif ($inCoroutine && $config['handler'] instanceof HandlerStack) {
            $config['handler']->setHandler(new CoroutineHandler());
        } elseif (!is_callable($config['handler'])) {
            throw new \InvalidArgumentException('handler must be a callable');
        }

        // Converte o base_uri para uma UriInterface
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
