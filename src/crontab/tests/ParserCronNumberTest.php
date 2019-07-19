<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Crontab;

use Hyperf\Crontab\Parser;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class ParserCronNumberTest extends TestCase
{
    protected function setUp()
    {
        ini_set('date.timezone', 'Asia/Shanghai');
    }

    public function testParse()
    {
        $parser = new Parser();
        $reflectionMethod = new ReflectionMethod(Parser::class, 'parseSegment');
        $reflectionMethod->setAccessible(true);

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

        $result = $reflectionMethod->invoke($parser, '11', 0, 59);
        $this->assertSame([11], $result);

        $result = $reflectionMethod->invoke($parser, '11,12,13', 0, 59);
        $this->assertSame([11, 12, 13], $result);
    }

    public function testParseWithStart()
    {
        $parser = new Parser();
        $reflectionMethod = new ReflectionMethod(Parser::class, 'parseSegment');
        $reflectionMethod->setAccessible(true);

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
