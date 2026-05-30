# Mekanisme ConfigProvider

Mekanisme ConfigProvider adalah mekanisme yang sangat penting untuk
komponenisasi Hyperf. `Decoupling between components` (Pemisahan ketergantungan
antar komponen), `Independence of components` (Kemandirian komponen), dan
`Reusability of components` (Ketergunaan ulang komponen) semuanya direalisasikan
berdasarkan mekanisme ini.

# Apa itu mekanisme ConfigProvider?

Secara sederhana, setiap komponen akan menyediakan sebuah `ConfigProvider`,
biasanya kelas `ConfigProvider` disediakan di direktori root komponen, dan
`ConfigProvider` akan menyediakan semua informasi konfigurasi dari komponen yang
bersangkutan. Saat framework Hyperf dijalankan, informasi konfigurasi akhir di
dalam `ConfigProvider` akan digabungkan ke dalam kelas implementasi yang sesuai
dari `Hyperf\Contract\ConfigInterface`, sehingga inisialisasi konfigurasi dari
setiap komponen dapat terwujud saat digunakan di bawah framework Hyperf.

`ConfigProvider` sendiri tidak memiliki dependensi apa pun, tidak mewarisi kelas
abstrak apa pun, dan tidak memerlukan implementasi antarmuka (interface) apa
pun. Ia hanya perlu menyediakan metode `__invoke` dan mengembalikan array dari
struktur konfigurasi yang sesuai.

# Bagaimana cara mendefinisikan ConfigProvider?

