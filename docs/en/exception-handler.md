# Exception Handler

In `Hyperf`, all the business code excute on `Worker Process`. In this case, once any request has an exception that has not been caught, the corresponding `Worker Process` will be interrupted and exited, which is unacceptable for the service. Catch exceptions and output reasonable error content is also more friendly to the client. We can define different `ExceptionHandlers` for each `server`, and once there are exceptions that are not caught in the process, they will be passed to the registered `ExceptionHandler` for processing.

## Customize an Exception Handling

### Register Exception Handler

Currently, it only supports the registration of `ExceptionHandler` in the form of a configuration file. The configuration file is located in `config/autoload/exceptions.php`. Configure your custom exception handler under the corresponding `server`:

```php
<?php
// config/autoload/exceptions.php
return [
    'handler' => [
        // The http here corresponds to the name value corresponding to the server in config/autoload/server.php
        'http' => [
            // The registration of the exception handler has done by configuring the complete class namespace address here
            \App\Exception\Handler\FooExceptionHandler::class,
        ],    
    ],
];
```

### Register the exception handler through [annotation](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)

```php
<?php
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Hyperf\ExceptionHandler\Annotation\ExceptionHandler as RegisterHandler;

// The http here corresponds to the name value corresponding to the server in config/autoload/server.php
// priority is sorting
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

### Define Exception Handler

We can define a `class (Class)` anywhere and inherit the abstract class `Hyperf\ExceptionHandler\ExceptionHandler` and implement the abstract methods in it. As shown below:

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
        // Determine that the caught exception is the wanted exception
        if ($throwable instanceof FooException) {
            // Formatted output
            $data = json_encode([
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ], JSON_UNESCAPED_UNICODE);

            // Prevent bubbling
            $this->stopPropagation();
            return $response->withStatus(500)->withBody(new SwooleStream($data));
        }

        // Hand over to the next exception handler
        return $response;

        // Or directly shield the exception without processing
    }

    /**
     * Determine whether the exception handler needs to handle the exception or not
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
```

### Define Exception Class

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

### Trigger Exception

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
In the example above, we assume that `FooException` is a thrown exception, and exception handlers are configured. When an uncaught exception has been thrown, it will be passed through the handler registration order. Imagine the processing as a pipe, the exception will not be passed once there are some handler calls `$this->stopPropagation()`. The default handler of Hyperf will be the last one to catch exceptions if there is no other handler to catch such exceptions.

## Integrated Whoops

The framework provides Whoops integration.

Install Whoops first
```php
composer require --dev filp/whoops
```

Then configure the special exception handler for Whoops.

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

As shown in the image:

![whoops](/imgs/whoops.png)


## Error Listener

The framework provides the `error_reporting()` error level listener `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.

### Configuration

Add a listener in `config/autoload/listeners.php`

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

When a code similar to the following appears, `\ErrorException` will be thrown

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

If no listener is configured, no exception will be thrown.

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```

