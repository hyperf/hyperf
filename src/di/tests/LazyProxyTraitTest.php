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
namespace HyperfTest\Di;

use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Container;
use Hyperf\Di\LazyLoader\LazyProxyTrait;
use HyperfTest\Di\Stub\LazyLoad\BarLazyLoad;
use HyperfTest\Di\Stub\LazyLoad\FooLazyLoad;
use HyperfTest\Di\Stub\LazyProxy;
use HyperfTest\Di\Stub\Proxied;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LazyProxyTraitTest extends TestCase
{
    public function testLaziness()
    {
        $lp = new LazyProxy();
        $this->assertFalse(Proxied::$isInitialized);
    }

    public function testSet()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        $lp->id = '12';
        $this->assertEquals('12', $proxied->id);
    }

    public function testGet()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        $this->assertEquals('20', $lp->id);
    }

    public function testUnset()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        unset($lp->id);
        $this->assertFalse(isset($proxied->id));
    }

    public function testIsset()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        $this->assertTrue(isset($lp->id));
    }

    public function testCallMethod()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        $lp->setId('1');
        $this->assertEquals('1', $proxied->id);
    }

    public function testClone()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        $lp2 = clone $lp;
        $this->assertEquals($proxied, $lp2->getInstance());
    }

    public function testSerialize()
    {
        $lp = new LazyProxy();
        $this->mockContainer();
        $proxied = ApplicationContext::getContainer()->get(Proxied::class);
        $s = serialize($lp);
        $lp2 = unserialize($s);
        $this->assertEquals($lp, $lp2);
    }

    public function testLazyLoadWithMagicCall()
    {
        $class1 = new class() extends FooLazyLoad {
            use LazyProxyTrait;
        };

        $class2 = new class() extends BarLazyLoad {
            use LazyProxyTrait;
        };

        $this->assertTrue(true);
    }

    private function mockContainer()
    {
        $container = Mockery::mock(Container::class);
        $container->shouldReceive('get')
            ->zeroOrMoreTimes()
            ->with(Proxied::class)
            ->andReturn(new Proxied('20', 'hello'));
        ApplicationContext::setContainer($container);
    }
}
