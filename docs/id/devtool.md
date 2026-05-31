# Developer Tools

## Instalasi

```
composer require hyperf/devtool
```

## Perintah yang Didukung

```bash
php bin/hyperf.php
```

Dengan menjalankan perintah di atas, Anda bisa melihat semua perintah yang didukung. Seri perintah `gen` dan perintah `vendor:publish` disediakan oleh komponen `devtool`.

```bash
 gen
  gen:amqp-consumer  Buat kelas amqp consumer baru
  gen:amqp-producer  Buat kelas amqp producer baru
  gen:aspect         Buat kelas aspect baru
  gen:command        Buat kelas command baru
  gen:controller     Buat kelas controller baru
  gen:job            Buat kelas job baru
  gen:listener       Buat kelas listener baru
  gen:middleware     Buat kelas middleware baru
  gen:process        Buat kelas process baru
 vendor
  vendor:publish     Publikasikan konfigurasi yang dapat dipublikasikan dari paket vendor.
```

## Quick Open

Fitur yang sangat sederhana telah ditambahkan untuk membuka file yang dibuat dengan cepat menggunakan perintah `gen` bawaan. Mendukung `sublime`, `textmate`, `cursor`, `emacs`, `macvim`, `phpstorm`, `idea`, `vscode`, `vscode-insiders`, `vscode-remote`, `vscode-insiders-remote`, `atom`, `nova`, `netbeans`, `xdebug`.

Anda juga perlu menambahkan blok konfigurasi ini ke `config/autoload/devtool.php`:

```php
return [
    /**
     * IDE yang didukung: "sublime", "textmate", "cursor", "emacs", "macvim", "phpstorm", "idea",
     *        "vscode", "vscode-insiders", "vscode-remote", "vscode-insiders-remote",
     *        "atom", "nova", "netbeans", "xdebug"
     */
    'ide' => env('DEVTOOL_IDE', ''),
    //...
];
```
