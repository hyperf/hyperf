# Registro de serviços

Com o aumento do número de serviços conforme a divisão do sistema, uma grande quantidade de serviços com muitos nós de cluster precisa ser gerenciada para garantir a execução normal de todo o sistema. Deve existir um componente centralizado para integrar as informações de vários serviços; isto é, agregar as informações de serviço que estão espalhadas em diferentes lugares.

As informações agregadas podem ser o nome, endereço, quantidade etc. do componente que fornece o serviço. Cada componente possui um dispositivo de monitoramento e, quando o status de um determinado serviço muda, ele reporta ao componente centralizado para atualizar o status. Quando um consumidor precisa acessar um serviço, primeiro ele vai ao componente centralizado para obter informações como IP, porta etc., e então um provedor do serviço é selecionado para acesso por uma estratégia padrão ou customizada.

Esse componente centralizado é geralmente chamado de `Service Center`. No Hyperf, implementamos o service center com base no `Consul`. Mais service centers serão adaptados no futuro.

# Instalação

```bash
composer require hyperf/service-governance
```

# Registrar serviço

O registro de serviço pode ser feito definindo uma classe por meio da anotação `#[RpcService]`, o que pode ser entendido como publicar um serviço. Até o momento, apenas o protocolo JSON RPC foi adaptado. Consulte [JSON RPC Service](pt-br/json-rpc.md) para mais detalhes.

```php
<?php

namespace App\JsonRpc;

use Hyperf\RpcServer\Annotation\RpcService;

#[RpcService(name: "CalculatorService", protocol: "jsonrpc-http", server: "jsonrpc-http")]
class CalculatorService implements CalculatorServiceInterface
{
    // Implement an add method with only int type in this example.
    public function calculate(int $a, int $b): int
    {
        // Specific implementation of the service method
        return $a + $b;
    }
}
```

Existem `4` parâmetros em `#[RpcService]`:
`name` é o nome deste serviço. Use um nome globalmente único; o Hyperf irá gerar um ID correspondente com base nesse atributo e registrá-lo no service center;
`protocol` é o protocolo que o serviço expõe. Até agora, apenas `jsonrpc` e `jsonrpc-http` são suportados, correspondendo aos protocolos sob TCP e HTTP, respectivamente. O valor padrão é `jsonrpc-http`. Esse valor corresponde à `key` do protocolo registrado em `Hyperf\Rpc\ProtocolManager`. Ambos são essencialmente protocolos JSON RPC; a diferença está na formatação dos dados, empacotamento e transmissor de dados;
`server` é o `Server` ao qual a classe do serviço publicado será vinculada. O valor padrão é `jsonrpc-http`. Esse atributo corresponde ao `name` em `servers` no arquivo `config/autoload/server.php`. Isso também significa que precisamos definir um `Server` correspondente; vamos detalhar isso no próximo capítulo;
`publishTo` define em qual service center o serviço deve ser publicado. Atualmente, apenas `consul` é suportado, ou você pode deixar como null. Quando é null, significa que o serviço não será publicado no service center, e você precisará lidar manualmente com descoberta de serviços. Quando o valor é `consul`, você precisa configurar o componente [hyperf/consul](pt-br/consul.md). Para usar essa função, você precisa instalar o componente [hyperf/service-governance](https://github.com/hyperf/service-governance).

> O `use Hyperf\RpcServer\Annotation\RpcService;` é necessário quando a anotação `#[RpcService]` é usada.
