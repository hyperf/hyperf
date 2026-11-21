# RPN - Reverse Polish Notation

![PHPUnit](https://github.com/hyperf/rpn-incubator/workflows/PHPUnit/badge.svg)

`RPN` é um método de expressão matemática introduzido pelo matemático polonês Jan Vukasevich em 1920. Na notação polonesa inversa, todos os operadores são colocados depois do operando, por isso também é chamada de notação pós-fixada. A notação polonesa inversa não requer parênteses para identificar a precedência de operadores.

```
composer require hyperf/rpn
```

## Lógica do RPN

Lógica básica

- enquanto houver entrada
    - lê o próximo símbolo X
    - SE X é um operando
        - empilha
    - SENÃO SE X é um operador
        - existe uma tabela a priori de quantos argumentos n o operador recebe
        - SE houver menos de n operandos na pilha
            - (Erro) O usuário não informou operandos suficientes
    - Caso contrário, desempilha n operandos
    - Calcula o operador.
    - Empilha o valor calculado
- SE houver apenas um valor na pilha
    - Esse valor é o resultado de todo o cálculo
- SENÃO houver mais de um valor
    - (Erro) O usuário informou operandos redundantes

Exemplo

A expressão infixa `5 + ((1 + 2) * 4) - 3` é escrita como

`5 1 2 + 4 * + 3 -`

A tabela a seguir mostra como essa expressão em notação polonesa inversa é avaliada da esquerda para a direita, com os valores intermediários apresentados na coluna da pilha, usada para rastrear o algoritmo.

| entrada | ação | pilha | comentário |
| ---- | -------- | ------- | ---------------------------- |
| 5 | Empilha | 5 | |
| 1 | Empilha | 5, 1 | |
| 2 | Empilha | 5, 1, 2 | |
| + | Adição | 5, 3 | Desempilha 1, 2, empilha o resultado 3 |
| 4 | Empilha | 5, 3, 4 | |
| * | Multiplicação | 5, 12 | Desempilha 3, 4, empilha o resultado 12 |
| + | Adição | 17 | Desempilha 5, 12, empilha o resultado 17 |
| 3 | Empilha | 17, 3 | |
| - | Subtração | 14 | Desempilha 17, 3, empilha o resultado 14 |

Quando o cálculo é concluído, há apenas um operando na pilha, que é o resultado da expressão: 14

## Uso

Avaliar expressões RPN diretamente

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

Definir a precisão do cálculo

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

Definir variável

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### Converter expressões infixas em expressões pós-fixadas

> O uso de variáveis não é suportado temporariamente

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
