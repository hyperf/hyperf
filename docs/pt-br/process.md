# Processo personalizado

[hyperf/process](https://github.com/hyperf/process) permite adicionar processos definidos pelo usuário. Esse recurso normalmente é usado para criar um processo especial para monitoramento, relatórios ou outras tarefas específicas. Quando o servidor inicia, ele cria automaticamente um processo e executa o subprocesso especificado. Se o processo sair inesperadamente, o servidor reiniciará o processo automaticamente.

## Criar um processo personalizado

Implemente uma subclasse que herde `Hyperf\Process\AbstractProcess` e implemente o método da interface `handle(): void`, colocando o código da sua lógica dentro do método. Vamos usar este código como exemplo:

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

Isso define uma classe de processo personalizada, mas ela ainda não foi registrada no `ProcessManager`. Podemos registrá-la de uma das duas formas: `arquivo de configuração` ou `anotação`.

### Registrar via arquivo de configuração

Basta adicionar sua classe de processo personalizada em `config/autoload/processes.php`:

```php
// config/autoload/processes.php
return [
    \App\Process\FooProcess::class,
];
```

### Registrar via anotação

Basta definir a anotação `#[Process]` na classe de processo personalizada, e o Hyperf coletará e concluirá o trabalho de registro automaticamente:

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

> Ao usar a anotação `#[Process]`, é necessário importar `use Hyperf\Process\Annotation\Process;`;

## Adicionar condições para iniciar o processo

Às vezes, um processo personalizado não deve ser iniciado o tempo todo. Se um processo personalizado deve iniciar ou não pode ser determinado conforme certas configurações ou condições ao sobrescrever o método `isEnable(): bool` na classe do processo personalizado. Por padrão, esse método retorna `true`, o que fará com que ele inicie junto com o serviço. Se o método retornar `false`, o processo personalizado não será iniciado quando o serviço iniciar.

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

## Configurando um processo personalizado

Há alguns parâmetros configuráveis no processo personalizado, que podem ser definidos sobrescrevendo os atributos correspondentes na subclasse ou definindo os atributos correspondentes na anotação `#[Process]`.

```php
<?php
declare(strict_types=1);

namespace App\Process;

use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
```php
#[Process(name: "foo_process", name: "user-process", redirectStdinStdout: false, pipeType: 2, enableCoroutine: true)]
class FooProcess extends AbstractProcess
{
    /**
     * Quantidade de processos
     * @var int
     */
    public $nums = 1;

    /**
     * Nome do processo
     * @var string
     */
    public $name = 'user-process';

    /**
     * Redireciona a entrada e saída padrão de um processo personalizado
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * Tipo de pipe
     * @var int
     */
    public $pipeType = 2;

    /**
     * Se deve habilitar corrotina
     * @var bool
     */
    public $enableCoroutine = true;
}
```

## Exemplo de uso

Criamos um processo filho para monitorar a quantidade de filas de falha e emitir um aviso quando houver dados na fila de falha.

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
       }
    }
}
```
