# 數據庫遷移

數據庫遷移可以理解為對數據庫結構的版本管理，可以有效的解決團隊中跨成員對數據庫結構的管理。

> 相關腳本的聲明位置已從 database 組件移入 devtool 組件，故線上 `--no-dev` 環境下，需要手動將可執行的命令寫入 `autoload/commands.php` 配置中。

# 生成遷移

通過 `gen:migration` 生成一個遷移文件，命令後面跟的是一個文件名參數，通常為這個遷移要打算做的事情。

```bash
php bin/hyperf.php gen:migration create_users_table
```

生成的遷移文件位於根目錄下的 `migrations` 文件夾內，每個遷移文件都包含一個時間戳，以便遷移程序確定遷移的順序。

`--table` 選項可以用來指定數據表的名稱，指定的表名將會默認生成在遷移文件中。   
`--create` 選項也是用來指定數據表的名稱，但跟 `--table` 的差異在於該選項是生成創建表的遷移文件，而 `--table` 是用於修改表的遷移文件。

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# 遷移結構

遷移類默認會包含 `2` 個方法：`up` 和 `down`。   
`up` 方法用於添加新的數據表，字段或者索引到數據庫，而 `down` 方法就是 `up` 方法的反操作，和 `up` 裏的操作相反，以便在回退的時候執行。

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

# 運行遷移

通過執行 `migrate` 命令運行所有尚未完成的遷移文件：

```bash
php bin/hyperf.php migrate
```

## 強制執行遷移

一些遷移操作是具有破壞性的，這意味着可能會導致數據丟失，為了防止有人在生產環境中運行這些命令，系統會在這些命令運行之前與你進行確認，但如果您希望忽略這些確認信息強制運行命令，可以使用 `--force` 標記：

```bash
php bin/hyperf.php migrate --force
```

## 回滾遷移

若您希望回滾最後一次的遷移，可以通過 `migrate:rollback` 命令回滾最後一次的遷移，注意一次遷移可能會包含多個遷移文件：

```bash
php bin/hyperf.php migrate:rollback
```

您還可以在 `migrate:rollback` 命令後面加上 `step` 參數來設置回滾遷移的次數，比如以下命令將回滾最近 5 次遷移：

```bash
php bin/hyperf.php migrate:rollback --step=5
```

如果您希望回滾所有的遷移，可以通過 `migrate:reset` 來回滾：

```bash
php bin/hyperf.php migrate:reset
```

## 回滾並遷移

`migrate:refresh` 命令不僅會回滾遷移還會接着運行 `migrate` 命令，這樣可以高效地重建某些遷移：

```bash
php bin/hyperf.php migrate:refresh

// 重建數據庫結構並執行數據填充
php bin/hyperf.php migrate:refresh --seed
```

通過 `--step` 參數指定回滾和重建次數，比如以下命令將回滾並重新執行最後 5 次遷移：

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## 重建數據庫

通過 `migrate:fresh` 命令可以高效地重建整個數據庫，這個命令會先刪除所有的數據庫，然後再執行 `migrate` 命令：

```bash
php bin/hyperf.php migrate:fresh

// 重建數據庫結構並執行數據填充
php bin/hyperf.php migrate:fresh --seed
```

# 數據表

在遷移文件中主要通過 `Hyperf\Database\Schema\Schema` 類來定義數據表和管理遷移流程。

## 創建數據表

通過 `create` 方法來創建新的數據庫表。 `create` 方法接受兩個參數：第一個參數為數據表的名稱，第二個參數是一個 `閉包(Closure)`，此閉包會接收一個用於定義新數據表的 `Hyperf\Database\Schema\Blueprint` 對象：

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

您可以在數據庫結構生成器上使用以下命令來定義表的選項：

```php
// 指定表存儲引擎
$table->engine = 'InnoDB';
// 指定數據表的默認字符集
$table->charset = 'utf8';
// 指定數據表默認的排序規則
$table->collation = 'utf8_unicode_ci';
// 創建臨時表
$table->temporary();
```

## 重命名數據表

若您希望重命名一個數據表，可以通過 `rename` 方法：

```php
Schema::rename($from, $to);
```

### 重命名帶外鍵的數據表

在重命名錶之前，您應該驗證表上的所有外鍵約束在遷移文件中都有明確的名稱，而不是讓遷移程序按照約定來設置一個名稱，否則，外鍵的約束名稱將引用舊錶名。

## 刪除數據表

刪除一個已存在的數據表，可以通過 `drop` 或 `dropIfExists` 方法：

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## 檢查數據表或字段是否存在

可以通過 `hasTable` 和 `hasColumn` 方法來檢查數據表或字段是否存在:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## 數據庫連接選項

如果在同時管理多個數據庫的情況下，不同的遷移會對應不同的數據庫連接，那麼此時我們可以在遷移文件中通過重寫父類的 `$connection` 類屬性來定義不同的數據庫連接：

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // 這裏對應 config/autoload/databases.php 內的連接 key
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

# 字段

## 創建字段

在 `table` 或 `create` 方法的第二個參數的 `閉包(Closure)` 內定義該遷移文件要執行的定義或變更，比如下面的代碼為定義一個 `name` 的字符串字段：

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

