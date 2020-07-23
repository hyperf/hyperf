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
namespace Hyperf\RpcClient;

use Hyperf\Contract\PackerInterface;
use Hyperf\Rpc\Contract\TransporterInterface;
use InvalidArgumentException;

class Client
{
    /**
     * @var null|PackerInterface
     */
    private $packer;

    /**
     * @var null|TransporterInterface
     */
    private $transporter;

    public function send($data)
    {
        if (! $this->packer) {
            throw new InvalidArgumentException('Packer missing.');
        }
        if (! $this->transporter) {
            throw new InvalidArgumentException('Transporter missing.');
        }
        $packer = $this->getPacker();
        $packedData = $packer->pack($data);
        $response = $this->getTransporter()->send($packedData);
        return $packer->unpack((string) $response);
    }

    public function recv()
    {
        $packer = $this->getPacker();
        $response = $this->getTransporter()->recv();
        return $packer->unpack((string) $response);
    }

    public function getPacker(): PackerInterface
    {
        return $this->packer;
    }

    public function setPacker(PackerInterface $packer): self
    {
        $this->packer = $packer;
        return $this;
    }

    public function getTransporter(): TransporterInterface
    {
        return $this->transporter;
    }

    public function setTransporter(TransporterInterface $transporter): self
    {
        $this->transporter = $transporter;
        return $this;
    }
}
