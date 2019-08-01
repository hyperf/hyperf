# 枚举类

当您需要定义错误码和错误信息时，可能会使用以下方式，

```php
<?php

class ErrorCode
{
    const SERVER_ERROR = 500;
    const PARAMS_INVALID = 1000;
    
    public static $messages = [
        self::SERVER_ERROR => 'Server Error',
        self::PARAMS_INVALID => '参数非法'
    ];
}

$message = ErrorCode::messages[ErrorCode::SERVER_ERROR] ?? '未知错误';

```

但这种实现方式并不友好，每当要查询错误码与对应错误信息时，都要在当前 `Class` 中搜索两次。所以框架提供了基于注解的枚举类。

## 安装

```
composer require hyperf/constants
```

## 使用

### 定义枚举类

```php
<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("Server Error！")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("系统参数错误")
     */
    const SYSTEM_INVALID = 700;
}

```

用户可以使用 `ErrorCode::getMessage(ErrorCode::SERVER_ERROR)` 来获取对应错误信息。

### 定义异常类

如果单纯使用 `枚举类`，在异常处理的时候，还是不够方便。所以我们需要自己定义异常类 `BusinessException`，当有异常进来，会根据错误码主动查询对应错误信息。

```php
<?php

declare(strict_types=1);

namespace App\Exception;

use App\Constants\ErrorCode;
use Hyperf\Server\Exception\ServerException;
use Throwable;

class BusinessException extends ServerException
{
    public function __construct(int $code = 0, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $message = ErrorCode::getMessage($code);
        }

        parent::__construct($message, $code, $previous);
    }
}

```

### 抛出异常

完成上面两步，就可以在业务逻辑中，抛出对应异常了。

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;

class IndexController extends Controller
{
    public function index()
    {
        throw new BusinessException(ErrorCode::SERVER_ERROR);
    }
}

```

