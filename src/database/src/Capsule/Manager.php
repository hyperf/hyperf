<?php

namespace Hyperf\Database\Capsule;

use Hyperf\Config\Config;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Model\Register;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Definition\DefinitionSource;
use PDO;
use Psr\Container\ContainerInterface as Container;
use Hyperf\Database\DatabaseManager;
use Psr\EventDispatcher\EventDispatcherInterface as Dispatcher;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Connectors\ConnectionFactory;
use Psr\EventDispatcher\EventDispatcherInterface;

class Manager
{

    /**
     * The database manager instance.
     *
     * @var DatabaseManager
     */
    protected $manager;

    /**
     * The current globally used instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * The container instance.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Create a new database capsule manager.
     */
    public function __construct(Container $container = null)
    {
        if (is_null($container)) {
            $container = new \Hyperf\Di\Container(new DefinitionSource([], [], new Scanner()));
        }
        $this->setupContainer($container);

        // Once we have the container setup, we will setup the default configuration
        // options in the container "config" binding. This will make the database
        // manager work correctly out of the box without extreme configuration.
        $this->setupDefaultConfiguration();

        $this->setupManager();
    }

    /**
     * Setup the IoC container instance.
     *
     * @param  \Psr\Container\ContainerInterface  $container
     * @return void
     */
    protected function setupContainer(Container $container)
    {
        $this->container = $container;

        if (! $this->container->has(ConfigInterface::class)) {
            $this->container->instance(ConfigInterface::class, new Config([]));
        }
    }

    /**
     * Make this capsule instance available globally.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Psr\Container\ContainerInterface  $container
     * @return void
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Setup the default database configuration options.
     *
     * @return void
     */
    protected function setupDefaultConfiguration()
    {
        $this->container['config']['database.fetch'] = PDO::FETCH_OBJ;

        $this->container['config']['database.default'] = 'default';
    }

    /**
     * Build the database manager instance.
     *
     * @return void
     */
    protected function setupManager()
    {
        $factory = new ConnectionFactory($this->container);

        $this->manager = new DatabaseManager($this->container, $factory);
    }

    /**
     * Get a connection instance from the global manager.
     *
     * @param  string  $connection
     * @return \Illuminate\Database\Connection
     */
    public static function connection($connection = null)
    {
        return static::$instance->getConnection($connection);
    }

    /**
     * Get a fluent query builder instance.
     *
     * @param  string  $table
     * @param  string  $connection
     * @return \Illuminate\Database\Query\Builder
     */
    public static function table($table, $connection = null)
    {
        return static::$instance->connection($connection)->table($table);
    }

    /**
     * Get a schema builder instance.
     *
     * @param  string  $connection
     * @return \Illuminate\Database\Schema\Builder
     */
    public static function schema($connection = null)
    {
        return static::$instance->connection($connection)->getSchemaBuilder();
    }

    /**
     * Get a registered connection instance.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Connection
     */
    public function getConnection($name = null)
    {
        return $this->manager->connection($name);
    }

    /**
     * Register a connection with the manager.
     *
     * @param  array   $config
     * @param  string  $name
     * @return void
     */
    public function addConnection(array $config, $name = 'default')
    {
        $connections = $this->container['config']['database.connections'];

        $connections[$name] = $config;

        $this->container['config']['database.connections'] = $connections;
    }

    /**
     * Bootstrap Eloquent so it is ready for usage.
     *
     * @return void
     */
    public function bootEloquent()
    {
        Register::setConnectionResolver($this->manager);

        // If we have an event dispatcher instance, we will go ahead and register it
        // with the Eloquent ORM, allowing for model callbacks while creating and
        // updating "model" instances; however, it is not necessary to operate.
        if ($dispatcher = $this->getEventDispatcher()) {
            Register::setEventDispatcher($dispatcher);
        }
    }

    /**
     * Set the fetch mode for the database connections.
     *
     * @param  int  $fetchMode
     * @return $this
     */
    public function setFetchMode($fetchMode)
    {
        $this->container['config']['database.fetch'] = $fetchMode;

        return $this;
    }

    /**
     * Get the database manager instance.
     *
     * @return DatabaseManager
     */
    public function getDatabaseManager()
    {
        return $this->manager;
    }

    /**
     * Get the current event dispatcher instance.
     *
     * @return EventDispatcherInterface|null
     */
    public function getEventDispatcher()
    {
        if ($this->container->has(EventDispatcherInterface::class)) {
            return $this->container->get(EventDispatcherInterface::class);
        }
    }

    /**
     * Set the event dispatcher instance to be used by connections.
     *
     * @param  \Psr\EventDispatcher\EventDispatcherInterface  $dispatcher
     * @return void
     */
    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->container->set(EventDispatcherInterface::class, $dispatcher);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::connection()->$method(...$parameters);
    }
}
