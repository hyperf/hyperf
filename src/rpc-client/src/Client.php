<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcClient;

use Hyperf\Contract\PackerInterface;
use Hyperf\Rpc\Contract\TransporterInterface;

class Client
{
    /**
     * @var PackerInterface
     */
    private $packer;

    /**
     * @var TransporterInterface
     */
    private $transporter;

    public function __construct(
        PackerInterface $packer,
        TransporterInterface $transporter
    ) {
        $this->packer = $packer;
        $this->transporter = $transporter;
    }

    public function send($data)
    {
        $packedData = $this->packer->pack($data);
        $response = $this->transporter->send($packedData);
        return $this->packer->unpack($response);
    }
}
