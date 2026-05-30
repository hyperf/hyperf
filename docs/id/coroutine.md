# Coroutine

## Concept

Hyperf dibangun di atas coroutine dari `Swoole 5`, yang merupakan salah
satu faktor besar mengapa Hyperf dapat memberikan performa tinggi.

### Mode Menjalankan PHP-FPM

Sebelum kita membahas apa yang terjadi di balik layar, mari kita bahas
terlebih dahulu mode operasi dari arsitektur tradisional `PHP-FPM`. `PHP-FPM`
adalah hypervisor `FastCGI` multi-process yang digunakan oleh sebagian besar
aplikasi PHP. Misalkan kita menggunakan `Nginx` untuk menyediakan layanan
`HTTP` (sama halnya jika menggunakan `Apache`). Semua request yang diinisiasi
oleh klien akan tiba di `Nginx` terlebih dahulu, kemudian `Nginx` meneruskan
request tersebut ke `PHP-FPM` untuk diproses melalui protokol `FastCGI`.
`Master Process` dari `PHP-FPM` akan mengalokasikan satu `Worker Process`
untuk setiap request. Pemrosesan ini berarti seluruh process akan terblokir
menunggu antara waktu parsing script `PHP` dan menunggu hasil dari bisnis
proses, kemudian mendaur ulang (recycle) child process tersebut. Artinya,
jumlah request yang dapat Anda tangani secara bersamaan bergantung pada jumlah
process `PHP-FPM` yang Anda miliki. Diasumsikan `PHP-FPM` memiliki `200`
`Worker Process`, dan sebuah request membutuhkan waktu `1` detik, maka secara
teoretis seluruh server hanya dapat menangani maksimal 200 request, dengan
`QPS` sebesar `200/s`. Dalam skenario high-concurrency, performa seperti ini
sering kali tidak cukup. Meskipun Anda dapat menggunakan `Nginx` sebagai load
balancing dengan beberapa server `PHP-FPM` untuk menyediakan layanan, namun
karena model blocking waiting dari `PHP-FPM`, satu request akan menempati
setidaknya satu koneksi `MySQL`. Hal ini akan membuat skenario multi-node
menghasilkan sangat banyak koneksi ke `MySQL`. Secara default, batas maksimal
koneksi `MySQL` adalah `100`. Meskipun Anda dapat mengubah nilai tersebut,
sangat jelas bahwa pola ini tidak dapat menangani skenario high-concurrency
dengan baik.

### Sistem Asynchronous Non-blocking

