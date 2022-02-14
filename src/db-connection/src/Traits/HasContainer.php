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
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

trait HasContainer
{
    /**
     * Get the database connection for the model.
     */
    public function getConnection(): ConnectionInterface
    {
        $connectionName = $this->getConnectionName();
        $resolver = $this->getContainer()->get(ConnectionResolverInterface::class);
        return $resolver->connection($connectionName);
    }

    public function getEventDispatcher(): ?EventDispatcherInterface
    {
        return $this->getContainer()->get(EventDispatcherInterface::class);
    }

    protected function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
