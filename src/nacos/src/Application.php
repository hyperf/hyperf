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

namespace Hyperf\Nacos;

use Hyperf\Nacos\Exception\InvalidArgumentException;
use Hyperf\Nacos\Provider\AuthProvider;
use Hyperf\Nacos\Provider\ConfigProvider;
use Hyperf\Nacos\Provider\InstanceProvider;
use Hyperf\Nacos\Provider\OperatorProvider;
use Hyperf\Nacos\Provider\ServiceProvider;

/**
 * @property AuthProvider $auth
 * @property ConfigProvider $config
 * @property InstanceProvider $instance
 * @property OperatorProvider $operator
 * @property ServiceProvider $service
 * @property GrpcFactory $grpc
 */
class Application
{
    protected array $alias = [
        'auth' => AuthProvider::class,
        'config' => ConfigProvider::class,
        'instance' => InstanceProvider::class,
        'operator' => OperatorProvider::class,
        'service' => ServiceProvider::class,
        'grpc' => GrpcFactory::class,
    ];

    protected array $providers = [];

    public function __construct(protected Config $config)
    {
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
