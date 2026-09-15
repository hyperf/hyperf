# Nacos

Um client `PHP` em coroutine para o `Nacos`, perfeitamente combinado com o configuration center e a governança de microsserviços do `Hyperf`.

## Instalação

```shell
composer require hyperf/nacos
```

### Publicar o arquivo de configuração

```shell
php bin/hyperf.php vendor:publish hyperf/nacos
```

```php
<?php

declare(strict_types=1);

return [
    // Developers who cannot use the IP port form can directly configure the url
    // 'url' => '',
    'host' => '127.0.0.1',
    'port' => 8848,
    'username' => null,
    'password' => null,
    'guzzle' => [
        'config' => null,
    ],
];

```

## Serviços e instâncias

O componente atual ainda mantém a funcionalidade de registro de serviços fornecida anteriormente.

Basta instalar o componente `hyperf/service-governance-nacos`, e então configurar os seguintes listeners e processos customizados.

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

Depois adicione a seguinte configuração para escutar o evento `Shutdown`

- config/autoload/server.php

```php
<?php
use Hyperf\Server\Event;
return [
    // ...other
    'callbacks' => [
        // ...other
        Event::ON_SHUTDOWN => [Hyperf\Framework\Bootstrap\ShutdownCallback::class, 'onShutdown']
    ]
];
```

## Autenticação de serviço do Aliyun

Ao usar o serviço Nacos do Aliyun, você pode precisar usar autenticação AK e SK. O componente Nacos suporta isso nativamente. Podemos facilmente adicionar a configuração correspondente, como a seguir:

```php
<?php

declare(strict_types=1);

return [
    // nacos server url like https://nacos.hyperf.io, Priority is higher than host:port
    // 'uri' => 'http://127.0.0.1:8848/',
    // The nacos host info
    'host' => '127.0.0.1',
    'port' => 8848,
    // The nacos account info
    'username' => null,
    'password' => null,
    'access_key' => 'xxxx',
    'access_secret' => 'yyyy',
    'guzzle' => [
        'config' => null,
    ],
];
```
