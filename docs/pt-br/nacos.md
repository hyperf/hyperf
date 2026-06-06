# Nacos

Um client de corrotina em `PHP` para `Nacos`, perfeitamente integrado ao centro de configuração e à governança de microsserviços do `Hyperf`.

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

Basta instalar o componente `hyperf/service-governance-nacos` e então configurar os seguintes listeners e processos customizados:

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

Em seguida, adicione a configuração abaixo para escutar o evento `Shutdown`.

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

## Autenticação do Aliyun Service

Ao usar o serviço Nacos da Aliyun, talvez você precise usar autenticação por AK e SK. O componente Nacos suporta isso nativamente. Podemos adicionar facilmente a configuração correspondente, conforme abaixo:

```php
<?php

declare(strict_types=1);
```php
return [
    // url do servidor nacos como https://nacos.hyperf.io, a prioridade é maior que host:port
    // 'uri' => 'http://127.0.0.1:8848/',
    // Informações de host do nacos
    'host' => '127.0.0.1',
    'port' => 8848,
    // Informações da conta do nacos
    'username' => null,
    'password' => null,
    'access_key' => 'xxxx',
    'access_secret' => 'yyyy',
    'guzzle' => [
        'config' => null,
    ],
];
```
