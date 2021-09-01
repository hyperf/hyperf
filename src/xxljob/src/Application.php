<?php

declare(strict_types=1);

namespace Hyperf\XxlJob;

use Exception;
use Hyperf\XxlJob\Provider\ServiceProvider;

/**
 * @property ServiceProvider $service
 */
class Application
{
    protected $alias = [
        'service' => ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected static $jobHandlers = [];

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param mixed $name
     * @throws Exception
     */
    public function __get($name)
    {
        if (! isset($name) || ! isset($this->alias[$name])) {
            throw new Exception("{$name} is invalid.");
        }

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        $class = $this->alias[$name];
        return $this->providers[$name] = new $class($this->config);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public static function getJobHandlers(string $key): string
    {
        return self::$jobHandlers[$key] ?? '';
    }

    public static function setJobHandlers(string $key, string $value): void
    {
        self::$jobHandlers[$key] = $value;
    }
}
