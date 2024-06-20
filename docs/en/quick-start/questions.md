# FAQ

## Swoole short function names have not been disabled

```
[ERROR] Swoole short function names must be disabled before the server starts, please set swoole.use_shortname = 'Off' in your php.ini.
```

You need to add `swoole.use_shortname ='Off'` to your php.ini configuration file

> Note that this configuration MUST be configured in php.ini and CANNOT be overridden by the ini_set() function.

You could also start the server through the following command, disabling the Swoole short function names when executing the PHP command:

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## Message loss in asynchronous queues

If the `handle` method is not being executed when using the `async-queue` component, please check the following possibilities:

1. Is `Redis` being shared with another project or other users, and are the messages being consumed by those projects or users?
2. Do you have remnants of old processes still running which could be consuming the messages?

The following provides an easy solution to both of these issues:
   
1. Run the command `killall php` in your `console`
2. Modify the `channel` configuration of your `async-queue`
   
## `Swoole\Error: API must be called in the coroutine` error when using the `hyperf/amqp` component
   
Set the `close_on_destruct` configuration value to `false` in the `config/autoload/amqp.php` configuration file.

## All requests return 404 errors when using Swoole version 4.5 and the `view` component
    
If you are using Swoole version 4.5 and the `view` component and there is a `404` error problem, you can try to remove the `static_handler_locations` configuration item from the `config/autoload/server.php` configuration file.
    
This configuration value contains a path that will be considered a `static file` route, so if the value is `/`, all requests are processed as files, resulting in 404 errors.

## Code changes have no effect
   
If there is no change when you modify the code of your `Hyperf` application, run the following command:
   
```bash
composer dump-autoload -o
```
   
During development, please DO NOT set the `scan_cacheable` configuration value to `true`, as it will cause the file to not be re-parsed when the `collector cache` is being used. In addition, the `Dockerfile` in the official `hyperf-skeleton` package has this configuration enabled by default. When developing under the `Docker` environment, please set `scan_cacheable` to `false`.

> When the environment variable `SCAN_CACHEABLE` exists, this configuration cannot be modified in any `.env` file.

## Syntax error when starting the server

Is the following exception is thrown when the `Hyperf` server starts:

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

Please run `composer analyse` to initialize a static scan of the source code in order to locate the issue.

Normally this issue is caused by running version `3.0.5` of [zircote/swagger](https://github.com/zircote/swagger-php), please see [#834](https://github.com/zircote/swagger-php/issues/834) for further information.

If you have installed [hyperf/swagger](https://github.com/hyperf/swagger), please lock the version of [zircote/swagger](https://github.com/zircote/swagger-php) at `3.0.4`.

## `Hyperf` cannot start because the memory_limit is too small

By default, the `memory_limit` of `PHP` is set to `128M`. Because `Hyperf` makes use of the `BetterReflection` package to perform code analysis, a large amount of memory may be consumed and the `PHP` process may throw fatal exceptions when it runs out of memory.

You can either run commands with an argument to increase the memory limit `php -d memory_limit=-1 bin/hyperf.php start` or modify the `php.ini` configuration file:

```ini
# Look for the location of your php.ini file
php --ini

# Set the memory_limit within that file
memory_limit=-1
```

## `Error while injecting dependencies into... No entry or class found...` error when injecting traits using `#[Inject]`

This error appears when you inject a trait using namespaces via `Inject` and the class containing the `use Trait;` syntax uses a conflicting namespace. This is a complex concept but the following examples should make it simple:

```php
use Hyperf\HttpServer\Contract\ResponseInterface; # Namespace containing ResponseInterface class
use Hyperf\Di\Annotation\Inject;

trait TestTrait
{
    #[Inject]
    protected ResponseInterface $response;
}
```

In the above trait, the class `Hyperf\HttpServer\Contract\ResponseInterface` is injected. If the sub-class (the class which uses this trait) uses a `ResponseInterface` class with a difference namespace, for example `Psr\Http\Message\ResponseInterface`, it will cause the injected `ResponseInterfece` to be overwritten.

```php
use Psr\Http\Message\ResponseInterface; # A conflicting namespace containing a ResponseInterface class

class IndexController
{
    use TestTrait;
    // Error while injecting dependencies into App\Controller\IndexController: No entry or class found for 'Psr\Http\Message\ResponseInterface'
}
```

This issue can be fixed using the following methods:

* Create an alias in the sub-class to prevent a conflict: `use Psr\Http\Message\ResponseInterface as PsrResponseInterface;`
* In `PHP` version `7.4` you can add a type to the attribute within the trait class: `protected ResponseInterface $response;`

## `Hyperf` will not execute commands because `gprc` or `pcntl` extensions are not installed

`Hyperf` version `2.2` requires the `pcntl` extension, you can check if it's installed by running the command `php --ri pcntl`:

```
pcntl

pcntl support => enabled
```

When using `grpc`, you must enable `fork support` to support opening child processes by adding the following to your `php.ini`:

```
grpc.enable_fork_support=1;
```

## The `open_websocket_protocol` value is set to `false` after receiving the error: `Swoole\Server::start(): require onReceive callback`

1. Check if `Swoole` has been compiled with `http2` support:

```
php --ri swoole | grep http2
http2 => enabled
```

If the result of this command is empty, you need to recompile `Swoole` with the `--enabled-http2` parameter

2. Check the `open_http2_protocol` configuration value is set to `true` in the `config/autoload/server.php` configuration file

## Command cannot be closed properly

After using multiplex technologies such as AMQP in Command, it will not be able to close normally. In this case, you only need to add the following code at the end of the execution logic.

```php
<?php
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Constants;

CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
```

## OSS upload component reports iconv error

- fix Aliyun oss wrong charset: https://github.com/aliyun/aliyun-oss-php-sdk/issues/101
- https://github.com/docker-library/php/issues/240#issuecomment-762438977
- https://github.com/docker-library/php/pull/1264

When using the `aliyuncs/oss-sdk-php` component to upload, an iconv error will be reported. You can try to avoid it by using the following methods:

When using `hyperf/hyperf:8.0-alpine-v3.12-swoole` image

```
RUN apk --no-cache --allow-untrusted --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ add gnu-libiconv=1.15-r2
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so
```

When using `hyperf/hyperf:8.0-alpine-v3.13-swoole` image

```dockerfile
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/v3.13/community/gnu-libiconv=1.15-r3
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php
```

## DI Reflection Manager collect failed

When an exception occurs during the DI collection phase (for example, a namespace error), the output of a log in the following format may be generated.

- Service code, check the files and classes related to the path in the log.
- Framework code, submit PR feedback.
- Third party components, feedback to the component author.

```bash
[ERROR] DI Reflection Manager collecting class reflections failed. 
File: xxxx.
Exception: xxxx
```

## The service can not start because the environment version is inconsistent

When the project starts, an error similar to the following is thrown

```
Hyperf\Engine\Channel::push(mixed $data, float $timeout = -1): bool must be compatible with Swoole\Coroutine\Channel::push($data, $timeout = -1)
```

This problem is usually caused by inconsistencies between the Swoole version used when installing frameworks/components and the actual Swoole version used at runtime.

Should keep the version of Swoole and PHP consistent when installing and using.
