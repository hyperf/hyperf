<?php
/**
 * Created by PhpStorm.
 * User: limx
 * Date: 2018/12/30
 * Time: 2:48 PM
 */

namespace Hyperf\DbConnection\Model;

use Hyperf\Database\Model\Model as BaseModel;
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\Framework\ApplicationContext;
use Hyperf\Database\ConnectionInterface;

class Model extends BaseModel
{
    /**
     * Get the database connection for the model.
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        $connectionName = $this->getConnectionName();
        $resolver = ApplicationContext::getContainer()->get(ConnectionResolver::class);
        return $resolver->connection($connectionName);
    }
}