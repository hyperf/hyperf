# Knowledge before start programming

Here are a collection of knowledges or contents that should be known before programming by Hyperf.

## Cannot get/set property parameters through global variables

Under `PHP-FPM`, you can get the requested parameters through global variables, server parameters, etc., in `Hyperf` and `Swoole`, ** can't ** via `$_GET/$_POST/$_REQUEST/$ _SESSION/$_COOKIE/$_SERVER` and other variables starting with `$_` get any attribute parameters.

## Classes obtained through the container are singletons

Through the dependency injection container, all of the in-process persistence is shared by multiple coroutines, so it cannot contain any data that is unique to the request or unique to the coroutine. This type of data is processed through the coroutine context. Please read the [Dependency Injection](en/di.md) and [Coroutine](en/coroutine.md) sections carefully.

## Deployment

> The official Dockerfile has already setup these operations.

When deploying the production environment, please make sure to enable `scan_cacheable`.

After enable this configuration, the proxy class and annotation cache will be generated during the first scan, and the cache can be used directly when it is restarted, which greatly optimizes the memory usage and startup time consution. Because the scan stage is skipped, the `Composer Class Map` will be relied upon, so we have to execute `--optimize-autoloader` option of composer command to optimize the class index.

In summary, update the code of production environment, you need to execute the following commands before restarting the project

```bash
# Optimize the composer class index
composer dump-autoload -o
# Generate all proxy classes and the annotation cache
php bin/hyperf.php
```