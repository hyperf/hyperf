# Watcher (Recarregamento automático)

Desde a versão `2.0`, o Hyperf usa `BetterReflection` para coletar a `abstract syntax tree (AST)` e os `reflection data`, então a velocidade de varredura é bem mais lenta do que na versão `1.1`.

> A primeira inicialização da aplicação será mais lenta porque não existe cache de varredura. As inicializações seguintes serão mais rápidas, mas como o `BetterReflection` precisa ser instanciado, o tempo de inicialização ainda é relativamente alto.

Além de resolver os problemas de inicialização acima, o componente `Watcher` também cuida de reiniciar a aplicação imediatamente após modificações em arquivos.

> Este componente é indicado apenas para ambiente de desenvolvimento; use com cautela em produção.

## Instalação

```bash
composer require hyperf/watcher --dev
```

## Configuração

### Publicar configuração

```bash
php bin/hyperf.php vendor:publish hyperf/watcher
```

### Instruções de configuração

|      Nome      |      Padrão      |                                     Descrição                                      |
| :------------: | :--------------: | :---------------------------------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                         O watcher padrão por polling de arquivos                    |
|      bin       |   `PHP_BINARY`   | Script usado para iniciar o serviço, por exemplo: `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                                Diretórios monitorados                               |
|   watch.file   |      `.env`      |                                   Arquivos monitorados                              |
| watch.interval |      `2000`      |                                Intervalo de polling (ms)                            |
|      ext       |  `.php`, `.env`  |                       Extensões de arquivo nos diretórios monitorados               |

## Suporte de drivers

|                Driver                 |               Observações               |
| :-----------------------------------: | :-------------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |       não requer extensão               |
|  Hyperf\Watcher\Driver\FswatchDriver  |         requer fswatch                 |
|   Hyperf\Watcher\Driver\FindDriver    | requer find; no macOS requer gfind     |
| Hyperf\Watcher\Driver\FindNewerDriver |           requer find                  |

### Instalação do `fswatch`

Mac:

```bash
brew install fswatch
```

Ubuntu/Debian

```bash
apt-get install fswatch
```

Linux:

```bash
wget https://github.com/emcrisostomo/fswatch/releases/download/1.14.0/fswatch-1.14.0.tar.gz \
&& tar -xf fswatch-1.14.0.tar.gz \
&& cd fswatch-1.14.0/ \
&& ./configure \
&& make \
&& make install
```

## Inicialização

Devido à estrutura de diretórios, o comando de inicialização precisa ser executado no diretório raiz do projeto.

```bash
php bin/hyperf.php server:watch
```

## Inicialização com docker

Ao configurar um file watcher para hot-reload no Docker, especifique o entrypoint no Dockerfile conforme abaixo:

```bash
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "server:watch"]
```

## Problemas

- Por enquanto, há um pequeno problema no ambiente Alpine Docker; isso será melhorado em versões futuras.
- A exclusão de arquivos e modificações no `.env` exigem um reinício manual para surtirem efeito.

