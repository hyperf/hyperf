# RPN - Reverse Polish Notation

![PHPUnit](https://github.com/hyperf/rpn-incubator/workflows/PHPUnit/badge.svg)

`RPN` is a mathematical expression method introduced by Polish mathematician Jan Vukasevich in 1920. In reverse Polish notation, all operators are placed after the operand, so it is also called postfix notation. . Reverse Polish notation does not require parentheses to identify operator precedence.

```
composer require hyperf/rpn
```

## RPN logic

basic logic

- while with input
    - read in the next symbol X
    - IF X is an operand
        - push the stack
    - ELSE IF X is an operator
        - there is a table of a priori that the operator takes n arguments
        - IF less than n operands on stack
            - (Error) User did not enter enough operands
    - Else, pop n operands from stack
    - Computational operators.
    - push the calculated value onto the stack
- There is only one value in the IF stack
    - This value is the result of the entire calculation
- ELSE more than one value
    - (Error) User entered redundant operands

Example

The infix expression `5 + ((1 + 2) * 4) - 3` is written

`5 1 2 + 4 * + 3 -`

The following table shows how this Reverse Polish expression is evaluated from left to right, with the intermediate values ​​given in the stack bar, which is used to keep track of the algorithm.

| input | action | stack | comment |
| ---- | -------- | ------- | ---------------------------- |
| 5 | Push | 5 | |
| 1 | Push | 5, 1 | |
| 2 | Push | 5, 1, 2 | |
| + | Addition | 5, 3 | Pop 1, 2, push result 3 |
| 4 | Push | 5, 3, 4 | |
| * | Multiplication | 5, 12 | 3, 4 pop, push result 12 |
| + | Add operation | 17 | 5, 12 pop, push result 17 |
| 3 | push | 17, 3 | |
| - | Subtraction | 14 | 17, 3 pop, push result 14 |

When the calculation is complete, there is only one operand on the stack, which is the result of the expression: 14

## use

Evaluate RPN expressions directly

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

Set calculation precision

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

set variable

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### Convert infix expressions to postfix expressions

> The use of variables is temporarily not supported

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
