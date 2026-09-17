# Classe Enum

Quando você precisa definir códigos e mensagens de erro, pode usar métodos como o seguinte:

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

Mas essa forma de implementação não é amigável. Sempre que você quiser consultar um código de erro e a informação correspondente, precisa procurar duas vezes na mesma `Class`. Por isso, o framework fornece uma classe de enumeração baseada em anotações.

## Instalação

```
composer require hyperf/constants
```

## Uso

### Definir a enum class

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

    #[Message("Erro de Servidor!")]
    case SERVER_ERROR = 500;

    #[Message("Erro de parâmetro do sistema")]
    case SYSTEM_INVALID = 700;
}
```

Você pode usar `ErrorCode::SERVER_ERROR->getMessage()` para obter a mensagem de erro correspondente.

### Definir a classe de exceção

Se você usar apenas a `enumeration class`, isso não é conveniente o suficiente ao lidar com exceções. Então precisamos definir nossa própria classe de exceção `BusinessException`. Quando uma exceção ocorrer, ela consultará ativamente as informações correspondentes de acordo com o código de erro.

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

Depois de completar os dois passos acima, a exceção correspondente pode ser lançada na lógica de negócio.

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

### Parâmetros variáveis

Ao usar `ErrorCode::SERVER_ERROR->getMessage()` para obter a mensagem correspondente, também podemos passar parâmetros variáveis para compor mensagens. Por exemplo:

```php
<?php

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum ErrorCode: int
{
    use EnumConstantsTrait;

    #[Message("Parâmetros %s são inválidos.")]
    case PARAMS_INVALID = 1000;
}

$message = ErrorCode::PARAMS_INVALID->getMessage(['user_id']);
```

### Globalização

> Este recurso está disponível apenas a partir da v1.1.13.

Para habilitar o suporte a internacionalização no componente [hyperf/constants](https://github.com/hyperf/constants), o componente [hyperf/translation](https://github.com/hyperf/translation) precisa estar instalado e com arquivos de idioma configurados corretamente. Por exemplo:

```
composer require hyperf/translation
```

Para configuração relacionada, consulte [Internationalization](pt-br/translation.md).

```php
<?php

// Configuração de internacionalização

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
