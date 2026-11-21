O Hyperf fornece suporte a configurações externas para sistemas distribuídos, com adaptações prontas por padrão:

- [ctripcorp/apollo](https://github.com/ctripcorp/apollo): um projeto open source da Ctrip; o componente [hyperf/config-apollo](https://github.com/hyperf/config-apollo) fornece suporte funcional.
- A Aliyun fornece um serviço gratuito de configuration center: [ACM (Application Config Manager)](https://help.aliyun.com/product/59604.html); o componente [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) fornece suporte.

## Por que usar um Configuration Center?

Com a evolução dos serviços e o upgrade para arquiteturas de microsserviços, o número de serviços e a configuração das aplicações (vários microsserviços, vários endereços de servidor, vários parâmetros) podem não ser bem atendidos por configurações em arquivos tradicionais ou em banco de dados.

As necessidades de gestão de configuração também podem envolver: controle de permissões (ACL), versionamento e rollback, validação de formato, publicação gradual (grayscale), isolamento de configuração por cluster etc., além de:

- Segurança: a configuração fica junto do código-fonte em sistemas de versionamento, o que pode facilitar vazamento de configurações.
- Atualidade: ao alterar uma configuração, cada servidor precisa modificar e reiniciar o serviço da aplicação.
- Limitações: não é possível suportar ajustes dinâmicos, como switches de log, feature flags etc.

Portanto, podemos gerenciar configurações de forma mais científica por meio de um configuration center.

## Instalação

### Apollo

```bash
composer require hyperf/config-apollo
```

### Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

## Usar Apollo

Se você não substituiu o componente de configuração padrão (ou seja, ainda usa [hyperf/config](https://github.com/hyperf/config)), adaptar o Apollo como configuration center é bem simples.

- Instale [hyperf/config-apollo](https://github.com/hyperf/config-apollo) via Composer executando `composer require hyperf/config-apollo`.
- Adicione o arquivo de configuração `apollo.php` na pasta `config/autoload`. Exemplo:

```php
<?php
return [
    // Se deve habilitar o processo do centro de configuração. Quando true, um processo ConfigFetcherProcess é iniciado automaticamente para atualizar a configuração
        'enable' => true,
    // Apollo Server
        'server' => 'http://127.0.0.1:8080',
    // Seu AppId
        'appid' => 'test',
    // O cluster onde a aplicação atual está localizada
        'cluster' => 'default',
    // Namespace que a aplicação atual precisa acessar; pode configurar múltiplos namespaces
    'namespaces' => [
        'application',
    ],
    // Modo estrito. When the value is false, the configuration value that pulled from Apollo will always is string type, when the value is true, the configuration value will transfer to the suitable type according to the original value type on config container.
    'strict_mode' => false,
// Intervalo de atualização da configuração (segundos)
    'interval' => 5,
];
```

## Usar Aliyun ACM

Acessar o configuration center do Aliyun ACM é tão simples quanto o Apollo: apenas dois passos.

- Execute `composer require hyperf/config-aliyun-acm` para instalar [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm).
- Adicione o arquivo de configuração `aliyun_acm.php` na pasta `config/autoload`. Exemplo:

```php
<?php
return [
    // Se deve habilitar o processo do centro de configuração. Quando true, um processo ConfigFetcherProcess é iniciado automaticamente para atualizar a configuração
    'enable' => true,
    // Intervalo de atualização da configuração (segundos)
    'interval' => 5,
    // Endereço do endpoint do ACM, dependendo da sua Availability Zone
    'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    // Namespace que a aplicação atual precisa acessar
    'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
    // Data ID da sua configuração
    'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
    // Group da sua configuração
    'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
    // Sua Access Key da conta Aliyun
    'access_key' => env('ALIYUN_ACM_AK', ''),
    // Sua Secret Key da conta Aliyun
    'secret_key' => env('ALIYUN_ACM_SK', ''),
];
```

## Escopo das atualizações de configuração

Na implementação padrão, um processo `ConfigFetcherProcess` busca a configuração do `namespace` correspondente no Configuration Center conforme o `interval` configurado, envia a nova configuração para cada worker via IPC e atualiza o objeto correspondente a `Hyperf\Contract\ConfigInterface`.

Observe que a configuração atualizada só atualiza o objeto `Config`; portanto, isso se aplica apenas a configurações da camada de aplicação ou de negócio. Não envolve mudanças de configuração na camada do framework.

Como mudanças de configuração na camada do framework normalmente exigem reiniciar o serviço, caso você tenha essa necessidade, também é possível atingir isso implementando seu próprio `ConfigFetcherProcess`.

## Configurar evento de atualização

Durante a execução do configuration center, se a configuração mudar, o evento `Hyperf\ConfigCenter\Event\ConfigChanged` será disparado. Você pode escutar esses eventos para atender às suas necessidades.

```php
<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\ConfigCenter\Event\ConfigChanged;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class DbQueryExecutedListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            ConfigChanged::class,
        ];
    }

    public function process(object $event)
    {
        var_dump($event);
    }
}
```
