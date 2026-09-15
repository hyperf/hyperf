# Prefácio do guia

Para ajudar os desenvolvedores a desenvolverem melhor componentes para o Hyperf e construir um ecossistema em conjunto, fornecemos este guia para orientar os desenvolvedores no desenvolvimento de componentes. Antes de ler este guia, você precisa ter feito uma leitura **completa** da documentação do Hyperf, especialmente os capítulos de [coroutine](pt-br/coroutine.md) e [Dependency Injection](pt-br/di.md). Se você não tiver um entendimento suficiente dos componentes básicos do Hyperf, isso pode causar erros durante o desenvolvimento.

# O propósito do desenvolvimento de componentes

No desenvolvimento sob a arquitetura tradicional PHP-FPM, geralmente quando precisamos usar uma biblioteca de terceiros para resolver nossas necessidades, introduzimos diretamente a biblioteca correspondente via Composer. No entanto, no Hyperf, devido às duas características de `application persistente` e `coroutine`, existem algumas diferenças no ciclo de vida e no modo da application, então nem toda `Library` pode ser usada diretamente no Hyperf. É claro que algumas `Library`s bem projetadas também podem ser usadas diretamente. Após ler este guia, você saberá como identificar se determinadas `Library`s podem ser usadas diretamente no projeto, e como fazer as alterações necessárias caso não possam.

# Preparação para o desenvolvimento de componentes

A preparação de desenvolvimento aqui referida, além das condições básicas de operação do Hyperf, foca mais em como organizar a estrutura do código de forma mais conveniente para facilitar o desenvolvimento de componentes. Observe que o método a seguir pode não funcionar corretamente devido ao *problema de soft link* e não se aplica ao ambiente de desenvolvimento Windows com Docker.
Em termos de organização do código, recomendamos clonar os dois projetos [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) (o esqueleto do projeto) e [hyperf/hyperf](https://github.com/hyperf/hyperf) (a biblioteca de componentes do projeto). Faça o seguinte para obter a estrutura abaixo:

```bash
// Install the skeleton and configure it
composer create-project hyperf/hyperf-skeleton

// Clone the hyperf component library project, remember to replace hyperf with your Github ID, that is, clone the project you forked
git clone git@github.com:hyperf/hyperf.git
```

Terá a seguinte estrutura:

```
.
├── hyperf
│ ├── bin
│ └── src
└── hyperf-skeleton
     ├── app
     ├── bin
     ├──config
     ├── runtime
     ├── test
     └── vendor
```

O objetivo disso é permitir que o projeto `hyperf-skeleton` obtenha o código-fonte diretamente através de referência de `path`, de forma que o Composer possa carregar diretamente o projeto na pasta `hyperf` como dependência no diretório `vendor` do projeto `hyperf-skeleton`. Adicionamos um item `repositories` ao arquivo `composer.json` em `hyperf-skeleton`, como segue:

```json
{
     "repositories": {
         "hyperf": {
             "type": "path",
             "url": "../hyperf/src/*"
         }
     }
}
```
Em seguida, delete o arquivo `composer.lock` e a pasta `vendor` no projeto `hyperf-skeleton`, e execute `composer update` para atualizar as dependências novamente. O comando é o seguinte:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Por fim, todas as pastas de projeto na pasta `hyperf-skeleton/vendor/hyperf` são conectadas à pasta `hyperf` através de `softlinks`. Podemos usar o comando `ls -l` para verificar se o `soft link (softlink)` foi estabelecido com sucesso:

```bash
cd vendor/hyperf/
ls -l
```

Quando vemos uma relação de conexão como a seguinte, significa que o `soft link (softlink)` foi estabelecido com sucesso:

```
cache -> ../../../hyperf/src/cache
command -> ../../../hyperf/src/command
config -> ../../../hyperf/src/config
contract -> ../../../hyperf/src/contract
database -> ../../../hyperf/src/database
db-connection -> ../../../hyperf/src/db-connection
devtool -> ../../../hyperf/src/devtool
di -> ../../../hyperf/src/di
dispatcher -> ../../../hyperf/src/dispatcher
event -> ../../../hyperf/src/event
exception-handler -> ../../../hyperf/src/exception-handler
framework -> ../../../hyperf/src/framework
guzzle -> ../../../hyperf/src/guzzle
http-message -> ../../../hyperf/src/http-message
http-server -> ../../../hyperf/src/http-server
logger -> ../../../hyperf/src/logger
memory -> ../../../hyperf/src/memory
paginator -> ../../../hyperf/src/paginator
pool -> ../../../hyperf/src/pool
process -> ../../../hyperf/src/process
redis -> ../../../hyperf/src/redis
server -> ../../../hyperf/src/server
testing -> ../../../hyperf/src/testing
support -> ../../../hyperf/src/support
```

Neste ponto, podemos modificar diretamente os arquivos em `vendor/hyperf` na IDE, mas o que estamos modificando é o código em `hyperf`, para que possamos, ao final, fazer o `commit` diretamente no projeto `hyperf`, e então enviar um `Pull Request (PR)` para o trunk.