# FAQ

## Swoole short name not disabled

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

You need to add the `swoole.use_shortname = 'Off'` configuration item to your `php.ini` configuration file.

> Note that this configuration must be set in `php.ini` and cannot be overwritten via the `ini_set()` function.

Alternatively, you can start the service with the following command to disable the Swoole short name feature when executing the PHP command:

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## Async queue message loss

If you find that the method in `handle` is not being executed when using the `async-queue` component, please first check the following situations:

1. Whether `Redis` is shared with others and messages are being consumed by others.
2. Whether there are residual local processes being consumed by other processes.

The following is an infallible solution:

1. `killall php`
2. Modify the `async-queue` configuration `channel`

## Using AMQP component results in `Swoole\Error: API must be called in the coroutine` error

You can change `params.close_on_destruct` to `false` in the `config/autoload/amqp.php` configuration file.

## Code changes not taking effect

When you encounter the problem of modified code not taking effect, please execute the following command:

```bash
composer dump-autoload -o
```

During the development stage, please do not set `scan_cacheable` to `true`, as it will cause the scanner to not scan files again when `collector cache` exists. Additionally, the `Dockerfile` in the official skeleton package has this configuration enabled by default. Developers working in a `Docker` environment, please pay attention to this.

> When the environment variable `SCAN_CACHEABLE` exists, this configuration cannot be modified in `.env`.

## Service fails to start due to syntax errors

When the project starts and throws an error similar to the following:

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

You can run the `composer analyse` script to perform static analysis on the project to find the problematic code segment.

This problem is usually caused by the update of `zircote/swagger` version 3.0.5. For details, please see [#834](https://github.com/zircote/swagger-php/issues/834).
If you have installed [hyperf/swagger](https://github.com/hyperf/swagger), it is recommended to lock the version of [zircote/swagger](https://github.com/zircote/swagger-php) to 3.0.4.

## Project fails to run due to small memory limit

The default `memory_limit` of PHP is only `128M`.

We can run using `php -d memory_limit=-1 bin/hyperf.php start`, or modify the `php.ini` configuration file:

```
# View the php.ini configuration file location
php --ini

# Modify the memory_limit configuration
memory_limit=-1
```

## Error when using `#[Inject]` in Trait: `Error while injecting dependencies into ... No entry or class found ...`

If a Trait injects a property via `#[Inject] @var`, and a subclass `use`s a same-named class from a different namespace, it will cause the class name in the Trait to be overwritten, which in turn causes the injection to fail:

```php
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

As shown above, the Trait class injects `Hyperf\HttpServer\Contract\ResponseInterface`. If the subclass uses a `ResponseInterface` class from a different namespace, such as `use Psr\Http\Message\ResponseInterface`, it will cause the original class name in the Trait to be overwritten:

```php
// use of the same class name will overwrite the Trait
use Psr\Http\Message\ResponseInterface;

class IndexController
{
    use TestTrait;
}
// Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
```

The above problem can be solved by the following two methods:

- Modify the alias in the subclass via `as`: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
- For Trait classes, restrict the property type in `PHP 7.4` and above: `protected ResponseInterface $response;`

## Project fails to start due to Grpc extension not installed or Pcntl missing

- The annotation scanning in version v2.2 uses the `pcntl` extension, so please ensure that your `PHP` has this extension installed.

```shell
php --ri pcntl

pcntl

pcntl support => enabled
```

- When `grpc` is enabled, you need to add `grpc.enable_fork_support= 1;` to `php.ini` to support enabling child processes.

## HTTP Server fails to start after setting `open_websocket_protocol` to `false`: `Swoole\Server::start(): require onReceive callback`

1. Check if Swoole is compiled with http2

```shell
php --ri swoole | grep http2
http2 => enabled
```

If not, you need to recompile Swoole and add the `--enable-http2` parameter.

2. Check if the `open_http2_protocol` option in the `server.php` file is `true`.

## Command cannot close normally

After using multiplexing technologies such as AMQP in a Command, it may lead to being unable to close normally. In such cases, you just need to add the following code at the end of the execution logic.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## OSS upload component reports iconv error

- fix aliyun oss wrong charset: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

When using the `aliyuncs/oss-sdk-php` component for uploading, an iconv error may be reported. You can try to avoid it using the following methods:

When using the `hyperf/hyperf:8.0-alpine-v3.12-swoole` image:

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

When using the `hyperf/hyperf:8.0-alpine-v3.13-swoole` image:

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/ gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## DI collection fails

If an exception occurs during the DI collection phase (due to reasons such as namespace errors, etc.), the following format of log output may be generated.

- For business code, troubleshoot the files and classes related to the path in the logs.
- For framework code, submit a PR or Issue for feedback.
- For third-party components, provide feedback to the component author.

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## Service fails to start due to environment version mismatch

When the project starts and throws an error similar to the following:

```bash
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

This problem is usually caused by the actual Swoole version used at runtime being inconsistent with the Swoole version used when installing the framework/components.

This can be resolved by using the same Swoole and PHP versions as used during installation.
