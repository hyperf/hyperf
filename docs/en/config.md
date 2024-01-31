# Configuration

When you are using a project created by the hyperf/hyperf-skeleton project, all of Hyperf's configuration files are in the config folder under the root directory, and each option has Instructions, you can always check and be familiar with the options available.

# Installation

```bash
composer require hyperf/config
```

# The structure of configuration file

The following structure is only the structure in the case of the default configuration provided by Hyperf-Skeleton, and the actual situation will vary depending on the components that are dependent or used.
```
config
├── autoload // The configuration file in this folder will be loaded by the configuration component itself, and the file name in the folder will be the first key value.
│   ├── amqp.php  // Used to manage AMQP component
│   ├── annotations.php // Used to manage Annotation
│   ├── apollo.php // Used to manage Apollo Configuration Center
│   ├── aspects.php // Used to manage Aspect of AOP
│   ├── async_queue.php // Used to manage Async-Queue component
│   ├── cache.php // Used to manage Cache component
│   ├── commands.php // Used to manage Custom Command
│   ├── consul.php // Used to manage Consul Client
│   ├── databases.php // Used to manage Database
│   ├── dependencies.php // Used to manage the relationship of dependencies of DI
│   ├── devtool.php // Used to manage Dev-Tool
│   ├── exceptions.php // Used to manage Exception Handler
│   ├── listeners.php // Used to manage Event Listener
│   ├── logger.php // Used to manage Logger
│   ├── middlewares.php // Used to manage Middleware
│   ├── opentracing.php // Used to manage Open-Tracing
│   ├── processes.php // Used to manage Custom Process
│   ├── redis.php // Used to manage Redis Client
│   └── server.php // Used to manage Server
├── config.php // Configuration for managing users or frameworks, such as relatively independent configuration can also be placed in the autoload folder
├── container.php // Responsible for the initialization of the container, running as a configuration file and eventually returning a Psr\Container\ContainerInterface object
└── routes.php // Used to manage Routing
```

## Relationship between `config.php` and configuration files in the `autoload` folder

The configuration files in `autoload` folder and `config.php` will be scanned and injected into the corresponding object of `Hyperf\Contract\ConfigInterface` when the server starts. The configured structure is a large array of key-value pairs, The difference of two configuration form. The file name of the configuration file in `autoload` will exist as the first layer key, and the inside of `config.php` will be defined as the first layer. We use the following example to demonstrate it.
Let's assume there is a `config/autoload/client.php` file with the following contents:

```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```

Then we want to get the value of `timeout` corresponding to the key is `client.request.timeout`;

We assume that we want to get the same result with the same key, but the configuration is written in the `config/config.php` file, then the file content should look like this:

```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## Use Config Component of Hyperf

This component is the official default configuration component that is implemented for the `Hyperf\Contract\ConfigInterface` interface, which is defined by the [hyperf/config](https://github.com/hyperf/config) component. ` Bind the `Hyperf\Config\Config` object to the interface by the ConfigProvider of the component.

### Set configuration value

Configurations in the `config/config.php` and `config/autoload/server.php` and `autoload` folders can be scanned and injected into the corresponding object of `Hyperf\Contract\ConfigInterface` when the server starts. This process is done by `Hyperf\Config\ConfigFactory` when the Config object is instantiated.

### Get configuration value

The Config component provides three ways to get the configuration value, obtained through the `Hyperf\Config\Config` object, obtained via the `#[Value]` annotation, and obtained via the `config(string $key, $default)` function.

#### Get configuration value by Config Object

This way requires you already have an instance of the `Config` object. The default object is `Hyperf\Config\Config`. For details on the injection instance, refer to the [Dependency Injection](en/di.md) chapter.

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Get the configuration corresponding to $key by get(string $key, $default): mixed method, the $key value can be positioned to the subordinate array by the . connector, and $default is the default value returned when the corresponding value does not exist.
$config->get($key, $default);
```

#### Get Configuration by `#[Value]` Annotation

This way requires the object must be created by the [hyperf/di](https://github.com/hyperf/di) component. The details of the injection instance can be found in [Dependency Injection](en/di.md) chapter, in the example we assume that `IndexController` is an already defined `Controller` class, and the `Controller` class must be created by the `DI` container;
The string in `#[Value()]` corresponds to the `$key` parameter in `$config->get($key)`. When the object instance is created, the corresponding configuration is automatically injected into the defined class property.

```php
<?php
use Hyperf\Config\Annotation\Value;

class IndexController
{
    
    #[Value(key: "config.key")]
    private $configValue;
    
    public function index()
    {
        return $this->configValue;
    }
    
}
```

#### Get Configuration by config() function

The corresponding configuration can be obtained from the `config(string $key, $default)` function anywhere, but this way of using it means [hyperf/config](https://github.com/hyperf/config) and [hyperf/support](https://github.com/hyperf/support) components are strongly dependent for your application.

### Determine if the configuration exists

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// The has(): bool method is used to determine whether the corresponding $key value exists in the configuration, and the $key value can be mapped to the subordinate array by the . connector.
$config->has($key);
```

## Environmental variable

It is a common requirement to use different configurations for different operating environments. For example, the Redis configuration of the test environment and the production environment is different, and the configuration of the production environment cannot be submitted to the source code version management system to avoid information leakage.

In Hyperf we provide a solution for environment variables, using the environment variable parsing functionality provided by [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) and the `env()` function to get the environment. This requirement is quite easy to solve.

In a newly installed Hyperf application, its root directory will contain a `.env.example` file. In the case of Hyperf installed via Composer, the Composer will automatically copy a new file based on `.env.example` and name it `.env`. Otherwise, you will need to manually change the file name.

Your `.env` file should not be submitted to the application's source code version management system, as each developer/server using your application may need to have a different environment configuration. In addition, in the case of intruders gaining access to your source code repository, this can lead to serious security issues, because sensitive data is available at a glance.

> All variables in the `.env` file can be overridden by external environment variables (such as server-level or system-level or Docker environment variables).

### Environment variable type

All variables in the `.env` file are parsed as a string type, so some reserved values are provided to allow you to get more types of variables from the `env()` function:

| .env value | env() value |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

If you need to use environment variables that contain spaces, you can do so by enclosing the values in double quotes, such as:

```dotenv
APP_NAME="Hyperf Skeleton"
```

### Get environment variable

We also mentioned above that the environment variable can be obtained by the `env()` function. In application development, the environment variable should only be used as a value of the configuration, and the value of the environment variable is used to override the configured value. **Only use configuration** instead of use environment variables directly.
Let us give a reasonable example:

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## Configuration Center

Hyperf provides you with external configuration support for distributed systems, by default we offer an open source project by Ctrip namely [ctripcorp/apollo](https://github.com/ctripcorp/apollo), by [hyperf/config-apollo](https://github.com/hyperf/config-apollo) component provides functional support.
Details on the usage of the configuration center are explained in the [Configuration Center](en/config-center.md) chapter.


