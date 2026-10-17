# Microsserviço

Microsserviços são serviços pequenos e autônomos que trabalham em conjunto.

## Pequeno, focado em fazer bem uma única coisa

Com a iteração dos requisitos e o aumento de novas funcionalidades, o repositório de código tende a ficar cada vez maior. Embora desejemos fortemente alcançar uma modularização clara em um repositório de código enorme, na prática, as fronteiras entre os módulos são difíceis de distinguir claramente. Gradualmente, códigos com funcionalidades semelhantes passam a aparecer em todos os lugares do repositório. Como resultado, torna-se muito difícil saber onde fazer alterações quando há atualizações de versão, e cada vez mais difícil corrigir `Bug`s e adicionar novas funcionalidades.
Em um sistema monolítico, geralmente são criadas camadas de abstração ou modularização para garantir a `coesão` do código, evitando assim os problemas mencionados acima.

> Segundo Robert C. Martin, o [Princípio da Responsabilidade Única](https://baike.baidu.com/item/单一职责原则/9456515) diz: "*Agrupe as coisas que mudam pelo mesmo motivo e separe as coisas que mudam por motivos diferentes.*" Esse argumento enfatiza muito bem o conceito de `coesão`.

Microsserviços aplicam esse conceito a serviços independentes e determinam as fronteiras dos serviços com base nas fronteiras do negócio. Cada serviço foca nas coisas dentro dos seus próprios limites. Dessa forma, evitamos muitos dos problemas decorrentes de um repositório de código excessivamente grande.
Quão pequeno deve ser um microsserviço? Pequeno o suficiente, mas não pequeno demais.
Como avaliar se um sistema foi decomposto de forma suficientemente pequena? Quando você não sentir mais vontade de torná-lo menor dentro do sistema como um todo, então ele já está pequeno o suficiente. Quanto menores os serviços, mais evidentes ficam as vantagens e desvantagens dos `Microsserviços`. Quanto menor o serviço utilizado, maiores os benefícios da independência, mas o gerenciamento de um grande número de serviços também se torna mais complexo.

## Autonomia

Um microsserviço é uma entidade independente, pode ser implantado de forma independente e também pode existir como um processo de sistema operacional. Há isolamento entre os serviços, e a comunicação entre eles ocorre pela rede, fortalecendo esse isolamento e evitando o acoplamento excessivo. Os serviços devem poder ser modificados de forma independente, e a implantação de um determinado serviço não deve causar alterações no `Consumidor do Serviço`. Isso exige que consideremos o quanto desses `Provedores de Serviço` deve ser exposto e o que deve ser ocultado. Se for exposto demais, o `Consumidor do Serviço` ficará acoplado à implementação interna dos provedores. Isso fará com que o serviço gere trabalho adicional de coordenação diretamente, reduzindo assim a autonomia do serviço.

## Principais benefícios

### Heterogeneidade tecnológica

Em um sistema onde múltiplos serviços cooperam entre si, é possível escolher, para cada serviço, a tecnologia mais adequada. Como os serviços são chamados pela rede, a implementação do serviço não fica limitada pela linguagem de implementação ou pelo framework do sistema. Isso significa que quando uma parte do sistema precisa de melhoria de desempenho, a implementação dessa parte pode ser reconstruída usando uma stack de tecnologia com melhor desempenho.

### Elasticidade

Um conceito-chave para viabilizar um sistema elástico é o `Bulkhead` (compartimento estanque). Se um componente ou serviço do sistema fica indisponível, mas isso não causa uma falha em cascata, então as demais partes do sistema ainda podem operar normalmente. A `fronteira de serviço` de um microsserviço é claramente um `Bulkhead`. Em um sistema de `arquitetura monolítica`, ou seja, um sistema sob a arquitetura tradicional `PHP-FPM`, se uma determinada parte fica indisponível, então, na maioria dos casos, todas as funcionalidades ficam indisponíveis. Embora o sistema possa ser implantado em múltiplos nós através de tecnologias como balanceamento de carga para reduzir a probabilidade de indisponibilidade total, em um sistema de `Microsserviços`, a própria arquitetura consegue lidar com a indisponibilidade de serviços e questões como degradação funcional.

### Capacidade de expansão

Um sistema de `arquitetura monolítica` só pode ser expandido como um todo, mesmo que apenas uma pequena parte do sistema tenha problemas de desempenho. Se você usar múltiplos serviços menores, poderá expandir apenas os serviços que precisam ser expandidos, de modo que os serviços que não precisam ser expandidos possam ser executados em servidores mais baratos, economizando custos.

### Implantação simplificada

Em um sistema de `arquitetura monolítica` com uma grande quantidade de código, mesmo que apenas uma linha de código seja modificada, o sistema inteiro precisa ser reimplantado para publicar a alteração. Esse tipo de implantação tem um grande impacto e alto risco, então as pessoas responsáveis raramente fazem esse tipo de implantação. Por isso, a frequência de implantações nas operações reais se torna muito baixa. Muitas funcionalidades ou correções de `Bug` acabam se acumulando no sistema entre as versões, e uma grande quantidade de mudanças é lançada de uma só vez em produção. Mas quanto maior a diferença entre duas versões, maior a probabilidade de erros.
Claro que, no desenvolvimento sob a arquitetura tradicional `PHP-FPM`, talvez não tenhamos esse problema, pois as atualizações a quente existem naturalmente. No entanto, os prós e os contras existem ao mesmo tempo.

### Compatibilidade com a estrutura organizacional

No caso de uma `arquitetura monolítica` em que a estrutura da equipe também é 'distribuída' (remota), os conflitos de código causados por um grande número de submissões de código de engenheiros e a comunicação iterativa em locais diferentes tornarão o sistema mais complexo de manter. Como todos sabem, uma equipe de tamanho apropriado consegue maior produtividade trabalhando em um repositório pequeno, então a divisão em serviços consegue dividir bem as responsabilidades relacionadas.

### Composabilidade

O principal benefício apontado pelos `Sistemas Distribuídos` e pela `Arquitetura Orientada a Serviços (SOA)` é a facilidade de reutilizar funcionalidades existentes. Sob os `Microsserviços`, uma divisão de serviços mais granular refletirá essa vantagem de forma ainda mais evidente.

### Alta capacidade de reconfiguração

Se você está lidando com um grande sistema de `arquitetura monolítica`, com código interno desorganizado, e todos têm medo de refatorar. Mas quando você está lidando com um serviço pequeno e granular, refatorar um serviço, ou até mesmo reescrevê-lo por completo, é relativamente viável.
Em um grande sistema de `arquitetura monolítica`, você tem certeza de que não causará nenhum problema ao apagar centenas de linhas de código em um único dia? Mas com bons `Microsserviços`, acredito que você conseguiria apagar um serviço diretamente sem nenhum problema.

## Não existe bala de prata

Embora os benefícios dos `Microsserviços` sejam numerosos, **Microsserviço não é uma bala de prata! ! !**. Você precisa considerar toda a complexidade que qualquer sistema distribuído exige. Pode ser necessário um grande trabalho em implantação, testes, monitoramento, chamadas entre serviços e confiabilidade dos serviços, e você pode até precisar lidar com questões semelhantes a transações distribuídas ou relacionadas ao CAP. Embora o `Hyperf` tenha resolvido muitos problemas para você, sua equipe precisa ter conhecimento suficiente sobre sistemas distribuídos antes de implementar `Microsserviços`, para lidar com problemas que talvez você nunca tenha enfrentado ou considerado.

*| Parte do conteúdo deste capítulo foi retirada do livro "Building Microservices", de Sam Newman*
