# Knowledge before start programming

Here are a collection of knowledges or contents that should be known before programming by Hyperf.

## Cannot get/set property parameters through global variables

Under `PHP-FPM`, you can get the requested parameters through global variables, server parameters, etc., in `Hyperf` and `Swoole`, ** can't ** via `$_GET/$_POST/$_REQUEST/$ _SESSION/$_COOKIE/$_SERVER` and other variables starting with `$_` get any attribute parameters.

## Classes obtained through the container are singletons

Through the dependency injection container, all of the in-process persistence is shared by multiple coroutines, so it cannot contain any data that is unique to the request or unique to the coroutine. This type of data is processed through the coroutine context. Please read the [Dependency Injection] (en/di.md) and [Coroutine] (en/coroutine.md) sections carefully.