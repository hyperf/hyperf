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

namespace Hyperf\DbConnection;

use Closure;
use Generator;
use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection as Conn;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;
use LogicException;
use Psr\Container\ContainerInterface;

/**
 * DB Helper.
 * @method static Builder table(Expression|string $table)
 * @method static Expression raw($value)
 * @method static mixed selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static Generator cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool insert(string $query, array $bindings = [])
 * @method static int update(string $query, array $bindings = [])
 * @method static int delete(string $query, array $bindings = [])
 * @method static bool statement(string $query, array $bindings = [])
 * @method static int affectingStatement(string $query, array $bindings = [])
 * @method static bool unprepared(string $query)
 * @method static array prepareBindings(array $bindings)
 * @method static mixed transaction(Closure $callback, int $attempts = 1)
 * @method static void beginTransaction()
 * @method static void rollBack()
 * @method static void commit()
 * @method static int transactionLevel()
 * @method static array pretend(Closure $callback)
 * @method static ConnectionInterface connection(?string $pool = null)
 */
class Db
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function __call(string $name, array $arguments): mixed
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

    private function __connection(?string $name = null): ConnectionInterface
    {
        $resolver = $this->container->get(ConnectionResolverInterface::class);
        return $resolver->connection($name);
    }

    public static function beforeExecuting(Closure $closure): void
    {
        Conn::beforeExecuting($closure);
    }

    /**
     * Enable releasing the pooled connection right after each use at runtime,
     * with priority over the `databases.{name}.release_after_use` option.
     * Applies to all coroutines from the moment it is called.
     */
    public static function enableReleaseAfterUse(?string $name = null): void
    {
        $resolver = static::releaseAfterUseResolver();
        $resolver->setReleaseAfterUse($name ?? $resolver->getDefaultConnection(), true);
    }

    /**
     * Disable releasing the pooled connection right after each use at runtime,
     * with priority over the `databases.{name}.release_after_use` option.
     * Applies to all coroutines from the moment it is called.
     */
    public static function disableReleaseAfterUse(?string $name = null): void
    {
        $resolver = static::releaseAfterUseResolver();
        $resolver->setReleaseAfterUse($name ?? $resolver->getDefaultConnection(), false);
    }

    /**
     * Remove the runtime override of release-after-use,
     * so the connection follows the configuration again.
     */
    public static function resetReleaseAfterUse(?string $name = null): void
    {
        $resolver = static::releaseAfterUseResolver();
        $resolver->resetReleaseAfterUse($name ?? $resolver->getDefaultConnection());
    }

    private static function releaseAfterUseResolver(): ConnectionResolver
    {
        $resolver = ApplicationContext::getContainer()->get(ConnectionResolverInterface::class);
        if (! $resolver instanceof ConnectionResolver) {
            throw new LogicException(sprintf('The connection resolver must be an instance of %s to toggle release-after-use at runtime.', ConnectionResolver::class));
        }
        return $resolver;
    }
}
