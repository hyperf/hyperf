# Database Pagination

Saat menggunakan [hyperf/database](https://github.com/hyperf/database) untuk query data, Anda bisa dengan mudah melakukan pagination dengan component [hyperf/paginator](https://github.com/hyperf/paginator).

# Cara Penggunaan

Ketika query data melalui [Query Builder](id/db/querybuilder.md) atau [Model](id/db/model.md), gunakan method `paginate` untuk pagination. Method ini otomatis mengatur limit dan offset berdasarkan halaman yang dilihat user. Secara default, nomor halaman dideteksi dari nilai parameter `page` di HTTP request:

> Karena Hyperf belum mendukung views, pagination component belum bisa me-render view. Mengembalikan hasil pagination langsung akan menghasilkan output `application/json` secara default.

## Query Builder Pagination

```php
<?php
// Tampilkan semua user di aplikasi, menampilkan 10 item per halaman
return Db::table('users')->paginate(10);
```

## Model Pagination

Anda bisa memanggil method `paginate` langsung melalui static method untuk melakukan pagination:

```php
<?php
// Tampilkan semua user di aplikasi, menampilkan 10 item per halaman
return User::paginate(10);
```

Tentu saja, Anda juga bisa mengatur query conditions atau pengaturan query lainnya:

```php
<?php 
// Tampilkan semua user di aplikasi, menampilkan 10 item per halaman
return User::where('gender', 1)->paginate(10);
```

## Paginator Instance Methods

Bagian ini hanya menjelaskan penggunaan paginator dalam database queries. Untuk detail lebih lanjut tentang paginator, silakan baca bab [Pagination](id/paginator.md).
