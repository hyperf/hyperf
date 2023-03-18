# ConfigProvider mechanism

The ConfigProvider mechanism is a very important mechanism for Hyperf componentization. `Decoupling between components`, `Independence of components` and `Reusability of components` are all realized based on this mechanism.

# What is the ConfigProvider mechanism?

To put it simply, each component will provide a `ConfigProvider`, usually a `ConfigProvider` class is provided in the root directory of the component, and `ConfigProvider` will provide all the configuration information of the corresponding component, which will be started by the Hyperf framework When loaded, the final configuration information in `ConfigProvider` will be merged into the corresponding implementation class of `Hyperf\Contract\ConfigInterface`, so as to realize the configuration initialization of each component when used under the Hyperf framework.

`ConfigProvider` itself does not have any dependencies, does not inherit any abstract classes, and does not require the implementation of any interfaces. It only needs to provide an `__invoke` method and return an array of corresponding configuration structures.

# How to define a ConfigProvider?

Generally speaking, `ConfigProvider` will be defined in the root directory of the component, and a `ConfigProvider` class is usually as follows:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
     public function __invoke(): array
     {
         return [
             // merged into config/autoload/dependencies.php file
             'dependencies' => [],
             // merged into config/autoload/annotations.php file
             'annotations' => [
                 'scan' => [
                     'paths' => [
                         __DIR__,
                     ],
                 ],
             ],
             // The definition of the default Command is merged into Hyperf\Contract\ConfigInterface, another way to understand it is corresponding to config/autoload/commands.php
             'commands' => [],
             // similar to commands
             'listeners' => [],
             // Component default configuration file, that is, after executing the command, the file corresponding to source will be copied to the file corresponding to destination
             'publish' => [
                 [
                     'id' => 'config',
                     'description' => 'description of this config file.', // description
                     // It is recommended that the default configuration be placed in the publish folder, and the file name is the same as the component name
                     'source' => __DIR__ . '/../publish/file.php', // corresponding configuration file path
                     'destination' => BASE_PATH . '/config/autoload/file.php', // copy as the file under this path
                 ],
             ],
             // You can also continue to define other configurations, which will eventually be merged into the configuration storage corresponding to ConfigInterface
         ];
     }
}
```

## Default configuration file description

After defining `publish` in `ConfigProvider`, you can use the following command to quickly generate configuration files

```bash
php bin/hyperf.php vendor:publish package name
```

If the package name is `hyperf/amqp`, you can execute the command to generate the default configuration file of `amqp`
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Just creating a class will not be automatically loaded by Hyperf, you still need to add some definitions in the `composer.json` of the component to tell Hyperf that this is a ConfigProvider class that needs to be loaded, you need to add `composer.json` in the component Add `extra.hyperf.config` configuration in the file, and specify the namespace of the corresponding `ConfigProvider` class, as shown below:

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

After definition, you need to execute commands such as `composer install` or `composer update` or `composer dump-autoload` to allow Composer to regenerate the `composer.lock` file before it can be read normally.

# Execution process of ConfigProvider mechanism

The configuration of `ConfigProvider` is not necessarily divided in this way. This is some agreed format. In fact, the final decision on how to parse these configurations is also up to the user. The user can modify `config/container.php of the Skeleton project ` The code in the file to adjust the relevant loading, that is, the `config/container.php` file determines the scanning and loading of `ConfigProvider`.

# Component design specification

Since the `extra` attribute in `composer.json` has no other effect and influence when the data is not used, the definitions in these components will not cause any interference and influence when used by other frameworks, so `ConfigProvider` is A mechanism that only works on the Hyperf framework, and will not have any impact on other frameworks that do not use this mechanism, which lays the foundation for component reuse, but it also requires that the following must be followed when designing components specification:

- All classes must be designed to allow standard `OOP` usage, and all Hyperf-specific features must be provided as enhancements and in separate classes, which means they can still be used in non-Hyperf frameworks through standard means to realize the use of components;
- If the dependency design of the component can meet the [PSR standard](https://www.php-fig.org/psr), it will be satisfied first and depend on the corresponding interface instead of the implementation class; such as [PSR standard](https:// www.php-fig.org/psr) does not contain functions, then it can satisfy the interface in the contract library [Hyperf/contract](https://github.com/hyperf/contract) defined by Hyperf, which is satisfied first and depends on The corresponding interface rather than the implementation class;
- For the enhanced function classes added to implement Hyperf's proprietary functions, generally speaking, they also have dependencies on some components of Hyperf, so the dependencies of these components should not be written in the `require` item of `composer.json`, but write exists as a suggestion in the `suggest` item;
- Component design should not perform any dependency injection through annotations, and the injection method should only use `constructor injection`, which can also meet the use under `OOP`;
- Component design should not define any functions through annotations, and function definitions should only be defined through `ConfigProvider`;
- The design of the class should not store state data as much as possible, because this will cause the class not to be provided as an object with a long life cycle, and the dependency injection function cannot be easily used, which will reduce performance and state to a certain extent Data should all be stored through `Hyperf\Utils\Context` coroutine context;