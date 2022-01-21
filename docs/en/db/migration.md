# database migration

Database migration can be understood as version management of the database structure, which can effectively solve the management of the database structure across members of the team.

# generate migrations

Generate a migration file via `gen:migration`, the command is followed by a filename parameter, usually for what the migration is intended to do.

```bash
php bin/hyperf.php gen:migration create_users_table
```

The resulting migration files are located in the `migrations` folder in the root directory, and each migration file includes a timestamp so that the migration program can determine the order of migrations.

The `--table` option can be used to specify the name of the data table. The specified table name will be generated in the migration file by default.
The `--create` option is also used to specify the name of the data table, but the difference from `--table` is that this option generates a migration file for creating a table, while `--table` is a migration file for modifying the table.

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# Migration structure

The migration class will contain `2` methods by default: `up` and `down`.
The `up` method is used to add a new data table, field or index to the database, and the `down` method is the inverse of the `up` method, which is the opposite of the operation in `up`, so that it is executed during rollback.

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

# run migration

Run all pending migration files by executing the `migrate` command:

```bash
php bin/hyperf.php migrate
```

## Force the migration

Some migration operations are destructive, which means that data loss may result. To prevent someone from running these commands in a production environment, the system will confirm with you before these commands are run, but if you wish to ignore these confirmations, force To run a command, you can use the `--force` flag:

```bash
php bin/hyperf.php migrate --force
```

## rollback migration

If you want to roll back the last migration, you can use the `migrate:rollback` command to roll back the last migration. Note that a migration may contain multiple migration files:

```bash
php bin/hyperf.php migrate:rollback
```

You can also add the `step` parameter to the `migrate:rollback` command to set the number of rollback migrations. For example, the following command will roll back the last 5 migrations:

```bash
php bin/hyperf.php migrate:rollback --step=5
```

If you wish to roll back all migrations, you can do so with `migrate:reset`:

```bash
php bin/hyperf.php migrate:reset
```

## rollback and migrate

The `migrate:refresh` command not only rolls back the migration but also runs the `migrate` command, which can efficiently rebuild some migrations:

```bash
php bin/hyperf.php migrate:refresh

// rebuild the database structure and perform data population
php bin/hyperf.php migrate:refresh --seed
```

Specify the number of rollbacks and rebuilds with the `--step` parameter. For example, the following command will rollback and re-execute the last 5 migrations:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## rebuild database

The entire database can be efficiently rebuilt with the `migrate:fresh` command, which deletes all databases before executing the `migrate` command:

```bash
php bin/hyperf.php migrate:fresh

// rebuild the database structure and perform data population
php bin/hyperf.php migrate:fresh --seed
```

# data sheet

In the migration file, the `Hyperf\Database\Schema\Schema` class is mainly used to define the data table and manage the migration process.

## create data table

Create a new database table with the `create` method. The `create` method accepts two parameters: the first parameter is the name of the data table, and the second parameter is a `Closure`, which will receive a `Hyperf\Database' to define the new data table \Schema\Blueprint` object:

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

You can use the following commands on the database structure generator to define options for a table:

```php
// Specify the table storage engine
$table->engine = 'InnoDB';
// Specify the default character set for the data table
$table->charset = 'utf8';
// Specify the default collation of the data table
$table->collation = 'utf8_unicode_ci';
// create temporary table
$table->temporary();
```

## rename data table

If you wish to rename a data table, you can use the `rename` method:

```php
Schema::rename($from, $to);
```

### Rename table with foreign key

Before renaming a table, you should verify that all foreign key constraints on the table have an explicit name in the migration file, rather than letting the migration program set a name by convention, otherwise, the foreign key's constraint name will refer to the old table name .

## delete data table

To drop an existing table, use the `drop` or `dropIfExists` methods:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## Check if the data table or field exists

The `hasTable` and `hasColumn` methods can be used to check whether a data table or field exists:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## Database connection options

If multiple databases are managed at the same time, different migrations will correspond to different database connections, then we can define different database connections in the migration file by overriding the `$connection` class attribute of the parent class:

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // This corresponds to the connection key in config/autoload/databases.php
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

# fields

## create fields

Define the definition or change to be performed by the migration file within the `Closure` of the second parameter of the `table` or `create` method. For example, the following code defines a string field of `name`:

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

## Available field definition methods

