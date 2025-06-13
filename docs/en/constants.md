# Enum class

When you need to define error codes and error messages, the following methods may be used,

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

But this implementation method is not friendly. Whenever you want to query the error code and the corresponding error information, you have to search the current `Class` twice, so the framework provides an annotation-based enumeration class.

## Install

```
composer require hyperf/constants
```

## Use

### Define the enum class

An enumeration class can be generated quickly with the `gen:constant` command.

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

User can use `ErrorCode::SERVER_ERROR->getMessage()` to get the corresponding error message.

### Define exception class

If you simply use the `enumeration class`, it is not convenient enough when handling exceptions. So we need to define our own exception class `BusinessException`. When an exception comes in, it will actively query the corresponding error information according to the error code.

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

### Throw an exception

After completing the above two steps, the corresponding exception can be thrown in the business logic.

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

### Variable parameter

When using `ErrorCode::SERVER_ERROR->getMessage()` to get the corresponding error message, we can also pass in variable parameters to combine error messages. For example the following

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

### Globalization

> This feature is only available on v1.1.13 and later

To enable the [hyperf/constants](https://github.com/hyperf/constants) component to support internationalization, the [hyperf/translation](https://github.com/hyperf/translation) component must be installed and configured Good language files, as follows:

```
composer require hyperf/translation
```

For related configuration, see [Internationalization](en/translation.md)

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
