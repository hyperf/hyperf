# Command

The default command component of Hyperf provided by [hyperf/command](https://github.com/hyperf/command) component, And this component is a abstraction of [symfony/console](https://github.com/symfony/console).

# Installation

This component usually exists by default, but if you want to use it for non-Hyperf projects, you can also rely on the [hyperf/command](https://github.com/hyperf/command) component with the following command:

```bash
composer require hyperf/command
```

# Command List

Run `php bin/hyperf.php` without any arguments directly is to display the command list.

# Custom Command

## Generate a Command

If you have the [hyperf/devtool](https://github.com/hyperf/devtool) component installed, you can generate a custom command with the `gen:command` command:

```bash
php bin/hyperf.php gen:command FooCommand
```
After executing the above command, a configured `FooCommand` class will be generated in the `app/Command` folder.

### Definition of Command

There are three forms of commands that define the command class. The first is defined by the `$name` property, the second is defined by the constructor argument, and the last is defined by annotations. We demonstrate this through code examples, assuming we want to define the command. The class command is `foo:hello`:

#### Define the command by `$name` property:

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
     * The command
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';
}
```

#### Define the command by constructor:

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

#### Define the command by annotations:

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

### Define the logic of the command

The logic that the command class actually runs depends on the `handle` method inside the code, which means that the `handle` method is the entry point to the command.

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
     * The command
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';
    
    public function handle()
    {
        // Output Hello Hyperf. in the Console via the built-in method line()
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### Define the arguments of the command

When writing a command, the user's input is usually collected by `parameter` and `option`, and the `parameter` or `option` must be defined before collecting a user input.

#### Parameter

Suppose we want to define a `name` parameter, and then pass the arbitrary string such as `Hyperf` to the command and execute `php bin/hyperf.php foo:hello Hyperf` to output `Hello Hyperf`. Let's demonstrate it by code:

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
     * The command
     *
     * @var string
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
            ['name', InputArgument::OPTIONAL, 'Here is an explanation of this parameter']
        ];
    }
}
``` 

Execute `php bin/hyperf.php foo:hello Hyperf` command and we can see `Hello Hyperf` display on Console.

## Common Configurations

The following code only modifies the content in `configure` and `handle`.

### Set Help

```php
public function configure()
{
    parent::configure();
    $this->setHelp('Hyperf's custom command demonstration');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# output
...
Help:
  Hyperf's custom command demonstration

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
# output
...
Description:
  Hyperf Demo Command

```

### Set Usage

```php
public function configure()
{
    parent::configure();
    $this->addUsage('--name Demo Code');
}
```
```bash
$ php bin/hyperf.php demo:command --help
# output
...
Usage:
  demo:command
  demo:command --name Demo Code
```

### Set parameters

The parameters support the following modes.

|          Mode           | Value |                Note                 |
|:-----------------------:|:--:|:-----------------------------------:|
| InputArgument::REQUIRED | 1  | Parameter is required, the "default" field in this mode is invalid. |
| InputArgument::OPTIONAL | 2  |    Parameter is optional and is often used with default    |
| InputArgument::IS_ARRAY | 4  |              Array type               |

#### Optional type

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::OPTIONAL, 'name', 'Hyperf');
}

public function handle()
{
    $this->line($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
...
Hyperf

$ php bin/hyperf.php demo:command Swoole
...
Swoole
```

#### Array type

```php
public function configure()
{
    parent::configure();
    $this->addArgument('name', InputArgument::IS_ARRAY, 'name');
}

public function handle()
{
    var_dump($this->input->getArgument('name'));
}
```
```bash
$ php bin/hyperf.php demo:command Hyperf Swoole
# output
...
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

### Set options

The options support the following modes.

|            Mode             | Value |     Note     |
|:---------------------------:|:--:|:------------:|
|   InputOption::VALUE_NONE   | 1  | Parameter is required, the "default" field in this mode is invalid |
| InputOption::VALUE_REQUIRED | 2  |   Option is required   |
| InputOption::VALUE_OPTIONAL | 4  |   Option is optional   |
| InputOption::VALUE_IS_ARRAY | 8  |   Option is an array   |

#### Whether to pass in options

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
# output
bool(false)

$ php bin/hyperf.php demo:command -o
# output
bool(true)

$ php bin/hyperf.php demo:command --opt
# output
bool(true)
```

### Options required and optional

`VALUE_OPTIONAL` is no different from `VALUE_REQUIRED` when used alone.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_REQUIRED, 'name', 'Hyperf');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
string(6) "Hyperf"

$ php bin/hyperf.php demo:command --name Swoole
# output
string(6) "Swoole"
```

### Option array

`VALUE_IS_ARRAY` and `VALUE_OPTIONAL`, when used together, can achieve the effect of passing multiple `Option`s.

```php
public function configure()
{
    parent::configure();
    $this->addOption('name', 'N', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'name');
}

public function handle()
{
    var_dump($this->input->getOption('name'));
}
```
```bash
$ php bin/hyperf.php demo:command
# output
array(0) {
}

$ php bin/hyperf.php demo:command --name Hyperf --name Swoole
# output
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}

```

## Configure the command through `$signature`.

In addition to the above configuration methods, the command line also supports using `$signature` configuration.

`$signature` is a string, divided into three parts: `command`, `argument`, and `option`, as follows:

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` represents `optional`.
- `*` represents `array`.
- `?*` represents `optional array`.
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

# Run command

!> Note: By default, running the command will trigger event dispatching. You can disable it by adding the `--disable-event-dispatcher` parameter.

## Run in the command line.

```bash
php bin/hyperf.php foo
```

## Run other commands in Command.

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

## Run commands outside of Command.

```php
$command = 'foo';

$params = ["command" => $command, "--foo" => "foo", "--bar" => "bar"];

// You can choose the input/output according to your own needs.
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// This method: will not expose exceptions during command execution and will not prevent the program from returning.
$exitCode = $application->run($input, $output);

// Another way: it will expose exceptions and require you to catch and handle runtime exceptions yourself, otherwise it will prevent the program from returning.
$exitCode = $application->find($command)->run($input, $output);
```

## Closure command

You can quickly define commands in `config\console.php`.

```php
use Hyperf\Command\Console;

Console::command('hello', function () {
    $this->comment('Hello, Hyperf!');
})->describe('This is a demo closure command.');
```

You can also set crontab for closure command.

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

You can convert a class into a command by annotating it with `AsCommand`.

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