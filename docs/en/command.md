# Command

Hyperf's command-line is provided by the [hyperf/command](https://github.com/hyperf/command) component by default, and this component itself is based on the abstraction of [symfony/console](https://github.com/symfony/console).

# Installation

Generally, this component exists by default, but if you want to use it for non-Hyperf projects, you can also depend on the [hyperf/command](https://github.com/hyperf/command) component with the following command:

```bash
composer require hyperf/command
```

# View Command List

Run `php bin/hyperf.php` directly without any arguments to output the command list.

# Custom Command

## Generate Command

If you have installed the [hyperf/devtool](https://github.com/hyperf/devtool) component, you can use the `gen:command` command to generate a custom command:

```bash
php bin/hyperf.php gen:command FooCommand
```
After executing the above command, a configured `FooCommand` class will be generated in the `app/Command` folder.

### Define Command

There are three forms for defining the command corresponding to this command class. The first is defined via the `$name` property, the second is defined by passing parameters to the constructor, and the last is defined by annotations. We will demonstrate with a code example. Suppose we want to define the command of this command class as `foo:hello`:

#### Definition via `$name` Property:

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
     * Executed command
     */
    protected ?string $name = 'foo:hello';
}
```

#### Definition via Constructor Argument:

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

#### Definition via Annotation:

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

### Define Command Class Logic

The actual logic for running the command class depends on the code within the `handle` method, which means the `handle` method is the entry point for the command.

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
     * Executed command
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // Output 'Hello Hyperf.' in Console using the built-in line method.
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### Define Command Class Parameters

When writing commands, user input is usually collected through `arguments` and `options`. Before collecting user input, you must define the `argument` or `option`.

#### Argument

Suppose we want to define a `name` argument, and then pass any string like `Hyperf` to the command and execute `php bin/hyperf.php foo:hello Hyperf` to output `Hello Hyperf`. Let's demonstrate this with code:

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
     * Executed command
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // Get the name argument from $input
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'This is an explanation of this argument']
        ];
    }
}
```

Execute `php bin/hyperf.php foo:hello Hyperf`, and we can see the output `Hello Hyperf`.

## Introduction to Common Command Configuration

The following code only modifies the content in `configure` and `handle`.

### Set Help

```php
public function configure()
{
    parent::configure();
    $this->setHelp('Hyperf custom command demonstration');
}

```
```bash
$ php bin/hyperf.php demo:command --help
# Output
...
Help:
  Hyperf custom command demonstration
```


### Set Description

```php
public function configure()
{
    parent::configure();
    $this->setDescription('Hyperf Demo Command');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# Output
...
Description:
  Hyperf Demo Command

```

### Set Usage

```php
public function configure()
{
    parent::configure();
    $this->addUsage('--name demonstration code');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# Output
...
Usage:
  demo:command
  demo:command --name demonstration code
```

### Set Arguments

Arguments support the following modes.

| Mode | Value | Note |
|:---:|:---:|:---:|
| InputArgument::REQUIRED | 1 | Argument is mandatory, the default field is invalid in this mode |
| InputArgument::OPTIONAL | 2 | Argument is optional, often used in conjunction with default |
| InputArgument::IS_ARRAY | 4 | Array type |

#### Optional Type

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::OPTIONAL, 'Name', 'Hyperf');
}

