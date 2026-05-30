# Registrasi Layanan

Jumlah layanan meningkat seiring dengan dilakukannya pembagian (splitting).
Jumlah layanan yang besar dengan banyak node cluster harus dikelola untuk
memastikan seluruh sistem berjalan normal. Diperlukan sebuah komponen terpusat
untuk mengintegrasikan informasi dari berbagai layanan, yaitu mengumpulkan
informasi layanan yang tersebar di mana-mana. Informasi yang dikumpulkan dapat
berupa nama, alamat, jumlah, dll. dari komponen penyedia layanan tersebut.
Setiap komponen memiliki perangkat pemantau, dan ketika status layanan tertentu
pada komponen ini berubah, komponen tersebut akan melapor ke komponen terpusat
untuk memperbarui statusnya. Ketika pemanggil (caller) layanan meminta layanan
tertentu, ia pertama-tama akan pergi ke komponen terpusat untuk mendapatkan
informasi komponen seperti IP, port, dll., lalu memilih penyedia layanan
tertentu untuk diakses melalui strategi bawaan (default) atau yang disesuaikan
(custom). Komponen terpusat ini umumnya disebut sebagai `Service Center`. Di
Hyperf, kami mengimplementasikan service center berbasis `Consul`. Lebih banyak
service center akan didukung di masa mendatang.

# Instalasi

```bash
composer require hyperf/service-governance
```

# Registrasi Layanan

Registrasi layanan dapat dilakukan dengan mendefinisikan class melalui
annotation `#[RpcService]`, yang dapat dianggap sebagai publikasi layanan.
Sejauh ini, hanya protokol JSON RPC yang telah disesuaikan. Silakan merujuk ke
[JSON RPC Service](id/json-rpc.md) untuk detail lebih lanjut.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an add method with only int type in this example.
    public function calculate(int $a, int $b): int
    {
        // Specific implementation of the service method
        return $a + $b;
    }
}
```

Terdapat `4` parameter pada `#[RpcService]`:
Atribut `name` adalah nama dari layanan ini. Cukup tentukan nama yang unik
secara global di sini, dan Hyperf akan membuat ID yang sesuai berdasarkan
atribut ini dan mendaftarkannya di service center;
Atribut `protocol` adalah protokol yang diekspos oleh layanan tersebut. Sejauh
ini, hanya `jsonrpc` dan `jsonrpc-http` yang didukung, yang masing-masing
berkorespondensi dengan kedua protokol di bawah TCP dan HTTP. Nilai default-nya
adalah `jsonrpc-http`. Nilai di sini berkorespondensi dengan `key` dari protokol
yang terdaftar di `Hyperf\Rpc\ProtocolManager`. Kedua protokol ini pada
dasarnya adalah protokol JSON RPC. Perbedaannya terletak pada pemformatan data,
pengemasan data (data packaging), dan pengirim data (data transmitters).
Atribut `server` adalah `Server` yang akan membawa class layanan yang akan
dipublikasikan. Nilai default-nya adalah `jsonrpc-http`. Atribut ini
berkorespondensi dengan `name` di bawah `servers` pada file
`config/autoload/server.php`. Ini juga berarti bahwa kita perlu mendefinisikan
`Server` yang sesuai, yang akan kami jelaskan secara detail di bab berikutnya;
Atribut `publishTo` mendefinisikan service center tempat layanan akan
dipublikasikan. Saat ini, hanya `consul` yang didukung, atau Anda dapat
membiarkannya bernilai null. Ketika bernilai null, berarti layanan tidak akan
dipublikasikan ke service center, yang berarti Anda harus menangani masalah
service discovery secara manual. Ketika nilainya adalah `consul`, Anda perlu
mengonfigurasi konfigurasi terkait dari komponen [hyperf/consul](id/consul.md).
Untuk menggunakan fungsi ini, Anda harus menginstal komponen
[hyperf/service-governance](https://github.com/hyperf/service-governance);

> `use Hyperf\RpcServer\Annotation\RpcService;` diperlukan ketika annotation `#[RpcService]` digunakan.
