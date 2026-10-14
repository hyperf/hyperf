# Alibaba Cloud Log Service

Coletar logs pode ser um problema trabalhoso ao implantar um projeto em um `cluster Docker`, mas a Alibaba Cloud fornece um `sistema de coleta de logs` bem útil. Este documento apresenta brevemente como usar a coleta de logs da Alibaba Cloud.

* [Construção de cluster Docker Swarm](pt-br/tutorial/docker-swarm.md)

## Habilitar o serviço de logs

O primeiro passo é ativar o `Log Service` na Alibaba Cloud.

[Documentação do Log Service](https://help.aliyun.com/product/28958.html)

O tutorial a seguir é um guia sequencial e passo a passo de como usar o serviço de logs.

## Instalar o container Logtail

[Documento do processo padrão de coleta de logs do Docker](https://help.aliyun.com/document_detail/66659.html)

| Parâmetros | Descrição |
| :-----------------------------------: | :------------ -------------------------------: |
| ${your_region_name} | ID da região. Por exemplo, a região East China 1 é cn-hangzhou |
| ${your_aliyun_user_id} | ID do usuário. Substitua pelo user ID da conta principal da Alibaba Cloud. |
| ${your_machine_group_user_defined_id} | ID customizado do grupo de máquinas do cluster. O exemplo a seguir usa Hyperf |

````
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
````

## Configurar coleta de logs

### Criar Project

Faça login no Alibaba Cloud Log Service, clique em `Create Project` e preencha as informações a seguir:

| Parâmetros | Exemplo |
| :------------: | :------------------: |
| Project name | hyperf |
| Comments | Para demonstração do sistema de logs |
| Region | East China 1 (Hangzhou) |
| Activate service | Log detalhado |
| Log Storage Location | Project atual |

### Criar Logstore

Exceto pelos parâmetros a seguir, preencha conforme necessário; os demais podem usar o padrão.

| Parâmetros | Exemplo |
| :------------: | :-------------: |
| Logstore name | hyperf-demo-api |
| save permanently | false |
| Data retention time | 60 |

### Acessar dados

1. Selecione o arquivo Docker

2. Crie um grupo de máquinas

Se você já criou um grupo de máquinas, pode pular esta etapa.

| Parâmetros | Exemplo |
| :------------: | :------------: |
| Machine Group Name | Hyperf |
| Machine group ID | User-defined ID |
| User Defined Logo | Hyperf |

3. Configure o grupo de máquinas

Aplique o grupo de máquinas que você acabou de criar.

4. Configure o Logtail

Whitelist de `Label`. Aqui você pode preencher conforme necessário; a seguir a configuração é feita de acordo com o nome do projeto, e o nome do projeto será definido quando o container Docker estiver rodando.

| Parâmetros | Exemplo | Exemplo |
| :------------: | :-------------------------------- ----------------: | :-------------: |
| Configuration Name | hyperf-demo-api | |
| Log Path | /opt/www/runtime/logs | *.log |
| Label whitelist | app.name | hyperf-demo-api |
| Pattern | Full Regular Pattern | |
| single-line mode | false | |
| Sample log | `[2019-03-07 11:58:57] hyperf.WARNING: xxx` | |
| First line regular expression | `\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*` | |
| Extract fields | true | |
| Regular Expression | `\[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*)` | |
| Log extraction content | time name level content | |

5. Configuração de consulta/análise

field index property

| Field name | Type | Alias ​​| Chinese word segmentation | Open statistics |
| :------: | :---: | :-----: | :------: | :------: |
| name | text | name | false | true |
| level | text | level | false | true |
| time | text | time | false | false |
| content | text | content | true | false |

### Executar a imagem

Ao executar a imagem, tudo o que você precisa fazer é definir os `labels` do container.

| name | value |
| :------: | :-------------: |
| app.name | hyperf-demo-api |

Por exemplo, o Dockerfile a seguir:

```Dockerfile
# Dockerfile padrão

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

# Outro conteúdo omitido
````

## Precauções

- Limitação do driver de armazenamento do Docker: atualmente, apenas `overlay` e `overlay2` são suportados. Para outros drivers de armazenamento, você precisa fazer `mount` do diretório onde os logs estão localizados e então coletar os logs do host `~/logtail_host/your_path`.

