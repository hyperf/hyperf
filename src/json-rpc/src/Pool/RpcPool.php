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
namespace Hyperf\JsonRpc\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Pool;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class RpcPool extends Pool
{
    public function __construct(ContainerInterface $container, protected string $name, protected array $config)
    {
        $this->frequency = make(Frequency::class, [$this]);

        parent::__construct($container, $config['pool'] ?? []);
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
