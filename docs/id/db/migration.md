# Database Migration

Migration database dapat dipahami sebagai manajemen versi dari struktur database,
yang secara efektif dapat menyelesaikan pengelolaan struktur database di antara
anggota tim.

# Menghasilkan Migration

Hasilkan file migration melalui `gen:migration`. Perintah ini diikuti dengan
parameter nama file, yang biasanya menggambarkan apa tujuan dari migration
tersebut.

```bash
php bin/hyperf.php gen:migration create_users_table
```

File migration yang dihasilkan terletak di dalam folder `migrations` di direktori
root, dan setiap file migration menyertakan timestamp agar program migration
dapat menentukan urutan migration.

Opsi `--table` dapat digunakan untuk menentukan nama tabel data. Nama tabel yang
ditentukan akan dibuat di dalam file migration secara default.
Opsi `--create` juga digunakan untuk menentukan nama tabel data, tetapi
perbedaannya dengan `--table` adalah opsi ini menghasilkan file migration untuk
membuat tabel baru, sedangkan `--table` menghasilkan file migration untuk
memodifikasi tabel.

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# Struktur Migration

Class migration secara default akan berisi `2` method: `up` dan `down`.
Method `up` digunakan untuk menambahkan tabel data, field, atau index baru ke
database, sedangkan method `down` adalah kebalikan dari method `up` (berisi
operasi yang berlawanan dengan `up`) sehingga dapat dieksekusi saat melakukan
rollback.

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('true', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('true');
    }
}
```

# Menjalankan Migration

Jalankan semua file migration yang tertunda dengan mengeksekusi perintah
`migrate`:

```bash
php bin/hyperf.php migrate
```

## Memaksa Migration

Beberapa operasi migration bersifat destruktif, yang berarti dapat mengakibatkan
hilangnya data. Untuk mencegah seseorang menjalankan perintah ini di lingkungan
production, sistem akan meminta konfirmasi sebelum perintah dijalankan. Namun,
jika Anda ingin mengabaikan konfirmasi ini dan memaksa menjalankan perintah,
Anda dapat menggunakan flag `--force`:

```bash
php bin/hyperf.php migrate --force
```

## Rollback Migration

Jika Anda ingin membatalkan (roll back) migration terakhir, Anda dapat menggunakan
perintah `migrate:rollback`. Perlu dicatat bahwa satu batch migration dapat
berisi beberapa file migration:

```bash
php bin/hyperf.php migrate:rollback
```

Anda juga dapat menentukan jumlah rollback migration dengan menambahkan parameter
`step` pada perintah `migrate:rollback`. Sebagai contoh, perintah berikut akan
melakukan rollback pada 5 migration terakhir:

```bash
php bin/hyperf.php migrate:rollback --step=5
```

Jika Anda ingin melakukan rollback pada semua migration, Anda dapat melakukannya
dengan perintah `migrate:reset`:

```bash
php bin/hyperf.php migrate:reset
```

## Rollback & Migrate

Perintah `migrate:refresh` tidak hanya melakukan rollback pada migration, tetapi
juga menjalankan kembali perintah `migrate`, yang membangun kembali beberapa
migration secara efisien:

```bash
php bin/hyperf.php migrate:refresh

// Membangun kembali struktur database dan melakukan pengisian data (seeding)
php bin/hyperf.php migrate:refresh --seed
```

Tentukan jumlah rollback dan rebuild dengan parameter `--step`. Sebagai contoh,
perintah berikut akan melakukan rollback dan mengeksekusi kembali 5 migration
terakhir:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## Membangun Kembali Database

Seluruh database dapat dibangun kembali secara efisien dengan perintah
`migrate:fresh`, yang akan menghapus semua tabel dalam database sebelum
mengeksekusi perintah `migrate`:

```bash
php bin/hyperf.php migrate:fresh

