# box, pelo Hyperf

O Box tem o compromisso de ajudar a melhorar a experiência de programação de aplicações PHP, especialmente para o Hyperf, gerenciando o ambiente PHP e as dependências relacionadas, fornecendo a capacidade de empacotar aplicações PHP como programas binários, e também fornecendo serviços de reverse proxy para gerenciar e implantar aplicações Swoole/Swow.

## Esta ainda é uma versão experimental inicial, divirta-se ~

### Uso

#### Instalar o box

##### Mac

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_macos -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Linux x86_64

```bash
wget https://github.com/hyperf/box/releases/download/v0.5.5/box_x86_64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```
##### Linux aarch64

Atualmente, estamos com escassez de Github Actions Runner para AARCH64, então não conseguimos construir o arquivo bin da versão AARCH64 em tempo hábil.

```bash
wget https://github.com/hyperf/box/releases/download/v0.0.3/box_php8.1_aarch64_linux -O box
sudo mv ./box /usr/local/bin/box
sudo chmod 755 /usr/local/bin/box
// Make sure /usr/local/bin/box in your $PATH env, or put `box` into any path in $PATH env that you want
```

##### Windows

```powershell
curl -o box.exe https://github.com/hyperf/box/releases/download/v0.5.5/box_x64_windows.exe
// Put `box.exe` into any path in $PATH env that you want, and use `box.exe` instead of `box` when executing on Windows
```

#### Iniciar o Github Access Token

O Box precisa de um Github Access Token para requisitar a api do github, para recuperar as versões do pacote.

1. [Crie um Github Access Token](https://github.com/settings/tokens/new); o escopo `workflow` deve ser selecionado.
2. Execute `box config set github.access-token <Seu Token>` para inicializar o token.
3. Divirta-se ~

#### Configurando o Box Kernel

Por padrão, o Box é suportado pelo Swow Kernel, mas também fornecemos o Swoole Kernel; você pode alternar para o Swoole Kernel através de `box config set kernel swoole`, mas deve-se notar que o Swoole Kernel só suporta a versão PHP 8.1, e a funcionalidade de Build Binaries e os Sistemas Windows não são suportados.

```bash
// define para o Swow Kernel [padrão]
box config set kernel swow

// define para o Swoole Kernel (NÃO suportado no Windows)
box config set kernel swoole
````

### Comandos

- `box get pkg@version` para instalar automaticamente o pacote remotamente; `pkg` é o nome do pacote, e `version` é a versão do pacote; `box get pkg` significa instalar a versão mais recente do pkg; por exemplo, execute `box get php@8.1` para instalar o PHP 8.1, execute `box get composer` para instalar o bin mais recente do composer
- `box build-prepare` para se preparar para os comandos `build` e `build-self`
- `box build-self` para compilar o próprio bin do `box`
- `box build <path>` para compilar uma aplicação Hyperf em um arquivo binário
- `box self-update` para atualizar o bin do `box` para a versão mais recente
- `box config list` para exibir o arquivo de configuração
- `box config get <key>` para recuperar o valor pela key do arquivo de configuração
- `box config set <key> <value>` para definir o valor pela key no arquivo de configuração
- `box config unset <key>` para remover o valor de configuração pela key
- `box config set-php-version <version>` para definir a versão atual do PHP do box; valor disponível: 8.0 | 8.1
- `box config get-php-version <version>` para obter a versão atual do PHP do box
- `box reverse-proxy -u <upsteamHost:upstreamPort>` para iniciar um server HTTP de reverse proxy para os servers upstream
- `box php <argument>` para executar qualquer comando PHP através da versão atual do PHP do box
- `box composer <argument>` para executar qualquer comando Composer através do box; a versão do bin do composer depende do último comando `get composer` executado
- `box php-cs-fixer <argument>` para executar qualquer comando `php-cs-fixer` através do box; a versão do bin do composer depende do último comando `get php-cs-fixer` executado
- `box cs-fix <argument>` para executar o comando `php-cs-fix fix` através do box; a versão do bin do composer depende do último comando `get php-cs-fixer` executado
- `box phpstan <argument>` para executar qualquer comando `phpstan` através do box; a versão do bin do composer depende do último comando `get phpstan` executado, desde o box v0.3.0
- `box pint <argument>` para executar qualquer comando `pint` através do box; a versão do bin do composer depende do último comando `get pint` executado, desde o box v0.3.0
- `box version` para exibir a versão atual do bin do box

### Sobre o Swow Skeleton

Se você quiser experimentar todas as funcionalidades do Box, você precisa executá-lo com base no Swow Kernel, então você precisa basear seu projeto no [hyperf/swow-skeleton](https://github.com/hyperf/swow-skeleton) para executar seu projeto; você pode criar um projeto de esqueleto Swow baseado na versão Hyperf 3.0 RC através do comando `box composer create-project hyperf/swow-skeleton:dev-master`.
