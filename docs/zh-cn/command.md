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

定义该命令类所对应的命令有三种形式，第一种是通过 `$name` 属性定义，第二种是通过构造函数传参来定义，最后一种是通过注解来定义，我们通过代码示例来演示一下，假设我们希望定义该命令类的命令为 `foo:hello`：

#### `$name` 属性定义：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     */
    protected ?string $name = 'foo:hello';
}
```

#### 构造函数传参定义：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command]
class FooCommand extends HyperfCommand
{
    public function __construct()
    {
        parent::__construct('foo:hello');
    }
}
```

#### 注解定义：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;

#[Command(name: "foo:hello")]
class FooCommand extends HyperfCommand
{

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

#[Command]
class FooCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     */
    protected ?string $name = 'foo:hello';

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

假设我们希望定义一个 `name` 参数，然后通过传递任意字符串如 `Hyperf` 于命令一起并执行 `php bin/hyperf.php foo:hello Hyperf` 输出 `Hello Hyperf`，我们通过代码来演示一下：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class FooCommand extends HyperfCommand
{
    /**
     * 执行的命令行
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // 从 $input 获取 name 参数
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, '这里是对这个参数的解释']
        ];
    }
}
```

执行 `php bin/hyperf.php foo:hello Hyperf` 我们就能看到输出了 `Hello Hyperf` 了。

## 命令常用配置介绍

以下代码皆只修改 `configure` 和 `handle` 中的内容。

### 设置 Help

```
public function configure()
{
    parent::configure();
    $this->setHelp('Hyperf 自定义命令演示');
}

$ php bin/hyperf.php demo:command --help
...
Help:
  Hyperf 自定义命令演示

```

### 设置 Description

```
public function configure()
{
    parent::configure();
    $this->setDescription('Hyperf Demo Command');
}

$ php bin/hyperf.php demo:command --help
...
Description:
  Hyperf Demo Command

```

### 设置 Usage

```
public function configure()
{
    parent::configure();
    $this->addUsage('--name 演示代码');
}

$ php bin/hyperf.php demo:command --help
...
Usage:
  demo:command
  demo:command --name 演示代码
```

### 设置参数

参数支持以下模式。

|          模式           | 值 |                备注                 |
|:-----------------------:|:--:|:-----------------------------------:|
| InputArgument::REQUIRED | 1  | 参数必填，此种模式 default 字段无效 |
| InputArgument::OPTIONAL | 2  |    参数可选，常配合 default 使用    |
| InputArgument::IS_ARRAY | 4  |              数组类型               |

#### 可选类型

```
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::OPTIONAL, '姓名', 'Hyperf');
}

public function handle()
{
    $this->line($this->input->getArgument('name'));
}

$ php bin/hyperf.php demo:command
...
Hyperf

$ php bin/hyperf.php demo:command Swoole
...
Swoole
```

#### 数组类型

```
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::IS_ARRAY, '姓名');
}

public function handle()
{
    var_dump($this->input->getArgument('name'));
}

$ php bin/hyperf.php demo:command Hyperf Swoole
...
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

### 设置选项

选项支持以下模式。

|            模式             | 值 |     备注     |
|:---------------------------:|:--:|:------------:|
|   InputOption::VALUE_NONE   | 1  | 是否传入可选项 default 字段无效 |
| InputOption::VALUE_REQUIRED | 2  |   选项必填   |
| InputOption::VALUE_OPTIONAL | 4  |   选项可选   |
| InputOption::VALUE_IS_ARRAY | 8  |   选项数组   |

#### 是否传入可选项

```
public function configure()
{
    parent::configure();
    $this->addOption('opt', 'o', InputOption::VALUE_NONE, '是否优化');
}

public function handle()
{
    var_dump($this->input->getOption('opt'));
}

$ php bin/hyperf.php demo:command
bool(false)

$ php bin/hyperf.php demo:command -o
bool(true)

$ php bin/hyperf.php demo:command --opt
bool(true)
```

### 选项必填和可选

`VALUE_OPTIONAL` 在单独使用上与 `VALUE_REQUIRED` 并无二致

```
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_REQUIRED, '姓名', 'Hyperf');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}

$ php bin/hyperf.php demo:command
string(6) "Hyperf"

$ php bin/hyperf.php demo:command --name Swoole
string(6) "Swoole"
```

### 选项数组

`VALUE_IS_ARRAY` 和 `VALUE_OPTIONAL` 配合使用，可以达到传入多个 `Option` 的效果。

```
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, '姓名');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}

$ php bin/hyperf.php demo:command
array(0) {
}

$ php bin/hyperf.php demo:command --name Hyperf --name Swoole
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}

```

## 通过 `$signature` 配置命令行

命令行除了上述配置方法外，还支持使用 `$signature` 配置。

`$signature` 为字符串，分为三部分，分别是 `command` `argument` 和 `option`，如下：

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` 代表 `非必传`。
- `*` 代表 `数组`。
- `?*` 代表 `非必传的数组`。
- `=` 代表 `非 Bool`。

### 示例

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

#[Command]
class DebugCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    protected $signature = 'test:test {id : user_id} {--name= : user_name}';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        var_dump($this->input->getArguments());
        var_dump($this->input->getOptions());
    }
}

```

# 运行命令

!> 注意：在运行命令时，默认会触发事件分发，可通过添加 `--disable-event-dispatcher` 参数来开启。

## 命令行中运行

```bash
php bin/hyperf.php foo
```

## 在 Command 中运行其他命令

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;

#[Command]
class FooCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('foo');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('foo command');
    }

    public function handle()
    {
        $this->call('bar', [
            '--foo' => 'foo'
        ]);
    }
}
```

## 在非 Command 中运行命令

```php
$command = 'foo';

$params = ["command" => $command, "--foo" => "foo", "--bar" => "bar"];

// 可以根据自己的需求, 选择使用的 input/output
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// 这种方式: 不会暴露出命令执行中的异常, 不会阻止程序返回
$exitCode = $application->run($input, $output);

// 第二种方式: 会暴露异常, 需要自己捕捉和处理运行中的异常, 否则会阻止程序的返回
$exitCode = $application->find($command)->run($input, $output);
```
