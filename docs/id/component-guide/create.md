# Membuat Komponen Baru

`Hyperf` secara resmi menyediakan tools untuk membuat paket komponen dengan
cepat.

```
# Create a component package that adapts to the latest version of Hyperf
composer create-project hyperf/component-creator your_component dev-master

# Create a component package that adapts to Hyperf 2.0 version
composer create-project hyperf/component-creator your_component "2.0.*"
```

## Menggunakan paket komponen yang belum dipublikasikan di dalam proyek

Misalkan direktori proyek adalah sebagai berikut:

```
/opt/project // direktori proyek
/opt/your_component // direktori paket komponen
```

Mengasumsikan komponen tersebut bernama `your_component/your_component`.

Ubah `/opt/project/composer.json`:

> Konfigurasi lain yang tidak relevan diabaikan di bawah ini

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

Terakhir, jalankan `composer update -o` di dalam direktori `/opt/project`.
