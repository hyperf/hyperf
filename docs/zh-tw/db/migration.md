# 資料庫遷移

資料庫遷移可以理解為對資料庫結構的版本管理，可以有效的解決團隊中跨成員對資料庫結構的管理。

> 相關指令碼的宣告位置已從 database 元件移入 devtool 元件，故線上 `--no-dev` 環境下，需要手動將可執行的命令寫入 `autoload/commands.php` 配置中。

# 生成遷移

通過 `gen:migration` 生成一個遷移檔案，命令後面跟的是一個檔名引數，通常為這個遷移要打算做的事情。

```bash
php bin/hyperf.php gen:migration create_users_table
```

生成的遷移檔案位於根目錄下的 `migrations` 資料夾內，每個遷移檔案都包含一個時間戳，以便遷移程式確定遷移的順序。

`--table` 選項可以用來指定資料表的名稱，指定的表名將會預設生成在遷移檔案中。   
`--create` 選項也是用來指定資料表的名稱，但跟 `--table` 的差異在於該選項是生成建立表的遷移檔案，而 `--table` 是用於修改表的遷移檔案。

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# 遷移結構

遷移類預設會包含 `2` 個方法：`up` 和 `down`。   
`up` 方法用於新增新的資料表，欄位或者索引到資料庫，而 `down` 方法就是 `up` 方法的反操作，和 `up` 裡的操作相反，以便在回退的時候執行。

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

# 執行遷移

通過執行 `migrate` 命令執行所有尚未完成的遷移檔案：

```bash
php bin/hyperf.php migrate
```

## 強制執行遷移

一些遷移操作是具有破壞性的，這意味著可能會導致資料丟失，為了防止有人在生產環境中執行這些命令，系統會在這些命令執行之前與你進行確認，但如果您希望忽略這些確認資訊強制執行命令，可以使用 `--force` 標記：

```bash
php bin/hyperf.php migrate --force
```

## 回滾遷移

若您希望回滾最後一次的遷移，可以通過 `migrate:rollback` 命令回滾最後一次的遷移，注意一次遷移可能會包含多個遷移檔案：

```bash
php bin/hyperf.php migrate:rollback
```

您還可以在 `migrate:rollback` 命令後面加上 `step` 引數來設定回滾遷移的次數，比如以下命令將回滾最近 5 次遷移：

```bash
php bin/hyperf.php migrate:rollback --step=5
```

如果您希望回滾所有的遷移，可以通過 `migrate:reset` 來回滾：

```bash
php bin/hyperf.php migrate:reset
```

## 回滾並遷移

`migrate:refresh` 命令不僅會回滾遷移還會接著執行 `migrate` 命令，這樣可以高效地重建某些遷移：

```bash
php bin/hyperf.php migrate:refresh

// 重建資料庫結構並執行資料填充
php bin/hyperf.php migrate:refresh --seed
```

通過 `--step` 引數指定回滾和重建次數，比如以下命令將回滾並重新執行最後 5 次遷移：

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## 重建資料庫

通過 `migrate:fresh` 命令可以高效地重建整個資料庫，這個命令會先刪除所有的資料庫，然後再執行 `migrate` 命令：

```bash
php bin/hyperf.php migrate:fresh

// 重建資料庫結構並執行資料填充
php bin/hyperf.php migrate:fresh --seed
```

# 資料表

在遷移檔案中主要通過 `Hyperf\Database\Schema\Schema` 類來定義資料表和管理遷移流程。

## 建立資料表

通過 `create` 方法來建立新的資料庫表。 `create` 方法接受兩個引數：第一個引數為資料表的名稱，第二個引數是一個 `閉包(Closure)`，此閉包會接收一個用於定義新資料表的 `Hyperf\Database\Schema\Blueprint` 物件：

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

您可以在資料庫結構生成器上使用以下命令來定義表的選項：

