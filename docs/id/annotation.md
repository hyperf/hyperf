# Annotation

Annotation adalah fitur yang sangat kuat di Hyperf yang dapat digunakan untuk
mengurangi banyak konfigurasi dalam bentuk annotation dan untuk
mengimplementasikan berbagai fitur yang sangat memudahkan.

## Konsep

### Apa itu annotation?

Annotation menawarkan kemampuan untuk menambahkan informasi metadata terstruktur yang
dapat dibaca mesin pada deklarasi di dalam kode: class, method, function,
parameter, property, dan class constant dapat menjadi target dari sebuah
annotation. Metadata yang didefinisikan oleh annotation kemudian dapat diperiksa
saat runtime menggunakan Reflection API. Oleh karena itu, annotation dapat
dianggap sebagai bahasa konfigurasi yang disematkan langsung ke dalam kode.

Dengan annotation, implementasi generik dari suatu fitur dan penggunaan
konkretnya dalam aplikasi dapat dipisahkan (decoupled). Dalam beberapa hal, ini
dapat dibandingkan dengan interface dan implementasinya. Namun, jika interface
dan implementasi berkaitan dengan kode, annotation berkaitan dengan memberikan
informasi tambahan (annotating) dan konfigurasi. Interface dapat diimplementasi
oleh class, sedangkan annotation juga dapat dideklarasikan pada method, function,
parameter, property, dan class constant. Dengan demikian, annotation lebih
fleksibel daripada interface.

Contoh sederhana dari penggunaan annotation adalah mengubah interface yang memiliki
method opsional untuk menggunakan annotation. Mari kita asumsikan sebuah interface
ActionHandler yang merepresentasikan suatu operasi dalam aplikasi, di mana
beberapa implementasi dari action handler memerlukan setup dan yang lainnya
tidak. Alih-alih mengharuskan semua class yang mengimplementasikan ActionHandler
untuk mengimplementasikan method setUp(), sebuah annotation dapat digunakan. Salah
satu keuntungan dari pendekatan ini adalah kita dapat menggunakan annotation
tersebut beberapa kali.

### Bagaimana annotation bekerja?

Seperti yang telah dikatakan bahwa annotation hanyalah definisi metadata yang
harus bekerja dengan aplikasi agar dapat berfungsi. Di Hyperf, data dalam
annotation dikumpulkan ke dalam class
`Hyperf\Di\Annotation\AnnotationCollector` untuk digunakan oleh aplikasi.
Bergantung pada kebutuhan Anda, Anda juga dapat mengumpulkan data tersebut ke
class kustom Anda sendiri, kemudian membaca dan memanfaatkan metadata annotation
yang terkumpul di tempat di mana annotation itu sendiri diharapkan bekerja untuk
mencapai implementasi fungsional yang diinginkan.

### Mengabaikan annotation tertentu

Dalam beberapa kasus, kita mungkin ingin mengabaikan annotation tertentu.
Sebagai contoh, ketika kita mengakses beberapa tool yang menghasilkan dokumen
secara otomatis, banyak tool menggunakan annotation untuk mendefinisikan konten
struktural yang relevan dari dokumen tersebut. Annotation ini mungkin tidak
sejalan dengan bagaimana Hyperf digunakan, sehingga kita dapat mengatur agar
annotation tersebut diabaikan melalui `config/autoload/annotations.php`.

```php
use JetBrains\PhpStorm\ArrayShape;

return [
    'scan' => [
        // Annotation di array ignore_annotations akan diabaikan oleh annotation scanner
        'ignore_annotations' => [
            ArrayShape::class,
        ],
    ],
];
```

## Menggunakan Annotation

Ada tiga tipe penerapan dari annotation, yaitu pada `class`, `method of class`,
dan `property of class`.

### Menggunakan class annotation

Definisi annotation tingkat class berada di blok komentar di atas keyword
`class`. Sebagai contoh, `Controller` dan `AutoController` yang umum digunakan
adalah contoh penggunaan dari class level annotation. Contoh kode berikut adalah
contoh penggunaan class level annotation yang benar, yang menunjukkan bahwa
annotation `ClassAnnotation` diterapkan pada class `Foo`.

```php
<?php
#[ClassAnnotation]
class Foo {}
```

### Menggunakan method annotation

Definisi annotation tingkat method berada di blok komentar di atas method class.
Sebagai contoh, `RequestMapping` yang umum digunakan adalah contoh penggunaan dari
method level annotation. Contoh kode berikut adalah contoh penggunaan method
level annotation yang benar, yang menunjukkan bahwa annotation `MethodAnnotation`
diterapkan pada method `bar` dari class `Foo`.

```php
<?php
class Foo
{
    #[MethodAnnotation]
    public function bar()
    {
        // some code
    }
}
```

### Menggunakan property annotation

Definisi annotation tingkat property berada di blok komentar di atas property.
Sebagai contoh, `Value` dan `Inject` yang sering digunakan adalah contoh
penggunaan dari property level annotation. Contoh kode berikut adalah contoh
penggunaan property level annotation yang benar, yang menunjukkan bahwa
annotation `PropertyAnnotation` diterapkan pada property `$bar` dari class
`Foo`.

