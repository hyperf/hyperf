# RPN - Reverse Polish Notation

![PHPUnit](https://github.com/hyperf/rpn-incubator/workflows/PHPUnit/badge.svg)

`RPN` adalah metode ekspresi matematika yang diperkenalkan oleh matematikawan
Polandia Jan Vukasevich pada tahun 1920. Dalam reverse Polish notation, semua
operator ditempatkan setelah operand, sehingga disebut juga dengan postfix
notation. Reverse Polish notation tidak memerlukan tanda kurung untuk
mengidentifikasi prioritas operator (operator precedence).

```
composer require hyperf/rpn
```

## Logika RPN

Logika dasar

- selagi (while) ada input
    - baca simbol berikutnya X
    - JIKA X adalah operand
        - masukkan ke stack (push)
    - JIKA X adalah operator
        - terdapat tabel a priori bahwa operator tersebut mengambil n argumen
        - JIKA operand di stack kurang dari n
            - (Error) Pengguna tidak memasukkan operand yang cukup
    - Selain itu, keluarkan (pop) n operand dari stack
    - Hitung operasi operator.
    - masukkan (push) nilai hasil perhitungan ke stack
- JIKA hanya ada satu nilai di stack
    - Nilai ini adalah hasil dari seluruh perhitungan
- JIKA LEBIH DARI satu nilai di stack
    - (Error) Pengguna memasukkan operand yang berlebihan

Contoh

Ekspresi infix `5 + ((1 + 2) * 4) - 3` ditulis sebagai

`5 1 2 + 4 * + 3 -`

Tabel berikut menunjukkan bagaimana ekspresi Reverse Polish ini dievaluasi dari
kiri ke kanan, dengan nilai perantara yang diberikan dalam kolom stack, yang
digunakan untuk melacak algoritma.

| input | action | stack | comment |
| ---- | -------- | ------- | ---------------------------- |
| 5 | Push | 5 | |
| 1 | Push | 5, 1 | |
| 2 | Push | 5, 1, 2 | |
| + | Addition | 5, 3 | Pop 1, 2, push result 3 |
| 4 | Push | 5, 3, 4 | |
| * | Multiplication | 5, 12 | Pop 3, 4, push result 12 |
| + | Add operation | 17 | Pop 5, 12, push result 17 |
| 3 | push | 17, 3 | |
| - | Subtraction | 14 | Pop 17, 3, push result 14 |

Ketika perhitungan selesai, hanya ada satu operand yang tersisa di stack, yang
merupakan hasil akhir dari ekspresi: 14

## Penggunaan

Evaluasi ekspresi RPN secara langsung

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

Mengatur presisi perhitungan

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

Mengatur variabel

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### Mengonversi ekspresi infix menjadi ekspresi postfix

> Penggunaan variabel sementara tidak didukung

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
