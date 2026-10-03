# Annotation

Annotation adalah fitur yang sangat kuat di Hyperf. Dengan annotation, Anda bisa mengurangi banyak konfigurasi dan mengimplementasikan berbagai fungsi dengan mudah.

## Konsep

### Apa itu Annotation?

Annotation menyediakan cara untuk menambahkan metadata terstruktur yang bisa dibaca mesin ke bagian deklarasi dalam kode. Target annotation bisa berupa class, method, function, parameter, property, atau class constant. Lewat Reflection API, metadata dari annotation bisa diakses saat runtime. Jadi annotation bisa dibilang seperti bahasa konfigurasi yang tertanam langsung di dalam kode.

Dengan annotation, implementasi fungsi dan penggunaannya dalam aplikasi bisa dipisahkan. Kurang lebih mirip kayak interface dan implementasinya. Tapi interface dan implementasi masih terkait kode, sedangkan annotation lebih ke deklarasi informasi tambahan dan konfigurasi. Interface bisa diimplementasikan lewat class, sementara annotation bisa dipasang di method, function, parameter, property, dan class constant. Makanya annotation lebih fleksibel daripada interface.

Contoh sederhana: mengimplementasikan method opsional dari sebuah interface pakai annotation. Misalnya interface `ActionHandler` mewakili operasi aplikasi, beberapa implementasi `action handler` butuh `setup`, beberapa nggak. Daripada maksa semua class untuk implementasiin interface `ActionHandler` dan method `setUp()`, kita bisa pake annotation. Keuntungannya, Anda bisa pake annotation berkali-kali.

### Bagaimana Cara Kerja Annotation?

Seperti yang sudah disinggung, annotation hanyalah definisi metadata dan perlu dipadukan dengan aplikasi agar berfungsi. Di Hyperf, data annotation bakal dikumpulkan ke kelas `Hyperf\Di\Annotation\AnnotationCollector` buat dipake aplikasi. Tentu aja, Anda juga bisa ngumpulinnya ke kelas kustom sendiri. Nantinya, metadata annotation yang udah dikumpulin bakal dibaca dan dipake di tempat yang sesuai, sesuai fungsi yang diinginkan.

### Mengabaikan Annotation Tertentu

Kadang kita perlu ngabaikan annotation tertentu. Misalnya, pas pake tools yang otomatis generate dokumentasi, banyak tool kayak gitu define struktur dokumentasi lewat annotation, dan annotation tersebut mungkin gak cocok sama cara kerja Hyperf. Kita bisa atur annotation yang mau diabaikan di `config/autoload/annotations.php`.

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // Annotation dalam array ignore_annotations akan diabaikan oleh annotation scanner
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## Menggunakan Annotation

Ada 3 tipe objek aplikasi untuk annotation: `class`, `class method`, dan `class property`.

### Menggunakan Class Annotation

Class annotation didefinisikan di blok komentar di atas keyword `class`. Contohnya `Controller` dan `AutoController` yang sering dipake, itu contoh klasik class annotation. Kode di bawah ini nunjukin cara yang benar, yaitu annotation `ClassAnnotation` dipasang di kelas `Foo`.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Menggunakan Class Method Annotation

Class method annotation didefinisikan di blok komentar di atas method. Contohnya `RequestMapping`, itu contoh klasik class method annotation. Kode di bawah ini nunjukin cara yang benar, yaitu annotation `MethodAnnotation` dipasang di method `Foo::bar()`.

```php
<?php
class Foo
{
    #[MethodAnnotation]
    public function bar()
    {
        // beberapa kode
    }
}
```

### Menggunakan Class Property Annotation

Class property annotation didefinisikan di blok komentar di atas property. Contohnya `Value` dan `Inject`, itu contoh klasik class property annotation. Kode di bawah ini nunjukin cara yang benar, yaitu annotation `PropertyAnnotation` dipasang di property `$bar` dari kelas `Foo`.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### Cara Memberikan Parameter Annotation

- Melewatkan parameter utama tunggal `#[DemoAnnotation('value')]`
- Melewatkan parameter string `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Melewatkan parameter array `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Custom Annotation

### Membuat Kelas Annotation

```php
<?php
namespace App\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Foo extends AbstractAnnotation
{
    public function __construct(public array $bar, public int $baz = 0)
    {
    }
}
```

Menggunakan kelas annotation:

```php
<?php
use App\Annotation\Foo;

#[Foo(bar: [1, 2], baz: 3)]
class IndexController extends AbstractController
{
    // Menggunakan data annotation
}
```

Perhatikan: di contoh di atas, kelas annotation mewarisi `AbstractAnnotation`. Ini sebenarnya tidak wajib, tapi untuk kelas annotation Hyperf, wajib mengimplementasikan interface `AnnotationInterface`. Abstract class ini cuma nyediain cara definisi yang minimalis, udah ada fitur praktis kayak `distribusi otomatis parameter annotation ke properti kelas` dan `pengumpulan otomatis ke AnnotationCollector sesuai aturan posisi pemakaian annotation`.

### Custom Annotation Collector

Proses pengumpulan annotation sebenarnya diimplementasikan di dalam kelas annotation itu sendiri. Method terkait diatur oleh `Hyperf\Di\Annotation\AnnotationInterface`. Interface ini mewajibkan 3 method berikut, dan Anda bisa ngimplementasiin logika sesuai kebutuhan:

