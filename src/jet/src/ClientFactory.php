<?php

namespace Hyperf\Jet;


use Psr\Container\ContainerInterface;

class ClientFactory
{

    /**
     * @var null|ContainerInterface
     */
    protected $container;

    public function create(string $service, string $protocol): AbstractClient
    {
        $transporter = $this->createTransporter($protocol);
        $packer = $this->createPacker($protocol);
        $dataFormatter = $this->createDataFormatter($protocol);
        $pathGenerator = $this->createPathGenerator($protocol);
        return new class($service, $transporter, $packer, $dataFormatter, $pathGenerator) extends AbstractClient {};
    }

}