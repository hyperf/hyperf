# RPN - Reverse Polish Notation

`RPN` is a mathematical expression method introduced by the Polish mathematician Jan Łukasiewicz in 1920. In reverse Polish notation, all operators are placed after the operands, so it is also called postfix notation. Reverse Polish notation does not require parentheses to identify the priority of operators.

```bash
composer require hyperf/rpn
```

## RPN Logic

Basic logic:

- while there is input
    - Read the next symbol X
    - IF X is an operand
        - Push onto stack
    - ELSE IF X is an operator
        - There is a prior table giving that the operator requires n arguments
        - IF there are fewer than n operands on the stack
            - (Error) User did not enter enough operands
        - Else, pop n operands from the stack
        - Calculate the operator.
        - Push the calculated value onto the stack
- IF there is only one value in the stack
    - This value is the result of the entire expression
- ELSE there is more than one value
    - (Error) User entered extra operands

Example:

The infix expression `5 + ((1 + 2) * 4) - 3` is written as:

`5 1 2 + 4 * + 3 -`

The following table shows the evaluation process of this reverse Polish expression from left to right. The stack column shows intermediate values used to track the algorithm.

| Input | Operation | Stack | Notes |
| ---- | -------- | ------- | -------------------------- |
| 5 | Push | 5 | |
| 1 | Push | 5, 1 | |
| 2 | Push | 5, 1, 2 | |
| + | Addition | 5, 3 | 1, 2 pop, push result 3 |
| 4 | Push | 5, 3, 4 | |
| * | Multiplication | 5, 12 | 3, 4 pop, push result 12 |
| + | Addition | 17 | 5, 12 pop, push result 17 |
| 3 | Push | 17, 3 | |
| - | Subtraction | 14 | 17, 3 pop, push result 14 |

When the calculation is completed, there is only one operand in the stack, which is the result of the expression: 14.

## Usage

Calculate RPN expression directly:

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

Set calculation precision:

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

Set variables:

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### Convert Infix Expression to Postfix Expression

> Variables are not supported yet

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
