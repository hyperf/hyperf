# Coroutine

## Konsep

Hyperf berjalan di atas coroutine `Swoole 5` dan `Swow`, inilah salah satu faktor utama yang membuat performa Hyperf bisa sangat tinggi.

### Mode Operasi PHP-FPM

Sebelum membahas coroutine, mari kita lihat dulu cara kerja arsitektur tradisional `PHP-FPM`. `PHP-FPM` adalah manajer `FastCGI` multi-proses yang digunakan oleh sebagian besar aplikasi `PHP`. Misalnya kita pakai `Nginx` sebagai Web server (sama juga untuk `Apache`): semua request dari klien masuk ke `Nginx`, lalu diteruskan ke `PHP-FPM` melalui protokol `FastCGI`. `Worker processes` di `PHP-FPM` kemudian mengambil request tersebut secara bergantian untuk diproses, yang artinya menunggu parsing script `PHP` dan menunggu hasil bisnis sampai selesai, lalu proses anak ditutup. Seluruh proses ini bersifat blocking dan waiting. Artinya, jumlah request yang bisa ditangani `PHP-FPM` sangat tergantung pada jumlah `Worker processes` yang tersedia. Anggap saja `PHP-FPM` punya `200` `Worker processes` dan satu request butuh `1` detik, secara teoritis server hanya bisa menangani maksimal `200` request, dengan `QPS` `200/s`. Di skenario high concurrency, performa seperti ini jelas kurang. Memang `Nginx` bisa jadi load balancer dengan beberapa server `PHP-FPM`, tapi karena model kerja `PHP-FPM` yang blocking, satu request tetap memakan setidaknya satu koneksi `MySQL`. Dalam kondisi high concurrency di banyak node, ini menghasilkan koneksi `MySQL` dalam jumlah besar, padahal default maksimal koneksi `MySQL` hanya `100`. Bisa saja diubah, tapi model seperti ini jelas tidak ideal untuk skenario high concurrency.

### Sistem Non-blocking Asinkron

Di skenario high concurrency, model asynchronous non-blocking punya keunggulan yang jelas. Keuntungan utamanya: `Worker process` tidak lagi menunggu secara sync satu per satu request, tapi bisa menangani banyak request sekaligus tanpa `I/O` waiting, hasilnya kemampuan concurrency jadi sangat tinggi. Kelemahan utamanya, seperti yang mungkin sudah Anda duga, adalah callback yang bertumpuk-tumpuk. Semua logika bisnis harus ditulis di dalam fungsi callback. Kalau bisnisnya butuh beberapa kali `I/O`, jadilah callback bertingkat-tingkat seperti ini, contoh pseudo-code gaya async callback di `Swoole 1.x`:

```php
$db = new swoole_mysql();
$config = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'test',
    'password' => 'test',
    'database' => 'test',
);

$db->connect($config, function ($db, $r) {
    // Query sebuah record dari tabel users
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r !== false) {
            // Update sebuah record setelah query berhasil
            $updateSql = 'update users set name="new name" where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                $rows = $db->affected_rows;
                if ($r === true) {
                    return $this->response->end('Update berhasil');
                }
            });
        }
        $db->close();
    });
});
```

