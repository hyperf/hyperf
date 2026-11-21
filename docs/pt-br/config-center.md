O Hyperf fornece suporte a configuração externa para sistemas distribuídos, que é adaptado por padrão:

- [ctripcorp/apollo](https://github.com/ctripcorp/apollo) Um projeto open source da Ctrip, com suporte funcional fornecido pelo componente [hyperf/config-apollo](https://github.com/hyperf/config-apollo).
- A Aliyun fornece um serviço gratuito de centro de configuração, o [ACM (Application Config Manager)](https://help.aliyun.com/product/59604.html), com suporte funcional fornecido pelo componente [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm).

## Por que usar o Centro de Configuração?

Com o desenvolvimento dos serviços, a evolução da arquitetura de microsserviços, o aumento do número de serviços e das configurações das aplicações (vários microsserviços, vários endereços de servidor, vários parâmetros), o método tradicional de arquivo de configuração e o método de banco de dados podem não atender aos requisitos dos desenvolvedores em relação ao gerenciamento de configuração. O gerenciamento de configuração também pode envolver gerenciamento de permissões ACL, gerenciamento de versão e rollback de configuração, validação de formato, publicação gradual (grayscale) de configuração, isolamento de configuração de cluster, entre outros, além de:

- Segurança: A configuração acompanha o código-fonte salvo no sistema de gerenciamento de versão, o que pode facilmente causar vazamento de configuração
- Tempestividade: Ao modificar a configuração, cada servidor precisa modificar e reiniciar o serviço para cada aplicação.
- Limitações: Ajustes dinâmicos não podem ser suportados, como switches de log, switches de funcionalidades etc.

Portanto, podemos gerenciar as configurações relevantes de forma científica através de um centro de configuração.

## Instalação

### Apollo

```bash
composer require hyperf/config-apollo
```

### Aliyun ACM

```bash
composer require hyperf/config-aliyun-acm
```

## Usando o Apollo

Se você não substituiu o componente de configuração padrão e ainda usa o componente [hyperf/config](https://github.com/hyperf/config), adaptar o Centro de Configuração Apollo é muito simples.
- Via composer [hyperf/config-apollo](https://github.com/hyperf/config-apollo), execute o comando `composer require hyperf/config-apollo`
- Adicione um arquivo de configuração `apollo.php` à pasta `config/autoload`. A configuração é a seguinte:

```php
<?php
return [
    // Whether to enable the process of the configuration center. When true, a ConfigFetcherProcess process is automatically started to update the configuration
    'enable' => true,
    // Apollo Server
    'server' => 'http://127.0.0.1:8080',
    // Your AppId
    'appid' => 'test',
    // The cluster where the current application is located
    'cluster' => 'default',
    // Namespace that the current application needs to access, can be configured multiple namespcaes
    'namespaces' => [
        'application',
    ],
    // Strict mode. When the value is false, the configuration value that pulled from Apollo will always is string type, when the value is true, the configuration value will transfer to the suitable type according to the original value type on config container.
    'strict_mode' => false,
    // The interval of update configuration (seconds)
    'interval' => 5,
];
```

## Usando o Aliyun ACM

Acessar o Centro de Configuração Aliyun ACM é tão fácil quanto o Apollo, apenas dois passos.
- Execute o comando `composer require hyperf/config-aliyun-acm` via Composer para instalar o [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm)
- Adicione um arquivo de configuração `aliyun_acm.php` à pasta `config/autoload`. A configuração é a seguinte:

```php
<?php
return [
    // Whether to enable the process of the configuration center. When true, a ConfigFetcherProcess process is automatically started to update the configuration
    'enable' => true,
    // The interval of update configuration (seconds)
    'interval' => 5,
    // ACM endpoint address, depending on your Availability Zone
    'endpoint' => env('ALIYUN_ACM_ENDPOINT', 'acm.aliyun.com'),
    // Namespace that the current application needs to access
    'namespace' => env('ALIYUN_ACM_NAMESPACE', ''),
    // The Data ID of your configuration
    'data_id' => env('ALIYUN_ACM_DATA_ID', ''),
    // The Group of your configuration
    'group' => env('ALIYUN_ACM_GROUP', 'DEFAULT_GROUP'),
    // Your Access Key of aliyun account
    'access_key' => env('ALIYUN_ACM_AK', ''),
    // Your Secret Key of aliyun account
    'secret_key' => env('ALIYUN_ACM_SK', ''),
];
```

## O escopo da atualização de configuração

Na implementação padrão desta funcionalidade, um processo `ConfigFetcherProcess` obtém a configuração do `namespace` correspondente do Centro de Configuração de acordo com o `interval` configurado, e passa a nova configuração obtida para cada worker através de comunicação IPC, atualizando o objeto correspondente a `Hyperf\Contract\ConfigInterface`.
Deve-se notar que a configuração atualizada atualizará apenas o objeto `Config`, portanto isso se aplica apenas à configuração da camada de aplicação ou da camada de negócio. Não envolve alterações de configuração da camada do framework. Porque as alterações de configuração da camada do framework exigem que o serviço seja reiniciado; se você tiver tal necessidade, também é possível alcançá-la implementando o `ConfigFetcherProcess` por conta própria.

## Evento de atualização de configuração

Durante a execução do centro de configuração, se a configuração mudar, o evento `Hyperf\ConfigCenter\Event\ConfigChanged` será disparado correspondentemente. Você pode escutar esses eventos para atender às suas necessidades.

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
