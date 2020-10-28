<?php


namespace HyperfTest\Cases;


use Hyperf\Lock\Factory\RedisLock;

class RedisLockTest extends AbstractTestCase
{
    public function testLock()
    {
        $container = $this->getContainer();
        /** @var RedisLock $redisLock */
        $redisLock = $container->make(RedisLock::class, [$container, []]);

        $ref = new \ReflectionClass($redisLock);
        $ref->getMethod('getLockContent')->setAccessible(true);
        $ref->getMethod('getLockKey')->setAccessible(true);
        $result = $redisLock->lock('test');
        $this->assertSame($result, true);
    }

    public function testUnlock()
    {
        $container = $this->getContainer();
        /** @var RedisLock $redisLock */
        $redisLock = $container->make(RedisLock::class, [$container, []]);

        $result = $redisLock->unlock('test');
        $this->assertSame($result, true);
    }
}