```php
// 指定表儲存引擎
$table->engine = 'InnoDB';
// 指定資料表的預設字符集
$table->charset = 'utf8';
// 指定資料表預設的排序規則
$table->collation = 'utf8_unicode_ci';
// 建立臨時表
$table->temporary();
```

## 重新命名資料表

若您希望重新命名一個數據表，可以通過 `rename` 方法：

```php
Schema::rename($from, $to);
```

### 重新命名帶外來鍵的資料表

在重命名錶之前，您應該驗證表上的所有外來鍵約束在遷移檔案中都有明確的名稱，而不是讓遷移程式按照約定來設定一個名稱，否則，外來鍵的約束名稱將引用舊錶名。

## 刪除資料表

刪除一個已存在的資料表，可以通過 `drop` 或 `dropIfExists` 方法：

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## 檢查資料表或欄位是否存在

可以通過 `hasTable` 和 `hasColumn` 方法來檢查資料表或欄位是否存在:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## 資料庫連線選項

如果在同時管理多個數據庫的情況下，不同的遷移會對應不同的資料庫連線，那麼此時我們可以在遷移檔案中通過重寫父類的 `$connection` 類屬性來定義不同的資料庫連線：

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // 這裡對應 config/autoload/databases.php 內的連線 key
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

# 欄位

## 建立欄位

在 `table` 或 `create` 方法的第二個引數的 `閉包(Closure)` 內定義該遷移檔案要執行的定義或變更，比如下面的程式碼為定義一個 `name` 的字串欄位：

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

## 可用的欄位定義方法

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
| $table->morphs('taggable');              | 相當於加入遞增的 taggable_id 與字串 taggable_type     |
| $table->multiLineString('positions');    | 相當於 MULTILINESTRING                                  |
| $table->multiPoint('positions');         | 相當於 MULTIPOINT                                       |
| $table->multiPolygon('positions');       | 相當於 MULTIPOLYGON                                     |
| $table->nullableMorphs('taggable');      | 相當於可空版本的 morphs() 欄位                          |
| $table->nullableTimestamps();            | 相當於可空版本的 timestamps() 欄位                      |
| $table->point('position');               | 相當於 POINT                                            |
| $table->polygon('positions');            | 相當於 POLYGON                                          |
| $table->rememberToken();                 | 相當於可空版本的 VARCHAR (100) 的 remember_token 欄位   |
| $table->smallIncrements('id');           | 遞增 ID (主鍵) ，相當於「UNSIGNED SMALL INTEGER」       |
| $table->smallInteger('votes');           | 相當於 SMALLINT                                         |
| $table->softDeletes();                   | 相當於為軟刪除新增一個可空的 deleted_at 欄位            |
| $table->softDeletesTz();                 | 相當於為軟刪除新增一個可空的 帶時區的 deleted_at 欄位   |
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
| $table->comment('Table Comment');        | 設定表註釋，相當於 COMMENT                              |

## 修改欄位

### 先決條件

在修改欄位之前，請確保將 `doctrine/dbal` 依賴新增到 `composer.json` 檔案中。Doctrine DBAL 庫用於確定欄位的當前狀態， 並建立對該欄位進行指定調整所需的 SQL 查詢：

```bash
composer require "doctrine/dbal:^3.0"
```

### 更新欄位屬性

`change` 方法可以將現有的欄位型別修改為新的型別或修改其它屬性。

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // 將欄位的長度修改為 50
    $table->string('name', 50)->change();
});
```

或修改欄位為 `可為空`：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 將欄位的長度修改為 50 並允許為空
    $table->string('name', 50)->nullable()->change();
});
```

> 只有下面的欄位型別能被 "修改"： bigInteger、 binary、 boolean、date、dateTime、dateTimeTz、decimal、integer、json、 longText、mediumText、smallInteger、string、text、time、 unsignedBigInteger、unsignedInteger and unsignedSmallInteger。

### 重新命名欄位

