# Retry

Komunikasi jaringan pada dasarnya tidak stabil, sehingga dalam distributed systems, desain fault-tolerant yang baik sangat diperlukan. Retry tanpa pandang bulu sangat berbahaya. Ketika terjadi masalah komunikasi, jika setiap request di-retry sekali, itu setara dengan peningkatan 100% pada beban IO sistem, yang dapat dengan mudah memicu avalanche. Retry juga harus mempertimbangkan penyebab error. Jika itu adalah masalah yang tidak dapat diselesaikan dengan retry, melakukan retry hanya membuang-buang sumber daya. Selain itu, jika interface yang di-retry tidak idempoten, hal ini juga dapat menyebabkan masalah seperti inkonsistensi data.

Komponen ini menyediakan serangkaian mekanisme retry yang kaya untuk memenuhi kebutuhan retry dalam berbagai skenario.


## Instalasi

```bash
composer require hyperf/retry
```

## Hello World

Tambahkan annotation `#[Retry]` pada method yang membutuhkan retry.

```php
/**
 * Retry method ketika terjadi exception
 */
#[Retry]
public function foo()
{
    // Memulai remote call
}
```

Strategi Retry default dapat memenuhi sebagian besar kebutuhan retry harian dan tidak akan menyebabkan avalanche akibat retry yang berlebihan.

## Kustomisasi Mendalam

Komponen ini mencapai pluggability dengan menggabungkan beberapa retry strategies. Setiap strategi fokus pada aspek yang berbeda dari proses retry, seperti retry judgment, retry interval, result processing, dll. Dengan menyesuaikan strategi yang digunakan dalam annotation, Anda dapat mengkonfigurasi aspek retry yang diadaptasi untuk skenario apa pun.

Sangat disarankan untuk membangun alias annotation Anda sendiri sesuai dengan kebutuhan bisnis spesifik. Di bawah ini kami mendemonstrasikan cara membuat annotation baru dengan jumlah maksimum percobaan sebanyak 3.

> Dalam annotation `Retry` default, Anda dapat mengontrol jumlah maksimum retry melalui `#[Retry(maxAttempts=3)]`. Untuk tujuan demonstrasi, anggaplah itu tidak ada.

Pertama, Anda perlu membuat `annotation class` baru dan mewarisi `\Hyperf\Retry\Annotations\AbstractRetry`.

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

Sesuai kebutuhan Anda, override properti `$policies`. Untuk membatasi jumlah retry, Anda perlu menggunakan `MaxAttemptsRetryPolicy`. `MaxAttemptsRetryPolicy` juga membutuhkan parameter, yaitu batas maksimum percobaan, `$maxAttempts`. Tambahkan kedua properti ini ke kelas di atas.

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

Sekarang, annotation `#[MyRetry]` akan menyebabkan method apa pun di-loop sebanyak tiga kali. Kita juga perlu menambahkan strategi baru, `ClassifierRetryPolicy`, untuk mengontrol jenis error apa yang dapat di-retry. Setelah menambahkan `ClassifierRetryPolicy`, secara default ia hanya akan melakukan retry setelah melemparkan `Throwable`.

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

Anda dapat terus menyempurnakan annotation ini hingga memenuhi kebutuhan kustom Anda. Sebagai contoh, konfigurasikan untuk hanya me-retry `TimeoutException` yang didefinisikan pengguna, dan menggunakan interval variabel di mana retry tidur setidaknya selama 100 milidetik. Caranya sebagai berikut:

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

Selama Anda memastikan bahwa file tersebut di-scan oleh Hyperf, Anda dapat menggunakan annotation `#[MyRetry]` di dalam method untuk me-retry error timeout.

## Konfigurasi Default

Properti default lengkap dari annotation `#[Retry]` adalah sebagai berikut:

