# View

Rendering view diimplementasikan oleh komponen
[hyperf/view](https://github.com/hyperf/view). Komponen ini mendukung lima
templating engine yang berbeda; `Blade`, `Smarty`, `Twig`, `Plates` dan
`ThinkTemplate`.

## Instalasi

```bash
composer require hyperf/view
```

## Konfigurasi

File konfigurasi komponen view terletak di `config/autoload/view.php`. Jika
file konfigurasi tidak ada, perintah berikut dapat dijalankan untuk
menghasilkan file konfigurasi:

```bash
php bin/hyperf.php vendor:publish hyperf/view
```

Opsi konfigurasi berikut tersedia:

| Konfigurasi         | Tipe     | Nilai Default                            | Keterangan                   |
| :-----------------: | :------: | :--------------------------------------: | :--------------------------: |
| engine              | string   | Hyperf\View\Engine\BladeEngine::class    | View rendering engine        |
| mode                | string   | Mode::TASK                               | Mode rendering view          |
| config.view_path    | string   | Tidak ada                                | Alamat default file view     |
| config.cache_path   | string   | Tidak ada                                | Alamat cache file view       |

Contoh format file konfigurasi:

```php
<?php
declare(strict_types=1);

use Hyperf\View\Mode;
use Hyperf\View\Engine\BladeEngine;

return [
    // Rendering engine yang digunakan
    'engine' => BladeEngine::class,
    // Jika tidak diisi, defaultnya adalah mode Task, disarankan menggunakan mode Task
    'mode' => Mode::TASK,
    'config' => [
        // Jika folder berikut tidak ada, silakan buat sendiri
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

### Mode Task

Ketika menggunakan mode `Task`, komponen
[hyperf/task](https://github.com/hyperf/task) harus diinstal dan
`task_enable_coroutine` harus dikonfigurasi sebagai `false`, jika tidak maka
akan terjadi masalah konsistensi data coroutine. Silakan merujuk ke
dokumentasi komponen [task](id/task.md).

Selain itu, pada mode `Task` pekerjaan rendering view dilakukan oleh proses
`Task Worker` sedangkan pemrosesan request di controller diselesaikan oleh
proses `Worker`. Ini berarti tidak memungkinkan untuk mengakses objek data yang
bergantung pada konteks seperti `Request` dan `Session` secara langsung dari
view. Jika Anda perlu menggunakan data yang bergantung pada konteks di view
Anda, pastikan Anda meneruskan data tersebut dari controller melalui metode
`render`.

### Mode Sync

Jika Anda menggunakan mode `Sync` untuk me-render view, harap pastikan bahwa
engine yang relevan aman terhadap coroutine (coroutine safe), jika tidak maka
akan ada masalah konsistensi data. Disarankan untuk menggunakan mode `Task`
yang lebih aman secara data.

### Mengonfigurasi Resource Statis

Jika Anda ingin `Swoole` mengelola resource statis, silakan tambahkan
konfigurasi berikut di konfigurasi `config/autoload/server.php`.

```
return [
    'settings' => [
        ...
        // static resources
        'document_root' => BASE_PATH.'/public',
        'enable_static_handler' => true,
    ],
];

```

## View rendering engine

Engine rendering yang didukung secara resmi saat ini adalah `Blade`,
`Smarty`, `Twig`, `Plates`, dan `ThinkTemplate`. Templating engine tidak akan
diinstal secara otomatis saat [hyperf/view](https://github.com/hyperf/view)
diinstal. Anda perlu menginstal templating engine yang sesuai selain paket
view tersebut.

### Menginstal Blade Engine

```bash
composer require hyperf/view-engine
```

Untuk detailnya, silakan merujuk ke [dokumentasi view engine](id/view-engine.md).

Atau gunakan

> duncan3dc/blade menggunakan library Support milik Laravel, sehingga beberapa
> fungsi akan tidak kompatibel, sehingga tidak direkomendasikan untuk saat ini

```bash
composer require duncan3dc/blade
```

### Menginstal Smarty Engine

```bash
composer require smarty/smarty
```

### Menginstal Twig Engine

```bash
composer require twig/twig
```

### Menginstal Plates Engine

```bash
composer require league/plates
```

### Menginstal ThinkTemplate Engine

```bash
composer require sy-records/think-template
```

### Mengakses Template Lain

Misalkan kita ingin menghubungkan template engine virtual bernama
`TemplateEngine`, maka kita perlu membuat kelas `TemplateEngine` yang sesuai
di mana saja dan mengimplementasikan interface
`Hyperf\View\Engine\EngineInterface`.

```php
<?php

declare(strict_types=1);

namespace App\Engine;

use Hyperf\View\Engine\EngineInterface;

class TemplateEngine implements EngineInterface
{
    public function render($template, $data, $config): string
    {
        // instantiate instansi dari template engine yang sesuai
        $engine = new TemplateInstance();
        // dan panggil metode rendering yang sesuai
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
        'view_path' => BASE_PATH.'/storage/view/',
        'cache_path' => BASE_PATH.'/runtime/view/',
    ],
];
```

## Penggunaan

Berikut ini mengambil `BladeEngine` sebagai contoh. Pertama, buat file view
`index.blade.php` di direktori yang sesuai.

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hyperf</title>
</head>
<body>
Hello, {{ $name }}. You are using blade template now.
</body>
</html>
```

Dapatkan instansi `Hyperf\View\Render` di controller, lalu panggil metode
`render` dan teruskan alamat file view `index` dan `data rendering`. Alamat
file mengabaikan akhiran (suffix) dari file view.

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
        return $render->render('index', ['name' =>'Hyperf']);
    }
}

```

Kunjungi URL yang sesuai untuk mendapatkan halaman view seperti yang
ditunjukkan di bawah ini:

```
Hello, Hyperf. You are using blade template now.
```
