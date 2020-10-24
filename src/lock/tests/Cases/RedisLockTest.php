<?php


namespace HyperfTest\Cases;


use Hyperf\Lock\Factory\RedisLock;

class RedisLockTest extends AbstractTestCase
{
    public function testLock()
    {
        $container = $this->getContainer();
        $redisLock = $container->make(RedisLock::class, [$container, []]);

        $ref = new \ReflectionClass($redisLock);
        $ref->getMethod('getLockContent')->setAccessible(true);
        $ref->getMethod('getLockKey')->setAccessible(true);
        $result = $redisLock->lock('test');
        var_dump($result);
        $this->assertSame($result, true);
    }
}
