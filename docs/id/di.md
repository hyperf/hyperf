# Dependency Injection

## Pendahuluan

Secara default, Hyperf menggunakan [hyperf/di](https://github.com/hyperf/di) sebagai container untuk mengelola dependency injection. Meskipun secara desain Anda bisa menggantinya dengan container DI lain, kami sangat menyarankan untuk tetap menggunakan komponen ini.

[hyperf/di](https://github.com/hyperf/di) adalah komponen powerful untuk mengelola dependency antar class dan melakukan injeksi secara otomatis. Bedanya dengan container DI tradisional, komponen ini dirancang khusus untuk aplikasi dengan lifecycle panjang, mendukung [Annotation dan Annotation Injection](id/annotation.md), serta dilengkapi kemampuan [AOP Aspect-Oriented Programming](id/aop.md) yang luar biasa. Sebagai inti dari Hyperf, kami yakin komponen ini adalah yang terbaik.

## Instalasi

Komponen ini sudah tersedia secara default di proyek [hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) sebagai komponen utama. Jika Anda ingin menggunakannya di framework lain, cukup jalankan perintah berikut:

```bash
composer require hyperf/di
```

## Mengikat Hubungan Objek

### Injeksi Objek Sederhana

Secara umum, hubungan dan injeksi kelas tidak perlu didefinisikan secara eksplisit. Semua ini akan dilakukan secara diam-diam untuk Anda oleh Hyperf. Kami menggunakan beberapa contoh kode untuk mengilustrasikan penggunaan terkait.
Misalkan kita perlu memanggil method `getInfoById(int $id)` dari kelas `UserService` di `IndexController`.

```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // Kita asumsikan sebuah entitas Info ada
        return (new Info())->fill($id);    
    }
}
```

#### Injeksi melalui Constructor Method

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private UserService $userService;
    
    // Menyelesaikan injeksi otomatis dengan mendeklarasikan tipe parameter di parameter constructor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        // Langsung gunakan
        return $this->userService->getInfoById($id);    
    }
}
```

> Perhatikan bahwa ketika menggunakan constructor injection, pemanggil, `IndexController`, harus berupa objek yang dibuat oleh DI untuk menyelesaikan injeksi otomatis, dan Controller dibuat oleh DI secara default, jadi constructor injection dapat digunakan langsung.

Ketika Anda ingin mendefinisikan dependensi opsional, Anda dapat mendefinisikan parameter sebagai `nullable` atau mendefinisikan nilai default parameter sebagai `null`. Ini berarti bahwa jika objek yang sesuai tidak ditemukan di DI container atau tidak dapat dibuat, tidak ada exception yang akan dilempar, tetapi `null` akan digunakan untuk injeksi.

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private ?UserService $userService;
    
    // Menunjukkan bahwa parameter ini adalah parameter opsional dengan mengaturnya menjadi nullable
    public function __construct(?UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService tersedia hanya ketika nilainya ada
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

#### Injeksi melalui Annotation `#[Inject]`

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{

    #[Inject]
    private UserService $userService;
    
    public function index()
    {
        $id = 1;
        // Langsung gunakan
        return $this->userService->getInfoById($id);    
    }
}
```

> Injeksi melalui annotation `#[Inject]` dapat berlaku untuk objek yang dibuat oleh DI (singleton), atau objek yang dibuat melalui keyword `new`;
>
> Saat menggunakan annotation `#[Inject]`, Anda perlu `use Hyperf\Di\Annotation\Inject;` namespace;

##### Required Parameter

Annotation `#[Inject]` memiliki parameter `required`, dan nilai defaultnya adalah `true`. Ketika parameter ini didefinisikan sebagai `false`, ini menunjukkan bahwa member property ini adalah dependensi opsional. Ketika objek yang sesuai dengan `@var` tidak ada di DI container atau tidak dapat dibuat, tidak ada exception yang akan dilempar, tetapi `null` akan diinjeksikan, sebagai berikut:

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * Menginjeksikan tipe objek dari properti yang dideklarasikan oleh annotation melalui annotation `#[Inject]`
     * Ketika UserService tidak ada di DI container atau tidak dapat dibuat, null akan diinjeksikan
     */
    #[Inject(required: false)]
    private ?UserService $userService;
    
    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService tersedia hanya ketika nilainya ada
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### Injeksi Objek Abstrak

Berdasarkan contoh di atas, dari perspektif yang wajar, Controller tidak boleh langsung berhadapan dengan kelas `UserService`, tetapi mungkin dengan kelas interface `UserServiceInterface`. Pada saat ini, kita dapat mengikat hubungan objek melalui `config/autoload/dependencies.php` untuk mencapai tujuan. Mari kita gunakan kode untuk menjelaskannya.

Definisikan kelas interface:

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` mengimplementasikan kelas interface:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // Kita asumsikan sebuah entitas Info ada
        return (new Info())->fill($id);    
    }
}
```

Konfigurasi hubungan di `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

Setelah konfigurasi seperti itu, Anda dapat langsung menginjeksikan objek `UserService` melalui `UserServiceInterface`. Kami hanya menggunakan annotation injection untuk memberikan contoh, constructor injection juga sama:

```php
<?php
namespace App\Controller;

