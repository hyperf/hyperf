# AOP Aspect-Oriented Programming

## Konsep

AOP adalah singkatan dari `Aspect Oriented Programming`, teknik yang memakai dynamic proxy untuk memelihara fungsi program secara terpadu. AOP adalah kelanjutan dari OOP, bagian penting dari Hyperf, dan turunan dari functional programming. Dengan AOP, berbagai bagian logika bisnis bisa diisolasi, sehingga ngurangin kopling, ningkatin reusability, dan bikin proses development lebih efisien.

Intinya, di Hyperf, Anda bisa nyusup ke alur eksekusi method mana pun lewat `Aspect` buat ngubah atau ngelengkapin fungsi aslinya. Itulah AOP.

> Perlu dicatat, "kelas mana pun" di sini tidak berarti semua kelas secara mutlak. Kelas yang dipake buat implementasi AOP di tahap awal startup Hyperf gak bisa dipotong.

## Pendahuluan

Dibanding framework lain yang nerapin AOP, Hyperf nyederhanain tanpa bikin pembagian yang terlalu rumit, cuma ada bentuk umum `Around`:

- `Aspect` adalah kelas definisi buat flow weaving, nyakup target yang mau diintervensi, plus implementasi modifikasi dan penguatan method asli.
- `ProxyClass`, buat setiap kelas target yang diintervensi, bakal digenerate proxy class yang ngejalanin method `Aspect`.

## Mendefinisikan Aspect

Setiap `Aspect` wajib mengimplementasikan interface `Hyperf\Di\Aop\AroundInterface` dan punya properti `public` `$classes` dan `$annotations`. Biar gampang, kita bisa nyederhanain proses definisi dengan mewarisi `Hyperf\Di\Aop\AbstractAspect`. Langsung aja liat kode:

```php
<?php
namespace App\Aspect;

use App\Service\SomeClass;
use App\Annotation\SomeAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class FooAspect extends AbstractAspect
{
    // Kelas atau Trait yang akan dipotong, bisa lebih dari satu, atau dapat diidentifikasi ke method tertentu melalui ::, dan dapat dicocokkan secara fuzzy melalui *
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];

    // Annotation yang akan dipotong. Yang sebenarnya dipotong adalah kelas yang menggunakan annotation ini. Hanya class annotation dan class method annotation yang dapat dipotong
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Setelah aspect dipotong, eksekusi method yang sesuai akan ditangani oleh ini
        // $proceedingJoinPoint adalah connection point, panggil method asli melalui method process() dari kelas ini dan dapatkan hasilnya
        // Lakukan beberapa pemrosesan sebelum pemanggilan
        $result = $proceedingJoinPoint->process();
        // Lakukan beberapa pemrosesan setelah pemanggilan
        return $result;
    }
}
```

Setiap `Aspect` wajib pake annotation `#[Aspect]` atau dikonfigurasi di `config/autoload/aspects.php` biar aktif.

> Saat menggunakan annotation `#[Aspect]`, Anda perlu `use Hyperf\Di\Annotation\Aspect;` namespace;

Anda juga bisa ngatur target yang mau dipotong lewat atribut annotation `#[Aspect]` itu sendiri. Hasilnya sama kayak contoh di atas:

```php
<?php
namespace App\Aspect;

use App\Service\SomeClass;
use App\Annotation\SomeAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[
    Aspect(
        classes: [
            SomeClass::class,
            "App\Service\SomeClass::someMethod",
            "App\Service\SomeClass::*Method"
        ],
        annotations: [
            SomeAnnotation::class
        ]
    )
]
class FooAspect extends AbstractAspect
{
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Setelah aspect dipotong, eksekusi method yang sesuai akan ditangani oleh ini
        // $proceedingJoinPoint adalah connection point, panggil method asli melalui method process() dari kelas ini dan dapatkan hasilnya
        // Lakukan beberapa pemrosesan sebelum pemanggilan
        $result = $proceedingJoinPoint->process();
        // Lakukan beberapa pemrosesan setelah pemanggilan
        return $result;
    }
}
```

## Mengubah atau Memperkuat Method Asli

Selain itu, Anda bisa implementasiin kebutuhan bisnis lewat instance asli, refleksi method, kirim parameter, dapetin annotation, dll:

```php
<?php
namespace App\Aspect;

use App\Service\SomeClass;
use App\Annotation\SomeAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class FooAspect extends AbstractAspect
{
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];

    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // Mendapatkan prototipe refleksi method saat ini
        /** @var \ReflectionMethod **/
        $reflect = $proceedingJoinPoint->getReflectMethod();

        // Mendapatkan parameter yang dikirimkan saat memanggil method
        $arguments = $proceedingJoinPoint->getArguments(); // array

        // Mendapatkan instance dari kelas asli dan memanggil method lain dari kelas asli
        $originalInstance = $proceedingJoinPoint->getInstance();
        $originalInstance->yourFunction();

        // Mendapatkan metadata annotation
        /** @var \Hyperf\Di\Aop\AnnotationMetadata **/
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // Memanggil method asli yang tidak terpengaruh oleh proxy class
        $proceedingJoinPoint->processOriginalMethod();

        // Tidak menjalankan method asli, melakukan operasi lain
        $result = date('YmdHis', time() - 86400);
        return $result;
    }
}
```

> Catatan: Kelas yang didapetin dari `getInstance` adalah proxy class, dan method di dalamnya masih bisa kena pengaruh aspect lain. Panggilan bersarang bisa bikin infinite loop dan habisin memori.

## Proxy Class Cache

Semua kelas yang kena AOP bakal menghasilkan `Proxy class cache` di folder `./runtime/container/proxy/`. Apakah cache ini digenerate otomatis pas startup tergantung nilai `scan_cacheable` di `config/config.php`. Defaultnya `false`. Kalau `true`, Hyperf gak bakal scan dan generate cache, langsung pake file cache yang udah ada sebagai proxy class final. Kalau `false`, Hyperf bakal scan annotation scan domain tiap aplikasi jalan dan generate cache proxy class. Pas kode berubah, cache juga otomatis digenerate ulang.

Di lingkungan development, biasanya nilai ini `false` biar lebih gampang proses development dan debugging. Pas deploy ke production, kita pengen Hyperf generate semua proxy class di awal, bukan pas dipake. Anda bisa generate semua proxy class lewat `php bin/hyperf.php`, lalu setel environment variable `SCAN_CACHEABLE` ke `true`, tujuannya biar startup lebih cepet dan pemakaian memori lebih rendah.

Makanya, kalau Anda pake teknologi virtualisasi kayak Docker atau Kubernetes buat deploy, Anda bisa generate cache proxy class pas fase build image dan simpen ke dalam image. Pas instance image jalan, ini bakal ngurangin startup time dan pemakaian memori secara signifikan.
