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

namespace HyperfTest\SingleFlight;

use Hyperf\SingleFlight\Annotation\SingleFlight;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    public function testSingleFlight()
    {
        $value = '#{arg1}_#{arg2}';
        $annotation = new SingleFlight($value);
        $this->assertSame($value, $annotation->value);
    }
}
