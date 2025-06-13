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

namespace HyperfTest\Stringable;

use Hyperf\Stringable\Stringable;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function Hyperf\Stringable\str;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FunctionsTest extends TestCase
{
    public function testStr()
    {
        $stringable = str('string-value');

        $this->assertInstanceOf(Stringable::class, $stringable);
        $this->assertSame('string-value', (string) $stringable);

        $stringable = str($name = null);
        $this->assertInstanceOf(Stringable::class, $stringable);
        $this->assertTrue($stringable->isEmpty());

        $strAccessor = str();
        $this->assertTrue((new ReflectionClass($strAccessor))->isAnonymous());
        $this->assertSame($strAccessor->limit('string-value', 3), 'str...');

        $strAccessor = str();
        $this->assertTrue((new ReflectionClass($strAccessor))->isAnonymous());
        $this->assertSame((string) $strAccessor, '');
    }
}
