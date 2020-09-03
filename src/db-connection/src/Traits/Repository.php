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

namespace Hyperf\DbConnection\Traits;


use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Model\Model;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;

/** @mixin Model */
trait Repository
{
    /**
     * @var string the full namespace of repository class
     */
    protected $repository;

    /**
     * Get the database connection for the model.
     */
    public function getConnection(): ConnectionInterface
    {
        $connectionName = $this->getConnectionName();
        $resolver = $this->getContainer()->get(ConnectionResolverInterface::class);
        return $resolver->connection($connectionName);
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->getContainer()->get(EventDispatcherInterface::class);
    }

    /**
     * @throws RuntimeException when the model does not define the repository class
     */
    public function getRepository()
    {
        if (!$this->repository || !class_exists($this->repository) && !interface_exists($this->repository)) {
            throw new RuntimeException(sprintf('Cannot detect the repository of %s', static::class));
        }
        return $this->getContainer()->get($this->repository);
    }
}