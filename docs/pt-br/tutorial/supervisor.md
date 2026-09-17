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
# Cria uma nova aplicação e define um nome, aqui está definido como hyperf
[program:hyperf]
# Aqui está o comando de inicialização do projeto que você deseja gerenciar, correspondendo ao caminho real do seu projeto
command=php /var/www/hyperf/bin/hyperf.php start
# Qual usuário executará o processo
user=root
# inicia automaticamente a aplicação quando o supervisor inicia
autostart=true
# Reinicia automaticamente o processo após a saída do processo
autorestart=true
# intervalo de retentativa em segundos
startsecs=5
# número de retentativas
startretries=3
# local de saída do log stderr
stderr_logfile=/var/www/hyperf/runtime/stderr.log
# local de saída do log stdout
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
