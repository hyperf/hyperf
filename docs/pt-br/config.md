# Configuração

Quando você está usando um projeto criado a partir de `hyperf/hyperf-skeleton`, todos os arquivos de configuração do Hyperf ficam na pasta `config` no diretório raiz, e cada opção contém instruções. Você pode sempre consultá-las para se familiarizar com as opções disponíveis.

# Instalação

```bash
composer require hyperf/config
```

# Estrutura dos arquivos de configuração

A estrutura abaixo é apenas a estrutura no caso da configuração padrão fornecida pelo Hyperf-Skeleton, e a situação real vai variar dependendo dos componentes dos quais o projeto depende ou que estão em uso.

```
config
├── autoload // O arquivo de configuração nesta pasta será carregado pelo próprio componente de configuração, e o nome do arquivo na pasta será o primeiro valor da chave.
│   ├── amqp.php  // Usado para gerenciar o componente AMQP
│   ├── annotations.php // Usado para gerenciar anotações
│   ├── apollo.php // Usado para gerenciar o Centro de Configuração Apollo
│   ├── aspects.php // Usado para gerenciar Aspectos do AOP
│   ├── async_queue.php // Usado para gerenciar o componente Async-Queue
│   ├── cache.php // Usado para gerenciar o componente de Cache
│   ├── commands.php // Usado para gerenciar Comandos Personalizados
│   ├── consul.php // Usado para gerenciar o Cliente Consul
│   ├── databases.php // Usado para gerenciar o Banco de Dados
│   ├── dependencies.php // Usado para gerenciar o relacionamento de dependências do DI
│   ├── devtool.php // Usado para gerenciar a Ferramenta de Desenvolvimento
│   ├── exceptions.php // Usado para gerenciar o Manipulador de Exceções
│   ├── listeners.php // Usado para gerenciar o Ouvinte de Eventos
│   ├── logger.php // Usado para gerenciar o Logger
│   ├── middlewares.php // Usado para gerenciar o Middleware
│   ├── opentracing.php // Usado para gerenciar o Open-Tracing
│   ├── processes.php // Usado para gerenciar Processos Personalizados
│   ├── redis.php // Usado para gerenciar o Cliente Redis
│   └── server.php // Usado para gerenciar o Servidor
├── config.php // Configuração para gerenciar usuários ou frameworks, como configurações relativamente independentes também podem ser colocadas na pasta autoload
├── container.php // Responsável pela inicialização do container, funcionando como um arquivo de configuração e eventualmente retornando um objeto Psr\Container\ContainerInterface
└── routes.php // Usado para gerenciar o Roteamento
```

## Relação entre `config.php` e os arquivos do diretório `autoload`

Os arquivos de configuração na pasta `autoload` e o `config.php` serão varridos e injetados no objeto correspondente a `Hyperf\Contract\ConfigInterface` quando o servidor iniciar. A estrutura de configuração é um grande array de pares chave/valor.

A diferença entre as duas formas de configuração é que o nome do arquivo dentro de `autoload` vira a chave de primeiro nível, enquanto o conteúdo de `config.php` é definido diretamente no primeiro nível. Usamos o exemplo abaixo para demonstrar.

Suponha que exista um arquivo `config/autoload/client.php` com o seguinte conteúdo:

```php
return [
    'request' => [
        'timeout' => 10,
    ],
];
```

Então queremos obter o valor de `timeout` cuja chave é `client.request.timeout`.

Se quisermos obter o mesmo resultado com a mesma chave, mas escrevendo a configuração no arquivo `config/config.php`, então o conteúdo do arquivo deveria ser assim:

```php
return [
    'client' => [
        'request' => [
            'timeout' => 10,
        ],
    ],
];
```

## Usar o componente Config do Hyperf