use App\Service\UserServiceInterface;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    #[Inject]
    private UserServiceInterface $userService;
    
    public function index()
    {
        $id = 1;
        // Langsung gunakan
        return $this->userService->getInfoById($id);    
    }
}
```

### Injeksi Objek Factory

Misalkan implementasi `UserService` lebih kompleks. Ketika membuat objek `UserService`, beberapa parameter yang tidak dapat diinjeksikan secara langsung juga perlu dilewatkan ke constructor. Misalkan kita perlu mendapatkan nilai dari konfigurasi, dan kemudian `UserService` perlu memutuskan apakah akan mengaktifkan mode cache berdasarkan nilai ini (omong-omong, Hyperf menyediakan fungsi [Model Cache](id/db/model-cache.md) yang lebih baik).

Kita perlu membuat factory untuk menghasilkan objek `UserService`:

```php
<?php 
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // Implementasikan method __invoke() untuk menyelesaikan produksi objek. Parameter method akan secara otomatis menginjeksikan instance container saat ini dan array parameter
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // Kita asumsikan key dari konfigurasi yang sesuai adalah cache.enable
        $enableCache = $config->get('cache.enable', false);
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` juga dapat menyediakan parameter di constructor untuk menerima nilai yang sesuai:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    private bool $enableCache;
    
    public function __construct(bool $enableCache)
    {
        // Terima nilai dan simpan di properti kelas
        $this->enableCache = $enableCache;
    }
    
    public function getInfoById(int $id)
    {
        return (new Info())->fill($id);    
    }
}
```

Sesuaikan hubungan binding di `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserServiceFactory::class
];
```

Dengan cara ini, ketika `UserServiceInterface` diinjeksikan, container akan menyerahkan pembuatan objek ke `UserServiceFactory`.

> Tentu saja, dalam skenario ini, Anda dapat menginjeksikan konfigurasi dengan lebih nyaman melalui annotation `#[Value]` tanpa membangun factory class. Ini hanya sebuah contoh.

### Lazy Loading

Dependency Injection dengan lifecycle panjang Hyperf diselesaikan ketika proyek dimulai. Ini berarti bahwa kelas dengan lifecycle panjang perlu memperhatikan:

* Constructor belum berada di lingkungan coroutine. Jika sebuah kelas yang mungkin memicu coroutine switching diinjeksikan, itu akan menyebabkan framework gagal dimulai.

* Hindari circular dependencies di constructor (contoh tipikal adalah `Listener` dan `EventDispatcherInterface`), jika tidak, ini juga akan gagal dimulai.

Solusi saat ini adalah: hanya injeksikan `Psr\Container\ContainerInterface` dalam instance, dan komponen lainnya diperoleh melalui `container` ketika non-constructor dieksekusi. Tetapi PSR-11 menunjukkan:

> "Pengguna tidak boleh melewatkan container sebagai argumen ke objek untuk mendapatkan dependensi objek dari container di dalam objek. Ini menggunakan container sebagai service locator, dan service locator adalah anti-pattern."

Artinya, meskipun praktik ini efektif, tidak disarankan dari perspektif design pattern.

Solusi lain adalah dengan menggunakan lazy proxy pattern yang umum digunakan di PHP, menginjeksikan objek proxy, dan kemudian membuat instance objek target ketika digunakan. Hyperf DI component mendesain fungsi lazy loading injection.

Tambahkan file `config/lazy_loader.php` dan ikat hubungan lazy loading:

```php
<?php
return [
    /**
     * Format: Nama kelas Proxy => Nama kelas asli
     * Kelas proxy belum ada saat ini, Hyperf akan secara otomatis menghasilkan kelas di bawah folder runtime.
     * Nama kelas proxy dan namespace dapat didefinisikan secara bebas.
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

Dengan cara ini, ketika `App\Service\LazyUserService` diinjeksikan, container akan membuat `lazy loading proxy class` dan menginjeksikannya ke objek target.

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
```

Anda juga dapat menginjeksikan lazy loading proxy melalui annotation `#[Inject(lazy: true)]`. Mengimplementasikan lazy loading melalui annotation tidak perlu membuat file konfigurasi.

```php
use Hyperf\Di\Annotation\Inject;
use App\Service\UserServiceInterface;

class Foo
{
    /**
     * @var UserServiceInterface
     */
    #[Inject(lazy: true)]
    public $service;
}
```

Catatan: Ketika objek proxy melakukan operasi berikut, objek yang diproksi akan benar-benar dibuat dari container.

```php
// Method call
$proxy->someMethod();

// Membaca properti
echo $proxy->someProperty;

// Menulis properti
$proxy->someProperty = 'foo';

// Mengecek apakah properti ada
isset($proxy->someProperty);

// Menghapus properti
unset($proxy->someProperty);
```

### Binding Weight

Sejak versi v3.0.17, fungsi weight telah ditambahkan. Anda dapat menginjeksikan objek dengan weight tertinggi sesuai dengan weight. Sebagai contoh, dua konfigurasi `ConfigProvider` berikut

