# Plugin IDE

## Plugin PhpStorm

### Plugin Hyperf Base

Anda dapat menginstal plugin [Hyperf Base](https://github.com/tw2066/idea-plugin-hyperf) di PhpStorm untuk mendapatkan dukungan code completion, navigasi, dan perintah cepat untuk framework Hyperf. Fitur utama:

- Routing: autocomplete dan navigasi untuk `Controller@action` pada `Router::get/post/...`
- Key konfigurasi: indexing, autocomplete, dan navigasi untuk helper `config()` dan `ConfigInterface::get()/has()` (mendukung subdirektori 3.1+ dan nama file dengan titik)
- Key terjemahan: indexing, autocomplete, dan navigasi untuk `trans()` / `__()` dan `TranslatorInterface::trans()`
- Environment variable: autocomplete dan navigasi untuk key `env()` (mengindex file `.env` proyek)
- Aturan validasi: autocomplete dan dokumentasi hover berbahasa China untuk string aturan di `FormRequest::rules()`, `ValidatorFactory::make()/validate()`, dan `$scenes`
- Path BASE_PATH: autocomplete dan navigasi untuk direktori/file dalam rangkaian konkatenasi `BASE_PATH . '/a/b'`
- Template view: autocomplete dan navigasi untuk nama template di `view()`, `RenderInterface::render()`, dll. (sintaks titik + namespace `pkg::name`)
- Aspek AOP: navigasi dan autocomplete nama method untuk string `'FQN::method'` di atribut `#[Aspect]` dan properti `AbstractAspect`
- Listener cache: autocomplete dan navigasi timbal balik untuk nama listener antara `#[Cacheable(listener: "...")]` dan `DeleteListenerEvent`
- DI binding: hover pada interface menampilkan tautan ke class implementasi yang berlaku di popup dokumentasi
- Crontab: autocomplete dan navigasi untuk nama method `callback`; hover pada ekspresi `rule` menampilkan 5 waktu eksekusi berikutnya
- Menu top-level Hyperf: menjalankan code generation (`gen:*`) dan perintah umum (`migrate`, `start`, `describe:routes`, dll.) di Terminal bawaan dengan satu klik
- Line marker command: tombol run di samping nama class subclass `Hyperf\Command\Command`; klik untuk langsung menjalankan command

> Hanya mendukung PhpStorm 2026.2 ke atas

### Plugin Database

Anda dapat menginstal plugin [Hyperf Query](https://github.com/tw2066/hyperf-query-intellij) di PhpStorm untuk menyediakan integrasi database untuk query builder Hyperf. Plugin ini bekerja bersama DataGrip untuk menyediakan autocomplete untuk schema, tabel, view, dan kolom database:

- Autocomplete schema, tabel, view, dan kolom pada metode query builder dan schema builder
- Autocomplete untuk migrasi
- Inspeksi elemen database yang tidak dikenal
- Dukungan alias tabel
- Resolusi nama tabel dari model untuk metode builder
- Resolusi nama tabel relasi model untuk metode closure relasi builder
- Tautan teks dengan elemen database untuk navigasi (Ctrl+Click)
- Prefiks tabel dan filter data source yang dapat dikonfigurasi

Cara instalasi: `Preferences` > `Plugins` > `Marketplace`, cari **Hyperf Base** / **Hyperf Query**, lalu klik **Install Plugin**. Setelah terinstal, disarankan untuk mengonfigurasi data source terlebih dahulu, lalu filter data source yang akan digunakan di pengaturan plugin.
