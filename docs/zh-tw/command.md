# 命令列

Hyperf 的命令列預設由 [hyperf/command](https://github.com/hyperf/command) 元件提供，而該元件本身也是基於 [symfony/console](https://github.com/symfony/console) 的抽象。

# 安裝

通常來說該元件會預設存在，但如果您希望用於非 Hyperf 專案，也可透過下面的命令依賴 [hyperf/command](https://github.com/hyperf/command) 元件：

```bash
composer require hyperf/command
```

# 檢視命令列表

直接執行 `php bin/hyperf.php` 不帶任何的引數即為輸出命令列表。

# 自定義命令

## 生成命令

如果你有安裝 [hyperf/devtool](https://github.com/hyperf/devtool) 元件的話，可以透過 `gen:command` 命令來生成一個自定義命令：

```bash
php bin/hyperf.php gen:command FooCommand
```
執行上述命令後，便會在 `app/Command` 資料夾內生成一個配置好的 `FooCommand` 類了。

### 定義命令

定義該命令類所對應的命令有兩種形式，一種是透過 `$name` 屬性定義，另一種是透過建構函式傳參來定義，我們透過程式碼示例來演示一下，假設我們希望定義該命令類的命令為 `foo:hello`：

#### `$name` 屬性定義：

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
     * 執行的命令列
     */
    protected ?string $name = 'foo:hello';
}
```

#### 建構函式傳參定義：

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

### 定義命令類邏輯

命令類實際執行的邏輯是取決於 `handle` 方法內的程式碼，也就意味著 `handle` 方法就是命令的入口。

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
     * 執行的命令列
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // 透過內建方法 line 在 Console 輸出 Hello Hyperf.
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### 定義命令類的引數

在編寫命令時，通常是透過 `引數` 和 `選項` 來收集使用者的輸入的，在收集一個使用者輸入前，必須對該 `引數` 或 `選項` 進行定義。

#### 引數

假設我們希望定義一個 `name` 引數，然後透過傳遞任意字串如 `Hyperf` 於命令一起並執行 `php bin/hyperf.php foo:hello Hyperf` 輸出 `Hello Hyperf`，我們透過程式碼來演示一下：

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
     * 執行的命令列
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // 從 $input 獲取 name 引數
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, '這裡是對這個引數的解釋']
        ];
    }
}
```

執行 `php bin/hyperf.php foo:hello Hyperf` 我們就能看到輸出了 `Hello Hyperf` 了。

## 命令常用配置介紹

以下程式碼皆只修改 `configure` 和 `handle` 中的內容。

### 設定 Help

```
public function configure()
{
    parent::configure();
    $this->setHelp('Hyperf 自定義命令演示');
}

$ php bin/hyperf.php demo:command --help
...
Help:
  Hyperf 自定義命令演示

```

### 設定 Description

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

### 設定 Usage

```
public function configure()
{
    parent::configure();
    $this->addUsage('--name 演示程式碼');
}

$ php bin/hyperf.php demo:command --help
...
Usage:
  demo:command
  demo:command --name 演示程式碼
```

### 設定引數

引數支援以下模式。

|          模式           | 值 |                備註                 |
|:-----------------------:|:--:|:-----------------------------------:|
| InputArgument::REQUIRED | 1  | 引數必填，此種模式 default 欄位無效 |
| InputArgument::OPTIONAL | 2  |    引數可選，常配合 default 使用    |
| InputArgument::IS_ARRAY | 4  |              陣列型別               |

#### 可選型別

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

#### 陣列型別

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

### 設定選項

選項支援以下模式。

|            模式             | 值 |     備註     |
|:---------------------------:|:--:|:------------:|
|   InputOption::VALUE_NONE   | 1  | 是否傳入可選項 default 欄位無效 |
| InputOption::VALUE_REQUIRED | 2  |   選項必填   |
| InputOption::VALUE_OPTIONAL | 4  |   選項可選   |
| InputOption::VALUE_IS_ARRAY | 8  |   選項陣列   |

#### 是否傳入可選項

```
public function configure()
{
    parent::configure();
    $this->addOption('opt', 'o', InputOption::VALUE_NONE, '是否最佳化');
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

### 選項必填和可選

`VALUE_OPTIONAL` 在單獨使用上與 `VALUE_REQUIRED` 並無二致

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

### 選項陣列

`VALUE_IS_ARRAY` 和 `VALUE_OPTIONAL` 配合使用，可以達到傳入多個 `Option` 的效果。

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

## 透過 `$signature` 配置命令列

命令列除了上述配置方法外，還支援使用 `$signature` 配置。

`$signature` 為字串，分為三部分，分別是 `command` `argument` 和 `option`，如下：

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` 代表 `非必傳`。
- `*` 代表 `陣列`。
- `?*` 代表 `非必傳的陣列`。
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

# 執行命令

!> 注意：在執行命令時，預設會觸發事件分發，可透過新增 `--disable-event-dispatcher` 引數來開啟。

## 命令列中執行

```bash
php bin/hyperf.php foo
```

## 在 Command 中執行其他命令

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

## 在非 Command 中執行命令

```php
$command = 'foo';

$params = ["command" => $command, "--foo" => "foo", "--bar" => "bar"];

// 可以根據自己的需求, 選擇使用的 input/output
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// 這種方式: 不會暴露出命令執行中的異常, 不會阻止程式返回
$exitCode = $application->run($input, $output);

// 第二種方式: 會暴露異常, 需要自己捕捉和處理執行中的異常, 否則會阻止程式的返回
$exitCode = $application->find($command)->run($input, $output);
```
