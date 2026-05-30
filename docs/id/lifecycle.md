# Siklus Hidup

## Siklus Hidup Framework

Hyperf berbasis pada [Swoole](http://github.com/swoole/swoole-src). Untuk
memahami siklus hidup dari Hyperf, memahami siklus hidup
[Swoole](http://github.com/swoole/swoole-src) juga sangat krusial.

Manajemen command Hyperf didukung oleh
[symfony/console](https://github.com/symfony/console) secara default *(jika
Anda ingin mengganti komponen ini, Anda juga dapat mengubah entry file dari
skeleton ke komponen yang ingin Anda gunakan)*. Setelah menjalankan
`php bin/hyperf.php start`, proses akan diambil alih oleh class command
`Hyperf\Server\Command\StartServer` dan dijalankan satu per satu sesuai dengan
`Server` yang didefinisikan dalam file konfigurasi
`config/autoload/server.php`.

Mengenai inisialisasi container dependency injection, kami tidak menerapkannya
melalui komponen tertentu, karena begitu diterapkan oleh suatu komponen,
coupling akan menjadi sangat jelas. Oleh karena itu secara default, file
konfigurasi `config/container.php` dimuat oleh entry file untuk
menginisialisasi container.

## Siklus Hidup Request dan Coroutine

Ketika Swoole menangani setiap koneksi, secara default Swoole akan membuat
sebuah coroutine untuk menanganinya, terutama pada event `onRequest`,
`onReceive`, dan `onConnect`. Oleh karena itu, dapat dipahami bahwa setiap
request adalah sebuah coroutine. Karena pembuatan coroutine merupakan operasi
yang normal, sebuah request coroutine dapat berisi banyak coroutine lainnya.
Coroutine dalam process yang sama berbagi memori (memory shared), namun urutan
penjadwalannya tidak berurutan, dan coroutine pada dasarnya independen satu
sama lain tanpa adanya hubungan parent-child. Oleh karena itu, pemrosesan state
untuk setiap coroutine perlu dikelola oleh
[Coroutine Context](id/coroutine.md#coroutine-context).
