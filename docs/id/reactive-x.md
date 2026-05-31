# ReactiveX Integration

Komponen [hyperf/reactive-x](https://github.com/hyperf/reactive-x) menyediakan integrasi ReactiveX di lingkungan Swoole/Hyperf.

## Sejarah ReactiveX

ReactiveX, umumnya disingkat Rx, adalah kependekan dari Reactive Extensions. Awalnya merupakan ekstensi dari LINQ yang dikembangkan oleh tim yang dipimpin arsitek Microsoft Erik Meijer, dan dirilis sebagai open-source pada November 2012. Rx adalah model pemrograman yang bertujuan menyediakan antarmuka yang konsisten untuk membantu developer menangani aliran data asinkron dengan lebih mudah. Pustaka Rx mendukung .NET, JavaScript, dan C++. Rx semakin populer dan kini mendukung hampir semua bahasa pemrograman utama. Sebagian besar pustaka bahasa untuk Rx dikelola oleh organisasi ReactiveX, seperti RxJava, RxJS, dan Rx.NET. Situs web komunitasnya adalah [reactivex.io](http://reactivex.io).

## Apa itu ReactiveX

Microsoft mendefinisikan Rx sebagai pustaka fungsi yang memungkinkan developer menulis program asinkron dan berbasis event menggunakan observable sequences dan operator query bergaya LINQ. Menggunakan Rx, developer dapat merepresentasikan aliran data asinkron dengan Observables, melakukan query aliran data asinkron dengan operator LINQ, dan memparametrikan pemrosesan konkuren dari aliran data asinkron dengan Schedulers. Rx dapat didefinisikan sebagai: Rx = Observables + LINQ + Schedulers.

[Reactivex.io](http://reactivex.io) mendefinisikan Rx sebagai antarmuka pemrograman untuk pemrograman asinkron menggunakan aliran data observable. ReactiveX menggabungkan esensi dari pola Observer, pola Iterator, dan pemrograman fungsional.

> Dua bagian di atas dikutip dari [RxDocs](https://github.com/mcxiaoke/RxDocs).

## Hal yang perlu dipertimbangkan sebelum digunakan

### Kelebihan

- Melalui mode berpikir reactive programming, beberapa masalah asinkron yang kompleks dapat disederhanakan.

- Jika Anda memiliki pengalaman dengan reactive programming di bahasa lain (seperti RxJS/RxJava), komponen ini dapat membantu Anda membawa pengalaman tersebut ke Hyperf.

- Meskipun di Swoole disarankan untuk menulis program asinkron seperti program sinkron melalui coroutine, Swoole masih mengandung sejumlah besar event, dan pemrosesan event adalah keunggulan utama Rx.

- Jika bisnis Anda mengandung stream processing, seperti WebSocket, gRPC streaming, dll., Rx juga dapat memainkan peran penting.

### Kekurangan

- Cara berpikir reactive programming cukup berbeda dari pemrograman berorientasi objek tradisional, butuh waktu untuk beradaptasi.

- Rx hanya menyediakan cara berpikir, tanpa sihir tambahan. Masalah yang bisa diselesaikan dengan reactive programming juga bisa diselesaikan dengan cara tradisional.

- RxPHP bukan yang terbaik dalam keluarga Rx.

## Instalasi

```bash
composer require hyperf/reactive-x
```

## Enkapsulasi

Berikut ini beberapa contoh enkapsulasi dari komponen ini untuk menunjukkan kemampuan Rx. Semua contoh bisa ditemukan di `src/Example` komponen ini.

### Observable::fromEvent

`Observable::fromEvent` mengonversi event standar PSR menjadi observable sequences.

Dalam paket skeleton hyperf-skeleton, event listener untuk mencetak pernyataan SQL disediakan secara default, berlokasi di `app/Listener/DbQueryExecutedListener.php`. Di bawah ini, kami melakukan beberapa optimasi pada listener ini:

1. Hanya mencetak query SQL yang melebihi 100ms.

2. Maksimal mencetak sekali per detik untuk setiap koneksi, agar hard drive tidak banjir oleh program yang bermasalah.

Tanpa ReactiveX, masalah nomor 1 mudah, tapi nomor 2 butuh usaha lebih. Dengan ReactiveX, kedua kebutuhan ini bisa diselesaikan dengan mudah:

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
                fn ($event) => $event->time > 100
            )
            ->groupBy(
                fn ($event) => $event->connectionName
            )
            ->flatMap(
                fn ($group) => $group->throttle(1000)
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
                fn ($message) => $this->logger->info(sprintf('slow log: [%s] [%s] %s', ...$message))
            );
    }
}
```

### Observable::fromChannel

Mengonversi Channel dalam coroutine Swoole menjadi observable sequence.

Channel di coroutine Swoole bersifat satu-ke-satu untuk baca-tulis. Lalu bagaimana cara membuat subscription dan publishing multi-ke-multi menggunakan Channel di ReactiveX?

Silakan lihat contoh di bawah ini.

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

Membuat satu atau lebih coroutine dan mengonversi hasil eksekusi menjadi observable sequence.

Sekarang kita biarkan dua fungsi berlomba dalam coroutine konkuren, dan mengembalikan hasil dari yang selesai lebih dulu. Efeknya mirip `Promise.race` di JavaScript.

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

Semua permintaan HTTP sebenarnya berbasis event. Oleh karena itu, routing permintaan HTTP juga dapat dikelola oleh ReactiveX.

> Karena kita menambahkan route, pastikan untuk menjalankan ini sebelum Server dimulai, misalnya di event listener `BootApplication`.

Misalnya ada route upload dengan lalu lintas tinggi yang perlu di-buffer di memori dan diproses secara batch setelah sepuluh kali upload.

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

Setelah mengambil alih routing, jika Anda perlu mengontrol Response yang dikembalikan, Anda dapat menambahkan parameter ketiga di `fromHttpRoute`, yang sama dengan cara penulisan route normal, seperti:

```php
$observable = Observable::fromHttpRoute('GET', '/hello-hyperf', 'App\Controller\IndexController::hello');
```

Pada saat ini, `Observable` bertindak seperti middleware. Setelah mendapatkan observable sequence dari objek request, ia akan terus melewatkan objek request ke `Controller` yang sebenarnya.

### IpcSubject

Komunikasi antar-process Swoole juga berbasis event. Selain empat [Subject](https://mcxiaoke.gitbooks.io/rxdocs/content/Subject.html) yang disediakan RxPHP, komponen ini juga menyediakan versi Subject lintas-process yang bisa dipakai untuk berbagi informasi antar process.

Misalnya, kita perlu membuat chat room berbasis WebSocket dengan kebutuhan berikut:

1. Pesan chat room perlu dibagikan antar `Worker processes`.

2. 5 pesan terbaru ditampilkan ketika pengguna login untuk pertama kalinya.

Kita implementasikan ini melalui versi lintas-process dari `ReplaySubject`.

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
        // Parameter pertama adalah objek Subject RxPHP asli.
        // Parameter kedua adalah metode broadcast, defaultnya adalah broadcast seluruh process
        // Parameter ketiga adalah ID channel. Setiap channel hanya dapat menerima pesan dari channel yang sama.
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

Untuk kemudahan, komponen ini menggunakan `IpcSubject` untuk membungkus "message bus" `MessageBusInterface`. Anda tinggal menginjeksi `MessageBusInterface` untuk mengirim dan menerima informasi yang dibagikan di semua process (termasuk custom process). Fitur seperti configuration center bisa dengan mudah diimplementasikan lewat ini.

```php
<?php
$bus = make(Hyperf\ReactiveX\MessageBusInterface::class);
// Broadcast informasi ke semua process
$bus->onNext('Hello Hyperf');
// Berlangganan informasi
$bus->subscribe(function($message){
    echo $message;
});
```

> Karena ReactiveX perlu event loop, pastikan untuk memanggil API ReactiveX hanya setelah Swoole Server dijalankan.

## Referensi

* [Rx Dokumentasi Bahasa Mandarin](https://mcxiaoke.gitbooks.io/rxdocs/content/)
* [Rx Dokumentasi Bahasa Inggris](http://reactivex.io/)
* [RxPHP Repository](https://github.com/ReactiveX/RxPHP)
