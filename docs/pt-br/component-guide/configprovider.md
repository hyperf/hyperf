# Mecanismo de ConfigProvider

O mecanismo de ConfigProvider é um mecanismo muito importante para a componentização do Hyperf. `Desacoplamento entre componentes`, `independência de componentes` e `reutilização de componentes` são todos viabilizados com base nesse mecanismo.

# O que é o mecanismo ConfigProvider?

Simplificando: cada componente fornece um `ConfigProvider`. Normalmente, existe uma classe `ConfigProvider` no diretório raiz do componente, e o `ConfigProvider` fornece todas as informações de configuração do componente correspondente. Quando carregado durante a inicialização do framework Hyperf, a configuração final do `ConfigProvider` será mesclada na implementação correspondente de `Hyperf\\Contract\\ConfigInterface`, realizando a inicialização de configuração de cada componente quando usado no Hyperf.

O `ConfigProvider` em si não tem dependências, não herda classes abstratas e não exige a implementação de interfaces. Ele só precisa fornecer um método `__invoke` e retornar um array com a estrutura de configuração correspondente.

# Como definir um ConfigProvider?

Em geral, o `ConfigProvider` fica definido no diretório raiz do componente, e uma classe `ConfigProvider` costuma ser assim:

```php
<?php

namespace Hyperf\Foo;

class ConfigProvider
{
     public function __invoke(): array
     {
         return [
             // merged into config/autoload/dependencies.php file
             'dependencies' => [],
             // merged into config/autoload/annotations.php file
             'annotations' => [
                 'scan' => [
                     'paths' => [
                         __DIR__,
                     ],
                 ],
             ],
             // The definition of the default Command is merged into Hyperf\Contract\ConfigInterface, another way to understand it is corresponding to config/autoload/commands.php
             'commands' => [],
             // similar to commands
             'listeners' => [],
             // Component default configuration file, that is, after executing the command, the file corresponding to source will be copied to the file corresponding to destination
             'publish' => [
                 [
                     'id' => 'config',
                     'description' => 'description of this config file.', // description
                     // It is recommended that the default configuration be placed in the publish folder, and the file name is the same as the component name
                     'source' => __DIR__ . '/../publish/file.php', // corresponding configuration file path
                     'destination' => BASE_PATH . '/config/autoload/file.php', // copy as the file under this path
                 ],
             ],
             // You can also continue to define other configurations, which will eventually be merged into the configuration storage corresponding to ConfigInterface
         ];
     }
}
```

## Descrição do arquivo de configuração padrão

Depois de definir `publish` no `ConfigProvider`, você pode usar o comando a seguir para gerar rapidamente arquivos de configuração

```bash
php bin/hyperf.php vendor:publish package name
```

Se o nome do pacote for `hyperf/amqp`, você pode executar o comando para gerar o arquivo de configuração padrão do `amqp`
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Apenas criar a classe não fará com que ela seja carregada automaticamente pelo Hyperf. Você ainda precisa adicionar algumas definições no `composer.json` do componente para dizer ao Hyperf que esta é uma classe ConfigProvider que precisa ser carregada. Para isso, adicione a configuração `extra.hyperf.config` no `composer.json` do componente e especifique o namespace da classe `ConfigProvider` correspondente, como mostrado abaixo:

```json
{
     "name": "hyperf/foo",
     "require": {
         "php": ">=7.3"
     },
     "autoload": {
         "psr-4": {
             "Hyperf\\Foo\\": "src/"
         }
     },
     "extra": {
         "hyperf": {
             "config": "Hyperf\\Foo\\ConfigProvider"
         }
     }
}
```

Depois de definir, você precisa executar comandos como `composer install`, `composer update` ou `composer dump-autoload` para permitir que o Composer regenere o arquivo `composer.lock` e assim a configuração possa ser lida normalmente.

# Processo de execução do mecanismo ConfigProvider

A configuração do `ConfigProvider` não precisa necessariamente ser dividida dessa forma; trata-se de um formato acordado. Na prática, a decisão final de como interpretar essas configurações também fica a cargo do usuário. Você pode modificar o código no arquivo `config/container.php` do projeto Skeleton para ajustar o carregamento relacionado — isto é, o arquivo `config/container.php` determina a varredura e o carregamento do `ConfigProvider`.

# Especificação de design de componentes

Como o atributo `extra` em `composer.json` não tem outros efeitos quando esses dados não são usados, as definições nesses componentes não causarão interferência quando usadas por outros frameworks. Portanto, o `ConfigProvider` é um mecanismo que funciona apenas no framework Hyperf e não terá impacto em frameworks que não usam esse mecanismo. Isso estabelece a base para reutilização de componentes, mas também exige que o seguinte seja seguido ao projetar componentes:

- Todas as classes devem ser projetadas para permitir uso padrão via `OOP`, e todos os recursos específicos do Hyperf devem ser fornecidos como melhorias e em classes separadas; isso significa que elas ainda podem ser usadas em frameworks que não são Hyperf por meios padrão, viabilizando o uso dos componentes;
- Se o design de dependências do componente puder atender ao [padrão PSR](https://www.php-fig.org/psr), ele deve ser priorizado, dependendo da interface correspondente em vez da classe de implementação. Se o padrão PSR não contiver o que você precisa, você pode atender à interface definida pelo Hyperf na biblioteca de contratos [hyperf/contract](https://github.com/hyperf/contract), priorizando depender da interface correspondente e não da implementação;
- Para classes de funcionalidades adicionais que implementam recursos proprietários do Hyperf, em geral elas também dependem de alguns componentes do Hyperf; portanto, essas dependências não devem ser escritas em `require` no `composer.json`, mas sim sugeridas no item `suggest`;
- O design do componente não deve realizar injeção de dependência via annotations; o método de injeção deve usar apenas `constructor injection`, o que também atende ao uso via `OOP`;
- O design do componente não deve definir funções via annotations; definições de função devem ser feitas apenas via `ConfigProvider`;
- O design da classe deve evitar armazenar dados de estado, pois isso impede que a classe seja usada como um objeto de longo ciclo de vida e dificulta o uso de injeção de dependência, reduzindo desempenho. Dados de estado devem ser armazenados via contexto de corrotina `Hyperf\\Context\\Context`;
