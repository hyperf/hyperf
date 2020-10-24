<?php


namespace Hyperf\Lock;


use Hyperf\Lock\Factory\LockFactory;
use Hyperf\Lock\Factory\LockInterface;

/**
 * @method lock(string $id)
 * @method unlock(string $id)
 */
class Lock
{
    /**
     * @var LockInterface
     */
    protected $lock;

    /**
     * @var string
     */
    protected $poolName = 'default';

    public function __construct(LockFactory $lockFactory, string $poolName = 'default')
    {
        $this->lock = $lockFactory->getLockDriver($poolName);
        $this->poolName = $poolName;
    }

    public function __call($name, $arguments)
    {
        return $this->lock->{$name}(...$arguments);
    }

}
