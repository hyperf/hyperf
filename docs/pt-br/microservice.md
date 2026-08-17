# Microsserviços

Microsserviços são serviços pequenos e autônomos que trabalham em conjunto.

## Pequenos, com foco em fazer bem uma coisa

Com a evolução dos requisitos e o aumento de novas funcionalidades, o repositório de código tende a ficar cada vez maior. Embora desejemos muito alcançar uma modularidade clara em um repositório grande, na prática os limites entre módulos são difíceis de distinguir com clareza. Aos poucos, códigos com funcionalidades semelhantes começam a aparecer por toda parte no repositório. Com isso, fica muito difícil saber onde fazer alterações quando uma edição é atualizada, e torna-se cada vez mais difícil corrigir `Bug` e adicionar novas funcionalidades.
Em um sistema monolítico, normalmente criamos algumas camadas de abstração ou modularização para garantir a `cohesion` do código e, assim, evitar os problemas citados acima.

> Segundo o Robert C. Martin, no [Single Responsibility Principle](https://baike.baidu.com/item/单一职责原则/9456515): "* Coloque juntas as coisas que mudam pelo mesmo motivo e separe as coisas que mudam por motivos diferentes. *" Esse argumento enfatiza muito bem o conceito de `cohesion`.

Microsserviços aplicam esse conceito a serviços independentes e determinam os limites de cada serviço com base nos limites do negócio. Cada serviço foca nas coisas dentro do seu próprio limite. Ao fazer isso, podemos evitar muitos problemas decorrentes de um repositório de código grande demais.
O quão pequeno um microsserviço deve ser? Pequeno o suficiente, mas não pequeno demais.
Como avaliar se um sistema foi desmembrado em partes pequenas o suficiente? Quando você não tiver mais vontade de deixá-lo menor no conjunto do sistema, então provavelmente ele já está pequeno o suficiente. Quanto menores os serviços, mais evidentes ficam as vantagens e desvantagens de `Microservice`. Quanto menor o serviço usado, maiores os benefícios de independência; porém, o gerenciamento de um grande número de serviços também se torna mais complexo.

## Autonomia

Um microsserviço é uma entidade independente: pode ser implantado de forma independente e também pode existir como um processo do sistema operacional. Há isolamento entre serviços, e a comunicação ocorre pela rede, reforçando esse isolamento e evitando acoplamento forte. Os serviços devem poder ser modificados de forma independente, e o deploy de um determinado serviço não deve causar mudanças no `Service Consumer`. Isso exige pensar o quanto desses `Service Providers` deve ser exposto e o que deve ser ocultado. Se expusermos demais, o `Service Consumer` ficará acoplado à implementação interna dos provedores. Isso faz o serviço gerar trabalho adicional de coordenação, reduzindo sua autonomia.

## Principais benefícios

### Heterogeneidade tecnológica

Em um sistema no qual vários serviços cooperam entre si, é possível escolher, para cada serviço, a tecnologia mais adequada. Como os serviços são chamados via rede, a implementação do serviço não fica limitada pela linguagem de implementação ou pelo framework do sistema. Isso significa que, quando uma parte do sistema precisar de melhoria de performance, a implementação daquela parte pode ser reconstruída usando uma stack com melhor desempenho.

### Elasticidade

Um conceito-chave para construir um sistema elástico é o `Bulkhead`. Se um componente ou serviço do sistema ficar indisponível, mas isso não causar uma falha em cascata, as demais partes do sistema ainda poderão operar normalmente. O `service boundary` de um microsserviço é, obviamente, um `Bulkhead`. Em um sistema de `Monolithic architecture` — isto é, o sistema sob a arquitetura tradicional de `PHP-FPM` — se uma parte ficar indisponível, na maioria dos casos todas as funcionalidades ficam indisponíveis. Embora o sistema possa ser implantado em múltiplos nós com tecnologias como balanceamento de carga para reduzir a probabilidade de indisponibilidade total, em um sistema de `Microservice` a própria arquitetura pode lidar com indisponibilidade de serviços e com questões como degradação funcional.

### Expansibilidade

Um sistema de `monolithic architecture` só pode ser escalado como um todo, mesmo que apenas uma pequena parte do sistema tenha problemas de performance. Se você usar vários serviços menores, pode escalar apenas os serviços que precisam ser escalados, de modo que aqueles que não precisam possam rodar em servidores mais baratos, reduzindo custos.

### Deploy mais simples

Em um sistema de `monolithic architecture` com um volume enorme de código, mesmo que apenas uma linha seja modificada, o sistema inteiro precisa ser reimplantado para publicar a mudança. Esse tipo de deploy tem grande impacto e alto risco; por isso, as pessoas envolvidas raramente fazem deploys desse tipo. Como resultado, a frequência de deploy em operações reais fica muito baixa. Muitas funcionalidades ou `Bugfix` acabam sendo feitas entre versões, e um grande volume de mudanças é liberado de uma só vez em produção. Porém, quanto maior a diferença entre duas releases, maior a probabilidade de erros.
É claro que, no desenvolvimento sob a arquitetura tradicional de `PHP-FPM`, talvez não tenhamos esse problema, pois hot updates existem naturalmente. Porém, prós e contras coexistem.

### Alinhamento com a estrutura organizacional

No caso de `Monolithic architecture`, quando a estrutura do time também é 'distribuída' (remota), conflitos de código causados por muitos engenheiros enviando código e pela comunicação iterativa em locais diferentes tornam a manutenção do sistema mais complexa. Como sabemos, um time com um tamanho apropriado pode ter produtividade maior trabalhando em um repositório pequeno; portanto, a divisão em serviços consegue dividir bem as responsabilidades relacionadas.

### Composabilidade

O principal benefício atribuído a `Distributed System` e `Service Oriented Architecture (SOA)` é a facilidade de reutilizar funcionalidades existentes. Em `Microservice`, uma divisão de serviços mais fina (fine-grained) evidencia essa vantagem de forma ainda mais clara.

### Altamente reconfigurável

Se você está diante de um sistema grande de `monolithic architecture`, o código interno fica bagunçado e todo mundo tem medo de refatorar. Mas quando você lida com um serviço pequeno e mais granular, refatorar um serviço — ou até reescrever um serviço correspondente — é relativamente viável.
Em um sistema grande de `monolithic architecture`, você pode ter certeza de que não haverá problemas se centenas de linhas de código forem apagadas em um único dia? Mas com um bom `Microservice`, acredito que você consegue remover um serviço diretamente sem maiores problemas.

## Sem bala de prata

Embora os benefícios de `Microservices` sejam muitos, **`Microservice` não é uma bala de prata! ! !** Você precisa considerar a complexidade que todo sistema distribuído precisa considerar. Talvez você precise fazer bastante trabalho em deploy, testes, monitoramento, chamadas entre serviços e confiabilidade do serviço — e ainda lidar com questões semelhantes a transações distribuídas ou problemas relacionados ao CAP. Embora o `Hyperf` tenha resolvido muitos problemas para você, seu time precisa ter conhecimento suficiente sobre sistemas distribuídos antes de implementar `Microservice`, para conseguir lidar com problemas que talvez você nunca tenha enfrentado ou considerado.

*| Parte do conteúdo deste capítulo é referenciada de《Building Microservices》, de Sam Newman*
