# Database Migration

Database migration can be understood as version management for the database structure, which effectively solves the management of database structure across team members.

> The declaration location of related scripts has been moved from the `database` component to the `devtool` component. Therefore, in the online `--no-dev` environment, you need to manually write the executable command into the `autoload/commands.php` configuration.

## Generate Migration

Generate a migration file via `gen:migration`. A filename parameter follows the command, which usually describes the purpose of the migration.

```bash
php bin/hyperf.php gen:migration create_users_table
```

The generated migration file is located in the `migrations` folder in the root directory. Each migration file contains a timestamp so that the migration program can determine the order of migrations.

The `--table` option can be used to specify the name of the database table. The specified table name will be generated in the migration file by default.
The `--create` option is also used to specify the table name, but the difference from `--table` is that this option generates a migration file for creating a table, whereas `--table` is used for migration files that modify a table.

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

## Migration Structure

The migration class contains `2` methods by default: `up` and `down`.
The `up` method is used to add new database tables, columns, or indexes to the database, while the `down` method is the reverse operation of the `up` method, performing the opposite of the operations in `up` so that they can be executed during rollback.

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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
```

## Running Migrations

Run all pending migration files by executing the `migrate` command:

```bash
php bin/hyperf.php migrate
```

### Force Migration Execution

Some migration operations are destructive, meaning they may result in data loss. To prevent someone from running these commands in a production environment, the system will ask for confirmation before running them. However, if you wish to ignore these confirmation messages and force the commands to run, you can use the `--force` flag:

```bash
php bin/hyperf.php migrate --force
```

### Rollback Migrations

If you wish to rollback the last migration, you can use the `migrate:rollback` command. Note that one migration may contain multiple migration files:

```bash
php bin/hyperf.php migrate:rollback
```

You can also append the `step` parameter to the `migrate:rollback` command to set the number of times to rollback. For example, the following command will rollback the last 5 migrations:

```bash
php bin/hyperf.php migrate:rollback --step=5
```

If you wish to rollback all migrations, you can use `migrate:reset`:

```bash
php bin/hyperf.php migrate:reset
```

### Rollback and Migrate

The `migrate:refresh` command not only rolls back migrations but also subsequently runs the `migrate` command, which allows for efficiently rebuilding certain migrations:

```bash
php bin/hyperf.php migrate:refresh

// Rebuild database structure and execute data seeding
php bin/hyperf.php migrate:refresh --seed
```

Specify the number of rollbacks and rebuilds via the `--step` parameter. For example, the following command will rollback and re-execute the last 5 migrations:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

### Rebuild Database

You can efficiently rebuild the entire database via the `migrate:fresh` command. This command will first drop all database tables and then execute the `migrate` command:

```bash
php bin/hyperf.php migrate:fresh

// Rebuild database structure and execute data seeding
php bin/hyperf.php migrate:fresh --seed
```

## Tables

In migration files, the `Hyperf\Database\Schema\Schema` class is primarily used to define database tables and manage the migration process.

### Creating Tables

Create new database tables via the `create` method. The `create` method accepts two arguments: the first is the name of the table, and the second is a `Closure`, which receives a `Hyperf\Database\Schema\Blueprint` object used to define the new table:

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

You can use the following commands on the database schema builder to define table options:

```php
// Specify table storage engine
$table->engine = 'InnoDB';
// Specify default character set for the table
$table->charset = 'utf8';
// Specify default collation for the table
$table->collation = 'utf8_unicode_ci';
// Create temporary table
$table->temporary();
```

### Renaming Tables

If you wish to rename a table, you can use the `rename` method:

```php
Schema::rename($from, $to);
```

#### Renaming Tables with Foreign Keys

Before renaming a table, you should verify that all foreign key constraints on the table have explicit names in the migration file, rather than letting the migration program set a name according to convention. Otherwise, the foreign key constraint name will reference the old table name.

### Deleting Tables

To delete an existing table, you can use the `drop` or `dropIfExists` methods:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

### Checking for Table or Column Existence

You can check whether a table or column exists via the `hasTable` and `hasColumn` methods:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('users', 'email')) {
    //
}
```

### Database Connection Options

If you are managing multiple databases simultaneously, and different migrations correspond to different database connections, you can define different database connections in the migration file by overriding the `$connection` class attribute of the parent class:

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

## Columns

### Creating Columns

Define the definitions or changes to be executed by the migration file within the `Closure` in the second argument of the `table` or `create` method. For example, the following code defines a string column named `name`:

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

