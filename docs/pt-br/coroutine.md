# Coroutine

## Conceito

Hyperf é construído sobre a coroutine do `Swoole 5`, que é um dos grandes fatores que permitem ao Hyperf oferecer alta performance.

### Modo de Execução do PHP-FPM

Antes de falarmos sobre o que está acontecendo, vamos falar sobre o modo de operação da arquitetura tradicional do `PHP-FPM`. O `PHP-FPM` é um hypervisor `FastCGI` multiprocesso, utilizado pela maioria das aplicações PHP. Suponha que usemos o `Nginx` para fornecer o serviço `HTTP` (é o mesmo ao usar `Apache`). Todas as requisições iniciadas pelo cliente chegam primeiro ao `Nginx`, que então encaminha a requisição para o `PHP-FPM` via protocolo `FastCGI`, e o `Master Process` do `PHP-FPM` alocará um `Worker Process` para cada requisição. Esse processamento significa que todo o processo fica bloqueado esperando tanto pelo parsing do script `PHP` quanto pelo resultado do negócio, e então o processo filho é reciclado, o que significa que a quantidade de requisições que você pode atender depende diretamente do número de processos do `PHP-FPM`. Supondo que o `PHP-FPM` tenha `200` `Worker Process`, e uma requisição leve `1` segundo, então, de forma simplificada, o servidor inteiro pode teoricamente atender no máximo 200, ou seja, o `QPS` é `200/s`. Em cenários de alta concorrência, essa performance frequentemente não é suficiente. Embora seja possível usar o `Nginx` como load balancer com múltiplos servidores `PHP-FPM` para fornecer o serviço, devido ao modelo de bloqueio do `PHP-FPM`, uma requisição ocupará ao menos uma conexão com o `MySQL`, então múltiplos nós gerarão obviamente muitas conexões com o `MySQL`, e o valor máximo padrão de conexões do `MySQL` é `100`. Embora seja possível modificá-lo, esse padrão claramente não consegue lidar adequadamente com cenários de alta concorrência.

### Sistema Assíncrono Não Bloqueante

