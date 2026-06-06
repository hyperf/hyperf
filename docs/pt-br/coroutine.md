# Corrotina

## Conceito

O Hyperf é construído sobre as corrotinas do `Swoole 5`, o que é um dos principais fatores para o Hyperf oferecer alta performance.

### Modo de execução do PHP-FPM

Antes de falarmos sobre o que está acontecendo, vamos falar sobre o modo de operação da arquitetura tradicional `PHP-FPM`. O `PHP-FPM` é um gerenciador `FastCGI` multiprocesso, usado pela maioria das aplicações PHP. Suponha que usemos o `Nginx` para fornecer o serviço `HTTP` (é o mesmo ao usar `Apache`). Todas as requisições iniciadas pelo cliente chegam primeiro ao `Nginx`; então o `Nginx` encaminha a requisição para processamento pelo `PHP-FPM` via protocolo `FastCGI`. O `Master Process` do `PHP-FPM` aloca um `Worker Process` para cada requisição. Esse processamento significa que o processo inteiro fica bloqueado: esperando pelo parse do script `PHP` e esperando pelo resultado do negócio; em seguida, o processo filho é reciclado. Ou seja, quantos processos `PHP-FPM` você tiver, essa é a quantidade de requisições que você consegue lidar ao mesmo tempo. Supondo que o `PHP-FPM` tenha `200` `Worker Process`, uma requisição leve `1` segundo, então o servidor inteiro teoricamente consegue lidar com até 200, o `QPS` é `200/s`. Em cenários de alta concorrência, esse desempenho muitas vezes não é suficiente. Embora você possa usar o `Nginx` como load balancer com múltiplos servidores `PHP-FPM`, devido ao modelo de espera bloqueante do `PHP-FPM`, uma requisição ocupará pelo menos uma conexão `MySQL`, e então o ambiente com múltiplos nós gerará claramente muitas conexões `MySQL`. O número máximo padrão de conexões de `MySQL` é `100`; embora você possa modificá-lo, fica evidente que esse padrão não consegue lidar adequadamente com cenários de alta concorrência.

### Sistema assíncrono não-bloqueante

Em um cenário de alta concorrência, o modelo assíncrono não-bloqueante tem vantagens óbvias. A vantagem mais intuitiva é que o `Worker Process` deixa de bloquear de forma síncrona ao lidar com uma requisição e passa a conseguir lidar com múltiplas requisições ao mesmo tempo, sem esperar por `I/O`. A capacidade de concorrência é extremamente forte, e um grande número de requisições pode ser iniciado ou mantido ao mesmo tempo. Porém, a desvantagem mais intuitiva que você talvez conheça é o “callback hell”: a lógica de negócio precisa ser implementada na função de callback correspondente. Se a lógica de negócio tiver várias requisições `I/O`, haverá muitas camadas de callbacks. A seguir, um exemplo de pseudo-código no `Swoole 1.x`.

```php
$db = new swoole_mysql();
$config = array(
    'host' => '127.0.0.1',
    'port' => 3306,
    'user' => 'test',
    'password' => 'test',
    'database' => 'test',
);

$db->connect($config, function ($db, $r) {
    // Consulta uma linha de dados da tabela users
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r === true) {
            $rows = $db->affected_rows;
            // Modifica uma linha de dados após a consulta ter sucesso
            $updateSql = 'update users set name='new name' where id = 1';
            $db->query($updateSql, function (swoole_mysql $db, $r) {
                if ($r === true) {
                    return $this->response->end('Update Successfully');
                }
            });
        }
        $db->close();
    });
});
```
Como você pode ver nos trechos de código acima, quase toda operação exige uma função de callback, e o empilhamento e a estrutura do código em um cenário de negócio complexo com callbacks certamente vai te derrubar. Não é difícil perceber que essa abordagem é semelhante a escrever métodos assíncronos em `JavaScript`, e o `JavaScript` oferece várias soluções (derivadas de outras linguagens, claro), como `Promise`, `yield + generator` e `Async/Await`. Enquanto `Promise` é uma forma de encapsular callbacks, `yield + generator` e `Async/Await` exigem adicionar explicitamente algumas marcações de sintaxe ao código, que são boas alternativas para callbacks — mas você ainda precisa de tempo para entender sua implementação e sintaxe.     
A corrotina do Swoole também é uma solução para callbacks assíncronos. Em PHP, tanto as corrotinas do Swoole quanto o `yield + generator` são soluções de corrotina que permitem escrever código assíncrono de maneira quase síncrona. A diferença óbvia é que, no mecanismo de corrotina de `yield + generator`, cada operação de `I/O` precisa ser precedida da sintaxe `yield` para realizar a troca de corrotina, e cada nível de chamada precisa ser precedido por `yield`, caso contrário ocorrerão erros inesperados. Já a solução de corrotina do `Swoole` é bem mais elegante: o `I/O` é trocado implicitamente no nível mais baixo, sem adicionar sintaxe extra ou `yield` ao código, e a troca de CORROTINA acontece de forma transparente, reduzindo bastante a carga mental de manter um sistema assíncrono.

