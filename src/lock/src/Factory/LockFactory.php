<?php


namespace Hyperf\Lock\Factory;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Lock\Exception\NotFountDriverException;

class LockFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getLockDriver(string $poolName = 'default')
    {
        $config = $this->container->get(ConfigInterface::class)->get(sprintf('lock.%s', $poolName));
        return  make(
            $this->getDriver($config['driver']),
            [
                $this->container,
                $config,
            ]
        );
    }


    protected function getDriver(string $driver): string
    {
        switch ($driver) {
            case 'redis':
                return RedisLock::class;
        }
        throw new NotFountDriverException('not fount lock driver');
    }
}
