<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DB\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\DB\Frequency;
use Hyperf\Pool\Pool as HyperfPool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

abstract class Pool extends HyperfPool
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('db.%s', $name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }

        $config->set("{$key}.name", $name);

        $this->name = $name;
        $this->config = $config->get($key);
        $options = Arr::get($this->config, 'pool', []);
        $this->frequency = make(Frequency::class);

        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
