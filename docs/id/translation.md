# Internasionalisasi

Hyperf menyediakan dukungan yang sangat baik untuk internasionalisasi, memungkinkan project Anda mendukung banyak bahasa.

# Instalasi

```bash
composer require hyperf/translation
```

> Komponen ini adalah komponen independen yang tidak memiliki ketergantungan terkait framework dan dapat digunakan kembali secara independen di project atau framework lain.

# File Bahasa

Secara default, file bahasa Hyperf ditempatkan di bawah `storage/languages`. Anda juga dapat mengganti folder file bahasa di `config/autoload/translation.php`. Setiap bahasa sesuai dengan subdirektori, misalnya, `en` merujuk pada file bahasa Inggris, dan `zh_CN` merujuk pada file bahasa Mandarin Sederhana. Anda dapat membuat folder bahasa baru dan file bahasa di dalamnya sesuai dengan kebutuhan Anda. Contohnya adalah sebagai berikut:

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

Semua file bahasa mengembalikan array, di mana kunci dari array tersebut adalah string:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Selamat datang di aplikasi kami',
];
```

## Mengonfigurasi Lingkungan Bahasa

### Mengonfigurasi Lingkungan Bahasa Default

Semua konfigurasi yang terkait dengan komponen internasionalisasi diatur dalam file konfigurasi `config/autoload/translation.php`, yang dapat Anda ubah sesuai kebutuhan.

```php
<?php
// config/autoload/translation.php

return [
    // Bahasa default
    'locale' => 'zh_CN',
    // Bahasa cadangan, digunakan ketika teks bahasa dari bahasa default tidak tersedia
    'fallback_locale' => 'en',
    // Folder tempat file bahasa disimpan
    'path' => BASE_PATH . '/storage/languages',
];
```

### Mengonfigurasi Lingkungan Bahasa Sementara

```php
<?php

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\TranslatorInterface;

class FooController
{
    #[Inject]
    private TranslatorInterface $translator;
    
    public function index()
    {
        // Hanya berlaku dalam siklus hidup request atau coroutine saat ini
        $this->translator->setLocale('zh_CN');
    }
}
```

# Menerjemahkan String

## Menerjemahkan melalui TranslatorInterface

Anda dapat langsung menginjeksi `Hyperf\Contract\TranslatorInterface` dan memanggil metode `trans` dari instance untuk menerjemahkan string:

```php
<?php

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\TranslatorInterface;

class FooController
{
    #[Inject]
    private TranslatorInterface $translator;
    
    public function index()
    {
        return $this->translator->trans('messages.welcome', [], 'zh_CN');
    }
}
```

## Menerjemahkan melalui Fungsi Global

Anda juga dapat menggunakan fungsi global `__()` atau `trans()` untuk menerjemahkan string.
Argumen pertama dari fungsi menggunakan format `key` (merujuk pada kunci yang digunakan sebagai string terjemahan) atau format `file.key`.

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# Mendefinisikan Placeholder dalam String Terjemahan

Anda juga dapat mendefinisikan placeholder dalam string bahasa, di mana semua placeholder diawali dengan `:`. Misalnya, gunakan username sebagai placeholder:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Selamat datang :name',
];
```

Gunakan argumen kedua dari fungsi untuk mengganti placeholder:

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

Jika placeholder semuanya huruf besar, atau huruf pertama adalah huruf besar, string terjemahan juga akan dalam bentuk huruf besar yang sesuai:

```php
'welcome' => 'Selamat datang, :NAME', // Selamat datang, HYPERF
'goodbye' => 'Selamat tinggal, :Name', // Selamat tinggal, Hyperf
```

# Menangani Bentuk Jamak

Bahasa yang berbeda memiliki aturan bentuk jamak yang berbeda. Dalam bahasa Indonesia, kita mungkin tidak terlalu memperhatikan hal ini, tetapi kita perlu menangani bentuk jamak saat menerjemahkan bahasa lain. Kita dapat menggunakan karakter `「pipa」` untuk membedakan antara bentuk tunggal dan jamak dari sebuah string:

```php
'apples' => 'Ada satu apel|Ada banyak apel',
```

Anda juga dapat menentukan rentang numerik untuk membuat aturan bentuk jamak yang lebih kompleks:

```php
'apples' => '{0} Tidak ada|[1,19] Ada beberapa|[20,*] Ada banyak',
```

Setelah mendefinisikan aturan bentuk jamak menggunakan karakter `「pipa」`, Anda dapat menggunakan fungsi global `trans_choice` untuk mendapatkan teks string untuk `「jumlah」` yang diberikan. Dalam contoh di bawah, karena jumlahnya lebih besar dari `1`, bentuk jamak dari string terjemahan dikembalikan:

```php
echo trans_choice('messages.apples', 10);
```

Tentu saja, selain fungsi global `trans_choice()`, Anda juga dapat menggunakan metode `transChoice` dari `Hyperf\Contract\TranslatorInterface`.
