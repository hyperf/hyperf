# Prefácio do guia

Para ajudar os desenvolvedores a criar componentes para o Hyperf e construir um ecossistema em conjunto, fornecemos este guia para orientar o desenvolvimento de componentes. Antes de ler este guia, você precisa fazer uma revisão **abrangente** da documentação do Hyperf, especialmente os capítulos de [coroutine](pt-br/coroutine.md) e [Dependency Injection](pt-br/di.md). Se você não tiver um entendimento suficiente dos componentes básicos do Hyperf, isso pode causar erros durante o desenvolvimento.

# O objetivo do desenvolvimento de componentes

No desenvolvimento sob a arquitetura tradicional PHP-FPM, normalmente quando precisamos usar uma biblioteca de terceiros para resolver nossas necessidades, introduzimos diretamente a biblioteca correspondente via Composer. Porém, sob o Hyperf, devido às duas características de aplicação persistente` e `coroutine`, há algumas diferenças no ciclo de vida e no modo de execução da aplicação; portanto, nem toda `Library` pode ser usada diretamente no Hyperf. É claro que algumas `Library` bem projetadas também podem ser usadas diretamente. Depois de ler este guia, você saberá como identificar se determinadas `Library` podem ser usadas diretamente no projeto e como fazer ajustes quando não for possível.

# Preparações para desenvolvimento de componentes

A preparação de desenvolvimento mencionada aqui, além das condições básicas de operação do Hyperf, foca mais em como organizar a estrutura do código de forma mais conveniente para facilitar o desenvolvimento de componentes. Observe que os métodos a seguir podem não conseguir pular devido ao *soft link Issue* e não se aplicam ao ambiente de desenvolvimento em Windows para Docker.
Em termos de organização de código, recomendamos clonar dois projetos: o skeleton do [hyperf/hyperf-skeleton](https://github.com/hyperf/hyperf-skeleton) e a biblioteca de componentes do [hyperf/hyperf](https://github. com/hyperf/hyperf). Faça o seguinte e tenha a seguinte estrutura:

```bash
// Instale o skeleton e configure-o
composer create-project hyperf/hyperf-skeleton

// Clone o projeto de biblioteca de componentes do hyperf; lembre-se de substituir hyperf pelo seu ID do Github (isto é, clone o projeto que você fez fork)
git clone git@github.com:hyperf/hyperf.git
```

Ele terá a seguinte estrutura:

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

O objetivo disso é permitir que o projeto `hyperf-skeleton` referencie diretamente via `path`, de modo que o Composer possa carregar diretamente a biblioteca de componentes do Hyperf como dependência no diretório `vendor` do projeto `hyperf-skeleton`, apontando para os projetos na pasta `hyperf` como um diretório de dependências. Para isso, adicionamos um item `repositories` ao arquivo `composer.json` do `hyperf-skeleton`, como a seguir:

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

Em seguida, apague o arquivo `composer.lock` e a pasta `vendor` no projeto `hyperf-skeleton` e então execute `composer update` para atualizar as dependências novamente. O comando é:

```bash
cd hyperf-skeleton
rm -rf composer.lock && rm -rf vendor && composer update
```

Por fim, todas as pastas de projetos em `hyperf-skeleton/vendor/hyperf` serão conectadas à pasta `hyperf` por meio de `softlinks`. Podemos usar o comando `ls -l` para verificar se o `softlink (softlink)` foi estabelecido com sucesso:

```bash
cd vendor/hyperf/
ls -l
```

Quando virmos uma relação de conexão como a seguinte, isso significa que o `soft link (softlink)` foi estabelecido com sucesso:

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

Nesse ponto, podemos modificar diretamente os arquivos em `vendor/hyperf` no IDE, mas o que modificamos é o código em `hyperf`, de modo que no fim conseguimos modificar diretamente o projeto `hyperf`, fazer `commit` e então submeter um `Pull Request (PR)` para o trunk.

