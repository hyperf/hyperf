# Registro de Serviço

O número de serviços aumenta com a divisão. Um grande número de serviços com uma grande quantidade de nós de cluster deve ser gerenciado para garantir a execução normal de todo o sistema. Deve haver um componente centralizado para integrar as informações dos vários serviços, ou seja, as informações de serviço espalhadas por todos os lugares serão agregadas. As informações agregadas podem ser o nome, o endereço, a quantidade etc. do componente que fornece o serviço. Cada componente possui um dispositivo de monitoramento, e quando o status de um determinado serviço nesse componente muda, ele é reportado ao componente centralizado para atualizar o status. Quando o solicitante do serviço requisita um determinado serviço, primeiro ele vai ao componente centralizado para obter informações do componente, como IP, porta etc., e então um determinado provedor do serviço é selecionado para acesso através de uma estratégia padrão ou personalizada. E esse componente centralizado é chamado, geralmente, de `Centro de Serviços` (`Service Center`). No Hyperf, implementamos o centro de serviços baseado no `Consul`. Mais centros de serviços serão adaptados no futuro.

# Instalação

```bash
composer require hyperf/service-governance
```

# Registrar Serviço

O registro de serviço pode ser feito definindo uma classe através da annotation `#[RpcService]`, o que pode ser considerado como a publicação do serviço. Até o momento, apenas o protocolo JSON RPC foi adaptado. Consulte [Serviço JSON RPC](pt-br/json-rpc.md) para mais detalhes.

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

Existem `4` parâmetros de `#[RpcService]`:
O atributo `name` é o nome deste serviço. Basta usar aqui um nome globalmente único, e o Hyperf gerará um ID correspondente com base nesse atributo e o registrará no centro de serviços;
O atributo `protocol` é o protocolo exposto pelo serviço. Até o momento, apenas `jsonrpc` e `jsonrpc-http` são suportados, correspondendo aos dois protocolos sob TCP e HTTP, respectivamente. O valor padrão é `jsonrpc-http`. O valor aqui corresponde à `key` do protocolo registrado em `Hyperf\Rpc\ProtocolManager`. Ambos são essencialmente protocolos JSON RPC. A diferença está na formatação de dados, no empacotamento de dados e nos transmissores de dados.
O atributo `server` é o `Server` a ser carregado pela classe de serviço que deve ser publicada. O valor padrão é `jsonrpc-http`. Esse atributo corresponde ao `name` sob `servers` no arquivo `config/autoload/server.php`. Isso também significa que precisamos definir um `Server` correspondente; detalharemos como lidar com isso no próximo capítulo;
O atributo `publishTo` define o centro de serviços onde o serviço será publicado. Atualmente, apenas `consul` é suportado, ou você pode deixá-lo como null. Quando é null, significa que o serviço não será publicado no centro de serviços, o que significa que você precisa lidar manualmente com o problema da descoberta de serviços. Quando o valor é `consul`, você precisa configurar as configurações relevantes do componente [hyperf/consul](pt-br/consul.md). Para usar essa funcionalidade, você precisa instalar o componente [hyperf/service-governance](https://github.com/hyperf/service-governance);

> O `use Hyperf\RpcServer\Annotation\RpcService;` é necessário quando a annotation `#[RpcService]` é usada.
