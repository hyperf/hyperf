# 数据库迁移

数据库迁移可以理解为对数据库结构的版本管理，可以有效的解决团队中跨成员对数据库结构的管理。

> 相关脚本的声明位置已从 database 组件移入 devtool 组件，故线上 `--no-dev` 环境下，需要手动将可执行的命令写入 `autoload/commands.php` 配置中。

# 生成迁移

通过 `gen:migration` 生成一个迁移文件，命令后面跟的是一个文件名参数，通常为这个迁移要打算做的事情。

```bash
php bin/hyperf.php gen:migration create_users_table
```

生成的迁移文件位于根目录下的 `migrations` 文件夹内，每个迁移文件都包含一个时间戳，以便迁移程序确定迁移的顺序。

`--table` 选项可以用来指定数据表的名称，指定的表名将会默认生成在迁移文件中。   
`--create` 选项也是用来指定数据表的名称，但跟 `--table` 的差异在于该选项是生成创建表的迁移文件，而 `--table` 是用于修改表的迁移文件。

```bash
php bin/hyperf.php gen:migration create_users_table --table=users
php bin/hyperf.php gen:migration create_users_table --create=users
```

# 迁移结构

迁移类默认会包含 `2` 个方法：`up` 和 `down`。   
`up` 方法用于添加新的数据表，字段或者索引到数据库，而 `down` 方法就是 `up` 方法的反操作，和 `up` 里的操作相反，以便在回退的时候执行。

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

# 运行迁移

通过执行 `migrate` 命令运行所有尚未完成的迁移文件：

```bash
php bin/hyperf.php migrate
```

## 强制执行迁移

一些迁移操作是具有破坏性的，这意味着可能会导致数据丢失，为了防止有人在生产环境中运行这些命令，系统会在这些命令运行之前与你进行确认，但如果您希望忽略这些确认信息强制运行命令，可以使用 `--force` 标记：

```bash
php bin/hyperf.php migrate --force
```

## 回滚迁移

若您希望回滚最后一次的迁移，可以通过 `migrate:rollback` 命令回滚最后一次的迁移，注意一次迁移可能会包含多个迁移文件：

```bash
php bin/hyperf.php migrate:rollback
```

您还可以在 `migrate:rollback` 命令后面加上 `step` 参数来设置回滚迁移的次数，比如以下命令将回滚最近 5 次迁移：

```bash
php bin/hyperf.php migrate:rollback --step=5
```

如果您希望回滚所有的迁移，可以通过 `migrate:reset` 来回滚：

```bash
php bin/hyperf.php migrate:reset
```

## 回滚并迁移

`migrate:refresh` 命令不仅会回滚迁移还会接着运行 `migrate` 命令，这样可以高效地重建某些迁移：

```bash
php bin/hyperf.php migrate:refresh

// 重建数据库结构并执行数据填充
php bin/hyperf.php migrate:refresh --seed
```

通过 `--step` 参数指定回滚和重建次数，比如以下命令将回滚并重新执行最后 5 次迁移：

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## 重建数据库

通过 `migrate:fresh` 命令可以高效地重建整个数据库，这个命令会先删除所有的数据库，然后再执行 `migrate` 命令：

```bash
php bin/hyperf.php migrate:fresh

// 重建数据库结构并执行数据填充
php bin/hyperf.php migrate:fresh --seed
```

# 数据表

在迁移文件中主要通过 `Hyperf\Database\Schema\Schema` 类来定义数据表和管理迁移流程。

## 创建数据表

通过 `create` 方法来创建新的数据库表。 `create` 方法接受两个参数：第一个参数为数据表的名称，第二个参数是一个 `闭包(Closure)`，此闭包会接收一个用于定义新数据表的 `Hyperf\Database\Schema\Blueprint` 对象：

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

您可以在数据库结构生成器上使用以下命令来定义表的选项：

```php
// 指定表存储引擎
$table->engine = 'InnoDB';
// 指定数据表的默认字符集
$table->charset = 'utf8';
// 指定数据表默认的排序规则
$table->collation = 'utf8_unicode_ci';
// 创建临时表
$table->temporary();
```

