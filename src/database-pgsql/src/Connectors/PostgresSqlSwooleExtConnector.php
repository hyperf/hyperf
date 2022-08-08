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

use Hyperf\Database\Connectors\ConnectorInterface;
use PDO;
use Swoole\Coroutine\PostgreSQL;

class PostgresSqlSwooleExtConnector implements ConnectorInterface
{
    /**
     * The default PDO connection options.
     *
     * @var array
     */
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

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
            throw new \Exception($connection->error);
        }

        return $connection;
    }

    /**
     * Get the default PDO connection options.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return $this->options;
    }

    /**
     * Set the default PDO connection options.
     */
    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Set the connection character set and collation.
     *
     * @param $connection
     * @param array $config
     */
    protected function configureEncoding($connection, $config)
    {
        if (! isset($config['charset'])) {
            return;
        }
        $id = uniqid();
        $connection->prepare($id, "set names '{$config['charset']}'");
        $connection->execute($id, []);
    }

    /**
     * Set the timezone on the connection.
     *
     * @param $connection
     */
    protected function configureTimezone($connection, array $config)
    {
        if (isset($config['timezone'])) {
            $timezone = $config['timezone'];
            $id = uniqid();
            $connection->prepare($id, "set time zone '{$timezone}'");
            $connection->execute($id, []);
        }
    }

    /**
     * Set the schema on the connection.
     *
     * @param $connection
     * @param array $config
     */
    protected function configureSchema($connection, $config)
    {
        if (isset($config['schema'])) {
            $schema = $this->formatSchema($config['schema']);
            $id = uniqid();
            $connection->prepare($id, "set search_path to {$schema}");
            $connection->execute($id, []);
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
     * @param $connection
     * @param array $config
     */
    protected function configureApplicationName($connection, $config)
    {
        if (isset($config['application_name'])) {
            $applicationName = $config['application_name'];
            $id = uniqid();
            $connection->prepare($id, "set application_name to '{$applicationName}'");
            $connection->execute($id, []);
        }
    }

    /**
     * Configure the synchronous_commit setting.
     *
     * @param $connection
     */
    protected function configureSynchronousCommit($connection, array $config)
    {
        if (! isset($config['synchronous_commit'])) {
            return;
        }
        $id = uniqid();
        $connection->prepare($id, "set synchronous_commit to '{$config['synchronous_commit']}'");
        $connection->execute($id, []);
    }
}
