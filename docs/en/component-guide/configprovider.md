# ConfigProvider Mechanism

The `ConfigProvider` mechanism is a crucial mechanism for Hyperf componentization. The decoupling between components, the independence of components, and the reusability of components are all achieved based on this mechanism.

# What is the ConfigProvider Mechanism?

Simply put, each component provides a `ConfigProvider`, usually as a class in the root directory of the component. The `ConfigProvider` provides all configuration information for the corresponding component. This information is loaded by the Hyperf framework during startup, and finally, the configuration information in the `ConfigProvider` is merged into the implementation class corresponding to `Hyperf\Contract\ConfigInterface`, thereby achieving the configuration initialization required when various components are used in the Hyperf framework.

The `ConfigProvider` itself has no dependencies, does not inherit any abstract classes, and does not require the implementation of any interfaces. It only needs to provide an `__invoke` method and return an array corresponding to the configuration structure.

# How to Define a ConfigProvider?

Typically, `ConfigProvider` is defined in the root directory of the component. A `ConfigProvider` class usually looks like this:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // Merged into the config/autoload/dependencies.php file
            'dependencies' => [],
            // Merged into the config/autoload/annotations.php file
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // Default Command definition, merged into Hyperf\Contract\ConfigInterface; in other words, it corresponds to config/autoload/commands.php
            'commands' => [],
            // Similar to commands
            'listeners' => [],
            // Component default configuration file; executing the command will copy the source file to the destination file
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'description of this config file.', // Description
                    // It is recommended to put default configuration in the publish folder, naming the file the same as the component name
                    'source' => __DIR__ . '/../publish/file.php',  // Corresponding configuration file path
                    'destination' => BASE_PATH . '/config/autoload/file.php', // Copy to this path as this file
                ],
            ],
            // You can continue to define other configurations, which will eventually be merged into the configuration container corresponding to ConfigInterface
        ];
    }
}
```

## Description of Default Configuration Files

After defining `publish` in `ConfigProvider`, you can use the following command to quickly generate the configuration file:

```bash
php bin/hyperf.php vendor:publish package_name
```

For example, if the package name is `hyperf/amqp`, you can execute the command to generate the default `amqp` configuration file:

```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Just creating a class will not be automatically loaded by Hyperf. You still need to add some definitions to the `composer.json` of the component to tell Hyperf that this is a `ConfigProvider` class that needs to be loaded. You need to add the `extra.hyperf.config` configuration to the `composer.json` file within the component and specify the namespace of the corresponding `ConfigProvider` class, as shown below:

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

After defining it, you need to execute commands that will cause Composer to regenerate the `composer.lock` file, such as `composer install`, `composer update`, or `composer dump-autoload`, for it to be read normally.

# Execution Flow of the ConfigProvider Mechanism

The configuration of `ConfigProvider` is not necessarily divided this way; this is a convention. In fact, the final decision on how to parse these configurations also rests with the user. The user can adjust the relevant loading by modifying the code in the `config/container.php` file of the Skeleton project, which means that the `config/container.php` file determines the scanning and loading of `ConfigProvider`.

# Component Design Specifications

Since the `extra` attribute in `composer.json` has no other function or impact when data is not utilized, the definitions in these components will not cause any interference or impact when used in other frameworks. Therefore, `ConfigProvider` is a mechanism that only acts on the Hyperf framework and will not have any impact on other frameworks that do not utilize this mechanism. This lays the foundation for component reuse, but this also requires that the following specifications must be followed when designing components:

- All class designs must allow usage through standard `OOP` usage methods. All proprietary Hyperf features must be provided as enhancement features in separate classes, which means that the component can still be used through standard means in non-Hyperf frameworks.
- If component dependency design can satisfy the [PSR Standards](https://www.php-fig.org/psr), prioritize satisfying them and depend on the corresponding interfaces rather than implementation classes. For functionalities not covered by [PSR Standards](https://www.php-fig.org/psr), prioritize satisfying the interfaces defined in the contract library [hyperf/contract](https://github.com/hyperf/contract) defined by Hyperf and depend on the corresponding interfaces rather than implementation classes.
- For enhancement feature classes added to implement Hyperf-proprietary functionalities, there is usually a dependency on some Hyperf components. The dependencies for these components should not be written in the `require` item of `composer.json`, but rather in the `suggest` item as suggested items.
- Component design should not perform any dependency injection through annotations; injection methods should only use `constructor injection`, which can also satisfy usage under `OOP`.
- Component design should not perform any functional definition through annotations; functional definition should only be defined through `ConfigProvider`.
- Class design should avoid storing state data as much as possible because this will cause the class to be unable to be provided as a long-lived object, and it will also be inconvenient to use dependency injection, which will reduce performance to a certain extent. State data should be stored through `Hyperf\Context\Context` coroutine context.