Em um cenário de alta concorrência, o modelo assíncrono não bloqueante tem vantagens evidentes. A vantagem intuitiva é que o `Worker Process` não fica mais bloqueado de forma síncrona ao tratar uma requisição, mas pode tratar múltiplas requisições ao mesmo tempo. Sem espera de `I/O`, a capacidade de concorrência é extremamente forte, e um grande número de requisições pode ser iniciado ou mantido simultaneamente. Portanto, a desvantagem mais intuitiva, que você talvez já conheça, é o callback hell: a lógica de negócio precisa ser implementada dentro da respectiva função de callback, e se a lógica de negócio tiver múltiplas requisições de `I/O`, haverá várias camadas de funções de callback. O exemplo a seguir é um trecho de pseudocódigo sob o `Swoole 1.x`.

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
    // Query a row of data from users table
    $sql = 'select * from users where id = 1';
    $db->query($sql, function(swoole_mysql $db, $r) {
        if ($r === true) {
            $rows = $db->affected_rows;
            // Modify a row of data after the query is successful
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
Como pode ser visto nos trechos de código acima, quase toda operação exige uma função de callback, e o aninhamento e a estrutura de código de um callback em um cenário de negócio complexo com certeza vão te esgotar. Não é difícil perceber que essa abordagem é semelhante à escrita de métodos assíncronos em `JavaScript`, e o `JavaScript` oferece diversas soluções (derivadas de outras linguagens de programação, é claro), como `Promise`, `yield + generator`, `Async/Await`. Enquanto `Promise` é uma forma de encapsular callbacks, `yield + generator` e `Async/Await` precisam adicionar explicitamente algumas marcações de sintaxe ao código, que são boas alternativas aos callbacks, mas ainda exigem tempo para entender sua implementação e sintaxe.     
A coroutine do Swoole também é uma solução para callbacks assíncronos. Em PHP, tanto a coroutine do Swoole quanto o `yield + generator` são soluções de coroutine que permitem escrever código assíncrono de forma quase síncrona. A diferença evidente é que, no mecanismo de coroutine do `yield + generator`, cada operação de `I/O` precisa ser precedida pela sintaxe `yield` para implementar a troca de coroutine, e cada nível de chamada precisa ser precedido pela sintaxe `yield`, caso contrário ocorrerão erros inesperados. A solução de coroutine do `Swoole`, por outro lado, é muito mais elegante, com a troca de `I/O` sendo feita implicitamente na camada inferior, sem nenhuma sintaxe adicional ou `yield` sendo adicionado ao código, e a troca de coroutine acontece silenciosamente. Isso reduz enormemente a carga mental de manter um sistema assíncrono.

### O que é Coroutine?

Já sabemos que coroutines conseguem resolver muito bem o problema de desenvolvimento de sistemas assíncronos não bloqueantes, mas o que é, de fato, uma coroutine? Por definição, *Coroutines são threads leves que são escalonadas e gerenciadas pelo código do usuário, e não pelo kernel do sistema operacional, ou seja, em modo usuário*. Isso pode ser entendido diretamente como uma implementação de thread não padrão, mas cabe ao usuário fazer a troca, e não ao sistema operacional alocar tempo de `CPU`. Especificamente, cada `Worker process` do `Swoole` possui um coordenador (Scheduler) para escalonar as coroutines, e o momento de uma troca de coroutine ocorre quando há uma operação de `I/O` ou uma troca explícita de código, e o processo executa a coroutine como uma única thread. Isso significa que existe apenas uma coroutine em execução por vez em um processo, e o momento da troca é bem definido, portanto não há necessidade de lidar com o problema de sincronização de locks como na programação multithread.    
O código dentro de uma única coroutine ainda é executado de forma serial. Em um servidor de coroutine `HTTP`, entende-se que cada requisição é uma coroutine. Por exemplo, suponha que `coroutine A` seja criada para a `request A` e `coroutine B` seja criada para a `request B`. Então o código roda até consultar o `MySQL` enquanto processa a `coroutine A`, momento em que a `coroutine A` acionará a troca de coroutine; a `coroutine A` continuará esperando o dispositivo de `I/O` retornar o resultado, e então será feita a troca para a `coroutine B`, iniciando o processamento da lógica da `coroutine B`. Ao encontrar outra operação de `I/O`, aciona-se a troca de coroutine novamente, voltando e continuando de onde a coroutine A havia sido interrompida, e assim por diante. Quando uma operação de `I/O` é encontrada, ocorre a troca para outra coroutine para continuar, em vez de bloquear e esperar.   
O ponto importante aqui é que a operação de consulta ao `MySQL` para a `coroutine A` *precisa ser uma operação assíncrona não bloqueante*, caso contrário o escalonador de coroutines não conseguirá trocar para outra coroutine para continuar a execução devido ao bloqueio. Esse é um dos problemas que precisa ser evitado na programação com coroutine.

### Qual é a diferença entre coroutine e thread comum?

Como dissemos, coroutine é uma thread leve. Coroutines e threads são adequadas para cenários de múltiplas tarefas. Sob essa perspectiva, coroutines são muito semelhantes a threads e possuem seu próprio contexto, podendo compartilhar variáveis globais, mas a diferença é que múltiplas threads podem estar em execução ao mesmo tempo, enquanto na coroutine do `Swoole` só pode haver uma em execução, e as demais coroutines ficarão pausadas. Além disso, uma thread comum é preemptiva, ou seja, qual thread obtém os recursos é determinado pelo sistema operacional, enquanto a coroutine é colaborativa, e o direito de execução é distribuído pelo modo usuário.

## Considerações sobre a programação com Coroutine

### Não pode existir código bloqueante

Código bloqueante dentro da coroutine fará com que o escalonador de coroutines não consiga trocar para outra coroutine para continuar executando código, então precisamos evitar a existência de código bloqueante dentro da coroutine. Supondo que tenhamos iniciado `4 Worker` para tratar requisições `HTTP` (geralmente o número de `Worker` iniciados é igual ao número de núcleos de `CPU` ou `2` vezes o número de núcleos de `CPU`). Se houver código bloqueante na coroutine, teoricamente, se cada requisição bloquear por `1` segundo, o `QPS` da aplicação também degenerará para `4/s`, o que sem dúvida degenera para uma situação semelhante à do `PHP-FPM`. Portanto, não podemos permitir a existência de código bloqueante na coroutine.

Então quais são os códigos bloqueantes? Podemos considerar de forma simplificada que a maioria das funções não assíncronas fornecidas por `MySQL`, `Redis`, `Memcache`, `MongoDB`, `HTTP`, `Socket`, operações de arquivo, `sleep/usleep`, etc., são código bloqueante, o que abrange quase todas as operações do dia a dia. Então como resolver isso? O `Swoole` fornece cliente de `MySQL`, `PostgreSQL`, `Redis`, `HTTP`, `Socket` para o cliente de coroutine. Além disso, a partir do `Swoole 4.1`, o Swoole fornece a função `\Swoole\Runtime::enableCoroutine()` para tornar a maior parte do código bloqueante compatível com coroutine; basta executar `\Swoole\Runtime::enableCoroutine()` antes de criar a coroutine, e o `Swoole` fará com que todos os sockets que usam php_stream sejam escalonados por coroutine, o que pode ser entendido como tornar as operações mais comuns compatíveis com coroutine, exceto o `curl`. Informações mais detalhadas podem ser encontradas nesta seção da [Documentação do Swoole](https://wiki.swoole.com/#/runtime).

No `Hyperf`, já tratamos isso para você; você só precisa se atentar ao código bloqueante que `\Swoole\Runtime::enableCoroutine()` ainda não consegue tornar compatível com coroutine automaticamente.

### Não é possível armazenar estado através de variáveis globais

Sob a aplicação persistente do `Swoole`, uma variável global em um `Worker` é compartilhada dentro do `Worker`, e a partir da introdução sobre coroutine sabemos que existirão múltiplas coroutines no mesmo `Worker`. A troca de coroutine significa que um `Worker` processará múltiplas coroutines (ou pode-se entender diretamente como requisições) em um único período de tempo, o que significa que, se você usar variáveis globais para armazenar estado, os dados de estado podem ser usados por múltiplas coroutines, ou seja, os dados podem se misturar entre diferentes requisições ou diferentes coroutines. As variáveis globais aqui se referem a `$_GET/$_POST/$_REQUEST/$_SESSION/$_COOKIE/$_SERVER`, etc., variáveis que começam com `$_`, variáveis `global` e propriedades ou variáveis `static`.   
Então o que devemos fazer quando precisamos usar esses recursos?

Para variáveis globais, elas são geradas por uma `Request`, e o Request/Response do Hyperf são feitos pelo [hyperf/http-message](https://github.com/hyperf/http-message) implementando o [PSR-7](https://www.php-fig.org/psr/psr-7/); todas as variáveis globais podem ser encontradas no objeto Request.

Para a variável `global` e a variável `static`, no modo `PHP-FPM`, a essência é sobreviver dentro de um ciclo de vida de request, e no `Hyperf`, por ser uma aplicação `CLI`, existirão dois ciclos de vida longos: `global cycle` e `request cycle (coroutine cycle)`.
- Global cycle: precisamos apenas criar uma variável static para chamada global. Variáveis static significam que qualquer coroutine e lógica de código compartilham os dados dessa variável static após o serviço ser iniciado, o que significa que os dados armazenados não podem ser especiais para uma requisição ou determinada coroutine;
- Coroutine cycle: como o `Hyperf` criará automaticamente uma coroutine para cada requisição a ser processada, então um ciclo de coroutine também pode ser entendido aqui como um ciclo de requisição. Na coroutine, todos os dados de estado devem ser armazenados na classe `Hyperf\Context\Context`, e os dados de qualquer estrutura são lidos e armazenados através dos métodos `get` e `set` da classe. Obter ou definir qualquer dado no `Context (contexto de coroutine)` é limitado à coroutine correspondente em que a função get ou set foi executada, e os dados de contexto relacionados também são automaticamente destruídos ao final da coroutine.

### Número máximo de coroutines

Configure o parâmetro `max_coroutine` do `Swoole Server` através do método `set` para definir o número máximo de coroutines que podem existir em um processo `Worker`. Como o número de coroutines processadas pelo processo `Worker` aumenta, o consumo correspondente de memória também aumentará. Para evitar exceder o limite de `memory_limit` do `PHP`, defina o valor de acordo com o resultado real de testes de carga do negócio. O valor padrão do `Swoole` é `3000`, que é definido como `100000` por padrão no projeto `hyperf-skeleton`.

## Uso de coroutine

### Criar uma coroutine

Use as funções `Hyperf\Coroutine\co(callable $callable)` ou `Hyperf\Coroutine\go(callable $callable)`, ou o método `Hyperf\Coroutine\Coroutine::create(callable $callable)` para criar uma coroutine facilmente. Métodos e clients relacionados a coroutine podem ser usados dentro da coroutine.

### Está sendo executado em ambiente de coroutine?

Em alguns casos, queremos determinar se a execução atual está em ambiente de coroutine, para que, com base nisso, algum código compatível com ambiente de coroutine e não-coroutine seja usado como critério de decisão. Podemos usar o método `Hyperf\Coroutine\Coroutine::inCoroutine(): bool` para obter o resultado.

### Obter o ID da coroutine

Em alguns casos, precisamos executar alguma lógica de acordo com o `coroutine ID`, como o `coroutine context`. Você pode obter o ID da coroutine atual através de `Hyperf\Coroutine\Coroutine::id(): int`; se não estiver em ambiente de coroutine, o método retornará `-1`.

### Channel

Semelhante ao `chan` da linguagem Go, o `Channel` oferece suporte para modos de múltiplos produtores e múltiplos consumidores de coroutine. A camada inferior implementa automaticamente a troca e o escalonamento da coroutine. O `Channel` é semelhante a um array do PHP, ele apenas ocupa memória, não há outros recursos adicionais a serem aplicados, todas as operações são operações de memória, sem `I/O`; o uso é semelhante à fila `SplQueue`.
`Channel` é usado principalmente para comunicação entre coroutines. Quando queremos retornar alguns dados de uma coroutine para outra coroutine, podemos transmiti-los através do `Channel`. 

Principais métodos:   
- `Channel->push`: Quando há outras coroutines na fila esperando pelo `pop` de dados, uma coroutine consumidora é automaticamente invocada em sequência. Automaticamente executa `yield` para liberar o direito de controle quando a fila está cheia, esperando outras coroutines consumirem os dados
- `Channel->pop`: Executa automaticamente `yield` quando a fila está vazia, esperando outra coroutine produzir dados. Após os dados serem consumidos, a fila pode inserir novos dados nela e automaticamente despertar uma coroutine produtora em sequência.
                   
A seguir, um exemplo simples de comunicação entre coroutines:

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

Quando queremos executar algum código ao final da coroutine, podemos usar a função `defer(callable $callable)` ou `Hyperf\Coroutine::defer(callable $callable)` para colocar uma função na forma de uma `stack`. Uma vez armazenadas, as funções na `stack` serão executadas uma a uma ao final da coroutine atual, na ordem LIFO (Last in, First out).

### WaitGroup

`WaitGroup` é um recurso derivado do `Channel`. Se você conhece a linguagem `Go`, já conhece o recurso `WaitGroup`. No `Hyperf`, o objetivo do `WaitGroup` é bloquear a coroutine principal, esperando até que todas as coroutines filhas relacionadas tenham completado a tarefa, para então continuar a execução. O bloqueio de espera mencionado aqui é apenas para a coroutine principal (ou seja, a coroutine atual) e não bloqueia o processo atual.   
Vamos demonstrar esse recurso com um trecho de código:

```php
<?php
$wg = new \Hyperf\Coroutine\WaitGroup();
// Counter increase 2
$wg->add(2);
// Create coroutine A
co(function () use ($wg) {
    // some code
    // Counter decrease 1
    $wg->done();
});
// Create coroutine B
co(function () use ($wg) {
    // some code
    // Counter decrease 1
    $wg->done();
});
// Wait for coroutine A and coroutine B finished
$wg->wait();
```

> Observe que o `WaitGroup` em si também precisa ser usado dentro de uma coroutine.

### Parallel

O recurso `Parallel` é uma abstração baseada no recurso `WaitGroup` fornecido pelo Hyperf, uma forma mais conveniente de uso do que o `WaitGroup`. Vamos demonstrar com um trecho de código:

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
// $result is [1, 2]
$result = $parallel->wait();
```

A partir do código acima, podemos ver que levou apenas 1 segundo para obter o ID de duas coroutines diferentes. Ao chamar `add(callable $callable)`, a classe `Parallel` criará automaticamente uma coroutine para ela e a associará ao dispatcher do `WaitGroup`.
Não só isso, podemos simplificar ainda mais o código acima usando a função `parallel(array $callables)` para alcançar o mesmo objetivo. A seguir está o código simplificado.

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

> Observe que o `Parallel` em si também precisa ser usado dentro de uma coroutine.

### Coroutine Context

Como as coroutines no mesmo processo compartilham memória, a execução/troca das coroutines é não sequencial, o que significa que é difícil controlar qual coroutine é a atual *(na verdade, seria possível, mas ninguém gostaria de fazer isso assim)*, então precisamos ser capazes de trocar o contexto correspondente no mesmo momento em que ocorre uma troca de coroutine.
Implementar o gerenciamento de contexto para coroutines no Hyperf é muito simples: com base nos métodos estáticos `set(string $id, $value)`, `get(string $id, $default = null)` e `has(string $id)` da classe `Hyperf\Context\Context`, é possível completar o gerenciamento dos dados de contexto. Os valores definidos e obtidos por esses métodos são limitados à coroutine atual. Ao final da coroutine, o contexto correspondente é automaticamente liberado. Não há necessidade de gerenciamento manual, nem de se preocupar com o risco de memory leak.
