# Version Description

## Version Rules

Hyperf menggunakan aturan penomoran versi x.y.z untuk semua versi, misalnya versi 1.2.3, di mana 1 adalah x, 2 adalah y, dan 3 adalah z. Anda bisa menyusun rencana pembaruan framework Hyperf berdasarkan aturan ini.

- x mewakili major version. Ketika core Hyperf mengalami refactoring besar, atau ada banyak perubahan API yang bersifat breaking, maka akan dirilis sebagai x version. Perubahan di x version umumnya tidak kompatibel dengan x version sebelumnya, tapi tidak selalu sepenuhnya tidak kompatibel. Detailnya harus diverifikasi sesuai upgrade guide versi terkait.
- y mewakili major feature iteration version. Ketika beberapa public API memiliki perubahan breaking, termasuk perubahan atau penghapusan public API, yang berpotensi tidak kompatibel dengan versi sebelumnya, maka akan dirilis sebagai y version.
- z mewakili fully compatible fix version. Ketika ada perbaikan bug atau keamanan pada fitur yang ada, akan dirilis sebagai z version. Jika sebuah bug menyebabkan fitur tidak bisa digunakan sama sekali, perubahan breaking pada API bisa saja dilakukan dalam z version. Namun, karena fiturnya sudah tidak bisa dipakai, perubahan semacam itu tidak akan dirilis sebagai y version. Selain perbaikan bug, z version juga bisa menyertakan fitur atau komponen baru tanpa memengaruhi kode sebelumnya.

## Upgrading Versions

Ketika Anda ingin meningkatkan versi Hyperf, untuk upgrade ke x dan y version, ikuti upgrade guide untuk versi yang sesuai di dokumentasi. Jika ingin upgrade ke z version, langsung jalankan `composer update hyperf` di direktori root proyek untuk memperbarui paket dependensi. Kami tidak menyarankan upgrade satu komponen secara terpisah; sebaiknya upgrade semua komponen secara seragam untuk pengalaman yang lebih konsisten.
