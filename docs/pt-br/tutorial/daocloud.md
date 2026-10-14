# Tutorial de build DevOps com DaoCloud

Como desenvolvedor individual, o custo de usar um `Gitlab` e um `cluster Docker Swarm` auto-hospedados é claramente inviável. Aqui apresentamos o serviço de `DevOps` `DaoCloud`.

O motivo da recomendação é simples: é gratuito e funciona bem.

[DaoCloud](https://dashboard.daocloud.io)

## Como usar

Você só precisa prestar atenção em três páginas: `project`, `application` e `cluster management`.

### Criar projeto
Primeiro, precisamos criar um novo projeto em `projects`. O DaoCloud suporta vários repositórios de imagem, que podem ser escolhidos conforme necessário.

Aqui vou usar o repositório [hyperf-demo](https://github.com/limingxinleo/hyperf-demo) como exemplo para configurar. Quando a criação for bem-sucedida, haverá uma URL correspondente em `WebHooks` do `Github repository`.

Em seguida, vamos modificar o `Dockerfile` no repositório e adicionar `&& apk add wget \` abaixo de `apk add`. O motivo específico não é muito claro; se você não atualizar o `wget`, podem ocorrer problemas ao usar. Porém, em um GitLab CI auto-hospedado não há esse problema.

Quando o código for enviado, o `DaoCloud` realizará a operação de build correspondente.

### Criar cluster

Depois, vamos em `cluster management`, criamos um `cluster` e adicionamos `hosts`.

Não vou entrar em detalhes aqui — basta seguir os passos exibidos.


### Criar aplicação

Clique em Apply -> Create Application -> selecione o projeto criado -> Deploy

De acordo com as instruções, você pode escolher uma porta não utilizada no host. Como o `DaoCloud` não tem a função `Config` do `Swarm`, fazemos o mapeamento do `.env` para o container manualmente.

Adicione um `Volume`: no container, o diretório `/opt/www/.env`; no host, use o caminho onde você armazenou o arquivo `.env` (com permissão de escrita ou não).

Então clique em Deploy Now.

### Teste

Vá até o host e acesse a porta escolhida; você verá os dados de boas-vindas do `Hyperf`.

```
$ curl http://127.0.0.1:9501
{"code":0,"data":{"user":"Hyperf","method":"GET","message":"Hello Hyperf."}}
```

