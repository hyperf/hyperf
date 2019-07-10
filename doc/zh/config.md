# 配置

当您使用的是 [hyperf-cloud/hyperf-skeleton](https://github.com/hyperf-cloud/hyperf-skeleton) 项目创建的项目时，或基于 [hyperf-cloud/installer](https://github.com/hyperf-cloud/installer) 创建的项目，Hyperf 的所有配置文件均处于根目录下的 `config` 文件夹内，每个选项都有说明，您可以随时查看并熟悉有哪些选项可以使用。

# 安装

```bash
composer require hyperf/config
```

# 配置文件结构

以下结构仅为 Hyperf-Skeleton 所提供的默认配置的情况下的结构，实际情况由于依赖或使用的组件的差异，文件会有差异。
```
config
├── autoload // 此文件夹内的配置文件会被配置组件自己加载，并以文件夹内的文件名作为第一个键值
│   ├── amqp.php  // 用于管理 AMQP 组件
│   ├── annotations.php // 用于管理注解
│   ├── apollo.php // 用于管理基于 Apollo 实现的配置中心
│   ├── aspects.php // 用于管理 AOP 切面
│   ├── async_queue.php // 用于管理基于 Redis 实现的简易队列服务
│   ├── cache.php // 用于管理缓存组件
│   ├── commands.php // 用于管理自定义命令
│   ├── consul.php // 用于管理 Consul 客户端
│   ├── databases.php // 用于管理数据库客户端
│   ├── devtool.php // 用于管理开发者工具
│   ├── exceptions.php // 用于管理异常处理器
│   ├── listeners.php // 用于管理事件监听者
│   ├── logger.php // 用于管理日志
│   ├── middlewares.php // 用于管理中间件
│   ├── opentracing.php // 用于管理调用链追踪
│   ├── processes.php // 用于管理自定义进程
│   ├── redis.php // 用于管理 Redis 客户端
│   └── server.php // 用于管理 Server 服务
├── config.php // 用于管理用户或框架的配置，如配置相对独立亦可放于 autoload 文件夹内
├── container.php // 负责容器的初始化，作为一个配置文件运行并最终返回一个 Psr\Container\ContainerInterface 对象
├── dependencies.php // 用于管理 DI 的依赖关系和类对应关系
└── routes.php // 用于管理路由
```

## `config.php` 与 `autoload` 文件夹内的配置文件的关系

`config.php` 与 `autoload` 文件夹内的配置文件在服务启动时都会被扫描并注入到 `Hyperf\Contract\ConfigInterface` 对应的对象中，配置的结构为一个键值对的大数组，两种配置形式不同的在于 `autoload`  内配置文件的文件名会作为第一层 键(Key) 存在，而 `config.php` 内的则以您定义的为第一层，我们通过下面的例子来演示一下。   
我们假设存在一个 `config/autoload/client.php` 文件，文件内容如下：
```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```
那么我们想要得到 `timeout` 的值对应的 键(Key) 为 `client.request.timeout`；   

我们假设想要以相同的 键(Key) 获得同样的结果，但配置是写在 `config/config.php` 文件内的，那么文件内容应如下：
```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## 使用 Hyperf Config 组件

该组件是官方提供的默认的配置组件，是面向 `Hyperf\Contract\ConfigInterface` 接口实现的，由 [hyperf/config](https://github.com/hyperf-cloud/config) 组件内的 `ConfigProvider` 将 `Hyperf\Config\Config` 对象绑定到接口上。   

### 设置配置

只需在 `config/config.php` 与 `config/server.php` 与 `autoload` 文件夹内的配置，都能在服务启动时被扫描并注入到 `Hyperf\Contract\ConfigInterface` 对应的对象中，这个流程是由 `Hyperf\Config\ConfigFactory` 在 Config 对象实例化时完成的。

### 获取配置

Config 组件提供了三种方式获取配置，通过 `Hyperf\Config\Config` 对象获取、通过 `@Value` 注解获取和通过 `config(string $key, $default)` 函数获取。

#### 通过 Config 对象获取配置

这种方式要求你已经拿到了 `Config` 对象的实例，默认对象为 `Hyperf\Config\Config`，注入实例的细节可查阅 [依赖注入](zh/di.md) 章节；

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 通过 get(string $key, $default): mixed 方法获取 $key 所对应的配置，$key 值可以通过 . 连接符定位到下级数组，$default 则是当对应的值不存在时返回的默认值
$config->get($key，$default);
```

#### 通过 `@Value` 注解获取配置

这种方式要求注解的应用对象必须是通过 [hyperf/di](https://github.com/hyperf-cloud/di) 组件创建的，注入实例的细节可查阅 [依赖注入](zh/di.md) 章节，示例中我们假设 `IndexController` 就是一个已经定义好的 `Controller` 类，`Controller` 类一定是由 `DI` 容器创建出来的；   
`@Value()` 内的字符串则对应到 `$config->get($key)` 内的 `$key` 参数，在创建该对象实例时，对应的配置会自动注入到定义的类属性中。

```php
class IndexController
{
    
    /**
     * @Value("config.key")
     */
    private $configValue;
    
    public function index()
    {
        return $this->configValue;
    }
    
}
```

#### 通过 config 函数获取

在任意地方可以通过 `config(string $key, $default)` 函数获取对应的配置，但这样的使用方式也就意味着您对 [hyperf/config](https://github.com/hyperf-cloud/config) 和 [hyperf/utils](https://github.com/hyperf-cloud/utils) 组件是强依赖的。

### 判断配置是否存在

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 通过 has(): bool 方法判断对应的 $key 值是否存在于配置中，$key 值可以通过 . 连接符定位到下级数组
$config->has($key);
```

## 环境变量

对于不同的运行环境使用不同的配置是一种常见的需求，比如在测试环境和生产环境的 Redis 配置不一样，而生产环境的配置又不能提交到源代码版本管理系统中以免信息泄露。   

在 Hyperf 里我们提供了环境变量这一解决方案，通过利用 [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) 提供的环境变量解析功能，以及 `env()` 函数来获取环境变量的值，这一需求解决起来是相当的容易。   

在新安装好的 Hyperf 应用中，其根目录会包含一个 `.env.example` 文件。如果是通过 Composer 安装的 Hyperf，该文件会自动基于 `.env.example` 复制一个新文件并命名为 `.env`。否则，需要你手动更改一下文件名。   

您的 `.env` 文件不应该提交到应用的源代码版本管理系统中，因为每个使用你的应用的开发人员 / 服务器可能需要有一个不同的环境配置。此外，在入侵者获得你的源代码仓库的访问权的情况下，这会导致严重的安全问题，因为所以敏感的数据都被一览无余了。   

> `.env` 文件中的所有变量均可被外部环境变量所覆盖（比如服务器级或系统级或 Docker 环境变量）。

### 环境变量类型

`.env` 文件中的所有变量都会被解析为字符串类型，因此提供了一些保留值以允许您从 `env()` 函数中获取更多类型的变量：

| .env 值 | env() 值 |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

如果你需要使用包含空格的环境变量，可以通过将值括在双引号中来实现，比如：

```dotenv
APP_NAME="Hyperf Skeleton"
```

### 读取环境变量

我们在上面也有提到环境变量可以通过 `env()` 函数获取，在应用开发中，环境变量只应作为配置的一个值，通过环境变量的值来覆盖配置的值，对于应用层来说应 **只使用配置**，而不是直接使用环境变量。   
我们举个合理使用的例子：

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## 配置中心

Hyperf 为您提供了分布式系统的外部化配置支持，默认且仅适配了由携程开源的 [ctripcorp/apollo](https://github.com/ctripcorp/apollo)，由 [hyper/config-apollo](https://github.com/hyperf-cloud/config-apollo) 组件提供功能支持。   
关于配置中心的使用细节我们由 [配置中心](zh/config-center.md) 章节来阐述。


