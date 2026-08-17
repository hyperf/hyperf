# Agendamento de tarefas

Na maioria dos casos, a execução de tarefas agendadas pode ser feita através do comando `crontab` do Linux. No entanto, em alguns casos, configurar o crontab em ambiente de produção pode ser inconveniente e apresenta a limitação de suportar no mínimo o agendamento em nível de `minuto`.

O componente [hyperf/crontab](https://github.com/hyperf/crontab) fornece um agendamento de tarefas em `nível de segundo`, facilitando a definição de tarefas.

# Instalação

```bash
composer require hyperf/crontab
```

# Uso

## Iniciar o processo do agendador

Antes de usar o componente de tarefa agendada, você precisa registrar o `Hyperf\Crontab\Process\CrontabDispatcherProcess` em `config/autoload/processes.php`, como a seguir:

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

Dessa forma, quando o serviço inicia, um processo personalizado é iniciado para a análise e agendamento das tarefas. Ao mesmo tempo, você também precisa definir a configuração `enable` em `config/autoload/crontab.php` como `true`, o que habilita o processamento do agendador. Se o arquivo de configuração não existir, você pode criá-lo você mesmo. A configuração é a seguinte:

```php
<?php
return [
    // Whether to enable timed tasks
    'enable' => true,
];
```

## Definir uma tarefa agendada

### Usando um arquivo de configuração

Você pode definir todas as suas tarefas agendadas no arquivo de configuração `config/autoload/crontab.php`. O arquivo retorna um array de objetos `Hyperf\Crontab\Crontab[]`. Se o arquivo de configuração não existir, você pode criá-lo você mesmo:

```php
<?php
// config/autoload/crontab.php
use Hyperf\Crontab\Crontab;
return [
    'enable' => true,
    // Timed tasks defined by configuration
    'crontab' => [
        // Callback type timed task (default)
        (new Crontab())->setName('Foo')->setRule('* * * * *')->setCallback([App\Task\FooTask::class, 'execute'])->setMemo('This is an example timed task'),
        // Command type timed task
        (new Crontab())->setType('command')->setName('Bar')->setRule('* * * * *')->setCallback([
            'command' => 'swiftmailer:spool:send',
            // (optional) arguments
            'fooArgument' => 'barValue',
            // (optional) options
            '--message-limit' => 1,
            // Remember to add it, otherwise it will cause the main process to exit
            '--disable-event-dispatcher' => true,
        ])->setEnvironments(['develop', 'production']),
        // Closure type timed task (Only supported in Coroutine style server)
        (new Crontab())->setType('closure')->setName('Closure')->setRule('* * * * *')->setCallback(function () {
            var_dump(date('Y-m-d H:i:s'));
        })->setEnvironments('production'),
    ],
];
```

A partir da versão 3.1, um novo método de configuração foi adicionado. Você pode definir tarefas agendadas através de `config/crontabs.php`. Se o arquivo de configuração não existir, você pode criá-lo você mesmo:

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### Usando annotations

A definição de uma tarefa pode ser feita rapidamente através da annotation `#[Crontab]`. Os exemplos de definição a seguir e a definição por arquivo de configuração alcançam o mesmo objetivo. Define uma tarefa agendada chamada `Foo` para executar `App\Task\FooTask::execute()` a cada minuto.

```php
<?php
namespace App\Task;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Di\Annotation\Inject;

#[Crontab(name: "Foo", rule: "* * * * *", callback: "execute", memo: "This is an example scheduled task")]
class FooTask
{
     #[Inject]
    private StdoutLoggerInterface $logger;

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));
    }

    #[Crontab(rule: "* * * * * *", memo: "foo")]
    public function foo()
    {
        var_dump('foo');
    }
}
```

### Configuração da tarefa

#### name

O nome da tarefa agendada pode ser qualquer string, e o nome de cada tarefa agendada deve ser único.

#### rule

As regras de execução de tarefas agendadas são definidas em nível de minuto, consistentes com as regras do comando `crontab` do Linux. Quando definidas em nível de segundo, o tamanho da regra é alterado de 5 dígitos para 6 dígitos, e um nó de nível de segundo é adicionado antes da regra. Isso significa que ela é executada com a regra de nível de minuto com 5 dígitos e a regra de nível de segundo com 6 dígitos. Por exemplo, `*/5 * * * * *` significa que será executado a cada 5 segundos. Note que barras invertidas na definição da regra de annotation devem ser escapadas usando o símbolo de barra invertida `\`: `*\/5 * * * * *`.

#### callback

O callback executado pela tarefa agendada. Quando definido pelo arquivo de configuração, um array `[$class, $method]` é usado, onde `$class` é o nome completo de uma classe e `$method` é um método `public` dessa classe. Ao usar annotations, você só precisa fornecer o nome do método de um método `public` na classe atual. Se a classe atual tiver apenas um método `public`, você nem precisa fornecer esse atributo.

#### singleton

Para resolver o problema de execução concorrente de tarefas, as tarefas sempre serão executadas ao mesmo tempo. Mas esse problema não pode garantir a execução repetida de tarefas no cluster.

#### onOneServer

Ao implantar um projeto com múltiplas instâncias, apenas uma instância executará uma determinada tarefa.

#### mutexPool

O connection pool `Redis` usado pelo mutex.

#### mutexExpires

O tempo limite do mutex lock. Se a tarefa agendada for executada, mas o mutex lock falhar ao ser liberado, ele será liberado automaticamente após esse tempo.

#### memo

Notas para a tarefa agendada. Este atributo é opcional e não tem significado sintático. Seu propósito é ajudar os desenvolvedores a entender a tarefa agendada.

#### enable

Se a tarefa atual está ativa.

#### environments

As variáveis de ambiente que precisam ser definidas ao executar a tarefa. O valor deste atributo é um array, onde a chave é o nome da variável de ambiente, e o valor é o valor da variável de ambiente.

### Estratégia de agendamento e distribuição

As tarefas agendadas são projetadas para permitir que diferentes estratégias sejam usadas para agendar e distribuir a execução das tarefas.

> Ao usar serviços do tipo Coroutine, use a estratégia de execução por Coroutine.

#### Personalizar a estratégia de agendamento e distribuição

Você pode alterar a estratégia atualmente usada alterando a instância correspondente à interface `Hyperf\Crontab\Strategy\StrategyInterface` em `config/autoload/dependencies.php`. Por padrão, a `estratégia de execução por processo Worker` é usada, e a classe correspondente é `Hyperf\Crontab\Strategy\WorkerStrategy`. Por exemplo, se quiséssemos usar `App\Crontab\Strategy\FooStrategy`:

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Estratégia de execução por processo Worker [padrão]

Classe: `Hyperf\Crontab\Strategy\WorkerStrategy`

Por padrão, essa estratégia é usada. O processo `CrontabDispatcherProcess` analisa as tarefas agendadas e passa as tarefas de execução para cada processo `worker` através de polling de comunicação entre processos. Cada processo `worker` então usa uma Coroutine para executar a tarefa de fato.

##### Estratégia de execução por TaskWorker

Classe: `Hyperf\Crontab\Strategy\TaskWorkerStrategy`

Esta estratégia analisa as tarefas agendadas para o processo `CrontabDispatcherProcess` e passa as tarefas de execução para cada processo `TaskWorker` através de polling de comunicação entre processos. Cada processo `TaskWorker` então usa a Coroutine para executar a tarefa de fato. Ao usar essa estratégia, preste atenção se o processo `TaskWorker` está configurado com um protocolo suportado.

##### Estratégia de execução por múltiplos processos

Classe: `Hyperf\Crontab\Strategy\ProcessStrategy`

Esta estratégia analisa as tarefas agendadas para o processo `CrontabDispatcherProcess` e transfere as tarefas de execução para cada processo `Worker` e `TaskWorker` através de polling de comunicação entre processos. Cada processo então usa uma Coroutine para executar as tarefas de fato. Ao usar essa estratégia, preste atenção se o processo `TaskWorker` está configurado para suportar Coroutines.

##### Estratégia de execução por Coroutine

Classe: `Hyperf\Crontab\Strategy\CoroutineStrategy`

Esta estratégia analisa as tarefas agendadas para o processo `CrontabDispatcherProcess` e cria uma Coroutine para executar cada tarefa de execução no processo.

## Executando tarefas agendadas

Depois de concluir a configuração acima e definir as tarefas agendadas, você só precisa iniciar diretamente o `Server`, e as tarefas agendadas serão iniciadas junto. Após iniciar, mesmo que você defina uma tarefa agendada com um período curto o suficiente, a tarefa agendada não será iniciada imediatamente. Todas as tarefas agendadas só serão iniciadas no próximo período de minuto. Por exemplo, quando você inicia às `10:11 12 segundos`, a tarefa agendada começará oficialmente a ser executada às `10:12:00`.
