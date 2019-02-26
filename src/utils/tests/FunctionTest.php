<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Utils;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FunctionTest extends TestCase
{
    public function testCall()
    {
        $result = call(function ($i) {
            return ++$i;
        }, [1]);

        $this->assertSame(2, $result);
    }
}
