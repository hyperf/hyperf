# 介绍

当您使用的是 [Hyperf-Skeleton](https://github.com/hyperf-cloud/hyperf-skeleton) 项目创建的项目时，或基于 [Hyperf-Installer](https://github.com/hyperf-cloud/hyperf-installer) 创建的项目，Hyperf 的所有配置文件均处于根目录下的 `config` 文件夹内，每个选项都有说明，您可以随时查看并熟悉有哪些选项可以使用。

# 配置文件结构
以下结构仅为 Hyperf-Skeleton 所提供的默认配置的情况下的结构，实际情况由于依赖或使用的组件的差异，文件会有差异。
```
config
├── autoload // 此文件夹内的配置文件会被配置组件自己加载，并以文件夹内的文件名作为第一个键值
│   ├── amqp.php  // 用于管理 AMQP 组件
│   ├── annotations.php // 用于管理注解
│   ├── aspects.php // 用于管理 AOP 切面
│   ├── cache.php // 用于管理缓存组件
│   ├── commands.php // 用于管理自定义命令
│   ├── config-center.php // 用于管理配置中心
│   ├── consul.php // 用于管理 Consul 客户端
│   ├── databases.php // 用于管理数据库客户端
│   ├── exceptions.php // 用于管理异常处理器
│   ├── listeners.php // 用于管理事件监听者
│   ├── logger.php // 用于管理日志
│   ├── middlewares.php // 用于管理中间件
│   ├── opentracing.php // 用于管理调用链追踪
│   ├── queue.php // 用于管理基于 Redis 实现的简易队列服务
│   └── redis.php // 用于管理 Redis 客户端
├── config.php // 用于管理用户或框架的配置，如配置相对独立亦可放于 autoload 文件夹内
├── container.php // 负责容器的初始化，作为一个配置文件运行并最终返回一个 Psr\Container\ContainerInterface 对象
├── dependencies.php // 用于管理 DI 的依赖关系和类对应关系
├── routes.php // 用于管理路由
└── server.php // 用于管理 Server 服务
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

Hyperf Config 组件提供了两种方式获取配置，通过 Config 对象获取和 `@Value` 注解获取。

#### 通过 Config 对象获取配置
这种方式要求你已经拿到了 Config 对象的实例，注入实例的细节可查阅 [依赖注入](../di/intro.md) 章节；
```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 通过 get(string $key, $default): mixed 方法获取 $key 所对应的配置，$key 值可以通过 . 连接符定位到下级数组，$default 则是当对应的值不存在时返回的默认值
$config->get($key，$default);
```

#### 通过 `@Value` 注解获取配置
这种方式要求当前的对象必须是通过 Hyperf DI 组件创建的，注入实例的细节可查阅 [依赖注入](../di/intro.md) 章节，示例中我们假设 `IndexController` 就是一个已经定义好的 Controller 类，Controller 类一定是由 DI 创建出来的；   
`@Value()` 内的字符串则对应到 `$config->get($key)` 内的 $key 参数，在创建该对象实例时，对应的配置会自动注入到定义的类属性中。

```php
class IndexController {
    
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

### 判断配置是否存在

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// 通过 has(): bool 方法判断对应的 $key 值是否存在于配置中，$key 值可以通过 . 连接符定位到下级数组
$config->has($key);
```

## 环境变量