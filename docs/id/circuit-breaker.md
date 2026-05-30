# Circuit Breaker

## Instalasi

```
composer require hyperf/circuit-breaker
```

## Mengapa Anda membutuhkan Circuit Breaker?

Dalam sistem terdistribusi, sering kali seluruh sistem tidak tersedia karena
tidak tersedianya layanan dasar. Fenomena ini disebut sebagai efek longsoran
layanan (service avalanche effect). Untuk menanggapi longsoran layanan, praktik
umum yang dilakukan adalah melakukan downgrade layanan. Komponen
[hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) dirancang
untuk menyelesaikan masalah ini.

## Penggunaan

## Mengapa Anda membutuhkan Circuit Breaker?

Dalam sistem terdistribusi, sering kali seluruh sistem tidak tersedia karena
tidak tersedianya layanan dasar. Fenomena ini disebut sebagai efek longsoran
layanan (service avalanche effect). Untuk menanggapi longsoran layanan, praktik
umum yang dilakukan adalah melakukan downgrade layanan. Komponen
[hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) dirancang
untuk menyelesaikan masalah ini.

## Menggunakan Circuit Breaker

Penggunaan Circuit Breaker sangat sederhana, cukup tambahkan anotasi
`Hyperf\CircuitBreaker\Annotation\CircuitBreaker`, Anda dapat melakukan
circuit break sesuai dengan strategi yang ditentukan.

Sebagai contoh, kita perlu mencari daftar pengguna di layanan lain. Daftar
pengguna tersebut perlu diasosiasikan dengan banyak tabel. Efisiensi kueri
rendah, namun ketika jumlah konkurensi normal, kecepatan respons masih wajar.
Begitu jumlah konkurensi meningkat, hal tersebut akan memperlambat respons dan
menyebabkan layanan lain melambat. Pada saat ini, kita hanya perlu mengonfigurasi
waktu timeout circuit break `timeout` sebesar 0,05 detik, jumlah kegagalan
`failCounter` agar terputus (blown) setelah lebih dari 1 kali kegagalan, dan
`fallback` yang sesuai adalah metode `searchFallback` dari kelas
`App\UserService`. Dengan cara ini, ketika respons mengalami timeout dan memicu
circuit break, sistem tidak akan meminta layanan rekanan lagi. Sebaliknya,
sistem akan langsung melakukan downgrade layanan dari aplikasi saat ini, yaitu
mengembalikan hasil sesuai dengan metode yang ditentukan oleh `fallback`.

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\UserServiceClient;
use Hyperf\CircuitBreaker\Annotation\CircuitBreaker;
use Hyperf\Di\Annotation\Inject;

class UserService
{
    #[Inject]
    private UserServiceClient $client;

    #[CircuitBreaker(options: ['timeout' => 0.05], failCounter: 1, successCounter: 1, fallback: [UserService::class, 'searchFallback'])]
    public function search($offset, $limit)
    {
        return $this->client->users($offset, $limit);
    }

    public function searchFallback($offset, $limit)
    {
        return [];
    }
}
```

Kebijakan circuit break default adalah `Timeout Policy`. Jika Anda ingin
mengimplementasikan sendiri kebijakan circuit break Anda, Anda hanya perlu
mengimplementasikan `Handler` yang mewarisi
`Hyperf\CircuitBreaker\Handler\AbstractHandler`.

```php
<?php
declare(strict_types=1);

namespace Hyperf\CircuitBreaker\Handler;

use Hyperf\CircuitBreaker\Annotation\CircuitBreaker as Annotation;
use Hyperf\CircuitBreaker\CircuitBreaker;
use Hyperf\CircuitBreaker\Exception\TimeoutException;
use Hyperf\Di\Aop\ProceedingJoinPoint;

class DemoHandler extends AbstractHandler
{
    const DEFAULT_TIMEOUT = 5;

    protected function process(ProceedingJoinPoint $proceedingJoinPoint, CircuitBreaker $breaker, Annotation $annotation)
    {
        $result = $proceedingJoinPoint->process();

        if (is_break()) {
            throw new TimeoutException('timeout, use ' . $use . 's', $result);
        }

        return $result;
    }
}
```
