# Rate Limiter Token Bucket

## Installation

```bash
composer require hyperf/rate-limit
```

## Configuration

### Publikasikan Konfigurasi

```bash
php bin/hyperf.php vendor:publish hyperf/rate-limit
```

### Deskripsi Konfigurasi

|  config item   | default |         remark        |
|:--------------:|:-------:|:---------------------:|
| create         | 1       | Jumlah token yang dihasilkan per detik            |
| consume        | 1       | Jumlah token yang dikonsumsi per request            |
| capacity       | 2       | Kapasitas maksimum dari token bucket                 |
| limitCallback  | `[]`    | Metode callback saat rate limit dipicu               |
| waitTimeout    | 1       | Timeout dalam antrean tunggu                          |

## Usage

Komponen ini menyediakan annotation `Hyperf\RateLimit\Annotation\RateLimit` yang
bekerja pada class dan method class, serta dapat menimpa (override) file
konfigurasi. Sebagai contoh:

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
        return ["QPS 1, Peek3"];
    }

    #[RequestMapping(path: "test2")]
    #[RateLimit(create: 2, consume: 2, capacity: 4)]
    public function test2()
    {
        return ["QPS 2, Peek2"];
    }
}
``` 
Prioritas konfigurasi: `Method Annotation > Class Annotation > File Konfigurasi > Konfigurasi Default`

## Memicu Rate Limit

Ketika rate limit dipicu, `Hyperf\RateLimit\Exception\RateLimitException` akan
dilempar (thrown) secara default.

Anda dapat menggunakan [Exception Handler](id/exception-handler.md) atau
mengonfigurasi `limitCallback` untuk menangani callback saat rate limit dipicu.

Sebagai contoh:
```php
<?php

namespace App\Controller;

use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\RateLimit\Annotation\RateLimit;

#[Controller(prefix: "rate-limit")]
#[RateLimit(limitCallback: {RateLimitController::class, "limitCallback"})]
class RateLimitController
{
    #[RequestMapping(path: "test")]
    #[RateLimit(create: 1, capacity: 3)]
    public function test()
    {
        return ["QPS 1, Peek3"];
    }
    
    public static function limitCallback(float $seconds, ProceedingJoinPoint $proceedingJoinPoint)
    {
        // $seconds Token generation time interval, in seconds
        // $proceedingJoinPoint The entry point for the execution of this request
        // You can handle it by yourself, or continue its execution by calling `$proceedingJoinPoint->process()`
        return $proceedingJoinPoint->process();
    }
}
```

## Kustomisasi Key Rate Limit Token Bucket

Key default didasarkan pada `url` dari request saat ini. Ketika seorang user
memicu rate limit, user lain juga akan dibatasi untuk me-request `url` ini.

Jika pembatasan rate limit dengan granularitas berbeda diperlukan, seperti
pembatasan pada dimensi user, rate limit dapat dilakukan berdasarkan `ID` user,
sehingga ketika user A dibatasi, user B tetap dapat me-request dengan normal:

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
    #[RateLimit(create: 1, capacity: 3, key: {TestController::class, "getUserId"})]
    public function test()
    {
        return ["QPS 1, 峰值3"];
    }

    public static function getUserId(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        // In the same way, traffic can be limited based on different dimensions such as mobile phone number and IP address.
        return $request->input('user_id');
    }
}
```