Secara umum, `ConfigProvider` akan didefinisikan di direktori root dari
komponen, dan kelas `ConfigProvider` biasanya seperti berikut:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
     public function __invoke(): array
     {
         return [
             // merged into config/autoload/dependencies.php file
             'dependencies' => [],
             // merged into config/autoload/annotations.php file
             'annotations' => [
                 'scan' => [
                     'paths' => [
                         __DIR__,
                     ],
                 ],
             ],
             // The definition of the default Command is merged into Hyperf\Contract\ConfigInterface, another way to understand it is corresponding to config/autoload/commands.php
             'commands' => [],
             // similar to commands
             'listeners' => [],
             // Component default configuration file, that is, after executing the command, the file corresponding to source will be copied to the file corresponding to destination
             'publish' => [
                 [
                     'id' => 'config',
                     'description' => 'description of this config file.', // description
                     // It is recommended that the default configuration be placed in the publish folder, and the file name is the same as the component name
                     'source' => __DIR__ . '/../publish/file.php', // corresponding configuration file path
                     'destination' => BASE_PATH . '/config/autoload/file.php', // copy as the file under this path
                 ],
             ],
             // You can also continue to define other configurations, which will eventually be merged into the configuration storage corresponding to ConfigInterface
         ];
     }
}
```

## Deskripsi file konfigurasi default

Setelah mendefinisikan `publish` di dalam `ConfigProvider`, Anda dapat
menggunakan perintah berikut untuk membuat file konfigurasi secara cepat:

```bash
php bin/hyperf.php vendor:publish package name
```

Jika nama paketnya adalah `hyperf/amqp`, Anda dapat menjalankan perintah
berikut untuk menghasilkan file konfigurasi default dari `amqp`:
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Hanya membuat sebuah kelas tidak akan membuatnya dimuat secara otomatis oleh
Hyperf. Anda masih perlu menambahkan beberapa definisi di `composer.json` dari
komponen untuk memberi tahu Hyperf bahwa ini adalah kelas ConfigProvider yang
perlu dimuat. Anda perlu menambahkan konfigurasi `extra.hyperf.config` pada file
`composer.json` di komponen tersebut, dan menentukan namespace dari kelas
`ConfigProvider` yang sesuai, seperti yang ditunjukkan di bawah ini:

```json
{
     "name": "hyperf/foo",
     "require": {
         "php": ">=7.3"
     },
     "autoload": {
         "psr-4": {
             "Hyperf\\Foo\\": "src/"
         }
     },
     "extra": {
         "hyperf": {
             "config": "Hyperf\\Foo\\ConfigProvider"
         }
     }
}
```

Setelah didefinisikan, Anda perlu menjalankan perintah seperti `composer install`
atau `composer update` atau `composer dump-autoload` untuk membiarkan Composer
membuat ulang file `composer.lock` sebelum dapat dibaca secara normal.

# Proses eksekusi mekanisme ConfigProvider

Konfigurasi dari `ConfigProvider` tidak harus dibagi dengan cara ini. Ini adalah
beberapa format yang disepakati. Faktanya, keputusan akhir tentang bagaimana
menganalisis konfigurasi ini juga ada pada pengguna. Pengguna dapat mengubah
kode dalam file `config/container.php` dari proyek Skeleton untuk menyesuaikan
pemuatan yang relevan, artinya file `config/container.php` menentukan pemindaian
(scanning) dan pemuatan (loading) dari `ConfigProvider`.

# Spesifikasi desain komponen

Karena atribut `extra` di `composer.json` tidak memiliki efek dan pengaruh lain
ketika data tidak digunakan, definisi di dalam komponen ini tidak akan
menyebabkan gangguan dan pengaruh apa pun saat digunakan oleh framework lain,
sehingga `ConfigProvider` adalah mekanisme yang hanya berfungsi pada framework
Hyperf, dan tidak akan memiliki dampak apa pun pada framework lain yang tidak
menggunakan mekanisme ini. Hal ini mendasari landasan untuk penggunaan kembali
komponen, namun hal ini juga mengharuskan spesifikasi berikut harus dipatuhi
saat merancang komponen:

- Semua kelas harus dirancang untuk memungkinkan penggunaan `OOP` standar, dan
  semua fitur khusus Hyperf harus disediakan sebagai peningkatan (enhancement)
  dan dalam kelas terpisah, yang berarti kelas tersebut masih dapat digunakan
  di framework non-Hyperf melalui cara standar untuk merealisasikan penggunaan
  komponen;
- Jika desain dependensi dari komponen dapat memenuhi [standar PSR](https://www.php-fig.org/psr),
  maka hal itu harus dipenuhi terlebih dahulu dan bergantung pada antarmuka
  (interface) yang sesuai, bukan kelas implementasinya; jika [standar PSR](https://www.php-fig.org/psr)
  tidak berisi fungsi tersebut, maka ia dapat memenuhi antarmuka dalam pustaka
  kontrak [hyperf/contract](https://github.com/hyperf/contract) yang
  didefinisikan oleh Hyperf, yang dipenuhi terlebih dahulu dan bergantung pada
  antarmuka yang sesuai daripada kelas implementasinya;
- Untuk kelas fungsi yang ditingkatkan yang ditambahkan untuk mengimplementasikan
  fungsi eksklusif Hyperf, secara umum, kelas tersebut juga memiliki dependensi
  pada beberapa komponen Hyperf, sehingga dependensi dari komponen ini tidak
  boleh ditulis di bagian `require` pada `composer.json`, melainkan ditulis
  sebagai saran di bagian `suggest`;
- Desain komponen tidak boleh melakukan dependency injection apa pun melalui
  anotasi (annotations), dan metode injeksi hanya boleh menggunakan
  `constructor injection`, yang juga dapat memenuhi penggunaan di bawah `OOP`;
- Desain komponen tidak boleh mendefinisikan fungsi apa pun melalui anotasi, dan
  definisi fungsi hanya boleh didefinisikan melalui `ConfigProvider`;
- Desain kelas sebisa mungkin tidak menyimpan data state, karena hal ini akan
  menyebabkan kelas tidak dapat disediakan sebagai objek dengan siklus hidup
  yang panjang (long life cycle), dan fitur dependency injection tidak dapat
  digunakan dengan mudah, yang akan mengurangi performa sampai tingkat tertentu.
  Semua data state harus disimpan melalui coroutine context `Hyperf\Context\Context`;
