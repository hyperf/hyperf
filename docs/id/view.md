# View

Komponen view disediakan oleh [hyperf/view](https://github.com/hyperf/view) untuk memenuhi kebutuhan rendering view Anda. Secara default, komponen ini mendukung lima template engine: `Blade`, `Smarty`, `Twig`, `Plates`, dan `ThinkTemplate`.

## Instalasi

```bash
composer require hyperf/view
```

## Konfigurasi

File konfigurasi untuk komponen View terletak di `config/autoload/view.php`. Jika file konfigurasi tidak ada, Anda dapat menjalankan perintah berikut untuk membuatnya:

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

Berikut adalah penjelasan dari konfigurasi yang relevan:

| Konfigurasi | Tipe | Nilai Default | Catatan |
|:---:|:---:|:---:|:---:|
| engine | string | Hyperf\View\Engine\BladeEngine::class | View rendering engine |
| mode | string | Mode::TASK | Mode rendering view |
| config.view_path | string | Tidak ada | Direktori default untuk file view |
| config.cache_path | string | Tidak ada | Direktori cache default untuk file view |

Contoh format file konfigurasi:

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // Rendering engine yang digunakan
    'engine' => BladeEngine::class,
    // Jika tidak diisi, mode Task digunakan secara default. Mode Task sangat direkomendasikan
    'mode' => Mode::TASK,
    'config' => [
        // Harap buat folder berikut jika belum ada
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

### Mode Task

Saat menggunakan mode `Task`, Anda perlu memperkenalkan komponen [hyperf/task](https://github.com/hyperf/task) dan harus mengonfigurasi `task_enable_coroutine` menjadi `false`, jika tidak, masalah kebingungan data coroutine akan terjadi. Untuk informasi lebih lanjut, silakan lihat dokumentasi komponen [Task](id/task.md).

Selain itu, dalam mode `Task`, pekerjaan rendering view diselesaikan di proses `Task Worker`, sementara pemrosesan request (yaitu Controller) diselesaikan di proses `Worker`. Pekerjaan kedua bagian diselesaikan oleh proses yang berbeda. Oleh karena itu, objek atau data yang dikelola melalui context di proses `Worker`, seperti `Request` dan `Session`, tidak dapat digunakan langsung di halaman view. Dalam hal ini, Anda perlu memproses data atau hasil keputusan di Controller terlebih dahulu, dan kemudian melewatkan data tersebut ke view untuk dirender saat memanggil `render`.

### Mode Sync

Jika Anda menggunakan mode `Sync` untuk merender view, pastikan engine yang terkait aman untuk coroutine, jika tidak, masalah kebingungan data akan terjadi. Disarankan untuk menggunakan mode `Task` yang lebih aman untuk data.

### Mengonfigurasi Static Resources

Jika Anda ingin `Swoole` mengelola resource statis, harap tambahkan konfigurasi berikut ke dalam konfigurasi `config/autoload/server.php` Anda.

```
return [
    'settings' => [
        ...
        // Static resources
        'document_root' => BASE_PATH . '/public',
        'enable_static_handler' => true,
    ],
];
```

## View Rendering Engine

Secara resmi, saat ini mendukung lima template: `Blade`, `Smarty`, `Twig`, `Plates`, dan `ThinkTemplate`. Secara default, menginstal [hyperf/view](https://github.com/hyperf/view) tidak akan secara otomatis menginstal template engine apa pun. Anda perlu menginstal template engine yang sesuai sendiri sesuai kebutuhan. Anda harus menginstal setidaknya satu template engine sebelum menggunakannya.

### Instalasi Blade Engine

```bash
composer require hyperf/view-engine
```

Untuk metode detailnya, lihat dokumen [View Engine](id/view-engine.md)

Atau gunakan:

> duncan3dc/blade tidak direkomendasikan untuk saat ini karena menggunakan Library Support Laravel, yang menyebabkan beberapa fungsi tidak kompatibel.

```bash
composer require duncan3dc/blade
```

### Instalasi Smarty Engine

```bash
composer require smarty/smarty
```

### Instalasi Twig Engine

```bash
composer require twig/twig
```

### Instalasi Plates Engine

```bash
composer require league/plates
```

### Instalasi ThinkTemplate Engine

```bash
composer require sy-records/think-template
```

### Mengintegrasikan Template Lain

Misalkan kita ingin mengintegrasikan template engine virtual bernama `TemplateEngine`. Kita perlu membuat kelas `TemplateEngine` yang sesuai di mana saja dan mengimplementasikan antarmuka `Hyperf\View\Engine\EngineInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // Instansiasi instance dari template engine yang sesuai
        $engine = new TemplateInstance();
        // Dan panggil metode rendering yang sesuai
        return $engine->render($template, $data);
    }
}
```

Kemudian ubah konfigurasi komponen view:

```php
<?php

use App\Engine\TemplateEngine;

return [
    // Ubah parameter engine ke kelas template engine kustom Anda
    'engine' => TemplateEngine::class,
    'mode' => Mode::TASK,
    'config' => [
        'view_path' => BASE_PATH . '/storage/view/',
        'cache_path' => BASE_PATH . '/runtime/view/',
    ],
];
```

## Penggunaan

Mengambil `BladeEngine` sebagai contoh, pertama buat file view `index.blade.php` di direktori yang sesuai.

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, {{ $name }}. Anda menggunakan blade template sekarang.
</body>
</html>
```

Di Controller, dapatkan instance `Hyperf\View\Render`, lalu panggil metode `render` dan berikan alamat file view `index` dan `data rendering`. Alamat file mengabaikan ekstensi file view.

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\View\RenderInterface;

#[AutoController]
class ViewController
{
    public function index(RenderInterface $render)
    {
        return $render->render('index', ['name' => 'Hyperf']);
    }
}
```

Akses URL yang sesuai untuk mendapatkan halaman view seperti di bawah ini:

```
Hello, Hyperf. Anda menggunakan blade template sekarang.
```