Este é o componente de configuração padrão oficial que implementa a interface `Hyperf\Contract\ConfigInterface`, definido pelo componente [hyperf/config](https://github.com/hyperf/config). O `Hyperf\Config\Config` é associado à interface via ConfigProvider do componente.

### Definir valor de configuração

As configurações em `config/config.php`, em `config/autoload/server.php` e nos arquivos da pasta `autoload` podem ser varridas e injetadas no objeto correspondente a `Hyperf\Contract\ConfigInterface` quando o servidor inicia. Esse processo é feito por `Hyperf\Config\ConfigFactory` quando o objeto Config é instanciado.

### Obter valor de configuração

O componente Config oferece três formas de obter o valor de configuração: por um objeto `Hyperf\Config\Config`, via anotação `#[Value]`, e via função `config(string $key, $default)`.

#### Obter valor de configuração via objeto Config

Este modo exige que você já tenha uma instância do objeto `Config`. O objeto padrão é `Hyperf\Config\Config`. Para detalhes sobre injeção, consulte o capítulo [Dependency Injection](pt-br/di.md).

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// Obtém a configuração correspondente a $key pelo método get(string $key, $default): mixed, o valor de $key pode apontar para o array subordinado pelo conector . (ponto), e $default é o valor padrão retornado quando o valor correspondente não existe.
$config->get($key, $default);
```

#### Obter configuração via anotação `#[Value]`

Este modo exige que o objeto seja criado pelo componente [hyperf/di](https://github.com/hyperf/di). Os detalhes podem ser encontrados no capítulo [Dependency Injection](pt-br/di.md). No exemplo, assumimos que `IndexController` é uma classe `Controller` já definida, e que a classe `Controller` deve ser criada pelo container `DI`.

A string em `#[Value()]` corresponde ao parâmetro `$key` em `$config->get($key)`. Quando a instância do objeto é criada, a configuração correspondente é injetada automaticamente na propriedade definida.

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

#### Obter configuração via função `config()`

A configuração correspondente pode ser obtida de qualquer lugar pela função `config(string $key, $default)`, mas esse modo implica que sua aplicação terá dependência forte dos componentes [hyperf/config](https://github.com/hyperf/config) e [hyperf/support](https://github.com/hyperf/support).

### Determinar se uma configuração existe

```php
/**
 * @var \Hyperf\Contract\ConfigInterface
 */
// The has(): bool method é usado para determinar se the corresponding $key value exists in the configuration, and the $key value can be mapped to the subordinate array by the . connector.
$config->has($key);
```

## Variáveis de ambiente

É uma necessidade comum usar configurações diferentes para diferentes ambientes de execução. Por exemplo: a configuração do Redis em ambiente de teste e em ambiente de produção é diferente, e a configuração de produção não pode ser enviada para o sistema de versionamento do código-fonte para evitar vazamento de informações.

No Hyperf, oferecemos uma solução com variáveis de ambiente usando a funcionalidade de parsing do [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) e a função `env()` para obter o ambiente. Isso torna a solução desse requisito bem simples.

Em uma aplicação Hyperf recém instalada, o diretório raiz contém um arquivo `.env.example`. No caso de instalação via Composer, o Composer copiará automaticamente um novo arquivo baseado em `.env.example` e o nomeará como `.env`. Caso contrário, você precisará alterar o nome do arquivo manualmente.

Seu arquivo `.env` não deve ser enviado para o sistema de versionamento do código-fonte da aplicação, pois cada desenvolvedor/servidor que usa sua aplicação pode precisar de configurações de ambiente diferentes. Além disso, no caso de invasores obterem acesso ao seu repositório de código, isso pode levar a problemas graves de segurança, pois dados sensíveis ficam visíveis rapidamente.

> Todas as variáveis no arquivo `.env` podem ser sobrescritas por variáveis de ambiente externas (como variáveis do servidor, do sistema ou do Docker).

### Tipos de variáveis de ambiente

Todas as variáveis no arquivo `.env` são interpretadas como string; por isso, existem alguns valores reservados que permitem obter mais tipos pela função `env()`:

| valor no .env | valor de env() |
| :------ | :----------- |
| true    | (bool) true  |
| (true)  | (bool) true  |
| false   | (bool) false |
| (false) | (bool) false |
| empty   | (string) ''  |
| (empty) | (string) ''  |
| null    | (null) null  |
| (null)  | (null) null  |

Se você precisar usar variáveis de ambiente que contenham espaços, você pode colocar os valores entre aspas duplas, por exemplo:

```dotenv
APP_NAME="Hyperf Skeleton"
```

### Obter variável de ambiente

Também mencionamos acima que a variável de ambiente pode ser obtida pela função `env()`. No desenvolvimento da aplicação, a variável de ambiente deve ser usada apenas como valor de configuração, e o valor da variável de ambiente é usado para sobrescrever o valor configurado. **Use apenas configuração**, em vez de usar variáveis de ambiente diretamente.

Vamos dar um exemplo:

```php
// config/config.php
return [
    'app_name' => env('APP_NAME', 'Hyperf Skeleton'),
];
```

## Configuration Center

O Hyperf fornece suporte a configurações externas para sistemas distribuídos. Por padrão, oferecemos suporte ao projeto open source da Ctrip chamado [ctripcorp/apollo](https://github.com/ctripcorp/apollo) via o componente [hyperf/config-apollo](https://github.com/hyperf/config-apollo).

Detalhes de uso do configuration center são explicados no capítulo [Configuration Center](pt-br/config-center.md).
