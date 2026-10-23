# Classe de enumeração

Quando você precisa definir códigos de erro e mensagens de erro, os seguintes métodos podem ser usados,

```php
<?php

class ErrorCode
{
    const SERVER_ERROR = 500;
    const PARAMS_INVALID = 1000;

    public static $messages = [
        self::SERVER_ERROR => 'Server Error',
        self::PARAMS_INVALID => 'Illegal parameter'
    ];
}

$message = ErrorCode::messages[ErrorCode::SERVER_ERROR] ?? 'unknown mistake';

```

Mas esse método de implementação não é amigável. Sempre que você quiser consultar o código de erro e a informação de erro correspondente, você tem que pesquisar a `Class` atual duas vezes, então o framework fornece uma classe de enumeração baseada em annotation.

## Instalação

```
composer require hyperf/constants
```

## Uso

### Definir a classe de enumeração

Uma classe de enumeração pode ser gerada rapidamente com o comando `gen:constant`.

```bash
php bin/hyperf.php gen:constant ErrorCode --type enum
```

```php
<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum ErrorCode: int
{
    use EnumConstantsTrait;

    #[Message("Server Error!")]
    case SERVER_ERROR = 500;

    #[Message("System parameter error")]
    case SYSTEM_INVALID = 700;
}
```

O usuário pode usar `ErrorCode::SERVER_ERROR->getMessage()` para obter a mensagem de erro correspondente.

### Definir a classe de exception

Se você simplesmente usar a `classe de enumeração`, não é conveniente o suficiente ao tratar exceções. Então precisamos definir nossa própria classe de exception `BusinessException`. Quando uma exceção ocorrer, ela consultará ativamente a informação de erro correspondente de acordo com o código de erro.

```php
<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class BusinessException extends ServerException
{
    public function __construct(ErrorCode|int $code = 0, ?string $message = null, ?Throwable $previous = null)
    {
        if (is_null($message)) {
            if ($code instanceof ErrorCode) {
                $message = $code->getMessage();
            } else {
                $message = ErrorCode::getMessage($code);
            }
        }

        $code = $code instanceof ErrorCode ? $code->value : $code;

        parent::__construct($message, $code, $previous);
    }
}
```

### Lançar uma exceção

Após concluir as duas etapas acima, a exceção correspondente pode ser lançada na lógica de negócio.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;

class IndexController extends AbstractController
{
    public function index()
    {
        throw new BusinessException(ErrorCode::SERVER_ERROR);
    }
}
```

### Parâmetro variável

Ao usar `ErrorCode::SERVER_ERROR->getMessage()` para obter a mensagem de erro correspondente, também podemos passar parâmetros variáveis para combinar mensagens de erro. Por exemplo, o seguinte

```php
<?php

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum ErrorCode: int
{
    use EnumConstantsTrait;

    #[Message("Params %s is invalid.")]
    case PARAMS_INVALID = 1000;
}

$message = ErrorCode::PARAMS_INVALID->getMessage(['user_id']);
```

### Globalização

> Este recurso está disponível apenas na v1.1.13 e posterior

Para habilitar o componente [hyperf/constants](https://github.com/hyperf/constants) a suportar internacionalização, o componente [hyperf/translation](https://github.com/hyperf/translation) deve estar instalado e os arquivos de idioma devidamente configurados, como a seguir:

```
composer require hyperf/translation
```

Para a configuração relacionada, consulte [Internacionalização](pt-br/translation.md)

```php
<?php

// International configuration

return [
    'params.invalid' => 'Params :param is invalid.',
];

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum ErrorCode: int
{
    use EnumConstantsTrait;

    #[Message("params.invalid")]
    case PARAMS_INVALID = 1000;
}

$message = ErrorCode::SERVER_ERROR->getMessage(['param' => 'user_id']);
```
