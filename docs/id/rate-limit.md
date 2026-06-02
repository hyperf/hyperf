# Token Bucket Rate Limiter

## Instalasi

```bash
composer require hyperf/rate-limit
```

## Konfigurasi

### Publikasikan Konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### Penjelasan Konfigurasi

| Konfigurasi | Default | Tipe | Deskripsi |
|:-----------:|:-------:|:----:|:---------:|
| create         | 1      |int| Token yang dihasilkan per detik |
| consume        | 1      |int| Token yang dikonsumsi per request |
| capacity       | 2      |int| Kapasitas maksimum token bucket |
| limitCallback  | `[]`   |null\|callable| Method callback ketika rate limiting dipicu |
| waitTimeout    | 1      |int| Waktu tunggu antrean dalam detik |
| key            | URL request saat ini |callable\|string| Key untuk rate limiting |

## Menggunakan Rate Limiter

Komponen ini menyediakan annotation `Hyperf\RateLimit\Annotation\RateLimit` yang bisa diterapkan ke class dan method class, dengan kemampuan menimpa konfigurasi file. Contoh:

```php
<?php

namespace App\Controller;

use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peak 3"];
    }

    #[RequestMapping(path: "test2")]
    #[RateLimit(create: 2, consume: 2, capacity: 4)]
    public function test2()
    {
        return ["QPS 2, Peak 2"];
    }
}
``` 
Prioritas konfigurasi: `Method Annotation > Class Annotation > Configuration File > Default Configuration`

## Memicu Rate Limiting
Ketika rate limiting dipicu, secara default akan melemparkan `Hyperf\RateLimit\Exception\RateLimitException`.

Ini bisa ditangani melalui [Exception Handler](id/exception-handler.md) atau dengan mengkonfigurasi `limitCallback`.

Contoh:
```php
<?php

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
#[RateLimit(limitCallback: [RateLimitController::class, "limitCallback"])]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peak 3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds: Interval untuk pembuatan token berikutnya, dalam detik
        // $proceedingJoinPoint: Join point dari eksekusi request ini
        // Anda bisa memanggil `$proceedingJoinPoint->process()` untuk melanjutkan eksekusi, atau menanganinya sendiri
        return $proceedingJoinPoint->process();
    }
}
```

## Menyesuaikan Key Token Bucket Rate Limit

Key default didasarkan pada `url` request saat ini. Ketika satu pengguna memicu rate limiting, pengguna lain juga ikut terbatasi ketika meminta `url` ini.

Jika Anda membutuhkan rate limiting yang lebih granular, misalnya di level pengguna, Anda bisa melakukan rate limiting berdasarkan `ID` pengguna, sehingga jika pengguna A terkena rate limiting, pengguna B tetap bisa melakukan request secara normal:

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;

class TestController
{
    /**
     * @RateLimit(create=1, capacity=3, key={TestController::class, "getUserId"})
     */
    public function test()
    {
        return ["QPS 1, Peak 3"];
    }

    public static function getUserId(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        // Demikian pula, Anda bisa melakukan rate limiting berdasarkan dimensi lain seperti nomor telepon, alamat IP, dll.
        return $request->input('user_id');
    }
}
```
