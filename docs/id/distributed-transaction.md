# Distributed Transaction

[dtm-client](https://github.com/dtm-php/dtm-client) adalah komponen client DTM distributed transaction yang dikembangkan dan dipelihara oleh tim Hyperf. Digunakan bersama dengan DTM-Server, ia dapat mengimplementasikan manajemen distributed transaction dan dapat digunakan secara stabil di lingkungan production.
[seata/seata-php](https://github.com/seata/seata-php) adalah komponen client PHP Seata yang dikembangkan oleh tim Hyperf dan dikontribusikan ke komunitas open-source Seata. Digunakan bersama dengan Seata-Server, ia dapat mengimplementasikan manajemen distributed transaction. Saat ini masih dalam pengembangan dan iterasi dan belum dapat digunakan di lingkungan production. Kami berharap semua orang dapat berpartisipasi untuk mempercepat inkubasinya.

# Pengenalan DTM-Client

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) adalah client PHP untuk distributed transaction manager [DTM](https://github.com/dtm-labs/dtm). Ia telah mendukung mode distributed transaction TCC, Saga, XA, dan two-phase message, dan mengimplementasikan komunikasi dengan DTM Server menggunakan protokol HTTP atau gRPC. Client dapat berjalan dengan aman di lingkungan PHP-FPM dan Swoole coroutine, dan menyediakan dukungan fungsional yang lebih mudah digunakan untuk [Hyperf](https://github.com/hyperf/hyperf).

# Tentang DTM

DTM adalah distributed transaction manager open-source yang diimplementasikan dalam Go, menyediakan kemampuan yang kuat untuk transaksi gabungan lintas bahasa dan lintas storage engine. DTM dengan elegan menyelesaikan masalah sulit dalam distributed transaction seperti idempotency, null compensation, dan hanging, serta menyediakan solusi distributed transaction yang mudah digunakan, berperforma tinggi, dan mudah di-scale secara horizontal.

## Keunggulan

* Sangat mudah untuk memulai
    - Service startup tanpa konfigurasi, menyediakan interface HTTP yang sangat sederhana, sangat mengurangi kesulitan memulai dengan distributed transaction.
* Lintas bahasa
    - Cocok untuk perusahaan dengan stack multi-bahasa. Nyaman untuk berbagai bahasa seperti Go, Python, PHP, NodeJs, Ruby, C#, dll.
* Sederhana digunakan
    - Developer tidak perlu lagi khawatir tentang masalah seperti hanging, null compensation, atau idempotency. Teknologi sub-transaction barrier dipelopori untuk menangani mereka.
* Mudah di-deploy dan dikembangkan
    - Hanya bergantung pada MySQL/Redis, deployment sederhana, mudah di-cluster, dan mudah di-scale secara horizontal.
* Mendukung berbagai protokol distributed transaction
    - TCC, SAGA, XA, two-phase message, solusi satu atap untuk berbagai masalah distributed transaction.

## Perbandingan

Untuk bahasa non-Java, saat ini belum ada distributed transaction manager yang matang selain DTM. Oleh karena itu, berikut adalah perbandingan antara DTM dan Seata, proyek open-source paling matang di Java:

| Fitur | DTM | SEATA | Catatan |
|:-----:|:----:|:------------------------------------------------------------------------------------------------:|:----:|
| [Bahasa yang Didukung](https://dtm.pub/other/opensource.html#lang) | <span style="color:green">Go, C#, Java, Python, PHP...</span> | <span style="color:orange">Java, Go</span> | DTM dapat dengan mudah mengintegrasikan bahasa baru |
| [Storage Engine](https://dtm.pub/other/opensource.html#store) | <span style="color:green"> Mendukung database, Redis, Mongo, dll. </span> | <span style="color:orange"> Database </span> | |
| [Penanganan Exception](https://dtm.pub/other/opensource.html#exception) | <span style="color:green"> Sub-transaction barrier otomatis </span> | <span style="color:orange"> Manual </span> | DTM menyelesaikan idempotency, hanging, null compensation |
| [SAGA Transaction](https://dtm.pub/other/opensource.html#saga) | <span style="color:green"> Minimalis dan mudah digunakan </span> | <span style="color:orange"> State machine kompleks </span> | |
| [Two-phase Message](https://dtm.pub/other/opensource.html#msg) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | Arsitektur message eventual consistency paling sederhana |
| [TCC Transaction](https://dtm.pub/other/opensource.html#tcc) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [XA Transaction](https://dtm.pub/other/opensource.html#xa) | <span style="color:green">✓</span> | <span style="color:green">✓</span> | |
| [AT Transaction](https://dtm.pub/other/opensource.html#at) | <span style="color:orange"> XA direkomendasikan </span> | <span style="color:green">✓</span> | AT mirip dengan XA, tetapi memiliki dirty rollback |
| [Single Service Multi-Data Source](https://dtm.pub/other/opensource.html#multidb) | <span style="color:green">✓</span> | <span style="color:red">✗</span> | |
| [Protokol Komunikasi](https://dtm.pub/other/opensource.html#protocol) | HTTP, gRPC | Dubbo dan protokol lainnya | DTM lebih ramah cloud-native |
| [Jumlah Bintang](https://dtm.pub/other/opensource.html#star) | <img src="https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social" alt="github stars"/> | <img src="https://img.shields.io/github/stars/seata/seata.svg?style=social" alt="github stars"/> | DTM merilis versi 0.1 pada 2021-06-04 dan berkembang pesat |

Dilihat dari fitur yang dibandingkan di atas, DTM memiliki keunggulan besar dalam banyak aspek. Jika Anda mempertimbangkan dukungan multi-bahasa dan dukungan multi-storage engine, maka DTM tidak diragukan lagi adalah pilihan pertama Anda.

# Instalasi

dtm-client dapat dengan mudah diinstal melalui Composer:

```bash
composer require dtm/dtm-client
```

* Jangan lupa untuk memulai DTM Server saat menggunakannya.

# Konfigurasi

## File Konfigurasi

Jika Anda menggunakannya di framework Hyperf, setelah menginstal komponen, Anda dapat menggunakan perintah `vendor:publish` berikut untuk menerbitkan file konfigurasi ke `./config/autoload/dtm.php` sekaligus:

```bash
php bin/hyperf.php vendor:publish dtm/dtm-client
```

Jika Anda menggunakannya di framework non-Hyperf, Anda dapat menyalin file `./vendor/dtm/dtm-client/publish/dtm.php` ke direktori konfigurasi yang sesuai.

```php
use DtmClient\Constants\Protocol;
use DtmClient\Constants\DbType;

return [
    // Protokol komunikasi antara client dan DTM Server, mendukung Protocol::HTTP dan Protocol::GRPC
    'protocol' => Protocol::HTTP,
    // Alamat DTM Server
    'server' => '127.0.0.1',
    // Port DTM Server
    'port' => [
        'http' => 36789,
        'grpc' => 36790,
    ],
    // Konfigurasi sub-transaction barrier
    'barrier' => [
        // Konfigurasi sub-transaction barrier di bawah mode DB
        'db' => [
            'type' => DbType::MySQL
        ],
        // Konfigurasi sub-transaction barrier di bawah mode Redis
        'redis' => [
            // Periode timeout untuk catatan sub-transaction barrier
            'expire_seconds' => 7 * 86400,
        ],
        // Class untuk menerapkan sub-transaction barrier di framework non-Hyperf
        'apply' => [],
    ],
    // Konfigurasi umum untuk Guzzle client di bawah protokol HTTP
    'guzzle' => [
        'options' => [],
    ],
];
```

## Konfigurasi Middleware

Sebelum digunakan, Anda perlu mengkonfigurasi middleware `DtmClient\Middleware\DtmMiddleware` sebagai global middleware untuk Server. Middleware ini mendukung spesifikasi PSR-15 dan dapat diterapkan ke berbagai framework yang mendukung spesifikasi ini.
Untuk konfigurasi middleware di Hyperf, Anda dapat merujuk ke bab [Hyperf Middleware](id/middleware.md).

# Penggunaan

Penggunaan dtm-client sangat sederhana. Kami menyediakan proyek contoh [dtm-php/dtm-sample](https://github.com/dtm-php/dtm-sample) untuk membantu Anda lebih memahami dan melakukan debugging.
Sebelum menggunakan komponen ini, sangat disarankan juga agar Anda membaca [Dokumentasi Resmi DTM](https://dtm.pub/) untuk pemahaman yang lebih mendetail.

## Mode TCC

Mode TCC adalah solusi transaksi fleksibel yang sangat populer, terdiri dari akronim Try-Confirm-Cancel. Pertama kali diusulkan oleh Pat Helland dalam makalah yang diterbitkan pada tahun 2007 berjudul "Life beyond Distributed Transactions: an Apostate's Opinion".

### 3 Tahapan TCC

Tahap Try: Mencoba mengeksekusi, menyelesaikan semua pemeriksaan bisnis (konsistensi), dan menyediakan resource bisnis yang diperlukan (quasi-isolasi).
Tahap Confirm: Jika Try dari semua branch berhasil, maka masuk ke tahap Confirm. Confirm benar-benar mengeksekusi bisnis, tidak melakukan pemeriksaan bisnis, dan hanya menggunakan resource bisnis yang disediakan di tahap Try.
Tahap Cancel: Jika salah satu Try dari semua branch gagal, maka masuk ke tahap Cancel. Cancel melepaskan resource bisnis yang disediakan di tahap Try.

Jika kita ingin melakukan bisnis yang mirip dengan transfer bank antar bank, TransOut dan TransIn masing-masing berada di microservice yang berbeda. Diagram timing yang khas untuk transaksi TCC yang berhasil diselesaikan adalah sebagai berikut:

<img src="https://dtm.pub/assets/tcc_normal.dea14fb3.jpg" height=600 />

### Contoh Kode

Berikut mendemonstrasikan metode penggunaan di framework Hyperf; framework lainnya serupa:

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
                // Buat data panggilan untuk sub-transaksi A
                $tcc->callBranch(
                    // Parameter untuk memanggil method Try
                    ['amount' => 30],
                    // URL untuk method Try
                    $this->serviceUri . '/tcc/transA/try',
                    // URL untuk method Confirm
                    $this->serviceUri . '/tcc/transA/confirm',
                    // URL untuk method Cancel
                    $this->serviceUri . '/tcc/transA/cancel'
                );
                // Buat data panggilan untuk sub-transaksi B, dan seterusnya
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
        // Dapatkan ID transaksi global melalui TransContext::getGid() dan kembalikan
        return TransContext::getGid();
    }
}
```

## Mode Saga

Mode Saga adalah salah satu solusi paling terkenal di bidang distributed transaction dan juga sangat populer di berbagai sistem besar. Pertama kali muncul dalam makalah yang diterbitkan pada tahun 1987 oleh Hector Garcaa-Molrna & Kenneth Salem, [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf).

Saga adalah bentuk transaksi eventual consistency, juga merupakan transaksi fleksibel, disebut juga Long-running-transaction. Saga terdiri dari serangkaian local transaction. Setelah setiap local transaction memperbarui database, ia akan mempublikasikan pesan atau event untuk memicu eksekusi local transaction berikutnya dalam global transaction Saga. Jika sebuah local transaction gagal karena aturan bisnis tertentu tidak dapat dipenuhi, Saga akan mengeksekusi operasi kompensasi untuk semua transaksi yang berhasil disubmit sebelum transaksi yang gagal ini. Oleh karena itu, ketika membandingkan mode Saga dengan mode TCC, karena kurangnya langkah resource reservation, implementasi logika rollback seringkali menjadi lebih rumit.

### Pembagian Sub-transaksi Saga

Misalnya, jika kita ingin melakukan bisnis yang mirip dengan transfer bank antar bank, transfer 30 yuan dari akun A ke akun B. Menurut prinsip Saga transaction, kita membagi seluruh global transaction menjadi service berikut:
- TransOut service, yang akan mengurangi 30 yuan dari akun A.
- TransOutCompensate service, yang melakukan rollback operasi transfer di atas, yaitu menambahkan 30 yuan ke akun A.
- TransIn service, yang akan menambahkan 30 yuan ke akun B.
- TransInCompensate service, yang melakukan rollback operasi transfer masuk di atas, yaitu mengurangi 30 yuan dari akun B.

Logika seluruh transaksi adalah:

Transfer keluar berhasil => Transfer masuk berhasil => Global transaction selesai

Jika terjadi kesalahan di tengah jalan, seperti kesalahan dalam transfer ke akun B, operasi kompensasi dari branch yang dieksekusi akan dipanggil, yaitu:

Transfer keluar berhasil => Transfer masuk gagal => Kompensasi transfer masuk berhasil => Kompensasi transfer keluar berhasil => Rollback global transaction selesai

Berikut adalah diagram timing yang khas untuk transaksi SAGA yang berhasil diselesaikan:

<img src="https://dtm.pub/assets/saga_normal.a2849672.jpg" height=428 />

### Contoh Kode

Berikut mendemonstrasikan metode penggunaan di framework Hyperf; framework lainnya serupa:

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
        // Inisialisasi Saga transaction
        $this->saga->init();
        // Tambah sub-transaksi TransOut
        $this->saga->add(
            $this->serviceUri . '/saga/transOut', 
            $this->serviceUri . '/saga/transOutCompensate', 
            $payload
        );
        // Tambah sub-transaksi TransIn
        $this->saga->add(
            $this->serviceUri . '/saga/transIn', 
            $this->serviceUri . '/saga/transInCompensate', 
            $payload
        );
        // Submit Saga transaction
        $this->saga->submit();
        // Dapatkan ID transaksi global melalui TransContext::getGid() dan kembalikan
        return TransContext::getGid();
    }
}
```

## Mode XA

XA adalah spesifikasi distributed transaction yang diusulkan oleh organisasi X/Open. Spesifikasi XA terutama mendefinisikan interface antara transaction manager (TM) global dan resource manager (RM) lokal. Database lokal seperti mysql berperan sebagai RM dalam XA.

XA dibagi menjadi dua fase:

Fase 1 (prepare): Semua RM yang berpartisipasi bersiap untuk mengeksekusi transaksi dan mengunci resource yang diperlukan. Ketika peserta sudah siap, mereka melaporkan ke TM bahwa mereka siap. Fase 2 (commit/rollback): Setelah transaction manager (TM) mengkonfirmasi bahwa semua peserta (RM) siap, ia mengirimkan perintah commit ke semua peserta.

Saat ini, database mainstream pada dasarnya mendukung XA transaction, termasuk mysql, oracle, sqlserver, postgres.

Berikut adalah diagram timing yang khas untuk transaksi XA yang berhasil diselesaikan:

<img src="https://dtm.pub/assets/xa_normal.5a0ce600.jpg" height=600/>

### Contoh Kode

Berikut mendemonstrasikan metode penggunaan di framework Hyperf; framework lainnya serupa:
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
        // Buka global transaction XA
        $gid = $this->xa->generateGid();
        $this->xa->globalTransaction($gid, function () use ($payload) {
            // Panggil interface sub-transaksi
            $respone = $this->xa->callBranch($this->serviceUri . '/xa/api/transIn', $payload);
            // Dapatkan struktur return sub-transaksi di bawah mode XA http
            /* @var ResponseInterface $respone */
            $respone->getBody()->getContents();
            // Panggil interface sub-transaksi
            $payload = ['amount' => 10];
            $this->xa->callBranch($this->serviceUri . '/xa/api/transOut', $payload);
        });
        // Dapatkan ID transaksi global melalui TransContext::getGid() dan kembalikan
        return TransContext::getGid();
    }

    #[RequestMapping(methods: ["GET", "POST", "PUT"], path: 'api/transIn')]
    public function transIn(RequestInterface $request): array
    {
        $content = $request->post('amount');
        $amount = $content['amount'] ?? 50;
        // Simulasi method transIn di bawah sistem terdistribusi
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Harap gunakan DBTransactionInterface untuk menangani transaksi Mysql lokal
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
        // Simulasi method transOut di bawah sistem terdistribusi
        $this->xa->localTransaction(function (DBTransactionInterface $dbTransaction) use ($amount) {
            // Harap gunakan DBTransactionInterface untuk menangani transaksi Mysql lokal
            $dbTransaction->xaExecute('UPDATE `order` set `amount` = `amount` - ? where id = 2', [$amount]);
        });

        return ['status' => 0, 'message' => 'ok'];
    }
}
```
Kode di atas pertama-tama mendaftarkan global transaction XA, kemudian menambahkan dua sub-transaksi, transIn dan transOut. Setelah semua sub-transaksi berhasil dieksekusi, mereka disubmit ke dtm. Setelah dtm menerima xa global transaction yang disubmit, ia akan memanggil xa commit dari semua sub-transaksi untuk menyelesaikan seluruh transaksi xa.