```php
<?php
use FooInterface;
use Foo;

return [
    'dependencies' => [
        FooInterface::class => new PriorityDefinition(Foo::class, 1),
    ]
];
```

```php
<?php
use FooInterface;
use Foo2;

return [
    'dependencies' => [
        FooInterface::class => Foo2::class,
    ]
];
```

Ketika `PriorityDefinition` tidak digunakan, weight-nya adalah 0. Jadi yang terikat ke `FooInterface` adalah `Foo`.

## Short Lifecycle Objects

Objek yang dibuat melalui keyword `new` sudah pasti bersifat short-lived. Jadi bagaimana jika Anda ingin membuat objek short-lived tetapi ingin menggunakan `fungsi injeksi dependensi otomatis constructor`? Pada saat ini, kita dapat membuat instance yang sesuai dengan `$name` melalui fungsi `make(string $name, array $parameters = [])`. Contoh kodenya adalah sebagai berikut:

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> Perhatikan bahwa hanya objek yang sesuai dengan `$name` yang merupakan objek short-lived. Semua dependensi dari objek ini diperoleh melalui method `get()`, yaitu objek long-lived. Dapat dipahami bahwa objek ini adalah objek shallow copy.

## Mendapatkan Objek Container

Terkadang ketika kita ingin mengimplementasikan beberapa kebutuhan yang lebih dinamis, kita ingin dapat langsung mendapatkan objek `Container`. Dalam banyak kasus, kelas entry framework (seperti command class, controller, penyedia layanan RPC, dll.) dibuat dan dikelola oleh `Container`, yang berarti bahwa sebagian besar kode bisnis yang Anda tulis berada di bawah manajemen `Container`, yang berarti bahwa dalam banyak kasus, Anda dapat memperoleh objek container `Hyperf\Di\Container` dengan mendeklarasikannya di `Constructor` atau menginjeksikan kelas interface `Psr\Container\ContainerInterface` melalui annotation `#[Inject]`. Kami mendemonstrasikannya dengan kode:

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    private ContainerInterface $container;
    
    // Menyelesaikan injeksi otomatis dengan mendeklarasikan tipe parameter di parameter constructor
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```

Dalam situasi dinamis yang lebih ekstrem, atau ketika tidak berada di bawah manajemen `Container`, untuk mendapatkan objek `Container`, Anda juga dapat memperoleh objek `Container` melalui method `\Hyperf\Context\ApplicationContext::getContainer()`.

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## Scan Adapter

Secara default, `Hyperf\Di\ScanHandler\PcntlScanHandler` digunakan.

- Hyperf\Di\ScanHandler\PcntlScanHandler

Menggunakan Pcntl untuk fork child process guna memindai annotation, hanya didukung di lingkungan Linux

- Hyperf\Di\ScanHandler\NullScanHandler

Tidak melakukan operasi pemindaian annotation

- Hyperf\Di\ScanHandler\ProcScanHandler

Menggunakan proc_open untuk membuat child process guna memindai annotation, didukung di Linux dan Windows (Swow)

### Mengganti Scan Adapter

Kita hanya perlu secara aktif memodifikasi potongan kode `Hyperf\Di\ClassLoader::init()` di file `bin/hyperf.php` untuk mengganti adapter.

```php
Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
```

## Hal yang Perlu Diperhatikan

### Container Hanya Mengelola Long-lifecycle Objects

Dengan kata lain, ini berarti bahwa objek yang dikelola dalam container **semuanya adalah singleton**. Desain ini lebih efisien untuk aplikasi dengan lifecycle panjang, mengurangi sejumlah besar pembuatan dan penghancuran objek yang tidak berarti. Desain ini juga berarti bahwa semua objek yang perlu dikelola oleh DI container **tidak boleh mengandung** nilai `state`.
`State` dapat langsung dipahami sebagai nilai yang berubah seiring dengan request. Sebenarnya, dalam pemrograman [Coroutine](id/coroutine.md), nilai state ini juga harus disimpan di `Coroutine Context`, yaitu `Hyperf\Context\Context`.

### Urutan Override Injeksi `#[Inject]`

Urutan override `#[Inject]` adalah: subclass override `Trait` override parent class. Artinya, variabel `foo` dari `Origin` di bawah adalah `Foo1` yang diinjeksikan oleh dirinya sendiri.

Demikian pula, jika variabel `$foo` tidak ada di `Origin`, `$foo` akan diinjeksikan oleh `Trait` pertama, menginjeksikan kelas `Foo2`.

```php
use Hyperf\Di\Annotation\Inject;

class ParentClass
{
    /**
     * @var Foo4 
     */
    #[Inject]
    protected $foo;
}

trait Foo1
{
    /**
     * @var Foo2 
     */
    #[Inject]
    protected $foo;
}

trait Foo2
{
    /**
     * @var Foo3
     */
    #[Inject]
    protected $foo;
}

class Origin extends ParentClass
{
    use Foo1;
    use Foo2;

    /**
     * @var Foo1
     */
    #[Inject]
    protected $foo;
}
```
