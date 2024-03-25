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

namespace HyperfTest\Crontab;

use Hyperf\Crontab\Parser;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class ParserCronNumberTest extends TestCase
{
    protected $timezone;

    protected function setUp(): void
    {
        $this->timezone = ini_get('date.timezone');
        ini_set('date.timezone', 'Asia/Shanghai');
    }

    protected function tearDown(): void
    {
        ini_set('date.timezone', $this->timezone);
    }

    public function testParse()
    {
        $parser = new Parser();
        $reflectionMethod = new ReflectionMethod(Parser::class, 'parseSegment');

        $result = $reflectionMethod->invoke($parser, '*', 0, 59);
        $expected = [];
        for ($i = 0; $i < 60; ++$i) {
            $expected[] = $i;
        }
        $this->assertSame($expected, $result);

        $result = $reflectionMethod->invoke($parser, '*/11', 0, 59);
        $this->assertSame([0, 11, 22, 33, 44, 55], $result);

        $result = $reflectionMethod->invoke($parser, '0-40/11', 0, 59);
        $this->assertSame([0, 11, 22, 33], $result);

        $result = $reflectionMethod->invoke($parser, '2-40/11', 0, 23);
        $this->assertSame([2, 13], $result);

        $result = $reflectionMethod->invoke($parser, '2-10/3', 0, 11);
        $this->assertSame([2, 5, 8], $result);

        $result = $reflectionMethod->invoke($parser, '11', 0, 59);
        $this->assertSame([11], $result);

        $result = $reflectionMethod->invoke($parser, '11,12,13', 0, 59);
        $this->assertSame([11, 12, 13], $result);
    }

    public function testParseWithStart()
    {
        $parser = new Parser();
        $reflectionMethod = new ReflectionMethod(Parser::class, 'parseSegment');

        $result = $reflectionMethod->invoke($parser, '*', 0, 59, 12);
        $expected = [];
        for ($i = 12; $i < 60; ++$i) {
            $expected[] = $i;
        }
        $this->assertSame($expected, $result);

        $result = $reflectionMethod->invoke($parser, '*/11', 0, 59, 12);
        $this->assertSame([12, 23, 34, 45, 56], $result);

        $result = $reflectionMethod->invoke($parser, '0-40/11', 0, 59, 12);
        $this->assertSame([12, 23, 34], $result);

        $result = $reflectionMethod->invoke($parser, '11', 0, 59, 12);
        $this->assertSame([], $result);

        $result = $reflectionMethod->invoke($parser, '11', 0, 59, 10);
        $this->assertSame([11], $result);

        $result = $reflectionMethod->invoke($parser, '11,12,13', 0, 59, 12);
        $this->assertSame([12, 13], $result);
    }
}
