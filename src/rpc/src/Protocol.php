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

namespace Hyperf\Rpc;

use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\PackerInterface;
use Hyperf\Rpc\Contract\DataFormatterInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\Contract\TransporterInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class Protocol
{
    public function __construct(private ContainerInterface $container, private ProtocolManager $protocolManager, private string $name, private array $options = [])
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPacker(): PackerInterface
    {
        $packer = $this->protocolManager->getPacker($this->name);
        if (! $this->container->has($packer)) {
            throw new InvalidArgumentException("Packer {$packer} for {$this->name} does not exist");
        }

        return make($packer, [$this->options]);
    }

    public function getTransporter(): TransporterInterface
    {
        $transporter = $this->protocolManager->getTransporter($this->name);
        if (! $this->container->has($transporter)) {
            throw new InvalidArgumentException("Transporter {$transporter} for {$this->name} does not exist");
        }
        return make($transporter, ['config' => $this->options]);
    }

    public function getPathGenerator(): PathGeneratorInterface
    {
        $pathGenerator = $this->protocolManager->getPathGenerator($this->name);
        if (! $this->container->has($pathGenerator)) {
            throw new InvalidArgumentException("PathGenerator {$pathGenerator} for {$this->name} does not exist");
        }
        return $this->container->get($pathGenerator);
    }

    public function getDataFormatter(): DataFormatterInterface
    {
        $dataFormatter = $this->protocolManager->getDataFormatter($this->name);
        if (! $this->container->has($dataFormatter)) {
            throw new InvalidArgumentException("DataFormatter {$dataFormatter} for {$this->name} does not exist");
        }
        return $this->container->get($dataFormatter);
    }

    public function getNormalizer(): NormalizerInterface
    {
        $normalizer = $this->protocolManager->getNormalizer($this->name);
        if (! $this->container->has($normalizer)) {
            throw new InvalidArgumentException("Normalizer {$normalizer} for {$this->name} does not exist");
        }
        return $this->container->get($normalizer);
    }
}