| Command | Description |
| ---------------------------------------- | ------------------------------------------------------- |
| $table->bigIncrements('id'); | Increment ID (primary key), equivalent to UNSIGNED BIG INTEGER |
| $table->bigInteger('votes'); | Equivalent to BIGINT |
| $table->binary('data'); | Equivalent to BLOB |
| $table->boolean('confirmed'); | Equivalent to BOOLEAN |
| $table->char('name', 100); | Equivalent to CHAR with length |
| $table->date('created_at'); | Equivalent to DATE |
| $table->dateTime('created_at'); | Equivalent to DATETIME |
| $table->dateTimeTz('created_at'); | Equivalent to DATETIME with timezone |
| $table->decimal('amount', 8, 2); | Equivalent to DECIMAL with precision and scale |
| $table->double('amount', 8, 2); | Equivalent to DOUBLE with precision and scale |
| $table->enum('level', ['easy', 'hard']); | Equivalent to ENUM |
| $table->float('amount', 8, 2); | Equivalent to FLOAT with precision and scale |
| $table->geometry('positions'); | Equivalent to GEOMETRY |
| $table->geometryCollection('positions'); | Equivalent to GEOMETRYCOLLECTION |
| $table->increments('id'); | Increment ID (primary key), equivalent to UNSIGNED INTEGER |
| $table->integer('votes'); | Equivalent to INTEGER |
| $table->ipAddress('visitor'); | Equivalent to IP address |
| $table->json('options'); | Equivalent to JSON |
| $table->jsonb('options'); | Equivalent to JSONB |
| $table->lineString('positions'); | Equivalent to LINESTRING |
| $table->longText('description'); | Equivalent to LONGTEXT |
| $table->macAddress('device'); | Equivalent to MAC address |
| $table->mediumIncrements('id'); | Increment ID (primary key), equivalent to UNSIGNED MEDIUM INTEGER |
| $table->mediumInteger('votes'); | Equivalent to MEDIUMINT |
| $table->mediumText('description'); | Equivalent to MEDIUMTEXT |
| $table->morphs('taggable'); | Adds incrementing taggable_id and string taggable_type |
| $table->multiLineString('positions'); | Equivalent to MULTILINESTRING |
| $table->multiPoint('positions'); | Equivalent to MULTIPOINT |
| $table->multiPolygon('positions'); | Equivalent to MULTIPOLYGON |
| $table->nullableMorphs('taggable'); | Nullable version of morphs() column |
| $table->nullableTimestamps(); | Nullable version of timestamps() column |
| $table->point('position'); | Equivalent to POINT |
| $table->polygon('positions'); | Equivalent to POLYGON |
| $table->rememberToken(); | Adds a nullable VARCHAR(100) remember_token column |
| $table->smallIncrements('id'); | Increment ID (primary key), equivalent to UNSIGNED SMALL INTEGER |
| $table->smallInteger('votes'); | Equivalent to SMALLINT |
| $table->softDeletes(); | Adds nullable deleted_at column for soft deletes |
| $table->softDeletesTz(); | Adds nullable deleted_at column with timezone for soft deletes |
| $table->string('name', 100); | Equivalent to VARCHAR with length |
| $table->text('description'); | Equivalent to TEXT |
| $table->time('sunrise'); | Equivalent to TIME |
| $table->timeTz('sunrise'); | Equivalent to TIME with timezone |
| $table->timestamp('added_on'); | Equivalent to TIMESTAMP |
| $table->timestampTz('added_on'); | Equivalent to TIMESTAMP with timezone |
| $table->timestamps(); | Nullable created_at and updated_at TIMESTAMP columns |
| $table->timestampsTz(); | Nullable created_at and updated_at TIMESTAMP columns with timezone |
| $table->tinyIncrements('id'); | Equivalent to auto-incrementing UNSIGNED TINYINT |
| $table->tinyInteger('votes'); | Equivalent to TINYINT |
| $table->unsignedBigInteger('votes'); | Equivalent to Unsigned BIGINT |
| $table->unsignedDecimal('amount', 8, 2); | Equivalent to UNSIGNED DECIMAL with precision and scale |
| $table->unsignedInteger('votes'); | Equivalent to Unsigned INT |
| $table->unsignedMediumInteger('votes'); | Equivalent to Unsigned MEDIUMINT |
| $table->unsignedSmallInteger('votes'); | Equivalent to Unsigned SMALLINT |
| $table->unsignedTinyInteger('votes'); | Equivalent to Unsigned TINYINT |
| $table->uuid('id'); | Equivalent to UUID |
| $table->year('birth_year'); | Equivalent to YEAR |
| $table->comment('Table Comment'); | Sets table comment, equivalent to COMMENT |

