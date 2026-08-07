# Plugin IDE

## Plugin PhpStorm

### Plugin Database

Anda dapat menginstal plugin [Hyperf Query](https://plugins.jetbrains.com/plugin/33333-hyperf-query) di PhpStorm untuk menyediakan integrasi database untuk query builder Hyperf. Plugin ini bekerja bersama DataGrip untuk menyediakan autocomplete untuk schema, tabel, view, dan kolom database:

- Autocomplete schema, tabel, view, dan kolom pada metode query builder dan schema builder
- Autocomplete untuk migrasi
- Inspeksi elemen database yang tidak dikenal
- Dukungan alias tabel
- Resolusi nama tabel dari model untuk metode builder
- Resolusi nama tabel relasi model untuk metode closure relasi builder
- Tautan teks dengan elemen database untuk navigasi (Ctrl+Click)
- Prefiks tabel dan filter data source yang dapat dikonfigurasi

Cara instalasi: `Preferences` > `Plugins` > `Marketplace`, cari **Hyperf Query**, lalu klik **Install Plugin**. Setelah terinstal, disarankan untuk mengonfigurasi data source terlebih dahulu, lalu filter data source yang akan digunakan di pengaturan plugin.

> Plugin ini merupakan fork dari [laravel-query-intellij](https://github.com/ekvedaras/laravel-query-intellij) (lisensi MIT), yang diadaptasi untuk menargetkan `Hyperf\Database\*` alih-alih `Illuminate\Database\*`. Kode sumber tersedia di [hyperf-query-intellij](https://github.com/tw2066/hyperf-query-intellij).
