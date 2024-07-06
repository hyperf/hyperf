<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Rpn;

use Hyperf\Rpn\Calculator;
use Hyperf\Rpn\Exception\InvalidOperatorException;
use PHPUnit\Framework\Attributes\CoversNothing;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CalculatorTest extends AbstractTestCase
{
    public function testCalculateBasic()
    {
        $calculator = new Calculator();
        $result = $calculator->calculate('1 1 +');
        $this->assertSame('2', $result);

        $result = $calculator->calculate('10 1 -', [], 2);
        $this->assertSame('9.00', $result);

        $result = $calculator->calculate('10 1.5 *', [], 2);
        $this->assertSame('15.00', $result);

        $result = $calculator->calculate('10 3 /', [], 3);
        $this->assertSame('3.333', $result);
    }

    public function testCalculateComplex()
    {
        $calculator = new Calculator();
        $result = $calculator->calculate('1 1 + 5 *');
        $this->assertSame('10', $result);

        $result = $calculator->calculate('10 1 - 3 /', [], 2);
        $this->assertSame('3.00', $result);

        $result = $calculator->calculate('5 1 2 + 4 * + 3 -', []);
        $this->assertSame('14', $result);
    }

    public function testCalculateBindings()
    {
        $calculator = new Calculator();
        $result = $calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10]);
        $this->assertSame('7', $result);

        $result = $calculator->calculate('[0] 1 2 + 4 * + [1] -', [5, 10], 1);
        $this->assertSame('7.0', $result);
    }

    public function testInvalidOperator()
    {
        $this->expectException(InvalidOperatorException::class);

        new Calculator([new stdClass()]);
    }

    public function testToRPNExpression()
    {
        $calculator = new Calculator();
        $got = $calculator->toRPNExpression('(4-2)*5+5-10');
        $this->assertSame('4 2 - 5 * 5 + 10 -', $got);

        $got = $calculator->toRPNExpression('4 - 2 * ( 5 + 5 ) - 10');
        $this->assertSame('4 2 5 5 + * - 10 -', $got);

        $got = $calculator->toRPNExpression('4 * (-2)');
        $this->assertSame('4 -2 *', $got);

        $got = $calculator->toRPNExpression('4 * -2');
        $this->assertSame('4 -2 *', $got);

        $got = $calculator->toRPNExpression('4--2*(5+5)-10');
        $this->assertSame('4 -2 5 5 + * - 10 -', $got);

        $got = $calculator->toRPNExpression('12 -- 10 * 4.4 + 1');
        $this->assertSame('12 -10 4.4 * - 1 +', $got);

        $got = $calculator->toRPNExpression('1 + 2 + 3 * 4 * -5 - 6');
        $this->assertSame('1 2 + 3 4 -5 * * + 6 -', $got);
    }
}
