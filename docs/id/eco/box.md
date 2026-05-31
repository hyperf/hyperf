# box, by Hyperf

Box bertujuan untuk meningkatkan pengalaman pemrograman aplikasi PHP, terutama untuk aplikasi Hyperf. Box mengelola environment PHP dan dependensi terkait, menyediakan kemampuan untuk mengemas aplikasi PHP menjadi program biner, dan juga menyediakan layanan reverse proxy untuk mengelola dan men-deploy service Swoole/Swow.

### Penggunaan

#### Install box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Pastikan /usr/local/bin/box ada di $PATH Anda, atau letakkan `box` di direktori $PATH mana pun yang Anda suka.
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Pastikan /usr/local/bin/box ada di $PATH Anda, atau letakkan `box` di direktori $PATH mana pun yang Anda suka.
```

##### Linux aarch64

Saat ini kami tidak memiliki Github Actions Runner AARCH64, sehingga tidak dapat membangun file biner versi AARCH64 secara tepat waktu.

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Pastikan /usr/local/bin/box ada di $PATH Anda, atau letakkan `box` di direktori $PATH mana pun yang Anda suka.
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// Letakkan `box.exe` di direktori variabel environment Path mana pun yang Anda suka. Perhatikan bahwa di Windows, Anda perlu menggunakan `box.exe` alih-alih `box` di baris perintah.
```

#### Inisialisasi Github Access Token

Box memerlukan Github Access Token untuk meminta API Github guna mengambil versi package.

1. [Buat Github Access Token](https://github.com/settings/tokens/new), dan scope `workflow` perlu dicentang;
2. Jalankan perintah `box config set github.access-token <Token Anda>` untuk mengatur token Anda;
3. Selamat bersenang-senang ~

#### Mengatur Box Kernel

Secara default, Box dijalankan oleh Swow Kernel, tetapi kami juga menyediakan Swoole Kernel. Anda dapat beralih ke Swoole Kernel melalui `box config set kernel swoole`. Perhatikan bahwa Swoole Kernel hanya mendukung PHP 8.1, dan tidak mendukung pembangunan program biner atau lingkungan sistem Windows.

```bash
// Set ke Swow Kernel [Default]
box config set kernel swow

// Set ke Swoole Kernel (Windows tidak didukung)
box config set kernel swoole
```

### Perintah

- `box get pkg@version` Menginstall package dari sumber jarak jauh. `pkg` adalah nama package, `version` adalah versi package. `box get pkg` berarti menginstall versi terbaru dari pkg. Misalnya, jalankan `box get php@8.1` untuk menginstall PHP 8.1, jalankan `box get composer` untuk menginstall biner composer terbaru.
- `box build-prepare` Mempersiapkan environment yang relevan untuk perintah `build` dan `build-self`.
- `box build-self` Membangun biner `box` itu sendiri.
- `box build <path>` Membangun aplikasi Hyperf menjadi program biner.
- `box self-update` Memperbarui biner `box` ke versi terbaru.
- `box config list` Menampilkan semua konten dari file konfigurasi box.
- `box config get <key>` Mengambil nilai berdasarkan key dari file konfigurasi.
- `box config set <key> <value>` Mengatur nilai ke file konfigurasi berdasarkan key.
- `box config unset <key>` Menghapus nilai konfigurasi berdasarkan key.
- `box config set-php-version <version>` Mengatur versi PHP saat ini untuk box. Nilai yang tersedia: 8.0 | 8.1
- `box config get-php-version <version>` Mendapatkan versi PHP yang saat ini diatur untuk box.
- `box reverse-proxy -u <upsteamHost:upstreamPort>` Memulai HTTP server reverse proxy untuk meneruskan HTTP request ke beberapa upstream server yang ditentukan.
- `box php <argument>` Menjalankan perintah PHP apa pun melalui versi PHP box saat ini.
- `box composer <argument>` Menjalankan perintah Composer apa pun melalui versi PHP box saat ini. Versi biner composer tergantung pada perintah `get composer` yang terakhir dijalankan.
- `box php-cs-fixer <argument>` Menjalankan perintah `php-cs-fixer` apa pun melalui versi PHP box saat ini. Versi biner composer tergantung pada perintah `get php-cs-fixer` yang terakhir dijalankan.
- `box cs-fix <argument>` Menjalankan perintah `php-cs-fixer fix` melalui versi PHP box saat ini. Versi biner composer tergantung pada perintah `get php-cs-fixer` yang terakhir dijalankan.
- `box phpstan <argument>` Menjalankan perintah `phpstan` apa pun melalui versi PHP box saat ini. Versi biner composer tergantung pada perintah `get phpstan` yang terakhir dijalankan. Perintah ini hanya tersedia di box v0.3.0 dan yang lebih tinggi.
- `box pint <argument>` Menjalankan perintah `pint` apa pun melalui versi PHP box saat ini. Versi biner composer tergantung pada perintah `get pint` yang terakhir dijalankan. Perintah ini hanya tersedia di box v0.3.0 dan yang lebih tinggi.
- `box version` Menampilkan nomor versi dari biner `box` saat ini.

### Tentang Swow-Skeleton

Teman-teman yang ingin merasakan fungsionalitas penuh Box perlu menjalankannya melalui Swow Kernel, jadi Anda perlu menjalankan project Anda berdasarkan [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton). Anda dapat membuat project Swow skeleton berbasis Hyperf 3.0 melalui perintah `box composer create-project hyperf/swow-skeleton`.
