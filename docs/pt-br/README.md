# Introdução

Hyperf é um framework PHP CLI extremamente performático e flexível, impulsionado por um servidor de corrotinas de última geração e por uma grande quantidade de componentes testados em produção. Além de superar com folga frameworks baseados em PHP-FPM em benchmarks, o Hyperf se destaca pelo foco em flexibilidade e composição. O Hyperf inclui um injetor de dependências com suporte a AOP (programação orientada a aspectos) para garantir que componentes e classes sejam plugáveis e meta-programáveis. Todos os componentes centrais do Hyperf seguem estritamente os padrões [PSR](https://www.php-fig.org/psr) e podem ser usados em outros frameworks.

A arquitetura do Hyperf é construída a partir de uma combinação de `Corrotinas`, `Injeção de dependência`, `Eventos`, `Anotações` e `AOP`. Além de fornecer `MySQL`, `Redis` e outros clientes de corrotina comuns, o `Hyperf` também fornece versões compatíveis com corrotinas de `servidor/cliente WebSocket`, `servidor/cliente JSON RPC`, `servidor/cliente gRPC`, `cliente Zipkin/Jaeger (OpenTracing)`, `cliente HTTP Guzzle`, `cliente Elasticsearch`, `cliente Consul`, `cliente ETCD`, `componente AMQP`, `central de configuração Apollo`, `Aliyun ACM`, `central de configuração ETCD`, `limitador baseado no algoritmo token bucket`, `pool de conexões universal`, `circuit breaker`, `Swagger`, `Snowflake`, `Simply Redis MQ`, `RabbitMQ`, `NSQ`, `Nats`, `crontab em nível de segundos`, `Processos personalizados`, etc. Assim, os desenvolvedores podem evitar totalmente implementar versões compatíveis com corrotinas dessas bibliotecas.

Fique tranquilo: o Hyperf ainda é um framework PHP. O Hyperf oferece todos os pacotes que você espera: `Middleware`, `Gerenciador de eventos`, `Eloquent ORM otimizado para corrotinas` (e Model Cache!), `Tradução`, `Validação`, `Motor de views (Blade/Smarty/Twig/Plates/ThinkTemplate)` e muito mais.

# Origem

Embora existam muitos frameworks PHP novos, ainda não encontramos um framework que una um design elegante a um desempenho ultra alto, nem um framework que abra caminho para microsserviços em PHP. Com essa visão em mente, continuaremos investindo no futuro deste framework, e você é muito bem-vindo para se juntar a nós contribuindo com o desenvolvimento open source do Hyperf.

# Objetivos de design

`Hyperspeed + Flexibility = Hyperf`. A equação escondida no nosso nome revela a ambição que deu origem ao Hyperf.

Hipervelocidade: aproveitando as corrotinas do `Swoole` e do `Swow`, o Hyperf é capaz de lidar com um volume massivo de tráfego. A equipe do Hyperf fez muitas otimizações no framework para eliminar cada gargalo entre o usuário final e o nosso motor extremamente rápido.

Flexibilidade: acreditamos que nosso componente de injeção de dependências é o melhor da categoria. Com a ajuda do `Hyperf DI`, componentes e classes são totalmente plugáveis e meta-programáveis. Em contrapartida, todos os componentes do Hyperf foram feitos para serem compartilhados com o mundo. Nosso compromisso com os padrões PSR significa que você pode usar componentes do Hyperf em qualquer framework compatível.

Com essas características, o Hyperf explorou um potencial ainda pouco aproveitado em muitos campos: implementação de servidores Web, servidores de gateway, software de middleware distribuído, arquitetura de microsserviços, servidores de jogos e Internet das Coisas (IoT).

# Pronto para produção

Além da nossa documentação multilíngue e bem mantida, uma grande quantidade de testes unitários para cada componente garante a correção lógica. Antes de o `Hyperf` ser lançado ao público (2019-06-20), ele já era usado de forma privada por algumas empresas de Internet de médio e grande porte em múltiplos serviços, que vêm rodando sem incidentes por anos em ambientes de produção rigorosos.

