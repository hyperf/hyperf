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

namespace Hyperf\JsonRpc\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;

class RpcPool extends Pool
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
        $options = [];
        $this->frequency = make(Frequency::class);
        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new RpcConnection($this->container, $this, $this->config);
    }
}
