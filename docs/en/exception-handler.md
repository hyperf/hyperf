# Exception Handler

In `Hyperf`, business code runs on `Worker processes`. This means that once there is an uncaught exception in the business of any request, it will cause the corresponding `Worker process` to be interrupted and exited. This is unacceptable for the service, and it is more friendly to capture the exception and output a reasonable error message to the client.
We can define different `ExceptionHandlers` for each `server`. Once there is an uncaught exception in the business flow, it will be passed to the registered `ExceptionHandler` for processing.

## Customizing an Exception Handler

### Registering Exception Handler via Configuration File

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // 'http' here corresponds to the value of the name attribute of the server in config/autoload/server.php
        'http' => [
            // Configure the complete class namespace address here to complete the registration of this exception handler
            \App\Exception\Handler\FooExceptionHandler::class,
        ],    
    ],
];
```

### Registering Exception Handler via [Annotation](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterHandler;

// 'http' here corresponds to the value of the name attribute of the server in config/autoload/server.php
// priority is for sorting
#[RegisterHandler(server: 'http')]
class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        return $response->withHeader('Server', 'Hyperf')->withStatus(500)->withBody(new SwooleStream('Internal Server Error.'));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```

> The order of each exception handler configuration array determines the order in which exceptions are passed between handlers.

### Defining Exception Handler

We can define a `Class` anywhere and inherit from the abstract class `Hyperf\ExceptionHandler\ExceptionHandler` and implement the abstract methods within it, as follows:

```php
<?php
namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use App\Exception\FooException;
use Throwable;

class FooExceptionHandler extends  ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // Determine whether the captured exception is the exception you want to capture
        if ($throwable instanceof FooException) {
            // Formatted output
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);

            // Stop exception bubbling
            $this->stopPropagation();
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        // Hand over to the next exception handler
        return $response;

        // Or do not handle it and directly shield the exception
    }

    /**
     * Determine whether this exception handler should handle this exception
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```

### Defining Exception Class

```php
<?php
namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class FooException extends ServerException
{
}
```

### Triggering Exception

```php

namespace App\Controller;

use App\Exception\FooException;

class IndexController extends AbstractController
{
    public function index()
    {
        throw new FooException('Foo Exception...', 800);
    }
}
```
In the example above, let's assume that `FooException` is an existing exception, and let's also assume that the configuration of this handler has been completed. Then when the business throws an uncaught exception, it will be passed in sequence according to the configured order. The entire processing flow can be understood as a pipeline. If the previous exception handler calls `$this->stopPropagation()`, it will no longer be passed backward. If the last configured exception handler still does not capture and handle the exception, then it will be handed over to Hyperf's default exception handler for processing.

## Integrating Whoops

The framework provides Whoops integration.

First, install Whoops
```php
composer require --dev filp/whoops
```

Then configure the dedicated Whoops exception handler.

```php
// config/autoload/exceptions.php
return [
    'handler' => [
        'http' => [
            \Hyperf\ExceptionHandler\Handler\WhoopsExceptionHandler::class,
        ],    
    ],
];
```

The effect is as shown in the figure:

![whoops](en/imgs/whoops.png)


## Error Listener

The framework provides an `error_reporting()` error level listener `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.

### Configuration

Add the listener to `config/autoload/listeners.php`

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

When code similar to the following appears, an `\ErrorException` exception will be thrown

```php
<?php
try {
    $a = [];
    var_dump($a[1]);
} catch (\Throwable $throwable) {
    var_dump(get_class($throwable), $throwable->getMessage());
}

// string(14) "ErrorException"
// string(19) "Undefined offset: 1"
```

If the listener is not configured, it will be as follows, and no exception will be thrown.

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```
