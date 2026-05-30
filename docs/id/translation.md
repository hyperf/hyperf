# I18n

Dukungan internasionalisasi (internationalization) pada Hyperf sangat ramah,
memungkinkan proyek Anda untuk mendukung berbagai bahasa.

# Instalasi

```bash
composer require hyperf/translation
```

> Komponen ini merupakan komponen independen tanpa dependensi yang terkait dengan
> framework, dan dapat digunakan kembali secara terpisah untuk proyek atau
> framework lainnya.

# File Bahasa

Secara default, file bahasa Hyperf ditempatkan di bawah folder
`storage/languages`. Anda juga dapat mengubah folder file bahasa ini di
`config/autoload/translation.php`. Setiap bahasa memiliki subfoldernya
masing-masing, misalnya `en` merujuk pada file bahasa Inggris, `zh_CN` merujuk
pada file bahasa Mandarin Sederhana. Anda dapat membuat folder bahasa baru
beserta file bahasa di dalamnya sesuai dengan kebutuhan proyek Anda. Contoh
strukturnya adalah sebagai berikut:

```
/storage
    /languages
        /en
            messages.php
        /zh_CN
            messages.php
```

Semua file bahasa mengembalikan sebuah array dengan key berupa string:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome to our application',
];
```

## Mengonfigurasi Locale

### Mengonfigurasi Default Locale

Konfigurasi yang relevan untuk komponen internasionalisasi diatur dalam file
konfigurasi `config/autoload/translation.php`. Anda dapat mengubahnya sesuai
dengan kebutuhan Anda.

```php
<?php
// config/autoload/translation.php

return [
    // default language
    'locale' => 'zh_CN',
    // Fallback language, when the language text of the default language is not provided, the corresponding language text of the fallback language will be used
    'fallback_locale' => 'en',
    // Folder where language files are stored
    'path' => BASE_PATH . '/storage/languages',
];
```

### Mengonfigurasi Locale Sementara

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
        // Only valid for the current request or coroutine lifetime
        $this->translator->setLocale('zh_CN');
    }
}
```

# Menerjemahkan String

## Menerjemahkan melalui TranslatorInterface

Penerjemahan string dapat dilakukan secara langsung dengan melakukan inject
pada `Hyperf\Contract\TranslatorInterface` dan memanggil method `trans` dari
instance tersebut:

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

Anda juga dapat menerjemahkan string melalui fungsi global `__()` atau
`trans()`. Parameter pertama dari fungsi tersebut menerima format `key`
(merujuk pada key yang menggunakan string terjemahan sebagai key) atau
`file.key`.

```php
echo __('messages.welcome');
echo trans('messages.welcome');
```

# Mendefinisikan Placeholder dalam String Terjemahan

Anda juga dapat mendefinisikan placeholder dalam string bahasa, di mana semua
placeholder diawali dengan tanda `:`. Sebagai contoh, menggunakan username
sebagai placeholder:

```php
<?php
// storage/languages/en/messages.php

return [
    'welcome' => 'Welcome :name',
];
```

Ganti placeholder menggunakan parameter kedua dari fungsi:

```php
echo __('messages.welcome', ['name' => 'Hyperf']);
```

Jika placeholder menggunakan huruf kapital semua, atau huruf pertamanya
kapital, maka string hasil terjemahan juga akan disesuaikan dengan format
huruf kapital tersebut:

```php
'welcome' => 'Welcome, :NAME', // Welcome, HYPERF
'goodbye' => 'Goodbye, :Name', // Goodbye, Hyperf
```

# Menangani Bentuk Jamak (Plural)

Aturan bentuk jamak berbeda-beda di setiap bahasa. Hal ini mungkin tidak
terlalu diperhatikan dalam bahasa Mandarin, namun saat menerjemahkan bahasa
lain, kita perlu menangani bentuk jamak dari kata-kata tersebut. Kita dapat
menggunakan karakter pipe `"|"`, yang dapat digunakan untuk membedakan bentuk
tunggal dan jamak dari string:

```php
'apples' => 'There is one apple|There are many apples',
```

Anda juga dapat menentukan rentang angka untuk membuat aturan jamak yang lebih
kompleks:

```php
'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
```

Dengan menggunakan karakter pipe `"|"`, setelah aturan bentuk jamak
didefinisikan, fungsi global `trans_choice` dapat digunakan untuk mendapatkan
representasi string literal sesuai dengan `"amount"` (jumlah) yang diberikan.
Pada contoh berikut, karena jumlahnya lebih besar dari `1`, maka bentuk jamak
dari string terjemahan akan dikembalikan:

```php
echo trans_choice('messages.apples', 10);
```

Tentu saja, selain fungsi global `trans_choice()`, Anda juga dapat menggunakan
method `transChoice` dari `Hyperf\Contract\TranslatorInterface`.
