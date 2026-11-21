# RPN - Notação Polonesa Reversa

![PHPUnit](https://github.com/hyperf/rpn-incubator/workflows/PHPUnit/badge.svg)

`RPN` é um método de expressão matemática introduzido pelo matemático polonês Jan Vukasevich em 1920. Na notação polonesa reversa, todos os operadores são colocados após os operandos, por isso ela também é chamada de notação pós-fixa. A notação polonesa reversa não exige parênteses para identificar a precedência de operadores.

```
composer require hyperf/rpn
```

## Lógica do RPN

Lógica básica

- enquanto houver entrada
    - leia o próximo símbolo X
    - SE X for um operando
        - empilhe na pilha
    - SENÃO SE X for um operador
        - existe uma tabela a priori que define que o operador recebe n argumentos
        - SE houver menos de n operandos na pilha
            - (Erro) o usuário não inseriu operandos suficientes
    - Senão, desempilhe n operandos da pilha
    - Calcule o operador
    - empilhe o valor calculado na pilha
- Se existir apenas um valor na pilha
    - Esse valor é o resultado de todo o cálculo
- SENÃO (mais de um valor)
    - (Erro) o usuário inseriu operandos redundantes

Exemplo

A expressão infixa `5 + ((1 + 2) * 4) - 3` é escrita como

`5 1 2 + 4 * + 3 -`

A tabela a seguir mostra como essa expressão em notação polonesa reversa é avaliada da esquerda para a direita, com os valores intermediários mostrados na barra de pilha, usada para acompanhar o algoritmo.

| entrada | ação | pilha | comentário |
| ---- | -------- | ------- | ---------------------------- |
| 5 | Empilhar | 5 | |
| 1 | Empilhar | 5, 1 | |
| 2 | Empilhar | 5, 1, 2 | |
| + | Adição | 5, 3 | Desempilha 1, 2, empilha o resultado 3 |
| 4 | Empilhar | 5, 3, 4 | |
| * | Multiplicação | 5, 12 | Desempilha 3, 4, empilha o resultado 12 |
| + | Adição | 17 | Desempilha 5, 12, empilha o resultado 17 |
| 3 | Empilhar | 17, 3 | |
| - | Subtração | 14 | Desempilha 17, 3, empilha o resultado 14 |

Quando o cálculo é concluído, existe apenas um operando na pilha, que é o resultado da expressão: 14

## Uso

Avaliar expressões RPN diretamente

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

Definir precisão do cálculo

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

### Converter expressões infixas em expressões pós-fixas

> O uso de variáveis não é suportado temporariamente

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
