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

定义该命令类所对应的命令有多形式，我们通过代码示例来演示其中一种，假设我们希望定义该命令类的命令为 `foo:hello`：   

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
    protected static $defaultName = 'foo:hello';
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
    protected static $defaultName = 'foo:hello';
    
    public function handle()
    {
        // 通过内置方法 line 在 Console 输出 Hello Hyperf.
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### 定义命令类的参数

在编写命令时，通常是通过 `参数` 和 `选项` 来收集用户的输入的，在收集一个用户输入前，必须对该 `参数` 和 `选项` 进行定义。

#### 参数

假设我们希望定义一个 `name` 参数，然后通过传递任意字符串如 `Hyperf` 于命令一起并执行 `php bin/hyperf.php foo:hello Hyperf` 输出 `Hello Hyperf`，我们通过代码来演示一下：

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
    protected static $defaultName = 'foo:hello';

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command')  //功能描述
             ->addArgument('name', InputArgument::REQUIRED, 'name 为必填参数');
    }

    public function handle()
    {
        // 从 $input 获取 name 参数
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }
    
}
``` 
执行 `php bin/hyperf.php foo:hello Hyperf` 我们就能看到输出了 `Hello Hyperf` 了。

### 附 `configure` 配置函数内常用的其他设置项，以下命令支持连贯操作。    

配置项 | 描述
---|---
setName('foo:hello') | 自定义命令名为: `foo:hello`, 如果通过配置自定义命令，可以代替通过类成员定义  
setDescription('Hyperf Demo Command') | 功能描述  
setHelp('可以执行本命令测试 php  bin/hyperf.php  foo:bar') | 本命令使用帮助信息
addArgument('params1', InputArgument::REQUIRED, 'parms1为必填') | 设置命令接受的参数为 `params1`,必填属性，参数三是对输入参数的描述
addArgument('params2', InputArgument::OPTIONAL, 'parms1为选填') | 设置命令接受的参数为 `params2`,选填属性，参数三是对输入参数的描述

