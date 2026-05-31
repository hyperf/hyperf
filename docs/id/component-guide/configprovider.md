# Mekanisme ConfigProvider

Mekanisme `ConfigProvider` adalah mekanisme krusial untuk komponenisasi Hyperf. Decoupling, independensi, dan reusabilitas antar komponen semuanya dicapai berdasarkan mekanisme ini.

# Apa itu Mekanisme ConfigProvider?

Secara sederhana, setiap komponen menyediakan `ConfigProvider`, biasanya sebagai class di direktori root komponen. `ConfigProvider` menyediakan semua informasi konfigurasi untuk komponen yang bersangkutan. Informasi ini dimuat oleh framework Hyperf saat startup, dan akhirnya digabungkan ke dalam class implementasi yang sesuai dengan `Hyperf\Contract\ConfigInterface`, sehingga tercapai inisialisasi konfigurasi yang diperlukan saat berbagai komponen digunakan di framework Hyperf.

`ConfigProvider` sendiri tidak memiliki dependensi, tidak mewarisi class abstrak, dan tidak perlu mengimplementasikan interface apa pun. Ia hanya perlu menyediakan method `__invoke` dan mengembalikan array yang sesuai dengan struktur konfigurasi.

# Bagaimana Cara Mendefinisikan ConfigProvider?

Biasanya, `ConfigProvider` didefinisikan di direktori root komponen. Sebuah class `ConfigProvider` biasanya terlihat seperti ini:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // Digabungkan ke dalam file config/autoload/dependencies.php
            'dependencies' => [],
            // Digabungkan ke dalam file config/autoload/annotations.php
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // Definisi Command default, digabungkan ke dalam Hyperf\Contract\ConfigInterface; dengan kata lain, ini sesuai dengan config/autoload/commands.php
            'commands' => [],
            // Mirip dengan commands
            'listeners' => [],
            // File konfigurasi default komponen; menjalankan perintah akan menyalin file sumber ke file tujuan
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'deskripsi dari file konfigurasi ini.', // Deskripsi
                    // Disarankan untuk meletakkan konfigurasi default di folder publish, beri nama file sama dengan nama komponen
                    'source' => __DIR__ . '/../publish/file.php',  // Jalur file konfigurasi yang sesuai
                    'destination' => BASE_PATH . '/config/autoload/file.php', // Salin ke jalur ini sebagai file ini
                ],
            ],
            // Anda dapat melanjutkan untuk mendefinisikan konfigurasi lain, yang pada akhirnya akan digabungkan ke dalam container konfigurasi yang sesuai dengan ConfigInterface
        ];
    }
}
```

## Deskripsi File Konfigurasi Default

Setelah mendefinisikan `publish` di `ConfigProvider`, Anda dapat menggunakan perintah berikut untuk menghasilkan file konfigurasi dengan cepat:

```bash
php bin/hyperf.php vendor:publish package_name
```

Misalnya, jika nama paket adalah `hyperf/amqp`, Anda dapat menjalankan perintah untuk menghasilkan file konfigurasi `amqp` default:

```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Hanya membuat class tidak akan otomatis dimuat oleh Hyperf. Anda tetap perlu menambahkan beberapa definisi komponen ke `composer.json` untuk memberi tahu Hyperf bahwa ini adalah class `ConfigProvider` yang perlu dimuat. Kamu perlu menambahkan konfigurasi `extra.hyperf.config` ke file `composer.json` di dalam komponen dan tentukan namespace dari class `ConfigProvider` yang sesuai, seperti di bawah ini:

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

Setelah mendefinisikannya, jalankan perintah yang membuat Composer menghasilkan ulang file `composer.lock`, seperti `composer install`, `composer update`, atau `composer dump-autoload`, agar dapat dibaca secara normal.

# Alur Eksekusi Mekanisme ConfigProvider

Konfigurasi `ConfigProvider` tidak harus dibagi dengan cara ini; ini adalah sebuah konvensi. Sebenarnya, keputusan akhir tentang cara mem-parsing konfigurasi ini ada di tangan pengguna. Pengguna dapat menyesuaikan pemuatan dengan memodifikasi kode di file `config/container.php` dari proyek Skeleton, yang berarti file `config/container.php` menentukan pemindaian dan pemuatan `ConfigProvider`.

# Spesifikasi Desain Komponen

Karena atribut `extra` di `composer.json` tidak memiliki fungsi atau efek lain ketika data tidak digunakan, definisi dalam komponen ini tidak akan menyebabkan gangguan saat digunakan di framework lain. Oleh karena itu, `ConfigProvider` adalah mekanisme yang hanya berfungsi di framework Hyperf dan tidak akan berdampak pada framework lain. Ini menjadi fondasi untuk penggunaan ulang komponen, tetapi juga mengharuskan spesifikasi berikut diikuti saat mendesain komponen:

- Semua desain class harus bisa digunakan melalui metode `OOP` standar. Semua fitur proprietary Hyperf harus disediakan sebagai fitur peningkatan di class terpisah, yang berarti komponen masih bisa digunakan secara standar di framework non-Hyperf.
- Jika desain dependensi komponen bisa memenuhi [PSR Standards](https://www.php-fig.org/psr), prioritaskan untuk memenuhinya dan bergantung pada interface yang sesuai, bukan class implementasi. Untuk fungsionalitas yang tidak tercakup oleh [PSR Standards](https://www.php-fig.org/psr), prioritaskan untuk memenuhi interface yang didefinisikan di library contract [hyperf/contract](https://github.com/hyperf/contract) dan bergantung pada interface tersebut.
- Untuk class fitur peningkatan yang ditambahkan untuk mengimplementasikan fungsionalitas proprietary Hyperf, biasanya ada dependensi pada beberapa komponen Hyperf. Dependensi ini tidak boleh ditulis di item `require` dari `composer.json`, melainkan di item `suggest`.
- Desain komponen tidak boleh melakukan dependency injection melalui anotasi; metode injeksi hanya boleh menggunakan `constructor injection`, yang juga bisa memenuhi penggunaan di bawah `OOP`.
- Desain komponen tidak boleh melakukan definisi fungsional melalui anotasi; definisi fungsional hanya boleh didefinisikan melalui `ConfigProvider`.
- Desain class harus menghindari penyimpanan data state sebanyak mungkin karena ini akan menyebabkan class tidak bisa disediakan sebagai objek berumur panjang, dan juga akan menyulitkan penggunaan dependency injection, yang pada akhirnya menurunkan performa. Data state harus disimpan melalui konteks coroutine `Hyperf\Context\Context`.
