# Command

O componente de comando padrão do Hyperf é fornecido pelo componente [hyperf/command](https://github.com/hyperf/command), e esse componente é uma abstração do [symfony/console](https://github.com/symfony/console).

# Instalação

Este componente geralmente já existe por padrão, mas se você quiser usá-lo em projetos que não sejam Hyperf, também pode depender do componente [hyperf/command](https://github.com/hyperf/command) com o seguinte comando:

```bash
composer require hyperf/command
```

# Lista de comandos

Executar `php bin/hyperf.php` diretamente, sem nenhum argumento, exibe a lista de comandos.

# Comando personalizado

## Gerar um Command

Se você tiver o componente [hyperf/devtool](https://github.com/hyperf/devtool) instalado, pode gerar um comando personalizado com o comando `gen:command`:

```bash
php bin/hyperf.php gen:command FooCommand
```
Depois de executar o comando acima, uma classe `FooCommand` já configurada será gerada na pasta `app/Command`.

### Definição do Command

Existem três formas de definir a classe de comando. A primeira é definida pela propriedade `$name`, a segunda é definida pelo argumento do construtor, e a última é definida por annotations. Vamos demonstrar isso através de exemplos de código, supondo que queremos definir o comando. O comando da classe é `foo:hello`:

#### Defina o comando pela propriedade `$name`:

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

#### Defina o comando pelo construtor:

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

#### Defina o comando por annotations:

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

### Defina a lógica do comando

A lógica que a classe de comando realmente executa depende do método `handle` dentro do código, o que significa que o método `handle` é o ponto de entrada do comando.

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

### Defina os argumentos do comando

Ao escrever um comando, a entrada do usuário geralmente é coletada por meio de `parameter` e `option`, e o `parameter` ou `option` deve ser definido antes de coletar uma entrada do usuário.

#### Parameter

Suponha que queremos definir um parâmetro `name`, e então passar uma string arbitrária, como `Hyperf`, para o comando e executar `php bin/hyperf.php foo:hello Hyperf` para exibir `Hello Hyperf`. Vamos demonstrar isso através de código:

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

Execute o comando `php bin/hyperf.php foo:hello Hyperf` e podemos ver `Hello Hyperf` exibido no Console.

## Configurações comuns

O código a seguir modifica apenas o conteúdo em `configure` e `handle`.

### Definir Help

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

### Definir a Description

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

### Definir o Usage

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

### Definir parâmetros

Os parâmetros suportam os seguintes modos.

|          Modo           | Valor |                Observação                 |
|:-----------------------:|:--:|:-----------------------------------:|
| InputArgument::REQUIRED | 1  | O parâmetro é obrigatório; o campo "default" não é válido neste modo. |
| InputArgument::OPTIONAL | 2  |    O parâmetro é opcional e é frequentemente usado com um valor padrão    |
| InputArgument::IS_ARRAY | 4  |              Tipo array               |

#### Tipo opcional

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

#### Tipo array

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

### Definir opções

As opções suportam os seguintes modos.

|            Modo             | Valor |     Observação     |
|:---------------------------:|:--:|:------------:|
|   InputOption::VALUE_NONE   | 1  | O parâmetro é obrigatório; o campo "default" não é válido neste modo |
| InputOption::VALUE_REQUIRED | 2  |   A opção é obrigatória   |
| InputOption::VALUE_OPTIONAL | 4  |   A opção é opcional   |
| InputOption::VALUE_IS_ARRAY | 8  |   A opção é um array   |

#### Se deve passar opções

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

### Opções obrigatórias e opcionais

`VALUE_OPTIONAL` não é diferente de `VALUE_REQUIRED` quando usado isoladamente.

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

### Array de opções

`VALUE_IS_ARRAY` e `VALUE_OPTIONAL`, quando usados juntos, permitem obter o efeito de passar múltiplas `Option`s.

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

## Configurar o comando através de `$signature`

Além dos métodos de configuração acima, a linha de comando também suporta o uso da configuração via `$signature`.

`$signature` é uma string, dividida em três partes: `command`, `argument` e `option`, como a seguir:

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` representa `opcional`.
- `*` representa `array`.
- `?*` representa `array opcional`.
- `=` representa `não-Bool`.

### Exemplo

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

# Executar comando

!> Nota: por padrão, executar o comando dispara o event dispatching. Você pode desabilitar isso adicionando o parâmetro `--disable-event-dispatcher`.

## Executar na linha de comando

```bash
php bin/hyperf.php foo
```

## Executar outros comandos dentro de um Command

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

## Executar comandos fora de um Command

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

## Comando closure

Você pode definir comandos rapidamente em `config\console.php`.

```php
use Hyperf\Command\Console;

Console::command('hello', function () {
    $this->comment('Hello, Hyperf!');
})->describe('This is a demo closure command.');
```

Você também pode configurar crontab para um comando closure.

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

Você pode transformar uma classe em um comando anotando-a com `AsCommand`.

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
