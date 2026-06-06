# box, por Hyperf

O Box tem o compromisso de ajudar a melhorar a experiência de desenvolvimento de aplicações PHP (especialmente para Hyperf), gerenciando o ambiente PHP e dependências relacionadas, fornecendo a capacidade de empacotar aplicações PHP como programas binários, e também fornecendo serviços de proxy reverso para gerenciar e implantar aplicações Swoole/Swow.

## Esta ainda é uma versão experimental inicial, divirta-se ~

### Uso

#### Instalar o box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Certifique-se de que /usr/local/bin/box esteja no seu PATH, ou coloque o executável box em qualquer caminho que você deseje no PATH.
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Certifique-se de que /usr/local/bin/box esteja no seu PATH, ou coloque o executável box em qualquer caminho que você deseje no PATH.
```
##### Linux aarch64

No momento, não temos um runner AARCH64 no GitHub Actions, então não conseguimos construir a tempo o arquivo binário para a versão AARCH64.

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Certifique-se de que /usr/local/bin/box esteja no seu PATH, ou coloque o executável box em qualquer caminho que você deseje no PATH.
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// Put `box.exe` into any path in $PATH env that you want, and use `box.exe` instead of `box` when executing on Windows
```

#### Inicializar Github Access Token

O Box precisa de um Github Access Token para chamar a API do GitHub e obter as versões dos pacotes.

1. [Criar Github Access Token](https://github.com/settings/tokens/new); o escopo `workflow` precisa ser selecionado.
2. Execute `box config set github.access-token <Your Token>` para inicializar o token.
3. Divirta-se ~

#### Configurar o kernel do Box

Por padrão, o Box é suportado pelo kernel Swow, mas também fornecemos o kernel Swoole. Você pode alternar para o kernel Swoole com `box config set kernel swoole`, mas observe que o kernel Swoole suporta apenas PHP 8.1 e não oferece suporte ao recurso de construir binários e a sistemas Windows.

```bash
// set to Swow Kernel [default]
box config set kernel swow

// set to Swoole Kernel (NOT supported on Windows)
box config set kernel swoole
````

### Comandos

- `box get pkg@version` para instalar automaticamente o pacote remoto; `pkg` é o nome do pacote e `version` é a versão do pacote. `box get pkg` significa instalar a versão mais recente de `pkg`. Por exemplo: execute `box get php@8.1` para instalar o PHP 8.1; execute `box get composer` para instalar o binário mais recente do composer
- `box build-prepare` para preparar os comandos `build` e `build-self`
- `box build-self` para compilar o próprio binário `box`
- `box build <path>` para compilar uma aplicação Hyperf em um arquivo binário
- `box self-update` para atualizar o binário `box` para a versão mais recente
- `box config list` para exibir o arquivo de configuração
- `box config get <key>` para obter um valor pela chave no arquivo de configuração
- `box config set <key> <value>` para definir um valor pela chave no arquivo de configuração
- `box config unset <key>` para remover o valor de configuração pela chave
- `box config set-php-version <version>` para definir a versão atual do PHP do box; valores disponíveis: 8.0 | 8.1
- `box config get-php-version <version>` para obter a versão atual do PHP do box
- `box reverse-proxy -u <upsteamHost:upstreamPort>` para iniciar um servidor HTTP de proxy reverso para os servidores upstream
- `box php <argument>` para executar qualquer comando PHP via a versão atual do PHP do box
- `box composer <argument>` para executar qualquer comando do Composer via box; a versão do binário do composer depende do último comando `get composer` executado
- `box php-cs-fixer <argument>` para executar qualquer comando `php-cs-fixer` via box; a versão do binário depende do último comando `get php-cs-fixer` executado
- `box cs-fix <argument>` para executar `php-cs-fix fix` via box; a versão do binário depende do último comando `get php-cs-fixer` executado
- `box phpstan <argument>` para executar qualquer comando `phpstan` via box; a versão do binário depende do último comando `get phpstan` executado (desde o box v0.3.0)
- `box pint <argument>` para executar qualquer comando `pint` via box; a versão do binário depende do último comando `get pint` executado (desde o box v0.3.0)
- `box version` para exibir a versão atual do binário do box

### Sobre o Swow Skeleton

Se você quiser experimentar todos os recursos do Box, você precisa executá-lo com base no kernel Swow. Por isso, você precisa basear seu projeto em [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) para rodar o projeto. Você pode criar um projeto Swow skeleton baseado na versão Hyperf 3.0 RC executando `box composer create-project hyperf/swow-skeleton:dev-master`.
