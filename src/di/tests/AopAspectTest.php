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

use Hyperf\Di\Aop\Aspect;
use Hyperf\Di\Aop\RewriteCollection;
use HyperfTest\Di\Stub\AspectCollector;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AopAspectTest extends TestCase
{
    protected function tearDown()
    {
        AspectCollector::clear();
    }

    public function testParseMoreThanOneMethods()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo::test1',
            'Demo::test2',
        ], []);

        $res = Aspect::parse('Demo');

        $this->assertArrayHasKey($aspect, $res->getMethods());
        $this->assertEquals(['test1', 'test2'], $res->getMethods()[$aspect]);
    }

    public function testParseOneMethod()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo::test1',
        ], []);

        $res = Aspect::parse('Demo');

        $this->assertArrayHasKey($aspect, $res->getMethods());
        $this->assertEquals(['test1'], $res->getMethods()[$aspect]);
    }

    public function testParseClass()
    {
        $aspect = 'App\Aspect\DebugAspect';

        AspectCollector::setAround($aspect, [
            'Demo',
        ], []);

        $res = Aspect::parse('Demo');
        $this->assertSame(RewriteCollection::LEVEL_CLASS, $res->getLevel());
    }
}