- `public function collectClass(string $className): void;` Method ini dipicu ketika annotation yang didefinisikan pada class dipindai
- `public function collectMethod(string $className, ?string $target): void;` Method ini dipicu ketika annotation yang didefinisikan pada class method dipindai
- `public function collectProperty(string $className, ?string $target): void` Method ini dipicu ketika annotation yang didefinisikan pada class property dipindai

Karena framework udah punya cache buat annotation collector, Anda perlu konfigurasi custom collector di `annotations.scan.collectors` biar framework otomatis nyimpen cache annotation yang udah dikumpulin dan pake lagi pas startup berikutnya.
Kalau collectornya gak dikonfigurasi, custom annotation cuma bakal jalan pas `server` pertama kali dijalankan, tapi gagal di startup berikutnya.

```php
<?php

return [
    // Perhatikan bahwa tidak ada level annotations dalam file konfigurasi di bawah folder config/autoload
    'annotations' => [
        'scan' => [
            'collectors' => [
                CustomCollector::class,
            ],
        ],
    ],
];
```

### Memanfaatkan Data Annotation

Kalau Anda gak define metode pengumpulan annotation sendiri, metadata annotation bakal dikumpulin ke `Hyperf\Di\Annotation\AnnotationCollector` secara default. Lewat method statis dari kelas ini, Anda bisa dapetin metadata yang dibutuhin buat logika atau implementasi.

### Fitur ClassMap

Framework menyediakan konfigurasi `class_map`, yang dapat memudahkan pengguna untuk langsung mengganti kelas yang perlu dimuat.

Sebagai contoh, kita mengimplementasikan fungsi yang dapat secara otomatis menyalin coroutine context:

Pertama, kita implementasikan kelas `Coroutine` untuk menyalin context. Method `create()` dapat menyalin context dari parent class ke subclass.

Untuk menghindari konflik penamaan, disepakati untuk menggunakan `class_map` sebagai nama folder, diikuti oleh folder dan file dari namespace yang akan diganti.

Seperti: `class_map/Hyperf/Coroutine/Coroutine.php`

[Coroutine.php](https://github.com/hyperf/biz-skeleton/blob/master/app/Kernel/Context/Coroutine.php)

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Kernel\Context;

use App\Kernel\Log\AppendRequestIdProcessor;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Coroutine
{
    protected LoggerInterface $logger;

    public function __construct(protected ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    /**
     * @return int Mengembalikan ID coroutine dari coroutine yang baru dibuat.
     *             Mengembalikan -1 ketika pembuatan coroutine gagal.
     */
    public function create(callable $callable): int
    {
        $id = Co::id();
        $coroutine = Co::create(function () use ($callable, $id) {
            try {
                // Tidak boleh menyalin semua context untuk menghindari socket yang sudah terikat ke coroutine lain.
                Context::copy($id, [
                    AppendRequestIdProcessor::REQUEST_ID,
                    ServerRequestInterface::class,
                ]);
                $callable();
            } catch (Throwable $throwable) {
                $this->logger->warning((string) $throwable);
            }
        });

        try {
            return $coroutine->getId();
        } catch (Throwable $throwable) {
            $this->logger->warning((string) $throwable);
            return -1;
        }
    }
}
```

Kemudian, kita implementasikan objek yang persis sama dengan `Hyperf\Coroutine\Coroutine`. Method `create()` diganti dengan method yang kita implementasikan di atas.

[Coroutine.php](https://github.com/hyperf/biz-skeleton/blob/master/app/Kernel/ClassMap/Coroutine.php)

`class_map/Hyperf/Coroutine/Coroutine.php`

```php
<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Coroutine;

use App\Kernel\Context\Coroutine as Go;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Engine\Exception\RunningInNonCoroutineException;
use Throwable;

class Coroutine
{
    /**
     * Mengembalikan ID coroutine saat ini.
     * Mengembalikan -1 ketika berjalan di konteks non-coroutine.
     */
    public static function id(): int
    {
        return Co::id();
    }

    public static function defer(callable $callable): void
    {
        Co::defer(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $exception) {
                di()->get(StdoutLoggerInterface::class)->error((string) $exception);
            }
        });
    }

    public static function sleep(float $seconds): void
    {
        usleep(intval($seconds * 1000 * 1000));
    }

    /**
     * Mengembalikan ID coroutine parent.
     * Mengembalikan 0 ketika berjalan di tingkat coroutine paling atas.
     * @throws RunningInNonCoroutineException ketika berjalan di konteks non-coroutine
     * @throws CoroutineDestroyedException ketika coroutine telah dihancurkan
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    /**
     * @return int Mengembalikan ID coroutine dari coroutine yang baru dibuat.
     *             Mengembalikan -1 ketika pembuatan coroutine gagal.
     */
    public static function create(callable $callable): int
    {
        return di()->get(Go::class)->create($callable);
    }

    public static function inCoroutine(): bool
    {
        return Co::id() > 0;
    }

    public static function stats(): array
    {
        return Co::stats();
    }

    public static function exists(int $id): bool
    {
        return Co::exists($id);
    }
}
```

Kemudian konfigurasi `class_map`, sebagai berikut:

```php
<?php

declare(strict_types=1);

use Hyperf\Coroutine\Coroutine;

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/app',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
        'class_map' => [
            // Nama kelas yang akan dipetakan => Path file tempat kelas berada
            Coroutine::class => BASE_PATH . '/class_map/Hyperf/Coroutine/Coroutine.php',
        ],
    ],
];
```

Dengan begitu, method seperti `co()` dan `parallel()` bisa otomatis dapetin parent coroutine beserta data di context, misalnya `Request`.
