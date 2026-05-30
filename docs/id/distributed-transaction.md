# Distributed Transaction

[dtm-client](https://github.com/dtm-php/dtm-client) adalah komponen client
distributed transaction DTM yang dikembangkan dan dipelihara oleh tim Hyperf.
Komponen ini dapat mewujudkan manajemen distributed transaction dengan
DTM-Server. Versi stabil sudah dapat digunakan di lingkungan produksi.
[seata/seata-php](https://github.com/seata/seata-php) adalah komponen client
Seata PHP yang dikembangkan oleh tim Hyperf dan dikontribusikan ke komunitas
open source Seata. Komponen ini dapat mewujudkan manajemen distributed
transaction dengan Seata-Server, namun masih dalam iterasi pengembangan dan
belum digunakan di lingkungan produksi. Kami berharap semua orang dapat
berpartisipasi di dalamnya untuk mempercepat inkubasi.


# Pengenalan DTM-Client

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) adalah client PHP
dari Distributed Transaction Manager [DTM](https://github.com/dtm-labs/dtm).
Komponen ini mendukung pattern distributed transaction seperti TCC pattern, Saga
pattern, XA pattern, dan two-phase message pattern. Dalam hal protokol
komunikasi, komponen ini mendukung komunikasi dengan DTM Server melalui protokol
HTTP atau gRPC. Selain itu, client ini dapat berjalan dengan aman di lingkungan
PHP-FPM dan Swoole coroutine, serta memberikan dukungan yang lebih mudah untuk
framework [Hyperf](https://github.com/hyperf/hyperf).

# Tentang DTM

DTM adalah distributed transaction manager open source berbasis bahasa Go, yang
menyediakan fungsi kuat untuk menggabungkan transaksi di berbagai bahasa dan
storage engine. DTM secara elegan menyelesaikan masalah distributed transaction
seperti interface idempotent, null compensation, dan transaction suspension,
serta menyediakan solusi distributed transaction yang mudah digunakan,
berkinerja tinggi, dan mudah diskalakan secara horizontal.

## Keunggulan

* Mudah dimulai
  - Memulai layanan dengan konfigurasi nol dan menyediakan interface HTTP yang
    sangat sederhana dan jelas, yang sangat mengurangi tingkat kesulitan untuk
    memulai dengan distributed transaction
* Lintas bahasa pemrograman
  - Dapat digunakan oleh perusahaan dengan berbagai stack bahasa. Sangat nyaman
    digunakan dalam berbagai bahasa seperti Go, Python, PHP, NodeJs, Ruby, C#,
    dll.
* Sederhana untuk digunakan
  - Developer tidak perlu lagi khawatir tentang transaction suspension, null
    compensation, interface idempotent, dan masalah lainnya, karena teknologi
    sub-transaction barrier yang pertama kali diperkenalkan akan menanganinya
    untuk Anda
* Mudah dideploy dan dikembangkan
  - Hanya bergantung pada MySQL/Redis, mudah dideploy, mudah dibuat cluster, dan
    mudah diskalakan secara horizontal
* Dukungan berbagai protokol distributed transaction
  - TCC, SAGA, XA, two-phase message, solusi satu atap untuk berbagai masalah
    distributed transaction

## Perbandingan

Di lingkungan selain bahasa Java, masih belum ada distributed transaction manager
yang matang selain DTM, jadi berikut adalah perbandingan antara DTM dan Seata,
proyek open source paling matang di Java:

| Features | DTM | SEATA | Memo |
|:---:|:---:|:---:|:---:|
| [Dukungan bahasa](https://dtm.pub/other/opensource.html#lang) | <span style="color:green">Go, C#, Java, Python, PHP...</span> | <span style="color:orange">Java, Go</span> | DTM lebih mudah menerapkan client ke bahasa baru |
| [Storage Engine](https://dtm.pub/other/opensource.html#store) | <span style="color:green">Mendukung Database, Redis, Mongo, dll.</span> | <span style="color:orange">Database</span> | |
| [Penanganan Eksepsi](https://dtm.pub/other/opensource.html#exception) | <span style="color:green">Sub-transaction barrier ditangani secara otomatis</span> | <span style="color:orange">Secara manual</span> | DTM menyelesaikan transaction suspension, null compensation, interface idempotent, dll. |
| [SAGA](https://dtm.pub/other/opensource.html#saga) | <span style="color:green">Mudah digunakan</span> | <span style="color:orange">State machine yang rumit</span> | |
| [Two-phase message](https://dtm.pub/other/opensource.html#msg) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | Arsitektur Konsistensi Akhir (Eventual Consistency) Pesan Minimal |
| [TCC](https://dtm.pub/other/opensource.html#tcc) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [XA](https://dtm.pub/other/opensource.html#xa) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [AT](https://dtm.pub/other/opensource.html#at) | <span style="color:orange">XA lebih direkomendasikan</span> | <span style="color:green">✓</span> | AT mirip dengan XA, tetapi dengan rollback kotor (dirty rollback) |
| [Satu layanan dengan beberapa data source](https://dtm.pub/other/opensource.html#multidb) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | |
| [Protokol komunikasi](https://dtm.pub/other/opensource.html#protocol) | HTTP, gRPC | Dubbo, dll. | DTM lebih ramah terhadap cloud native |
| [Github Stargazers](https://dtm.pub/other/opensource.html#star) | <img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/> | <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> | DTM merilis versi 0.1 sejak 2021-06-04, berkembang dengan sangat cepat |

Dari karakteristik perbandingan di atas, DTM memiliki keuntungan besar dalam
banyak aspek. Jika Anda mempertimbangkan dukungan multi-bahasa dan dukungan
multi-storage engine, maka DTM tidak diragukan lagi adalah pilihan pertama Anda.

# Instalasi

Sangat mudah untuk menginstal dtm-client melalui Composer

```bash
composer require dtm/dtm-client
```

* Jangan lupa untuk menjalankan DTM Server sebelum Anda menggunakannya

# Konfigurasi

## File Konfigurasi

Jika Anda menggunakan framework Hyperf, setelah menginstal komponen, Anda dapat
mempublikasikan file konfigurasi ke `./config/autoload/dtm.php` dengan perintah
`vendor:publish` berikut:

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

Jika Anda menggunakan framework non-Hyperf, salin file
`./vendor/dtm/dtm-client/publish/dtm.php` ke direktori konfigurasi yang sesuai.

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    // The communication protocol between the client and the DTM Server, supports Protocol::HTTP and Protocol::GRPC
    'protocol' => Protocol::HTTP,
    // DTM Server address
    'server' => '127.0.0.1',
    // DTM Server port
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // Sub-transaction barrier
    'barrier' => [
        // Subtransaction barrier configuration in DB mode 
        'db' => [
            'type' => DbType::MySQL
        ],
        // Subtransaction barrier configuration in Redis mode
        'redis' => [
            // Timeout for subtransaction barrier records
            'expire_seconds' => 7 * 86400,
        ],
        // Classes that apply sub-transaction barriers in non-Hyperf frameworks or without annotation usage
        'apply' => [],
    ],
    // Options of Guzzle client under HTTP protocol
    'guzzle' => [
        'options' => [],
    ],
];
```

## Konfigurasi Middleware

Sebelum menggunakannya, Anda perlu mengonfigurasi middleware
`DtmClient\Middleware\DtmMiddleware` sebagai middleware global server.
Middleware ini mendukung spesifikasi PSR-15 dan berlaku untuk semua framework
yang mendukung spesifikasi tersebut.
Untuk konfigurasi middleware di Hyperf, silakan merujuk ke bab [Dokumentasi
Hyperf - Middleware](https://www.hyperf.wiki/2.2/#/zh-cn/middleware/middleware).

# Penggunaan

Penggunaan dtm-client sangat sederhana, kami menyediakan proyek contoh
[dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) untuk membantu Anda
memahami dan men-debug-nya dengan lebih baik.
Sebelum menggunakan komponen ini, sangat disarankan agar Anda membaca
[dokumentasi resmi DTM](https://dtm.pub/) untuk pemahaman yang lebih rinci.

## TCC pattern

TCC pattern adalah solusi distributed transaction fleksibel yang sangat
populer. Konsep TCC terdiri dari akronim tiga kata Try-Confirm-Cancel. Konsep
ini pertama kali dipublikasikan dalam makalah berjudul [Life beyond Distributed
Transactions:an Apostate’s
Opinion](https://www.ics.uci.edu/~cs223/papers/cidr07p15.pdf) oleh Pat Helland
pada tahun 2007.

### Tiga Tahap TCC

Tahap Try: mencoba mengeksekusi, menyelesaikan semua pemeriksaan bisnis
(konsistensi), memesan sumber daya bisnis yang diperlukan (pre-isolation)
Tahap Confirm: Jika semua cabang Try berhasil, lanjut ke tahap Confirm. Confirm
sebenarnya mengeksekusi bisnis tanpa pemeriksaan bisnis apa pun, dan hanya
menggunakan sumber daya bisnis yang dipesan pada tahap Try
Tahap Cancel: Jika salah satu cabang Try gagal, lanjut ke tahap Cancel. Rilis
sumber daya bisnis yang dipesan pada tahap Try.

Jika kita ingin melakukan bisnis yang mirip dengan transfer antar bank, transfer
keluar (TransOut) dan transfer masuk (TransIn) berada di microservice yang
berbeda, dan diagram urutan (sequence diagram) tipikal dari transaksi TCC yang
berhasil diselesaikan adalah sebagai berikut:

<img src="https://en.dtm.pub/assets/tcc_normal.85ceb661.jpg" height=600 />

### Contoh

Berikut menunjukkan cara menggunakannya dalam framework Hyperf, framework
lainnya serupa

```php
<?php
namespace App\Controller;

use DtmClient\TCC;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Throwable;

#[Controller(prefix: '/tcc')]
class TccController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';

    #[Inject]
    protected TCC $tcc;

    #[GetMapping(path: 'successCase')]
    public function successCase()
    {
        try {
            
            $this->tcc->globalTransaction(function (TCC $tcc) {
                // Create call data for subtransaction A
                $tcc->callBranch(
                    // Arguments for calling the Try method
                    ['amount' => 30],
                    // URL of Try stage
                    $this->serviceUri . '/tcc/transA/try',
                    // URL of Confirm stage
                    $this->serviceUri . '/tcc/transA/confirm',
                    // URL of Cancel stage
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // Create call data for subtransaction B, and so on
                $tcc->callBranch(
                    ['amount' => 30],
                    $this->serviceUri . '/tcc/transB/try',
                    $this->serviceUri . '/tcc/transB/confirm',
                    $this->serviceUri . '/tcc/transB/cancel'
                );
            });
        } catch (Throwable $e) {
            var_dump($e->getMessage(), $e->getTraceAsString());
        }
        // Get the global transaction ID through TransContext::getGid() and return it to the client
        return TransContext::getGid();
    }
}
```

## Saga pattern

Saga pattern adalah salah satu solusi paling terkenal di bidang distributed
transaction, dan juga sangat populer di sistem-sistem besar. Pola ini pertama
kali muncul dalam makalah [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf)
yang diterbitkan oleh Hector Garcia-Molina & Kenneth Salem pada tahun 1987.

Saga adalah transaksi eventual consistency, yang juga merupakan transaksi
fleksibel, juga dikenal sebagai long-running transaction. Saga terdiri dari
serangkaian transaksi lokal. Setelah setiap transaksi lokal memperbarui
database, ia akan menerbitkan pesan atau event untuk memicu eksekusi transaksi
lokal berikutnya dalam transaksi global Saga. Jika transaksi lokal gagal karena
beberapa aturan bisnis tidak dapat dipenuhi, Saga melakukan tindakan kompensasi
(compensating action) untuk semua transaksi yang berhasil dicommit sebelum
transaksi yang gagal tersebut. Oleh karena itu, ketika Saga pattern dibandingkan
dengan TCC pattern, implementasi logika rollback sering kali menjadi lebih rumit
karena tidak adanya langkah pemesanan sumber daya (resource reservation).

### Pembagian Sub-transaksi Saga

Sebagai contoh, kita ingin melakukan bisnis yang mirip dengan transfer antar
bank, dan mentransfer 30 dollar dari akun A ke akun B. Berdasarkan prinsip
transaksi Saga, kita akan membagi seluruh transaksi global menjadi
layanan-layanan berikut:
- Layanan transfer keluar (TransOut), akun A akan dikurangi 30 dollar
- Layanan kompensasi transfer keluar (TransOutCompensate), melakukan rollback
  pada operasi transfer keluar di atas, yaitu menambahkan kembali akun A sebesar
  30 dollar
- Layanan transfer masuk (TransIn), akun B akan ditambahkan 30 dollar
- Layanan kompensasi transfer masuk (TransInCompensate), melakukan rollback
  pada operasi transfer masuk di atas, yaitu mengurangi akun B sebesar 30 dollar

Logika dari seluruh transaksi tersebut adalah:

Eksekusi transfer keluar sukses => Eksekusi transfer masuk sukses => transaksi global selesai

Jika terjadi kesalahan di tengah jalan, seperti kesalahan saat mentransfer ke
akun B, operasi kompensasi dari cabang yang telah dieksekusi akan dipanggil,
yaitu:

Eksekusi transfer keluar sukses => eksekusi transfer masuk gagal => eksekusi kompensasi transfer masuk sukses => eksekusi kompensasi transfer keluar sukses => rollback transaksi global selesai

Berikut adalah diagram urutan (sequence diagram) tipikal dari transaksi SAGA
yang berhasil diselesaikan:

<img src="https://en.dtm.pub/assets/saga_normal.59a75c01.jpg" height=428 />

### Contoh

Berikut menunjukkan cara menggunakannya dalam framework Hyperf, framework
lainnya serupa

```php
namespace App\Controller;

use DtmClient\Saga;
use DtmClient\TransContext;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/saga')]
class SagaController
{

    protected string $serviceUri = 'http://127.0.0.1:9501';
    
    #[Inject]
    protected Saga $saga;

    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // Init Saga global transaction
        $this->saga->init();
        // Add TransOut sub-transaction
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // Add TransIn sub-transaction
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // Submit Saga global transaction
        $this->saga->submit();
        // Get the global transaction ID through TransContext::getGid() and return it to the client
        return TransContext::getGid();
    }
}
```

## XA pattern

XA adalah spesifikasi untuk distributed transaction yang diusulkan oleh
organisasi X/Open. Model X/Open Distributed Transaction Processing (DTP)
membayangkan tiga komponen software:

Application Program (AP) mendefinisikan batas-batas transaksi dan menentukan
tindakan yang membentuk suatu transaksi.

Resource Manager (RM, seperti database atau sistem akses file) menyediakan akses
ke sumber daya bersama (shared resources).

Komponen terpisah yang disebut Transaction Manager (TM) menetapkan pengidentifikasi
ke transaksi, memantau kemajuannya, dan bertanggung jawab atas penyelesaian
transaksi serta pemulihan kegagalan.

Gambar berikut mengilustrasikan interface yang didefinisikan oleh model X/Open DTP.

<img src="https://en.dtm.pub/assets/xa-dtp.78622cb4.jpeg" />

XA dibagi menjadi dua fase.

Fase 1 (prepare): Semua RM yang berpartisipasi bersiap untuk mengeksekusi
transaksi mereka dan mengunci sumber daya yang diperlukan. Ketika setiap
peserta siap, ia melaporkannya ke TM.

Fase 2 (commit/rollback): Ketika transaction manager (TM) menerima laporan
bahwa semua peserta (RM) telah siap, ia mengirimkan perintah commit ke semua
peserta. Jika tidak, ia mengirimkan perintah rollback ke semua peserta.

Saat ini, hampir semua database populer mendukung transaksi XA, termasuk MySQL,
Oracle, SqlServer, dan Postgres.

<img src="https://en.dtm.pub/assets/xa_normal.ebc35054.jpg" height=600 />

### Contoh Kode

Berikut ini ditunjukkan dalam framework Hyperf, serupa dengan yang lainnya

```php
<?php

namespace App\Controller;

use App\Grpc\GrpcClient;
use DtmClient\DbTransaction\DBTransactionInterface;
use DtmClient\TransContext;
use DtmClient\XA;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/xa')]
class XAController
{

    private GrpcClient $grpcClient;

    protected string $serviceUri = 'http://127.0.0.1:9502';

    public function __construct(
        private XA $xa,
        protected ConfigInterface $config,
    ) {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        $this->grpcClient = new GrpcClient($hostname);
    }


    #[GetMapping(path: 'successCase')]
    public function successCase(): string
    {
        $payload = ['amount' => 50];
        // Open the Xa, the global thing
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // Call the subthings interface
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // Get subthings return structure in XA http mode
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // Call the subthings interface
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // Return the global transaction ID via TransContext:: getGid()
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // The transIn method under the simulated distributed system
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Please use the DBTransactionInterface to handle the local Mysql things
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` + ? where id = 1', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transOut')]
    public function transOut(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 10;
        // The transOut method under the simulated distributed system
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Please use the DBTransactionInterface to handle the local Mysql things
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}
```

Kode di atas pertama-tama meregistrasikan transaksi XA global, lalu memanggil dua
sub-transaksi TransOut, TransIn. Setelah semua sub-transaksi berhasil dieksekusi,
transaksi XA global dicommit ke DTM. DTM menerima commit dari transaksi global
XA tersebut, lalu memanggil XA commit dari semua sub-transaksi, dan akhirnya
mengubah status transaksi global menjadi sukses.
