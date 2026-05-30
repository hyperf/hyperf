# Versi

## Aturan Versi

Hyperf menggunakan aturan penomoran versi `x.y.z` untuk menamai setiap versi,
seperti versi 1.2.3, di mana 1 adalah `x`, 2 adalah `y`, dan 3 adalah `z`. Anda
dapat membuat rencana pembaruan framework Hyperf berdasarkan aturan versi
tersebut.
- `x` menunjukkan versi major. Ketika core Hyperf mengalami banyak perubahan
  refactoring, atau ketika ada banyak perubahan API yang destruktif (breaking
  changes), versi tersebut akan dirilis sebagai versi `x`. Secara umum,
  perubahan versi `x` tidak kompatibel dengan versi `x` sebelumnya, namun tidak
  selalu berarti tidak kompatibel sepenuhnya. Identifikasi spesifik dilakukan
  berdasarkan panduan upgrade dari versi yang bersangkutan.
- `y` merepresentasikan versi iteratif dari fungsi utama. Ketika beberapa
  public API mengalami perubahan destruktif, termasuk perubahan atau
  penghapusan public API yang dapat menyebabkan ketidakkompatibelan dengan
  versi sebelumnya, versi tersebut akan dirilis sebagai versi `y`.
- `z` berarti versi perbaikan yang sepenuhnya kompatibel. Ketika
  perbaikan bug atau perbaikan keamanan dilakukan pada fungsi komponen yang
  sudah ada, versi `z` akan dipilih untuk dirilis. Ketika sebuah bug
  menyebabkan suatu fungsi sama sekali tidak dapat digunakan, ada kemungkinan
  saat memperbaiki bug ini di versi `z`, dilakukan perubahan destruktif
  pada API. Namun karena fungsi tersebut sebelumnya benar-benar tidak dapat
  digunakan, perubahan seperti itu tidak akan dirilis di versi `y`. Selain
  perbaikan bug, versi `z` juga dapat menyertakan beberapa fitur atau
  komponen baru, di mana fitur dan komponen baru ini tidak akan memengaruhi
  penggunaan kode sebelumnya.

## Upgrade

Ketika Anda ingin melakukan upgrade versi Hyperf, jika itu adalah upgrade ke
versi `x` atau `y`, silakan ikuti panduan upgrade untuk versi yang bersangkutan
di dalam dokumen. Jika Anda ingin melakukan upgrade versi `z`, Anda
dapat langsung menjalankan perintah `composer update hyperf` di direktori
root proyek Anda untuk memperbarui paket dependensi. Kami tidak menyarankan
Anda melakukan upgrade versi komponen tertentu secara terpisah, melainkan
meng-upgrade semua komponen bersama-sama untuk mendapatkan pengalaman
pengembangan yang lebih konsisten.
