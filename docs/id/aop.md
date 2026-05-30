# AOP (Aspect Oriented Programming)

## Konsep

AOP adalah singkatan dari `Aspect Oriented Programming`, sebuah teknik untuk
mencapai pemeliharaan terpadu dari fungsi program melalui teknik-teknik seperti
dynamic proxy. AOP merupakan kelanjutan dari OOP dan bagian penting dari Hyperf.
Ini adalah paradigma turunan dari pemrograman fungsional. AOP dapat digunakan
untuk mengisolasi berbagai bagian dari business logic, yang mengurangi tingkat
coupling di antara bagian-bagian tersebut, meningkatkan reusabilitas program,
serta meningkatkan efisiensi pengembangan.

Secara sederhana, di Hyperf Anda dapat menggunakan `Aspect` untuk masuk ke alur
eksekusi method mana pun dari class mana pun, lalu mengubah atau memperkuat
fungsi method asli. Inilah yang disebut AOP.

> Perlu diperhatikan bahwa "class mana pun" di sini bukan berarti semua class
> secara mutlak. Class yang digunakan untuk mengimplementasikan fitur AOP pada
> tahap awal startup Hyperf tidak dapat di-aspect.

## Pendahuluan

Dibandingkan dengan fitur AOP yang diimplementasikan oleh framework lain, kami
telah menyederhanakan penggunaan fungsi ini tanpa pembagian yang rumit, hanya
ada bentuk universal "Around":

- `Aspect` adalah kelas definisi yang ditenun (weaved) ke dalam alur kode,
  termasuk definisi target yang akan dilibatkan, dan modifikasi metode asli dari
  target tersebut.
- `ProxyClass`, Setiap kelas target yang dilibatkan pada akhirnya akan
  menghasilkan proxy class untuk mencapai tujuan menjalankan metode `Aspect`,
  bukan meneruskan kelas asli.

## Mendefinisikan Aspect

Setiap `Aspect` harus mengimplementasikan `Hyperf\Di\Aop\AroundInterface`, dan
menyediakan properti `$classes` dan `$annotations` dengan visibilitas `public`.
Untuk kemudahan penggunaan, kita dapat menyederhanakan penggunaan dengan
mewarisi `Hyperf\Di\Aop\AbstractAspect` pada kelas aspect kita.

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
    // The class to be cut in can be multiple, or can be identified by `::` to the specific method, or use * for fuzzy matching
    public array $classes = [
        SomeClass::class,
        'App\Service\SomeClass::someMethod',
        'App\Service\SomeClass::*Method',
    ];
    
    // The annotations to be cut into, means the classes that use these annotations to be cut into, can only cut into class annotations and class method annotations.
    public array $annotations = [
        SomeAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        // After the Aspect is cut into, the corresponding method will be responsible by this method.
        // $proceedingJoinPoint is the joining point, the original method is called by the process() method of the class and obtain the result.
        // Do something before original method
        $result = $proceedingJoinPoint->process();
        // Do something after original method
        return $result;
    }
}
```

Setiap `Aspect` harus mendefinisikan annotation `#[Aspect]` atau dikonfigurasi
di dalam `config/autoload/aspects.php` agar aktif.

> Untuk menggunakan annotation `#[Aspect]`, Anda harus mengimpor namespace
> `use Hyperf\Di\Annotation\Aspect;`.

Anda juga dapat mengonfigurasi target melalui property annotation `#[Aspect]`
itu sendiri. Bentuk annotation berikut memiliki tujuan yang sama dengan contoh
di atas:

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
        // Setelah aspect masuk, method ini bertanggung jawab menjalankan method terkait.
        // $proceedingJoinPoint adalah join point, method process() akan memanggil method asli dan mendapatkan hasilnya.
        // Lakukan beberapa pemrosesan sebelum pemanggilan
        $result = $proceedingJoinPoint->process();
        // Lakukan beberapa pemrosesan setelah pemanggilan
        return $result;
    }
}
```

## Mengubah atau Memperkuat Method Asli

Selain itu, Anda juga dapat merealisasikan kebutuhan bisnis (business requirement) Anda dengan cara mendapatkan instance asli, melakukan refleksi pada method, mengirimkan argumen, mendapatkan annotation, dll.:

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
        // Mendapatkan prototipe refleksi dari method saat ini
        /** @var \ReflectionMethod **/
        $reflect = $proceedingJoinPoint->getReflectMethod();

        // Mendapatkan parameter/argumen yang disubmit saat method dipanggil
        $arguments = $proceedingJoinPoint->getArguments(); // array

        // Mendapatkan instance class asli dan memanggil method lain pada class asli
        $originalInstance = $proceedingJoinPoint->getInstance();
        $originalInstance->yourFunction();

        // Mendapatkan metadata annotation
        /** @var \Hyperf\Di\Aop\AnnotationMetadata **/
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();

        // Memanggil method asli yang tidak terpengaruh oleh class proxy
        $proceedingJoinPoint->processOriginalMethod();

        // Tidak mengeksekusi method asli, dan melakukan operasi lain
        $result = date('YmdHis', time() - 86400);
        return $result;
    }
}
```

> Catatan: Class yang diperoleh melalui `getInstance` adalah proxy class, method di dalamnya masih akan terpengaruh oleh aspect lain. Pemanggilan bersarang yang saling silang akan menyebabkan infinite loop dan menghabiskan memori.

## Cache dari Proxy Class

Semua class yang dipengaruhi oleh AOP akan menghasilkan `proxy class cache`
yang sesuai di folder `./runtime/container/proxy/`. Apakah cache ini dibuat
otomatis saat startup bergantung pada nilai konfigurasi `scan_cacheable` di file
`config/config.php`. Nilai default-nya adalah `false`. Jika bernilai `true`,
Hyperf tidak akan melakukan scan dan membuat proxy class cache, melainkan
langsung menggunakan file cache yang sudah ada sebagai proxy class final. Jika
bernilai `false`, Hyperf akan melakukan scan pada annotation scan domain setiap
kali aplikasi dimulai dan otomatis membuat proxy class cache yang sesuai. Saat
kode berubah, proxy class cache juga akan otomatis dibuat ulang.

Biasanya nilai ini adalah `false` di lingkungan development agar debugging lebih
mudah. Saat deployment ke production, kita mungkin ingin Hyperf membuat semua
proxy class lebih awal, bukan membuatnya secara dinamis saat digunakan. Anda
dapat menggunakan perintah `php bin/hyperf.php` untuk membuat semua proxy class,
lalu mengubah nilai konfigurasi melalui environment variable `SCAN_CACHEABLE`
menjadi `true`, sehingga startup lebih cepat dan penggunaan memori aplikasi
lebih rendah.

Berdasarkan hal di atas, jika Anda menggunakan Docker, Kubernetes, atau teknologi
virtualisasi lain untuk deployment, Anda dapat membuat proxy class cache pada
tahap build image dan memasukkannya ke dalam image. Saat instance image berjalan,
waktu startup dan penggunaan memori aplikasi dapat berkurang secara signifikan.