可以通過 `renameColumn` 方法來重新命名欄位：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 將欄位從 from 重新命名為 to
    $table->renameColumn('from', 'to')->change();
});
```

> 當前不支援 enum 型別的欄位重新命名。

### 刪除欄位

可以通過 `dropColumn` 方法來刪除欄位：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 刪除 name 欄位
    $table->dropColumn('name');
    // 刪除多個欄位
    $table->dropColumn(['name', 'age']);
});
```

#### 可用的命令別名

| 命令                         | 描述                                  |
| ---------------------------- | ------------------------------------- |
| $table->dropRememberToken(); | 刪除 remember_token 欄位。            |
| $table->dropSoftDeletes();   | 刪除 deleted_at 欄位。                |
| $table->dropSoftDeletesTz(); | dropSoftDeletes() 方法的別名。        |
| $table->dropTimestamps();    | 刪除 created_at and updated_at 欄位。 |
| $table->dropTimestampsTz();  | dropTimestamps() 方法的別名。         |

## 索引

### 建立索引

###  唯一索引
通過 `unique` 方法來建立一個唯一索引：

```php
<?php

// 在定義時建立索引
$table->string('name')->unique();
// 在定義完欄位之後建立索引
$table->unique('name');
```

#### 複合索引

```php
<?php

// 建立一個複合索引
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### 定義索引名稱

遷移程式會自動生成一個合理的索引名稱，每個索引方法都接受一個可選的第二個引數來指定索引的名稱：

```php
<?php

// 定義唯一索引名稱為 unique_name
$table->unique('name', 'unique_name');
// 定義一個複合索引名稱為 index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### 可用的索引型別

| 命令                                  | 描述         |
| ------------------------------------- | ------------ |
| $table->primary('id');                | 新增主鍵     |
| $table->primary(['id', 'parent_id']); | 新增複合鍵   |
| $table->unique('email');              | 新增唯一索引 |
| $table->index('state');               | 新增普通索引 |
| $table->spatialIndex('location');     | 新增空間索引 |

### 重新命名索引

您可通過 `renameIndex` 方法重新命名一個索引的名稱：

```php
<?php

$table->renameIndex('from', 'to');
```

### 刪除索引

您可通過下面的方法來刪除索引，預設情況下遷移程式會自動將資料庫名稱、索引的欄位名及索引型別簡單地連線在一起作為名稱。舉例如下:

| 命令                                                   | 描述                      |
| ------------------------------------------------------ | ------------------------- |
| $table->dropPrimary('users_id_primary');               | 從 users 表中刪除主鍵     |
| $table->dropUnique('users_email_unique');              | 從 users 表中刪除唯一索引 |
| $table->dropIndex('geo_state_index');                  | 從 geo 表中刪除基本索引   |
| $table->dropSpatialIndex('geo_location_spatialindex'); | 從 geo 表中刪除空間索引   |

您也可以通過傳遞欄位陣列到 `dropIndex` 方法，遷移程式會根據表名、欄位和鍵型別生成的索引名稱：

```php
<?php

Schema:table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### 外來鍵約束

我們還可以通過 `foreign`、`references`、`on` 方法建立資料庫層的外來鍵約束。比如我們讓 `posts` 表定義一個引用 `users` 表的 `id` 欄位的 `user_id` 欄位：

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

您可以通過 `dropForeign` 方法來刪除外來鍵。外來鍵約束採用的命名方式與索引相同，然後加上 `_foreign` 字尾：

```php
$table->dropForeign('posts_user_id_foreign');
```

或者傳遞一個欄位陣列，讓遷移程式按照約定的規則生成名稱：

```php
$table->dropForeign(['user_id'']);
```

您可以在遷移檔案中使用以下方法來開啟或關閉外來鍵約束：

```php
// 開啟外來鍵約束
Schema::enableForeignKeyConstraints();
// 禁用外來鍵約束
Schema::disableForeignKeyConstraints();
```
