# 命令行

Hyperf 的命令行默认由 [hyperf/command](https://github.com/hyperf/command) 组件提供，而该组件本身也是基于 [symfony/console](https://github.com/symfony/console) 的抽象。

# 安装

通常来说该组件会默认存在，但如果您希望用于非 Hyperf 项目，也可通过下面的命令依赖 [hyperf/command](https://github.com/hyperf/command) 组件：

```bash
composer require hyperf/command
```

# 查看命令列表

直接运行 `php bin/hyperf.php` 不带任何的参数即为输出命令列表。

# 自定义命令

## 生成命令

如果你有安装 [hyperf/devtool](https://github.com/hyperf/devtool) 组件的话，可以通过 `gen:command` 命令来生成一个自定义命令：

```bash
php bin/hyperf.php gen:command FooCommand
```
执行上述命令后，便会在 `app/Command` 文件夹内生成一个配置好的 `FooCommand` 类了。

### 定义命令

定义该命令类所对应的命令有两种形式，一种是通过 `$name` 属性定义，另一种是通过构造函数传参来定义，我们通过代码示例来演示一下，假设我们希望定义该命令类的命令为 `foo:hello`：   

#### `$name` 属性定义：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class FooCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     *
     * @var string
     */
    protected $name = 'foo:hello';
}
```

#### 构造函数传参定义：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class FooCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('foo:hello');    
    }
}
```

### 定义命令类逻辑

命令类实际运行的逻辑是取决于 `handle` 方法内的代码，也就意味着 `handle` 方法就是命令的入口。

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

/**
 * @Command
 */
class FooCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     *
     * @var string
     */
    protected $name = 'foo:hello';
    
    public function handle()
    {
        // 通过内置方法 line 在 Console 输出 Hello Hyperf.
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### 定义命令类的参数

在编写命令时，通常是通过 `参数` 和 `选项` 来收集用户的输入的，在收集一个用户输入前，必须对该 `参数` 或 `选项` 进行定义。

#### 参数

假设我们希望定义两个 `param1` `param2`  参数，然后通过传递任意字符串如 `Hello Hyperf` 于命令一起并执行 `php bin/hyperf.php foo:hello Hello Hyperf` 输出 `Hello Hyperf`，我们通过代码来演示一下：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class FooCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     *
     * @var string
     */
    protected $name = 'foo:hello';

    public function handle()
    {
        // 从 $input 获取 name 参数
        $param1 = $this->input->getArgument('param1');
        $param2 = $this->input->getArgument('param2') ?? 'Hyperf';
        $this->line($param1.' '.$param2, 'info');
    }
    
    public function configure()
    {
             $this
             //->setName('model:create')  //  也可以在配置项设置命令名称,效果等同于给类成员 `$name` 赋值 
             ->setDescription('Hyperf Command 命令功能介绍...')
             ->setHelp('运行命令时使用 "--help" 选项时的完整命令描述')
             ->addArgument('param1',InputArgument::REQUIRED,'参数一是一个必填参数...')
             ->addArgument('param2',InputArgument::OPTIONAL,'参数二是非必填参数...');
    }
}
``` 

执行 `php bin/hyperf.php foo:hello Hello Hyperf` 我们就能看到输出了 `Hello Hyperf` 了。

