# Mekanisme Event

## Kata Pengantar

Event pattern wajib diimplementasikan berdasarkan [PSR-14](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-14-event-dispatcher.md).
Event manager Hyperf diimplementasikan oleh [hyperf/event](https://github.com/hyperf/event) secara default. Komponen ini juga bisa dipake di framework atau aplikasi lain, tinggal install lewat Composer.

```bash
composer require hyperf/event
```

## Konsep

Event pattern adalah mekanisme yang udah teruji dan cocok banget buat decoupling. Ada 3 peran:

- `Event` adalah objek komunikasi yang dilewatkan antara kode aplikasi dan `Listener`
- `Listener` adalah objek pendengar yang digunakan untuk mendengarkan terjadinya `Event`
- `EventDispatcher` adalah objek manajer yang digunakan untuk memicu `Event` dan mengelola hubungan antara `Listener` dan `Event`

Biar gampang dipahami, misalnya kita punya method `UserService::register()` buat daftarin akun. Abis akun berhasil didaftarin, kita bisa picu event `UserRegistered` lewat event dispatcher. Listener bakal dengerin event ini dan jalanin operasi tertentu, misalnya kirim SMS sukses pendaftaran. Seiring berkembangnya bisnis, kita mungkin mau ngelakuin lebih banyak hal pas user berhasil daftar, kayak kirim email sukses juga. Nah, tinggal nambahin listener lain buat dengerin event `UserRegistered`, tanpa perlu ubah kode di method `UserService::register()`.

## Menggunakan Event Manager

> Kita akan bahas dua cara mendaftarkan listener: lewat konfigurasi dan lewat annotation. Pake salah satu aja. Kalau keduanya dipake bareng, listener bakal kepicu berkali-kali.

### Mendefinisikan Event

Event pada dasarnya adalah class biasa yang ngelola data state. Pas dipicu, data aplikasi dilempar ke event, lalu listener ngelakuin operasi di class event tersebut. Satu event bisa didengerin sama banyak listener.

```php
<?php
namespace App\Event;

class UserRegistered
{
    // Disarankan untuk mendefinisikan public property di sini sehingga listener dapat langsung menggunakan properti ini, atau Anda dapat menyediakan Getter untuk properti ini
    public $user;
    
    public function __construct($user)
    {
        $this->user = $user;    
    }
}
```

### Mendefinisikan Listener

Listener perlu mengimplementasikan method constraint dari interface `Hyperf\Event\Contract\ListenerInterface`. Contohnya adalah sebagai berikut.

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Contract\ListenerInterface;

class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // Mengembalikan array event yang ingin didengarkan oleh listener ini, dapat mendengarkan beberapa event sekaligus
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // Kode yang ingin dijalankan listener setelah event dipicu ditulis di sini, seperti mengirim SMS sukses untuk pendaftaran pengguna dalam contoh ini
        // Akses langsung properti user dari $event untuk mendapatkan nilai parameter yang dilewatkan saat event dipicu
        // $event->user;
        
    }
}
```

#### Mendaftarkan Listener melalui File Konfigurasi

Setelah mendefinisikan listener, kita harus daftarin ke `EventDispatcher` biar dikenali. Tinggal tambahin listener di file konfigurasi `config/autoload/listeners.php` *(buat kalo belum ada)*. Urutan pemicuan listener tergantung urutan di file konfigurasi ini:

```php
<?php
return [
    \App\Listener\UserRegisteredListener::class,
];
```

### Mendaftarkan Listener melalui Annotation

Hyperf juga nyediain cara yang lebih praktis, lewat annotation `#[Listener]`. Selama annotation ini dipasang di kelas listener dan kelas listener ada di dalam `Hyperf annotation scan domain`, pendaftaran bakal otomatis. Contohnya:

```php
<?php
namespace App\Listener;

use App\Event\UserRegistered;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class UserRegisteredListener implements ListenerInterface
{
    public function listen(): array
    {
        // Mengembalikan array event yang ingin didengarkan oleh listener ini, dapat mendengarkan beberapa event sekaligus
        return [
            UserRegistered::class,
        ];
    }

    /**
     * @param UserRegistered $event
     */
    public function process(object $event): void
    {
        // Kode yang ingin dijalankan listener setelah event dipicu ditulis di sini, seperti mengirim SMS sukses untuk pendaftaran pengguna dalam contoh ini
        // Akses langsung properti user dari $event untuk mendapatkan nilai parameter yang dilewatkan saat event dipicu
        // $event->user;
    }
}
```

Pas daftarin listener lewat annotation, kita bisa ngatur urutan pake properti `priority`, misal `#[Listener(priority=1)]`. Di dalemnya, prioritas diatur pake struktur `SplPriorityQueue`, makin gede angka `priority`, makin tinggi prioritasnya.

> Saat menggunakan annotation `#[Listener]`, Anda perlu `use Hyperf\Event\Annotation\Listener;` namespace;

### Memicu Event

Event cuma bisa didengerin sama `Listener` kalo udah dikirim lewat `EventDispatcher`. Langsung aja liat kode berikut buat demo cara picu event:

```php
<?php
namespace App\Service;

use Hyperf\Di\Annotation\Inject;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\Event\UserRegistered; 

class UserService
{
    #[Inject]
    private EventDispatcherInterface $eventDispatcher;
    
    public function register()
    {
        // Kita asumsikan sebuah entitas User ada
        $user = new User();
        $result = $user->save();
        // Menyelesaikan logika pendaftaran akun
        // Di sini dispatch(object $event) akan menjalankan listener yang mendengarkan event ini satu per satu
        $this->eventDispatcher->dispatch(new UserRegistered($user));
        return $result;
    }
}
```

## Hyperf Lifecycle Events

![](imgs/hyperf-events.svg)

## Hyperf Coroutine-style Lifecycle Events

![](https://raw.githubusercontent.com/hyperf/raw-storage/main/hyperf/svg/hyperf-coroutine-events.svg)

## Hal yang Perlu Diperhatikan

### Jangan Menginjeksikan `EventDispatcherInterface` di `Listener`

Soalnya `EventDispatcherInterface` butuh `ListenerProviderInterface`, dan `ListenerProviderInterface` ngumpulin semua `Listeners` pas diinisialisasi.

Kalau `Listener` juga butuh `EventDispatcherInterface`, jadinya circular dependency, bisa bikin memory overflow.

### Sebaiknya Hanya Menginjeksikan `ContainerInterface` di `Listener`

Sebaiknya cuma `ContainerInterface` yang di-inject ke `Listener`, komponen lain ambil aja dari `container` di method `process`. Pas framework mulai, `EventDispatcherInterface` bakal dibuat, saat itu belum ada lingkungan coroutine. Kalau kelas yang bisa memicu coroutine switching di-inject ke `Listener`, framework bakal gagal start.
