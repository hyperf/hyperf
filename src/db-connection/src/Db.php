<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Framework\ApplicationContext;
use Psr\Container\ContainerInterface;

/**
 * DB Helper.
 * @method static beginTransaction
 * @method static rollback
 * @method statuc commit
 */
class Db
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __call($name, $arguments)
    {
        return $this->connection()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $db = ApplicationContext::getContainer()->get(Db::class);
        return $db->connection()->{$name}(...$arguments);
    }

    public function connection($pool = 'default'): ConnectionInterface
    {
        $resolver = $this->container->get(ConnectionResolver::class);
        return $resolver->connection($pool);
    }
}
