# Kelas Enum

Ketika Anda perlu mendefinisikan kode error dan pesan error, metode berikut dapat
digunakan,

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

Namun metode implementasi ini kurang bersahabat. Setiap kali Anda ingin mencari
kode error dan informasi error yang sesuai, Anda harus mencari di `Class` saat
ini sebanyak dua kali, sehingga framework menyediakan kelas enumerasi berbasis
annotation.

## Instalasi

```
composer require hyperf/constants
```

## Penggunaan

### Mendefinisikan Kelas Enum

Kelas enumerasi dapat dibuat dengan cepat menggunakan perintah `gen:constant`.

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

User dapat menggunakan `ErrorCode::SERVER_ERROR->getMessage()` untuk mendapatkan
pesan error yang sesuai.

### Mendefinisikan Kelas Exception

Jika Anda hanya menggunakan `kelas enumerasi`, hal tersebut kurang nyaman saat
menangani exception. Oleh karena itu, kita perlu mendefinisikan kelas exception
kita sendiri, yaitu `BusinessException`. Ketika exception terjadi, kelas ini
secara aktif akan mencari informasi error yang sesuai berdasarkan kode error.

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

Setelah menyelesaikan dua langkah di atas, exception yang sesuai dapat dilempar
di dalam business logic.

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

Saat menggunakan `ErrorCode::SERVER_ERROR->getMessage()` untuk mendapatkan
pesan error yang sesuai, kita juga dapat meneruskan parameter variabel untuk
menggabungkan pesan error. Sebagai contoh berikut:

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

### Globalisasi

> Fitur ini hanya tersedia pada versi v1.1.13 dan yang lebih baru

Untuk mengaktifkan komponen [hyperf/constants](https://github.com/hyperf/constants)
agar mendukung internasionalisasi, komponen [hyperf/translation](https://github.com/hyperf/translation)
harus diinstal dan dikonfigurasi dengan file bahasa yang tepat, sebagai berikut:

```
composer require hyperf/translation
```

Untuk konfigurasi terkait, lihat [Internasionalisasi](id/translation.md)

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
