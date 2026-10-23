# Tutorial de build Devops com DaoCloud

Como desenvolvedor individual, o custo de usar `Gitlab` autogerenciado e um `cluster Docker Swarm` é claramente inaceitável. Aqui está um serviço `Devops`, o `DaoCloud`.

O motivo da recomendação é simples: porque é gratuito e funciona bem.

[DaoCloud](https://dashboard.daocloud.io)

## Como usar

Você só precisa se atentar às três páginas de `project`, `application` e `cluster management`.

### Criando o projeto
Primeiro precisamos criar um novo projeto em `projects`. O DaoCloud suporta diversos tipos de repositórios de imagens, que podem ser selecionados conforme a necessidade.

Aqui eu uso o repositório [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) como exemplo de configuração. Quando a criação for concluída com sucesso, haverá uma URL correspondente em `WebHooks`, correspondente ao `repositório do Github`.

Em seguida, vamos modificar o `Dockerfile` no repositório e adicionar `&& apk add wget \` embaixo de `apk add`. O motivo exato aqui não está muito claro, mas se você não atualizar o `wget`, haverá problemas ao usá-lo. Porém não há problema com o Gitlab CI autogerenciado.

Quando o código for enviado (commit), o `DaoCloud` executará a operação de empacotamento correspondente.

### Criando o cluster

Depois vamos até `cluster management`, criamos um `cluster` e adicionamos os `hosts`.

Não vou entrar em detalhes aqui, basta seguir os passos acima.


### Criando a application

Clique em Apply -> Create Application -> Selecione o projeto recém-criado -> Deploy

Conforme as instruções, o usuário pode escolher uma porta não utilizada para a porta do host, porque o `DaoCloud` não possui a funcionalidade `Config` do `Swarm`, então mapeamos ativamente o `.env` para o container.

Adicione um `Volume`, com diretório do container `/opt/www/.env`, e diretório do host use o endereço onde você armazena o arquivo `.env`, seja ele gravável ou não.

Depois clique em Deploy Now.

### teste

Vá até o host para acessar o número da porta anterior, e você poderá ver os dados da interface de boas-vindas do `Hyperf`.

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```
