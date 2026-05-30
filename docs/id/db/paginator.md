# Paginasi Query

Ketika menggunakan [hyperf/database](https://github.com/hyperf/database) untuk
melakukan query data, sangat mudah menggunakan komponen
[hyperf/paginator](https://github.com/hyperf/paginator) untuk mempaginasi hasil
query dengan mudah.

# Petunjuk Penggunaan

Ketika Anda melakukan query data melalui [Query Builder](id/db/querybuilder.md)
atau [Model](id/db/model.md), paginasi dapat ditangani melalui metode
`paginate`, yang secara otomatis menggunakan halaman yang sedang dilihat untuk
mengatur limit dan offset. Secara default, jumlah halaman saat ini dideteksi
dari nilai parameter `page` yang dibawa oleh HTTP request saat ini:

> Karena Hyperf saat ini tidak mendukung view, komponen paginasi belum
> mendukung perenderan view, dan hasil paginasi yang dikembalikan secara
> langsung akan secara default di-output dalam format application/json.

## Paginasi Query Builder

```php
<?php
// Show all users in the app, 10 pieces of data per page
return Db::table('users')->paginate(10);
```

## Paginasi Model

Anda dapat melakukan paginasi dengan memanggil metode `paginate` secara langsung
dari static method:

```php
<?php
// Show all users in the app, 10 pieces of data per page
return User::paginate(10);
```

Anda juga dapat mengatur kondisi query atau pengaturan query lainnya:

```php
<?php 
// Show all users in the app, 10 pieces of data per page
return User::where('gender', 1)->paginate(10);
```

## Metode Instance Paginator

Hanya penggunaan paginator dalam query database yang dijelaskan di sini.
Untuk detail lebih lanjut tentang paginator, silakan baca bab
[Paginasi](id/paginator.md).