Dalam skenario high-concurrency, asynchronous non-blocking memiliki keunggulan
yang jelas. Keuntungan intuitifnya adalah bahwa `Worker Process` tidak lagi
terblokir secara sinkron (synchronously blocking) untuk menangani suatu
request, melainkan dapat menangani beberapa request sekaligus tanpa harus
menunggu `I/O`. Kemampuan konkurensinya sangat kuat, dan sejumlah besar
request dapat diinisiasi atau dipertahankan pada saat yang sama. Namun,
kekurangan paling intuitif yang mungkin Anda ketahui adalah callback hell;
logika bisnis harus diimplementasikan di dalam fungsi callback yang sesuai.
Jika logika bisnis memiliki beberapa request `I/O`, akan ada banyak lapisan
fungsi callback. Berikut adalah contoh fragmen pseudo-code pada `Swoole 1.x`.

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
    // Query a row of data from users table
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r === true) {
            $rows = $db->affected_rows;
            // Modify a row of data after the query is successful
            $updateSql = 'update users set name='new name' where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                if ($r === true) {
                    return $this->response->end('Update Successfully');
                }
            });
        }
        $db->close();
    });
});
```

Seperti yang dapat Anda lihat pada cuplikan kode di atas, hampir setiap
operasi membutuhkan fungsi callback. Struktur kode dan tingkatan callback
dalam skenario bisnis yang kompleks tentu akan menyulitkan Anda. Tidak sulit
untuk melihat bahwa pendekatan ini mirip dengan menulis metode asinkron pada
`JavaScript`. `JavaScript` menawarkan sejumlah solusi (yang tentu saja diadaptasi
dari bahasa pemrograman lain), seperti `Promise`, `yield + generator`, dan
`Async/Await`. Jika `Promise` adalah cara untuk membungkus callback, maka
`yield + generator` dan `Async/Await` perlu menambahkan beberapa penanda
sintaksis secara eksplisit pada kode. Ini adalah alternatif yang baik untuk
menghindari callback, tetapi Anda masih memerlukan waktu untuk memahami
implementasi dan sintaksisnya.

Coroutine Swoole juga merupakan solusi untuk callback asinkron. Di PHP, baik
coroutine Swoole maupun `yield + generator` adalah solusi coroutine yang
memungkinkan penulisan kode asinkron dengan gaya yang hampir sama dengan kode
sinkron. Perbedaan yang jelas adalah bahwa pada mekanisme coroutine
`yield + generator`, setiap operasi `I/O` harus didahului oleh sintaksis
`yield` untuk melakukan peralihan (switch) coroutine, dan setiap tingkat
pemanggilan juga harus didahului oleh sintaksis `yield`. Jika tidak, akan
terjadi kesalahan yang tidak terduga. Sebaliknya, solusi coroutine `Swoole`
jauh lebih elegan. `I/O` dialihkan secara implisit di tingkat bawah tanpa
perlu menambahkan sintaksis tambahan atau `yield` ke dalam kode, dan
peralihan coroutine ini terjadi secara senyap. Hal ini sangat mengurangi
beban mental dalam memelihara sistem asinkron.

### Apa itu Coroutine?

Kita telah mengetahui bahwa coroutine dapat menyelesaikan masalah pengembangan
sistem asynchronous non-blocking dengan sangat baik. Jadi, apa sebenarnya
coroutine itu? Berdasarkan definisinya, *coroutine adalah thread ringan yang
dijadwalkan dan dikelola oleh kode pengguna, bukan oleh kernel sistem operasi,
yaitu dalam user mode*. Ini dapat dipahami secara langsung sebagai implementasi
thread non-standar, tetapi pengguna yang melakukan peralihan, bukan sistem
operasi yang mengalokasikan waktu `CPU`. Secara khusus, setiap `Worker process`
dari `Swoole` memiliki scheduler untuk menjadwalkan coroutine, dan waktu
peralihan coroutine adalah ketika operasi `I/O` atau peralihan kode eksplisit
terjadi. Proses ini menjalankan coroutine sebagai single thread, yang berarti
hanya ada satu coroutine yang berjalan pada waktu yang sama dalam sebuah
process dan waktu peralihannya jelas. Oleh karena itu, tidak perlu berurusan
dengan masalah sinkronisasi lock seperti pada pemrograman multi-threaded.

Kode di dalam satu coroutine tetap berjalan secara serial. Dalam server
coroutine HTTP, setiap request dipahami sebagai sebuah coroutine. Sebagai
contoh, misalkan `coroutine A` dibuat untuk `request A` dan `coroutine B` dibuat
untuk `request B`. Ketika kode berjalan hingga melakukan query `MySQL` saat
memproses `coroutine A`, pada titik tersebut `coroutine A` akan memicu
peralihan coroutine. `coroutine A` akan terus menunggu perangkat `I/O`
mengembalikan hasil, kemudian beralih ke `coroutine B` untuk mulai memproses
logika `coroutine B`. Ketika menemui operasi `I/O` lain, peralihan coroutine
akan dipicu kembali, dan kemudian akan kembali melanjutkan dari titik di mana
`coroutine A` terhenti tadi, dan seterusnya. Ketika operasi `I/O` ditemui,
sistem akan beralih ke coroutine lain untuk melanjutkan proses, alih-alih
memblokir dan menunggu.

Masalahnya di sini adalah bahwa operasi query `MySQL` untuk *`coroutine A`
harus berupa operasi asynchronous non-blocking, jika tidak, scheduler
coroutine tidak akan dapat beralih ke coroutine lain untuk melanjutkan
eksekusi*. Masalah pemblokiran ini adalah salah satu hal yang harus dihindari
dalam pemrograman coroutine.

### Apa perbedaan antara coroutine dan thread biasa?

Seperti yang telah kita bahas bahwa coroutine adalah thread ringan.
Coroutine dan thread sama-sama cocok untuk skenario multitasking. Dari sudut
pandang ini, coroutine sangat mirip dengan thread dan memiliki context-nya
sendiri, yang dapat berbagi variabel global. Namun, perbedaannya adalah
beberapa thread dapat berjalan secara bersamaan, sedangkan pada coroutine
`Swoole` hanya ada satu coroutine yang aktif, dan coroutine lainnya akan
ditangguhkan (paused). Selain itu, thread biasa bersifat preemptive, di mana
thread yang mendapat sumber daya ditentukan oleh sistem operasi. Sedangkan
coroutine bersifat kolaboratif, di mana hak eksekusi dialokasikan oleh user
state.

## Hal-hal yang Perlu Diperhatikan dalam Pemrograman Coroutine

### Tidak Boleh Ada Kode yang Memblokir (Blocking Code)

Kode pemblokir (blocking code) di dalam coroutine akan menyebabkan scheduler
coroutine tidak dapat beralih ke coroutine lain untuk melanjutkan eksekusi
kode. Oleh karena itu, kita harus mencegah adanya blocking code di dalam
coroutine. Misalkan kita telah memulai `4 Worker` untuk menangani request `HTTP`
(biasanya jumlah `Worker` yang dimulai sama dengan jumlah core `CPU` atau `2`
kali jumlah core `CPU`). Jika terdapat blocking code di dalam coroutine, secara
teoretis, jika setiap request memblokir selama `1` detik, maka `QPS` aplikasi
juga akan menurun menjadi `4/s`. Hal ini tentu saja menurunkan performa hingga
serupa dengan `PHP-FPM`. Oleh karena itu, kita tidak boleh membiarkan adanya
blocking code di dalam coroutine.

Jadi, apa saja yang termasuk blocking code? Secara sederhana, sebagian besar
fungsi yang disediakan oleh selain Swoole seperti operasi `MySQL`, `Redis`,
`Memcache`, `MongoDB`, `HTTP`, `Socket`, operasi file, `sleep/usleep`, dll. adalah
blocking code, yang mencakup hampir semua operasi sehari-hari. Lalu bagaimana
cara menyelesaikannya? `Swoole` menyediakan client coroutine untuk MySQL,
`PostgreSQL`, `Redis`, `HTTP`, dan `Socket`. Selain itu, setelah `Swoole 4.1`,
Swoole menyediakan fungsi `\Swoole\Runtime::enableCoroutine()` untuk mengubah
sebagian besar blocking code menjadi ter-coroutine secara otomatis. Cukup jalankan
`\Swoole\Runtime::enableCoroutine()` sebelum membuat coroutine, maka `Swoole`
akan mengubah semua socket yang menggunakan php_stream untuk penjadwalan
coroutine. Ini berarti sebagian besar operasi umum akan ter-coroutine, kecuali
`curl`. Informasi lebih rinci dapat ditemukan di bagian [Dokumentasi Swoole](https://wiki.swoole.com/#/runtime) ini.

Di dalam `Hyperf`, kami telah menangani hal ini untuk Anda. Anda hanya perlu
memperhatikan blocking code yang masih tidak dapat di-coroutine-kan secara
otomatis oleh `\Swoole\Runtime::enableCoroutine()`.

### Tidak Boleh Menyimpan State Melalui Variabel Global

Pada aplikasi persisten `Swoole`, variabel global di dalam `Worker` dibagikan di
dalam `Worker` tersebut. Dari penjelasan mengenai coroutine, kita tahu bahwa
akan ada beberapa coroutine yang berada di dalam `Worker` yang sama. Peralihan
coroutine berarti bahwa satu `Worker` akan memproses beberapa coroutine (atau
dapat langsung dipahami sebagai request) dalam satu rentang waktu. Ini berarti
jika Anda menggunakan variabel global untuk menyimpan state, data state tersebut
mungkin akan digunakan oleh beberapa coroutine secara bergantian, sehingga data
dapat menjadi kacau di antara request atau coroutine yang berbeda. Variabel
global di sini merujuk pada variabel yang diawali dengan `$_` seperti
`$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`, variabel `global`, serta
properti atau variabel `static`.

Lalu apa yang harus kita lakukan ketika kita perlu menggunakan fitur-fitur
tersebut?

Untuk variabel global, data tersebut dihasilkan oleh sebuah `Request`. Request
dan Response di Hyperf dibuat oleh [hyperf/http-message](https://github.com/hyperf/http-message)
dengan mengimplementasikan [PSR-7](https://www.php-fig.org/psr/psr-7/). Semua
variabel global dapat ditemukan di dalam objek Request.

Untuk variabel `global` dan variabel `static`, pada mode `PHP-FPM`, esensinya
adalah untuk bertahan hidup dalam satu siklus hidup request. Sedangkan pada
`Hyperf`, karena merupakan aplikasi `CLI`, akan ada dua siklus hidup yang panjang,
yaitu `global cycle` dan `request cycle (coroutine cycle)`.

- Global cycle: kita hanya perlu membuat variabel statis untuk panggilan global.
  Variabel statis berarti semua coroutine dan logika kode berbagi data yang sama
  dalam variabel statis ini setelah layanan dimulai. Ini berarti data yang
  disimpan tidak dapat dikhususkan untuk request tertentu atau coroutine tertentu.
- Coroutine cycle: karena `Hyperf` akan secara otomatis membuat coroutine untuk
  memproses setiap request, maka siklus coroutine di sini juga dapat dipahami
  sebagai siklus request. Di dalam coroutine, semua data state harus disimpan
  di dalam kelas `Hyperf\Context\Context`. Data dengan struktur apa pun dibaca dan
  disimpan menggunakan metode `get` dan `set` dari kelas tersebut. Proses Get
  atau Set data apa pun di dalam `Context` (coroutine context) dibatasi hanya pada
  coroutine tempat fungsi get atau set tersebut dieksekusi, dan data context yang
  relevan akan otomatis dihancurkan ketika coroutine berakhir.

### Jumlah Maksimum Coroutine

Atur parameter `max_coroutine` pada `Swoole Server` melalui metode `set` untuk
mengonfigurasi jumlah maksimum coroutine yang dapat ada di dalam satu proses
`Worker`. Karena peningkatan jumlah coroutine yang diproses oleh proses `Worker`
akan meningkatkan penggunaan memori yang sesuai, untuk menghindari terlampauinya
batas `memory_limit` pada `PHP`, atur nilai ini berdasarkan hasil pengujian
tekanan (stress test) bisnis yang sebenarnya. Nilai default untuk `Swoole`
adalah `3000`, yang diatur menjadi `100000` secara default di proyek
`hyperf-skeleton`.

## Penggunaan Coroutine

### Membuat Coroutine

Gunakan fungsi `Hyperf\Coroutine\co(callable $callable)` atau
`Hyperf\Coroutine\go(callable $callable)` atau metode
`Hyperf\Coroutine\Coroutine::create(callable $callable)` untuk membuat coroutine
dengan mudah. Metode dan client yang berkaitan dengan coroutine dapat digunakan
di dalam coroutine tersebut.

### Apakah Sedang Berjalan di Lingkungan Coroutine?

Dalam beberapa kasus, kita ingin menentukan apakah saat ini sedang berjalan di
lingkungan coroutine. Untuk beberapa kode yang kompatibel dengan lingkungan
coroutine maupun non-coroutine, ini akan digunakan sebagai basis penilaian. Kita
dapat menggunakan metode `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` untuk
mendapatkan hasilnya.

### Mendapatkan ID Coroutine

Dalam beberapa kasus, kita perlu melakukan beberapa logika berdasarkan
`coroutine ID`, seperti `coroutine context`. Anda dapat memperoleh ID coroutine
saat ini menggunakan `Hyperf\Coroutine\Coroutine::id(): int`. Jika tidak sedang
berada di lingkungan coroutine, metode ini akan mengembalikan `-1`.

### Channel

Mirip dengan `chan` pada bahasa Go, `Channel` menyediakan dukungan untuk mode
coroutine multi-producer dan multi-consumer. Lapisan bawah secara otomatis
mengimplementasikan peralihan dan penjadwalan coroutine. `Channel` mirip dengan
array PHP; ia hanya memakan memori dan tidak ada sumber daya tambahan lain yang
diminta. Semua operasinya adalah operasi memori, tanpa `I/O`, dan penggunaannya
mirip dengan antrean `SplQueue`.

`Channel` terutama digunakan untuk komunikasi antar-coroutine. Ketika kita
ingin mengembalikan beberapa data dari satu coroutine ke coroutine lainnya, kita
dapat meneruskannya melalui `Channel`.

Metode utama:
- `Channel->push` : Ketika ada coroutine lain di dalam antrean yang menunggu
  data `pop`, coroutine konsumen secara otomatis dipanggil secara berurutan.
  Secara otomatis `yield` melepaskan hak kontrol ketika antrean penuh, menunggu
  coroutine lain untuk mengonsumsi data.
- `Channel->pop` : Secara otomatis `yield` ketika antrean kosong, menunggu
  coroutine lain memproduksi data. Setelah data dikonsumsi, antrean dapat
  menerima data baru yang di-push ke dalamnya dan secara otomatis membangunkan
  coroutine produsen secara berurutan.

Berikut adalah contoh sederhana komunikasi antar-coroutine:

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

### Defer

Ketika kita ingin menjalankan beberapa kode di akhir coroutine, kita dapat
menggunakan fungsi `defer(callable $callable)` atau
`Hyperf\Coroutine::defer(callable $callable)` untuk memasukkan fungsi dalam
bentuk `stack`. Setelah disimpan, fungsi-fungsi di dalam `stack` tersebut akan
dieksekusi satu per satu pada akhir coroutine saat ini, dengan urutan eksekusi
LIFO (Last in, First out).

### WaitGroup

`WaitGroup` adalah fitur yang diturunkan dari `Channel`. Jika Anda mengetahui
tentang bahasa `Go`, Anda akan familier dengan fitur `WaitGroup`. Di dalam
`Hyperf`, tujuan dari `WaitGroup` adalah untuk memblokir coroutine utama,
menunggu hingga semua child coroutine yang relevan selesai menyelesaikan tugas,
dan kemudian melanjutkan jalannya program. Pemblokiran tunggu yang dimaksud di
sini hanya berlaku untuk coroutine utama (yaitu coroutine saat ini) dan tidak
memblokir proses saat ini.
Kami mendemonstrasikan fitur ini dengan sepotong kode:

```php
<?php
$wg = new \Hyperf\Coroutine\WaitGroup();
// Counter increase 2
$wg->add(2);
// Create coroutine A
co(function () use ($wg) {
    // some code
    // Counter decrease 1
    $wg->done();
});
// Create coroutine B
co(function () use ($wg) {
    // some code
    // Counter decrease 1
    $wg->done();
});
// Wait for coroutine A and coroutine B finished
$wg->wait();
```

> Perhatikan bahwa `WaitGroup` sendiri juga perlu digunakan di dalam coroutine.

### Parallel

Fitur `Parallel` adalah sebuah abstraksi berbasis fitur `WaitGroup` yang
disediakan oleh Hyperf, memberikan cara penggunaan yang lebih nyaman dibandingkan
dengan `WaitGroup`. Mari kita demonstrasikan dengan sepotong kode:

```php
<?php
$parallel = new \Hyperf\Coroutine\Parallel();
$parallel->add(function () {
    \Hyperf\Coroutine\Coroutine::sleep(1);
    return \Hyperf\Coroutine\Coroutine::id();
});
$parallel->add(function () {
    \Hyperf\Coroutine\Coroutine::sleep(1);
    return \Hyperf\Coroutine\Coroutine::id();
});
// $result is [1, 2]
$result = $parallel->wait();
```

Dari kode di atas, kita dapat melihat bahwa hanya membutuhkan waktu 1 detik
untuk mendapatkan ID dari dua coroutine yang berbeda. Saat memanggil
`add(callable $callable)`, kelas `Parallel` akan secara otomatis membuat
coroutine untuknya, dan menggabungkannya ke dalam dispatcher milik `WaitGroup`.
Tidak hanya itu, kita juga dapat menyederhanakan kode di atas lebih lanjut
dengan menggunakan fungsi `parallel(array $callables)` untuk mencapai tujuan yang
sama. Berikut adalah kode yang disederhanakan.

```php
<?php
use Hyperf\Coroutine\Coroutine;

