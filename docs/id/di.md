# Dependency Injection

## Pengenalan

Hyperf menggunakan [hyperf/di](https://github.com/hyperf/di) sebagai container
manajemen dependency injection bawaan framework. Meskipun secara desain kami
memungkinkan Anda untuk mengganti container manajemen dependency injection dengan
komponen lain, sangat disarankan untuk tidak mengganti
[hyperf/di](https://github.com/hyperf/di).

[hyperf/di](https://github.com/hyperf/di) adalah komponen kuat yang digunakan
untuk mengelola dependency kelas dan melakukan injeksi otomatis. Dibandingkan
dengan container dependency injection tradisional, komponen ini lebih cocok untuk
aplikasi berumur panjang (long-life applications), menyediakan dukungan
[Annotation & Annotation Injection](id/annotation.md) serta kemampuan
[AOP Aspect-Oriented Programming](id/aop.md) yang sangat kuat. Kemampuan dan
kemudahan penggunaan ini adalah output utama dari Hyperf, dan kami sangat
percaya bahwa komponen ini adalah yang terbaik.

## Instalasi

Komponen ini sudah ada secara default di dalam
[hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) dan berfungsi
sebagai komponen utama. Jika Anda ingin menggunakan komponen ini di framework
lain, Anda dapat menginstalnya dengan perintah berikut.

```bash
composer require hyperf/di
```

## Binding Hubungan Objek

### Injeksi Objek Sederhana

Secara umum, hubungan dan injeksi dari suatu kelas tidak perlu didefinisikan secara
jelas. Hyperf akan melakukan semua ini untuk Anda. Demo kode berikut akan
mengilustrasikan penggunaan terkait.
Misalkan kita perlu memanggil method `getInfoById(int $id)` dari kelas
`UserService` di dalam `IndexController`.

```php
<?php
namespace App\Service;

class UserService
{
    public function getInfoById(int $id)
    {
        // Assume that there is an entity of Info.
        return (new Info())->fill($id);
    }
}
```

#### Injeksi melalui Constructor

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private UserService $userService;

    // Automatic injection is completed by declaring the parameter type on the parameters of the constructor
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $id = 1;
        // Use directly
        return $this->userService->getInfoById($id);
    }
}
```

> Perhatikan bahwa pemanggil, yaitu `IndexController`, harus berupa objek yang
> dibuat oleh `DI` untuk melakukan injeksi otomatis. Dan controller dibuat oleh
> `DI` secara default, sehingga Anda dapat melakukan injeksi langsung di
> constructor.

Ketika Anda ingin mendefinisikan dependency opsional, Anda dapat mendefinisikan
parameter sebagai `nullable` atau menetapkan nilai default parameter sebagai
`null`. Ini berarti jika parameter tidak ditemukan di dalam container DI atau
objek terkait tidak dapat dibuat, `null` akan diinjeksikan alih-alih melempar
exception. *(Fitur ini hanya tersedia pada versi 1.1.0 atau yang lebih tinggi)*

```php
<?php
namespace App\Controller;

use App\Service\UserService;

class IndexController
{
    private ?UserService $userService;

    // Declare an optional parameter by setting it as nullable.
    public function __construct(?UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService is available only in the condition that it is not null
            return $this->userService->getInfoById($id);
        }
        return null;
    }
}
```

#### Injeksi melalui `#[Inject]`

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
        // Use directly
        return $this->userService->getInfoById($id);
    }
}
```

> Injeksi melalui annotation `#[Inject]` dapat bekerja pada objek singleton yang
> dibuat oleh DI, dan juga pada objek yang dibuat dengan keyword `new`.

> Namespace `use Hyperf\Di\Annotation\Inject;` harus digunakan ketika
> `#[Inject]` digunakan.

##### Parameter Wajib (Required Parameter)

