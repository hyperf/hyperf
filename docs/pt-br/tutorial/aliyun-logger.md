# Alibaba Cloud Log Service

Coletar logs pode ser um problema incômodo ao fazer o deploy de um projeto em um `cluster Docker`, mas a Alibaba Cloud oferece um `sistema de coleta de logs` muito útil. Este documento apresenta brevemente como usar a coleta de logs da Alibaba Cloud.

* [Construção de cluster com Docker Swarm](pt-br/tutorial/docker-swarm.md)

## Ativando o log service

O primeiro passo é ativar o `Log Service` na Alibaba Cloud.

[Documentação do Log Service](https://help.aliyun.com/product/28958.html)

O tutorial a seguir é um guia sequencial, passo a passo, sobre como usar o log service.

## Instalando o container Logtail

[Documento do processo padrão de coleta de logs do Docker](https://help.aliyun.com/document_detail/66659.html)

| Parâmetros | Descrição |
| :-----------------------------------: | :------------ -------------------------------: |
| ${your_region_name} | ID da região. Por exemplo, a região East China 1 é cn-hangzhou |
| ${your_aliyun_user_id} | ID do usuário, substitua pelo ID do usuário da sua conta principal da Alibaba Cloud. |
| ${your_machine_group_user_defined_id} | O ID personalizado do grupo de máquinas do cluster. O exemplo a seguir usa Hyperf |

````
docker run -d -v /:/logtail_host:ro -v /var/run/docker.sock:/var/run/docker.sock \
--env ALIYUN_LOGTAIL_CONFIG=/etc/ilogtail/conf/${your_region_name}/ilogtail_config.json \
--env ALIYUN_LOGTAIL_USER_ID=${your_aliyun_user_id} \
--env ALIYUN_LOGTAIL_USER_DEFINED_ID=${your_machine_group_user_defined_id} \
registry.cn-hangzhou.aliyuncs.com/log-service/logtail
````

## Configurando a coleta de logs

### Criando o Project

Faça login no Alibaba Cloud Log Service, clique em `Create Project`, e preencha as seguintes informações

| Parâmetros | Exemplo de preenchimento |
| :------------: | :------------------: |
| Nome do project | hyperf |
| Comentários | Para demonstração do sistema de logs |
| Região | East China 1 (Hangzhou) |
| Ativar serviço | Log detalhado |
| Local de armazenamento do log | Project atual |

### Criando o Logstore

Exceto os parâmetros a seguir, preencha conforme necessário, os demais podem usar o padrão

| Parâmetros | Exemplo de preenchimento |
| :------------: | :-------------: |
| Nome do Logstore | hyperf-demo-api |
| salvar permanentemente | false |
| Tempo de retenção dos dados | 60 |

### Acessando os dados

1. Selecione o arquivo Docker

2. Crie um grupo de máquinas

Se você já criou um grupo de máquinas, pode pular esta etapa

| Parâmetros | Exemplo de preenchimento |
| :------------: | :------------: |
| Nome do grupo de máquinas | Hyperf |
| ID do grupo de máquinas | ID personalizado |
| Marca personalizada do usuário | Hyperf |

3. Configure o grupo de máquinas

Aplique o grupo de máquinas que você acabou de criar

4. Configure o Logtail

Whitelist de `Label`, aqui você pode preencher conforme necessário. O exemplo a seguir é configurado de acordo com o nome do projeto, e o nome do projeto será definido quando o container Docker estiver em execução.

| Parâmetros | Exemplo de preenchimento | Exemplo de preenchimento |
| :------------: | :-------------------------------- ----------------: | :-------------: |
| Nome da configuração | hyperf-demo-api | |
| Caminho do log | /opt/www/runtime/logs | *.log |
| Whitelist de label | app.name | hyperf-demo-api |
| Padrão | Padrão Regular Completo | |
| modo single-line | false | |
| Log de exemplo | `[2019-03-07 11:58:57] hyperf.WARNING: xxx` | |
| Expressão regular da primeira linha | `\[\d+-\d+-\d+\s\d+:\d+:\d+\]\s.*` | |
| Extrair campos | true | |
| Expressão regular | `\[(\d+-\d+-\d+\s\d+:\d+:\d+)\]\s(\w+)\.(\w+):(.*)` | |
| Conteúdo extraído do log | time name level content | |

5. Configuração de query e análise

Propriedades de índice de campos

| Nome do campo | Tipo | Alias | Segmentação de palavras em chinês | Abrir estatísticas |
| :------: | :---: | :-----: | :------: | :------: |
| name | text | name | false | true |
| level | text | level | false | true |
| time | text | time | false | false |
| content | text | content | true | false |

### Executando a imagem

Ao executar a imagem, tudo que você precisa fazer é definir as `labels` do container.

| nome | valor |
| :------: | :-------------: |
| app.name | hyperf-demo-api |

Por exemplo, o Dockerfile a seguir

```Dockerfile
# Dockerfile padrão

FROM hyperf/hyperf:7.4-alpine-v3.11-swoole
LABEL maintainer="Hyperf Developers <group@hyperf.io>" version="1.0" license="MIT" app.name="hyperf-demo-api"

#Demais conteúdos omitidos
````

## Precauções

- Limitação do driver de armazenamento Docker: atualmente, apenas `overlay` e `overlay2` são suportados. Para outros drivers de armazenamento, você precisa fazer o `mount` do diretório onde os logs estão localizados, e então coletar os logs a partir do host `~/logtail_host/your_path` em vez disso.