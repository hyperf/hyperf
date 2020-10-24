<?php


namespace HyperfTest\Cases;


use Hyperf\Lock\Factory\LockFactory;
use Hyperf\Lock\Factory\RedisLock;
use Mockery;

class LockFactoryTest extends AbstractTestCase
{

    public function testGetDriver()
    {
        $container = $this->getContainer();
        $driver = $container->get(LockFactory::class)->getLockDriver('default');
        $this->assertInstanceOf(RedisLock::class, $driver);
    }
}
