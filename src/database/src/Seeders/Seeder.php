<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Seeders;

use InvalidArgumentException;

abstract class Seeder
{
    /**
     * The name of the database connection to use.
     *
     * @var string|null
     */
    protected $connection = 'default';

    /**
     * Enables, if supported, wrapping the seeder within a transaction.
     *
     * @var bool
     */
    public $withinTransaction = true;

    /**
     * Get the seeder connection name.
     *
     * @return string|null
     */
    public function getConnection()
    {
        return $this->connection;
    }
}