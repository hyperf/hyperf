# Processo personalizado

O [hyperf/process](https://github.com/hyperf/process) permite que você adicione processos personalizados pelo usuário. Esse recurso geralmente é usado para criar um processo especial para monitoramento, relatórios ou outras tarefas especiais. Quando o servidor inicia, ele cria automaticamente um processo e executa o subprocesso especificado. Se o processo sair inesperadamente, o servidor reiniciará automaticamente o processo.

## Criar um processo personalizado

Implemente uma subclasse que herda de `Hyperf\Process\AbstractProcess` e implemente o método de interface `handle(): void`, colocando seu código de lógica nesse método. Vejamos este código como exemplo:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }
}
```

Isso define uma classe de processo personalizada, mas a classe ainda não foi registrada no `ProcessManager`. Podemos registrá-la usando uma das duas formas: `arquivo de configuração` ou `annotation`.

### Registrar via arquivo de configuração

Basta adicionar sua classe de processo personalizada em `config/autoload/processes.php`:

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### Registrar via annotation

Basta definir a annotation `#[Process]` na classe de processo personalizada, e o Hyperf coletará e completará automaticamente o trabalho de registro:

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process")]
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }
}
```

> Ao usar a annotation `#[Process]`, o namespace `use Hyperf\Process\Annotation\Process;` é obrigatório;

## Adicionar condições para a inicialização do processo

Às vezes, um processo personalizado não deve ser iniciado em todos os momentos. Se um processo personalizado é iniciado ou não pode ser determinado de acordo com certas configurações ou condições, sobrescrevendo o método `isEnable(): bool` na classe de processo personalizada. O método é implementado por padrão com o valor de retorno `true`, o que fará com que ele seja iniciado junto com o serviço. Se o método retornar `false`, o processo personalizado não será iniciado quando o serviço for iniciado.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process")]
class FooProcess extends AbstractProcess
{
    public function handle(): void
    {
        // Your code...
    }

    public function isEnable($server): bool
    {
        // Do not start with service startup
        return false;
    }
}
```

## Configurar um processo personalizado

Existem alguns parâmetros configuráveis no processo personalizado, que podem ser definidos sobrescrevendo os atributos correspondentes aos parâmetros na subclasse ou definindo os atributos correspondentes na annotation `#[Process]`.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: "foo_process", name: "user-process", redirectStdinStdout: false, pipeType: 2, enableCoroutine: true)]
class FooProcess extends AbstractProcess
{
    /**
     * Number of processes
     * @var int
     */
    public $nums = 1;

    /**
     * Process name
     * @var string
     */
    public $name = 'user-process';

    /**
     * Redirect the standard input and output of a custom process
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * Pipe type
     * @var int
     */
    public $pipeType = 2;

    /**
     * Whether to enable coroutine
     * @var bool
     */
    public $enableCoroutine = true;
}
```

## Exemplo de uso

Vamos criar um processo filho para monitorar o número de filas com falha e reportar um alerta quando houver dados na fila de falhas.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Contract\StdoutLoggerInterface;

#[Process(name: "demo_process")]
class DemoProcess extends AbstractProcess
{
    public function handle(): void
    {
        $logger = $this->container->get(StdoutLoggerInterface::class);

        while (true) {
            $redis = $this->container->get(\Redis::class);
            $count = $redis->llen('queue:failed');

            if ($count > 0) {
                $logger->warning('The num of failed queue is ' . $count);
            }

            sleep(1);
        }
    }
}
```
