# ConfigProvider 机制

ConfigProvider 机制对于 Hyperf 组件化来说是个非常重要的机制，`组件间的解耦` 和 `组件的独立性` 以及 `组件的可重用性` 都是基于这个机制才得以实现。   

# 什么是 ConfigProvider 机制 ？

简单来说，就是每个组件都会提供一个 `ConfigProvider`，通常是在组件的根目录提供一个 `ConfigProvider` 的类，`ConfigProvider` 会提供对应组件的所有配置信息，这些信息都会被 Hyperf 框架在启动时加载，最终`ConfigProvider` 内的配置信息会被合并到 `Hyperf\Contract\ConfigInterface` 对应的实现类去，从而实现各个组件在 Hyperf 框架下使用时要进行的配置初始化。   

`ConfigProvider` 本身不具备任何依赖，不继承任何的抽象类和不要求实现任何的接口，只需提供一个 `__invoke` 方法并返回一个对应配置结构的数组即可。

# 如何定义一个 ConfigProvider ？

通常来说，`ConfigProvider` 会定义在组件的根目录下，一个 `ConfigProvider` 类通常如下：

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 合并到  config/autoload/dependencies.php 文件
            'dependencies' => [],
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 默认 Command 的定义，合并到 Hyperf\Contract\ConfigInterface 内，换个方式理解也就是与 config/autoload/commands.php 对应
            'commands' => [],
            // 与 commands 类似
            'listeners' => [],
            // 组件默认配置文件，即执行命令后会把 source 的对应的文件复制为 destination 对应的的文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'description of this config file.', // 描述
                    // 建议默认配置放在 publish 文件夹中，文件命名和组件名称相同
                    'source' => __DIR__ . '/../publish/file.php',  // 对应的配置文件路径
                    'destination' => BASE_PATH . '/config/autoload/file.php', // 复制为这个路径下的该文件
                ],
            ],
            // 亦可继续定义其它配置，最终都会合并到与 ConfigInterface 对应的配置储存器中
        ];
    }
}
```

## 默认配置文件说明

在 `ConfigProvider` 中定义好 `publish` 后，可以使用如下命令快速生成配置文件

```bash
php bin/hyperf.php vendor:publish 包名称
```

如包名称为 `hyperf/amqp`，可执行命令来生成 `amqp` 默认的配置文件
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

只创建一个类并不会被 Hyperf 自动的加载，您仍需在组件的 `composer.json` 添加一些定义，告诉 Hyperf 这是一个 ConfigProvider 类需要被加载，您需要在组件内的 `composer.json` 文件内增加 `extra.hyperf.config` 配置，并指定对应的 `ConfigProvider` 类的命名空间，如下所示：

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

定义了之后需执行 `composer install` 或 `composer update` 或 `composer dump-autoload` 等会让 Composer 重新生成 `composer.lock` 文件的命令，才能被正常读取。   

# ConfigProvider 机制的执行流程

关于 `ConfigProvider` 的配置并非一定就是这样去划分，这是一些约定成俗的格式，实际上最终如何来解析这些配置的决定权也在于用户，用户可通过修改 Skeleton 项目的 `config/container.php` 文件内的代码来调整相关的加载，也就意味着，`config/container.php` 文件决定了 `ConfigProvider` 的扫描和加载。

# 组件设计规范

由于 `composer.json` 内的 `extra` 属性在数据不被利用时无其它作用和影响，故这些组件内的定义在其它框架使用时，不会造成任何的干扰和影响，故`ConfigProvider` 是一种仅作用于 Hyperf 框架的机制，对其它没有利用此机制的框架不会造成任何的影响，这也就为组件的复用打下了基础，但这也要求在进行组件设计时，必须遵循以下规范：

- 所有类的设计都必须允许通过标准 `OOP` 的使用方式来使用，所有 Hyperf 专有的功能必须作为增强功能并以单独的类来提供，也就意味着在非 Hyperf 框架下仍能通过标准的手段来实现组件的使用；
- 组件的依赖设计如果可满足 [PSR 标准](https://www.php-fig.org/psr) 则优先满足且依赖对应的接口而不是实现类；如 [PSR 标准](https://www.php-fig.org/psr) 没有包含的功能，则可满足由 Hyperf 定义的契约库 [hyperf/contract](https://github.com/hyperf/contract) 内的接口时优先满足且依赖对应的接口而不是实现类；
- 对于实现 Hyperf 专有功能所增加的增强功能类，通常来说也会对 Hyperf 的一些组件有依赖，那么这些组件的依赖不应该写在 `composer.json` 的 `require` 项，而是写在 `suggest` 项作为建议项存在；
- 组件设计时不应该通过注解进行任何的依赖注入，注入方式应只使用 `构造函数注入` 的方式，这样同时也能满足在 `OOP` 下的使用；
- 组件设计时不应该通过注解进行任何的功能定义，功能定义应只通过 `ConfigProvider` 来定义； 
- 类的设计时应尽可能的不储存状态数据，因为这会导致这个类不能作为长生命周期的对象来提供，也无法很方便的使用依赖注入功能，这样会在一定程度下降低性能，状态数据应都通过 `Hyperf\Context\Context` 协程上下文来储存；
