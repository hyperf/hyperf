# Agendamento de tarefas

Na maioria dos casos, a execução de tarefas agendadas pode ser feita por meio do comando `crontab` do Linux. Porém, em alguns cenários, configurar o crontab em ambiente de produção pode ser inconveniente e também vem com a limitação de suportar, no mínimo, agendamento em nível de `minuto`.

O componente [hyperf/crontab](https://github.com/hyperf/crontab) oferece agendamento de tarefas em `nível de segundo` e facilita a definição de tarefas.

# Instalação

```bash
composer require hyperf/crontab
```

# Uso

## Iniciar o processo do scheduler

Antes de usar o componente de tarefas agendadas, você precisa registrar `Hyperf\Crontab\Process\CrontabDispatcherProcess` em `config/autoload/processes.php`, como a seguir:

```php
<?php
// config/autoload/processes.php
return [
    Hyperf\Crontab\Process\CrontabDispatcherProcess::class,
];
```

Dessa forma, quando o serviço inicia, um processo personalizado é iniciado para análise e agendamento das tarefas. Ao mesmo tempo, você também precisa definir `enable` em `config/autoload/crontab.php` como `true`, o que habilita o processamento do scheduler. Se o arquivo de configuração não existir, você pode criá-lo. A configuração é a seguinte:

```php
<?php
return [
    // Se deve habilitar timed tasks
    'enable' => true,
];
```

## Definir uma tarefa agendada

### Usando um arquivo de configuração

Você pode definir todas as suas tarefas agendadas no arquivo de configuração `config/autoload/crontab.php`. O arquivo retorna um array de objetos `Hyperf\Crontab\Crontab[]`. Se o arquivo de configuração não existir, você pode criá-lo:

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

Desde a versão 3.1, um novo método de configuração foi adicionado. Você pode definir tarefas agendadas via `config/crontabs.php`. Se o arquivo de configuração não existir, você pode criá-lo:

```php
<?php
// config/crontabs.php
use Hyperf\Crontab\Schedule;

Schedule::command('foo:bar')->setName('foo-bar')->setRule('* * * * *');
Schedule::call([Foo::class, 'bar'])->setName('foo-bar')->setRule('* * * * *');
Schedule::call(fn() => (new Foo)->bar())->setName('foo-bar')->setRule('* * * * *');
```

### Usando anotações

A definição de uma tarefa pode ser concluída rapidamente por meio da anotação `#[Crontab]`. Os exemplos a seguir e a definição via arquivo de configuração atingem o mesmo objetivo. Defina uma tarefa agendada chamada `Foo` para executar `App\Task\FooTask::execute()` a cada minuto.

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

As regras de execução das tarefas agendadas são definidas no nível de minuto, consistentes com as regras do comando `crontab` do Linux. Quando definido no nível de segundo, o tamanho da expressão muda de 5 para 6 campos, adicionando um campo de segundos no início. Isso significa que a expressão com 5 campos executa no nível de minuto, e com 6 campos executa no nível de segundo. Por exemplo, `*/5 * * * * *` significa que será executada a cada 5 segundos. Observe que barras `/` na regra definida por anotação precisam ser escapadas com `\`: `*\/5 * * * * *`.

#### callback

O callback executado pela tarefa agendada. Quando definido pelo arquivo de configuração, usa-se um array `[$class, $method]`, onde `$class` é o nome completo da classe e `$method` é um método `public` dessa classe. Ao usar anotações, você só precisa informar o nome de um método `public` na classe atual. Se a classe atual tiver apenas um método `public`, você nem precisa fornecer esse atributo.

#### singleton

Para resolver o problema de execução concorrente, as tarefas sempre rodarão ao mesmo tempo. Porém, isso não garante a execução repetida das tarefas no cluster.

#### onOneServer

Ao implantar um projeto com múltiplas instâncias, apenas uma instância executará uma determinada tarefa.

#### mutexPool

O pool de conexões `Redis` usado pelo mutex.

#### mutexExpires

O tempo limite do lock do mutex. Se a tarefa agendada for executada mas o lock do mutex falhar em ser liberado, o lock será liberado automaticamente após esse tempo.

#### memo

Observações da tarefa agendada. Esse atributo é opcional e não tem significado sintático. Seu propósito é ajudar desenvolvedores a entenderem a tarefa agendada.

#### enable

Se a tarefa atual é efetiva.

#### environments

As variáveis de ambiente que precisam ser definidas ao executar a tarefa. O valor desse atributo é um array em que a chave é o nome da variável de ambiente e o valor é o valor da variável de ambiente.

### Estratégia de distribuição do agendamento

As tarefas agendadas são projetadas para permitir diferentes estratégias de agendamento e distribuição da execução.

> Ao usar serviços no estilo de corrotinas, use a estratégia de execução por corrotina.

#### Personalizar a estratégia de distribuição do agendamento

Você pode alterar a estratégia usada atualmente trocando a instância correspondente à interface `Hyperf\Crontab\Strategy\StrategyInterface` em `config/autoload/dependencies.php`. Por padrão, a `estratégia de execução no processo Worker` é usada, e a classe correspondente é `Hyperf\Crontab\Strategy\WorkerStrategy`. Por exemplo, se quisermos usar `App\Crontab\Strategy\FooStrategy`:

```php
<?php
return [
    \Hyperf\Crontab\Strategy\StrategyInterface::class => \App\Crontab\Strategy\FooStrategy::class,
];
```

##### Estratégia de execução no processo Worker [padrão]

Class: `Hyperf\Crontab\Strategy\WorkerStrategy`

Por padrão, esta estratégia é usada. O processo `CrontabDispatcherProcess` faz o parse das tarefas agendadas e repassa as tarefas de execução para cada processo `worker` por polling de comunicação entre processos. Em seguida, cada processo `worker` usa uma corrotina para executar a tarefa de fato.

##### Estratégia de execução no TaskWorker

Class: `Hyperf\Crontab\Strategy\TaskWorkerStrategy`

Esta estratégia faz o parse das tarefas agendadas no processo `CrontabDispatcherProcess` e repassa as tarefas de execução para cada processo `TaskWorker` por polling de comunicação entre processos. Em seguida, cada processo `TaskWorker` usa uma corrotina para executar a tarefa de fato. Ao usar esta estratégia, preste atenção se o processo `TaskWorker` está configurado com um protocolo suportado.

##### Estratégia de execução multi-processo

Class: `Hyperf\Crontab\Strategy\ProcessStrategy`

Esta estratégia faz o parse das tarefas agendadas no processo `CrontabDispatcherProcess` e transfere as tarefas de execução para cada processo `Worker` e `TaskWorker` por polling de comunicação entre processos. Em seguida, cada processo usa uma corrotina para executar as tarefas de fato. Ao usar esta estratégia, preste atenção se o processo `TaskWorker` está configurado para suportar corrotinas.

##### Estratégia de execução por corrotina

Class: `Hyperf\Crontab\Strategy\CoroutineStrategy`

Esta estratégia faz o parse das tarefas agendadas no processo `CrontabDispatcherProcess` e cria uma corrotina para rodar cada tarefa de execução no processo.

## Executando tarefas agendadas

Depois de concluir a configuração acima e definir as tarefas agendadas, basta iniciar o `Server`, e as tarefas agendadas iniciarão junto. Após iniciar, mesmo que você defina uma tarefa agendada com um período suficientemente curto, ela não começará imediatamente. Todas as tarefas agendadas só começarão na próxima virada de minuto. Por exemplo, se ao iniciar for `10:11 12 seconds`, então a tarefa agendada começará oficialmente a executar às `10:12:00`.
