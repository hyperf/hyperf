# Dev Tool

## Instalasi

```
composer require hyperf/devtool
```

# Command yang Didukung

```bash
php bin/hyperf.php
```

Semua command yang didukung oleh Command dapat didaftarkan dengan menjalankan
command di atas. Rangkaian command di bawah `gen` dan `vendor:publish` terutama
menyediakan dukungan untuk komponen `devtool`.

```
 gen
  gen:amqp-consumer  Create a new amqp consumer class
  gen:amqp-producer  Create a new amqp producer class
  gen:aspect         Create a new aspect class
  gen:command        Create a new command class
  gen:controller     Create a new controller class
  gen:job            Create a new job class
  gen:listener       Create a new listener class
  gen:middleware     Create a new middleware class
  gen:process        Create a new process class
 vendor
  vendor:publish     Publish any publishable configs from vendor packages.
```

## Buka Cepat

Menambahkan fungsi yang sangat sederhana untuk membuka file yang dibuat secara
cepat dengan command bawaan `gen`, mendukung `sublime`, `textmate`, `cursor`,
`emacs`, `macvim`, `phpstorm`, `idea`, `vscode`, `vscode-insiders`,
`vscode-remote`, `vscode-insiders-remote`, `atom`, `nova`, `netbeans`, `xdebug`.

Anda juga perlu menambahkan blok konfigurasi ini pada
`config/autoload/devtool.php`:

```php
return [
    /**
     * Supported IDEs: "sublime", "textmate", "cursor", "emacs", "macvim", "phpstorm", "idea",
     *        "vscode", "vscode-insiders", "vscode-remote", "vscode-insiders-remote",
     *        "atom", "nova", "netbeans", "xdebug"
     */
    'ide' => env('DEVTOOL_IDE', ''),
    //...
];
```
