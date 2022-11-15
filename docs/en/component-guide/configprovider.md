# ConfigProvider mechanism

The ConfigProvider mechanism is a very important mechanism for Hyperf componentization. `Decoupling between components` and `component independence` and `component reusability` are all based on this mechanism.

# What is the ConfigProvider mechanism?

In short, each component will provide a `ConfigProvider`, usually a `ConfigProvider` class is provided in the root directory of the component, and `ConfigProvider` will provide all the configuration information of the corresponding component, which will be started by the Hyperf framework When loading, the configuration information in `ConfigProvider` will be merged into the corresponding implementation class of `Hyperf\Contract\ConfigInterface`, so as to realize the configuration initialization when each component is used under the Hyperf framework.

`ConfigProvider` itself does not have any dependencies, does not inherit any abstract classes and does not require to implement any interfaces, just provide a `__invoke` method and return an array of corresponding configuration structures.

# How to define a ConfigProvider?

Usually, `ConfigProvider` is defined in the root directory of the component. A `ConfigProvider` class is usually as follows:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // Merged into config/autoload/dependencies.php file
            'dependencies' => [],
            // Merged into config/autoload/annotations.php file
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // The definition of the default Command is merged into Hyperf\Contract\ConfigInterface and understood in another way, that is, it corresponds to config/autoload/commands.php
            'commands' => [],
            // similar to commands
            'listeners' => [],
            // The default configuration file of the component, that is, after executing the command, the corresponding file of source will be copied to the file corresponding to destination
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'description of this config file.', // describe
                    // It is recommended that the default configuration be placed in the publish folder with the same file name as the component name
                    'source' => __DIR__ . '/../publish/file.php',  // Corresponding configuration file path
                    'destination' => BASE_PATH . '/config/autoload/file.php', // Copy as the file under this path
                ],
            ],
            // You can also continue to define other configurations, which will eventually be merged into the configuration store corresponding to the ConfigInterface
        ];
    }
}
```

## Default Profile Description

After defining `publish` in `ConfigProvider`, you can use the following commands to quickly generate configuration files

```bash
php bin/hyperf.php vendor:publish 包名称
```

If the package name is `hyperf/amqp`, you can execute the command to generate the `amqp` default configuration file
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Just creating a class will not be loaded automatically by Hyperf, you still need to add some definitions to the component's `composer.json` to tell Hyperf that this is a ConfigProvider class that needs to be loaded, you need to add some definitions in the component's `composer.json` Add the `extra.hyperf.config` configuration to the file, and specify the corresponding namespace of the `ConfigProvider` class, as follows:

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

After the definition, you need to execute `composer install` or `composer update` or `composer dump-autoload` and other commands that will make Composer regenerate the `composer.lock` file before it can be read normally.   

# Execution process of the ConfigProvider mechanism

The configuration of `ConfigProvider` is not necessarily divided in this way. This is some conventional format. In fact, the final decision on how to parse these configurations is also up to the user. The user can modify the Skeleton project's `config/container.php' ` code in the file to adjust the relevant loading, which means that the `config/container.php` file determines the scanning and loading of the `ConfigProvider`.

# Component design specification

Since the `extra` property in `composer.json` has no other function and influence when the data is not used, the definitions in these components will not cause any interference and influence when other frameworks are used, so `ConfigProvider` is A mechanism that only acts on the Hyperf framework and will not have any impact on other frameworks that do not use this mechanism, which lays the foundation for the reuse of components, but it also requires the following when designing components. specification:

- All classes must be designed to allow use through standard `OOP` usage, all Hyperf-specific functionality must be provided as enhancements and provided as separate classes, which means that standard non-Hyperf frameworks can still pass the standard means to achieve the use of components;
- If the dependent design of a component can meet the [PSR standard](https://www.php-fig.org/psr), it will first satisfy and depend on the corresponding interface rather than the implementation class; such as [PSR standard](https://www.php-fig.org/psr) www.php-fig.org/psr) does not contain functions, it can satisfy the interface in the contract library [Hyperf/contract](https://github.com/hyperf/contract) defined by Hyperf first and depend on The corresponding interface instead of the implementing class;
- For the enhanced function classes that implement Hyperf's proprietary functions, usually also have dependencies on some components of Hyperf, so the dependencies of these components should not be written in the `require` item of `composer.json`, but write exists as a suggestion in the `suggest` item;
- Component design should not perform any dependency injection through annotations. The injection method should only use the `constructor injection` method, which can also meet the use under `OOP`;
- During component design, any function definition should not be carried out through annotations, and function definitions should only be defined through `ConfigProvider`;
- The class design should not store state data as much as possible, because this will cause the class to not be provided as an object with a long life cycle, and it will not be able to use the dependency injection function very conveniently, which will reduce performance and state to a certain extent. Data should be stored through the `Hyperf\Context\Context` coroutine context;