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
namespace Hyperf\Database\Migrations;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;

abstract class Migration
{
    /**
     * Enables, if supported, wrapping the migration within a transaction.
     */
    public bool $withinTransaction = true;

    /**
     * The name of the database connection to use.
     */
    protected ?string $connection = null;

    /**
     * Get the migration connection name.
     */
    public function getConnection(): string
    {
        if ($connection = $this->connection) {
            return $connection;
        }

        return ApplicationContext::getContainer()
            ->get(ConfigInterface::class)
            ->get('databases.connection', 'default');
    }
}