// Membangun kembali struktur database dan melakukan pengisian data (seeding)
php bin/hyperf.php migrate:fresh --seed
```

# Schema

Di dalam file migration, class `Hyperf\Database\Schema\Schema` terutama digunakan
untuk mendefinisikan tabel data dan mengelola proses migration.

## Membuat Tabel

Buat tabel database baru dengan method `create`. Method `create` menerima dua
parameter: parameter pertama adalah nama tabel data, dan parameter kedua adalah
`Closure` yang akan menerima object `Hyperf\Database\Schema\Blueprint` untuk
mendefinisikan tabel data baru:

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

Anda dapat menggunakan perintah berikut pada generator struktur database untuk
mendefinisikan opsi tabel:

```php
// Menentukan storage engine tabel
$table->engine = 'InnoDB';
// Menentukan default character set untuk tabel data
$table->charset = 'utf8';
// Menentukan default collation untuk tabel data
$table->collation = 'utf8_unicode_ci';
// Membuat tabel temporer
$table->temporary();
```

## Mengubah Nama Tabel

Jika Anda ingin mengubah nama tabel data, Anda dapat menggunakan method
`rename`:

```php
Schema::rename($from, $to);
```

### Mengubah Nama Tabel dengan Foreign Key

Sebelum mengubah nama tabel, Anda harus memastikan bahwa semua constraint foreign
key pada tabel tersebut memiliki nama eksplisit di dalam file migration,
alih-alih membiarkan program migration menetapkan nama berdasarkan konvensi.
Jika tidak, nama constraint foreign key akan tetap merujuk pada nama tabel yang
lama.

## Menghapus Tabel

Untuk menghapus tabel yang ada, gunakan method `drop` atau `dropIfExists`:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## Memeriksa Keberadaan Tabel atau Field

Method `hasTable` dan `hasColumn` dapat digunakan untuk memeriksa apakah tabel
data atau field tertentu ada:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## Opsi Koneksi Database

Jika beberapa database dikelola secara bersamaan di mana migration yang berbeda
akan merujuk pada koneksi database yang berbeda pula, kita dapat mendefinisikan
koneksi database yang berbeda di dalam file migration dengan meng-override
attribute class `$connection` dari parent class:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // Ini sesuai dengan key koneksi di config/autoload/databases.php
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

# Field

## Membuat Field

Definisikan pembuatan atau perubahan field yang akan dilakukan oleh file migration
di dalam `Closure` pada parameter kedua dari method `table` atau `create`.
Sebagai contoh, kode berikut mendefinisikan sebuah field bertipe string dengan
nama `name`:

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

## Method Definisi Field yang Tersedia

| Command | Deskripsi |
| ------------------------------------------ | ------------------------------------------------------------------------------- |
| $table->bigIncrements('id'); | Increment ID (primary key), setara dengan "UNSIGNED BIG INTEGER" |
| $table->bigInteger('votes'); | Setara dengan BIGINT |
| $table->binary('data'); | Setara dengan BLOB |
| $table->boolean('confirmed'); | Setara dengan BOOLEAN |
| $table->char('name', 100); | Setara dengan CHAR dengan panjang tertentu |
| $table->date('created_at'); | Setara dengan DATE |
| $table->dateTime('created_at'); | Setara dengan DATETIME |
| $table->dateTimeTz('created_at'); | Setara dengan DATETIME dengan timezone |
| $table->decimal('amount', 8, 2); | Setara dengan DECIMAL dengan presisi dan skala |
| $table->double('amount', 8, 2); | Setara dengan DOUBLE dengan presisi dan skala |
| $table->enum('level', ['easy', 'hard']); | Setara dengan ENUM |
| $table->float('amount', 8, 2); | Setara dengan FLOAT dengan presisi dan skala |
| $table->geometry('positions'); | Setara dengan GEOMETRY |
| $table->geometryCollection('positions'); | Setara dengan GEOMETRYCOLLECTION |
| $table->increments('id'); | Auto-incrementing ID (primary key), setara dengan "UNSIGNED INTEGER" |
| $table->integer('votes'); | Setara dengan INTEGER |
| $table->ipAddress('visitor'); | Setara dengan alamat IP |
| $table->json('options'); | Setara dengan JSON |
| $table->jsonb('options'); | Setara dengan JSONB |
| $table->lineString('positions'); | Setara dengan LINESTRING |
| $table->longText('description'); | Setara dengan LONGTEXT |
| $table->macAddress('device'); | Setara dengan alamat MAC |
| $table->mediumIncrements('id'); | Increment ID (primary key), setara dengan "UNSIGNED MEDIUM INTEGER" |
| $table->mediumInteger('votes'); | Setara dengan MEDIUMINT |
| $table->mediumText('description'); | Setara dengan MEDIUMTEXT |
| $table->morphs('taggable'); | Setara dengan menambahkan field bigInteger `taggable_id` dan string `taggable_type` |
| $table->multiLineString('positions'); | Setara dengan MULTILINESTRING |
| $table->multiPoint('positions'); | Setara dengan MULTIPOINT |
| $table->multiPolygon('positions'); | Setara dengan MULTIPOLYGON |
| $table->nullableMorphs('taggable'); | Setara dengan versi nullable dari field morphs() |
| $table->nullableTimestamps(); | Setara dengan versi nullable dari field timestamps() |
| $table->point('position'); | Setara dengan POINT |
| $table->polygon('positions'); | Setara dengan POLYGON |
| $table->rememberToken(); | Setara dengan field `remember_token` sebagai VARCHAR(100) nullable |
| $table->smallIncrements('id'); | Increment ID (primary key), setara dengan "UNSIGNED SMALL INTEGER" |
| $table->smallInteger('votes'); | Setara dengan SMALLINT |
| $table->softDeletes(); | Setara dengan menambahkan field `deleted_at` nullable untuk soft delete |
| $table->softDeletesTz(); | Setara dengan menambahkan field `deleted_at` nullable dengan timezone untuk soft delete |
| $table->string('name', 100); | Setara dengan VARCHAR dengan panjang tertentu |
| $table->text('description'); | Setara dengan TEXT |
| $table->time('sunrise'); | Setara dengan TIME |
| $table->timeTz('sunrise'); | Setara dengan TIME dengan timezone |
| $table->timestamp('added_on'); | Setara dengan TIMESTAMP |
| $table->timestampTz('added_on'); | Setara dengan TIMESTAMP dengan timezone |
| $table->timestamps(); | Setara dengan TIMESTAMP `created_at` dan `updated_at` yang nullable |
| $table->timestampsTz(); | Setara dengan TIMESTAMP `created_at` dan `updated_at` nullable dengan timezone |
| $table->tinyIncrements('id'); | Setara dengan auto-increment UNSIGNED TINYINT |
| $table->tinyInteger('votes'); | Setara dengan TINYINT |
| $table->unsignedBigInteger('votes'); | Setara dengan UNSIGNED BIGINT |
| $table->unsignedDecimal('amount', 8, 2); | Setara dengan UNSIGNED DECIMAL dengan presisi dan skala |
| $table->unsignedInteger('votes'); | Setara dengan UNSIGNED INT |
| $table->unsignedMediumInteger('votes'); | Setara dengan UNSIGNED MEDIUMINT |
| $table->unsignedSmallInteger('votes'); | Setara dengan UNSIGNED SMALLINT |
| $table->unsignedTinyInteger('votes'); | Setara dengan UNSIGNED TINYINT |
| $table->uuid('id'); | Setara dengan UUID |
| $table->year('birth_year'); | Setara dengan YEAR |
| $table->comment('Table Comment'); | Menetapkan komentar tabel, setara dengan COMMENT |

## Memodifikasi Field

### Prasyarat

Pastikan untuk menambahkan dependency `doctrine/dbal` ke file `composer.json`
sebelum memodifikasi field. Library Doctrine DBAL digunakan untuk menentukan
status saat ini dari suatu field dan membuat query SQL yang diperlukan untuk
melakukan penyesuaian yang ditentukan pada field tersebut:

```bash
composer require "doctrine/dbal:^3.0"
```

### Memperbarui Properti Field

Method `change` dapat memodifikasi tipe field yang ada menjadi tipe baru atau
memodifikasi properti lainnya.

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // Memodifikasi panjang field menjadi 50
    $table->string('name', 50)->change();
});
```