## 重命名数据表

若您希望重命名一个数据表，可以通过 `rename` 方法：

```php
Schema::rename($from, $to);
```

### 重命名带外键的数据表

在重命名表之前，您应该验证表上的所有外键约束在迁移文件中都有明确的名称，而不是让迁移程序按照约定来设置一个名称，否则，外键的约束名称将引用旧表名。

## 删除数据表

删除一个已存在的数据表，可以通过 `drop` 或 `dropIfExists` 方法：

```php
Schema::drop('users');

Schema::dropIfExists('users');
```

## 检查数据表或字段是否存在

可以通过 `hasTable` 和 `hasColumn` 方法来检查数据表或字段是否存在:

```php
if (Schema::hasTable('users')) {
    //
}

if (Schema::hasColumn('name', 'email')) {
    //
}
```

## 数据库连接选项

如果在同时管理多个数据库的情况下，不同的迁移会对应不同的数据库连接，那么此时我们可以在迁移文件中通过重写父类的 `$connection` 类属性来定义不同的数据库连接：

```php
<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    // 这里对应 config/autoload/databases.php 内的连接 key
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

## 创建字段

在 `table` 或 `create` 方法的第二个参数的 `闭包(Closure)` 内定义该迁移文件要执行的定义或变更，比如下面的代码为定义一个 `name` 的字符串字段：

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

## 可用的字段定义方法

| 命令                                     | 描述                                                    |
| ---------------------------------------- | ------------------------------------------------------- |
| $table->bigIncrements('id');             | 递增 ID（主键），相当于「UNSIGNED BIG INTEGER」         |
| $table->bigInteger('votes');             | 相当于 BIGINT                                           |
| $table->binary('data');                  | 相当于 BLOB                                             |
| $table->boolean('confirmed');            | 相当于 BOOLEAN                                          |
| $table->char('name', 100);               | 相当于带有长度的 CHAR                                   |
| $table->date('created_at');              | 相当于 DATE                                             |
| $table->dateTime('created_at');          | 相当于 DATETIME                                         |
| $table->dateTimeTz('created_at');        | 相当于带时区 DATETIME                                   |
| $table->decimal('amount', 8, 2);         | 相当于带有精度与基数 DECIMAL                            |
| $table->double('amount', 8, 2);          | 相当于带有精度与基数 DOUBLE                             |
| $table->enum('level', ['easy', 'hard']); | 相当于 ENUM                                             |
| $table->float('amount', 8, 2);           | 相当于带有精度与基数 FLOAT                              |
| $table->geometry('positions');           | 相当于 GEOMETRY                                         |
| $table->geometryCollection('positions'); | 相当于 GEOMETRYCOLLECTION                               |
| $table->increments('id');                | 递增的 ID (主键)，相当于「UNSIGNED INTEGER」            |
| $table->integer('votes');                | 相当于 INTEGER                                          |
| $table->ipAddress('visitor');            | 相当于 IP 地址                                          |
| $table->json('options');                 | 相当于 JSON                                             |
| $table->jsonb('options');                | 相当于 JSONB                                            |
| $table->lineString('positions');         | 相当于 LINESTRING                                       |
| $table->longText('description');         | 相当于 LONGTEXT                                         |
| $table->macAddress('device');            | 相当于 MAC 地址                                         |
| $table->mediumIncrements('id');          | 递增 ID (主键) ，相当于「UNSIGNED MEDIUM INTEGER」      |
| $table->mediumInteger('votes');          | 相当于 MEDIUMINT                                        |
| $table->mediumText('description');       | 相当于 MEDIUMTEXT                                       |
| $table->morphs('taggable');              | 相当于加入递增的 taggable_id 与字符串 taggable_type     |
| $table->multiLineString('positions');    | 相当于 MULTILINESTRING                                  |
| $table->multiPoint('positions');         | 相当于 MULTIPOINT                                       |
| $table->multiPolygon('positions');       | 相当于 MULTIPOLYGON                                     |
| $table->nullableMorphs('taggable');      | 相当于可空版本的 morphs() 字段                          |
| $table->nullableTimestamps();            | 相当于可空版本的 timestamps() 字段                      |
| $table->point('position');               | 相当于 POINT                                            |
| $table->polygon('positions');            | 相当于 POLYGON                                          |
| $table->rememberToken();                 | 相当于可空版本的 VARCHAR (100) 的 remember_token 字段   |
| $table->smallIncrements('id');           | 递增 ID (主键) ，相当于「UNSIGNED SMALL INTEGER」       |
| $table->smallInteger('votes');           | 相当于 SMALLINT                                         |
| $table->softDeletes();                   | 相当于为软删除添加一个可空的 deleted_at 字段            |
| $table->softDeletesTz();                 | 相当于为软删除添加一个可空的 带时区的 deleted_at 字段   |
| $table->string('name', 100);             | 相当于带长度的 VARCHAR                                  |
| $table->text('description');             | 相当于 TEXT                                             |
| $table->time('sunrise');                 | 相当于 TIME                                             |
| $table->timeTz('sunrise');               | 相当于带时区的 TIME                                     |
| $table->timestamp('added_on');           | 相当于 TIMESTAMP                                        |
| $table->timestampTz('added_on');         | 相当于带时区的 TIMESTAMP                                |
| $table->timestamps();                    | 相当于可空的 created_at 和 updated_at TIMESTAMP         |
| $table->timestampsTz();                  | 相当于可空且带时区的 created_at 和 updated_at TIMESTAMP |
| $table->tinyIncrements('id');            | 相当于自动递增 UNSIGNED TINYINT                         |
| $table->tinyInteger('votes');            | 相当于 TINYINT                                          |
| $table->unsignedBigInteger('votes');     | 相当于 Unsigned BIGINT                                  |
| $table->unsignedDecimal('amount', 8, 2); | 相当于带有精度和基数的 UNSIGNED DECIMAL                 |
| $table->unsignedInteger('votes');        | 相当于 Unsigned INT                                     |
| $table->unsignedMediumInteger('votes');  | 相当于 Unsigned MEDIUMINT                               |
| $table->unsignedSmallInteger('votes');   | 相当于 Unsigned SMALLINT                                |
| $table->unsignedTinyInteger('votes');    | 相当于 Unsigned TINYINT                                 |
| $table->uuid('id');                      | 相当于 UUID                                             |
| $table->year('birth_year');              | 相当于 YEAR                                             |
| $table->comment('Table Comment');        | 设置表注释，相当于 COMMENT                              |

## 修改字段

### 先决条件

在修改字段之前，请确保将 `doctrine/dbal` 依赖添加到 `composer.json` 文件中。Doctrine DBAL 库用于确定字段的当前状态， 并创建对该字段进行指定调整所需的 SQL 查询：

```bash
composer require "doctrine/dbal:^3.0"
```

### 更新字段属性

`change` 方法可以将现有的字段类型修改为新的类型或修改其它属性。

```php
<?php

