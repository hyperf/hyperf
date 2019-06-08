# 数据库迁移

数据库迁移可以理解为对数据库结构的版本管理，可以有效的解决团队中跨成员对数据库结构的管理。

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

一些迁移操作是具有破坏性的，这意味着可能会导致数据丢失，为了防止有人在生产环境中运行这些命令，系统会在这些命令运行之前与你进行确认，但如果您希望忽略这些确认信息强制运行命令，可以使用 `--force` 标记:

```bash
php bin/hyperf.php migrate --force
```

## 回滚迁移

若您希望回滚最后一次的迁移，可以通过 `migrate:rollback` 命令回滚最后一侧的迁移，注意一次迁移可能会包含多个迁移文件：

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

通过 `--step` 参数指定回滚和重建次数，比如以下命令将回滚并重新执行最后 5 次迁移:

```bash
php bin/hyperf.php migrate:refresh --step=5
```

## 重建数据库

通过 `migrate:fresh` 命令可以高效地重建整个数据库，这个命令会先删除所有的数据库，然后再执行 `migrate` 命令:

```bash
php bin/hyperf.php migrate:fresh

// 重建数据库结构并执行数据填充
php bin/hyperf.php migrate:fresh --seed
```

# 数据表

在迁移文件中主要通过 `Hyperf\Database\Schema\Schema` 类来定义数据表和管理迁移流程。

## 创建数据表

通过 `create` 方法来创建新的数据库表。 `create` 方法接受两个参数：第一个参数为数据表的名称，第二个参数是一个 `闭包(Closure)`，此闭包会接收一个用于定义新数据表的 `Hyperf\Database\Schema\Blueprint` 对象:

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

如果在同时管理多个数据库的情况下，不同的迁移会对应不同的数据库连接，那么此时我们可以在迁移文件中通过重写父类的 `$connection` 类属性来定义不同的数据库连接:

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

您可以在数据库结构生成器上使用以下命令来定义表的选项:

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

若您希望重命名一个数据表，可以通过 `rename` 方法:

```php
Schema::rename($from, $to);
```

### 重命名带外键的数据表

在重命名表之前，您应该验证表上的所有外键约束在迁移文件中都有明确的名称，而不是让迁移程序按照约定来设置一个名称，否则，外键的约束名称将引用旧表名。

## 删除数据表

删除一个已存在的数据表，可以通过 `drop` 或 `dropIfExists` 方法:

```php
Schema::drop('users');

Schema::dropIfExists('users');
```