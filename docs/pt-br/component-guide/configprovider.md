# Mecanismo do ConfigProvider

O mecanismo do ConfigProvider é um mecanismo muito importante para a componentização do Hyperf. O `desacoplamento entre componentes`, a `independência dos componentes` e a `reutilização de componentes` são todos realizados com base nesse mecanismo.

# O que é o mecanismo do ConfigProvider?

Simplificando, cada componente fornecerá um `ConfigProvider`, geralmente uma classe `ConfigProvider` fornecida no diretório raiz do componente, e o `ConfigProvider` fornecerá todas as informações de configuração do componente correspondente, que serão carregadas quando o framework Hyperf for iniciado. As informações de configuração finais em `ConfigProvider` serão mescladas na classe de implementação correspondente de `Hyperf\Contract\ConfigInterface`, realizando assim a inicialização da configuração de cada componente quando usado sob o framework Hyperf.

O `ConfigProvider` em si não possui nenhuma dependência, não herda nenhuma classe abstrata e não requer a implementação de nenhuma interface. Ele só precisa fornecer um método `__invoke` e retornar um array com a estrutura de configuração correspondente.

# Como definir um ConfigProvider?

Geralmente falando, o `ConfigProvider` será definido no diretório raiz do componente, e uma classe `ConfigProvider` normalmente é assim:

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

Após definir `publish` no `ConfigProvider`, você pode usar o seguinte command para gerar rapidamente arquivos de configuração

```bash
php bin/hyperf.php vendor:publish package name
```

Se o nome do pacote for `hyperf/amqp`, você pode executar o command para gerar o arquivo de configuração padrão do `amqp`
```bash
php bin/hyperf.php vendor:publish hyperf/amqp
```

Apenas criar uma classe não fará com que ela seja automaticamente carregada pelo Hyperf, você ainda precisa adicionar algumas definições no `composer.json` do componente para informar ao Hyperf que essa é uma classe ConfigProvider que precisa ser carregada. Você precisa adicionar a configuração `extra.hyperf.config` no arquivo `composer.json` do componente, e especificar o namespace da classe `ConfigProvider` correspondente, como mostrado abaixo:

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

Após a definição, você precisa executar comandos como `composer install`, `composer update` ou `composer dump-autoload` para que o Composer regenere o arquivo `composer.lock` antes que ele possa ser lido normalmente.

# Processo de execução do mecanismo do ConfigProvider

A configuração do `ConfigProvider` não é necessariamente dividida dessa forma. Este é apenas um formato acordado. Na verdade, a decisão final sobre como analisar essas configurações também cabe ao usuário. O usuário pode modificar o código no arquivo `config/container.php` do projeto Skeleton para ajustar o carregamento relacionado, ou seja, o arquivo `config/container.php` determina a varredura (scan) e o carregamento do `ConfigProvider`.

# Especificação de design de componentes

Como o atributo `extra` no `composer.json` não tem outro efeito ou influência quando os dados não são usados, as definições nesses componentes não causarão nenhuma interferência ou influência quando usadas por outros frameworks, então o `ConfigProvider` é um mecanismo que funciona apenas no framework Hyperf, e não terá nenhum impacto em outros frameworks que não usam esse mecanismo, o que estabelece a base para a reutilização de componentes. Porém, isso também exige que as seguintes especificações sejam seguidas ao projetar componentes:

- Todas as classes devem ser projetadas para permitir o uso padrão de `OOP`, e todas as funcionalidades específicas do Hyperf devem ser fornecidas como melhorias e em classes separadas, o que significa que elas ainda podem ser usadas em frameworks que não sejam o Hyperf através de meios padrão para realizar o uso dos componentes;
- Se o design de dependências do componente puder atender ao [padrão PSR](https://www.php-fig.org/psr), isso deve ser priorizado, dependendo da interface correspondente em vez da classe de implementação; caso o [padrão PSR](https://www.php-fig.org/psr) não contenha a funcionalidade, então pode atender à interface na biblioteca de contratos [hyperf/contract](https://github.com/hyperf/contract) definida pelo Hyperf, o que é priorizado, dependendo da interface correspondente em vez da classe de implementação;
- Para as classes de funcionalidades aprimoradas adicionadas para implementar funcionalidades proprietárias do Hyperf, geralmente falando, elas também têm dependências em alguns componentes do Hyperf, então as dependências desses componentes não devem ser escritas no item `require` do `composer.json`, mas sim como uma sugestão no item `suggest`;
- O design de componentes não deve realizar nenhuma injeção de dependência através de annotations, e o método de injeção deve usar apenas `constructor injection`, o que também pode atender ao uso sob `OOP`;
- O design de componentes não deve definir nenhuma funcionalidade através de annotations, e as definições de funcionalidade devem ser definidas apenas através do `ConfigProvider`;
- O design da classe deve, tanto quanto possível, evitar armazenar dados de estado, porque isso fará com que a classe não possa ser fornecida como um objeto com um ciclo de vida longo, e a funcionalidade de injeção de dependência não pode ser usada facilmente, o que reduzirá a performance em certa medida. Os dados de estado devem ser armazenados através do contexto de coroutine `Hyperf\Context\Context`;