# Exception Handler

No `Hyperf`, todo o código de negócio é executado no `Worker Process`. Nesse caso, uma vez que qualquer request tenha uma exception que não foi capturada, o `Worker Process` correspondente será interrompido e finalizado, o que é inaceitável para o serviço. Capturar exceptions e retornar um conteúdo de erro razoável também é mais amigável para o cliente. Podemos definir diferentes `ExceptionHandlers` para cada `server`, e uma vez que existam exceptions não capturadas no processo, elas serão passadas para o `ExceptionHandler` registrado para tratamento.

## Customizar um Exception Handling

### Registrar Exception Handler

Atualmente, apenas é suportado o registro de `ExceptionHandler` na forma de um arquivo de configuração. O arquivo de configuração está localizado em `config/autoload/exceptions.php`. Configure seu exception handler customizado sob o `server` correspondente:

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

### Registrar o exception handler através de [annotation](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)

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

> A ordem de cada array de configuração de exception handler determina a ordem em que as exceptions são passadas entre os handlers.

### Definir Exception Handler

Podemos definir uma `class (Class)` em qualquer lugar, herdar a abstract class `Hyperf\ExceptionHandler\ExceptionHandler` e implementar os methods abstratos nela. Como mostrado abaixo:

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

### Definir Exception Class

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

### Disparar Exception

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
No exemplo acima, assumimos que `FooException` é uma exception lançada, e os exception handlers estão configurados. Quando uma exception não capturada é lançada, ela passará pela ordem de registro dos handlers. Imagine o tratamento como um pipe; a exception não será mais propagada assim que algum handler chamar `$this->stopPropagation()`. O handler padrão do Hyperf será o último a capturar exceptions, caso não haja outro handler para capturar tais exceptions.

## Whoops Integrado

O framework fornece integração com o Whoops.

Primeiro instale o Whoops
```php
composer require --dev filp/whoops
```

Então configure o exception handler especial para o Whoops.

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

Como mostrado na imagem:

![whoops](pt-br/imgs/whoops.png)


## Error Listener

O framework fornece o listener de nível de erro `error_reporting()`, `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.

### Configuração

Adicione um listener em `config/autoload/listeners.php`

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

Quando um código semelhante ao seguinte aparecer, `\ErrorException` será lançado

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

Se nenhum listener estiver configurado, nenhuma exception será lançada.

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```