| Command | Description |
| ---------------------------------------- | ----------------------------------------------------------------- |
| $table->bigIncrements('id'); | Increment ID (primary key), equivalent to "UNSIGNED BIG INTEGER" |
| $table->bigInteger('votes'); | Equivalent to BIGINT |
| $table->binary('data'); | Equivalent to BLOB |
| $table->boolean('confirmed'); | Equivalent to BOOLEAN |
| $table->char('name', 100); | Equivalent to CHAR with length |
| $table->date('created_at'); | Equivalent to DATE |
| $table->dateTime('created_at'); | Equivalent to DATETIME |
| $table->dateTimeTz('created_at'); | Equivalent to DATETIME with time zone |
| $table->decimal('amount', 8, 2); | Equivalent to DECIMAL with precision and radix |
| $table->double('amount', 8, 2); | Equivalent to DOUBLE with precision and base |
| $table->enum('level', ['easy', 'hard']); | Equivalent to ENUM |
| $table->float('amount', 8, 2); | Equivalent to FLOAT with precision and base |
| $table->geometry('positions'); | Equivalent to GEOMETRY |
| $table->geometryCollection('positions'); | Equivalent to GEOMETRYCOLLECTION |
| $table->increments('id'); | Incrementing ID (primary key), equivalent to "UNSIGNED INTEGER" |
| $table->integer('votes'); | Equivalent to INTEGER |
| $table->ipAddress('visitor'); | Equivalent to IP address |
| $table->json('options'); | Equivalent to JSON |
| $table->jsonb('options'); | Equivalent to JSONB |
| $table->lineString('positions'); | Equivalent to LINESTRING |
| $table->longText('description'); | Equivalent to LONGTEXT |
| $table->macAddress('device'); | Equivalent to MAC address |
| $table->mediumIncrements('id'); | Increment ID (primary key), equivalent to "UNSIGNED MEDIUM INTEGER" |
| $table->mediumInteger('votes'); | Equivalent to MEDIUMINT |
| $table->mediumText('description'); | Equivalent to MEDIUMTEXT |
| $table->morphs('taggable'); | Equivalent to adding incremental taggable_id and string taggable_type |
| $table->multiLineString('positions'); | Equivalent to MULTILINESTRING |
| $table->multiPoint('positions'); | Equivalent to MULTIPOINT |
| $table->multiPolygon('positions'); | Equivalent to MULTIPOLYGON |
| $table->nullableMorphs('taggable'); | Equivalent to the nullable version of the morphs() field |
| $table->nullableTimestamps(); | Equivalent to the nullable version of the timestamps() field |
| $table->point('position'); | Equivalent to POINT |
| $table->polygon('positions'); | Equivalent to POLYGON |
| $table->rememberToken(); | Equivalent to the nullable version of the remember_token field of VARCHAR (100) |
| $table->smallIncrements('id'); | Increment ID (primary key), equivalent to "UNSIGNED SMALL INTEGER" |
| $table->smallInteger('votes'); | Equivalent to SMALLINT |
| $table->softDeletes(); | Equivalent to adding a nullable deleted_at field for soft deletes |
| $table->softDeletesTz(); | Equivalent to adding a nullable deleted_at field with time zone for soft deletes |
| $table->string('name', 100); | Equivalent to VARCHAR with length |
| $table->text('description'); | Equivalent to TEXT |
| $table->time('sunrise'); | Equivalent to TIME |
| $table->timeTz('sunrise'); | Equivalent to TIME with time zone |
| $table->timestamp('added_on'); | Equivalent to TIMESTAMP |
| $table->timestampTz('added_on'); | Equivalent to TIMESTAMP with time zone |
| $table->timestamps(); | Equivalent to nullable created_at and updated_at TIMESTAMP |
| $table->timestampsTz(); | Equivalent to nullable and timezone created_at and updated_at TIMESTAMP |
| $table->tinyIncrements('id'); | Equivalent to auto increment UNSIGNED TINYINT |
| $table->tinyInteger('votes'); | Equivalent to TINYINT |
| $table->unsignedBigInteger('votes'); | Equivalent to Unsigned BIGINT |
| $table->unsignedDecimal('amount', 8, 2); | Equivalent to UNSIGNED DECIMAL with precision and radix |
| $table->unsignedInteger('votes'); | Equivalent to Unsigned INT |
| $table->unsignedMediumInteger('votes'); | Equivalent to Unsigned MEDIUMINT |
| $table->unsignedSmallInteger('votes'); | Equivalent to Unsigned SMALLINT |
| $table->unsignedTinyInteger('votes'); | Equivalent to Unsigned TINYINT |
| $table->uuid('id'); | Equivalent to UUID |
| $table->year('birth_year'); | Equivalent to YEAR |
| $table->comment('Table Comment'); | Set table comment, equivalent to COMMENT |

