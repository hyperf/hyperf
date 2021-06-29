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

use Multiplex\Constract\IdGeneratorInterface;
use Multiplex\Constract\PackerInterface;
use Multiplex\Constract\SerializerInterface;
use Psr\Container\ContainerInterface;

class Socket extends \Multiplex\Socket\Client
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

    /**
     * @return $this
     */
    public function setName(string $name): Socket
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return $this
     */
    public function setPort(int $port)
    {
        $this->port = $port;
        return $this;
    }
}