```php
/**
 * Array dari retry policies. Anggap ini sebagai middleware yang ditumpuk.
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
 * Algoritma untuk interval retry.
 */
public string $sleepStrategyClass = SleepStrategyInterface::class;

/**
 * Maksimum percobaan.
 */
public int $maxAttempts = 10;

/**
 * Retry Budget.
 * ttl: Detik masa berlaku token.
 * minRetriesPerSec: Kecepatan dasar pembuatan token retry.
 * percentCanRetry: Hasilkan token baru dengan rasio ini dari volume request.
 *
 * @var array|RetryBudgetInterface
 */
public $retryBudget = [
    'ttl' => 10,
    'minRetriesPerSec' => 1,
    'percentCanRetry' => 0.2,
];

/**
 * Interval waktu dasar (ms) untuk setiap percobaan. Untuk backoff strategy ini adalah interval untuk percobaan pertama,
 * sedangkan untuk flat strategy ini adalah interval untuk setiap percobaan.
 */
public int $base = 0;

/**
 * Mengonfigurasi Predicate yang mengevaluasi apakah sebuah exception harus di-retry.
 * Predicate harus mengembalikan true jika exception harus di-retry, jika tidak harus mengembalikan false.
 *
 * @var callable|string
 */
public $retryOnThrowablePredicate = '';

/**
 * Mengonfigurasi Predicate yang mengevaluasi apakah sebuah hasil harus di-retry.
 * Predicate harus mengembalikan true jika hasil harus di-retry, jika tidak harus mengembalikan false.
 *
 * @var callable|string
 */
public $retryOnResultPredicate = '';

/**
 * Mengonfigurasi daftar kelas Throwable yang dicatat sebagai kegagalan dan karenanya di-retry.
 * Throwable apa pun yang cocok atau mewarisi dari salah satu daftar akan di-retry, kecuali diabaikan melalui ignoreExceptions.
 *
 * Mengabaikan Throwable memiliki prioritas lebih tinggi daripada me-retry exception.
 *
 * @var array<string|\Throwable>
 */
public $retryThrowables = [\Throwable::class];

/**
 * Mengonfigurasi daftar kelas error yang diabaikan dan karenanya tidak di-retry.
 * Exception apa pun yang cocok atau mewarisi dari salah satu daftar tidak akan di-retry, bahkan jika ditandai melalui retryExceptions.
 *
 * @var array<string|\Throwable>
 */
public $ignoreThrowables = [];

/**
 * Callable fallback ketika semua percobaan habis.
 *
 * @var callable|string
 */
public $fallback = '';
```

## Strategi Opsional

### Max Attempts Policy `MaxAttemptsRetryPolicy`

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| maxAttempts | int | Jumlah maksimum percobaan |


### Error Classifier Policy `ClassifierRetryPolicy`

Menentukan apakah sebuah error dapat di-retry melalui classifier.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| ignoreThrowables | array | Nama kelas `Throwable` yang diabaikan. Memiliki prioritas lebih tinggi dari `retryThrowables` |
| retryThrowables | array | Nama kelas `Throwable` yang perlu di-retry. Memiliki prioritas lebih tinggi dari `retryOnThrowablePredicate` |
| retryOnThrowablePredicate | callable | Menentukan apakah `Throwable` dapat di-retry melalui sebuah fungsi. Jika dapat di-retry, return true, jika tidak return false. |
| retryOnResultPredicate | callable | Menentukan apakah nilai kembalian dapat di-retry melalui sebuah fungsi. Jika dapat di-retry, return true, jika tidak return false. |

### Fallback Policy `FallbackRetryPolicy`

Menjalankan method alternatif setelah sumber daya retry habis.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| fallback | callable | Method fallback |

Selain kode yang dapat diidentifikasi oleh `is_callable`, `fallback` juga dapat diisi dalam format `class@method`. Framework akan mendapatkan `class` yang sesuai dari `Container` dan kemudian menjalankan method `method`-nya.

### Sleep Policy `SleepRetryPolicy`

Menyediakan dua strategi interval retry: Flat Retry Interval (FlatStrategy) dan Variable-Length Retry Interval (BackoffStrategy).

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| base | int | Waktu tidur dasar (milidetik) |
| strategy | string | Nama kelas apa pun yang mengimplementasikan `Hyperf\Retry\SleepStrategyInterface`, seperti `Hyperf\Retry\BackoffStrategy` |

