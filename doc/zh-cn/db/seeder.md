# 数据填充

通过使用 seeder 可以快速填充测试数据到你的数据库中。

# 生成填充类

使用 `gen:seeder` 快速生成一个填充类，命令后面跟的是一个文件名参数。

```bash
php bin/hyperf.php gen:seeder user_seeder
```

生成的填充文件默认位于根目录下的 seeders 文件夹内。

* 使用 `--path` 参数可以指定填充类所在的目录，以下命令将在根目录下的 database/seeds 目录下创建 user_seeder.php 文件。

```bash
php bin/hyperf.php gen:seeder user_seeder --path database/seeds
```

以下为填充一条用户数据的例子：

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Hyperf\DbConnection\Db::table('users')
            ->insert([
                'username' => \Hyperf\Utils\Str::random(8),
                'email' => \Hyperf\Utils\Str::random(10) . '@gmail.com',
                'password' => password_hash(\Hyperf\Utils\Str::random(8), PASSWORD_BCRYPT),
            ]);
    }
}

```

# 使用填充类

seeder 类只包含一个默认方法：run 。这个方法会在执行 `db:seed` 这个命令时被调用。在 run 方法里你可以根据需要在数据库中插入数据。你也可以用 [查询构造器](/zh-cn/db/querybuilder) 或 [模型工厂](#创建模型工厂) 来手动插入数据。

```bash
php bin/hyperf.php db:seed
```

* 通过 `--path` 参数，指定 seeder 类所在的目录。

# 使用模型工厂填充数据

hyperf 提供了与 laravel 相同的模型工厂，通过使用模型工厂可以轻松的生成大量的测试数据。

> 在使用模型工厂填充数据之前，你应该先知道[如何创建模型工厂](#创建模型工厂)。

以下为使用模型工厂填充 50 条用户数据的例子：

* `factories/user_factory.php`

```php
<?php
declare(strict_types=1);

use Faker\Generator as Faker;
use Hyperf\Database\Model\Factory;

/** @var Factory $factory */
$factory->define(App\Model\User::class, function (Faker $faker) {
    return [
        'username' => $faker->name,
        'email' => $faker->safeEmail,
        'password' => $faker->password,
    ];
});

```

* `seeders/user_seeder.php`

```php
<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Database\Model\Factory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $factory = ApplicationContext::getContainer()->get(Factory::class);
        $factory->of(App\Model\User::class)->times(50)->create();
    }
}

```

# 创建模型工厂

> 模型工厂的创建需要依赖 `fzaninotto/faker` 扩展，如果没有安装，请使用以下命令安装。

```bash
composer require fzaninotto/faker --dev
```

使用 `gen:factory` 快速生成一个模型工厂，命令后面跟的是文件名。或者你也可以在使用 `gen:model` 时，通过 `--factory` 或 `-f` 参数直接生成模型工厂。

```bash
php bin/hyperf.php gen:factory user_factory
```

或者

```bash
php bin/hyperf.php gen:model users -f
```
生成模型工厂的文件内容如下：

```php
<?php
declare(strict_types=1);

use Faker\Generator as Faker;
use Hyperf\Database\Model\Factory;

/** @var Factory $factory */
$factory->define(Model::class, function (Faker $faker) {
    return [
        //
    ];
});

```

生成的模型工厂文件默认位于根目录下的 factories 文件夹内。

* 使用 `--model` 或 `-m` 参数，可以指定是为哪个模型创建的工厂。

```bash
php bin/hyperf.php gen:factory user_factory -m App/Model/User
```
