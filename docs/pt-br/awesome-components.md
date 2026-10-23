# Awesome Components

Todas as bibliotecas de componentes fornecidas oficialmente foram processadas com coroutine, sendo seguras para uso dentro do Hyperf ou de outros frameworks com coroutine. Com base na abertura e extensibilidade do Hyperf, a comunidade pode desenvolver ou adaptar uma variedade de componentes para isso; beneficiando-se disso, o Hyperf terá possibilidades ilimitadas.

Esta página incluirá uma variedade de componentes em coroutine compatíveis com o Hyperf e bibliotecas comumente usadas que foram validadas e usadas de forma segura em coroutine, para que você possa selecionar rapidamente os componentes certos para atender às suas necessidades.

## Como faço para enviar meu componente?

Se o componente que você desenvolveu for adaptado ao Hyperf, então você pode enviar diretamente um `Pull Request` na branch `master` do projeto [hyperf/hyperf](https://github.com/hyperf/hyperf), que é alterar a página atual `(pt-br/awesome-components.md)`.

## Como adaptar ao Hyperf?

Fornecemos a você um [guia de desenvolvimento de componentes Hyperf](pt-br/component-guide/intro) para ajudá-lo a desenvolver um componente Hyperf ou adaptar um componente ao framework Hyperf.

# Awesome Components

Todas as bibliotecas de componentes fornecidas oficialmente foram processadas com coroutine, sendo seguras para uso dentro do Hyperf ou de outros frameworks com coroutine. Com base na abertura e extensibilidade do Hyperf, a comunidade pode desenvolver ou adaptar uma variedade de componentes para isso; beneficiando-se disso, o Hyperf terá possibilidades ilimitadas.

Esta página incluirá uma variedade de componentes em coroutine compatíveis com o Hyperf e bibliotecas comumente usadas que foram validadas e usadas de forma segura em coroutine, para que você possa selecionar rapidamente os componentes certos para atender às suas necessidades.

## Como faço para enviar meu componente?

Se o componente que você desenvolveu for adaptado ao Hyperf, então você pode enviar diretamente um `Pull Request` na branch `master` do projeto [hyperf/hyperf](https://github.com/hyperf/hyperf), que é alterar a página atual `(pt-br/awesome-components.md)`.

## Como adaptar ao Hyperf?

Fornecemos a você um [guia de desenvolvimento de componentes Hyperf](pt-br/component-guide/intro) para ajudá-lo a desenvolver um componente Hyperf ou adaptar um componente ao framework Hyperf.

# Lista de componentes

## Rota

- [nikic/fastroute](https://github.com/nikic/FastRoute) um roteamento de alta velocidade comumente usado
- [lazychanger/urlrewrite](https://github.com/lazychanger/urlrewrite) Uma ferramenta de reescrita de URL baseada nas mesmas regras de roteamento do [nikic/fastroute](https://github.com/nikic/FastRoute), baseada em PSR-7

## Event

- [hyperf/event](https://github.com/hyperf/event) Gerenciador de Event baseado em PSR-14 fornecido oficialmente pelo Hyperf

## Log

- [hyperf/logger](https://github.com/hyperf/logger) Gerenciador de log baseado em PSR-3 fornecido oficialmente pelo Hyperf

## Command

- [hyperf/command](https://github.com/hyperf/command) Componente de gerenciamento de Command baseado na extensão [symfony/console](https://github.com/symfony/console) e com suporte a annotation, fornecido oficialmente pelo Hyperf
- [symfony/console](https://github.com/symfony/console) Componente independente de gerenciamento de Command fornecido pelo Symfony

## Database

- [hyperf/database](https://github.com/hyperf/database) Baseado no ORM de banco de dados Eloquent com fork feito pelo Hyperf; este componente pode ser reutilizado em outros frameworks
- [hyperf/model-cache](https://github.com/hyperf/model-cache) Componente de cache automático de model baseado no componente [hyperf/database](https://github.com/hyperf/database), fornecido oficialmente pelo Hyperf

## Container de injeção de dependência

- [hyperf/di](https://github.com/hyperf/di) Um container de Dependency Injection fornecido oficialmente pelo Hyperf, com suporte a annotations e AOP

## Server

- [hyperf/http-server](https://github.com/hyperf/http-server) O HTTP server fornecido oficialmente pelo Hyperf
- [hyperf/grpc-server](https://github.com/hyperf/grpc-server) O gRPC server fornecido oficialmente pelo Hyperf
- [hyperf/websocket-server](https://github.com/hyperf/websocket-server) O WebSocket server fornecido oficialmente pelo Hyperf
- [hyperf/rpc-server](https://github.com/hyperf/rpc-server) O RPC server abstrato fornecido oficialmente pelo Hyperf

## Client

- [hyperf/consul](https://github.com/hyperf/consul) O client Consul em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/elasticsearch](https://github.com/hyperf/elasticsearch) O client Elasticsearch em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/grpc-client](https://github.com/hyperf/grpc-client) O client gRPC em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/rpc-client](https://github.com/hyperf/rpc-client) O client RPC abstrato em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/guzzle](https://github.com/hyperf/guzzle) O client HTTP Guzzle em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/redis](https://github.com/hyperf/redis) O client Redis em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/websocket-client](https://github.com/hyperf/websocket-client) O client WebSocket em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/cache](https://github.com/hyperf/cache) Client de cache em coroutine baseado em PSR-16, fornecido oficialmente pelo Hyperf
- [friendsofhyperf/http-client](https://github.com/friendsofhyperf/http-client) O client HTTP Guzzle em coroutine baseado no Hyperf
- [friendsofhyperf/openai-client](https://github.com/friendsofhyperf/openai-client) O client OpenAI em coroutine baseado no Hyperf

## Testing

- [hyperf/testing](https://github.com/hyperf/testing) O componente oficial de testes unitários do Hyperf
- [friendsofhyperf/pest-plugin-hyperf](https://github.com/friendsofhyperf/pest-plugin-hyperf) Plugin do [Pest](https://pestphp.com/) desenvolvido especificamente para o Hyperf, fornecendo suporte a ambiente de coroutine para o Pest.

## Message queue

- [hyperf/amqp](https://github.com/hyperf/amqp) Componente AMQP em coroutine fornecido oficialmente pelo Hyperf
- [hyperf/async-queue](https://github.com/hyperf/async-queue) Componente de fila assíncrona baseado em Redis, fornecido oficialmente pelo Hyperf

## Centro de Configuração

- [hyperf/config-apollo](https://github.com/hyperf/config-apollo) Componente de Configuration Center Apollo fornecido oficialmente pelo Hyperf
- [hyperf/config-aliyun-acm](https://github.com/hyperf/config-aliyun-acm) Componente do serviço de configuração de aplicação Aliyun ACM fornecido oficialmente pelo Hyperf

## Service governance

- [hyperf/json-rpc](https://github.com/hyperf/json-rpc) Componente do protocolo JSON-RPC fornecido oficialmente pelo Hyperf
- [hyperf/rate-limit](https://github.com/hyperf/rate-limit) Componente de rate limiter baseado no algoritmo de token bucket, fornecido oficialmente pelo Hyperf
- [hyperf/load-balancer](https://github.com/hyperf/load-balancer) Componente de load balancer fornecido oficialmente pelo Hyperf
- [hyperf/service-governance](https://github.com/hyperf/service-governance) Componente de governança de serviços fornecido oficialmente pelo Hyperf
- [hyperf/tracer](https://github.com/hyperf/tracer) Componente OpenTracing fornecido oficialmente pelo Hyperf
- [hyperf/circuit-breaker](https://github.com/hyperf/circuit-breaker) Componente de circuit breaker de serviço fornecido oficialmente pelo Hyperf
- [friendsofhyperf/sentry](https://github.com/friendsofhyperf/sentry) Componente do [Sentry](https://sentry.io) baseado no Hyperf

## Configuração via Annotation

- [hyperf-helper/dependency](https://github.com/lazychanger/hyperf-helper-dependency) Use annotations para configurar dependências rapidamente e com suporte a prioridade de dependência.

## DTO

- [fatbit/form-request-param](https://github.com/duncanxia97/hyperf-form-request-param) - Um componente elegante, baseado em `DTO`, de validação de parâmetros de requisição fortemente tipados (validação de formulário) e injeção automática.


## Development and debugging

- [firstphp/wsdebug](https://github.com/lamplife/wsdebug) Componente de desenvolvimento e debug para observação em tempo real de erros anormais através de `WebSocket`
- [qbhy/hyperf-multi-env](https://github.com/qbhy/hyperf-multi-env) suporta a funcionalidade de arquivo de configuração multi-env semelhante ao Laravel, como `APP_ENV=testing`, que pode carregar `.env.testing` sobrescrevendo o `.env` padrão
- [qiutuleng/hyperf-dump-server](https://github.com/qiutuleng/hyperf-dump-server) fornece uma função de `dump` que pode imprimir variáveis ou dados do programa em outra janela de linha de comando, baseado no componente `Var-Dump Server` do Symfony
- [learvin/hyperf-tinker](https://github.com/Arvin-Lee/hyperf-tinker) Fornece um shell container interativo do Hyperf baseado no PsySH
- [friendsofhyperf/telescope](https://github.com/friendsofhyperf/telescope) Ferramenta de debug adaptada para o Hyperf