### Timeout Policy `TimeoutRetryPolicy`

Keluar dari sesi retry setelah total waktu eksekusi melebihi waktu yang ditentukan.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| timeout | float | Waktu timeout (detik) |

### Circuit Breaker Policy `CircuitBreakerRetryPolicy`

Setelah retry gagal dan keluar dari sesi retry, secara langsung ditandai sebagai fused untuk jangka waktu tertentu, dan tidak ada percobaan lebih lanjut yang dilakukan.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| circuitBreakerState.resetTimeout | float | Waktu yang diperlukan untuk pemulihan (detik) |

### Budget Policy `BudgetRetryPolicy`

Setiap annotation `#[Retry]` menghasilkan token bucket yang sesuai. Setiap kali method yang diberi annotation dipanggil, sebuah token dengan waktu kedaluwarsa (ttl) ditempatkan di token bucket. Jika terjadi error yang dapat di-retry, sejumlah token yang sesuai (percentCanRetry) harus dikonsumsi sebelum melakukan retry, jika tidak maka tidak akan di-retry (error terus diteruskan ke bawah). Sebagai contoh, ketika percentCanRetry=0.2, 5 token dikonsumsi untuk setiap retry. Dengan demikian, ketika peer crash, itu hanya akan menyebabkan maksimum 20% konsumsi retry tambahan, yang seharusnya dapat diterima untuk sebagian besar sistem.

Untuk mengakomodasi beberapa method dengan frekuensi penggunaan yang lebih rendah, sejumlah token "minimum" (minRetriesPerSec) dihasilkan setiap detik untuk memastikan stabilitas sistem.

| Parameter | Tipe | Deskripsi |
| ---------- | --- | --- |
| retryBudget.ttl | int | Waktu kedaluwarsa token (detik) |
| retryBudget.minRetriesPerSec | int | Jumlah minimum retry yang dijamin per detik |
| retryBudget.percentCanRetry | float | Persentase retry yang tidak melebihi total jumlah request |

> Token bucket dari komponen retry tidak dibagikan antar worker, sehingga jumlah retry akhir harus dikalikan dengan jumlah worker.

## Alias Annotation

Karena konfigurasi retry annotation relatif kompleks, beberapa alias preset disediakan di sini untuk kemudahan penulisan.

* `#[RetryThrowable]` hanya me-retry `Throwable`. Sama seperti `#[Retry]` default.

* `#[RetryFalsy]` hanya me-retry error di mana nilai kembalian secara loose sama dengan false ($result == false), dan tidak me-retry exception.

* `#[BackoffRetryThrowable]` Versi interval retry variabel dari `#[RetryThrowable]`, dengan interval retry setidaknya 100 milidetik.

* `#[BackoffRetryFalsy]` Versi interval retry variabel dari `#[RetryFalsy]`, dengan interval retry setidaknya 100 milidetik.

## Fluent Chained Calls

Selain menggunakan komponen ini dengan metode annotation, Anda juga dapat menggunakannya melalui fungsi PHP biasa.

```php
<?php

$result = \Hyperf\Retry\Retry::with(
    new \Hyperf\Retry\Policy\ClassifierRetryPolicy(), // Default retry semua Throwable
    new \Hyperf\Retry\Policy\MaxAttemptsRetryPolicy(5) // Retry maksimal 5 kali
)->call(function(){
    if (rand(1, 100) >= 20){
        return true;
    }
    throw new Exception;
});
```
Untuk meningkatkan keterbacaan, Anda juga dapat menggunakan sintaks fluent berikut.

```php
<?php

$result = \Hyperf\Retry\Retry::whenReturns(false) // Retry ketika mengembalikan false
    ->max(3) // Maksimal 3 kali
    ->inSeconds(5) // Maksimal 5 detik
    ->sleep(1) // Interval 1 milidetik
    ->fallback(function(){return true;}) // Fungsi fallback
    ->call(function(){
        if (rand(1, 100) >= 20){
            return true;
        }
        return false;
    });
```
