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
namespace Hyperf\NacosSdk;

use Hyperf\NacosSdk\Exception\InvalidArgumentException;
use Hyperf\NacosSdk\Provider\AuthProvider;
use Hyperf\NacosSdk\Provider\ConfigProvider;
use Hyperf\NacosSdk\Provider\InstanceProvider;
use Hyperf\NacosSdk\Provider\OperatorProvider;
use Hyperf\NacosSdk\Provider\ServiceProvider;

/**
 * @property AuthProvider $auth
 * @property ConfigProvider $config
 * @property InstanceProvider $instance
 * @property OperatorProvider $operator
 * @property ServiceProvider $service
 */
class Application
{
    protected $alias = [
        'auth' => AuthProvider::class,
        'config' => ConfigProvider::class,
        'instance' => InstanceProvider::class,
        'operator' => OperatorProvider::class,
        'service' => ServiceProvider::class,
    ];

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var Config
     */
    protected $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function __get($name)
    {
        if (! isset($name) || ! isset($this->alias[$name])) {
            throw new InvalidArgumentException("{$name} is invalid.");
        }

        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        $class = $this->alias[$name];
        return $this->providers[$name] = new $class($this, $this->config);
    }
}
