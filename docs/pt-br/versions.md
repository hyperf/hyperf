# Versões

## Regras de versão

O Hyperf usa a regra de numeração `x.y.z` para nomear cada versão, por exemplo 1.2.3, onde 1 é `x`, 2 é `y` e 3 é `z`. Você pode definir seu plano de atualização do framework Hyperf de acordo com essas regras.

- `x` indica uma versão principal (major). Quando o core do Hyperf passa por muitas mudanças de refatoração, ou quando há muitas mudanças destrutivas de API, isso será lançado como uma versão `x`. Em geral, mudanças de uma versão `x` não podem ser consideradas compatíveis com a versão `x` anterior, mas isso não significa necessariamente incompatibilidade total. A identificação específica deve ser feita com base no guia de upgrade da versão correspondente.
- `y` representa uma versão iterativa de uma grande funcionalidade. Quando algumas APIs públicas passam por mudanças destrutivas, incluindo alterações ou remoções de APIs públicas, o que pode tornar a versão anterior incompatível, isso será lançado como uma versão `y`.
- `z` significa uma versão de correção totalmente compatível. Quando correções de bugs ou correções de segurança são feitas nas funcionalidades existentes de cada componente, uma versão `z` será escolhida para release. Quando um BUG torna uma funcionalidade completamente inutilizável, também pode acontecer de, ao corrigir esse BUG em uma versão `z`, serem feitas mudanças destrutivas na API; porém, como a funcionalidade estava completamente indisponível antes, essas mudanças não serão lançadas como uma versão `y`. Além de correções de bugs, uma versão `z` também pode incluir algumas novas funcionalidades ou componentes; essas funcionalidades e componentes não afetarão o uso do código anterior.

## Upgrade

Quando você quiser fazer upgrade da versão do Hyperf, se for um upgrade para as versões `x` ou `y`, siga o guia de upgrade da versão correspondente na documentação. Se você quiser fazer upgrade para uma versão `z`, pode executar diretamente o comando `composer update hyperf` no diretório raiz do seu projeto para atualizar os pacotes dependentes. Não recomendamos fazer upgrade da versão de um componente específico de forma isolada; em vez disso, faça upgrade de todos os componentes em conjunto para ter uma experiência de desenvolvimento mais consistente.