Atau memodifikasi field agar bernilai `nullable`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Memodifikasi panjang field menjadi 50 dan mengizinkan null
    $table->string('name', 50)->nullable()->change();
});
```

> Hanya tipe field berikut yang dapat "dimodifikasi": bigInteger, binary,
> boolean, date, dateTime, dateTimeTz, decimal, integer, json, longText,
> mediumText, smallInteger, string, text, time, unsignedBigInteger,
> unsignedInteger, dan unsignedSmallInteger.

### Mengubah Nama Field

Field dapat diubah namanya melalui method `renameColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Mengubah nama field dari from ke to
    $table->renameColumn('from', 'to')->change();
});
```

> Mengubah nama field dengan tipe enum saat ini tidak didukung.

### Menghapus Field

Field dapat dihapus melalui method `dropColumn`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Menghapus field name
    $table->dropColumn('name');
    // Menghapus beberapa field sekaligus
    $table->dropColumn(['name', 'age']);
});
```

#### Alias Perintah yang Tersedia

| Command | Deskripsi |
| ---------------------------- | ---------------------------------------------- |
| $table->dropRememberToken(); | Menghapus field remember_token. |
| $table->dropSoftDeletes(); | Menghapus field deleted_at. |
| $table->dropSoftDeletesTz(); | Alias untuk method dropSoftDeletes(). |
| $table->dropTimestamps(); | Menghapus field created_at dan updated_at. |
| $table->dropTimestampsTz(); | Alias untuk method dropTimestamps(). |

