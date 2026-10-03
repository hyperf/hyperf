# Membuat Komponen Baru

`Hyperf` menyediakan alat untuk membuat paket komponen dengan cepat.

```bash
# Buat paket komponen yang sesuai dengan versi terbaru Hyperf
composer create-project hyperf/component-creator your_component dev-master

# Buat paket komponen yang sesuai dengan versi Hyperf 2.0
composer create-project hyperf/component-creator your_component "2.0.*"
```

## Menggunakan Paket Komponen yang Belum Dipublikasikan di Proyek

Misalkan struktur direktori proyek sebagai berikut:

```
/opt/project // Direktori proyek
/opt/your_component // Direktori paket komponen
```

Asumsikan nama komponen adalah `your_component/your_component`.

Modifikasi `/opt/project/composer.json`:

> Konfigurasi lain yang tidak relevan dihilangkan di bawah ini.

```json
{
    "require": {
        "your_component/your_component": "dev-master"
    },
    "repositories": {
        "your_component": {
            "type": "path",
            "url": "/opt/your_component"
        }
    }
}
```

Terakhir, jalankan `composer update -o` di direktori `/opt/project`.
