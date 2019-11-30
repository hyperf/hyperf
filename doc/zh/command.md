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

## 生成命令、描述以及帮助信息

如果你有安装 [hyperf/devtool](https://github.com/hyperf/devtool) 组件的话，可以通过 `gen:command` 命令来生成一个自定义命令：

```bash
php bin/hyperf.php gen:command FooCommand
```
执行上述命令后，便会在 `app/Command` 文件夹内生成一个配置好的 `FooCommand` 类了。

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class FooCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setName('demo:command')  // 命令名称
             ->setDescription('Hyperf Demo Command')  //功能描述
             ->setHelp("you can exec this command test: php  bin/hyperf.php  demo:command ");  // 使用帮助
    }
    
    /**
    * 命令类实际运行的逻辑是取决于 `handle` 方法内的代码，也就意味着 `handle` 方法就是命令的入口。
    */
    public function handle()
    {
        $this->line('Hello Hyperf!', 'info');
    }
}

```

### 定义带参数的命令

在编写命令时，通常是通过 `参数` 和 `选项` 来收集用户的输入的，在收集一个用户输入前，必须对该 `参数` 和 `选项` 进行定义。

#### 参数

假设我们希望定义两个 `params1` `prams2` 参数，然后通过传递任意字符串如 `Hello` `Hyperf` 于命令一起并执行 `php bin/hyperf.php foo:bar Hello Hyperf` 输出 `Hello Hyperf`，我们通过代码来演示一下：

```php
<?php

declare(strict_types=1);

namespace App\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @Command
 */
class FooCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setName('foo:bar')
             ->setDescription('Hyperf Demo Command')
             ->setHelp("you can exec this command test: php  bin/hyperf.php  foo:bar Hello Hyperf")
             ->addArgument('params1', InputArgument::REQUIRED, 'parms1 为必填参数')
             ->addArgument('params2', InputArgument::OPTIONAL, 'parms2 为可选参数');

    }

    public function handle()
    {
         $params1 = $this->input->getArgument('params1');
         $params2 = $this->input->getArgument('params2');
        $this->line("{$params1} {$params2}", 'info');
    }
}
``` 

执行 `php bin/hyperf.php foo:hello Hyperf` 我们就能看到输出了 `Hello Hyperf` 了。

