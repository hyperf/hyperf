<?php
/**
 * Created by PhpStorm.
 * User: limx
 * Date: 2019/1/3
 * Time: 5:59 PM
 */

namespace Hyperf\Redis;


use Hyperf\Redis\Pool\PoolFactory;
use Psr\Container\ContainerInterface;

class Redis
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $name = 'default';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        $factory = $this->container->get(PoolFactory::class);
        $pool = $factory->getRedisPool($this->name);
        
        $connection = $pool->get()->getConnection();
        // TODO: Handle multi ...
        $res = $connection->$name(...$arguments);
        $connection->release();

        return $res;
    }
}