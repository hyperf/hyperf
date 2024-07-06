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

namespace Hyperf\RpcMultiplex;

use Multiplex\Contract\IdGeneratorInterface;
use Multiplex\Contract\PackerInterface;
use Multiplex\Contract\SerializerInterface;
use Multiplex\Socket\Client;
use Psr\Container\ContainerInterface;

class Socket extends Client
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct(
            '',
            80,
            $container->get(IdGeneratorInterface::class),
            $container->get(SerializerInterface::class),
            $container->get(PackerInterface::class)
        );
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setPort(int $port): static
    {
        $this->port = $port;
        return $this;
    }
}
