# Circuit Breaker

## Instalasi

```
composer require hyperf/circuit-breaker
```

## Mengapa Kita Membutuhkan Circuit Breaker?

Dalam distributed systems, ketidaktersediaan sebuah layanan fundamental sering menyebabkan seluruh sistem menjadi tidak tersedia. Fenomena ini dikenal sebagai service avalanche effect. Untuk mengatasi service avalanche, praktik umum yang dilakukan adalah service degradation. Komponen [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) dirancang untuk menyelesaikan masalah ini.

## Menggunakan Circuit Breaker

Penggunaan circuit breaker sangat sederhana. Cukup tambahkan annotation `Hyperf\CircuitBreaker\Annotation\CircuitBreaker`, dan Anda dapat melakukan circuit breaking sesuai dengan kebijakan yang ditentukan.
Sebagai contoh, kita perlu mencari daftar pengguna di layanan lain. Daftar pengguna perlu digabungkan dengan banyak tabel, dan efisiensi query relatif rendah. Namun, ketika volume konkurensi tidak tinggi, kecepatan respons masih lumayan. Begitu volume konkurensi melonjak, hal itu akan menyebabkan kecepatan respons melambat dan menyebabkan slow query di layanan lawan. Pada saat ini, kita hanya perlu mengkonfigurasi circuit breaking timeout `timeout` menjadi 0,05 detik, failure counter `failCounter` menjadi lebih dari 1 kali untuk memicu circuit breaking, dan `fallback` yang sesuai ke method `searchFallback` dari kelas `App\Service\UserService`. Dengan cara ini, ketika respons timeout dan memicu circuit breaker, request tidak lagi dikirim ke layanan lawan, tetapi layanan akan di-degradasi, dan data akan dikembalikan dari proyek saat ini, yaitu dikembalikan sesuai dengan method yang ditentukan oleh `fallback`.

```php
<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\UserServiceClient;
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

Strategi circuit breaking default adalah `Timeout Strategy`. Jika Anda ingin mengimplementasikan strategi circuit breaking Anda sendiri, Anda hanya perlu mengimplementasikan `Handler` yang mewarisi dari `Hyperf\CircuitBreaker\Handler\AbstractHandler`.

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
            throw new TimeoutException('timeout, menggunakan ' . $use . 's', $result);
        }

        return $result;
    }
}
```