## modify fields

### prerequisites

Make sure to add the `doctrine/dbal` dependency to the `composer.json` file before modifying the fields. The Doctrine DBAL library is used to determine the current state of a field and create the SQL query required to make the specified adjustments to that field:

```bash
composer require "doctrine/dbal:^3.0"
```

### Update field properties

The `change` method can modify an existing field type to a new type or modify other properties.

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // Change the length of the field to 50
    $table->string('name', 50)->change();
});
```

Or modify the field to be `nullable`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Modify the length of the field to 50 and allow nulls
    $table->string('name', 50)->nullable()->change();
});
```

> Only the following field types can be "modified": bigInteger, binary, boolean, date, dateTime, dateTimeTz, decimal, integer, json, longText, mediumText, smallInteger, string, text, time, unsignedBigInteger, unsignedInteger and unsignedSmallInteger.

### Rename fields

Fields can be renamed via the `renameColumn` method:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // rename field from from to to
    $table->renameColumn('from', 'to')->change();
});
```

> Field renaming of type enum is not currently supported.

### delete field

Fields can be dropped via the `dropColumn` method:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // delete the name field
    $table->dropColumn('name');
    // delete multiple fields
    $table->dropColumn(['name', 'age']);
});
```

#### Available command aliases

| Command | Description |
| ---------------------------- | ------------------------------------- |
| $table->dropRememberToken(); | Remove the remember_token field. |
| $table->dropSoftDeletes(); | Delete the deleted_at field. |
| $table->dropSoftDeletesTz(); | Alias ​​for dropSoftDeletes() method. |
| $table->dropTimestamps(); | Drop created_at and updated_at fields. |
| $table->dropTimestampsTz(); | Alias ​​for dropTimestamps() method. |

## index

### create index

### Unique index
Use the `unique` method to create a unique index:

```php
<?php

// create index at definition time
$table->string('name')->unique();
// create the index after defining the field
$table->unique('name');
```

#### compound index

```php
<?php

// create a composite index
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### define index name

The migrator automatically generates a reasonable index name, and each index method accepts an optional second argument to specify the name of the index:

```php
<?php

// Define the unique index name as unique_name
$table->unique('name', 'unique_name');
// Define a composite index named index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### Available index types

| Command | Description |
| ------------------------------------- | ------------ |
| $table->primary('id'); | Add primary key |
| $table->primary(['id', 'parent_id']); | Add composite key |
| $table->unique('email'); | Add Unique Index |
| $table->index('state'); | Add normal index |
| $table->spatialIndex('location'); | Add Spatial Index |

### rename index

You can rename an index with the `renameIndex` method:

```php
<?php

$table->renameIndex('from', 'to');
```

### delete index

You can delete an index in the following way. By default, the migration program will automatically concatenate the database name, the field name of the index, and the index type as the name. Examples are as follows:

| Command | Description |
| ------------------------------------------------------ | ------------------------- |
| $table->dropPrimary('users_id_primary'); | Drop primary key from users table |
| $table->dropUnique('users_email_unique'); | Drop unique index from users table |
| $table->dropIndex('geo_state_index'); | Drop base index from geo table |
| $table->dropSpatialIndex('geo_location_spatialindex'); | Drop spatial index from geo table |

You can also pass an array of fields to the `dropIndex` method and the migrator will generate an index name based on the table name, field and key type:

```php
<?php

Schema:table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### foreign key constraints

We can also create foreign key constraints at the database layer through the `foreign`, `references`, `on` methods. For example, let's let the `posts` table define a `user_id` field that references the `id` field of the `users` table:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

You can also specify the desired action for the `on delete` and `on update` properties:

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

You can drop foreign keys with the `dropForeign` method. Foreign key constraints are named in the same way as indexes, followed by a `_foreign` suffix:

```php
$table->dropForeign('posts_user_id_foreign');
```

Or pass an array of fields and have the migrator generate the names according to the agreed-upon rules:

```php
$table->dropForeign(['user_id'']);
```

You can turn foreign key constraints on or off using the following methods in the migration file:

```php
// Enable foreign key constraints
Schema::enableForeignKeyConstraints();
// disable foreign key constraints
Schema::disableForeignKeyConstraints();
```
