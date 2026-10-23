# Watcher (Hot Reload)

Desde a versão `2.0`, usa-se o `BetterReflection` para coletar a `abstract syntax tree (AST)` e os dados de reflection, então a velocidade de scan é bem mais lenta do que na versão `1.1`.

> A primeira inicialização da aplicação será mais lenta porque não existe cache de scan. As inicializações subsequentes serão mais rápidas, mas como o `BetterReflection` precisa ser instanciado, o tempo de inicialização ainda é relativamente longo.


Além de resolver os problemas de inicialização mencionados acima, o componente `Watcher` também lida com a reinicialização imediata da aplicação após a modificação de arquivos.

> Este componente é adequado apenas para ambiente de desenvolvimento; use-o com cautela em ambiente de produção.

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

|      Nome      |     Padrão      |                                     Descrição                                      |
| :------------: | :--------------: | :----------------------------------------------------------------------------------: |
|     driver     | `ScanFileDriver` |                           O watcher de arquivos por polling padrão                           |
|      bin       |   `PHP_BINARY`   | O script usado para iniciar o serviço, por exemplo: `php -d swoole.use_shortname=Off` |
|   watch.dir    | `app`, `config`  |                                 Diretórios monitorados                                  |
|   watch.file   |      `.env`      |                                     Arquivos monitorados                                     |
| watch.interval |      `2000`      |                                Intervalo de polling (ms)                                 |
|      ext       |  `.php`, `.env`  |                      Extensão de arquivo no diretório monitorado                       |

## Suporte a drivers

|                Driver                 |               Observações               |
| :-----------------------------------: | :-------------------------------: |
| Hyperf\Watcher\Driver\ScanFileDriver  |       não requer extensão adicional       |
|  Hyperf\Watcher\Driver\FswatchDriver  |         requer fswatch          |
|   Hyperf\Watcher\Driver\FindDriver    | requer find; no MAC requer gfind |
| Hyperf\Watcher\Driver\FindNewerDriver |           requer find           |

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
Ao configurar um watcher de arquivos para hot-reload no Docker, especifique o entry point no Dockerfile da seguinte forma:

```bash
ENTRYPOINT ["php", "/opt/www/bin/hyperf.php", "server:watch"]
```

## Problemas

- Por enquanto, há um pequeno problema no ambiente Docker Alpine, que será melhorado em uma versão futura.
- A exclusão de arquivos e a modificação do `.env` requerem reinicialização manual para ter efeito.
