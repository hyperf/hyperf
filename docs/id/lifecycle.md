# Siklus Hidup

## Siklus Hidup Framework

Hyperf berjalan di atas [Swoole](http://github.com/swoole/swoole-src). Makanya, untuk memahami lifecycle Hyperf sepenuhnya, Anda perlu paham dulu lifecycle [Swoole](http://github.com/swoole/swoole-src).

Manajemen command di Hyperf menggunakan [symfony/console](https://github.com/symfony/console) secara default (kalau mau diganti, Anda bisa ubah entry file skeleton sesuai komponen yang diinginkan). Saat `php bin/hyperf.php start` dijalankan, kelas command `Hyperf\Server\Command\StartServer` akan mengambil alih dan menjalankan satu per satu `Server` yang sudah didefinisikan di `config/autoload/server.php`.

Inisialisasi dependency injection container sengaja tidak dibungkus dalam komponen tersendiri karena akan menimbulkan kopling yang besar. Secara default, inisialisasi DI container dilakukan melalui entry file yang me-load `config/container.php`.

## Request dan Coroutine Lifecycle

Secara default, Swoole membuat satu coroutine untuk setiap koneksi, terutama pada event `onRequest`, `onReceive`, dan `onConnect`. Jadi bisa dibilang, setiap request adalah sebuah coroutine. Karena pembuatan coroutine adalah operasi yang ringan, satu coroutine request bisa mengandung banyak coroutine lain di dalamnya. Dalam process yang sama, coroutine berbagi memory tapi urutan eksekusinya tidak sequential. Coroutine pada dasarnya independen satu sama lain dan tidak punya hubungan parent-child. Karena itu, state setiap coroutine perlu dikelola melalui [Coroutine Context](id/coroutine.md#coroutine-context).
