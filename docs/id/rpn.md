# RPN - Reverse Polish Notation

`RPN` adalah metode ekspresi matematika yang diperkenalkan oleh matematikawan Polandia Jan Łukasiewicz pada tahun 1920. Dalam reverse Polish notation, semua operator ditempatkan setelah operand, sehingga disebut juga postfix notation. Reverse Polish notation tidak memerlukan tanda kurung untuk mengidentifikasi prioritas operator.

```bash
composer require hyperf/rpn
```

## Logika RPN

Logika dasar:

- while ada input
    - Baca simbol berikutnya X
    - IF X adalah operand
        - Push ke stack
    - ELSE IF X adalah operator
        - Ada tabel prioritas yang memberikan bahwa operator membutuhkan n argumen
        - IF ada kurang dari n operand di stack
            - (Error) Pengguna tidak memasukkan operand yang cukup
        - Else, pop n operand dari stack
        - Hitung operator.
        - Push nilai yang telah dihitung ke stack
- IF hanya ada satu nilai di stack
    - Nilai ini adalah hasil dari seluruh ekspresi
- ELSE ada lebih dari satu nilai
    - (Error) Pengguna memasukkan operand tambahan

Contoh:

Ekspresi infix `5 + ((1 + 2) * 4) - 3` ditulis sebagai:

`5 1 2 + 4 * + 3 -`

Tabel berikut menunjukkan proses evaluasi dari ekspresi reverse Polish ini dari kiri ke kanan. Kolom stack menunjukkan nilai antara yang digunakan untuk melacak algoritma.

| Input | Operasi     | Stack      | Catatan                          |
| ----- | ----------- | ---------- | -------------------------------- |
| 5     | Push        | 5          |                                  |
| 1     | Push        | 5, 1       |                                  |
| 2     | Push        | 5, 1, 2    |                                  |
| +     | Penambahan  | 5, 3       | 1, 2 pop, push hasil 3           |
| 4     | Push        | 5, 3, 4    |                                  |
| *     | Perkalian   | 5, 12      | 3, 4 pop, push hasil 12          |
| +     | Penambahan  | 17         | 5, 12 pop, push hasil 17         |
| 3     | Push        | 17, 3      |                                  |
| -     | Pengurangan | 14         | 17, 3 pop, push hasil 14         |

Ketika perhitungan selesai, hanya ada satu operand di stack, yang merupakan hasil dari ekspresi: 14.

## Penggunaan

Hitung ekspresi RPN secara langsung:

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', []); // '14'
```

Atur presisi perhitungan:

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('5 1 2 + 4 * + 3 -', [], 2); // '14.00'
```

Atur variabel:

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]); // '7'
```

### Mengonversi Infix Expression ke Postfix Expression

> Variabel belum didukung

```php
<?php
use Hyperf\Rpn\Calculator;

$calculator = new Calculator();
$calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10'); // 4 2 5 5 + * - 10 -
```
