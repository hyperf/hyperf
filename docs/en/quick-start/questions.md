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

## Dependency injection is not working correctly in `PHP` version `7.3`

In versions `2.0` - `2.1`, `Hyperf` uses the `BetterReflection` package to make `AOP` work with none `DI` managed objects (such as objects instantiated using the `new` keyword). While implementing `AOP` in this way enhances the developer experience, it also brings several difficulties:

* Project startup is slow without a `scan cache`
* `Inject` and `Value` annotations have no effect
* `BetterReflection` does not support `PHP` version `8` (as of the `2.2` release)

In newer versions, we stopped using the `BetterReflection` package in favour of using a child process to scan the codebase to solve these pain points but this introduced some compatibility issues in older versions of `PHP`:

In `php7.3` you may encounter an error similar to the following when starting the application:

```
PHP Fatal error:  Interface 'Hyperf\Signal\SignalHandlerInterface' not found in vendor/hyperf/process/src/Handler/ProcessStopHandler.php on line 17

PHP Fatal error:  Interface 'Symfony\Component\Serializer\SerializerInterface' not found in vendor/hyperf/utils/src/Serializer/Serializer.php on line 46
```

This problem is due to how `PHP` version `7.3` handles using reflection to find an interface which does exist or has no corresponding class. The best solution is to upgrade to `PHP` version `7.4` or `8.0` but the issue can also be fixed by installing the components which contain these interfaces as follows:

```
composer require hyperf/signal

composer require symfony/serializer
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
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\Coordinator\Constants;

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