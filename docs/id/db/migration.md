# Database Migration

Database migration adalah version control untuk struktur database, solusi efektif untuk mengelola perubahan struktur database di antara anggota tim.

> Lokasi deklarasi script terkait sudah dipindahkan dari komponen `database` ke `devtool`. Jadi di lingkungan online `--no-dev`, Anda perlu menulis manual perintah yang dapat dijalankan ke konfigurasi `autoload/commands.php`.

## Generate Migration

Hasilkan file migration dengan `gen:migration`. Parameter nama file mengikuti perintah, biasanya mendeskripsikan tujuan migration.

```bash
php bin/hyperf.php gen:migration create_users_table
```

File migration yang dihasilkan berada di folder `migrations` di root direktori. Setiap file berisi timestamp sehingga program migration tahu urutannya.

Opsi `--table` digunakan untuk menentukan nama tabel, nama tersebut akan otomatis muncul di file migration.
Opsi `--create` juga untuk menentukan nama tabel, bedanya opsi ini menghasilkan file migration untuk membuat tabel baru, sementara `--table` untuk memodifikasi tabel yang sudah ada.

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

## Migration Structure

Class migration berisi `2` method bawaan: `up` dan `down`.
Method `up` digunakan untuk menambahkan tabel, kolom, atau index baru ke database. Method `down` adalah kebalikannya, membatalkan apa yang dilakukan `up` agar bisa dijalankan saat rollback.

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Jalankan migrasinya.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasinya.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
```

## Running Migrations

Jalankan semua file migration yang tertunda dengan mengeksekusi perintah `migrate`:

```bash
php bin/hyperf.php migrate
```

### Force Migration Execution

Beberapa operasi migration bersifat destruktif dan berpotensi menghilangkan data. Untuk mencegah eksekusi di production, sistem akan meminta konfirmasi. Jika Anda yakin dan ingin memaksakan perintahnya, gunakan flag `--force`:

```bash
php bin/hyperf.php migrate --force
```

### Rollback Migrations

Untuk rollback migrasi terakhir, gunakan `migrate:rollback`. Perhatikan bahwa satu migrasi bisa berisi beberapa file:

```bash
php bin/hyperf.php migrate:rollback
```

Juga bisa menambahkan parameter `step` untuk mengatur jumlah rollback. Misalnya, perintah berikut rollback 5 migrasi terakhir:

```bash
php bin/hyperf.php migrate:rollback --step=5
```

Untuk rollback semua migrasi, gunakan `migrate:reset`:

```bash
php bin/hyperf.php migrate:reset
```

### Rollback dan Migrate

Perintah `migrate:refresh` melakukan rollback lalu menjalankan `migrate` kembali, cocok untuk membangun ulang migrasi tertentu secara efisien:

```bash
php bin/hyperf.php migrate:refresh

// Bangun ulang struktur database dan jalankan data seeding
php bin/hyperf.php migrate:refresh --seed
```

Atur jumlah rollback dan rebuild dengan `--step`. Contoh berikut rollback dan menjalankan ulang 5 migrasi terakhir:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

### Rebuild Database

Bangun ulang seluruh database dengan `migrate:fresh`. Perintah ini akan menghapus semua tabel lalu menjalankan `migrate`:

```bash
php bin/hyperf.php migrate:fresh

// Bangun ulang struktur database dan jalankan data seeding
php bin/hyperf.php migrate:fresh --seed
```

## Tables

Di file migration, class `Hyperf\Database\Schema\Schema` digunakan untuk mendefinisikan tabel dan mengelola proses migration.

### Creating Tables

Buat tabel baru dengan method `create`. Method ini menerima dua argumen: nama tabel dan `Closure` yang menerima objek `Hyperf\Database\Schema\Blueprint` untuk mendefinisikan tabel:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }
}
```

Gunakan perintah berikut pada schema builder untuk mendefinisikan opsi tabel:

```php
// Tentukan storage engine tabel
$table->engine = 'InnoDB';
// Tentukan default character set untuk tabel
$table->charset = 'utf8';
// Tentukan default collation untuk tabel
$table->collation = 'utf8_unicode_ci';
// Buat temporary table
$table->temporary();
```

### Renaming Tables

Untuk mengganti nama tabel, gunakan method `rename`:

```php
Schema::rename($from, $to);
```

#### Renaming Tables dengan Foreign Keys

Sebelum rename tabel, pastikan semua foreign key constraint memiliki nama eksplisit di file migration, jangan biarkan program migration mengaturnya secara otomatis. Jika tidak, nama constraint akan tetap mereferensikan nama tabel lama.

### Deleting Tables

Untuk menghapus tabel, gunakan method `drop` atau `dropIfExists`:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

### Checking for Table atau Column Existence

Cek apakah tabel atau kolom ada dengan method `hasTable` dan `hasColumn`:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('users', 'email')) {
    //
}
```

### Database Connection Options

Jika Anda mengelola beberapa database sekaligus dengan koneksi berbeda untuk tiap migration, Anda bisa mendefinisikan koneksi di file migration dengan override atribut class `$connection`:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // Ini merujuk pada connection key di config/autoload/databases.php
    protected $connection = 'foo';
    
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }
}
```