### O que é corrotina?

Já sabemos que corrotinas conseguem resolver muito bem o problema de desenvolvimento de um sistema assíncrono não-bloqueante, então o que são corrotinas? Por definição, *corrotinas são threads leves que são agendadas e gerenciadas por código do usuário, e não pelo kernel do sistema operacional, ou seja, em modo de usuário*. Isso pode ser entendido como uma implementação não padrão de threads, em que a troca é feita pelo usuário, e não pelo sistema operacional alocando tempo de `CPU`. Especificamente, cada `Worker process` do `Swoole` tem um scheduler coordenador para agendar corrotinas, e o momento de uma troca de corrotina ocorre quando há uma operação de `I/O` ou uma troca explícita de código. E como o processo executa as corrotinas como se fosse uma única thread, isso significa que existe apenas uma corrotina rodando por vez dentro do processo, e o momento de troca é claro. Assim, não há necessidade de lidar com problemas de sincronização/locks como em programação multi-thread.    
O código dentro de uma única corrotina ainda roda de forma serial. Em um servidor HTTP baseado em corrotinas, dá para entender que cada requisição é uma corrotina. Por exemplo, suponha que `coroutine A` seja criada para `request A` e `coroutine B` seja criada para `request B`. Ao processar `coroutine A`, o código chega em uma query `MySQL`; nesse momento, `coroutine A` dispara a troca de corrotina e continua aguardando o dispositivo de `I/O` retornar o resultado. Então ele troca para `coroutine B` e começa a processar a lógica de `coroutine B`. Quando encontrar outra operação de `I/O`, a troca acontece novamente; e então volta e continua de onde a corrotina A foi interrompida, e assim por diante. Ao encontrar uma operação `I/O`, ele alterna para outra corrotina para continuar, em vez de bloquear e esperar.   
O problema aqui é que a operação de query `MySQL` para * `coroutine A` precisa ser uma operação assíncrona não-bloqueante; caso contrário, o scheduler de corrotinas não conseguirá trocar para outra corrotina para continuar a execução * por causa do bloqueio. Esse é um dos problemas que precisam ser evitados na programação com corrotinas.

### Qual é a diferença entre corrotina e uma thread comum?

Como dissemos, corrotina é uma thread leve. Corrotinas e threads são adequadas para cenários de multitarefa. Nesse sentido, corrotinas são muito semelhantes a threads e têm seus próprios contextos, podendo compartilhar variáveis globais. Porém, a diferença é que múltiplas threads podem estar executando ao mesmo tempo, enquanto no `Swoole` só pode haver uma corrotina em execução por vez e as outras ficam pausadas. Além disso, uma thread normal é preemptiva: qual thread obtém recursos é determinado pelo sistema operacional; já a corrotina é colaborativa, e o direito de execução é alocado pelo estado do usuário.

## Considerações para programação com corrotinas

### Não pode existir código bloqueante

Código bloqueante dentro de uma corrotina fará com que o scheduler de corrotinas não consiga alternar para outra corrotina e continuar a execução. Por isso, precisamos impedir que exista código bloqueante dentro de corrotinas. Supondo que iniciamos `4 Worker` para lidar com requisições `HTTP` (normalmente o número de `Worker` iniciados é igual ao número de cores de `CPU` ou `2` vezes o número de cores de `CPU`). Se existir código bloqueante dentro da corrotina, teoricamente, se cada requisição bloquear por `1` segundo, então o `QPS` da aplicação também degradará para `4/s`. Isso sem dúvida degrada para uma situação similar ao `PHP-FPM`, então não devemos permitir código bloqueante dentro da corrotina.

