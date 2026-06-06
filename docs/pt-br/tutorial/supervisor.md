# Implantar aplicação com Supervisor

[Supervisor](http://www.supervisord.org/) é uma ferramenta de gerenciamento de processos em sistemas `Linux/Unix`. Um ou mais processos podem ser facilmente monitorados, iniciados, parados e reiniciados. Quando um processo gerenciado pelo [Supervisor](http://www.supervisord.org/) é `Kill` acidentalmente, o [Supervisor](http://www.supervisord.org/) o reinicia automaticamente. Isso torna bem fácil atingir o objetivo de recuperação automática do processo sem precisar escrever um script `shell` para gerenciar o processo.

## Instalar o Supervisor

A seguir está apenas um exemplo de instalação no sistema `CentOS`:

```bash
# Instale o repositório epel; se já estiver instalado, pule esta etapa
yum install -y epel-release
yum install -y supervisor  
```

## Criar um arquivo de configuração

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edite o arquivo recém-copiado `/etc/supervisord.d/supervisord.conf` e salve após adicionar o seguinte no final do arquivo:

```ini
# Create a new application and set a name, here is set to hyperf
[program:hyperf]
# Here is the startup command of the project you want to manage, corresponding to the real path of your project
command=php /var/www/hyperf/bin/hyperf.php start
# Which user to run the process as
user=root
# automatically the app when supervisor starts
autostart=true
# Automatically restart the process after the process exits
autorestart=true
# retry interval in seconds
startsecs=5
# number of retries
startretries=3
# stderr log output location
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# stdout log output location
stdout_logfile=/var/www/hyperf/runtime/stdout.log
```

## Iniciar o Supervisor

Execute o comando a seguir para iniciar o Supervisor com base no arquivo de configuração:

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## Usar `supervisorctl` para gerenciar a aplicação

```bash
# iniciar a aplicação hyperf
supervisorctl start hyperf
# reiniciar a aplicação hyperf
supervisorctl restart hyperf
# parar a aplicação hyperf
supervisorctl stop hyperf
# Ver o status de execução de todos os projetos gerenciados
supervisorctl status
```
