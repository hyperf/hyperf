# Comando

O componente de comandos padrão do Hyperf é fornecido pelo componente [hyperf/command](https://github.com/hyperf/command). Esse componente é uma abstração do [symfony/console](https://github.com/symfony/console).

# Instalação

Esse componente normalmente já existe por padrão, mas se você quiser usá-lo em projetos que não sejam Hyperf, você também pode depender do componente [hyperf/command](https://github.com/hyperf/command) com o seguinte comando:

```bash
composer require hyperf/command
```

# Lista de comandos

Executar `php bin/hyperf.php` diretamente sem argumentos é para exibir a lista de comandos.

# Comando personalizado

## Gerar um comando

Se você tiver o componente [hyperf/devtool](https://github.com/hyperf/devtool) instalado, você pode gerar um comando personalizado com o comando `gen:command`:

```bash
php bin/hyperf.php gen:command FooCommand
```

Depois de executar o comando acima, uma classe `FooCommand` configurada será gerada na pasta `app/Command`.

### Definição do comando

Há três formas de definir a classe do comando. A primeira é definida pela propriedade `$name`, a segunda é definida por argumento no construtor, e a última é definida por anotações. Vamos demonstrar isso com exemplos de código, assumindo que queremos definir um comando. O comando da classe é `foo:hello`:

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
     * O comando
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

#### Defina o comando por anotações:

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

A lógica que a classe de comando de fato executa depende do método `handle` dentro do código, o que significa que o método `handle` é o ponto de entrada do comando.

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
     * O comando
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';
    
    public function handle()
    {
        // Exibe Hello Hyperf. no console via o método embutido line()
        $this->line('Hello Hyperf.', 'info');
    }
}
```

### Defina os argumentos do comando

Ao escrever um comando, a entrada do usuário normalmente é coletada por `parameter` e `option`, e o `parameter` ou `option` precisa ser definido antes de coletar a entrada do usuário.

#### Parameter

Suponha que queremos definir um parâmetro `name` e então passar uma string arbitrária como `Hyperf` para o comando e executar `php bin/hyperf.php foo:hello Hyperf` para exibir `Hello Hyperf`. Vamos demonstrar com código:

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
     * O comando
     *
     * @var string
     */
    protected ?string $name = 'foo:hello';

    public function handle()
    {
        // Obtém o argumento name a partir de $input
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }
    
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'Aqui está uma explicação deste parâmetro']
        ];
    }
}
``` 

Execute o comando `php bin/hyperf.php foo:hello Hyperf` e você verá `Hello Hyperf` no console.

## Configurações comuns

O código a seguir só modifica o conteúdo em `configure` e `handle`.

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
# saída
...
Help:
  Hyperf's custom command demonstration

```

### Definir Description

```php
public function configure()
{
    parent::configure();
    $this->setDescription('Hyperf Demo Command');
}
```

```bash
$ php bin/hyperf.php demo:command --help
# saída
...
Description:
  Hyperf Demo Command

```

### Definir Usage

```php
public function configure()
{
    parent::configure();
    $this->addUsage('--name Demo Code');
}
```

```bash
$ php bin/hyperf.php demo:command --help
# saída
...
Usage:
  demo:command
  demo:command --name Demo Code
```

### Definir parâmetros

Os parâmetros suportam os seguintes modos:

|          Mode           | Value |                Note                 |
|:-----------------------:|:--:|:-----------------------------------:|
| InputArgument::REQUIRED | 1  | O parâmetro é obrigatório; o campo "default" neste modo é inválido. |
| InputArgument::OPTIONAL | 2  | O parâmetro é opcional e costuma ser usado com default |
| InputArgument::IS_ARRAY | 4  | Tipo array |

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
# saída
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
# saída
...
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}
```

### Definir options

As options suportam os seguintes modos:

|            Mode             | Value |     Note     |
|:---------------------------:|:--:|:------------:|
|   InputOption::VALUE_NONE   | 1  | O parâmetro é obrigatório; o campo "default" neste modo é inválido |
| InputOption::VALUE_REQUIRED | 2  | A option é obrigatória |
| InputOption::VALUE_OPTIONAL | 4  | A option é opcional |
| InputOption::VALUE_IS_ARRAY | 8  | A option é um array |

#### Se deve passar options

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
# saída
bool(false)

$ php bin/hyperf.php demo:command -o
# saída
bool(true)

$ php bin/hyperf.php demo:command --opt
# saída
bool(true)
```

### Options obrigatórias e opcionais

`VALUE_OPTIONAL` não é diferente de `VALUE_REQUIRED` quando usado sozinho.

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
# saída
string(6) "Hyperf"

$ php bin/hyperf.php demo:command --name Swoole
# saída
string(6) "Swoole"
```

### Array de options

`VALUE_IS_ARRAY` e `VALUE_OPTIONAL`, quando usados juntos, podem alcançar o efeito de passar múltiplas `Option`s.

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
# saída
array(0) {
}

$ php bin/hyperf.php demo:command --name Hyperf --name Swoole
# saída
array(2) {
  [0]=>
  string(6) "Hyperf"
  [1]=>
  string(6) "Swoole"
}

```

## Configurar o comando via `$signature`

Além dos métodos de configuração acima, a linha de comando também suporta configurar com `$signature`.

`$signature` é uma string dividida em três partes: `command`, `argument` e `option`, como a seguir:

```
command:name {argument?* : The argument description.} {--option=* : The option description.}
```

- `?` representa `optional`.
- `*` representa `array`.
- `?*` representa `optional array`.
- `=` representa `non-Bool`.

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

!> Note: por padrão, executar o comando dispara o dispatch de eventos. Você pode desativar isso adicionando o parâmetro `--disable-event-dispatcher`.

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

// Você pode escolher a entrada/saída conforme a sua necessidade.
$input = new ArrayInput($params);
$output = new NullOutput();

/** @var \Psr\Container\ContainerInterface $container */
$container = \Hyperf\Context\ApplicationContext::getContainer();

/** @var \Symfony\Component\Console\Application $application */
$application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
$application->setAutoExit(false);

// Este método: não expõe exceções durante a execução do comando e não impede o programa de retornar.
$exitCode = $application->run($input, $output);

// Outra forma: expõe exceções e exige que você capture e trate exceções em runtime por conta própria; caso contrário, impedirá o programa de retornar.
$exitCode = $application->find($command)->run($input, $output);
```

## Closure command

Você pode definir comandos rapidamente em `config\console.php`.

```php
use Hyperf\Command\Console;

Console::command('hello', function () {
    $this->comment('Hello, Hyperf!');
})->describe('This is a demo closure command.');
```

Você também pode definir crontab para closure command.

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

Você pode converter uma classe em um comando anotando-a com `AsCommand`.

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
