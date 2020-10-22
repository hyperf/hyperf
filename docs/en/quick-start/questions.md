# FAQ

## `Inject` or `Value` annotation does not works

`2.0` version has changed the mechanism of injection, in the new version, Hyperf will injecting the value of `Inject` and `Value` annotations in the constructor of target class. The following two scenarios may cause injection failure, please pay attention.

1. The target class have not use `Inject` or `Value` annotations, but the parent class uses `Inject` or `Value` annotations, also the target class has a constructor, but at the same time the parent class constructor is not called by child class.

This case will cause the target class will not generate the proxy class, and call its own constructor when instantiating, so there is no way to execute the parent class's constructor.   
So the method `__handlePropertyHandler` in the proxy class of the parent class will not be executed, then the `Inject` or `Value` annotations will not take effect.

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

2. The target class have not use `Inject` or `Value` annotatinos, but `Inject` or `Value` annotations are used in the `Trait` that use by target class.

This case will cause the target class will not generate the proxy class, so there is no way to execute the `__handlePropertyHandler` in the constructor, then the `Inject` or `Value` annotations of `Trait` will not take effect.

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

Based on the above two cases, it can be seen that whether the target class generates the proxy class is very important action. Therefore, if you use `Trait` and `parent class` with `Inject` or `Value` annotations, you could just add a `Inject` or `Value` annotations to the target class could solve the above two cases.

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

## Swoole Short Name has not been disabled

```
[ERROR] Swoole short name have to disable before start server, please set swoole.use_shortname = 'Off' into your php.ini.
```

You need to add `swoole.use_shortname ='Off'` configuration in your php.ini configuration file

> Note that this configuration MUST be configured in php.ini and CANNOT be overridden by the ini_set() function.

You could also start the server through the following command, turn off the Swoole short name function when executing the PHP command

```
php -d swoole.use_shortname=Off bin/hyperf.php start
```

## The message of async-queue loss
   
If you notice that the logical of `handle` method does not executed when using the async-queue component, please check the following situations first:
   
1. Is `Redis Server` shared with other project or other peoples, and messages are consumed by other project or other peoples?
2. Whether the local process has remnants and is consumed by other processes
   
The following provides a foolproof solution:
   
1. killall php
2. Modify `async-queue` configuration `channel`
   
## Shows `Swoole\Error: API must be called in the coroutine` error when using hyperf/amqp component
   
You can modify the `close_on_destruct` configuration value to `false` in the `config/autoload/amqp.php` configuration file.

## When using Swoole 4.5 version and view component, all accesses appears 404
    
If you are using Swoole 4.5 version and the view component if there is an 404 problem, you can try to remove the `static_handler_locations` configuration item in the `config/autoload/server.php` file.
    
The path under this configuration will be considered as a static file route, so if `/` is configured, all requests will be considered as file paths, resulting in 404.

## Code does not take effect after modified
   
When you encounter the problem that the modified code does not take effect, please execute the following command
   
```bash
composer dump-autoload -o
```
   
During the development stage, please DO NOT set `scan_cacheable` configuration value to `true`, it will cause the file to not be scanned again when the `collector cache` exists. In addition, the `Dockerfile` in the official skeleton package has this configuration enabled by default. When developing under the `Docker` environment, please pay attention to this.

> When the environment variable exists SCAN_CACHEABLE, this configuration cannot be modified in .env file.

`2.0.0` and `2.0.1`, these two versions, when judging whether the file is modified, there is no judgment that the modification time is equal, so after the file is modified, the cache will be generated immediately (for example, when the `watcher` component is used), as a result, the code cannot take effect in time.

## Syntax error 

Exception will be thrown when hyperf server is started

```
Fatal error: Uncaught PhpParser\Error: Syntax error, unexpected T_STRING on line 27 in vendor/nikic/php-parser/lib/PhpParser/ParserAbstract.php:315
```

Please run `composer analyse` to initialize a static scan of the source code in order to locate the issue

Normally this issue is caused by  [zircote/swagger](https://github.com/zircote/swagger-php) version 3.0.5, Please see [#834](https://github.com/zircote/swagger-php/issues/834) for further information.
If you have installed [hyperf/swagger](https://github.com/hyperf/swagger), please lock the version of [zircote/swagger](https://github.com/zircote/swagger-php) at 3.0.4.