## Columns

### Creating Columns

Tulis definisi atau perubahan yang akan dijalankan di dalam `Closure` pada argumen kedua method `table` atau `create`. Misalnya:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{   
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('name');
        });
    }
}
```

### Available Column Definitions

| Perintah | Deskripsi |
| ---------------------------------------- | ------------------------------------------------------- |
| $table->bigIncrements('id'); | Increment ID (primary key), setara dengan UNSIGNED BIG INTEGER |
| $table->bigInteger('votes'); | Setara dengan BIGINT |
| $table->binary('data'); | Setara dengan BLOB |
| $table->boolean('confirmed'); | Setara dengan BOOLEAN |
| $table->char('name', 100); | Setara dengan CHAR dengan panjang |
| $table->date('created_at'); | Setara dengan DATE |
| $table->dateTime('created_at'); | Setara dengan DATETIME |
| $table->dateTimeTz('created_at'); | Setara dengan DATETIME dengan timezone |
| $table->decimal('amount', 8, 2); | Setara dengan DECIMAL dengan presisi dan skala |
| $table->double('amount', 8, 2); | Setara dengan DOUBLE dengan presisi dan skala |
| $table->enum('level', ['easy', 'hard']); | Setara dengan ENUM |
| $table->float('amount', 8, 2); | Setara dengan FLOAT dengan presisi dan skala |
| $table->geometry('positions'); | Setara dengan GEOMETRY |
| $table->geometryCollection('positions'); | Setara dengan GEOMETRYCOLLECTION |
| $table->increments('id'); | Increment ID (primary key), setara dengan UNSIGNED INTEGER |
| $table->integer('votes'); | Setara dengan INTEGER |
| $table->ipAddress('visitor'); | Setara dengan IP address |
| $table->json('options'); | Setara dengan JSON |
| $table->jsonb('options'); | Setara dengan JSONB |
| $table->lineString('positions'); | Setara dengan LINESTRING |
| $table->longText('description'); | Setara dengan LONGTEXT |
| $table->macAddress('device'); | Setara dengan MAC address |
| $table->mediumIncrements('id'); | Increment ID (primary key), setara dengan UNSIGNED MEDIUM INTEGER |
| $table->mediumInteger('votes'); | Setara dengan MEDIUMINT |
| $table->mediumText('description'); | Setara dengan MEDIUMTEXT |
| $table->morphs('taggable'); | Menambahkan taggable_id incrementing dan taggable_type string |
| $table->multiLineString('positions'); | Setara dengan MULTILINESTRING |
| $table->multiPoint('positions'); | Setara dengan MULTIPOINT |
| $table->multiPolygon('positions'); | Setara dengan MULTIPOLYGON |
| $table->nullableMorphs('taggable'); | Versi nullable dari kolom morphs() |
| $table->nullableTimestamps(); | Versi nullable dari kolom timestamps() |
| $table->point('position'); | Setara dengan POINT |
| $table->polygon('positions'); | Setara dengan POLYGON |
| $table->rememberToken(); | Menambahkan kolom remember_token VARCHAR(100) nullable |
| $table->smallIncrements('id'); | Increment ID (primary key), setara dengan UNSIGNED SMALL INTEGER |
| $table->smallInteger('votes'); | Setara dengan SMALLINT |
| $table->softDeletes(); | Menambahkan kolom deleted_at nullable untuk soft deletes |
| $table->softDeletesTz(); | Menambahkan kolom deleted_at nullable dengan timezone untuk soft deletes |
| $table->string('name', 100); | Setara dengan VARCHAR dengan panjang |
| $table->text('description'); | Setara dengan TEXT |
| $table->time('sunrise'); | Setara dengan TIME |
| $table->timeTz('sunrise'); | Setara dengan TIME dengan timezone |
| $table->timestamp('added_on'); | Setara dengan TIMESTAMP |
| $table->timestampTz('added_on'); | Setara dengan TIMESTAMP dengan timezone |
| $table->timestamps(); | Kolom created_at dan updated_at TIMESTAMP nullable |
| $table->timestampsTz(); | Kolom created_at dan updated_at TIMESTAMP nullable dengan timezone |
| $table->tinyIncrements('id'); | Setara dengan auto-incrementing UNSIGNED TINYINT |
| $table->tinyInteger('votes'); | Setara dengan TINYINT |
| $table->unsignedBigInteger('votes'); | Setara dengan Unsigned BIGINT |
| $table->unsignedDecimal('amount', 8, 2); | Setara dengan UNSIGNED DECIMAL dengan presisi dan skala |
| $table->unsignedInteger('votes'); | Setara dengan Unsigned INT |
| $table->unsignedMediumInteger('votes'); | Setara dengan Unsigned MEDIUMINT |
| $table->unsignedSmallInteger('votes'); | Setara dengan Unsigned SMALLINT |
| $table->unsignedTinyInteger('votes'); | Setara dengan Unsigned TINYINT |
| $table->uuid('id'); | Setara dengan UUID |
| $table->year('birth_year'); | Setara dengan YEAR |
| $table->comment('Table Comment'); | Mengatur komentar tabel, setara dengan COMMENT |

## Modifying Columns

### Prerequisites

Sebelum memodifikasi kolom, pastikan dependensi `doctrine/dbal` sudah ditambahkan ke `composer.json`. Library Doctrine DBAL digunakan untuk menentukan status kolom saat ini dan membuat query SQL yang diperlukan:

```bash
composer require "doctrine/dbal:^3.0"
```

### Updating Column Attributes

Method `change` bisa mengubah tipe kolom yang ada atau memodifikasi atribut lainnya.

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Ubah panjang kolom menjadi 50
    $table->string('name', 50)->change();
});
```

