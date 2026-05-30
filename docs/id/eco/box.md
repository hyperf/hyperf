# box, oleh Hyperf

Box berkomitmen untuk membantu meningkatkan pengalaman pemrograman aplikasi PHP,
khususnya untuk Hyperf, mengelola lingkungan PHP dan dependency terkait,
menyediakan kemampuan untuk mengemas aplikasi PHP menjadi program binary, dan
juga menyediakan layanan reverse proxy untuk mengelola dan mendeploy aplikasi
Swoole/Swow.

## Ini masih versi eksperimental awal, selamat menikmati ~

### Penggunaan

#### Menginstal box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```
##### Linux aarch64

Saat ini, kami kekurangan AARCH64 Github Actions Runner, sehingga kami tidak
dapat membuat file bin versi AARCH64 secara tepat waktu.

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// Put `box.exe` into any path in $PATH env that you want, and use `box.exe` instead of `box` when executing on Windows
```

#### Inisialisasi Github Access Token

Box memerlukan Github Access Token untuk membuat request ke API GitHub guna
mengambil versi package.

1. [Buat Github Access Token](https://github.com/settings/tokens/new), scope `workflow` harus dipilih.
2. Jalankan `box config set github.access-token <Token Anda>` untuk menginisialisasi token.
3. Selamat menikmati ~

#### Mengatur Box Kernel

Secara default, Box didukung oleh Swow Kernel, tetapi kami juga menyediakan
Swoole Kernel. Anda dapat beralih ke Swoole Kernel dengan perintah
`box config set kernel swoole`. Namun, perlu dicatat bahwa Swoole Kernel
hanya mendukung PHP versi 8.1, serta fitur Build Binaries dan sistem Windows
tidak didukung.

```bash
// set to Swow Kernel [default]
box config set kernel swow

// set to Swoole Kernel (NOT supported on Windows)
box config set kernel swoole
```

### Perintah

- `box get pkg@version` untuk menginstal package dari remote secara otomatis,
  `pkg` adalah nama package, dan `version` adalah versi package. `box get pkg`
  berarti menginstal versi terbaru dari pkg, contohnya, jalankan `box get php@8.1`
  untuk menginstal PHP 8.1, jalankan `box get composer` untuk menginstal composer
  bin terbaru
- `box build-prepare` untuk bersiap melakukan perintah `build` dan `build-self`
- `box build-self` untuk melakukan build file bin `box` itu sendiri
- `box build <path>` untuk melakukan build aplikasi Hyperf menjadi file binary
- `box self-update` untuk memperbarui bin `box` ke versi terbaru
- `box config list` untuk menampilkan seluruh isi file konfigurasi
- `box config get <key>` untuk mengambil nilai berdasarkan key dari file
  konfigurasi
- `box config set <key> <value>` untuk mengatur nilai berdasarkan key ke dalam
  file konfigurasi
- `box config unset <key>` untuk menghapus nilai konfigurasi berdasarkan key
- `box config set-php-version <version>` untuk mengatur versi PHP aktif pada box,
  nilai yang tersedia: 8.0 | 8.1
- `box config get-php-version <version>` untuk mendapatkan versi PHP aktif pada
  box
- `box reverse-proxy -u <upstreamHost:upstreamPort>` untuk memulai server HTTP
  reverse proxy untuk upstream server
- `box php <argument>` untuk menjalankan perintah PHP apa pun menggunakan versi
  PHP aktif pada box
- `box composer <argument>` untuk menjalankan perintah Composer apa pun melalui
  box, versi dari file bin composer bergantung pada perintah `get composer`
  yang terakhir dijalankan
- `box php-cs-fixer <argument>` untuk menjalankan perintah `php-cs-fixer` apa
  pun melalui box, versi dari file bin composer bergantung pada perintah
  `get php-cs-fixer` yang terakhir dijalankan
- `box cs-fix <argument>` untuk menjalankan perintah `php-cs-fix fix` melalui
  box, versi dari file bin composer bergantung pada perintah `get php-cs-fixer`
  yang terakhir dijalankan
- `box phpstan <argument>` untuk menjalankan perintah `phpstan` apa pun melalui
  box, versi dari file bin composer bergantung pada perintah `get phpstan` yang
  terakhir dijalankan, sejak box v0.3.0
- `box pint <argument>` untuk menjalankan perintah `pint` apa pun melalui box,
  versi dari file bin composer bergantung pada perintah `get pint` yang terakhir
  dijalankan, sejak box v0.3.0
- `box version` untuk menampilkan versi aktif dari file bin box

### Tentang Swow Skeleton

Jika Anda ingin merasakan fitur lengkap Box, Anda harus menjalankannya
berdasarkan Swow Kernel, sehingga Anda perlu mendasarkan proyek Anda pada
[hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) untuk menjalankan
proyek Anda. Anda dapat membuat proyek Swow skeleton berdasarkan Hyperf versi
3.0 RC dengan perintah `box composer create-project hyperf/swow-skeleton:dev-master`.