Schema::create('users', function (Blueprint $table) {
    // 将字段的长度修改为 50
    $table->string('name', 50)->change();
});
```

或修改字段为 `可为空`：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 将字段的长度修改为 50 并允许为空
    $table->string('name', 50)->nullable()->change();
});
```

> 只有下面的字段类型能被 "修改"： bigInteger、 binary、 boolean、date、dateTime、dateTimeTz、decimal、integer、json、 longText、mediumText、smallInteger、string、text、time、 unsignedBigInteger、unsignedInteger and unsignedSmallInteger。

### 重命名字段

可以通过 `renameColumn` 方法来重命名字段：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 将字段从 from 重命名为 to
    $table->renameColumn('from', 'to')->change();
});
```

> 当前不支持 enum 类型的字段重命名。

### 删除字段

可以通过 `dropColumn` 方法来删除字段：

```php
<?php

Schema::table('users', function (Blueprint $table) {
    // 删除 name 字段
    $table->dropColumn('name');
    // 删除多个字段
    $table->dropColumn(['name', 'age']);
});
```

#### 可用的命令别名

| 命令                         | 描述                                  |
| ---------------------------- | ------------------------------------- |
| $table->dropRememberToken(); | 删除 remember_token 字段。            |
| $table->dropSoftDeletes();   | 删除 deleted_at 字段。                |
| $table->dropSoftDeletesTz(); | dropSoftDeletes() 方法的别名。        |
| $table->dropTimestamps();    | 删除 created_at and updated_at 字段。 |
| $table->dropTimestampsTz();  | dropTimestamps() 方法的别名。         |

## 索引

### 创建索引

###  唯一索引
通过 `unique` 方法来创建一个唯一索引：

```php
<?php