## Modifying Columns

### Prerequisites

Before modifying a column, please ensure that the `doctrine/dbal` dependency is added to your `composer.json` file. The Doctrine DBAL library is used to determine the current state of a column and create the SQL queries required to make the specified adjustments:

```bash
composer require "doctrine/dbal:^3.0"
```

### Updating Column Attributes

The `change` method can modify existing column types to a new type or modify other attributes.

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Change the length of the column to 50
    $table->string('name', 50)->change();
});
```

Or modify the column to be `nullable`:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Change length to 50 and allow null
    $table->string('name', 50)->nullable()->change();
});
```

> Only the following column types can be "changed": bigInteger, binary, boolean, date, dateTime, dateTimeTz, decimal, integer, json, longText, mediumText, smallInteger, string, text, time, unsignedBigInteger, unsignedInteger, and unsignedSmallInteger.

### Renaming Columns

Rename columns via the `renameColumn` method:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Rename column from 'from' to 'to'
    $table->renameColumn('from', 'to');
});
```

> Renaming enum type columns is not currently supported.

### Deleting Columns

Delete columns via the `dropColumn` method:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // Delete 'name' column
    $table->dropColumn('name');
    // Delete multiple columns
    $table->dropColumn(['name', 'age']);
});
```

#### Available Command Aliases

| Command | Description |
| ---------------------------- | ------------------------------------- |
| $table->dropRememberToken(); | Delete remember_token column. |
| $table->dropSoftDeletes(); | Delete deleted_at column. |
| $table->dropSoftDeletesTz(); | Alias of dropSoftDeletes() method. |
| $table->dropTimestamps(); | Delete created_at and updated_at columns. |
| $table->dropTimestampsTz(); | Alias of dropTimestamps() method. |

## Indexes

### Creating Indexes

#### Unique Index

Create a unique index via the `unique` method:

```php
<?php

// Create index at definition
$table->string('name')->unique();
// Create index after defining the column
$table->unique('name');
```

#### Composite Index

```php
<?php

// Create a composite index
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### Defining Index Names

The migration program will automatically generate a reasonable index name. Each index method accepts an optional second argument to specify the name of the index:

```php
<?php

// Define unique index name as 'unique_name'
$table->unique('name', 'unique_name');
// Define composite index name as 'index_account_id_and_created_at'
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

##### Available Index Types

| Command | Description |
| ------------------------------------- | ------------ |
| $table->primary('id'); | Add primary key |
| $table->primary(['id', 'parent_id']); | Add composite key |
| $table->unique('email'); | Add unique index |
| $table->index('state'); | Add plain index |
| $table->spatialIndex('location'); | Add spatial index |

### Renaming Indexes

You can rename an index via the `renameIndex` method:

```php
<?php

$table->renameIndex('from', 'to');
```

### Deleting Indexes

You can delete indexes via the following methods. By default, the migration program will automatically concatenate the table name, index column name, and index type as the name. For example:

| Command | Description |
| ------------------------------------------------------ | ------------------------- |
| $table->dropPrimary('users_id_primary'); | Delete primary key from users table |
| $table->dropUnique('users_email_unique'); | Delete unique index from users table |
| $table->dropIndex('geo_state_index'); | Delete basic index from geo table |
| $table->dropSpatialIndex('geo_location_spatialindex'); | Delete spatial index from geo table |

You can also pass an array of columns to the `dropIndex` method, and the migration program will generate the index name based on the table name, columns, and key type:

```php
<?php

Schema::table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### Foreign Key Constraints

We can also create database-level foreign key constraints via `foreign`, `references`, and `on` methods. For example, let the `posts` table define a `user_id` column that references the `id` column of the `users` table:

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

You can also specify the required operations for `on delete` and `on update` attributes:

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

You can delete foreign keys via the `dropForeign` method. Foreign key constraints adopt the same naming convention as indexes, plus a `_foreign` suffix:

```php
$table->dropForeign('posts_user_id_foreign');
```

Or pass an array of columns, and let the migration program generate the name according to conventions:

```php
$table->dropForeign(['user_id']);
```

You can use the following methods in the migration file to enable or disable foreign key constraints:

```php
// Enable foreign key constraints
Schema::enableForeignKeyConstraints();
// Disable foreign key constraints
Schema::disableForeignKeyConstraints();
```
