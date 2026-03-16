# RPN - 逆波蘭表示法

`RPN` 是一種是由波蘭數學家揚·武卡謝維奇 1920 年引入的數學表示式方式，在逆波蘭記法中，所有運算子置於運算元的後面，因此也被稱為字尾表示法。逆波蘭記法不需要括號來標識運算子的優先順序。

```bash
composer require hyperf/rpn
```

## RPN 邏輯

基本邏輯

- while 有輸入
    - 讀入下一個符號 X
    - IF X 是一個運算元
        - 入棧
    - ELSE IF X 是一個運算子
        - 有一個先驗的表格給出該運算子需要 n 個引數
        - IF 堆疊中少於 n 個運算元
            - （錯誤）使用者沒有輸入足夠的運算元
    - Else，n 個操作數出棧
    - 計算運算子。
    - 將計算所得的值入棧
- IF 棧內只有一個值
    - 這個值就是整個計算式的結果
- ELSE 多於一個值
    - （錯誤）使用者輸入了多餘的運算元

例項

中綴表示式 `5 + ((1 + 2) * 4) - 3` 寫作

`5 1 2 + 4 * + 3 -`

下表給出了該逆波蘭表示式從左至右求值的過程，堆疊欄給出了中間值，用於跟蹤演算法。

| 輸入 | 操作     | 堆疊    | 註釋                       |
| ---- | -------- | ------- | -------------------------- |
| 5    | 入棧     | 5       |                            |
| 1    | 入棧     | 5, 1    |                            |
| 2    | 入棧     | 5, 1, 2 |                            |
| +    | 加法運算 | 5, 3    | 1, 2 出棧，將結果 3 入棧    |
| 4    | 入棧     | 5, 3, 4 |                            |
| *    | 乘法運算 | 5, 12   | 3, 4 出棧，將結果 12 入棧  |
| +    | 加法運算 | 17      | 5, 12 出棧，將結果 17 入棧 |
| 3    | 入棧     | 17, 3   |                            |
| -    | 減法運算 | 14      | 17, 3 出棧，將結果 14 入棧 |

計算完成時，棧內只有一個運算元，這就是表示式的結果：14

## 使用

直接計算 RPN 表示式

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

設定計算精度

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

設定變數

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### 中綴表示式轉化為字尾表示式

> 暫時不支援使用變數

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
