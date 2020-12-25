# 常见问题

## `Inject` 或 `Value` 注解不生效

`2.0` 使用了构造函数中注入 `Inject` 和 `Value` 的功能，以下两种场景，可能会导致注入失效，请注意使用。

1. 原类没有使用 `Inject` 或 `Value`，但父类使用了 `Inject` 或 `Value`，且原类写了构造函数，同时又没有调用父类构造函数的情况。

这样就会导致原类不会生成代理类，而实例化的时候又调用了自身的构造函数，故没办法执行到父类的构造函数。
所以父类代理类中的方法 `__handlePropertyHandler` 就不会执行，那么 `Inject` 或 `Value` 注解就不会生效。

```php
class ParentClass {
    /**
     * @Inject
     * @var Service
     */
    protected $value;
}

class Origin extends ParentClass
{
    public function __construct() {}
}
```

2. 原类没有使用 `Inject` 或 `Value`，但 `Trait` 中使用了 `Inject` 或 `Value`。

这样就会导致原类不会生成代理类，故没办法执行构造函数里的 `__handlePropertyHandler`，所以 `Trait` 的 `Inject` 或 `Value` 注解就不会生效。

```php
trait OriginTrait {
    /**
     * @Inject
     * @var Service
     */
    protected $value;
}

class Origin
{
    use OriginTrait;
}
```

基于上述两种情况，可见 `原类` 是否生成代理类至关重要，所以，如果使用了带有 `Inject` 或 `Value` 的 `Trait` 和 `父类` 时，给原类添加一个 `Inject`，即可解决上述两种情况。

```php

use Hyperf\Contract\StdoutLoggerInterface;

trait OriginTrait {
    /**
     * @Inject
     * @var Service
     */
    protected $trait;
}

class ParentClass {
    /**
     * @Inject
     * @var Service
     */
    protected $value;
}

class Origin extends ParentClass
{
    use OriginTrait;

    /**
     * @Inject
     * @var StdoutLoggerInterface
     */
    protected $logger;
}
```

## Swoole 短名未关闭

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

您需要在您的 php.ini 配置文件增加 `swoole.use_shortname = 'Off'` 配置项

> 注意该配置必须于 php.ini 内配置，无法通过 ini_set() 函数来重写

当然，也可以通过以下的命令来启动服务，在执行 PHP 命令时关闭掉 Swoole 短名功能

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## 异步队列消息丢失

如果在使用 `async-queue` 组件时，发现 `handle` 中的方法没有执行，请先检查以下几种情况：

1. `Redis` 是否与其他人共用，消息被其他人消费走
2. 本地进程是否存在残余，被其他进程消费掉

以下提供万无一失的解决办法：

1. killall php
2. 修改 `async-queue` 配置 `channel`

## 使用 AMQP 组件报 `Swoole\Error: API must be called in the coroutine` 错误

可以在 `config/autoload/amqp.php` 配置文件中将 `params.close_on_destruct` 改为 `false` 即可。

## 使用 Swoole 4.5 版本和 view 组件时访问接口出现 404

使用 Swoole 4.5 版本和 view 组件如果出现接口 404 的问题，可以尝试删除 `config/autoload/server.php` 文件中的 `static_handler_locations` 配置项。

此配置下的路径都会被认为是静态文件路由，所以如果配置了`/`，就会导致所有接口都会被认为是文件路径，导致接口 404。

## 代码不生效

当碰到修改后的代码不生效的问题，请执行以下命令

```bash
composer dump-autoload -o
```

开发阶段，请不要设置 `scan_cacheable` 为 `true`，它会导致 `收集器缓存` 存在时，不会再次扫描文件。另外，官方骨架包中的 `Dockerfile` 是默认开启这个配置的，`Docker` 环境下开发的同学，请注意这里。

> 当环境变量存在 SCAN_CACHEABLE 时，.env 中无法修改这个配置。

`2.0.0` 和 `2.0.1` 两个版本，判断文件是否修改时，没有判断修改时间相等的情况，所以文件修改后，立马生成缓存的情况（比如使用 `watcher` 组件时）, 会导致代码无法及时生效。

## 语法错误导致服务无法启动

当项目启动时，抛出类似于以下错误时

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

可以执行脚本 `composer analyse`，对项目进行静态检测，便可以找到出现问题的代码段。

此问题通常是由于 [zircote/swagger](https://github.com/zircote/swagger-php) 的 3.0.5 版本更新导致, 详情请见 [#834](https://github.com/zircote/swagger-php/issues/834) 。 
如果安装了 [hyperf/swagger](https://github.com/hyperf/swagger) 建议将 [zircote/swagger](https://github.com/zircote/swagger-php) 的版本锁定在 3.0.4

## 内存限制太小导致项目无法运行

PHP 默认的 `memory_limit` 只有 `128M`，因为 `Hyperf` 使用了 `BetterReflection`，不使用扫描缓存时，会消耗大量内存，所以可能会出现内存不够的情况。

我们可以使用 `php -dmemory_limit=-1 bin/hyperf.php start` 运行, 或者修改 `php.ini` 配置文件

```
# 查看 php.ini 配置文件位置
php --ini

# 修改 memory_limit 配置
memory_limit=-1
```

## Hyperf 版本从 `2.0.18` 之前版本升级之后, 提示 `Call to undefined method Hyperf\Utils\Arr::merge()`

原因是 `doctrine/common` 组件导致的 `hyperf/utils` 组件无法升级。执行以下命令移除 `doctrine/common` 组件，重新更新 Hyperf 相关组件即可。

```bash
# 移除组件
composer remove doctrine/common
# 更新
composer update "hyperf/*" -o
```
