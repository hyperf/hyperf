# ConfigProvider mechanism

The ConfigProvider mechanism is a very important mechanism for Hyperf componentization，`Decoupling between components` and `Component independence` and `Component reusability` are based on this mechanism to be realized.

# What is ConfigProvider mechanism?

In brief, each component will provide a `ConfigProvider` class，usually `ConfigProvider` provides in the root of the component，`ConfigProvider` will provide all configuration information for the current component. This configuration information is loaded by the Hyperf framework at startup, finally, the configuration information in `ConfigProvider` will be merged into the implementation class corresponding to `Hyperf\Contract\ConfigInterface`, The `dependencies` information will be merged into `Hyperf\Di\Definition\DefinitionSource`. This allows the configuration initialization to be performed when the component is used under the Hyperf framework.

`ConfigProvider` itself does not have any dependencies, does not inherit any abstract classes and does not require any implementation of the interface, just provide a `__invoke` method and return an array of the corresponding configuration structure.

# How to define a ConfigProvider?

As usually，`ConfigProvider` will be defined in the root directory of the component, a `ConfigProvider` class is usually as follows：

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // Merge into config/dependencies.php
            'dependencies' => [],
            // Merge into config/autoload/annotations.php
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
            // Default Command definition，Merge into Hyperf\Contract\ConfigInterface，also means is to correspond to config/autoload/commands.php
            'commands' => [],
            // The listeners is similar to commands
            'listeners' => [],
        ];
    }
}
```

It will not to load automatically by Hyperf that if you just create a `ConfigProvider` class, you still need to add some definitions to the `composer.json` of component, telling Hyperf that this is a ConfigProvider class that needs to be loaded, you need to add `extra.hyperf.config` configuration to `composer.json` and specify the namespace of the `ConfigProvider` class as shown below:

```json
{
    "name": "hyperf/foo",
    "require": {
        "php": ">=7.2"
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

After the definition, you need to execute `composer install` or `composer update` or `composer dump-autoload`, etc., which will make Composer to regenerate the `composer.lock` file, then Hyperf could detects the configuration of ConfigProvider.

# Execution flow of the ConfigProvider mechanism

The configuration of `ConfigProvider` is not necessarily the way to divide it. This is a form of convention. In fact, the final decision on how to parse these configurations lies in the user，Users can adjust the relevant loading by modifying the code in the `config/container.php` file of the Skeleton project，This means that the `config/container.php` file determines the scanning and loading of `ConfigProvider`.

# Component design specification

Since the `extra` attribute in `composer.json` has no other effects and effects when the data is not used, the definitions in these components will not cause any interference or influence when used in other frameworks. A mechanism that only works on the Hyperf framework does not have any impact on other frameworks that do not use this mechanism. This lays the foundation for component reuse, but it also requires the following to be followed when designing components. specification:

- All classes must be designed to be used by the standard `OOP`. All Hyperf-specific features must be provided as enhancements and in separate classes, meaning that they can still pass standard under non-Hyperf frameworks. Means to achieve the use of components;
- The component's dependency design, if it satisfies [PSR standard](https://www.php-fig.org/psr), is prioritized and depends on the corresponding interface rather than the implementation class; eg [PSR standard](https://www .php-fig.org/psr) does not include functionality, which satisfies the interface in the contract library [Hyperf/contract](https://github.com/hyperf-cloud/contract) defined by Hyperf. Rely on the corresponding interface instead of the implementation class;
- For enhanced classes that implement Hyperf's proprietary features, there are usually dependencies on some components of Hyperf, so the dependencies of these components should not be written in the `require` entry of `composer.json`, but in The `suggust` item exists as a suggestion;
- Component design should not be done with annotations for any dependency injection. The injection method should only use the `constructor injection` method, which can also satisfy the use under `OOP`;
- Component design should not be defined by annotations. Function definitions should only be defined by `ConfigProvider`;
- Class design should not store state data as much as possible, because this will not provide this class as a long-life object, nor can it use the dependency injection function very conveniently, which will reduce performance to a certain extent, state data. Should be stored through the `Hyperf\Utils\Context` coroutine context;
