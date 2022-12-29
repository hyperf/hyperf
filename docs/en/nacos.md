#Nacos

A `PHP` coroutine client of `Nacos`, perfectly combined with the configuration center and microservice governance of `Hyperf`.

## Install

```shell
composer require hyperf/nacos
```

### publish profile

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

## Services and instances

The current component still retains the previously provided service registration functionality.

Just install the `hyperf/service-governance-nacos` component, then configure the following listeners and custom processes.

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

Then add the following configuration to listen to the `Shutdown` event

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

