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

namespace Hyperf\Rpc;

class Client
{
    /**
     * @var Contract\PackerInterface
     */
    private $packer;

    /**
     * @var Contract\TransporterInterface
     */
    private $transporter;

    public function send($data)
    {
        $packedData = $this->packer->pack($data);
        return $this->transporter->send($packedData);
    }
}