## 可用的字段定義方法

| 命令                                     | 描述                                                    |
| ---------------------------------------- | ------------------------------------------------------- |
| $table->bigIncrements('id');             | 遞增 ID（主鍵），相當於「UNSIGNED BIG INTEGER」         |
| $table->bigInteger('votes');             | 相當於 BIGINT                                           |
| $table->binary('data');                  | 相當於 BLOB                                             |
| $table->boolean('confirmed');            | 相當於 BOOLEAN                                          |
| $table->char('name', 100);               | 相當於帶有長度的 CHAR                                   |
| $table->date('created_at');              | 相當於 DATE                                             |
| $table->dateTime('created_at');          | 相當於 DATETIME                                         |
| $table->dateTimeTz('created_at');        | 相當於帶時區 DATETIME                                   |
| $table->decimal('amount', 8, 2);         | 相當於帶有精度與基數 DECIMAL                            |
| $table->double('amount', 8, 2);          | 相當於帶有精度與基數 DOUBLE                             |
| $table->enum('level', ['easy', 'hard']); | 相當於 ENUM                                             |
| $table->float('amount', 8, 2);           | 相當於帶有精度與基數 FLOAT                              |
| $table->geometry('positions');           | 相當於 GEOMETRY                                         |
| $table->geometryCollection('positions'); | 相當於 GEOMETRYCOLLECTION                               |
| $table->increments('id');                | 遞增的 ID (主鍵)，相當於「UNSIGNED INTEGER」            |
| $table->integer('votes');                | 相當於 INTEGER                                          |
| $table->ipAddress('visitor');            | 相當於 IP 地址                                          |
| $table->json('options');                 | 相當於 JSON                                             |
| $table->jsonb('options');                | 相當於 JSONB                                            |
| $table->lineString('positions');         | 相當於 LINESTRING                                       |
| $table->longText('description');         | 相當於 LONGTEXT                                         |
| $table->macAddress('device');            | 相當於 MAC 地址                                         |
| $table->mediumIncrements('id');          | 遞增 ID (主鍵) ，相當於「UNSIGNED MEDIUM INTEGER」      |
| $table->mediumInteger('votes');          | 相當於 MEDIUMINT                                        |
| $table->mediumText('description');       | 相當於 MEDIUMTEXT                                       |
| $table->morphs('taggable');              | 相當於加入遞增的 taggable_id 與字符串 taggable_type     |
| $table->multiLineString('positions');    | 相當於 MULTILINESTRING                                  |
| $table->multiPoint('positions');         | 相當於 MULTIPOINT                                       |
| $table->multiPolygon('positions');       | 相當於 MULTIPOLYGON                                     |
| $table->nullableMorphs('taggable');      | 相當於可空版本的 morphs() 字段                          |
| $table->nullableTimestamps();            | 相當於可空版本的 timestamps() 字段                      |
| $table->point('position');               | 相當於 POINT                                            |
| $table->polygon('positions');            | 相當於 POLYGON                                          |
| $table->rememberToken();                 | 相當於可空版本的 VARCHAR (100) 的 remember_token 字段   |
| $table->smallIncrements('id');           | 遞增 ID (主鍵) ，相當於「UNSIGNED SMALL INTEGER」       |
| $table->smallInteger('votes');           | 相當於 SMALLINT                                         |
| $table->softDeletes();                   | 相當於為軟刪除添加一個可空的 deleted_at 字段            |
| $table->softDeletesTz();                 | 相當於為軟刪除添加一個可空的 帶時區的 deleted_at 字段   |
| $table->string('name', 100);             | 相當於帶長度的 VARCHAR                                  |
| $table->text('description');             | 相當於 TEXT                                             |
| $table->time('sunrise');                 | 相當於 TIME                                             |
| $table->timeTz('sunrise');               | 相當於帶時區的 TIME                                     |
| $table->timestamp('added_on');           | 相當於 TIMESTAMP                                        |
| $table->timestampTz('added_on');         | 相當於帶時區的 TIMESTAMP                                |
| $table->timestamps();                    | 相當於可空的 created_at 和 updated_at TIMESTAMP         |
| $table->timestampsTz();                  | 相當於可空且帶時區的 created_at 和 updated_at TIMESTAMP |
| $table->tinyIncrements('id');            | 相當於自動遞增 UNSIGNED TINYINT                         |
| $table->tinyInteger('votes');            | 相當於 TINYINT                                          |
| $table->unsignedBigInteger('votes');     | 相當於 Unsigned BIGINT                                  |
| $table->unsignedDecimal('amount', 8, 2); | 相當於帶有精度和基數的 UNSIGNED DECIMAL                 |
| $table->unsignedInteger('votes');        | 相當於 Unsigned INT                                     |
| $table->unsignedMediumInteger('votes');  | 相當於 Unsigned MEDIUMINT                               |
| $table->unsignedSmallInteger('votes');   | 相當於 Unsigned SMALLINT                                |
| $table->unsignedTinyInteger('votes');    | 相當於 Unsigned TINYINT                                 |
| $table->uuid('id');                      | 相當於 UUID                                             |
| $table->year('birth_year');              | 相當於 YEAR                                             |
| $table->comment('Table Comment');        | 設置表註釋，相當於 COMMENT                              |

