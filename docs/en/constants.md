# Enum Class

When you need to define error codes and error messages, you might use the following approach:

```php
<?php

class ErrorCode
{
    const SERVER_ERROR = 500;
    const PARAMS_INVALID = 1000;

    public static $messages = [
        self::SERVER_ERROR => 'Server Error',
        self::PARAMS_INVALID => 'Invalid Parameters'
    ];
}

$message = ErrorCode::messages[ErrorCode::SERVER_ERROR] ?? 'Unknown error';
```

However, this implementation is not very user-friendly. Whenever you need to query an error code and its corresponding error message, you have to search twice in the current `Class`. Therefore, the framework provides an enumeration class based on annotations.

## Installation

```
composer require hyperf/constants
```

## Usage

### Define Enum Class

You can quickly generate an enum class using the `gen:constant` command.

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
    
    #[Message("Server Error！")]
    case SERVER_ERROR = 500;

    #[Message("System parameter error")]
    case SYSTEM_INVALID = 700;
}
```

Users can use `ErrorCode::SERVER_ERROR->getMessage()` to get the corresponding error message.

### Define Exception Class

If you only use `Enum Class`, it is still not convenient enough for exception handling. So we need to define our own exception class, `BusinessException`. When an exception occurs, it will automatically query the corresponding error message based on the error code.

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

### Throw Exception

After completing the above two steps, you can throw the corresponding exception in your business logic.

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

### Variable Parameters

When using `ErrorCode::SERVER_ERROR->getMessage()` to obtain the corresponding error message, we can also pass variable parameters for error message combination, as in the following case:

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

### Internationalization

To enable the [hyperf/constants](https://github.com/hyperf/constants) component to support internationalization, you must install the [hyperf/translation](https://github.com/hyperf/translation) component and configure the language files, as follows:

```
composer require hyperf/translation
```

For relevant configuration, please refer to [Internationalization](translation.md).

```php
<?php

// Internationalization configuration

return [
    'params.invalid' => 'Params :param is invalid.',
];

use Hyperf\Constants\Annotation\Constants;
use Hyperf\Constants\Annotation\Message;
use Hyperf\Constants\EnumConstantsTrait;

#[Constants]
enum ErrorCode: int
{
    #[Message("params.invalid")]
    case PARAMS_INVALID = 1000;
}

$message = ErrorCode::SERVER_ERROR->getMessage(['param' => 'user_id']);
```