Atau memodifikasi kolom menjadi `nullable`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Ubah panjang menjadi 50 dan izinkan null
    $table->string('name', 50)->nullable()->change();
});
```

> Hanya tipe kolom berikut yang dapat "diubah": bigInteger, binary, boolean, date, dateTime, dateTimeTz, decimal, integer, json, longText, mediumText, smallInteger, string, text, time, unsignedBigInteger, unsignedInteger, dan unsignedSmallInteger.

### Renaming Columns

Ganti nama kolom melalui method `renameColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Ganti nama kolom dari 'from' menjadi 'to'
    $table->renameColumn('from', 'to');
});
```

> Mengganti nama kolom dengan tipe enum saat ini tidak didukung.

### Deleting Columns

Hapus kolom melalui method `dropColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Hapus kolom 'name'
    $table->dropColumn('name');
    // Hapus beberapa kolom
    $table->dropColumn(['name', 'age']);
});
```

#### Available Command Aliases

| Perintah | Deskripsi |
| ---------------------------- | ------------------------------------- |
| $table->dropRememberToken(); | Hapus kolom remember_token. |
| $table->dropSoftDeletes(); | Hapus kolom deleted_at. |
| $table->dropSoftDeletesTz(); | Alias dari method dropSoftDeletes(). |
| $table->dropTimestamps(); | Hapus kolom created_at dan updated_at. |
| $table->dropTimestampsTz(); | Alias dari method dropTimestamps(). |

## Indexes

### Creating Indexes

#### Unique Index

Buat unique index dengan method `unique`:

```php
<?php

// Buat index saat definisi
$table->string('name')->unique();
// Buat index setelah mendefinisikan kolom
$table->unique('name');
```

#### Composite Index

```php
<?php

// Buat composite index
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### Defining Index Names

Program migration otomatis menghasilkan nama index yang masuk akal. Setiap method index menerima argumen kedua opsional untuk nama kustom:

```php
<?php

// Tentukan nama unique index sebagai 'unique_name'
$table->unique('name', 'unique_name');
// Tentukan nama composite index sebagai 'index_account_id_and_created_at'
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

##### Available Index Types

| Perintah | Deskripsi |
| ------------------------------------- | ------------ |
| $table->primary('id'); | Tambahkan primary key |
| $table->primary(['id', 'parent_id']); | Tambahkan composite key |
| $table->unique('email'); | Tambahkan unique index |
| $table->index('state'); | Tambahkan plain index |
| $table->spatialIndex('location'); | Tambahkan spatial index |

### Renaming Indexes

Ganti nama index dengan method `renameIndex`:

```php
<?php

$table->renameIndex('from', 'to');
```

### Deleting Indexes

Hapus index dengan method berikut. Secara default, program migration otomatis menggabungkan nama tabel, nama kolom, dan tipe index sebagai nama. Contoh:

| Perintah | Deskripsi |
| ------------------------------------------------------ | ------------------------- |
| $table->dropPrimary('users_id_primary'); | Hapus primary key dari tabel users |
| $table->dropUnique('users_email_unique'); | Hapus unique index dari tabel users |
| $table->dropIndex('geo_state_index'); | Hapus basic index dari tabel geo |
| $table->dropSpatialIndex('geo_location_spatialindex'); | Hapus spatial index dari tabel geo |

Anda juga bisa memberikan array kolom ke `dropIndex`, dan program migration akan menghasilkan nama index secara otomatis:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### Foreign Key Constraints

Kita juga bisa membuat foreign key constraint di level database melalui method `foreign`, `references`, dan `on`. Misalnya, tabel `posts` dengan kolom `user_id` yang mereferensikan `id` dari tabel `users`:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

Anda juga bisa menentukan perilaku `on delete` dan `on update`:

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

Hapus foreign key dengan method `dropForeign`. Foreign key constraint menggunakan konvensi penamaan yang sama dengan index, ditambah suffix `_foreign`:

```php
$table->dropForeign('posts_user_id_foreign');
```

Atau berikan array kolom, biarkan program migration menghasilkan nama sesuai konvensi:

```php
$table->dropForeign(['user_id']);
```

Gunakan method berikut di file migration untuk mengaktifkan atau menonaktifkan foreign key constraint:

```php
// Aktifkan foreign key constraints
Schema::enableForeignKeyConstraints();
// Nonaktifkan foreign key constraints
Schema::disableForeignKeyConstraints();
```