// 在定义时创建索引
$table->string('name')->unique();
// 在定义完字段之后创建索引
$table->unique('name');
```

#### 复合索引

```php
<?php

// 创建一个复合索引
$table->index(['account_id', 'created_at'], 'index_account_id_and_created_at');
```

#### 定义索引名称

迁移程序会自动生成一个合理的索引名称，每个索引方法都接受一个可选的第二个参数来指定索引的名称：

```php
<?php

// 定义唯一索引名称为 unique_name
$table->unique('name', 'unique_name');
// 定义一个复合索引名称为 index_account_id_and_created_at
$table->index(['account_id', 'created_at'], '');
```

##### 可用的索引类型

| 命令                                  | 描述         |
| ------------------------------------- | ------------ |
| $table->primary('id');                | 添加主键     |
| $table->primary(['id', 'parent_id']); | 添加复合键   |
| $table->unique('email');              | 添加唯一索引 |
| $table->index('state');               | 添加普通索引 |
| $table->spatialIndex('location');     | 添加空间索引 |

### 重命名索引

您可通过 `renameIndex` 方法重命名一个索引的名称：

```php
<?php

$table->renameIndex('from', 'to');
```

### 删除索引

您可通过下面的方法来删除索引，默认情况下迁移程序会自动将数据库名称、索引的字段名及索引类型简单地连接在一起作为名称。举例如下:

| 命令                                                   | 描述                      |
| ------------------------------------------------------ | ------------------------- |
| $table->dropPrimary('users_id_primary');               | 从 users 表中删除主键     |
| $table->dropUnique('users_email_unique');              | 从 users 表中删除唯一索引 |
| $table->dropIndex('geo_state_index');                  | 从 geo 表中删除基本索引   |
| $table->dropSpatialIndex('geo_location_spatialindex'); | 从 geo 表中删除空间索引   |

您也可以通过传递字段数组到 `dropIndex` 方法，迁移程序会根据表名、字段和键类型生成的索引名称：

```php
<?php

Schema:table('users', function (Blueprint $table) {
    $table->dropIndex(['account_id', 'created_at']);
});
```

### 外键约束

我们还可以通过 `foreign`、`references`、`on` 方法创建数据库层的外键约束。比如我们让 `posts` 表定义一个引用 `users` 表的 `id` 字段的 `user_id` 字段：

```php
Schema::table('posts', function (Blueprint $table) {
    $table->unsignedInteger('user_id');

    $table->foreign('user_id')->references('id')->on('users');
});
```

还可以为 `on delete` 和 `on update` 属性指定所需的操作：

```php
$table->foreign('user_id')
      ->references('id')->on('users')
      ->onDelete('cascade');
```

您可以通过 `dropForeign` 方法来删除外键。外键约束采用的命名方式与索引相同，然后加上 `_foreign` 后缀：

```php
$table->dropForeign('posts_user_id_foreign');
```

或者传递一个字段数组，让迁移程序按照约定的规则生成名称：

```php
$table->dropForeign(['user_id'']);
```

您可以在迁移文件中使用以下方法来开启或关闭外键约束：

```php
// 开启外键约束
Schema::enableForeignKeyConstraints();
// 禁用外键约束
Schema::disableForeignKeyConstraints();
```