> Perhatikan bahwa modul asinkron seperti `MySQL` telah dihapus di [4.3.0](https://wiki.swoole.com/#/version/bc?id=430) dan dipindahkan ke [swoole_async](https://github.com/swoole/ext-async).

Seperti yang terlihat dari cuplikan kode di atas, hampir setiap operasi membutuhkan sebuah fungsi callback. Dalam skenario bisnis yang kompleks, berlapis-lapisnya callback dan struktur kode pasti akan membuat Anda menderita. Sebenarnya, tidak sulit untuk melihat bahwa cara penulisan ini agak mirip dengan penulisan metode asinkron di `JavaScript`, dan `JavaScript` telah menyediakan banyak solusi untuk ini (tentu saja, solusinya berasal dari bahasa pemrograman lain), seperti `Promise`, `yield + generator`, `async/await`. `Promise` adalah cara untuk membungkus callback, sementara `yield + generator` dan `async/await` memerlukan penambahan marker sintaks kode yang eksplisit pada kode. Dibandingkan dengan fungsi callback, ini semua adalah solusi yang sangat baik, tetapi Anda perlu menghabiskan waktu ekstra untuk memahami mekanisme implementasi dan sintaksnya.
Coroutine `Swoole` juga merupakan solusi untuk callback asinkron. Dalam bahasa `PHP`, baik coroutine `Swoole` maupun `yield + generator` termasuk dalam solusi coroutine. Solusi coroutine memungkinkan kode ditulis dengan cara yang hampir sinkron namun tetap asinkron. Perbedaan eksplisitnya adalah di bawah mekanisme coroutine `yield + generator`, setiap kode pemanggilan operasi `I/O` perlu ditambahkan sintaks `yield` di depannya untuk mengimplementasikan coroutine switching, dan setiap lapisan pemanggilan perlu ditambahkan, jika tidak, error yang tidak terduga akan terjadi. Solusi coroutine `Swoole` jauh lebih pintar dari ini. Ketika menemukan `I/O`, lapisan bawah secara otomatis melakukan coroutine switching implisit, tanpa menambahkan sintaks tambahan apa pun, tanpa menambahkan `yield` sebelum kode, dan proses coroutine switching-nya tidak terlihat, yang sangat mengurangi beban mental dalam memelihara sistem asinkron.

### Apa itu Coroutine?

Kita sudah tahu bahwa coroutine dapat menyelesaikan masalah pengembangan sistem non-blocking asinkron dengan baik, tetapi apa sebenarnya coroutine itu? Menurut definisi, **coroutine adalah thread ringan yang dijadwalkan dan dikelola oleh kode pengguna, bukan oleh kernel sistem operasi, yang berarti ia berjalan di user space**. Ini dapat langsung dipahami sebagai implementasi thread yang tidak standar, tetapi kapan harus beralih diimplementasikan oleh pengguna, bukan ditentukan oleh sistem operasi yang mengalokasikan waktu `CPU`. Secara spesifik, setiap `Worker process` di `Swoole` memiliki sebuah coroutine scheduler untuk menjadwalkan coroutine. Waktu yang tepat untuk coroutine switching adalah ketika operasi `I/O` ditemukan atau ketika kode secara eksplisit melakukan switching. Coroutine berjalan dengan cara single-threaded di dalam proses, yang berarti hanya satu coroutine yang akan berjalan dalam sebuah proses pada waktu yang sama, dan waktu switching-nya jelas, sehingga tidak perlu berurusan dengan berbagai masalah synchronization lock seperti pada pemrograman multi-threaded.

Kode yang berjalan dalam satu coroutine tetaplah serial. Untuk memahaminya dalam konteks layanan HTTP coroutine, setiap request adalah sebuah coroutine. Sebagai contoh, misalkan `Coroutine A` dibuat untuk `Request A`, dan `Coroutine B` dibuat untuk `Request B`. Ketika memproses `Coroutine A`, kode mencapai statement query `MySQL`. Pada saat ini, `Coroutine A` akan memicu coroutine switch, dan `Coroutine A` akan terus menunggu perangkat `I/O` mengembalikan hasil. Pada saat ini, akan beralih ke `Coroutine B`, dan mulai memproses logika `Coroutine B`. Ketika operasi `I/O` lain ditemukan, itu memicu coroutine switch, dan kemudian beralih kembali untuk melanjutkan eksekusi dari tempat `Coroutine A` berhenti. Proses ini berulang: ketika operasi `I/O` ditemukan, ia beralih ke coroutine lain untuk melanjutkan eksekusi, bukan memblokir dan menunggu.

Sebuah masalah dapat ditemukan di sini: **Operasi query `MySQL` di `Coroutine A` harus berupa operasi non-blocking asinkron, jika tidak, blocking akan menyebabkan coroutine scheduler tidak dapat beralih ke coroutine lain untuk melanjutkan eksekusi**. Ini juga merupakan salah satu masalah yang harus dihindari dalam pemrograman coroutine.

### Apa Perbedaan antara Coroutine dan Thread Biasa?

Sering dikatakan bahwa coroutine adalah thread ringan. Baik coroutine maupun thread cocok untuk skenario multi-tasking. Dari perspektif ini, coroutine dan thread sangat mirip, keduanya memiliki context sendiri dan dapat berbagi variabel global. Tetapi perbedaannya adalah banyak thread dapat berada dalam keadaan berjalan pada saat yang sama, tetapi untuk coroutine `Swoole`, hanya satu yang dapat berada dalam keadaan berjalan, dan coroutine lainnya akan berada dalam keadaan dijeda. Selain itu, thread biasa bersifat preemptive, dan sistem operasi yang memutuskan thread mana yang mendapatkan resources, sementara coroutine bersifat kooperatif, dan kekuatan eksekusi dialokasikan oleh user space itu sendiri.

## Hal yang Perlu Diperhatikan dalam Pemrograman Coroutine

### Tidak Boleh Ada Blocking Code

Blocking code dalam coroutine akan menyebabkan coroutine scheduler tidak dapat beralih ke coroutine lain untuk melanjutkan eksekusi kode, jadi kita sama sekali tidak boleh memiliki blocking code dalam coroutine. Misalkan kita memulai `4` `Workers` untuk menangani request `HTTP` (biasanya jumlah `Workers` yang dimulai sama dengan jumlah core `CPU` atau `2` kali lipatnya), jika ada blocking dalam kode, dan kita secara teoritis berasumsi bahwa setiap request akan memblokir selama `1` detik, maka `QPS` sistem juga akan menurun menjadi `4/s`, yang tidak diragukan lagi merupakan degradasi menjadi situasi yang mirip dengan `PHP-FPM`, jadi kita sama sekali tidak boleh memiliki blocking code dalam coroutine.

Lalu apa sebenarnya blocking code itu? Kita dapat mengasumsikan bahwa sebagian besar client `MySQL`, `Redis`, `Memcache`, `MongoDB`, `HTTP`, `Socket`, dll., yang Anda kenal dan tidak disediakan oleh `Swoole` sebagai fungsi asinkron, serta operasi file, `sleep/usleep`, dll., adalah fungsi blocking. Ini mencakup hampir semua operasi sehari-hari. Jadi bagaimana cara mengatasinya? `Swoole` menyediakan client coroutine untuk `MySQL`, `PostgreSQL`, `Redis`, `HTTP`, `Socket` yang dapat digunakan. Pada saat yang sama, setelah `Swoole 4.1`, metode coroutine satu-klik `\Swoole\Runtime::enableCoroutine()` disediakan. Anda hanya perlu menjalankan baris kode ini sebelum menggunakan coroutine. `Swoole` akan mengubah semua operasi socket yang menggunakan `php_stream` menjadi `I/O` asinkron yang dijadwalkan oleh coroutine. Dapat dipahami bahwa kecuali `curl`, sebagian besar operasi native dapat diterapkan. Untuk informasi lebih lanjut tentang bagian ini, silakan merujuk ke [Dokumentasi Swoole](https://wiki.swoole.com/#/runtime).

Di `Hyperf`, kami telah menangani semua ini untuk Anda. Anda hanya perlu memperhatikan blocking code yang masih belum bisa dibuat coroutine-friendly oleh `\Swoole\Runtime::enableCoroutine()`.

### Tidak Bisa Menyimpan State melalui Global Variables

Dalam aplikasi persisten `Swoole`, global variable dalam sebuah `Worker` dibagikan di dalam `Worker` tersebut. Dari pengenalan coroutine, kita tahu bahwa akan ada beberapa coroutine dalam `Worker` yang sama dan coroutine switching akan terjadi. Ini berarti bahwa sebuah `Worker` akan memproses kode untuk beberapa coroutine (atau dapat dipahami sebagai request) pada waktu yang sama dalam siklus waktu, yang berarti bahwa jika global variable digunakan untuk menyimpan state, mereka mungkin digunakan oleh beberapa coroutine, yaitu data mungkin tercampur antara request yang berbeda. Global variable di sini merujuk pada variabel yang dimulai dengan `$_`, seperti `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`, `global` variables, dan `static` properties.

Jadi apa yang harus kita lakukan ketika kita perlu menggunakan fitur-fitur ini?

Untuk global variable, mereka semua dihasilkan mengikuti sebuah `Request`. `Request/Response` dari `Hyperf` ditangani oleh [hyperf/http-message](https://github.com/hyperf/http-message) dengan mengimplementasikan [PSR-7](https://www.php-fig.org/psr/psr-7/), sehingga semua global variable bisa mendapatkan nilai yang relevan dalam objek `Request`;

Untuk `global` variables dan `static` variables, dalam mode `PHP-FPM`, mereka pada dasarnya hidup dalam satu lifecycle request, sementara di `Hyperf`, karena ini adalah aplikasi `CLI`, ada dua lifecycle panjang: `global cycle` dan `request cycle (coroutine cycle)`.
- Global cycle: Kita hanya perlu membuat static variable untuk pemanggilan global. Static variable berarti setelah layanan dimulai, coroutine dan logika kode apa pun berbagi data dalam static variable ini, yang berarti data yang disimpan tidak dapat dilayani secara khusus untuk request tertentu atau coroutine tertentu;
- Coroutine cycle: Karena `Hyperf` secara otomatis membuat sebuah coroutine untuk setiap request untuk memprosesnya, coroutine cycle di sini juga dapat dipahami sebagai request cycle. Dalam sebuah coroutine, semua data state harus disimpan di kelas `Hyperf\Context\Context`. Anda dapat membaca dan menyimpan data dengan struktur apa pun melalui metode `get` dan `set` dari kelas ini. Data yang dibaca atau disimpan oleh kelas `Context (coroutine context)` ini saat mengeksekusi coroutine apa pun terbatas pada coroutine yang sesuai, dan data context terkait akan secara otomatis dihancurkan ketika coroutine berakhir.

### Batas Jumlah Maksimum Coroutine

Atur parameter `max_coroutine` melalui metode `set` pada `Swoole Server` untuk mengonfigurasi jumlah maksimum coroutine yang dapat ada dalam sebuah `Worker` process. Karena seiring bertambahnya jumlah coroutine yang diproses oleh sebuah `Worker` process, penggunaan memori yang sesuai juga akan meningkat. Untuk menghindari melebihi batas `memory_limit` `PHP`, harap atur nilai ini sesuai dengan hasil stress test dari bisnis aktual. Nilai default `Swoole` adalah `100000` (nilai default adalah `3000` ketika versi `Swoole` kurang dari `v4.4.0-beta`). Dalam proyek `hyperf-skeleton`, ini diatur ke `100000` secara default.

## Menggunakan Coroutine

### Membuat Coroutine

Cukup buat coroutine melalui fungsi `Hyperf\Coroutine\co(callable $callable)`, `Hyperf\Coroutine\go(callable $callable)`, atau `Hyperf\Coroutine\Coroutine::create(callable $callable)`. Di dalam coroutine, Anda dapat menggunakan metode dan client yang terkait dengan coroutine.

### Mengecek Apakah Lingkungan Saat Ini adalah Lingkungan Coroutine

Dalam beberapa kasus, kita ingin mengecek apakah kita sedang berjalan di lingkungan coroutine. Untuk beberapa kode yang kompatibel dengan lingkungan coroutine dan non-coroutine, ini dapat dijadikan sebagai dasar pengecekan. Kita bisa mendapatkan hasilnya melalui metode `Hyperf\Coroutine\Coroutine::inCoroutine(): bool`.

### Mendapatkan ID Coroutine Saat Ini

Dalam beberapa kasus, kita perlu melakukan beberapa logika berdasarkan `Coroutine ID`, seperti logika untuk `Coroutine Context`. Anda bisa mendapatkan `Coroutine ID` saat ini melalui `Hyperf\Coroutine\Coroutine::id(): int`. Jika tidak berada di lingkungan coroutine, `-1` akan dikembalikan.

### Channel

Mirip dengan `chan` di bahasa `Go`, `Channel` menyediakan dukungan untuk mode coroutine multi-producer dan multi-consumer. Lapisan bawah secara otomatis mengimplementasikan coroutine switching dan scheduling. `Channel` mirip dengan array `PHP`, hanya menggunakan memori, tanpa permintaan resource tambahan lainnya. Semua operasi adalah operasi memori, tanpa konsumsi `I/O`. Penggunaannya mirip dengan antrian `SplQueue`.
`Channel` terutama digunakan untuk komunikasi antar coroutine. Ketika kita ingin mengembalikan beberapa data dari satu coroutine ke coroutine lainnya, kita dapat melewatinya melalui `Channel`.

Method utama:
- `Channel->push`: Ketika coroutine lain sedang menunggu untuk `pop` data dalam antrian, secara otomatis membangunkan consumer coroutine secara berurutan. Ketika antrian penuh, secara otomatis `yield` untuk menyerahkan kontrol, dan menunggu coroutine lain untuk mengonsumsi data.
- `Channel->pop`: Ketika antrian kosong, secara otomatis `yield`, dan menunggu coroutine lain untuk memproduksi data. Setelah mengonsumsi data, antrian dapat menulis data baru, dan secara otomatis membangunkan producer coroutine secara berurutan.

Berikut adalah contoh sederhana komunikasi antar coroutine:

```php
<?php
co(function () {
    $channel = new \Swoole\Coroutine\Channel();
    co(function () use ($channel) {
        $channel->push('data');
    });
    $data = $channel->pop();
});
```

### Fitur Defer

Ketika kita ingin menjalankan beberapa kode saat sebuah coroutine berakhir, kita dapat menggunakan fungsi `defer(callable $callable)` atau `Hyperf\Coroutine::defer(callable $callable)` untuk menyimpan sebuah fungsi dalam bentuk `stack`. Fungsi-fungsi dalam `stack` akan dieksekusi satu per satu dalam proses `first-in-last-out` ketika coroutine saat ini berakhir.

### Fitur WaitGroup

`WaitGroup` adalah fitur yang berasal dari `Channel`. Jika Anda pernah mengenal bahasa `Go`, kita semua tahu fitur `WaitGroup`. Di `Hyperf`, tujuan `WaitGroup` adalah untuk memungkinkan main coroutine memblokir dan menunggu sampai semua sub-coroutine terkait menyelesaikan tugas mereka sebelum melanjutkan eksekusi. Pemblokiran dan penungguan yang disebutkan di sini hanya berlaku untuk main coroutine (yaitu coroutine saat ini) dan tidak akan memblokir proses saat ini.
Kita gunakan sepotong kode untuk mendemonstrasikan fitur ini:

```php
<?php
$wg = new \Hyperf\Coroutine\WaitGroup();
// Increment counter sebanyak dua
$wg->add(2);
// Membuat Coroutine A
co(function () use ($wg) {
    // beberapa kode
    // Decrement counter sebanyak satu
    $wg->done();
});
// Membuat Coroutine B
co(function () use ($wg) {
    // beberapa kode
    // Decrement counter sebanyak satu
    $wg->done();
});
// Tunggu Coroutine A dan Coroutine B selesai berjalan
$wg->wait();
```

> Perhatikan bahwa `WaitGroup` sendiri juga perlu digunakan dalam sebuah coroutine.

### Fitur Parallel

Fitur `Parallel` adalah metode penggunaan yang lebih nyaman yang diabstraksi oleh Hyperf berdasarkan fitur `WaitGroup`. Mari kita demonstasikan dengan sepotong kode.

```php
<?php
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

$parallel = new Parallel();
$parallel->add(function () {
    sleep(1);
    return Coroutine::id();
});
$parallel->add(function () {
    sleep(1);
    return Coroutine::id();
});

try{
    // $results akan berupa [1, 2]
    $results = $parallel->wait();
} catch(ParallelExecutionException $e){
    // $e->getResults() untuk mendapatkan nilai return dalam coroutine.
    // $e->getThrowables() untuk mendapatkan exception yang terjadi dalam coroutine.
}
```

> Perhatikan bahwa exception `Hyperf\Coroutine\Exception\ParallelExecutionException` hanya akan dilempar di versi 1.1.6 dan versi yang lebih baru.

Melalui kode di atas, kita dapat melihat bahwa hanya butuh `1` detik untuk mendapatkan `ID` dari dua coroutine yang berbeda. Ketika memanggil `add(callable $callable)`, kelas `Parallel` akan secara otomatis membuat sebuah coroutine untuknya dan menambahkannya ke penjadwalan `WaitGroup`.
Tidak hanya itu, kita juga dapat lebih menyederhanakan kode di atas melalui fungsi `parallel(array $callables)` untuk mencapai tujuan yang sama. Berikut adalah kode yang disederhanakan.

```php
<?php
use Hyperf\Coroutine\Coroutine;

// Anda juga dapat menambahkan key ke parameter array yang dikirim untuk memudahkan membedakan sub-coroutine, dan hasil yang dikembalikan juga akan mengembalikan hasil yang sesuai berdasarkan key tersebut.
$result = parallel([
    function () {
        sleep(1);
        return Coroutine::id();
    },
    function () {
        sleep(1);
        return Coroutine::id();
    }
]);
```

> Perhatikan bahwa `Parallel` sendiri juga perlu digunakan dalam sebuah coroutine.

#### Membatasi Jumlah Maksimum Coroutine yang Berjalan Secara Bersamaan di Parallel

Ketika ada banyak tugas yang ditambahkan ke `Parallel`, dengan asumsi semuanya adalah task request, mengirim semua request sekaligus sangat mungkin menyebabkan layanan lawan tidak dapat menanganinya karena menerima sejumlah besar request dalam satu waktu, yang membawa risiko downtime. Oleh karena itu, perlu untuk melindungi pihak lawan dengan tepat, tetapi kami juga berharap dapat mempercepat request ini melalui mekanisme `Parallel`. Dalam kasus ini, Anda dapat mengatur jumlah maksimum coroutine yang berjalan dengan melewatkan parameter pertama saat menginisiasi objek `Parallel`. Sebagai contoh, jika kita ingin mengatur jumlah maksimum coroutine menjadi `5`, itu berarti paling banyak `5` coroutine akan berjalan di `Parallel` pada waktu yang sama. Hanya ketika sebuah coroutine dalam `5` tersebut selesai, coroutine berikutnya akan mulai berjalan sampai semua coroutine menyelesaikan tugas mereka. Contoh kodenya adalah sebagai berikut:

```php
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

$parallel = new Parallel(5);
for ($i = 0; $i < 20; $i++) {
    $parallel->add(function () {
        sleep(1);
        return Coroutine::id();
    });
}

try{
   $results = $parallel->wait();
} catch(ParallelExecutionException $e){
    // $e->getResults() untuk mendapatkan nilai return dalam coroutine.
    // $e->getThrowables() untuk mendapatkan exception yang terjadi dalam coroutine.
}
```

### Kontrol Eksekusi Coroutine Konkuren

`Hyperf\Coroutine\Concurrent` diimplementasikan berdasarkan `Swoole\Coroutine\Channel` dan digunakan untuk mengontrol jumlah maksimum coroutine yang berjalan secara bersamaan dalam sebuah blok kode.

Dalam contoh berikut, ketika `10` sub-coroutine dijalankan pada saat yang sama, ia akan memblokir dalam loop, tetapi hanya akan memblokir coroutine saat ini sampai ada posisi yang dirilis, dan kemudian loop akan terus mengeksekusi sub-coroutine berikutnya.

```php
<?php

use Hyperf\Coroutine\Concurrent;

$concurrent = new Concurrent(10);

for ($i = 0; $i < 15; ++$i) {
    $concurrent->create(function () {
        // Lakukan sesuatu...
    });
}
```

### Coroutine Context

Karena memori dibagikan antar coroutine dalam proses yang sama, tetapi eksekusi/switching coroutine tidak berurutan, yang berarti sulit bagi kita untuk mengontrol coroutine mana yang sedang berjalan **(sebenarnya mungkin, tetapi biasanya tidak ada yang melakukannya)**, jadi kita harus dapat mengganti context yang sesuai pada saat yang sama ketika coroutine switch terjadi.
Di `Hyperf`, mengimplementasikan manajemen coroutine context sangat sederhana. Berdasarkan metode statis `set(string $id, $value)`, `get(string $id, $default = null)`, `has(string $id)`, dan `override(string $id, \Closure $closure)` dari kelas `Hyperf\Context\Context`, Anda dapat menyelesaikan manajemen data context. Nilai yang diatur dan diperoleh melalui metode ini terbatas pada coroutine saat ini. Ketika coroutine berakhir, context yang sesuai juga akan secara otomatis dilepaskan. Tidak perlu mengelolanya secara manual, dan tidak perlu khawatir tentang risiko memory leak.

#### Hyperf\Context\Context::set()

Simpan sebuah nilai dalam context dari coroutine saat ini dengan memanggil metode `set(string $id, $value)`, sebagai berikut:

```php
<?php
use Hyperf\Context\Context;

// Simpan string bar ke dalam context coroutine saat ini dengan foo sebagai key
$foo = Context::set('foo', 'bar');
// Method set akan mengembalikan nilai tersebut sebagai nilai return dari method, jadi nilai $foo adalah bar
```

#### Hyperf\Context\Context::get()

Ambil sebuah nilai yang disimpan dengan `$id` sebagai `key` dari context coroutine saat ini dengan memanggil metode `get(string $id, $default = null)`. Jika tidak ada, kembalikan `$default`, sebagai berikut:

```php
<?php
use Hyperf\Context\Context;

// Ambil nilai dengan key foo dari context coroutine saat ini. Jika tidak ada, kembalikan string bar
$foo = Context::get('foo', 'bar');
```

#### Hyperf\Context\Context::has()

Tentukan apakah sebuah nilai yang disimpan dengan `$id` sebagai `key` ada dalam context coroutine saat ini dengan memanggil metode `has(string $id)`. Jika ada, kembalikan `true`, jika tidak, kembalikan `false`, sebagai berikut:

```php
<?php
use Hyperf\Context\Context;

// Tentukan apakah nilai dengan key foo ada dalam context coroutine saat ini
$foo = Context::has('foo');
```

#### Hyperf\Context\Context::override()

Ketika kita perlu melakukan beberapa pemrosesan context yang kompleks, seperti pertama-tama mengecek apakah sebuah `key` ada, jika ada, ambil `value` dan kemudian lakukan beberapa modifikasi pada `value`, lalu set `value` kembali ke container context, akan ada kondisi pengecekan yang relatif rumit pada saat ini. Anda bisa langsung memanggil metode `override` untuk mengimplementasikan logika ini, sebagai berikut:

```php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\Context\Context;

// Ambil objek $request dari coroutine context dan set Header dengan key foo, lalu simpan kembali ke coroutine context
$request = Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) {
    return $request->withAddedHeader('foo', 'bar');
});
```

### Swoole Runtime Hook Level

Framework menyediakan konstanta `SWOOLE_HOOK_FLAGS` dalam fungsi entry. Jika Anda perlu memodifikasi level `Runtime Hook` dari seluruh proyek, misalnya, jika Anda ingin mendukung `CURL coroutines` dan versi `Swoole` lebih awal dari `v4.5.4`, Anda dapat memodifikasi kode di sini, sebagai berikut.

```php
<?php
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL);
```

!> Jika versi Swoole >= `v4.5.4`, tidak perlu modifikasi.
