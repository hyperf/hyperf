# Configuração

Quando você está usando um projeto criado a partir do projeto hyperf/hyperf-skeleton, todos os arquivos de configuração do Hyperf estão na pasta config no diretório raiz, e cada opção possui instruções, para que você possa sempre verificar e se familiarizar com as opções disponíveis.

# Instalação

```bash
composer require hyperf/config
```

# Estrutura do arquivo de configuração

A estrutura a seguir é apenas a estrutura no caso da configuração padrão fornecida pelo Hyperf-Skeleton, e a situação real irá variar dependendo dos componentes dos quais se depende ou que são utilizados.
```
config
├── autoload // The configuration file in this folder will be loaded by the configuration component itself, and the file name in the folder will be the first key value.
│   ├── amqp.php  // Used to manage AMQP component
│   ├── annotations.php // Used to manage Annotation
│   ├── apollo.php // Used to manage Apollo Configuration Center
│   ├── aspects.php // Used to manage Aspect of AOP
│   ├── async_queue.php // Used to manage Async-Queue component
│   ├── cache.php // Used to manage Cache component
│   ├── commands.php // Used to manage Custom Command
│   ├── consul.php // Used to manage Consul Client
│   ├── databases.php // Used to manage Database
│   ├── dependencies.php // Used to manage the relationship of dependencies of DI
│   ├── devtool.php // Used to manage Dev-Tool
│   ├── exceptions.php // Used to manage Exception Handler
│   ├── listeners.php // Used to manage Event Listener
│   ├── logger.php // Used to manage Logger
│   ├── middlewares.php // Used to manage Middleware
│   ├── opentracing.php // Used to manage Open-Tracing
│   ├── processes.php // Used to manage Custom Process
│   ├── redis.php // Used to manage Redis Client
│   └── server.php // Used to manage Server
├── config.php // Configuration for managing users or frameworks, such as relatively independent configuration can also be placed in the autoload folder
├── container.php // Responsible for the initialization of the container, running as a configuration file and eventually returning a Psr\Container\ContainerInterface object
└── routes.php // Used to manage Routing
```

## Relação entre `config.php` e os arquivos de configuração na pasta `autoload`

Os arquivos de configuração na pasta `autoload` e o `config.php` serão escaneados e injetados no objeto correspondente de `Hyperf\Contract\ConfigInterface` quando o servidor iniciar. A estrutura configurada é um grande array de pares chave-valor; a diferença está na forma das duas configurações. O nome do arquivo de configuração dentro de `autoload` existirá como a chave de primeiro nível, enquanto dentro de `config.php` isso deve ser definido como o primeiro nível. Vamos usar o exemplo a seguir para demonstrar.
Vamos supor que exista um arquivo `config/autoload/client.php` com o seguinte conteúdo:

```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```

Então queremos obter o valor de `timeout`, cuja chave correspondente é `client.request.timeout`;

Vamos supor que queremos obter o mesmo resultado com a mesma chave, mas a configuração está escrita no arquivo `config/config.php`; então o conteúdo do arquivo deve ser assim:

```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## Usando o Config Component do Hyperf

Este componente é o componente de configuração oficial padrão implementado para a interface `Hyperf\Contract\ConfigInterface`, definida pelo componente [hyperf/config](https://github.com/hyperf/config). O objeto `Hyperf\Config\Config` é vinculado à interface através do ConfigProvider do componente.

### Definir valor de configuração

As configurações em `config/config.php`, `config/autoload/server.php` e na pasta `autoload` podem ser escaneadas e injetadas no objeto correspondente de `Hyperf\Contract\ConfigInterface` quando o servidor inicia. Esse processo é feito pelo `Hyperf\Config\ConfigFactory` quando o objeto Config é instanciado.

### Obter valor de configuração

O Config Component fornece três formas de obter o valor de configuração: através do objeto `Hyperf\Config\Config`, através da annotation `#[Value]`, e através da função `config(string $key, $default)`.

#### Obter valor de configuração através do Config Object

Esta forma exige que você já tenha uma instância do objeto `Config`. O objeto padrão é `Hyperf\Config\Config`. Para detalhes sobre a instância de injeção, consulte o capítulo [Dependency Injection](pt-br/di.md).

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Get the configuration corresponding to $key by get(string $key, $default): mixed method, the $key value can be positioned to the subordinate array by the . connector, and $default is the default value returned when the corresponding value does not exist.
$config->get($key, $default);
```

#### Obter configuração através da annotation `#[Value]`

