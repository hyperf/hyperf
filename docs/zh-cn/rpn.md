# RPN - 逆波兰表示法

`RPN` 是一种是由波兰数学家扬·武卡谢维奇 1920 年引入的数学表达式方式，在逆波兰记法中，所有操作符置于操作数的后面，因此也被称为后缀表示法。逆波兰记法不需要括号来标识操作符的优先级。

```bash
composer require hyperf/rpn
```

## RPN 逻辑

基本逻辑

- while 有输入
    - 读入下一个符号 X
    - IF X 是一个操作数
        - 入栈
    - ELSE IF X 是一个操作符
        - 有一个先验的表格给出该操作符需要 n 个参数
        - IF 堆栈中少于 n 个操作数
            - （错误）用户没有输入足够的操作数
    - Else，n 个操作数出栈
    - 计算操作符。
    - 将计算所得的值入栈
- IF 栈内只有一个值
    - 这个值就是整个计算式的结果
- ELSE 多于一个值
    - （错误）用户输入了多余的操作数

实例

中缀表达式 `5 + ((1 + 2) * 4) - 3` 写作

`5 1 2 + 4 * + 3 -`

下表给出了该逆波兰表达式从左至右求值的过程，堆栈栏给出了中间值，用于跟踪算法。

| 输入 | 操作     | 堆栈    | 注释                       |
| ---- | -------- | ------- | -------------------------- |
| 5    | 入栈     | 5       |                            |
| 1    | 入栈     | 5, 1    |                            |
| 2    | 入栈     | 5, 1, 2 |                            |
| +    | 加法运算 | 5, 3    | 1, 2 出栈，将结果 3 入栈    |
| 4    | 入栈     | 5, 3, 4 |                            |
| *    | 乘法运算 | 5, 12   | 3, 4 出栈，将结果 12 入栈  |
| +    | 加法运算 | 17      | 5, 12 出栈，将结果 17 入栈 |
| 3    | 入栈     | 17, 3   |                            |
| -    | 减法运算 | 14      | 17, 3 出栈，将结果 14 入栈 |

计算完成时，栈内只有一个操作数，这就是表达式的结果：14

## 使用

直接计算 RPN 表达式

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

设置计算精度

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

设置变量

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### 中缀表达式转化为后缀表达式

> 暂时不支持使用变量

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
