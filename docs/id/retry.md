# Retry

Komunikasi jaringan pada dasarnya tidak stabil, sehingga dalam sistem
terdistribusi, diperlukan desain toleransi kesalahan (fault-tolerant) yang
baik. Melakukan retry secara membabi buta sangatlah berbahaya. Ketika terjadi
masalah komunikasi, jika setiap request di-retry sekali saja, hal ini setara
dengan peningkatan beban IO sistem sebesar 100%, yang dapat dengan mudah memicu
bencana avalanche. Retry juga harus mempertimbangkan penyebab kesalahan. Jika
kesalahannya adalah masalah yang tidak dapat diselesaikan dengan retry, maka
melakukan retry hanya akan membuang-buang sumber daya. Selain itu, jika
interface yang di-retry tidak bersifat idempotent, hal ini juga dapat
menyebabkan inkonsistensi data dan masalah lainnya.

Komponen ini menyediakan mekanisme retry yang kaya untuk memenuhi kebutuhan
retry dalam berbagai skenario.

## Instalasi

```bash
composer require hyperf/retry
```

## Hello World

Tambahkan annotation `#[Retry]` ke method yang perlu di-retry.

```php
/**
 * Retry the method on exception
 */
#[Retry]
public function foo()
{
    // make a remote call
}
```

Strategi Retry default dapat memenuhi sebagian besar kebutuhan retry sehari-hari
tanpa retry berlebih yang dapat menyebabkan avalanche.

## Kustomisasi Mendalam

Komponen ini mencapai sifat pluggable dengan menggabungkan beberapa strategi
retry. Setiap strategi berfokus pada aspek yang berbeda dari proses retry,
seperti penilaian retry, interval retry, dan pemrosesan hasil. Dengan
menyesuaikan strategi yang digunakan pada annotation, Anda dapat mengonfigurasi
aspek retry yang sesuai untuk skenario apa pun.

Disarankan untuk membuat alias annotation Anda sendiri sesuai dengan kebutuhan
bisnis yang spesifik. Di bawah ini kami mendemonstrasikan cara membuat
annotation baru dengan jumlah percobaan maksimal 3 kali.

> Pada annotation `Retry` default, Anda dapat mengontrol jumlah maksimum retry
> dengan `#[Retry(maxAttempts=3)]`. Demi demonstrasi, anggap saja fitur ini
> tidak ada.

Pertama, Anda perlu membuat sebuah `annotation class` dan mewarisi
`\Hyperf\Retry\Annotations\AbstractRetry`.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
}
```

Override property `$policies` sesuai dengan kebutuhan Anda. Untuk membatasi
jumlah retry, gunakan `MaxAttemptsRetryPolicy`. `MaxAttemptsRetryPolicy` juga
membutuhkan sebuah parameter, yaitu batas jumlah percobaan maksimum,
`$maxAttempts`. Tambahkan kedua property ini ke class di atas.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
    ];
    public $maxAttempts = 3;
}
```

Sekarang, annotation `#[MyRetry]` akan menyebabkan method apa pun dijalankan
tiga kali dalam perulangan. Kita juga perlu menambahkan policy baru
`ClassifierRetryPolicy` untuk mengontrol error apa saja yang dapat di-retry.
Secara default, penambahan `ClassifierRetryPolicy` hanya akan melakukan retry
setelah dilemparkannya `Throwable`.

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\AbstractRetry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
        ClassifierRetryPolicy::class,
    ];
    public $maxAttempts = 3;
}
```

Anda dapat terus menyempurnakan annotation tersebut hingga memenuhi kebutuhan
kustomisasi Anda. Sebagai contoh, konfigurasikan untuk hanya melakukan retry
pada `TimeoutException` yang ditentukan pengguna, dan gunakan interval jeda
(sleep) variabel dengan panjang minimal 100ms untuk retry, sebagai berikut:

```php
<?php

declare(strict_types=1);

namespace App\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

