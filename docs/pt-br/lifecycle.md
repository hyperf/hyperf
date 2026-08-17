# Life Cycle

## Life Cycle do Framework

Hyperf é baseado no [Swoole](http://github.com/swoole/swoole-src). Para entender o Life Cycle do Hyperf, entender o Life Cycle do [Swoole](http://github.com/swoole/swoole-src) também é fundamental.   
 
O gerenciamento de comandos do Hyperf é suportado por padrão pelo [symfony/console](https://github.com/symfony/console) *(caso deseje substituir esse componente, você também pode alterar o arquivo de entrada do skeleton para o componente que desejar utilizar)*. Após executar `php bin/hyperf.php start`, o processo será assumido pela classe de comando `Hyperf\Server\Command\StartServer` e iniciado um a um de acordo com o `Server` definido no arquivo de configuração `config/autoload/server.php`.   
 
Quanto à inicialização do container de injeção de dependência, não a implementamos através de nenhum componente, pois assim que ela é implementada por algum componente, o acoplamento se torna muito evidente. Portanto, por padrão, o arquivo de configuração `config/container.php` é carregado pelo arquivo de entrada para inicializar o container.

## Life Cycle da Request e da Coroutine

Quando o Swoole trata cada conexão, ele cria uma coroutine para tratá-la por padrão, principalmente nos eventos `onRequest`, `onReceive` e `onConnect`, de modo que se pode entender que cada request é uma coroutine. Como criar coroutines também é uma operação normal, uma coroutine de request pode conter muitas outras coroutines, e coroutines dentro do mesmo processo compartilham memória, mas a ordem de escalonamento é não sequencial, e as coroutines são essencialmente independentes entre si, sem relação de parentesco (pai-filho). Por isso, o tratamento de estado de cada coroutine precisa ser gerenciado através do [Coroutine Context](pt-br/coroutine.md#coroutine-context).