Annotation `#[Inject]` memiliki parameter `required`, dan nilai default-nya adalah
`true`. Ketika parameter didefinisikan sebagai `false`, ini menunjukkan bahwa
atribut ini adalah dependency opsional. Ketika objek yang sesuai dengan `@var`
tidak ada di dalam DI, `null` akan diinjeksikan alih-alih melempar exception.

```php
<?php
namespace App\Controller;

use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class IndexController
{
    /**
     * Menginjeksikan objek tipe property yang dideklarasikan oleh annotation `#[Inject]`
     * Jika UserService tidak ada di DI container atau tidak dapat dibuat, null akan diinjeksikan
     */
    #[Inject(required: false)]
    private ?UserService $userService;

    public function index()
    {
        $id = 1;
        if ($this->userService instanceof UserService) {
            // $userService is available only in the condition that it is not null
            return $this->userService->getInfoById($id);    
        }
        return null;
    }
}
```

### Injeksi Objek Abstrak

Berdasarkan contoh di atas, dari sudut pandang yang masuk akal, Controller
seharusnya tidak bekerja secara langsung dengan kelas `UserService`, melainkan lebih
ke kelas interface seperti `UserServiceInterface`. Oleh karena itu, kita dapat
menggunakan `config/autoload/dependencies.php` untuk mengikat (bind) hubungan
objek tersebut guna mencapai tujuan ini. Demo kode berikut dapat menjelaskannya.

Definisikan sebuah kelas interface:

```php
<?php
namespace App\Service;

interface UserServiceInterface
{
    public function getInfoById(int $id);
}
```

`UserService` mengimplementasikan interface tersebut:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    public function getInfoById(int $id)
    {
        // Assume that there is an entity of Info.
        return (new Info())->fill($id);    
    }
}
```

Konfigurasikan hubungan (binding) di `config/autoload/dependencies.php`:

```php
<?php
return [
    \App\Service\UserServiceInterface::class => \App\Service\UserService::class
];
```

Setelah konfigurasi ini, Anda dapat langsung menginjeksikan objek `UserService`
melalui `UserServiceInterface`. Kita menggunakan injeksi annotation sebagai contoh,
dan injeksi constructor juga sama:

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
        // Use directly
        return $this->userService->getInfoById($id);    
    }
}
```

### Injeksi Objek Factory

Sekarang, mari buat implementasi `UserService` menjadi lebih kompleks, di mana ada
beberapa parameter yang diinjeksikan secara tidak langsung yang harus dimasukkan
ke dalam constructor saat instance `UserService` dibuat. Bayangkan kita harus
mengambil nilai dari config, lalu `UserService` perlu memutuskan apakah akan
mengaktifkan mode cache berdasarkan nilai ini. (Sebagai info tambahan, Hyperf
menyediakan fungsi [mode cache](id/db/model-cache.md) yang lebih baik)

Kita harus membuat factory untuk menghasilkan objek `UserService`:

```php
<?php
namespace App\Service;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class UserServiceFactory
{
    // Implement an __invoke() method for the production of the object, and parameters will be automatically injected into a current container instance and the parameters array.
    public function __invoke(ContainerInterface $container, array $parameters = [])
    {
        $config = $container->get(ConfigInterface::class);
        // Assume that the key of corresponding config is cache.enable
        $enableCache = $config->get('cache.enable', false);
        // The method make(string $name, array $parameters = []) is equivalent to new. Using make() allows AOP to intervene, however, using new will prevent AOP to intervene into normal processing.
        return make(UserService::class, compact('enableCache'));
    }
}
```

`UserService` mungkin menyediakan atribut di dalam constructor untuk menerima
nilai yang sesuai:

```php
<?php
namespace App\Service;

class UserService implements UserServiceInterface
{
    private bool $enableCache;

