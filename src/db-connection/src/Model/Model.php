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

namespace Hyperf\DbConnection\Model;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Model\Model as BaseModel;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\Framework\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;

class Model extends BaseModel
{
    /**
     * Get the database connection for the model.
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        $resolver = ApplicationContext::getContainer()->get(ConnectionResolver::class);
        return $resolver->connection($this->getConnectionName());
    }

    public function getEventDispatcher()
    {
        return ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
    }
}
