# Session Management

HTTP itu protokol stateless, artinya server gak nyimpen state apapun selama transaksi dengan client. Makanya, pas ngembangin aplikasi HTTP Server, kita biasanya pake Session buat sharing data user antar request. Anda bisa pake [hyperf/session](https://github.com/hyperf/session) buat implementasi Session. Komponen Session saat ini cuma support dua storage driver: `File` dan `Redis`. Defaultnya `File`. Di production, kami sangat rekomen `Redis` sebagai storage driver, soalnya performanya lebih baik dan cocok buat arsitektur cluster.

# Instalasi

```bash
composer require hyperf/session
```

# Konfigurasi

Konfigurasi Session component disimpan di `config/autoload/session.php`. Kalo filenya gak ada, tinggal publish ke Skeleton lewat `php bin/hyperf.php vendor:publish hyperf/session`.

## Mengonfigurasi Session Middleware

Sebelum pake Session, Anda perlu konfigurasi middleware `Hyperf\Session\Middleware\SessionMiddleware` sebagai global middleware di HTTP Server, biar komponen bisa nyusup ke proses request. Contoh konfigurasi `config/autoload/middlewares.php`:

```php
<?php

return [
    // 'http' di sini sesuai dengan nama server default. Jika Anda perlu menggunakan Session di server lain, Anda memerlukan konfigurasi global middleware yang sesuai
    'http' => [
        \Hyperf\Session\Middleware\SessionMiddleware::class,
    ],
];
```

## Mengonfigurasi Storage Driver

Ganti konfigurasi `handler` di file konfigurasi buat milih storage driver Session yang beda. Item konfigurasi spesifik tiap Handler diatur lewat `options`.

### Menggunakan File Storage Driver

> File storage driver adalah default, tapi disarankan pake Redis di production.

Kalo nilai `handler` adalah `Hyperf\Session\Handler\FileHandler`, berarti pake driver `File`. Semua file data Session bakal dibuat dan disimpan di folder sesuai `options.path`. Defaultnya di folder `runtime/session`.

### Menggunakan Redis Driver

Sebelum pake `Redis` storage driver, perlu install komponen [hyperf/redis](https://github.com/hyperf/redis). Kalo nilai `handler` adalah `Hyperf\Session\Handler\RedisHandler`, berarti pake driver `Redis`. Anda bisa atur koneksi `Redis` lewat `options.connection`. Koneksi ini cocok sama penamaan key di `config/autoload/redis.php` dari komponen [hyperf/redis](https://github.com/hyperf/redis).

# Penggunaan

## Mendapatkan Session Object

Anda bisa dapetin Session object dengan inject `Hyperf\Contract\SessionInterface`, lalu panggil method dari interface-nya:

```php
<?php

namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Contract\SessionInterface;

class IndexController
{
    #[Inject]
    private SessionInterface $session;

    public function index()
    {
        // Gunakan langsung melalui $this->session
    } 
}
```

## Menyimpan Data

Kalo mau nyimpen data ke Session, panggil aja method `set(string $name, $value): void`:

```php
<?php

$this->session->set('foo', 'bar');
```

## Mendapatkan Data

Kalo mau dapetin data dari Session, panggil aja method `get(string $name, $default = null)`:

```php
<?php

$this->session->get('foo', $default = null);
```

### Mendapatkan Semua Data

Bisa juga dapetin semua data Session sekaligus pake method `all(): array`:

```php
<?php

$data = $this->session->all();
```

## Menentukan Apakah Suatu Nilai Ada di Session

Buat ngecek apakah suatu nilai ada di Session, pake method `has(string $name): bool`. Kalo nilainya ada dan gak null, method `has` ngembaliin `true`:

```php
<?php

if ($this->session->has('foo')) {
    //
}
```

## Mendapatkan dan Menghapus Satu Data

Panggil method `remove(string $name)` buat dapetin sekaligus hapus satu data dari Session cuma pake satu method:

```php
<?php

$data = $this->session->remove('foo');
```

## Menghapus Satu atau Beberapa Data

Panggil method `forget(string|array $name): void` buat hapus satu atau beberapa data dari Session pake satu method. Kalo dikasih string, hapus satu data. Kalo dikasih array of strings, hapus beberapa data:

```php
<?php

$this->session->forget('foo');
$this->session->forget(['foo', 'bar']);
```

## Membersihkan Data Session Saat Ini

Kalo mau bersihin semua data Session saat ini, panggil method `clear(): void`:

```php
<?php

$this->session->clear();
```

## Mendapatkan Session ID Saat Ini

Kalo mau dapetin Session ID saat ini buat keperluan logika sendiri, panggil method `getId(): string`:

```php
<?php

$sessionId = $this->session->getId();
```
