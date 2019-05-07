<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;

/**
 * DB Helper.
 * @method static table(string $table)
 * @method static raw($value)
 * @method static selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static insert(string $query, array $bindings = [])
 * @method static update(string $query, array $bindings = [])
 * @method static delete(string $query, array $bindings = [])
 * @method static statement(string $query, array $bindings = [])
 * @method static affectingStatement(string $query, array $bindings = [])
 * @method static unprepared(string $query)
 * @method static prepareBindings(array $bindings)
 * @method static transaction(Closure $callback, int $attempts = 1)
 * @method static beginTransaction()
 * @method static rollBack()
 * @method static commit()
 * @method static transactionLevel()
 * @method static pretend(Closure $callback)
 * @method static connection(string $pool)
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
        if ($name === 'connection') {
            return $this->__connection(...$arguments);
        }
        return $this->__connection()->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $db = ApplicationContext::getContainer()->get(Db::class);
        if ($name === 'connection') {
            return $db->__connection(...$arguments);
        }
        return $db->__connection()->{$name}(...$arguments);
    }

    private function __connection($pool = 'default'): ConnectionInterface
    {
        $resolver = $this->container->get(ConnectionResolver::class);
        return $resolver->connection($pool);
    }
}