Então, o que é código bloqueante? Podemos simplesmente considerar que a maioria das funções assíncronas fornecidas fora do Swoole — `MySQL`, `Redis`, `Memcache`, `MongoDB`, `HTTP`, `Socket`, operações de arquivo, `sleep/usleep` etc. — são código bloqueante, o que cobre praticamente todas as operações do dia a dia. Então como resolver? O `Swoole` fornece clientes de corrotina para MySQL, `PostgreSQL`, `Redis`, `HTTP` e `Socket`. Além disso, a partir do `Swoole 4.1`, o Swoole fornece a função `\Swoole\Runtime::enableCoroutine()` para tornar a maior parte do código bloqueante “corrotinado”. Basta executar `\Swoole\Runtime::enableCoroutine()` antes de criar corrotinas: o `Swoole` colocará todos os sockets que usam php_stream sob agendamento por corrotina. Isso pode ser entendido como fazer com que as operações mais comuns se tornem compatíveis com corrotinas, exceto `curl`. Mais detalhes podem ser encontrados nesta seção da [Documentação do Swoole](https://wiki.swoole.com/#/runtime).

No `Hyperf`, já tratamos isso para você; você só precisa prestar atenção no código bloqueante que `\Swoole\Runtime::enableCoroutine()` ainda não consegue “corrotinar” automaticamente.

### Não armazene estado via variáveis globais

Em uma aplicação persistente do `Swoole`, uma variável global no `Worker` é compartilhada dentro do `Worker`. E, pela introdução de corrotinas, sabemos que existirão múltiplas corrotinas no mesmo `Worker`. A troca de corrotina significa que um `Worker` processará múltiplas corrotinas (ou, diretamente, requisições) em um mesmo período. Isso significa que, se você usar variáveis globais para armazenar estado, os dados de estado poderão ser usados por várias corrotinas, ou seja, os dados podem se confundir entre requisições/corrotinas diferentes. As variáveis globais aqui se referem a `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER` etc. (variáveis iniciadas com `$_`), variáveis `global` e propriedades/variáveis `static`.   
Então o que devemos fazer quando precisamos usar esses recursos?

Para variáveis globais, elas são geradas por um `Request`, e o Request/Response do Hyperf é feito por [hyperf/http-message](https://github) .com/hyperf/http-message) implementando [PSR-7](https://www.php-fig.org/psr/psr-7/). Todas as variáveis globais podem ser encontradas no objeto Request.

Para variáveis `global` e variáveis `static`, no modo `PHP-FPM` a essência é sobreviver dentro do ciclo de vida de uma requisição. Já no `Hyperf`, por ser uma aplicação `CLI`, existem dois ciclos de vida mais longos: o `ciclo global` e o `ciclo de requisição (ciclo de corrotina)`.
- Ciclo global: basta criar uma variável estática para chamada global. Variáveis estáticas significam que qualquer corrotina e lógica de código compartilham os dados dessa variável estática após o serviço iniciar; isso significa que os dados armazenados não podem ser específicos de uma requisição ou de uma determinada corrotina;
- Ciclo de corrotina: como o `Hyperf` cria automaticamente uma corrotina para processar cada requisição, um ciclo de corrotina pode ser entendido como o ciclo de uma requisição. Dentro da corrotina, todos os dados de estado devem ser armazenados na classe `Hyperf\Context\Context`. Dados de qualquer estrutura são lidos e armazenados via `get` e `set` da classe. Fazer Get/Set de quaisquer dados no `Context (contexto de corrotina)` fica limitado à corrotina correspondente onde o get/set foi executado. E os dados de contexto relevantes também são destruídos automaticamente ao final da corrotina.

### Número máximo de corrotinas

Defina o parâmetro `max_coroutine` do `Swoole Server` via o método `set` para configurar o número máximo de corrotinas que podem existir em um processo `Worker`. Conforme a quantidade de corrotinas processadas pelo `Worker` aumenta, o uso de memória correspondente também aumenta. Para evitar exceder o limite `memory_limit` do `PHP`, defina o valor conforme o resultado de medição de carga do negócio. O valor padrão no `Swoole` é `3000`, e no projeto `hyperf-skeleton` ele é definido como `100000` por padrão.

## Uso de corrotinas

### Criar uma corrotina

Use as funções `Hyperf\Coroutine\co(callable $callable)` ou `Hyperf\Coroutine\go(callable $callable)`, ou o método `Hyperf\Coroutine\Coroutine::create(callable $callable)` para criar uma corrotina de forma simples. Métodos e clientes relacionados a corrotinas podem ser usados dentro da corrotina.

### Está rodando em ambiente de corrotina?

Em alguns casos, queremos determinar se estamos rodando no ambiente de corrotinas. Para código compatível tanto com ambiente de corrotina quanto sem corrotina, isso pode servir como base de decisão. Podemos usar o método `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` para obter o resultado.

### Obter o ID da corrotina

Em alguns casos, precisamos executar alguma lógica conforme o `coroutine ID`, como no `coroutine context`. Você pode obter o ID da corrotina atual com `Hyperf\Coroutine\Coroutine::id(): int`. Se não estiver no ambiente de corrotina, o método retornará `-1`.

### Channel

Similar ao `chan` da linguagem Go, `Channel` fornece suporte para modos multi-produtor e multi-consumidor entre corrotinas. A camada inferior implementa automaticamente a troca e o agendamento das corrotinas. `Channel` é similar ao array do PHP: ele apenas consome memória e não requer outros recursos adicionais; todas as operações são em memória, sem `I/O`. O uso é semelhante à fila `SplQueue`.
O `Channel` é usado principalmente para comunicação entre corrotinas. Quando queremos retornar algum dado de uma corrotina para outra, podemos passá-lo por meio de `Channel`.

Métodos principais:   
- `Channel->push`: quando existem outras corrotinas na fila aguardando por `pop`, uma corrotina consumidora é automaticamente chamada em sequência. Automaticamente faz `yield` e cede o controle quando a fila está cheia, aguardando outras corrotinas consumirem dados
- `Channel->pop`: faz `yield` automaticamente quando a fila está vazia, aguardando outra corrotina produzir dados. Depois que os dados são consumidos, a fila pode receber novos dados via push e automaticamente acorda uma corrotina produtora em sequência.
                   
O seguinte é um exemplo simples de comunicação entre corrotinas:

```php
<?php
co(function () {
    $channel = new \Swoole\Coroutine\Channel();
    co(function () use ($channel) {
        $channel->push('data');
    });
    $data = $channel->pop();
});
```

### Defer

Quando queremos rodar algum código ao final da corrotina, podemos usar a função `defer(callable $callable)` ou `Hyperf\Coroutine::defer(callable $callable)` para colocar uma função na forma de uma `stack`. Uma vez armazenadas, as funções na `stack` serão executadas uma a uma ao final da corrotina atual, seguindo LIFO (Last In, First Out).

### WaitGroup

`WaitGroup` é um recurso derivado de `Channel`. Se você conhece a linguagem `Go`, então conhece o recurso `WaitGroup`. No `Hyperf`, o objetivo do `WaitGroup` é bloquear a corrotina principal, aguardar até que todas as corrotinas filhas relevantes concluam a tarefa e então continuar. Esse bloqueio citado aqui é apenas para a corrotina principal (isto é, a corrotina atual) e não bloqueia o processo atual.   
Demonstramos esse recurso com um trecho de código:

```php
<?php
$wg = new \Hyperf\Coroutine\WaitGroup();
// Contador incrementa 2
$wg->add(2);
// Cria corrotina A
co(function () use ($wg) {
    // algum código
    // Contador decrementa 1
    $wg->done();
});
// Cria corrotina B
co(function () use ($wg) {
    // algum código
    // Contador decrementa 1
    $wg->done();
});
// Aguarda coroutine A and coroutine B finalizar
$wg->wait();
```

> Observe que o próprio `WaitGroup` também precisa ser usado dentro de uma corrotina.

### Parallel

O recurso `Parallel` é uma abstração com base no `WaitGroup` fornecido pelo Hyperf, oferecendo uma forma mais conveniente de uso do que `WaitGroup`. Vamos demonstrar com um trecho de código:

```php
<?php
$parallel = new \Hyperf\Coroutine\Parallel();
$parallel->add(function () {
    \Hyperf\Coroutine\Coroutine::sleep(1);
    return \Hyperf\Coroutine\Coroutine::id();
});
$parallel->add(function () {
    \Hyperf\Coroutine\Coroutine::sleep(1);
    return \Hyperf\Coroutine\Coroutine::id();
});
// $result é [1, 2]
$result = $parallel->wait();
```

Pelo código acima, vemos que levou apenas 1 segundo para obter o ID de duas corrotinas diferentes. Ao chamar `add(callable $callable)`, a classe `Parallel` cria automaticamente uma corrotina para ele e a adiciona ao dispatcher do `WaitGroup`.
Além disso, podemos simplificar ainda mais o código acima usando a função `parallel(array $callables)` para obter o mesmo propósito. A seguir está o código simplificado.

```php
<?php
use Hyperf\Coroutine\Coroutine;

// The passed array parameters can also use `key of array` to facilitate distinguish the result of coroutine, and the returned result will also return the corresponding result according to key.
$result = parallel([
    function () {
        Coroutine::sleep(1);
        return Coroutine::id();
    },
    function () {
        Coroutine::sleep(1);
        return Coroutine::id();
    }
]);
```

> Observe que o próprio `Parallel` também precisa ser usado dentro de uma corrotina.

### Contexto de corrotina

Como as corrotinas no mesmo processo compartilham memória e a execução/troca das corrotinas é não sequencial, é difícil controlar qual corrotina atual é (na prática, até dá, mas ninguém gostaria de fazer isso). Por isso, precisamos conseguir trocar o contexto correspondente no momento em que ocorre uma troca de corrotina.
Implementar gerenciamento de contexto para corrotinas no Hyperf é muito simples: com base nos métodos estáticos `set(string $id, $value)`, `get(string $id, $default = null)` e `has(string $id)` da classe `Hyperf\Context\Context`, conseguimos gerenciar dados de contexto. Os valores definidos e obtidos por esses métodos ficam limitados à corrotina atual. Ao final da corrotina, o contexto correspondente é liberado automaticamente. Não é necessário gerenciar manualmente e não há necessidade de se preocupar com risco de memory leaks.
y leaks.
