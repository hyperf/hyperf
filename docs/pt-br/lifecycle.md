# Ciclo de vida

## Ciclo de vida do framework

O Hyperf é baseado no [Swoole](http://github.com/swoole/swoole-src). Para entender o ciclo de vida do Hyperf, é crucial também entender o ciclo de vida do [Swoole](http://github.com/swoole/swoole-src).

O gerenciamento de comandos do Hyperf é suportado por padrão pelo [symfony/console](https://github.com/symfony/console) *(se você quiser substituir esse componente, também pode trocar o arquivo de entrada do skeleton para o componente que deseja usar)*. Após executar `php bin/hyperf.php start`, o processo será assumido pela classe de comando `Hyperf\Server\Command\StartServer` e iniciado, um a um, de acordo com o `Server` definido no arquivo de configuração `config/autoload/server.php`.

Quanto à inicialização do container de injeção de dependências, não a implementamos por nenhum componente específico, porque, uma vez implementada por algum componente, o acoplamento ficaria muito evidente. Por isso, por padrão, o arquivo de configuração `config/container.php` é carregado pelo arquivo de entrada para inicializar o container.

## Ciclo de vida de requisições e corrotinas

Quando o Swoole lida com cada conexão, ele cria por padrão uma corrotina para tratá-la, principalmente nos eventos `onRequest`, `onReceive` e `onConnect`. Portanto, pode-se entender que cada requisição é uma corrotina. Como criar corrotinas também é uma operação normal, uma corrotina de requisição pode conter muitas corrotinas. Corrotinas dentro do mesmo processo compartilham memória, mas a ordem de agendamento não é sequencial. As corrotinas são essencialmente independentes entre si, sem relação de pai/filho. Por isso, o processamento de estado de cada corrotina precisa ser gerenciado via [Coroutine Context](pt-br/coroutine.md#coroutine context).
