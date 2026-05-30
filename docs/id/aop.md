# AOP (Aspect Oriented Programming)

## Konsep

AOP adalah singkatan dari `Aspect Oriented Programming`, sebuah teknik untuk
mencapai pemeliharaan terpadu dari fungsi program melalui teknik-teknik seperti
dynamic proxy. AOP merupakan kelanjutan dari OOP dan bagian penting dari Hyperf.
Ini adalah paradigma turunan dari pemrograman fungsional. AOP dapat digunakan
untuk mengisolasi berbagai bagian dari business logic, yang mengurangi tingkat
coupling di antara bagian-bagian tersebut, meningkatkan reusabilitas program,
serta meningkatkan efisiensi pengembangan.

Secara populer, dalam Hyperf Anda dapat mengintervensi eksekusi metode apa pun
dari kelas mana pun yang dikelola oleh [hyperf/di](https://github.com/hyperf/di)
melalui `Aspect`. Mengintervensi proses untuk mengubah atau meningkatkan fungsi
dari metode asli, inilah yang disebut AOP.

> Untuk menggunakan AOP, Anda harus menggunakan
> [hyperf/di](https://github.com/hyperf/di) sebagai dependency injection
> container.

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

Semua kelas yang dipengaruhi oleh AOP akan menghasilkan `proxy class cache`
yang sesuai di dalam folder `./runtime/container/proxy/`. Saat server berjalan,
jika proxy class cache yang sesuai dengan kelas tersebut sudah ada, cache
tersebut tidak akan dibuat ulang dan langsung digunakan, bahkan jika `Aspect`
atau `Business Class` telah berubah. Ketika cache tidak ada, proxy class cache
yang baru akan dibuat ulang secara otomatis.

Saat melakukan deployment ke lingkungan produksi (production environment), kita
mungkin ingin Hyperf membuat semua proxy class terlebih dahulu, daripada
membuatnya secara dinamis saat runtime. Semua proxy class dapat dibuat
menggunakan perintah `php bin/hyperf.php di:init-proxy`. Perintah ini mengabaikan
proxy class cache yang sudah ada dan membuat ulang semuanya.

Berdasarkan hal di atas, kita dapat menggabungkan perintah untuk membuat proxy
class dengan perintah untuk menjalankan server, yaitu
`php bin/hyperf.php di:init-proxy && php bin/hyperf.php start`. Perintah ini
akan secara otomatis membuat ulang semua proxy class cache lalu menjalankan
server.
