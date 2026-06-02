# Nacos

A `PHP` coroutine client for `Nacos`, perfectly combined with Hyperf's configuration center and microservice governance.

## Installation

```shell
composer require hyperf/nacos
```

### Publish configuration file

```shell
php bin/hyperf.php vendor:publish hyperf/nacos
```

```php
<?php

declare(strict_types=1);

return [
    // Developers who cannot use IP port format can directly configure url
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

## Services and Instances

The current component still retains the previously provided service registration function.

You only need to install the `hyperf/service-governance-nacos` component, and then configure the following listeners and custom processes.

`Hyperf\ServiceGovernanceNacos\Listener\MainWorkerStartListener`
`Hyperf\ServiceGovernanceNacos\Listener\OnShutdownListener`
`Hyperf\ServiceGovernanceNacos\Process\InstanceBeatProcess`

Then add the following configuration to listen for the `Shutdown` event:

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

## Alibaba Cloud Service Authentication

When using Alibaba Cloud's Nacos service, you may need to use AK and SK for authentication. The Nacos component supports it natively. We can easily add the corresponding configuration as follows:

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