## Index

### Membuat Index

### Unique Index

Gunakan method `unique` untuk membuat index unik (unique index):

```php
<?php

// Membuat index pada saat pendefinisian field
$table->string('name')->unique();
// Membuat index setelah field didefinisikan
$table->unique('name');
```

#### Compound Index

```php
<?php

// Membuat compound index
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### Menentukan Nama Index

Migrator secara otomatis akan menghasilkan nama index yang sesuai, dan setiap
method index menerima argumen kedua opsional untuk menentukan nama index tersebut:

```php
<?php

// Menentukan nama unique index sebagai unique_name
$table->unique('name', 'unique_name');
// Menentukan compound index bernama index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### Tipe Index yang Tersedia

| Command | Deskripsi |
| ------------------------------------- | ----------------- |
| $table->primary('id'); | Menambahkan primary key |
| $table->primary(['id', 'parent_id']); | Menambahkan composite key |
| $table->unique('email'); | Menambahkan unique index |
| $table->index('state'); | Menambahkan index biasa |
| $table->spatialIndex('location'); | Menambahkan spatial index |

### Mengubah Nama Index

Anda dapat mengubah nama index dengan method `renameIndex`:

```php
<?php

$table->renameIndex('from', 'to');
```

### Menghapus Index

Anda dapat menghapus index dengan cara berikut. Secara default, program
migration akan secara otomatis menggabungkan nama tabel, nama field index, dan
tipe index sebagai namanya. Contohnya adalah sebagai berikut:

| Command | Deskripsi |
| ------------------------------------------------------ | ----------------------------------------- |
| $table->dropPrimary('users_id_primary'); | Menghapus primary key dari tabel users |
| $table->dropUnique('users_email_unique'); | Menghapus unique index dari tabel users |
| $table->dropIndex('geo_state_index'); | Menghapus index dasar dari tabel geo |
| $table->dropSpatialIndex('geo_location_spatialindex'); | Menghapus spatial index dari tabel geo |

Anda juga dapat meneruskan array field ke method `dropIndex`, dan migrator akan
menghasilkan nama index berdasarkan nama tabel, field, dan tipe key:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### Constraint Foreign Key

Kita juga dapat membuat constraint foreign key pada lapisan database melalui
method `foreign`, `references`, dan `on`. Sebagai contoh, mari kita definisikan
field `user_id` pada tabel `posts` yang merujuk pada field `id` di tabel
`users`:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

Anda juga dapat menentukan aksi yang diinginkan untuk properti `on delete` dan
`on update`:

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

Anda dapat menghapus foreign key menggunakan method `dropForeign`. Constraint
foreign key dinamai dengan cara yang sama seperti index, diikuti oleh akhiran
`_foreign`:

```php
$table->dropForeign('posts_user_id_foreign');
```

Atau teruskan array berisi field dan biarkan migrator menghasilkan nama sesuai
dengan aturan yang disepakati:

```php
$table->dropForeign(['user_id'']);
```

Anda dapat mengaktifkan atau menonaktifkan constraint foreign key menggunakan
method berikut di dalam file migration:

```php
// Mengaktifkan constraint foreign key
Schema::enableForeignKeyConstraints();
// Menonaktifkan constraint foreign key
Schema::disableForeignKeyConstraints();
```
