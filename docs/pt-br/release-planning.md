# Planejamento de releases

## Ciclo de vida

| Versão   | Status                     | Fim do suporte principal | Fim do suporte a correções de segurança | Data de release (ou data estimada) |
|----------|----------------------------|--------------------------|----------------------------------------|------------------------------------|
| 3.1(LTS) | Suporte principal          | 2026-01-01               | 2027-01-01                             | 2023-12-01                         |
| 3.0      | Suporte a correções de segurança | 2023-11-30         | 2024-06-30                             | 2023-01-03                         |
| 2.2      | Descontinuada              | 2022-06-20               | 2023-11-30                             | 2021-07-19                         |
| 2.1      | Descontinuada              | 2021-06-30               | 2021-12-31                             | 2020-12-28                         |
| 2.0      | Descontinuada              | 2020-12-28               | 2021-06-30                             | 2020-06-22                         |
| 1.1      | Descontinuada              | 2020-06-23               | 2020-12-31                             | 2019-10-08                         |
| 1.0      | Descontinuada              | 2019-10-08               | 2019-12-31                             | 2019-06-20                         |

* Suporte principal inclui correções de BUG, correções de segurança, upgrade de funcionalidades e suporte a novas funcionalidades em ciclos regulares de iteração;
* Suporte a correções de segurança inclui apenas correções de problemas de segurança;
* Versões com status descontinuado (Deprecated) não terão mais mudanças de código. Faça upgrade para a versão mais recente seguindo o guia de upgrade o quanto antes para obter um suporte melhor;


## Ciclo de iteração de versões

O Hyperf adota um modelo de desenvolvimento ágil, com um plano de upgrade semanal e releases toda `segunda-feira (UTC/GMT+08:00)`, normalmente uma release de versão `z` (ou, às vezes, uma versão `y`). Para versões `x`, o plano de iteração e o prazo serão definidos de acordo com os resultados de pesquisa e necessidade reais.

Para as regras de versionamento adotadas pelo Hyperf, consulte o capítulo [Versões](pt-br/versions.md).