#[Attribute(Attribute::TARGET_METHOD)]
class MyRetry extends \Hyperf\Retry\Annotation\Retry
{
    public $policies = [
        MaxAttemptsRetryPolicy::class,
        ClassifierRetryPolicy::class,
        SleepRetryPolicy::class,
    ];
    public $maxAttempts = 3;
    public $base = 100;
    public $strategy = \Hyperf\Retry\BackoffStrategy::class;
    public $retryThrowables = [\App\Exception\TimeoutException::class];
}
```

Cukup pastikan file tersebut dipindai (scanned) oleh Hyperf, maka Anda dapat
menggunakan annotation `#[MyRetry]` pada method untuk melakukan retry pada error
timeout.

## Konfigurasi Default

Properti default annotation lengkap dari `#[Retry]` adalah sebagai berikut:

```php
/**
 * Array of retry policies. Think of these as stacked middlewares.
 * @var string[]
 */
public $policies = [
    FallbackRetryPolicy::class,
    ClassifierRetryPolicy::class,
    BudgetRetryPolicy::class,
    MaxAttemptsRetryPolicy::class,
    SleepRetryPolicy::class,
];

/**
 * The algorithm for retry intervals.
 */
public string $sleepStrategyClass = SleepStrategyInterface::class;

/**
 * Max Attampts.
 */
public int $maxAttempts = 10;

/**
 * Retry Budget.
 * ttl: Seconds of token lifetime.
 * minRetriesPerSec: Base retry token generation speed.
 * percentCanRetry: Generate new token at this ratio of the request volume.
 *
 * @var array|RetryBudgetInterface
 */
public $retryBudget = [
    'ttl' => 10,
    'minRetriesPerSec' => 1,
    'percentCanRetry' => 0.2,
];

/**
 * Base time inteval (ms) for each try. For backoff strategy this is the interval for the first try
 * while for flat strategy this is the interval for every try.
 */
public int $base = 0;

/**
 * Configures a Predicate which evaluates if an exception should be retried.
 * The Predicate must return true if the exception should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnThrowablePredicate = '';

/**
 * Configures a Predicate which evaluates if an result should be retried.
 * The Predicate must return true if the result should be retried, otherwise it must return false.
 *
 * @var callable|string
 */
public $retryOnResultPredicate = '';

/**
 * Configures a list of Throwable classes that are recorded as a failure and thus are retried.
 * Any Throwable matching or inheriting from one of the list will be retried, unless ignored via ignoreExceptions.
 *
 * Ignoring an Throwable has priority over retrying an exception.
 *
 * @var array<string|\Throwable>
 */
public $retryThrowables = [\Throwable::class];

/**
 * Configures a list of error classes that are ignored and thus are not retried.
 * Any exception matching or inheriting from one of the list will not be retried, even if marked via retryExceptions.
 *
 * @var array<string|\Throwable>
 */
public $ignoreThrowables = [];

/**
 * The fallback callable when all attempts exhausted.
 *
 * @var callable|string
 */
public $fallback = '';
```

## Strategi Pilihan

### Policy Percobaan Maksimum `MaxAttemptsRetryPolicy`

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| maxAttempts | int | Jumlah percobaan maksimum |

### Policy Klasifikasi Error `ClassifierRetryPolicy`

