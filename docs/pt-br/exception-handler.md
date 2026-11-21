# Tratamento de exceções

No `Hyperf`, todo o código de negócio é executado no `Worker Process`. Nessa situação, caso uma requisição gere uma exceção não capturada, o `Worker Process` correspondente será interrompido e encerrado, o que é inaceitável para um serviço. Capturar exceções e retornar uma resposta de erro razoável também é mais amigável para o client.

Podemos definir diferentes `ExceptionHandlers` para cada `server` e, quando ocorrer uma exceção não capturada no processo, ela será encaminhada ao `ExceptionHandler` registrado para tratamento.

## Customizar o tratamento de exceções

### Registrar Exception Handler

Atualmente, o registro de `ExceptionHandler` é suportado apenas na forma de arquivo de configuração. O arquivo fica em `config/autoload/exceptions.php`. Configure seu handler customizado sob o `server` correspondente:

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

### Registrar o exception handler via [annotation](https://github.com/hyperf/hyperf/blob/master/src/exception-handler/src/Annotation/ExceptionHandler.php)

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

> A ordem de cada array de configuração de exception handlers determina a ordem em que as exceções passam entre handlers.

### Definir Exception Handler

Você pode definir uma `class (Class)` em qualquer lugar, herdar a classe abstrata `Hyperf\ExceptionHandler\ExceptionHandler` e implementar os métodos abstratos. Por exemplo:

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
    ```php
        public function handle(Throwable $throwable, ResponseInterface $response)
        {
            // Determina se a exceção capturada é a exceção desejada
            if ($throwable instanceof FooException) {
                // Saída formatada
                $data = json_encode([
                    'code' => $throwable->getCode(),
                    'message' => $throwable->getMessage(),
                ], JSON_UNESCAPED_UNICODE);

                // Evita a propagação (bubbling)
                $this->stopPropagation();
                return $response->withStatus(500)->withBody(new SwooleStream($data));
            }

            // Passa para o próximo exception handler
            return $response;

            // Ou simplesmente ignora a exceção sem processar
        }

        /**
         * Determina se o exception handler precisa tratar a exceção ou não
         */
        public function isValid(Throwable $throwable): bool
        {
            return true;
        }
    ```

### Definir a classe de exceção

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

### Disparar exceção

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

No exemplo acima, assumimos que `FooException` é uma exceção lançada e que exception handlers estão configurados. Quando uma exceção não capturada for lançada, ela passará pela ordem de registro dos handlers. Imagine o processamento como um pipe: a exceção deixará de passar quando algum handler chamar `$this->stopPropagation()`. O handler padrão do Hyperf será o último a capturar exceções, caso nenhum outro handler tenha capturado.

## Integração com Whoops

O framework fornece integração com Whoops.

Instale o Whoops primeiro:

```php
composer require --dev filp/whoops
```

Depois configure o exception handler específico para o Whoops.

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

![whoops](/imgs/whoops.png)


## Error Listener

O framework fornece o listener de nível do `error_reporting()` chamado `Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler`.

### Configuração

Adicione um listener em `config/autoload/listeners.php`.

```php
<?php
return [
    \Hyperf\ExceptionHandler\Listener\ErrorExceptionHandler::class
];
```

Quando um código semelhante ao abaixo aparecer, `\ErrorException` será lançado.

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

Se nenhum listener estiver configurado, nenhuma exceção será lançada.

```
PHP Notice:  Undefined offset: 1 in IndexController.php on line 24

Notice: Undefined offset: 1 in IndexController.php on line 24
NULL
```

