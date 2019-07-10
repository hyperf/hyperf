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

namespace HyperfTest\Di;

use Hyperf\Di\MethodDefinitionCollector;
use HyperfTest\Di\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class MethodDefinitionTest extends TestCase
{
    public function testGetOrParse()
    {
        $definitions = MethodDefinitionCollector::getOrParse(Foo::class, 'getBar');
        $this->assertSame(4, count($definitions));

        $this->assertArrayNotHasKey('defaultValue', $definitions[0]);
        $this->assertArrayHasKey('defaultValue', $definitions[1]);
    }
}
