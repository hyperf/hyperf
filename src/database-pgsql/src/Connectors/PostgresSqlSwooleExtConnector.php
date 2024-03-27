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

namespace Hyperf\Database\PgSQL\Connectors;

use Exception;
use Hyperf\Database\Connectors\ConnectorInterface;
use Swoole\Coroutine\PostgreSQL;

class PostgresSqlSwooleExtConnector implements ConnectorInterface
{
    /**
     * @return PostgreSQL
     */
    public function connect(array $config)
    {
        $connection = $this->createConnection($config);

        $this->configureEncoding($connection, $config);

        // Next, we will check to see if a timezone has been specified in this config
        // and if it has we will issue a statement to modify the timezone with the
        // database. Setting this DB timezone is an optional configuration item.
        $this->configureTimezone($connection, $config);

        $this->configureSchema($connection, $config);

        // Postgres allows an application_name to be set by the user and this name is
        // used to when monitoring the application with pg_stat_activity. So we'll
        // determine if the option has been specified and run a statement if so.
        $this->configureApplicationName($connection, $config);

        $this->configureSynchronousCommit($connection, $config);

        return $connection;
    }

    public function createConnection(array $config): PostgreSQL
    {
        $connection = new PostgreSQL();

        $result = $connection->connect(sprintf(
            'host=%s port=%s dbname=%s user=%s password=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['username'],
            $config['password']
        ));

        if ($result === false) {
            throw new Exception($connection->error ?? 'Create connection failed, Please check the database configuration.');
        }

        return $connection;
    }

    /**
     * Set the connection character set and collation.
     *
     * @param array $config
     */
    protected function configureEncoding(PostgreSQL $connection, $config)
    {
        if (! isset($config['charset'])) {
            return;
        }

        $connection->prepare("set names '{$config['charset']}'")->execute();
    }

    /**
     * Set the timezone on the connection.
     */
    protected function configureTimezone(PostgreSQL $connection, array $config)
    {
        if (isset($config['timezone'])) {
            $timezone = $config['timezone'];
            $connection->prepare("set time zone '{$timezone}'")->execute();
        }
    }

    /**
     * Set the schema on the connection.
     *
     * @param array $config
     */
    protected function configureSchema(PostgreSQL $connection, $config)
    {
        if (isset($config['schema'])) {
            $schema = $this->formatSchema($config['schema']);
            $connection->prepare("set search_path to {$schema}")->execute();
        }
    }

    /**
     * Format the schema for the DSN.
     *
     * @param array|string $schema
     * @return string
     */
    protected function formatSchema($schema)
    {
        if (is_array($schema)) {
            return '"' . implode('", "', $schema) . '"';
        }

        return '"' . $schema . '"';
    }

    /**
     * Set the schema on the connection.
     *
     * @param array $config
     * @param mixed $connection
     */
    protected function configureApplicationName($connection, $config)
    {
        if (isset($config['application_name'])) {
            $applicationName = $config['application_name'];
            $connection->prepare("set application_name to '{$applicationName}'")->execute();
        }
    }

    /**
     * Configure the synchronous_commit setting.
     * @param mixed $connection
     */
    protected function configureSynchronousCommit($connection, array $config)
    {
        if (! isset($config['synchronous_commit'])) {
            return;
        }
        $connection->prepare("set synchronous_commit to '{$config['synchronous_commit']}'")->execute();
    }
}
