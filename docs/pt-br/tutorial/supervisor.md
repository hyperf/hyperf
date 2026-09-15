# Deploy da aplicação com Supervisor

[Supervisor](http://www.supervisord.org/) é uma ferramenta de gerenciamento de processos em sistemas `Linux/Unix`. Um ou mais processos podem ser facilmente monitorados, iniciados, parados e reiniciados. Nos processos gerenciados pelo [Supervisor](http://www.supervisord.org/), quando o processo é acidentalmente `Kill`ado, o [Supervisor](http://www.supervisord.org/) irá reiniciá-lo automaticamente. É muito conveniente para alcançar o objetivo de recuperação automática de processos, sem precisar escrever um script `shell` para gerenciar o processo.

## Instalação do Supervisor

Aqui está apenas um exemplo do método de instalação em sistemas `CentOS`:

```bash
# Install the epel source, if it has been installed before, skip this step
yum install -y epel-release
yum install -y supervisor  
```

## Criando um arquivo de configuração

```bash
cp /etc/supervisord.conf /etc/supervisord.d/supervisord.conf
```

Edite o arquivo de configuração recém-copiado `/etc/supervisord.d/supervisord.conf` e salve o arquivo após adicionar o seguinte ao final do arquivo:

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

## Iniciando o Supervisor

Execute o seguinte comando para iniciar o programa Supervisor com base no arquivo de configuração:

```bash
supervisord -c /etc/supervisord.d/supervisord.conf
```

## Usando o `supervisorctl` para gerenciar a application

```bash
# start the hyperf application
supervisorctl start hyperf
# restart hyperf application
supervisorctl restart hyperf
# stop hyperf application
supervisorctl stop hyperf
# View the running status of all managed projects
supervisorctl status
```
