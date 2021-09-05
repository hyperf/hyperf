# Configuration provider

The ConfigProvider mechanism helps with `Decoupling between components`, `Component independence`, and `Component reusability`. It is thus very important for Hyperf componentization.

# What is the configuration provider mechanism?

Simply put each component provides a `ConfigProvider` class, usually in the root directory of the component. `ConfigProvider` exposes all configuration information of the corresponding component, which is then red by the Hyperf framework and injected into a corresponding `Hyperf\Contract\ConfigInterface` implementation.

`ConfigProvider` itself does not have any dependencies, does not inherit any abstract classes and does not require any interfaces to be implemented. It only needs to provide a `__invoke` method and return an array of corresponding configuration structures.

# How to define a config provider?

Generally speaking, `ConfigProvider` will be defined in the root directory of the component. A `ConfigProvider` class usually looks something like this:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // merge into the config/autoload/dependencies.php file
            'dependencies' => [],
            // merge into the config/autoload/annotations.php file
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // The definition of the default Command is merged into Hyperf\Contract\ConfigInterface. Another way of understanding is that it corresponds to config/autoload/commands.php
            'commands' => [],
            // similar to commands
            'listeners' => [],
            // Component default configuration file, after executing the command the file corresponding to source will be copied to the corresponding to destination
            'publish' => [
                [
                    'id' =>'config',
                    'description' =>'description of this config file.', // description
                    // It is recommended that the default configuration be placed in the publish folder, the file name is the same as the component name
                    'source' => __DIR__.'/../publish/file.php', // corresponding configuration file path
                    'destination' => BASE_PATH.'/config/autoload/file.php', // copy the file under this path
                ],
            ],
            // You can continue to define other configurations, and they will eventually be merged into the configuration memory corresponding to ConfigInterface
        ];
    }
}
```

## Default configuration file description

After defining the `publish` information in `ConfigProvider`, you can use the following command to quickly generate configuration files

```bash
php bin/hyperf.php vendor:publish package name
```

If the package name is `hyperf/amqp`, the command can be executed to generate the default configuration file of `amqp`
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Just creating a `ConfigProvider` class doesn't mean that it will be automatically loaded by Hyperf. You still need to tell Hyperf that this is a `ConfigProvider` class that needs to be loaded. Add the `extra.hyperf.config` configuration in the package's `composer.json` file and specify the namespace of the corresponding `ConfigProvider` class as shown below:

```json
{
    "name": "hyperf/foo",
    "require": {
        "php": ">=7.3"
    },
    "autoload": {
        "psr-4": {
            "Hyperf\\Foo\\": "src/"
        }
    },
    "extra": {
        "hyperf": {
            "config": "Hyperf\\Foo\\ConfigProvider"
        }
    }
}
```

After adding the definition, you need to execute a command that makes Composer regenerate the `composer.lock` file, such as `composer install`, `composer update` or `composer dump-autoload` to make sure that the config provider is found.

# Processing configuration providers

A given `ConfigProvider` is not necessarily structured this way and these examples simply illustrate common conventions. Ultimately, the final decision about how to parse a provided configuration is up to the user. You can modify the `config/container.php` of the skeleton project to adjust the configuration loading, as this file is responsible for defining how configuration is processed.

# Designing components

Since the `extra` property in `composer.json` is used only for providing metadata, it doesn't affect your application in any way unless that metadata is used explicitly by the application itself. What this means is that the `ConfigProvider` mechanism is something used only by the Hyperf framework and has no impact when the package is used by other frameworks or projects. This helps make the component more reusable, but there are also a few things to note regarding reusability when designing components:

- The design of all classes must allow the use of the standard `OOP` and the usage of all proprietary Hyperf functions must be wrapped and provided under interfaces to ensure interoperability when using the package outside of Hyperf;

- If a dependency of the component can utilize [PSR standard](https://www.php-fig.org/psr), the component should rely on the interface instead of the implementation class. If the functionality can't utilize a PSR standard interface, then it should define and utilize the interface in the Hyperf contract library [hyperf/contract](https://github.com/hyperf/contract);

- When the component utilizes some functionality provided by other Hyperf components, the dependencies should be declared under `suggest` and not `require` in `composer.json`;

- The component should not use annotations for any dependency injection. The injection should only use the `constructor injection` method to ensure that the dependency works with `OOP`;

- The component should not use annotations to define any functionality. Such functionality should be defined by `ConfigProvider`;

- Classes should avoid storing state data as much as possible to make objects long-lived. Retaining state does not play nice with the dependency injection model and will reduce the application performance as well as data integrity. Instead, data should be stored using the `Hyperf\Utils\Context` coroutine context;