    public function __construct(bool $enableCache)
    {
        // Receiving the value and store it at an attribute
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

Dengan cara ini, saat menginjeksikan `UserServiceInterface`, container akan
menyerahkan pembuatan objek kepada `UserServiceFactory`.

> Tentu saja, dalam skenario ini, Anda dapat menggunakan annotation `#[Value]`
> untuk menginjeksikan konfigurasi secara lebih mudah daripada membangun kelas
> factory. Contoh ini hanya untuk penjelasan.

### Lazy Loading

Dependency injection berumur panjang (long-lived) di Hyperf diselesaikan saat proyek
dimulai. Ini berarti kelas berumur panjang perlu memperhatikan hal-hal berikut:

* Lingkungan saat constructor berjalan bukanlah lingkungan coroutine. Jika injeksi
  terjadi, kelas yang memicu peralihan coroutine (coroutine switching) mungkin
  akan terpicu. Hal ini akan menyebabkan framework gagal dimulai.

* Hindari circular dependency di dalam constructor (biasanya, `Listener` dan
  `EventDispatcherInterface`), jika tidak, proses startup akan gagal.

Solusi saat ini adalah: hanya injeksikan `Psr\Container\ContainerInterface` ke
dalam instance, dan komponen lainnya diperoleh melalui `container` saat
diperlukan di luar waktu eksekusi constructor. Namun, seperti yang dinyatakan
dalam PSR-11:

> 「Pengguna sebaiknya tidak melewatkan container sebagai parameter ke suatu objek
> lalu mendapatkan dependency dari objek tersebut melalui container yang dilewatkan.
> Ini menggunakan container sebagai service locator, dan service locator adalah
> sebuah anti-pattern.」

Dengan kata lain, meskipun pendekatan ini berhasil, hal ini tidak disarankan
dari perspektif design pattern.

Solusi lainnya adalah menggunakan mode lazy proxy yang umum digunakan di PHP,
yaitu menginjeksikan objek proxy, lalu menginstansiasi objek target saat
objek tersebut digunakan.
Komponen Hyperf DI dirancang dengan fungsi injeksi lazy loading.

Tambahkan file `config/lazy_loader.php` dan ikat hubungan lazy loading:

```php
<?php
return [
    /**
     * Format: proxy class name => original class name
     * The proxy class does not exist at this time, and Hyperf will automatically generate this class in the runtime folder.
     * The proxy class name and namespace can be defined by yourself.
     */
    'App\Service\LazyUserService' => \App\Service\UserServiceInterface::class
];
```

Dengan cara ini, saat menginjeksikan `App\Service\LazyUserService`, container akan
membuat `lazy loading proxy class` dan menginjeksikannya ke dalam objek target.

```php
use App\Service\LazyUserService;

class Foo{
    public $service;
    public function __construct(LazyUserService $service){
        $this->service = $service;
    }
}
```

Anda juga dapat menginjeksikan lazy loading proxy melalui annotation
`#[Inject(lazy: true)]`. Mengimplementasikan lazy loading melalui annotation
tidak memerlukan pembuatan file konfigurasi.

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

Catatan: Ketika objek proxy melakukan operasi berikut, objek proxy tersebut
akan benar-benar diinstansiasi dari container.

```php
// Call methods
$proxy->someMethod();

// Get attributes
echo $proxy->someProperty;

// Set attributes
$proxy->someProperty = 'foo';

// Check if a attribute exists
isset($proxy->someProperty);

// Delete attributes
unset($proxy->someProperty);
```

### Bobot Binding (Binding Weight)

Sejak versi v3.0.17, fitur bobot (weight) telah ditambahkan. Objek dengan bobot terbesar dapat diinjeksikan berdasarkan urutan bobotnya. Sebagai contoh, perhatikan dua konfigurasi `ConfigProvider` berikut:

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

Jika tidak menggunakan `PriorityDefinition`, maka nilai default bobotnya adalah 0. Sehingga objek yang di-bind ke `FooInterface` adalah `Foo`.

## Objek Berumur Pendek

Objek yang dibuat dengan perintah `new` tidak diragukan lagi adalah objek berumur
pendek. Jika Anda ingin membuat objek berumur pendek dan ingin menginjeksikan
dependency terkait melalui container dependency injection, Anda dapat membuat
`$name` melalui fungsi `make(string $name, array $parameters = [])`. Contoh kode adalah
sebagai berikut:

```php
$userService = make(UserService::class, ['enableCache' => true]);
```

> Perhatikan bahwa hanya objek yang sesuai dengan `$name` yang merupakan objek
> berumur pendek, dan semua dependency dari objek ini diperoleh melalui method
> `get()`, yang berarti objek dependency tersebut adalah objek berumur panjang.

## Mendapatkan Objek Container

Terkadang kita ingin mencapai beberapa kebutuhan yang lebih dinamis, di mana kita
ingin dapat secara langsung memperoleh objek `Container`. Dalam kebanyakan kasus,
kelas entri framework, seperti kelas command, controller, RPC service provider,
dll., dibuat dan dipelihara oleh `Container`, yang berarti sebagian besar kode
bisnis Anda berada di bawah manajemen `Container`. Ini juga berarti bahwa dalam
kebanyakan kasus Anda dapat memperoleh objek `Hyperf\Di\Container` dengan mendeklarasikannya
dalam `Constructor` or dengan menginjeksikan interface `Psr\Container\ContainerInterface`
melalui annotation `#[Inject]`. Berikut adalah contohnya:

```php
<?php
namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Psr\Container\ContainerInterface;

class IndexController
{
    private ContainerInterface $container;
    
    // Automatic injection is completed by declaring the parameter type on the parameters of the constructor
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
```

Dalam beberapa situasi dinamis yang lebih ekstrem, atau ketika tidak berada di
bawah manajemen `Container`, Anda juga dapat menggunakan method
`\Hyperf\Context\ApplicationContext::getContainer()` untuk mendapatkan objek
`Container`.

```php
$container = \Hyperf\Context\ApplicationContext::getContainer();
```

## Adaptor Pemindaian

Secara default menggunakan `Hyperf\Di\ScanHandler\PcntlScanHandler`.

- Hyperf\Di\ScanHandler\PcntlScanHandler

Menggunakan Pcntl fork pada proses child untuk memindai annotation, hanya mendukung lingkungan Linux.

- Hyperf\Di\ScanHandler\NullScanHandler

Tidak melakukan operasi pemindaian annotation.

- Hyperf\Di\ScanHandler\ProcScanHandler

Menggunakan `proc_open` untuk membuat proses child memindai annotation, mendukung Linux dan Windows (Swow).

### Mengganti Adaptor Pemindaian

Kita hanya perlu mengubah potongan kode `Hyperf\Di\ClassLoader::init()` di dalam file `bin/hyperf.php` untuk mengganti adaptor.

```php
Hyperf\Di\ClassLoader::init(handler: new Hyperf\Di\ScanHandler\ProcScanHandler());
```

## Perhatian

### Container hanya mengelola objek berumur panjang

Dengan kata lain, objek yang dikelola oleh container **semuanya adalah singleton**.
Desain ini lebih efisien untuk aplikasi berumur panjang, mengurangi pembuatan
dan penghancuran objek yang tidak berarti. Ini juga berarti bahwa semua objek
yang perlu dikelola oleh container DI **tidak boleh** mengandung nilai status (`state`).
Nilai status tersebut mewakili beberapa nilai yang akan berubah seiring dengan
request. Faktanya, dalam pemrograman [coroutine](id/coroutine.md), nilai status ini
juga harus disimpan dalam `coroutine context`, yaitu `Hyperf\Context\Context`.

### Urutan Overriding Injeksi `#[Inject]`

Urutan overriding untuk `#[Inject]` adalah: Sub-kelas menimpa (override) `Trait`, dan `Trait` menimpa (override) Parent class. Sebagai contoh, variabel `foo` pada kelas `Origin` di bawah ini akan diinjeksi dengan `Foo1` miliknya sendiri.

Demikian pula, jika `Origin` tidak mendefinisikan variabel `$foo`, maka `$foo` akan diinjeksi oleh `Trait` pertama yang dipanggil, yaitu kelas `Foo2`.

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
