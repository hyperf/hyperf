# 常见问题

## Swoole 短名未关闭

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
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

## 代码不生效

当碰到修改后的代码不生效的问题，请执行以下命令

```bash
composer dump-autoload -o
```

开发阶段，请不要设置 `scan_cacheable` 为 `true`，它会导致 `收集器缓存` 存在时，不会再次扫描文件。另外，官方骨架包中的 `Dockerfile` 是默认开启这个配置的，`Docker` 环境下开发的同学，请注意这里。

> 当环境变量存在 SCAN_CACHEABLE 时，.env 中无法修改这个配置。

## 语法错误导致服务无法启动

当项目启动时，抛出类似于以下错误时

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

可以执行脚本 `composer analyse`，对项目进行静态检测，便可以找到出现问题的代码段。

此问题通常是由于 [zircote/swagger](https://github.com/zircote/swagger-php) 的 3.0.5 版本更新导致, 详情请见 [#834](https://github.com/zircote/swagger-php/issues/834) 。
如果安装了 [hyperf/swagger](https://github.com/hyperf/swagger) 建议将 [zircote/swagger](https://github.com/zircote/swagger-php) 的版本锁定在 3.0.4

## 内存限制太小导致项目无法运行

PHP 默认的 `memory_limit` 只有 `128M`。

我们可以使用 `php -d memory_limit=-1 bin/hyperf.php start` 运行, 或者修改 `php.ini` 配置文件

```
# 查看 php.ini 配置文件位置
php --ini

# 修改 memory_limit 配置
memory_limit=-1
```

## Trait 内使用 `#[Inject]` 注入报错 `Error while injecting dependencies into ... No entry or class found ...`

若 Trait 通过 `#[Inject] @var` 注入属性, 同时子类里 `use` 了不同命名空间的同名类, 会导致 Trait 里类名被覆盖，进而导致注入失效:

```php
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

如上 Trait 类注入 `Hyperf\HttpServer\Contract\ResponseInterface`, 若子类使用不同命名空间的`ResponseInterface` 类, 如`use Psr\Http\Message\ResponseInterface`, 会导致 Trait 原类名被覆盖:

```php
// use 同类名会覆盖Trait
use Psr\Http\Message\ResponseInterface;

class IndexController
{
    use TestTrait;
}
// Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
```

上述问题可以通过以下两个方法解决:

- 子类通过 `as` 修改别名: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
- Trait 类`PHP7.4` 以上通过属性类型限制: `protected ResponseInterface $response;`

## Grpc 扩展或未安装 Pcntl 导致项目无法启动

- v2.2 版本的注解扫描使用了 `pcntl` 扩展，所以请先确保您的 `PHP` 安装了此扩展。

```shell
php --ri pcntl

pcntl

pcntl support => enabled
```

- 当开启 `grpc` 的时候，需要添加 `grpc.enable_fork_support= 1;` 到 `php.ini` 中，以支持开启子进程。

## HTTP Server 将 `open_websocket_protocol` 设置为 `false` 后启动报错：`Swoole\Server::start(): require onReceive callback`

1. 检查 Swoole 是否编译了 http2

```shell
php --ri swoole | grep http2
http2 => enabled
```

如果没有，需要重新编译 Swoole 并增加 `--enable-http2` 参数。

2. 检查 server.php 文件中 `open_http2_protocol` 选项是否为 `true`。

## Command 无法正常关闭

在 Command 中使用 AMQP 等多路复用技术后，会导致无法正常关闭，碰到这种情况只需要在执行逻辑最后增加以下代码即可。

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## OSS 上传组件报 iconv 错误

- fix aliyun oss wrong charset: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

当使用 `aliyuncs/oss-sdk-php` 组件上传时，会报 iconv 错误，可以尝试使用以下方式规避：

使用 `hyperf/hyperf:8.0-alpine-v3.12-swoole` 镜像时

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

使用 `hyperf/hyperf:8.0-alpine-v3.13-swoole` 镜像时

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/ gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## DI 收集失败

在 DI 收集阶段发生异常（如命名空间错误等原因），可能产生以下格式日志的输出。

- 业务代码，排查日志中路径相关的文件和类。
- 框架代码，提交 PR 或 Issue 反馈。
- 第三方组件，反馈给组件作者。

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## 环境版本不一致导致服务无法启动

当项目启动时，抛出类似如下错误时：

```bash
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

此问题通常是由于实际运行时使用的 Swoole 版本和安装框架/组件时使用的 Swoole 版本不一致导致。

使用和安装时相同的 Swoole、PHP 版本即可解决。
