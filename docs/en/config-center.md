Hyperf provides you with external configuration support for distributed systems, which is adapted by default:

- [ctripcorp/apollo](https://github.com/ctripcorp/apollo) An open source project by Ctrip, by [hyperf/config-apollo](https://github.com/hyperf/config-apollo) component Provide functional support.
- Aliyun provides a free configuration center service [ACM (Application Config Manager)](https://help.aliyun.com/product/59604.html) by [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) component provides feature support.

## Why use the Configuration Center?

With the development of services, the upgrade of micro-service architecture, the number of services and the configuration of applications (various micro-services, various server addresses, various parameters), the traditional configuration file method and database method may not be satisfied. Developers' requirements for configuration management, as well as configuration management, may also involve ACL rights management, configuration version management and rollback, format verification, configuration grayscale publishing, cluster configuration isolation, etc., as well as:

- Security: Configuration follows the source code saved in the version management system, which is easy to cause configuration leakage
- Timeliness: Modify the configuration, each server needs to modify and restart the service for each application.
- Limitations: Dynamic adjustments cannot be supported, such as log switches, function switches, etc.

Therefore, we can manage the relevant configuration in a scientific management manner through a configuration center.

## Installation

### Apollo

```bash
composer require hyperf/config-apollo
```

### Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

## Use Apollo

If you have not replace the default configuration component, still use [hyperf/config](https://github.com/hyperf/config) component, adapte the Apollo Configuration Center is a breeze.
- By composer [hyperf/config-apollo](https://github.com/hyperf/config-apollo) , execute the command `composer require hyperf/config-apollo`
- Add a `apollo.php` configuration file to the `config/autoload` folder. The configuration is as follows:

```php
<?php
return [
    // Whether to enable the process of the configuration center. When true, a ConfigFetcherProcess process is automatically started to update the configuration
    'enable' => true,
    // Apollo Server
    'server' => 'http://127.0.0.1:8080',
    // Your AppId
    'appid' => 'test',
    // The cluster where the current application is located
    'cluster' => 'default',
    // Namespace that the current application needs to access, can be configured multiple namespcaes
    'namespaces' => [
        'application',
    ],
    // Strict mode. When the value is false, the configuration value that pulled from Apollo will always is string type, when the value is true, the configuration value will transfer to the suitable type according to the original value type on config container.
    'strict_mode' => false,
    // The interval of update configuration (seconds)
    'interval' => 5,
];
```

## Use Aliyun ACM

Accessing the Aliyun ACM Configuration Center is as easy as Apollo, just two steps.
- Execute the command `composer require hyperf/config-aliyun-acm` by Composer to install [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm)
- Add a `aliyun_acm.php` configuration file to the `config/autoload` folder. The configuration is as follows:

```php
<?php
return [
    // Whether to enable the process of the configuration center. When true, a ConfigFetcherProcess process is automatically started to update the configuration
    'enable' => true,
    // The interval of update configuration (seconds)
    'interval' => 5,
    // ACM endpoint address, depending on your Availability Zone
    'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    // Namespace that the current application needs to access
    'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
    // The Data ID of your configuration
    'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
    // The Group of your configuration
    'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
    // Your Access Key of aliyun account
    'access_key' => env('ALIYUN_ACM_AK', ''),
    // Your Secret Key of aliyun account
    'secret_key' => env('ALIYUN_ACM_SK', ''),
];
```

## The scope of the configuration update

In the default feature implementation, a `ConfigFetcherProcess` process pulls the corresponding `namespace` configuration from Configuration Center according to the configured `interval`, and passes the new configuration pulled to each worker through IPC communication, and Update to the object corresponding to `Hyperf\Contract\ConfigInterface`.
It should be noted that the updated configuration will only update the `Config` object, so it is only applicable to the application layer or business layer configuration. It does not involve the configuration changes of the framework layer. Because the configuration changes of the framework layer need to restart the service, if you have such a Requirements can also be achieved by implementing `ConfigFetcherProcess` on its own.

## Configure update event

During the running of the configuration center, if the configuration changes, the `Hyperf\ConfigCenter\Event\ConfigChanged` event will be triggered correspondingly. You can monitor these events to meet your needs.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\ConfigCenter\Event\ConfigChanged;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ConfigChanged::class,
        ];
    }

    public function process(object $event)
    {
        var_dump($event);
    }
}
```
