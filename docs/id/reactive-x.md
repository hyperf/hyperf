# Integrasi ReactiveX

Komponen [hyperf/reactive-x](https://github.com/hyperf/reactive-x) menyediakan
integrasi ReactiveX dalam lingkungan Swoole/Hyperf.

## Sejarah ReactiveX

ReactiveX adalah singkatan dari Reactive Extensions, yang umumnya disingkat
sebagai Rx. Pada awalnya, ini merupakan ekstensi untuk LINQ. Rx dikembangkan
oleh tim yang dipimpin oleh arsitek Microsoft Erik Meijer, dan dijadikan open
source pada November 2012. Rx adalah sebuah model pemrograman. Tujuannya adalah
untuk menyediakan interface pemrograman yang konsisten guna membantu developer
menangani data stream asinkron dengan lebih mudah. Library Rx mendukung .NET,
JavaScript, dan C++. Rx menjadi semakin populer dalam beberapa tahun terakhir,
dan kini mendukung hampir semua bahasa pemrograman populer. Sebagian besar
library bahasa Rx dikelola oleh organisasi ReactiveX, dengan beberapa yang
paling populer adalah RxJava/RxJS/Rx.NET, dan situs web komunitasnya adalah
[reactivex.io](http://reactivex.io).

## Apa itu ReactiveX

Definisi dari Microsoft adalah bahwa Rx merupakan sebuah library fungsi yang
memungkinkan developer menulis program berbasis event dan asinkron menggunakan
observable sequence dan operator query bergaya LINQ. Dengan menggunakan Rx,
developer dapat menggunakan Observable untuk merepresentasikan data stream
asinkron, menggunakan Operator LINQ untuk melakukan query pada data stream
asinkron, dan menggunakan Scheduler untuk memparameterisasi pemrosesan konkuren
dari data stream asinkron. Rx dapat didefinisikan sebagai berikut: Rx =
Observables + LINQ + Schedulers.

Definisi yang diberikan oleh [Reactivex.io](http://reactivex.io) adalah bahwa Rx
merupakan interface pemrograman untuk pemrograman asinkron menggunakan
observable data stream. ReactiveX menggabungkan esensi dari observer pattern,
iterator pattern, dan pemrograman fungsional.

> Dua bagian di atas diambil dari [RxDocs](https://github.com/mcxiaoke/RxDocs).

## Pertimbangkan Sebelum Menggunakan

### Kelebihan

- Dengan menggunakan cara berpikir pemrograman reaktif (reactive programming),
  beberapa masalah asinkron yang kompleks dapat disederhanakan.

- Jika Anda sudah memiliki pengalaman pemrograman reaktif di bahasa lain (seperti
  RxJS/RxJava), komponen ini dapat membantu Anda menerapkan pengalaman tersebut
  ke Hyperf.

- Meskipun Swoole menyarankan penulisan program asinkron seperti program
  sinkron melalui coroutine, Swoole tetap memiliki banyak event, dan menangani
  event adalah keunggulan dari Rx.

- Rx juga dapat memainkan peran penting jika bisnis Anda melibatkan pemrosesan
  stream seperti WebSocket, gRPC streaming, dll.

### Kekurangan

- Cara berpikir pemrograman reaktif cukup berbeda dengan cara berpikir
  berorientasi objek tradisional, yang mengharuskan developer untuk beradaptasi.

- Rx hanya menyediakan cara berpikir, tidak ada keajaiban tambahan. Masalah
  yang dapat diselesaikan dengan pemrograman reaktif juga dapat diselesaikan
  dengan cara tradisional.

- RxPHP bukanlah yang terbaik dalam keluarga Rx.

## Instalasi

```bash
composer require hyperf/reactive-x
```

## Paket

Mari kita perkenalkan beberapa enkapsulasi dari komponen ini dengan contoh dan
demonstrasikan kemampuan kuat dari Rx. Semua contoh dapat ditemukan dalam
komponen ini di bawah direktori `src/Example`.

### Observable::fromEvent

`Observable::fromEvent` mengubah event standar PSR menjadi observable sequence.

Event listener untuk mencetak SQL statement disediakan secara default di dalam
paket skeleton hyperf-skeleton, dengan lokasi default di
`app/Listener/DbQueryExecutedListener.php`. Mari kita lakukan beberapa
optimasi pada listener ini:

1. Hanya mencetak SQL query yang memakan waktu lebih dari 100ms.

2. Setiap koneksi hanya dapat mencetak maksimal 1 kali per detik untuk
   menghindari hard disk penuh akibat program bermasalah.

Tanpa ReactiveX, poin pertama tidak masalah, namun poin kedua akan memerlukan
beberapa pemikiran ekstra. Dengan ReactiveX, persyaratan ini dapat diselesaikan
dengan mudah melalui contoh kode berikut:

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Logger\LoggerFactory;
use Hyperf\ReactiveX\Observable;
use Hyperf\Collection\Arr;
use Hyperf\Stringable\Str;
use Psr\Container\ContainerInterface;

class SqlListener implements ListenerInterface
{
    private $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(LoggerFactory::class)->get('sql');
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        Observable::fromEvent(QueryExecuted::class)
            ->filter(
                function ($event) {
                    return $event->time > 100;
                }
            )
            ->groupBy(
                function ($event) {
                    return $event->connectionName;
                }
            )
            ->flatMap(
                function ($group) {
                    return $group->throttle(1000);
                }
            )
            ->map(
                function ($event) {
                    $sql = $event->sql;
                    if (! Arr::isAssoc($event->bindings)) {
                        foreach ($event->bindings as $key => $value) {
                            $sql = Str::replaceFirst('?', "'{$value}'", $sql);
                        }
                    }
                    return [$event->connectionName, $event->time, $sql];
                }
            )->subscribe(
                function ($message) {
                    $this->logger->info(sprintf('slow log: [%s] [%s] %s', ...$message));
                }
            );
    }
}
```

### Observable::fromChannel

Mengubah Channel dalam coroutine Swoole menjadi observable sequence.

Channel dalam coroutine Swoole bersifat read dan write satu-ke-satu. Bagaimana
jika kita ingin melakukan publikasi dan langganan (publish and subscribe)
banyak-ke-banyak melalui Channel di bawah ReactiveX?

Lihat contoh di bawah ini.

```php
<?php

declare(strict_types=1);

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;

$chan = new Channel(1);
$pub = Observable::fromChannel($chan)->publish();

$pub->subscribe(function ($x) {
    echo 'First Subscription:' . $x . PHP_EOL;
});
$pub->subscribe(function ($x) {
    echo 'Second Subscription:' . $x . PHP_EOL;
});
$pub->connect();

$chan->push('hello');
$chan->push('world');

// First Subscription: hello
// Second Subscription: hello
// First Subscription: world
// Second Subscription: world
```

### Observable::fromCoroutine

Membuat satu atau lebih coroutine dan mengubah hasil eksekusinya menjadi
observable sequence.

Sekarang kita membiarkan dua fungsi bersaing dalam coroutine konkuren, dan
fungsi mana pun yang selesai dieksekusi lebih dulu akan mengembalikan hasilnya.
Efeknya serupa dengan `Promise.race` pada JavaScript.

```php
<?php

declare(strict_types=1);

use Hyperf\ReactiveX\Observable;
use Swoole\Coroutine\Channel;

$result = new Channel(1);
$o = Observable::fromCoroutine([function () {
    sleep(2);
    return 1;
}, function () {
    sleep(1);
    return 2;
}]);
$o->take(1)->subscribe(
    function ($x) use ($result) {
        $result->push($x);
    }
);
echo $result->pop(); // 2;
```

### Observable::fromHttpRoute

Semua HTTP request sebenarnya berbasis event. Sehingga routing HTTP request
juga dapat diambil alih dengan ReactiveX.

> Karena kita akan menambahkan route, ini harus dieksekusi sebelum Server
> dimulai, misalnya di dalam event listener `BootApplication`.

Misalkan kita memiliki route upload dengan traffic yang besar, yang perlu
disangga (buffer) di dalam memori dan diunggah secara batch setelah terkumpul sepuluh upload.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\ReactiveX\Observable;
use Psr\Http\Message\RequestInterface;

class BatchSaveRoute implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event)
    {
        Observable::fromHttpRoute(['POST', 'PUT'], '/save')
            ->map(
                function (RequestInterface $request) {
                    return $request->getBody();
                }
            )
            ->bufferWithCount(10)
            ->subscribe(
                function (array $bodies) {
                    echo count($bodies); //10
                }
            );
    }
}
```

Setelah mengambil alih route, jika Anda perlu mengontrol Response yang
dikembalikan, Anda dapat menambahkan parameter ketiga dari `fromHttpRoute`,
yang sama dengan route biasa, misalnya:

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

Pada titik ini, `Observable` bertindak seperti middleware. Setelah mendapatkan
observable sequence dari objek request, ia akan terus meneruskan objek request
tersebut ke `Controller` yang sebenarnya.

### IpcSubject

Komunikasi antar-proses (inter-process communication) dari Swoole juga berbasis
event. Komponen ini menyediakan versi Subject lintas-proses yang sesuai,
berbasis pada empat [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) yang disediakan oleh RxPHP, yang dapat digunakan untuk berbagi informasi
antar-proses.

Sebagai contoh, kita perlu membuat chat room berbasis WebSocket dengan
persyaratan sebagai berikut:

1. Pesan chat room perlu dibagikan di antara `Worker process`.

2. 5 pesan terakhir ditampilkan saat user pertama kali login.

Kita melakukan ini melalui `ReplaySubject` versi lintas-proses.

```php
<?php

declare(strict_types=1);

namespace Hyperf\ReactiveX\Example;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\ReactiveX\Contract\BroadcasterInterface;
use Hyperf\ReactiveX\IpcSubject;
use Rx\Subject\ReplaySubject;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    private IpcSubject $subject;

    private $subscriber = [];

    public function __construct(BroadcasterInterface $broadcaster)
    {
        $relaySubject = make(ReplaySubject::class, ['bufferSize' => 5]);
        // The first parameter is the original RxPHP Subject object.
        // The second parameter is the broadcast mode, the default is the whole process broadcast
        // The third parameter is the channel ID, each channel can only receive messages from the same channel.
        $this->subject = new IpcSubject($relaySubject, $broadcaster, 1);
    }

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
        $this->subject->onNext($frame->data);
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        $this->subscriber[$fd]->dispose();
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        $this->subscriber[$request->fd] = $this->subject->subscribe(function ($data) use ($server, $request) {
            $server->push($request->fd, $data);
        });
    }
}
```

Untuk kemudahan, komponen ini menggunakan `IpcSubject` untuk mengenkapsulasi
sebuah "message bus" `MessageBusInterface`. Cukup inject `MessageBusInterface`
untuk mengirim dan menerima informasi yang dibagikan oleh semua proses
(termasuk custom process). Fitur seperti configuration center dapat
diimplementasikan dengan mudah melalui cara ini.

```php
<?php
$bus = make(Hyperf\ReactiveX\MessageBusInterface::class);
// whole process broadcast information
$bus->onNext('Hello Hyperf');
// subscription info
$bus->subscribe(function($message){
    echo $message;
});
```

> Karena ReactiveX perlu menggunakan event loop, harap dicatat bahwa API yang
> berkaitan dengan ReactiveX harus dipanggil setelah Swoole Server dijalankan.

## Referensi

* [Dokumentasi Rx Bahasa Mandarin](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Dokumentasi Rx Bahasa Inggris](http://reactivex.io/)
* [Repository RxPHP](https://github.com/ReactiveX/RxPHP)
