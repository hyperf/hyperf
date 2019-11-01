<?php
declare(strict_types=1);

namespace Hyperf\DB;

use Hyperf\Pool\Exception\ConnectionException;
use Hyperf\Pool\Pool;
use PDO;
use Psr\Container\ContainerInterface;

class PDOConnection extends AbstractConnection
{

    /**
     * @var PDO
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config = [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'test',
        'username' => 'root',
        'password' => 'root',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => '60',
        ],
    ];

    /**
     * Current mysql database.
     * @var null|int
     */
    protected $database;

    public function __construct(ContainerInterface $container, Pool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = array_replace($this->config, $config);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    /**
     * Reconnect the connection.
     */
    public function reconnect(): bool
    {
        $dbms = $this->config['driver'];
        $host = $this->config['host'];
        $dbName = $this->config['database'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $dsn = "$dbms:host=$host;dbname=$dbName";
        try {
            $pdo = new \PDO($dsn, $username, $password, [PDO::ATTR_PERSISTENT => true]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new ConnectionException('Connection reconnect failed.:' . $e->getMessage());
        }

        $this->connection = $pdo;
        $this->lastUseTime = microtime(true);

        return true;
    }

    /**
     * Close the connection.
     */
    public function close(): bool
    {
        unset($this->connection);

        return true;
    }
}