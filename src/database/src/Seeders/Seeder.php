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
namespace Hyperf\Database\Seeders;

abstract class Seeder
{
    /**
     * Enables, if supported, wrapping the seeder within a transaction.
     *
     * @var bool
     */
    public $withinTransaction = true;

    /**
     * The name of the database connection to use.
     *
     * @var null|string
     */
    protected $connection = 'default';

    /**
     * Get the seeder connection name.
     *
     * @return null|string
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
