# Configuration

When you are using a project created with the [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) project, all configuration files of Hyperf are located in the `config` folder under the root directory. Each option has a description, and you can view and familiarize yourself with the available options at any time.

# Installation

```bash
composer require hyperf/config
```

# Configuration File Structure

The following structure is only the structure under the default configuration provided by Hyperf-Skeleton. In actual situations, the files will differ due to differences in dependencies or components used.

```
config
├── autoload // Configuration files in this folder will be loaded by the configuration component itself, using the filename in the folder as the first key
│   ├── amqp.php  // Used to manage AMQP components
│   ├── annotations.php // Used to manage annotations
│   ├── apollo.php // Used to manage the configuration center based on Apollo
│   ├── aspects.php // Used to manage AOP aspects
│   ├── async_queue.php // Used to manage simple queue services based on Redis
│   ├── cache.php // Used to manage cache components
│   ├── commands.php // Used to manage custom commands
│   ├── consul.php // Used to manage Consul client
│   ├── databases.php // Used to manage database client
│   ├── dependencies.php // Used to manage DI dependencies and class mappings
│   ├── devtool.php // Used to manage developer tools
│   ├── exceptions.php // Used to manage exception handlers
│   ├── listeners.php // Used to manage event listeners
│   ├── logger.php // Used to manage logs
│   ├── middlewares.php // Used to manage middlewares
│   ├── opentracing.php // Used to manage call chain tracing
│   ├── processes.php // Used to manage custom processes
│   ├── redis.php // Used to manage Redis client
│   └── server.php // Used to manage Server services
├── config.php // Used to manage user or framework configurations. If the configuration is relatively independent, it can also be placed in the autoload folder
├── container.php // Responsible for container initialization, running as a configuration file and finally returning a Psr\Container\ContainerInterface object
└── routes.php // Used to manage routes
```

## `server.php` Configuration Description

The following are the default `settings` provided by `config/autoload/server.php` in Hyperf-Skeleton:

```php
<?php
declare(strict_types=1);

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Other configurations for this file are omitted here
    'settings' => [
        'enable_coroutine' => true, // Enable built-in coroutines
        'worker_num' => swoole_cpu_num(), // Set the number of Worker processes to start
        'pid_file' => BASE_PATH . '/runtime/hyperf.pid', // PID of the master process
        'open_tcp_nodelay' => true, // Disable the Nagle algorithm when sending data via TCP connection, immediately send to the client connection
        'max_coroutine' => 100000, // Set the maximum number of coroutines for the current worker process
        'open_http2_protocol' => true, // Enable HTTP2 protocol parsing
        'max_request' => 100000, // Set the maximum number of tasks for worker processes
        'socket_buffer_size' => 2 * 1024 * 1024, // Configure the buffer length for client connections
    ],
];
```

