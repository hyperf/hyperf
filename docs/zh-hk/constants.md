# 枚舉類

當您需要定義錯誤碼和錯誤信息時，可能會使用以下方式，

```php
<?php

class ErrorCode
{
    const SERVER_ERROR = 500;
    const PARAMS_INVALID = 1000;

    public static $messages = [
        self::SERVER_ERROR => 'Server Error',
        self::PARAMS_INVALID => '參數非法'
    ];
}

$message = ErrorCode::messages[ErrorCode::SERVER_ERROR] ?? '未知錯誤';

```

但這種實現方式並不友好，每當要查詢錯誤碼與對應錯誤信息時，都要在當前 `Class` 中搜索兩次，所以框架提供了基於註解的枚舉類。

## 安裝

```
composer require hyperf/constants
```

## 使用

### 定義枚舉類

通過 `gen:constant` 命令可以快速的生成一個枚舉類。

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
    use EnumConstantsTrait
    #[Message("Server Error！")]
    case SERVER_ERROR = 500;

    #[Message("系統參數錯誤")]
    case SYSTEM_INVALID = 700;
}
```

用户可以使用 `ErrorCode::getMessage(ErrorCode::SERVER_ERROR)` 來獲取對應錯誤信息。

### 定義異常類

如果單純使用 `枚舉類`，在異常處理的時候，還是不夠方便。所以我們需要自己定義異常類 `BusinessException`，當有異常進來，會根據錯誤碼主動查詢對應錯誤信息。

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

### 拋出異常

完成上面兩步，就可以在業務邏輯中，拋出對應異常了。

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

### 可變參數

在使用 `ErrorCode::getMessage(ErrorCode::SERVER_ERROR)` 來獲取對應錯誤信息時，我們也可以傳入可變參數，進行錯誤信息組合。比如以下情況

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

$message = ErrorCode::getMessage(ErrorCode::PARAMS_INVALID, ['user_id']);

// 1.2 版本以下 可以使用以下方式，但會在 1.2 版本移除
$message = ErrorCode::getMessage(ErrorCode::PARAMS_INVALID, 'user_id');
```

### 國際化

要使 [hyperf/constants](https://github.com/hyperf/constants) 組件支持國際化，就必須要安裝 [hyperf/translation](https://github.com/hyperf/translation) 組件並配置好語言文件，如下：

```
composer require hyperf/translation
```

相關配置詳見 [國際化](zh-hk/translation.md)

```php
<?php

// 國際化配置

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

$message = ErrorCode::getMessage(ErrorCode::SERVER_ERROR, ['param' => 'user_id']);
```
