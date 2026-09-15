# Introdução

Hyperf é um framework PHP CLI extremamente performático e flexível, potencializado por um servidor de coroutines de última geração e um grande número de componentes testados em produção. Além de superar de forma decisiva os frameworks baseados em PHP-FPM em benchmarks, o Hyperf é único em seu foco em flexibilidade e composição. O Hyperf vem com um injetor de dependências habilitado para AOP (programação orientada a aspectos) para garantir que componentes e classes sejam plugáveis e meta-programáveis. Todos os componentes principais do Hyperf seguem estritamente os padrões [PSR](https://www.php-fig.org/psr) e podem ser usados em outros frameworks.

A arquitetura do Hyperf é construída usando uma combinação de `Coroutines`, `Dependency Injection`, `Events`, `Annotations` e `AOP`. Além de fornecer clientes coroutine para `MySQL`, `Redis` e outros comuns, o `Hyperf` também fornece versões compatíveis com coroutines de `servidor/cliente WebSocket`, `servidor/cliente JSON RPC`, `servidor/cliente gRPC`, `cliente Zipkin/Jaeger (OpenTracing)`, `cliente HTTP Guzzle`, `cliente Elasticsearch`, `cliente Consul`, `cliente ETCD`, `componente AMQP`, `centro de configuração Apollo`, `Aliyun ACM`, `centro de configuração ETCD`, `limitador baseado no algoritmo de token bucket`, `pool de conexões universal`, `circuit breaker`, `Swagger`, `Snowflake`, `MQ Redis simplificado`, `RabbitMQ`, `NSQ`, `Nats`, `crontab com granularidade de segundos`, `processos personalizados`, entre outros. Assim, os desenvolvedores podem evitar completamente a implementação de versões compatíveis com coroutines dessas bibliotecas.

Fique tranquilo, o Hyperf ainda é um framework PHP. O Hyperf fornece todos os pacotes que você espera: `Middleware`, `Event Manager`, `ORM Eloquent otimizado para coroutines` (e Model Cache!), `Translation`, `Validation`, `motor de views (Blade/Smarty/Twig/Plates/ThinkTemplate)` e muito mais.

# Origem

Embora existam muitos frameworks PHP novos, ainda não encontramos um framework que combine um design elegante com performance ultra alta, nem encontramos um framework que abra caminho para microsserviços em PHP. Com essa visão em mente, continuaremos investindo no futuro deste framework, e você é bem-vindo para se juntar a nós na contribuição para o desenvolvimento open-source do Hyperf.

# Objetivos de Design

`Hyperspeed + Flexibility = Hyperf`. A equação escondida em nosso nome exibe a ambição fundadora do Hyperf.

Hyperspeed: aproveitando as coroutines do `Swoole` e do `Swow`, o Hyperf é capaz de lidar com enormes volumes de tráfego. A equipe do Hyperf fez diversas otimizações no framework para eliminar todos os gargalos entre o usuário final e nosso motor extremamente rápido.

Flexibility: acreditamos que nosso componente de Dependency Injection é o melhor da categoria. Com a ajuda do `Hyperf DI`, componentes e classes são todos plugáveis e meta-programáveis. Inversamente, todos os componentes do Hyperf são pensados para serem compartilhados com o mundo. Nosso compromisso com os padrões PSR significa que você pode usar os componentes do Hyperf em qualquer framework compatível.

Por meio dessas características, o Hyperf descobriu potencial inexplorado em muitos campos: implementação de servidores Web, servidores gateway, softwares de middleware distribuído, arquitetura de microsserviços, servidores de jogos e Internet das Coisas (IoT).

# Pronto para produção

Junto com nossa documentação multilíngue bem mantida, um grande número de testes unitários para cada componente garante a correção lógica. Antes de o `Hyperf` ser lançado publicamente (2019-06-20), ele já vinha sendo utilizado de forma privada por algumas empresas de internet de médio e grande porte em diversos serviços, que vêm funcionando sem incidentes por anos em ambientes de produção adversos.