```php
<?php
class Foo
{
    #[PropertyAnnotation]
    private $bar;
}
```

### Pengiriman parameter annotation

- Melewatkan parameter tunggal utama: `#[DemoAnnotation('value')]`
- Melewatkan parameter string: `#[DemoAnnotation(key1: 'value1', key2: 'value2')]`
- Melewatkan parameter array: `#[DemoAnnotation(key: ['value1', 'value2'])]`

## Custom Annotation

### Membuat class Annotation

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

Menggunakan class annotation:

```php
<?php
use App\Annotation\Foo;

#[Foo(bar: [1, 2], baz: 3)]
class IndexController extends AbstractController
{
    // Memanfaatkan data annotation
}
```

Perlu dicatat bahwa pada contoh kode di atas, class annotation mewarisi
abstract class `Hyperf\Di\Annotation\AbstractAnnotation`. Ini tidak wajib untuk
class annotation, tetapi untuk class annotation Hyperf, wajib untuk mengimplementasi
interface `Hyperf\Di\Annotation\AnnotationInterface`, sehingga peran dari abstract
class di sini adalah untuk menyediakan definisi minimal. Abstract class tersebut
telah diimplementasikan untuk Anda agar dapat `secara otomatis menetapkan
parameter annotation ke property class`, dan `secara otomatis mengumpulkan data
annotation ke AnnotationCollector sesuai aturan berdasarkan lokasi penggunaan
annotation`.

### Custom Annotation Collector

Alur eksekusi spesifik dari pengumpulan annotation juga diimplementasikan di
dalam class annotation. Method terkait dibatasi oleh
`Hyperf\Di\Annotation\AnnotationInterface`. Interface tersebut memerlukan
implementasi dari tiga method berikut, dan Anda dapat mengimplementasikan logika
yang sesuai berdasarkan kebutuhan Anda sendiri:

- `public function collectClass(string $className): void;` Method ini akan
  dijalankan ketika annotation didefinisikan pada class.
- `public function collectMethod(string $className, ?string $target): void;`
  Method ini akan dijalankan ketika annotation didefinisikan pada method.
- `public function collectProperty(string $className, ?string $target): void`
  Method ini akan dijalankan ketika annotation didefinisikan pada property.

Karena framework menyediakan fitur cache untuk annotation collector, Anda perlu
mengonfigurasi custom collector ke `annotations.scan.collectors`. Dengan begitu,
framework dapat otomatis melakukan cache terhadap annotation yang sudah
dikumpulkan dan menggunakannya kembali pada startup berikutnya. Jika collector
terkait tidak dikonfigurasi, custom annotation hanya akan aktif saat `server`
pertama kali dijalankan, dan tidak akan aktif pada startup berikutnya.

```php
<?php

return [
    // Perhatikan bahwa pada file konfigurasi di config/autoload tidak ada layer annotations ini
    'annotations' => [
        'scan' => [
            'collectors' => [
                CustomCollector::class,
            ],
        ],
    ],
];

```

### Memanfaatkan data annotation

Ketika tidak ada method pengumpulan annotation kustom, metadata annotation akan
dikumpulkan di class `Hyperf\Di\Annotation\AnnotationCollector` secara default.
Static method dari class tersebut dapat dengan mudah memperoleh metadata yang
sesuai untuk penilaian logika atau implementasi.

### Fitur ClassMap

Framework ini menyediakan konfigurasi `class_map`, yang memungkinkan pengguna untuk dengan mudah mengganti class yang perlu dimuat.

Misalnya, kita mengimplementasikan fitur yang secara otomatis dapat menyalin *coroutine context*:

Pertama, kita mengimplementasikan class `Coroutine` yang digunakan untuk menyalin context. Method `create()` di dalamnya, dapat menyalin context dari parent ke child.

Untuk menghindari konflik penamaan, disepakati untuk menggunakan `class_map` sebagai nama folder, diikuti dengan folder namespace dan file yang akan diganti.

Contoh: `class_map/Hyperf/Coroutine/Coroutine.php`

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
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public function create(callable $callable): int
    {
        $id = Co::id();
        $coroutine = Co::create(function () use ($callable, $id) {
            try {
                // Shouldn't copy all contexts to avoid socket already been bound to another coroutine.
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

Kemudian, kita mengimplementasikan objek yang persis sama dengan `Hyperf\Coroutine\Coroutine`. Di mana method `create()` diganti dengan method yang telah kita implementasikan di atas.

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
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
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
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @throws RunningInNonCoroutineException when running in non-coroutine context
     * @throws CoroutineDestroyedException when the coroutine has been destroyed
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return Co::pid($coroutineId);
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
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

Lalu konfigurasikan `class_map` sebagai berikut:

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
            // Nama class yang akan dipetakan => path file tempat class berada
            Coroutine::class => BASE_PATH . '/class_map/Hyperf/Coroutine/Coroutine.php',
        ],
    ],
];

```

Dengan demikian, method seperti `co()` dan `parallel()` secara otomatis bisa mendapatkan data di context dari parent coroutine, misalnya `Request`.