Operasikan classifier untuk menentukan apakah error dapat di-retry.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| ignoreThrowables | array | Nama class `Throwable` yang diabaikan. Memiliki prioritas lebih tinggi daripada `retryThrowables` |
| retryThrowables | array | Nama class `Throwable` yang di-retry. Memiliki prioritas lebih tinggi daripada `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | Mengirimkan fungsi untuk menentukan apakah `Throwable` dapat di-retry. Mengembalikan true jika retry memungkinkan, false jika tidak. |
| retryOnResultPredicate | callable | Menggunakan fungsi untuk menentukan apakah nilai kembalian (return value) dapat di-retry. Mengembalikan true jika retry memungkinkan, false jika tidak. |

### Policy Fallback `FallbackRetryPolicy`

Menjalankan method alternatif setelah percobaan retry kehabisan sumber daya.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| fallback | callable | method fallback |

Selain kode yang dikenali oleh `is_callable`, `fallback` juga dapat diisi
dengan format `class@method`. Framework akan mengambil `class` yang sesuai dari
`Container`, lalu mengeksekusi method `method`-nya.

### Policy Jeda `SleepRetryPolicy`

Menyediakan dua strategi jeda retry. Jeda retry yang sama (FlatStrategy) dan
jeda retry variabel (BackoffStrategy).

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| base | int | Waktu jeda dasar (ms) |
| strategy | string | Nama class apa pun yang mengimplementasikan `Hyperf\Retry\SleepStrategyInterface`, seperti `Hyperf\Retry\BackoffStrategy` |

### Policy Timeout `TimeoutRetryPolicy`

Keluar dari sesi retry setelah total waktu eksekusi melebihi batas waktu.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| timeout | float | batas waktu (detik) |

### Policy Circuit Breaker `CircuitBreakerRetryPolicy`

Setelah retry gagal, sesi retry akan langsung ditandai sebagai circuit breaker
selama jangka waktu tertentu, dan tidak ada percobaan lebih lanjut yang akan
dilakukan.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout | float | Waktu yang dibutuhkan untuk pemulihan (detik) |

### Policy Anggaran `BudgetRetryPolicy`

Setiap annotation `#[Retry]` akan menghasilkan token bucket yang sesuai, dan
setiap kali method ber-annotation tersebut dipanggil, sebuah token dengan waktu
kedaluwarsa (ttl) akan dimasukkan ke dalam token bucket. Jika terjadi error
yang dapat di-retry, sejumlah token yang sesuai (percentCanRetry) harus
dikonsumsi sebelum melakukan retry, jika tidak, retry tidak akan dilakukan (error
akan terus diteruskan ke bawah). Sebagai contoh, ketika percentCanRetry=0.2,
setiap retry mengonsumsi 5 token. Dengan cara ini, ketika peer sedang down,
maksimal 20% konsumsi retry tambahan yang akan terjadi, yang seharusnya dapat
diterima oleh sebagian besar sistem.

Untuk mengakomodasi beberapa method yang lebih jarang digunakan, sejumlah
tertentu token "jaminan minimal" (minRetriesPerSec) juga dihasilkan per detik
untuk memastikan stabilitas sistem.

| Parameter | Tipe | Deskripsi |
| --- | --- | --- |
| retryBudget.ttl | int | Waktu kedaluwarsa token pemulihan (detik) |
| retryBudget.minRetriesPerSec | int | Jumlah minimum retry per detik untuk "jaminan minimal" |
| retryBudget.percentCanRetry | float | Jumlah retry tidak melebihi persentase dari total request |

> Token bucket dari komponen retry tidak dibagi di antara worker, sehingga
> jumlah akhir retry dikalikan dengan jumlah worker.

## Alias Annotation

Karena konfigurasi annotation retry cukup rumit, beberapa alias bawaan
disediakan di sini untuk kemudahan penulisan.

* `#[RetryThrowable]` hanya me-retry `Throwable`. Sama seperti `#[Retry]` default.

* `#[RetryFalsy]` hanya me-retry error yang nilai kembaliannya secara longgar
  sama dengan false ($result == false), bukan exception.

* `#[BackoffRetryThrowable]` Versi interval jeda retry variabel dari
  `#[RetryThrowable]`, dengan interval jeda retry minimal 100ms.

* `#[BackoffRetryFalsy]` Versi interval jeda retry variabel dari
  `#[RetryFalsy]`, dengan interval jeda retry minimal 100ms.

## Pemanggilan Berantai yang Fasih (Fluent Chain Call)

Selain menggunakan komponen ini pada method yang memiliki annotation, Anda juga
dapat menggunakannya pada fungsi PHP biasa.

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), // Retry all Throwables by default
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) //Retry up to 5 times
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```

Untuk meningkatkan keterbacaan, penulisan fasih (fluent writing) berikut juga
dapat digunakan.

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // Retry when false is returned
    ->max(3) // up to 3 times
    ->inSeconds(5) // up to 5 seconds
    ->sleep(1) // 1ms interval
    ->fallback(function(){return true;}) // fallback function
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