// The passed array parameters can also use `key of array` to facilitate distinguish the result of coroutine, and the returned result will also return the corresponding result according to key.
$result = parallel([
    function () {
        Coroutine::sleep(1);
        return Coroutine::id();
    },
    function () {
        Coroutine::sleep(1);
        return Coroutine::id();
    }
]);
```

> Perhatikan bahwa `Parallel` sendiri juga perlu digunakan di dalam coroutine.

### Coroutine Context

Karena coroutine di dalam proses yang sama berbagi memori yang sama,
eksekusi/peralihan coroutine bersifat tidak berurutan. Ini berarti sulit untuk
mengendalikan coroutine mana yang saat ini aktif secara langsung (sebenarnya
bisa, tetapi tidak ada yang ingin melakukannya dengan cara tersebut). Oleh karena
itu, kita perlu dapat beralih ke context yang sesuai pada saat yang sama ketika
peralihan coroutine terjadi.

Mengimplementasikan manajemen context untuk coroutine di Hyperf sangatlah
mudah. Berdasarkan metode statis `set(string $id, $value)`,
`get(string $id, $default = null)`, dan `has(string $id)` dari
`Hyperf\Context\Context`, manajemen data context dapat diselesaikan. Nilai yang
diatur (set) dan didapatkan (get) oleh metode-metode ini dibatasi hanya untuk
coroutine saat ini. Ketika coroutine berakhir, context yang sesuai akan dirilis
secara otomatis. Tidak perlu mengelolanya secara manual, dan tidak perlu
khawatir tentang risiko kebocoran memori.
