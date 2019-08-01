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

use Hyperf\Di\DocMethodDefinitionCollector;
use Hyperf\Di\ReflectionType;
use HyperfTest\Di\Stub\Foo;
use kuiper\docReader\DocReader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DocMethodDefinitionTest extends TestCase
{
    public function testGetParameters()
    {
        $collector = new DocMethodDefinitionCollector(new DocReader());
        /** @var ReflectionType[] $definitions */
        $definitions = $collector->getParameters(Foo::class, 'getBar');
        $this->assertEquals(4, count($definitions));
        $this->assertEquals('?int', $definitions[0]->getName());
        $this->assertFalse($definitions[0]->getMeta('defaultValueAvailable'));
        $this->assertTrue($definitions[1]->getMeta('defaultValueAvailable'));
    }
}
