# Componentes incríveis

Todas as bibliotecas de componentes fornecidas oficialmente foram adaptadas para corrotinas, sendo seguras para uso no Hyperf ou em outros frameworks de corrotinas. Com base na abertura e extensibilidade do Hyperf, a comunidade pode desenvolver ou adaptar uma variedade de componentes para ele. Com isso, o Hyperf pode ter possibilidades ilimitadas.

Esta página inclui uma variedade de componentes compatíveis com Hyperf e bibliotecas comuns que já foram validadas e usadas com segurança em corrotinas, para que você possa selecionar rapidamente os componentes certos para atender às suas necessidades.

## Como eu envio meu componente?

Se o componente que você desenvolveu é adaptado para o Hyperf, você pode enviar um `Pull Request` diretamente na branch `master` do projeto [hyperf/hyperf](https://github.com/hyperf/hyperf), alterando a página atual `(pt-br/awesome-components.md)`.

## Como adaptar para Hyperf?

Nós fornecemos um [guia de desenvolvimento de componentes do Hyperf](pt-br/component-guide/intro) para ajudar você a desenvolver componentes para o Hyperf ou adaptar componentes para o framework Hyperf.

# Componentes incríveis

Todas as bibliotecas de componentes fornecidas oficialmente foram adaptadas para corrotinas, sendo seguras para uso no Hyperf ou em outros frameworks de corrotinas. Com base na abertura e extensibilidade do Hyperf, a comunidade pode desenvolver ou adaptar uma variedade de componentes para ele. Com isso, o Hyperf pode ter possibilidades ilimitadas.

Esta página inclui uma variedade de componentes compatíveis com Hyperf e bibliotecas comuns que já foram validadas e usadas com segurança em corrotinas, para que você possa selecionar rapidamente os componentes certos para atender às suas necessidades.

## Como eu envio meu componente?

Se o componente que você desenvolveu é adaptado para o Hyperf, você pode enviar um `Pull Request` diretamente na branch `master` do projeto [hyperf/hyperf](https://github.com/hyperf/hyperf), alterando a página atual `(pt-br/awesome-components.md)`.

## Como adaptar para Hyperf?

Nós fornecemos um [guia de desenvolvimento de componentes do Hyperf](pt-br/component-guide/intro) para ajudar você a desenvolver componentes para o Hyperf ou adaptar componentes para o framework Hyperf.

# Lista de componentes

## Roteamento

- [nikic/fastroute](https://github.com/nikic/FastRoute) um roteador de alta velocidade bastante usado
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) uma ferramenta de reescrita de URL baseada nas mesmas regras de roteamento do [nikic/fastroute](https://github.com/nikic/FastRoute), baseada em PSR-7

## Evento

- [hyperf/event](https://github.com/hyperf/event) gerenciador de eventos baseado em PSR-14 fornecido oficialmente pelo Hyperf

## Log

- [hyperf/logger](https://github.com/hyperf/logger) gerenciador de logs baseado em PSR-3 fornecido oficialmente pelo Hyperf

## Command

- [hyperf/command](https://github.com/hyperf/command) componente de gerenciamento de comandos baseado em [symfony/console](https://github.com/symfony/console), com suporte a extensão e anotações, fornecido oficialmente pelo Hyperf
- [symfony/console](https://github.com/symfony/console) componente independente de gerenciamento de comandos fornecido pelo Symfony

## Banco de dados

- [hyperf/database](https://github.com/hyperf/database) baseado no ORM Eloquent (forkado pelo Hyperf); este componente pode ser reutilizado em outros frameworks
- [hyperf/model-cache](https://github.com/hyperf/model-cache) componente oficial de cache automático de models baseado no [hyperf/database](https://github.com/hyperf/database)

## Container de injeção de dependências

- [hyperf/di](https://github.com/hyperf/di) container de injeção de dependências fornecido oficialmente pelo Hyperf, com suporte a anotações e AOP

## Servidor

- [hyperf/http-server](https://github.com/hyperf/http-server) servidor HTTP fornecido oficialmente pelo Hyperf
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) servidor gRPC fornecido oficialmente pelo Hyperf
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) servidor WebSocket fornecido oficialmente pelo Hyperf
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) servidor RPC abstrato fornecido oficialmente pelo Hyperf

## Client

- [hyperf/consul](https://github.com/hyperf/consul) client Consul com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) client Elasticsearch com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) client gRPC com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) client RPC abstrato com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/guzzle](https://github.com/hyperf/guzzle) client HTTP Guzzle com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/redis](https://github.com/hyperf/redis) client Redis com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) client WebSocket com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/cache](https://github.com/hyperf/cache) client de cache com suporte a corrotinas baseado em PSR-16 fornecido oficialmente pelo Hyperf
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) client HTTP Guzzle com suporte a corrotinas baseado em Hyperf
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) client OpenAI com suporte a corrotinas baseado em Hyperf

## Testes

- [hyperf/testing](https://github.com/hyperf/testing) componente oficial de testes unitários do Hyperf
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) plugin do [Pest](https://pestphp.com/) projetado especificamente para Hyperf, fornecendo suporte a ambiente de corrotinas para o Pest

## Fila de mensagens

- [hyperf/amqp](https://github.com/hyperf/amqp) componente AMQP com suporte a corrotinas fornecido oficialmente pelo Hyperf
- [hyperf/async-queue](https://github.com/hyperf/async-queue) componente de fila assíncrona baseada em Redis fornecido oficialmente pelo Hyperf

## Configuration center

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) componente de Configuration Center Apollo fornecido oficialmente pelo Hyperf
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) componente do serviço Aliyun ACM fornecido oficialmente pelo Hyperf

## Governança de serviços

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) componente do protocolo JSON-RPC fornecido oficialmente pelo Hyperf
- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) limitador baseado no algoritmo de token bucket fornecido oficialmente pelo Hyperf
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) componente de balanceamento de carga fornecido oficialmente pelo Hyperf
- [hyperf/service-governance](https://github.com/hyperf/service-governance) componente de governança de serviços fornecido oficialmente pelo Hyperf
- [hyperf/tracer](https://github.com/hyperf/tracer) componente OpenTracing fornecido oficialmente pelo Hyperf
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) componente de circuit breaker de serviço fornecido oficialmente pelo Hyperf
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) componente [Sentry](https://sentry.io) baseado em Hyperf

## Configuração por annotation

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) usa anotações para configurar dependências rapidamente e suporta prioridade de dependências

## DTO

- [fatbit/form-request-param](https://github.com/duncanxia97/hyperf-form-request-param) componente elegante de validação e injeção automática de parâmetros de requisição baseado em `DTO` e fortemente tipado

## Desenvolvimento e depuração

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) componente de desenvolvimento e depuração para observar erros anormais em tempo real via `WebSocket`
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) suporta múltiplos arquivos de configuração por ambiente, similar ao Laravel; por exemplo, `APP_ENV=testing` pode carregar `.env .testing` para sobrescrever o `.env` padrão
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) fornece uma função `dump` que pode imprimir variáveis/dados do programa em outra janela de terminal, baseado no componente `Var-Dump Server` do Symfony
- [learvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) fornece um shell interativo do Hyperf baseado em PsySH
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) ferramentas de depuração adaptadas ao Hyperf