## 修改字段

### 先決條件

在修改字段之前，請確保將 `doctrine/dbal` 依賴添加到 `composer.json` 文件中。Doctrine DBAL 庫用於確定字段的當前狀態， 並創建對該字段進行指定調整所需的 SQL 查詢：

```bash
composer require "doctrine/dbal:^3.0"
```

### 更新字段屬性

`change` 方法可以將現有的字段類型修改為新的類型或修改其它屬性。

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // 將字段的長度修改為 50
    $table->string('name', 50)->change();
});
```

或修改字段為 `可為空`：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 將字段的長度修改為 50 並允許為空
    $table->string('name', 50)->nullable()->change();
});
```

> 只有下面的字段類型能被 "修改"： bigInteger、 binary、 boolean、date、dateTime、dateTimeTz、decimal、integer、json、 longText、mediumText、smallInteger、string、text、time、 unsignedBigInteger、unsignedInteger and unsignedSmallInteger。

### 重命名字段

可以通過 `renameColumn` 方法來重命名字段：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 將字段從 from 重命名為 to
    $table->renameColumn('from', 'to')->change();
});
```

> 當前不支持 enum 類型的字段重命名。

### 刪除字段

可以通過 `dropColumn` 方法來刪除字段：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 刪除 name 字段
    $table->dropColumn('name');
    // 刪除多個字段
    $table->dropColumn(['name', 'age']);
});
```

#### 可用的命令別名

| 命令                         | 描述                                  |
| ---------------------------- | ------------------------------------- |
| $table->dropRememberToken(); | 刪除 remember_token 字段。            |
| $table->dropSoftDeletes();   | 刪除 deleted_at 字段。                |
| $table->dropSoftDeletesTz(); | dropSoftDeletes() 方法的別名。        |
| $table->dropTimestamps();    | 刪除 created_at and updated_at 字段。 |
| $table->dropTimestampsTz();  | dropTimestamps() 方法的別名。         |

## 索引

### 創建索引

###  唯一索引
通過 `unique` 方法來創建一個唯一索引：

```php
<?php

// 在定義時創建索引
$table->string('name')->unique();
// 在定義完字段之後創建索引
$table->unique('name');
```

#### 複合索引

```php
<?php

// 創建一個複合索引
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### 定義索引名稱

遷移程序會自動生成一個合理的索引名稱，每個索引方法都接受一個可選的第二個參數來指定索引的名稱：

```php
<?php

// 定義唯一索引名稱為 unique_name
$table->unique('name', 'unique_name');
// 定義一個複合索引名稱為 index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### 可用的索引類型

| 命令                                  | 描述         |
| ------------------------------------- | ------------ |
| $table->primary('id');                | 添加主鍵     |
| $table->primary(['id', 'parent_id']); | 添加複合鍵   |
| $table->unique('email');              | 添加唯一索引 |
| $table->index('state');               | 添加普通索引 |
| $table->spatialIndex('location');     | 添加空間索引 |

### 重命名索引

您可通過 `renameIndex` 方法重命名一個索引的名稱：

```php
<?php

$table->renameIndex('from', 'to');
```

### 刪除索引

您可通過下面的方法來刪除索引，默認情況下遷移程序會自動將數據庫名稱、索引的字段名及索引類型簡單地連接在一起作為名稱。舉例如下:

| 命令                                                   | 描述                      |
| ------------------------------------------------------ | ------------------------- |
| $table->dropPrimary('users_id_primary');               | 從 users 表中刪除主鍵     |
| $table->dropUnique('users_email_unique');              | 從 users 表中刪除唯一索引 |
| $table->dropIndex('geo_state_index');                  | 從 geo 表中刪除基本索引   |
| $table->dropSpatialIndex('geo_location_spatialindex'); | 從 geo 表中刪除空間索引   |

您也可以通過傳遞字段數組到 `dropIndex` 方法，遷移程序會根據表名、字段和鍵類型生成的索引名稱：

```php
<?php

Schema:table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### 外鍵約束

我們還可以通過 `foreign`、`references`、`on` 方法創建數據庫層的外鍵約束。比如我們讓 `posts` 表定義一個引用 `users` 表的 `id` 字段的 `user_id` 字段：

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

還可以為 `on delete` 和 `on update` 屬性指定所需的操作：

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

您可以通過 `dropForeign` 方法來刪除外鍵。外鍵約束採用的命名方式與索引相同，然後加上 `_foreign` 後綴：

```php
$table->dropForeign('posts_user_id_foreign');
```

或者傳遞一個字段數組，讓遷移程序按照約定的規則生成名稱：

```php
$table->dropForeign(['user_id'']);
```

您可以在遷移文件中使用以下方法來開啟或關閉外鍵約束：

```php
// 開啟外鍵約束
Schema::enableForeignKeyConstraints();
// 禁用外鍵約束
Schema::disableForeignKeyConstraints();
```