Esta forma exige que o objeto seja criado pelo componente [hyperf/di](https://github.com/hyperf/di). Os detalhes da instância de injeção podem ser encontrados no capítulo [Dependency Injection](pt-br/di.md); no exemplo assumimos que `IndexController` é uma classe `Controller` já definida, e a classe `Controller` deve ser criada pelo container do `DI`;
A string em `#[Value()]` corresponde ao parâmetro `$key` em `$config->get($key)`. Quando a instância do objeto é criada, a configuração correspondente é automaticamente injetada na propriedade da classe definida.

```php
<?php
use Hyperf\Config\Annotation\Value;

class IndexController
{
    
    #[Value(key: "config.key")]
    private $configValue;
    
    public function index()
    {
        return $this->configValue;
    }
    
}
```

#### Obter configuração através da função config()

A configuration correspondente pode ser obtida através da função `config(string $key, $default)` em qualquer lugar, mas essa forma de uso significa que os componentes [hyperf/config](https://github.com/hyperf/config) e [hyperf/support](https://github.com/hyperf/support) são fortemente dependentes para sua aplicação.

### Determinar se a configuração existe

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// The has(): bool method is used to determine whether the corresponding $key value exists in the configuration, and the $key value can be mapped to the subordinate array by the . connector.
$config->has($key);
```

## Variável de ambiente

É um requisito comum usar configurações diferentes para ambientes operacionais diferentes. Por exemplo, a configuração do Redis do ambiente de teste e do ambiente de produção é diferente, e a configuração do ambiente de produção não pode ser submetida ao sistema de gerenciamento de versão do código-fonte para evitar vazamento de informações.

No Hyperf, oferecemos uma solução para variáveis de ambiente, usando a funcionalidade de parsing de variáveis de ambiente fornecida pelo [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) e a função `env()` para obter o ambiente. Esse requisito se torna bem fácil de resolver.

Em uma aplicação Hyperf recém-instalada, seu diretório raiz conterá um arquivo `.env.example`. No caso do Hyperf instalado via Composer, o Composer copiará automaticamente um novo arquivo com base em `.env.example` e o nomeará como `.env`. Caso contrário, você precisará alterar o nome do arquivo manualmente.

Seu arquivo `.env` não deve ser submetido ao sistema de gerenciamento de versão do código-fonte da aplicação, já que cada desenvolvedor/servidor que utiliza sua aplicação pode precisar ter uma configuração de ambiente diferente. Além disso, no caso de invasores obterem acesso ao seu repositório de código-fonte, isso pode levar a graves problemas de segurança, pois dados sensíveis ficam disponíveis à primeira vista.

> Todas as variáveis no arquivo `.env` podem ser sobrescritas por variáveis de ambiente externas (como variáveis de ambiente de nível de servidor, de sistema ou do Docker).

### Tipo de variável de ambiente

Todas as variáveis no arquivo `.env` são interpretadas como um tipo string, portanto alguns valores reservados são fornecidos para permitir que você obtenha mais tipos de variáveis a partir da função `env()`:

| Valor no .env | Valor de env() |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

Se você precisar usar variáveis de ambiente que contenham espaços, você pode fazer isso colocando os valores entre aspas duplas, como:

```dotenv
APP_NAME="Hyperf Skeleton"
```

### Obter variável de ambiente

Também mencionamos acima que a variável de ambiente pode ser obtida pela função `env()`. No desenvolvimento de aplicações, a variável de ambiente deve ser usada apenas como um valor da configuração, e o valor da variável de ambiente é usado para sobrescrever o valor configurado. **Use apenas a configuration**, em vez de usar variáveis de ambiente diretamente.
Vamos dar um exemplo razoável:

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## Centro de Configuração

O Hyperf fornece a você suporte de configuração externa para sistemas distribuídos; por padrão, oferecemos um projeto open source da Ctrip, o [ctripcorp/apollo](https://github.com/ctripcorp/apollo), com suporte funcional fornecido pelo componente [hyperf/config-apollo](https://github.com/hyperf/config-apollo).
Detalhes sobre o uso do Configuration Center são explicados no capítulo [Centro de Configuração](pt-br/config-center.md).