public function handle()
{
    $this->line($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
...
Hyperf

$ php bin/hyperf.php demo:command Swoole
# Output
...
Swoole
```

#### Array Type

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::IS_ARRAY, 'Name');
}

public function handle()
{
    var_dump($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command Hyperf Swoole
# Output
...
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

### Set Options

Options support the following modes.

| Mode | Value | Note |
|:---:|:---:|:---:|
| InputOption::VALUE_NONE | 1 | Whether an optional value is passed, the default field is invalid |
| InputOption::VALUE_REQUIRED | 2 | Option is mandatory |
| InputOption::VALUE_OPTIONAL | 4 | Option is optional |
| InputOption::VALUE_IS_ARRAY | 8 | Option array |

#### Whether an Optional Value is Passed

```php
public function configure()
{
    parent::configure();
    $this->addOption('opt', 'o', InputOption::VALUE_NONE, 'Whether to optimize');
}

public function handle()
{
    var_dump($this->input->getOption('opt'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
bool(false)

$ php bin/hyperf.php demo:command -o
# Output
bool(true)

$ php bin/hyperf.php demo:command --opt
# Output
bool(true)
```

### Mandatory and Optional Options

`VALUE_OPTIONAL` is identical to `VALUE_REQUIRED` when used alone.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_REQUIRED, 'Name', 'Hyperf');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
string(6) "Hyperf"

$ php bin/hyperf.php demo:command --name Swoole
# Output
string(6) "Swoole"
```

### Option Array

`VALUE_IS_ARRAY` and `VALUE_OPTIONAL` used in combination can achieve the effect of passing in multiple `Options`.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Name');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# Output
array(0) {
}

$ php bin/hyperf.php demo:command --name Hyperf --name Swoole
# Output
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

## Configure Command Line via `$signature`

In addition to the configuration methods mentioned above, the command line also supports configuration using `$signature`.

`$signature` is a string consisting of three parts: `command`, `argument`, and `option`, as follows:

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` represents `non-mandatory`.
- `*` represents `array`.
- `?*` represents `non-mandatory array`.
- `=` represents `non-Bool`.

### Example

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

    protected ?string $signature = 'test:test {id : user_id} {--name= : user_name}';

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

# Run Command

!> Note: When running a command, event distribution is triggered by default. You can close it by adding the `--disable-event-dispatcher` argument.

## Run in Command Line

```bash
php bin/hyperf.php foo
```

## Run Other Commands in Command

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

## Run Commands in Non-Command

```php
$command = 'foo';

$params = ["command" => $command, "--foo" => "foo", "--bar" => "bar"];

// You can choose the input/output to use according to your needs
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// This way: will not expose exceptions during command execution, will not prevent the program from returning
$exitCode = $application->run($input, $output);

// The second way: will expose exceptions, you need to catch and handle the exceptions running yourself, otherwise it will prevent the program from returning
$exitCode = $application->find($command)->run($input, $output);
```

## Closure Command

You can quickly define commands in `config\console.php`.

```php
use Hyperf\Command\Console;

Console::command('hello', function () {
    $this->comment('Hello, Hyperf!');
})->describe('This is a demo closure command.');
```

Define scheduled tasks for closure commands.

```php
use Hyperf\Command\Console;

Console::command('foo', function () {
    $this->comment('Hello, Foo!');
})->describe('This is a demo closure command.')->cron('* * * * *');

Console::command('bar', function () {
    $this->comment('Hello, Bar!');
})->describe('This is another demo closure command.')->cron('* * * * *', callback: fn($cron) => $cron->setSingleton(true));
```

## AsCommand

You can convert a class into a command through the `AsCommand` annotation.

```php
<?php

namespace App\Service;

use Hyperf\Command\Annotation\AsCommand;
use Hyperf\Command\Concerns\InteractsWithIO;

#[AsCommand(signature: 'foo:bar1', handle: 'bar1', description: 'The description of foo:bar1 command.')]
#[AsCommand(signature: 'foo', description: 'The description of foo command.')]
class FooService
{
    use InteractsWithIO;

    #[AsCommand(signature: 'foo:bar {--bar=1 : Bar Value}', description: 'The description of foo:bar command.')]
    public function bar($bar)
    {
        $this->output?->info('Bar Value: ' . $bar);

        return $bar;
    }

    public function bar1()
    {
        $this->output?->info(__METHOD__);
    }

    public function handle()
    {
        $this->output?->info(__METHOD__);
    }
}
```

```shell
$ php bin/hyperf.php

...
foo
  foo:bar                   The description of foo:bar command.
  foo:bar1                  The description of foo:bar1 command.
```
