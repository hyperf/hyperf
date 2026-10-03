# Enum Class

Ketika Anda perlu mendefinisikan kode error dan pesan error, Anda mungkin menggunakan pendekatan berikut:

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

Namun, implementasi ini kurang praktis. Setiap kali ingin mencari kode error dan pesan error yang sesuai, Anda harus mencarinya dua kali di `Class` yang sama. Oleh karena itu, framework menyediakan kelas enumerasi berbasis annotation.

## Instalasi

```
composer require hyperf/constants
```

## Penggunaan

### Mendefinisikan Enum Class

Anda dapat dengan cepat membuat enum class menggunakan perintah `gen:constant`.

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

    #[Message("Kesalahan parameter sistem")]
    case SYSTEM_INVALID = 700;
}
```

Pengguna dapat menggunakan `ErrorCode::SERVER_ERROR->getMessage()` untuk mendapatkan pesan error yang sesuai.

### Mendefinisikan Exception Class

Jika hanya menggunakan `Enum Class`, penanganan exception masih kurang praktis. Karena itu kita perlu mendefinisikan kelas exception sendiri, yaitu `BusinessException`. Ketika terjadi exception, kelas ini akan otomatis mencari pesan error yang sesuai berdasarkan kode error.

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

### Melempar Exception

Setelah menyelesaikan dua langkah di atas, Anda dapat melempar exception yang sesuai dalam logika bisnis Anda.

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

### Parameter Variabel

Saat menggunakan `ErrorCode::SERVER_ERROR->getMessage()` untuk mendapatkan pesan error, kita juga bisa melewatkan parameter variabel untuk menyusun pesan error, seperti contoh berikut:

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

### Internasionalisasi

Untuk mengaktifkan internasionalisasi pada komponen [hyperf/constants](https://github.com/hyperf/constants), Anda perlu menginstal komponen [hyperf/translation](https://github.com/hyperf/translation) dan mengonfigurasi file bahasa:

```
composer require hyperf/translation
```

Untuk konfigurasi terkait, silakan merujuk ke [Internasionalisasi](id/translation.md).

```php
<?php

// Konfigurasi internasionalisasi

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