This configuration file is used to manage Server services. The `settings` options within can directly use the options provided by `Swoole Server`. For other options, please refer to the [Swoole official documentation](https://wiki.swoole.com/#/server/setting).

If you need to set up daemonization, you can add `'daemonize' => true` in `settings`. After executing `php bin/hyperf.php start`, the program will turn to the background and run as a daemon.

Individual Server configurations need to be added to the `settings` of the corresponding `servers`. For example, for the TCP Server configuration of the `jsonrpc` protocol, enable EOF automatic packet splitting and set the EOF string:

```php
<?php

use Hyperf\Server\Server;
use Hyperf\Server\Event;

return [
    // Other configurations for this file are omitted here
    'servers' => [
        [
            'name' => 'jsonrpc',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9503,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_RECEIVE => [\Hyperf\JsonRpc\TcpServer::class, 'onReceive'],
            ],
            'settings' => [
                'open_eof_split' => true, // Enable EOF automatic packet splitting
                'package_eof' => "\r\n", // Set EOF string
            ],
        ],
    ],
];
```

## Relationship Between `config.php` and Configuration Files in the `autoload` Folder

Both `config.php` and the configuration files in the `autoload` folder will be scanned at service startup and injected into the object corresponding to `Hyperf\Contract\ConfigInterface`. The structure of the configuration is a large array of key-value pairs. The difference between the two configuration forms is that the filename of the configuration file in `autoload` will exist as the first-level Key, while the one in `config.php` will use the key you defined as the first level. We will demonstrate this with the following example.
Suppose there is a `config/autoload/client.php` file with the following content:

```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```

Then the Key corresponding to the value of `timeout` is `client.request.timeout`;

Suppose we want to obtain the same result with the same Key, but the configuration is written in the `config/config.php` file, the file content should be as follows:

```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## Using the Hyperf Config Component

This component is the default configuration component provided officially. It is implemented oriented towards the `Hyperf\Contract\ConfigInterface` interface, and the `Hyperf\Config\Config` object is bound to the interface by the `ConfigProvider` within the [hyperf/config](https://github.com/hyperf/config) component.

### Setting Configuration

Configurations in `config/config.php`, `config/autoload/server.php`, and the `autoload` folder can all be scanned at service startup and injected into the object corresponding to `Hyperf\Contract\ConfigInterface`. This process is completed by `Hyperf\Config\ConfigFactory` when the Config object is instantiated.

### Getting Configuration

The Config component provides three ways to obtain configuration: through the `Hyperf\Config\Config` object, through the `#[Value]` annotation, and through the `config(string $key, $default)` function.

#### Getting Configuration via the Config Object

This method requires that you have already obtained an instance of the `Config` object. The default object is `Hyperf\Config\Config`. For details on injecting instances, please refer to the [Dependency Injection](di.md) chapter;

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Obtain the configuration corresponding to $key via the get(string $key, $default): mixed method. The $key value can be positioned to a lower-level array via the . connector, and $default is the default value returned when the corresponding value does not exist.
$config->get($key, $default);
```

#### Getting Configuration via `#[Value]` Annotation

This method requires that the annotation's application object must be created by the [hyperf/di](https://github.com/hyperf/di) component. For details on injecting instances, please refer to the [Dependency Injection](di.md) chapter. In the example, we assume that `IndexController` is a defined `Controller` class, and the `Controller` class must be created by the `DI` container;
The string inside `#[Value]` corresponds to the `$key` parameter in `$config->get($key)`. When creating an instance of this object, the corresponding configuration will be automatically injected into the defined class property.

```php
use Hyperf\Config\Annotation\Value;

class IndexController
{
    #[Value("config.key")]
    private $configValue;

    public function index()
    {
        return $this->configValue;
    }
}
```

#### Getting via `config` Function

In any place, you can obtain the corresponding configuration through the `config(string $key, $default)` function, but this usage method means that you have a strong dependency on the [hyperf/config](https://github.com/hyperf/config) and [hyperf/support](https://github.com/hyperf/support) components.

### Judging Whether a Configuration Exists

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Determine whether the corresponding $key value exists in the configuration via the has(): bool method. The $key value can be positioned to a lower-level array via the . connector
$config->has($key);
```

## Environment Variables

Using different configurations for different running environments is a common requirement, for example, the Redis configuration for the testing environment and the production environment are different, and the production environment configuration cannot be committed to the source code version management system to avoid information leakage.

In Hyperf, we provide an environment variable solution. By using the environment variable parsing function provided by [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) and the `env()` function to obtain the value of environment variables, this requirement is quite easy to solve.

In a newly installed Hyperf application, its root directory will contain a `.env.example` file. If Hyperf is installed via Composer, this file will automatically be copied based on `.env.example` and named `.env`. Otherwise, you need to change the file name manually.

Your `.env` file should not be committed to the application's source code version management system, because each developer/server using your application may need to have a different environment configuration. In addition, this could lead to serious security issues in the event that an intruder gains access to your source code repository, as all sensitive data is exposed.

> All variables in the `.env` file can be overridden by external environment variables (such as server-level, system-level, or Docker environment variables).

### Environment Variable Types

All variables in the `.env` file will be parsed as string types, so some reserved values are provided to allow you to obtain more types of variables from the `env()` function:

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

If you need to use environment variables that contain spaces or other special characters, you can achieve this by enclosing the value in double quotes, for example:

```dotenv
APP_NAME="Hyperf Skeleton"
```

### Reading Environment Variables

We mentioned above that environment variables can be obtained through the `env()` function. In application development, environment variables should only be used as a value for configuration, and the value of environment variables should be used to override the value of configuration. At the application layer, you should **only use configuration**, rather than directly using environment variables.
Here is an example of reasonable use:

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## Publishing Component Configuration

Hyperf adopts a component-based design. After adding some components to the skeleton project, we usually need to create corresponding configuration files for the newly added components to meet the needs of using the components. Hyperf provides a `component configuration publishing mechanism` for components. Through this mechanism, you only need to publish the preset configuration file templates of the components to the skeleton project via a `vendor:publish` command.
For example, if we want to add a `hyperf/foo` component (this component does not actually exist, just an example) and the configuration file corresponding to this component, after executing `composer require hyperf/foo` to install, you can publish the component's preset configuration file to the `config/autoload` folder of the skeleton project by executing `php bin/hyperf.php vendor:publish hyperf/foo`. The specific content to be published is defined and provided by the component.

## Configuration Center

Hyperf provides you with externalized configuration support for distributed systems. Currently, it supports `Apollo` open-sourced by Ctrip, Alibaba Cloud ACM application configuration management, ETCD, Nacos, and Zookeeper as configuration center support.
For details on the use of the configuration center, we will elaborate in the [Configuration Center](config-center.md) chapter.
